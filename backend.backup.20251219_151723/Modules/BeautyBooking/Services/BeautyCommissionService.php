<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Services;

use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyCommissionSetting;

/**
 * Beauty Commission Service
 * سرویس کمیسیون
 *
 * Handles commission calculation based on various rules
 * مدیریت محاسبه کمیسیون بر اساس قوانین مختلف
 */
class BeautyCommissionService
{
    /**
     * Calculate commission for a booking
     * محاسبه کمیسیون برای یک رزرو
     *
     * @param int $salonId
     * @param int $serviceId
     * @param float $basePrice
     * @return float
     */
    public function calculateCommission(int $salonId, int $serviceId, float $basePrice): float
    {
        $service = BeautyService::findOrFail($serviceId);
        $salon = BeautySalon::findOrFail($salonId);
        
        // Get commission percentage
        // دریافت درصد کمیسیون
        $commissionPercentage = $this->getCommissionPercentage($salonId, $service->category_id, $salon->business_type);
        
        // Calculate commission amount
        // محاسبه مبلغ کمیسیون
        $commissionAmount = $basePrice * ($commissionPercentage / 100);
        
        // Apply min/max constraints if set
        // اعمال محدودیت‌های حداقل/حداکثر در صورت تنظیم
        $setting = $this->getCommissionSetting($service->category_id, $salon->business_type);
        
        if ($setting) {
            if ($setting->min_commission > 0 && $commissionAmount < $setting->min_commission) {
                $commissionAmount = $setting->min_commission;
            }
            
            if ($setting->max_commission && $commissionAmount > $setting->max_commission) {
                $commissionAmount = $setting->max_commission;
            }
        }
        
        return round($commissionAmount, 2);
    }
    
    /**
     * Get commission percentage
     * دریافت درصد کمیسیون
     *
     * @param int $salonId
     * @param int $serviceCategoryId
     * @param string $salonLevel
     * @return float
     */
    public function getCommissionPercentage(int $salonId, int $serviceCategoryId, string $salonLevel): float
    {
        // 1. Check for category-specific + salon level setting
        // بررسی تنظیمات مخصوص دسته‌بندی + سطح سالن
        $setting = BeautyCommissionSetting::where('service_category_id', $serviceCategoryId)
            ->where('salon_level', $salonLevel)
            ->where('status', 1)
            ->first();
        
        if ($setting) {
            $commissionPercentage = $setting->commission_percentage;
        } else {
            // 2. Check for category-specific setting (any salon level)
            // بررسی تنظیمات مخصوص دسته‌بندی (هر سطح سالن)
            $setting = BeautyCommissionSetting::where('service_category_id', $serviceCategoryId)
                ->whereNull('salon_level')
                ->where('status', 1)
                ->first();
            
            if ($setting) {
                $commissionPercentage = $setting->commission_percentage;
            } else {
                // 3. Check for salon level setting (any category)
                // بررسی تنظیمات سطح سالن (هر دسته‌بندی)
                $setting = BeautyCommissionSetting::whereNull('service_category_id')
                    ->where('salon_level', $salonLevel)
                    ->where('status', 1)
                    ->first();
                
                if ($setting) {
                    $commissionPercentage = $setting->commission_percentage;
                } else {
                    // 4. Default commission percentage
                    // درصد کمیسیون پیش‌فرض
                    $commissionPercentage = config('beautybooking.commission.default_percentage', 10.0);
                }
            }
        }
        
        // 5. Apply Top Rated badge discount if salon has Top Rated badge
        // اعمال تخفیف نشان Top Rated در صورت داشتن نشان Top Rated
        $salon = BeautySalon::find($salonId);
        if ($salon && $salon->badges()->where('badge_type', 'top_rated')->active()->exists()) {
            $topRatedDiscount = config('beautybooking.commission.top_rated_discount', 0);
            $commissionPercentage = max(0, $commissionPercentage - $topRatedDiscount);
        }
        
        return $commissionPercentage;
    }
    
    /**
     * Get commission setting
     * دریافت تنظیمات کمیسیون
     *
     * @param int $serviceCategoryId
     * @param string $salonLevel
     * @return BeautyCommissionSetting|null
     */
    private function getCommissionSetting(int $serviceCategoryId, string $salonLevel): ?BeautyCommissionSetting
    {
        return BeautyCommissionSetting::where(function($q) use ($serviceCategoryId, $salonLevel) {
                $q->where(function($q2) use ($serviceCategoryId, $salonLevel) {
                    $q2->where('service_category_id', $serviceCategoryId)
                       ->where('salon_level', $salonLevel);
                })
                ->orWhere(function($q3) use ($serviceCategoryId) {
                    $q3->where('service_category_id', $serviceCategoryId)
                       ->whereNull('salon_level');
                })
                ->orWhere(function($q4) use ($salonLevel) {
                    $q4->whereNull('service_category_id')
                       ->where('salon_level', $salonLevel);
                });
            })
            ->where('status', 1)
            ->first();
    }
    
    /**
     * Record commission transaction
     * ثبت تراکنش کمیسیون
     *
     * @param \Modules\BeautyBooking\Entities\BeautyBooking $booking
     * @param float $commissionAmount
     * @return \Modules\BeautyBooking\Entities\BeautyTransaction
     */
    public function recordCommissionTransaction($booking, float $commissionAmount)
    {
        return \Modules\BeautyBooking\Entities\BeautyTransaction::create([
            'booking_id' => $booking->id,
            'salon_id' => $booking->salon_id,
            'zone_id' => $booking->zone_id,
            'transaction_type' => 'commission',
            'amount' => $booking->total_amount,
            'commission' => $commissionAmount,
            'service_fee' => $booking->service_fee,
            'status' => 'completed',
            'notes' => 'Commission for booking #' . $booking->booking_reference,
        ]);
    }
}

