<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Observers;

use Illuminate\Support\Facades\Cache;
use Modules\BeautyBooking\Entities\BeautyReview;
use Modules\BeautyBooking\Services\BeautyBadgeService;
use Modules\BeautyBooking\Services\BeautySalonService;
use Modules\BeautyBooking\Services\BeautyRankingService;

/**
 * Beauty Review Observer
 * Observer برای نظرات
 *
 * Automatically updates salon statistics and badges when reviews are created/updated
 * به‌روزرسانی خودکار آمار سالن و نشان‌ها هنگام ایجاد/به‌روزرسانی نظرات
 */
class BeautyReviewObserver
{
    public function __construct(
        private BeautyBadgeService $badgeService,
        private BeautySalonService $salonService,
        private BeautyRankingService $rankingService
    ) {}

    /**
     * Handle the BeautyReview "created" event.
     * مدیریت رویداد ایجاد نظر
     *
     * @param BeautyReview $review
     * @return void
     */
    public function created(BeautyReview $review): void
    {
        try {
            // Only process if review is approved
            // فقط در صورت تأیید نظر پردازش شود
            // Note: Status enum values are 'pending', 'approved', 'rejected' - only 'approved' reviews count
            // توجه: مقادیر enum وضعیت عبارتند از 'pending', 'approved', 'rejected' - فقط نظرات 'approved' محاسبه می‌شوند
            if ($review->status !== 'approved') {
                return;
            }

            // Update salon statistics (rating, review count)
            // به‌روزرسانی آمار سالن (امتیاز، تعداد نظرات)
            $this->salonService->updateSalonStatistics($review->salon_id);

            // Recalculate badges (rating affects Top Rated badge)
            // محاسبه مجدد نشان‌ها (امتیاز بر نشان Top Rated تأثیر می‌گذارد)
            $this->badgeService->calculateAndAssignBadges($review->salon_id);
            
            // Invalidate ranking and search caches
            // باطل کردن cache های رتبه‌بندی و جستجو
            $this->rankingService->invalidateSalonRankingCache($review->salon_id);
            $this->invalidateListCaches();
        } catch (\Exception $e) {
            // Log error but don't throw - allow review creation to succeed
            // ثبت خطا اما عدم throw - اجازه موفقیت ایجاد نظر
            \Log::error('BeautyReviewObserver::created failed', [
                'review_id' => $review->id,
                'salon_id' => $review->salon_id,
                'booking_id' => $review->booking_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the BeautyReview "updated" event.
     * مدیریت رویداد به‌روزرسانی نظر
     *
     * @param BeautyReview $review
     * @return void
     */
    public function updated(BeautyReview $review): void
    {
        try {
            // Check if status changed
            // بررسی تغییر وضعیت
            if ($review->isDirty('status')) {
                // Update statistics when status changes (to approved, from approved, or to rejected)
                // به‌روزرسانی آمار هنگام تغییر وضعیت (به approved، از approved، یا به rejected)
                // This ensures statistics are recalculated when:
                // - Review is approved (should be included in rating)
                // - Review is rejected (should be excluded from rating)
                // - Review status changes from approved to something else
            // این اطمینان می‌دهد که آمار در موارد زیر دوباره محاسبه می‌شود:
            // - نظر تأیید می‌شود (باید در رتبه‌بندی گنجانده شود)
            // - نظر رد می‌شود (باید از رتبه‌بندی حذف شود)
            // - وضعیت نظر از approved به چیز دیگری تغییر می‌کند
            $this->salonService->updateSalonStatistics($review->salon_id);
            $this->badgeService->calculateAndAssignBadges($review->salon_id);
            
            // Invalidate ranking and search caches
            // باطل کردن cache های رتبه‌بندی و جستجو
            $this->rankingService->invalidateSalonRankingCache($review->salon_id);
            $this->invalidateListCaches();
        } elseif ($review->isDirty('rating')) {
                // If rating changed and review is approved, update statistics and badges
                // در صورت تغییر امتیاز و تأیید بودن نظر، به‌روزرسانی آمار و نشان‌ها
                // Only update if review is approved (pending/rejected reviews don't affect rating)
                // فقط در صورت تأیید بودن نظر به‌روزرسانی شود (نظرات pending/rejected بر رتبه‌بندی تأثیر نمی‌گذارند)
            if ($review->status === 'approved') {
                $this->salonService->updateSalonStatistics($review->salon_id);
                $this->badgeService->calculateAndAssignBadges($review->salon_id);
                
                // Invalidate ranking and search caches
                // باطل کردن cache های رتبه‌بندی و جستجو
                $this->rankingService->invalidateSalonRankingCache($review->salon_id);
                $this->invalidateListCaches();
            }
        }
        } catch (\Exception $e) {
            // Log error but don't throw - allow review update to succeed
            // ثبت خطا اما عدم throw - اجازه موفقیت به‌روزرسانی نظر
            \Log::error('BeautyReviewObserver::updated failed', [
                'review_id' => $review->id,
                'salon_id' => $review->salon_id,
                'booking_id' => $review->booking_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the BeautyReview "deleted" event.
     * مدیریت رویداد حذف نظر
     *
     * @param BeautyReview $review
     * @return void
     */
    public function deleted(BeautyReview $review): void
    {
        try {
            // Update salon statistics when review is deleted
            // به‌روزرسانی آمار سالن هنگام حذف نظر
            $this->salonService->updateSalonStatistics($review->salon_id);

            // Recalculate badges
            // محاسبه مجدد نشان‌ها
            $this->badgeService->calculateAndAssignBadges($review->salon_id);
            
            // Invalidate ranking and search caches
            // باطل کردن cache های رتبه‌بندی و جستجو
            $this->rankingService->invalidateSalonRankingCache($review->salon_id);
            $this->invalidateListCaches();
        } catch (\Exception $e) {
            // Log error but don't throw - allow review deletion to succeed
            // ثبت خطا اما عدم throw - اجازه موفقیت حذف نظر
            \Log::error('BeautyReviewObserver::deleted failed', [
                'review_id' => $review->id,
                'salon_id' => $review->salon_id,
                'booking_id' => $review->booking_id,
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

