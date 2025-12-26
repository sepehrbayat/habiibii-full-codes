<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Services;

use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyServiceRelation;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Illuminate\Support\Facades\DB;

/**
 * Beauty Cross Selling Service
 * سرویس فروش متقابل/افزایش فروش
 *
 * Handles cross-selling and upsell suggestions
 * مدیریت پیشنهادات فروش متقابل و افزایش فروش
 */
class BeautyCrossSellingService
{
    /**
     * Get suggested services for a given service
     * دریافت خدمات پیشنهادی برای یک خدمت مشخص
     *
     * @param int $serviceId
     * @param int|null $userId
     * @param int|null $salonId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSuggestedServices(int $serviceId, ?int $userId = null, ?int $salonId = null)
    {
        $service = BeautyService::findOrFail($serviceId);
        $maxSuggestions = config('beautybooking.cross_selling.max_suggestions', 5);
        
        // Get explicit relations defined by vendor
        // دریافت روابط صریح تعریف شده توسط فروشنده
        $explicitRelations = BeautyServiceRelation::where('service_id', $serviceId)
            ->where('status', 1)
            ->with('relatedService')
            ->orderByDesc('priority')
            ->get()
            ->map(function($relation) {
                $relatedService = $relation->relatedService;
                if (!$relatedService || !$relatedService->status) {
                    return null;
                }
                
                return [
                    'service' => $relatedService,
                    'relation_type' => $relation->relation_type,
                    'priority' => $relation->priority,
                    'source' => 'explicit',
                ];
            })
            ->filter()
            ->take($maxSuggestions);
        
        $suggestions = collect($explicitRelations);
        
        // If we need more suggestions, add popular services from same salon/category
        // اگر به پیشنهادات بیشتری نیاز داریم، خدمات محبوب از همان سالن/دسته را اضافه کنیم
        if ($suggestions->count() < $maxSuggestions && $salonId) {
            $popularServices = $this->getPopularServices($service, $salonId, $maxSuggestions - $suggestions->count());
            $suggestions = $suggestions->merge($popularServices);
        }
        
        // If user is provided, personalize based on booking history
        // اگر کاربر ارائه شده باشد، بر اساس تاریخچه رزرو شخصی‌سازی شود
        if ($userId && $suggestions->count() < $maxSuggestions) {
            $personalized = $this->getPersonalizedSuggestions($serviceId, $userId, $salonId, $maxSuggestions - $suggestions->count());
            $suggestions = $suggestions->merge($personalized);
        }
        
        // Remove duplicates and format
        // حذف تکراری‌ها و فرمت
        $uniqueSuggestions = $suggestions->unique(function($item) {
            // Use service ID for uniqueness check
            // استفاده از شناسه خدمت برای بررسی یکتایی
            return isset($item['service']) && $item['service'] ? $item['service']->id : null;
        })->take($maxSuggestions);
        
        return $uniqueSuggestions->map(function($item) {
            // Ensure item has the expected structure
            // اطمینان از ساختار مورد انتظار آیتم
            if (!isset($item['service']) || !$item['service']) {
                return null;
            }
            
            $service = $item['service'];
            
            // Validate service has required properties
            // اعتبارسنجی وجود ویژگی‌های مورد نیاز خدمت
            if (!isset($service->id) || !isset($service->name)) {
                return null;
            }
            
            return [
                'id' => $service->id,
                'name' => $service->name ?? '',
                'description' => $service->description ?? '',
                'price' => $service->price ?? 0.0,
                'duration_minutes' => $service->duration_minutes ?? 60,
                'image' => $service->image ? asset('storage/' . $service->image) : null,
                'relation_type' => $item['relation_type'] ?? 'complementary',
                'source' => $item['source'] ?? 'algorithm',
            ];
        })
        ->filter() // Remove null values
        ->values();
    }
    
    /**
     * Get popular services from same salon/category
     * دریافت خدمات محبوب از همان سالن/دسته
     *
     * @param BeautyService $service
     * @param int $salonId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function getPopularServices(BeautyService $service, int $salonId, int $limit)
    {
        // Get services from same salon and category that are frequently booked together
        // دریافت خدمات از همان سالن و دسته که اغلب با هم رزرو می‌شوند
        $popularServices = BeautyService::where('salon_id', $salonId)
            ->where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->where('status', 1)
            ->where('service_type', 'service') // Only regular services, not consultations
            ->withCount(['bookings' => function($q) {
                $q->where('status', '!=', 'cancelled')
                  ->where('created_at', '>=', now()->subDays(30));
            }])
            ->orderByDesc('bookings_count')
            ->limit($limit)
            ->get();
        
        return $popularServices->map(function($relatedService) {
            return [
                'service' => $relatedService,
                'relation_type' => 'complementary',
                'priority' => 0,
                'source' => 'popular',
            ];
        });
    }
    
    /**
     * Get personalized suggestions based on user booking history
     * دریافت پیشنهادات شخصی‌سازی شده بر اساس تاریخچه رزرو کاربر
     *
     * @param int $serviceId
     * @param int $userId
     * @param int|null $salonId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function getPersonalizedSuggestions(int $serviceId, int $userId, ?int $salonId, int $limit)
    {
        // Find services that users who booked this service also booked
        // یافتن خدماتی که کاربرانی که این خدمت را رزرو کرده‌اند نیز رزرو کرده‌اند
        $relatedServiceIds = DB::table('beauty_bookings as b1')
            ->join('beauty_bookings as b2', function($join) use ($serviceId, $userId) {
                $join->on('b1.user_id', '=', 'b2.user_id')
                     ->where('b1.service_id', '=', $serviceId)
                     ->where('b2.service_id', '!=', $serviceId)
                     ->where('b1.user_id', '!=', $userId); // Exclude current user
            })
            ->where('b2.status', '!=', 'cancelled')
            ->where('b2.created_at', '>=', now()->subDays(90))
            ->select('b2.service_id')
            ->selectRaw('COUNT(*) as booking_count')
            ->groupBy('b2.service_id')
            ->orderByDesc('booking_count')
            ->limit($limit)
            ->pluck('b2.service_id');
        
        if ($relatedServiceIds->isEmpty()) {
            return collect([]);
        }
        
        $query = BeautyService::whereIn('id', $relatedServiceIds)
            ->where('status', 1)
            ->where('service_type', 'service');
        
        if ($salonId) {
            $query->where('salon_id', $salonId);
        }
        
        $services = $query->get();
        
        return $services->map(function($service) {
            return [
                'service' => $service,
                'relation_type' => 'complementary',
                'priority' => 0,
                'source' => 'personalized',
            ];
        });
    }
    
    /**
     * Record cross-selling revenue
     * ثبت درآمد فروش متقابل
     *
     * @param BeautyBooking $booking
     * @param array $additionalServices
     * @return void
     */
    public function recordCrossSellingRevenue(BeautyBooking $booking, array $additionalServices): void
    {
        if (empty($additionalServices)) {
            return;
        }
        
        $totalAmount = array_sum(array_column($additionalServices, 'price'));
        $commissionPercentage = config('beautybooking.cross_selling.commission_percentage', 10.0);
        $commissionAmount = $totalAmount * ($commissionPercentage / 100);
        
        // Record in revenue service
        // ثبت در سرویس درآمد
        $revenueService = app(BeautyRevenueService::class);
        $revenueService->recordCrossSellingRevenue($booking, $additionalServices);
    }
}

