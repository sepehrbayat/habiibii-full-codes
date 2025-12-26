<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Observers;

use Illuminate\Support\Facades\Cache;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Services\BeautyBadgeService;
use Modules\BeautyBooking\Services\BeautySalonService;
use Modules\BeautyBooking\Services\BeautyRankingService;

/**
 * Beauty Booking Observer
 * Observer برای رزروها
 *
 * Automatically updates salon statistics and badges when bookings are created/updated
 * به‌روزرسانی خودکار آمار سالن و نشان‌ها هنگام ایجاد/به‌روزرسانی رزروها
 */
class BeautyBookingObserver
{
    public function __construct(
        private BeautyBadgeService $badgeService,
        private BeautySalonService $salonService,
        private BeautyRankingService $rankingService
    ) {}

    /**
     * Handle the BeautyBooking "created" event.
     * مدیریت رویداد ایجاد رزرو
     *
     * @param BeautyBooking $booking
     * @return void
     */
    public function created(BeautyBooking $booking): void
    {
        try {
            // Update salon booking statistics
            // به‌روزرسانی آمار رزرو سالن
            $this->salonService->updateBookingStatistics($booking->salon_id);

            // Recalculate badges (booking count affects Top Rated badge)
            // محاسبه مجدد نشان‌ها (تعداد رزرو بر نشان Top Rated تأثیر می‌گذارد)
            $this->badgeService->calculateAndAssignBadges($booking->salon_id);
            
            // Invalidate ranking and search caches
            // باطل کردن cache های رتبه‌بندی و جستجو
            $this->rankingService->invalidateSalonRankingCache($booking->salon_id);
            $this->invalidateListCaches();
        } catch (\Exception $e) {
            // Log error but don't throw - allow booking creation to succeed
            // ثبت خطا اما عدم throw - اجازه موفقیت ایجاد رزرو
            \Log::error('BeautyBookingObserver::created failed', [
                'booking_id' => $booking->id,
                'salon_id' => $booking->salon_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the BeautyBooking "updated" event.
     * مدیریت رویداد به‌روزرسانی رزرو
     *
     * @param BeautyBooking $booking
     * @return void
     */
    public function updated(BeautyBooking $booking): void
    {
        try {
            // Check if status changed (affects cancellation rate and booking count)
            // بررسی تغییر وضعیت (تأثیر بر نرخ لغو و تعداد رزرو)
            if ($booking->isDirty('status')) {
                // Update all salon statistics
                // به‌روزرسانی تمام آمار سالن
                $this->salonService->updateSalonStatistics($booking->salon_id);

                // Recalculate badges
                // محاسبه مجدد نشان‌ها
                $this->badgeService->calculateAndAssignBadges($booking->salon_id);
                
                // Invalidate ranking and search caches
                // باطل کردن cache های رتبه‌بندی و جستجو
                $this->rankingService->invalidateSalonRankingCache($booking->salon_id);
                $this->invalidateListCaches();
            }
        } catch (\Exception $e) {
            // Log error but don't throw - allow booking update to succeed
            // ثبت خطا اما عدم throw - اجازه موفقیت به‌روزرسانی رزرو
            \Log::error('BeautyBookingObserver::updated failed', [
                'booking_id' => $booking->id,
                'salon_id' => $booking->salon_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the BeautyBooking "deleted" event.
     * مدیریت رویداد حذف رزرو
     *
     * @param BeautyBooking $booking
     * @return void
     */
    public function deleted(BeautyBooking $booking): void
    {
        try {
            // Update salon statistics when booking is deleted
            // به‌روزرسانی آمار سالن هنگام حذف رزرو
            $this->salonService->updateSalonStatistics($booking->salon_id);

            // Recalculate badges
            // محاسبه مجدد نشان‌ها
            $this->badgeService->calculateAndAssignBadges($booking->salon_id);
            
            // Invalidate ranking and search caches
            // باطل کردن cache های رتبه‌بندی و جستجو
            $this->rankingService->invalidateSalonRankingCache($booking->salon_id);
            $this->invalidateListCaches();
        } catch (\Exception $e) {
            // Log error but don't throw - allow booking deletion to succeed
            // ثبت خطا اما عدم throw - اجازه موفقیت حذف رزرو
            \Log::error('BeautyBookingObserver::deleted failed', [
                'booking_id' => $booking->id,
                'salon_id' => $booking->salon_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    
    /**
     * Invalidate list caches (popular, top-rated)
     * باطل کردن cache های لیست (محبوب، دارای رتبه بالا)
     *
     * @return void
     */
    private function invalidateListCaches(): void
    {
        Cache::forget('beauty_salons_popular');
        Cache::forget('beauty_salons_top_rated');
    }
}

