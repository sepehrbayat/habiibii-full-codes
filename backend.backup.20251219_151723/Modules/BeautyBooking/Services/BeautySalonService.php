<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Services;

use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyReview;
use Modules\BeautyBooking\Entities\BeautyBooking;

/**
 * Beauty Salon Service
 * سرویس سالن زیبایی
 *
 * Handles salon-related business logic
 * مدیریت منطق کسب‌وکار مربوط به سالن
 */
class BeautySalonService
{
    /**
     * Update salon rating statistics
     * به‌روزرسانی آمار رتبه‌بندی سالن
     *
     * @param int $salonId
     * @return void
     */
    public function updateRatingStatistics(int $salonId): void
    {
        $salon = BeautySalon::findOrFail($salonId);
        
        // Calculate average rating from approved reviews
        // محاسبه میانگین رتبه از نظرات تأیید شده
        $approvedReviews = BeautyReview::where('salon_id', $salonId)
            ->where('status', 'approved')
            ->get();
        
        $totalReviews = $approvedReviews->count();
        $avgRating = $totalReviews > 0 
            ? round($approvedReviews->avg('rating'), 2) 
            : 0.00;
        
        // Update salon
        // به‌روزرسانی سالن
        $salon->update([
            'avg_rating' => $avgRating,
            'total_reviews' => $totalReviews,
        ]);
        
        // Recalculate badges (rating affects Top Rated badge)
        // محاسبه مجدد نشان‌ها (رتبه بر نشان Top Rated تأثیر می‌گذارد)
        $badgeService = app(\Modules\BeautyBooking\Services\BeautyBadgeService::class);
        $badgeService->calculateAndAssignBadges($salonId);
    }
    
    /**
     * Update salon booking statistics
     * به‌روزرسانی آمار رزرو سالن
     *
     * @param int $salonId
     * @return void
     */
    public function updateBookingStatistics(int $salonId): void
    {
        $salon = BeautySalon::findOrFail($salonId);
        
        // Count total bookings
        // شمارش کل رزروها
        $totalBookings = BeautyBooking::where('salon_id', $salonId)
            ->count();
        
        // Update salon
        // به‌روزرسانی سالن
        $salon->update([
            'total_bookings' => $totalBookings,
        ]);
        
        // Recalculate badges (booking count affects Top Rated badge)
        // محاسبه مجدد نشان‌ها (تعداد رزرو بر نشان Top Rated تأثیر می‌گذارد)
        $badgeService = app(\Modules\BeautyBooking\Services\BeautyBadgeService::class);
        $badgeService->calculateAndAssignBadges($salonId);
    }
    
    /**
     * Update all salon statistics (rating, reviews, bookings, cancellation rate)
     * به‌روزرسانی تمام آمار سالن (رتبه، نظرات، رزروها، نرخ لغو)
     *
     * @param int $salonId
     * @return void
     */
    public function updateSalonStatistics(int $salonId): void
    {
        $salon = BeautySalon::findOrFail($salonId);
        
        // Update rating statistics
        // به‌روزرسانی آمار رتبه‌بندی
        $this->updateRatingStatistics($salonId);
        
        // Update booking statistics
        // به‌روزرسانی آمار رزرو
        $this->updateBookingStatistics($salonId);
        
        // Update cancellation rate
        // به‌روزرسانی نرخ لغو
        $this->updateCancellationRate($salonId);
    }
    
    /**
     * Update salon cancellation rate
     * به‌روزرسانی نرخ لغو سالن
     *
     * @param int $salonId
     * @return void
     */
    public function updateCancellationRate(int $salonId): void
    {
        $salon = BeautySalon::findOrFail($salonId);
        
        // Count total bookings and cancellations
        // شمارش کل رزروها و لغوها
        $totalBookings = BeautyBooking::where('salon_id', $salonId)
            ->where('status', '!=', 'cancelled')
            ->count();
        
        $totalCancellations = BeautyBooking::where('salon_id', $salonId)
            ->where('status', 'cancelled')
            ->count();
        
        // Calculate cancellation rate
        // محاسبه نرخ لغو
        $totalBookingsWithCancellations = $totalBookings + $totalCancellations;
        $cancellationRate = $totalBookingsWithCancellations > 0
            ? round(($totalCancellations / $totalBookingsWithCancellations) * 100, 2)
            : 0.00;
        
        // Update salon
        // به‌روزرسانی سالن
        $salon->update([
            'total_cancellations' => $totalCancellations,
            'cancellation_rate' => $cancellationRate,
        ]);
    }
}

