<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Services;

use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBadge;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Beauty Badge Service
 * سرویس نشان‌ها (Badge)
 *
 * Handles badge assignment and management
 * مدیریت تخصیص و مدیریت نشان‌ها
 */
class BeautyBadgeService
{
    /**
     * Calculate and assign badges for a salon
     * محاسبه و تخصیص نشان‌ها برای یک سالن
     *
     * @param int $salonId
     * @return void
     */
    public function calculateAndAssignBadges(int $salonId): void
    {
        $salon = BeautySalon::with('store')->findOrFail($salonId);
        
        // Check if salon's store is active before assigning badges
        // بررسی فعال بودن فروشگاه سالن قبل از تخصیص نشان‌ها
        $isStoreActive = $salon->store && $salon->store->status === 1 && $salon->store->active === 1;
        
        if (!$isStoreActive) {
            // Revoke all badges if store is inactive
            // لغو تمام نشان‌ها در صورت غیرفعال بودن فروشگاه
            $this->revokeBadge($salonId, 'top_rated');
            $this->revokeBadge($salonId, 'featured');
            $this->clearBadgeCache($salonId);
            return;
        }
        
        // Get badge criteria from config
        // دریافت معیارهای نشان از config
        $topRatedConfig = config('beautybooking.badge.top_rated', []);
        $minRating = $topRatedConfig['min_rating'] ?? 4.8;
        $minBookings = $topRatedConfig['min_bookings'] ?? 50;
        $maxCancellationRate = $topRatedConfig['max_cancellation_rate'] ?? 2.0;
        $activityDays = $topRatedConfig['activity_days'] ?? 30;
        
        // Top Rated Badge: Check all criteria
        // نشان Top Rated: بررسی تمام معیارها
        // Requirement: "میانگین امتیاز بالاتر از ۴.۸" means >= 4.8 (greater than or equal)
        // الزامات: "میانگین امتیاز بالاتر از ۴.۸" یعنی >= 4.8 (بزرگتر یا مساوی)
        // Fixed: Changed from > to >= to include salons with exactly 4.8 rating
        // اصلاح شده: از > به >= تغییر یافت تا سالن‌های با رتبه دقیقاً 4.8 نیز شامل شوند
        $hasMinRating = $salon->avg_rating >= $minRating;
        $hasMinBookings = $salon->total_bookings >= $minBookings;
        $hasLowCancellationRate = ($salon->cancellation_rate ?? 0) < $maxCancellationRate;
        
        // Check activity in last N days
        // بررسی فعالیت در N روز گذشته
        $recentBookings = BeautyBooking::where('salon_id', $salonId)
            ->where('booking_date', '>=', Carbon::now()->subDays($activityDays))
            ->where('status', '!=', 'cancelled')
            ->exists();
        
        if ($hasMinRating && $hasMinBookings && $hasLowCancellationRate && $recentBookings) {
            $this->assignBadgeIfNotExists($salonId, 'top_rated');
        } else {
            $this->revokeBadge($salonId, 'top_rated');
        }
        
        // Featured Badge: Active subscription exists (if required)
        // نشان Featured: وجود اشتراک فعال (در صورت نیاز)
        $featuredConfig = config('beautybooking.badge.featured', []);
        $requiresSubscription = $featuredConfig['requires_subscription'] ?? true;
        
        if ($requiresSubscription) {
            $hasActiveSubscription = $salon->subscriptions()
                ->where('status', 'active')
                ->where('end_date', '>=', now()->toDateString())
                ->exists();
                
            if ($hasActiveSubscription) {
                $this->assignBadgeIfNotExists($salonId, 'featured');
            } else {
                // Check if subscription expired and revoke
                // بررسی انقضای اشتراک و لغو
                $this->revokeBadge($salonId, 'featured');
            }
        }
        
        // Clear badge cache after calculation
        // پاک کردن cache نشان پس از محاسبه
        $this->clearBadgeCache($salonId);
        
        // Verified Badge: Manual admin approval (not auto-calculated)
        // نشان Verified: تأیید دستی ادمین (خودکار محاسبه نمی‌شود)
        // This is set by admin when verifying salon
    }
    
    /**
     * Assign badge if it doesn't already exist
     * تخصیص نشان در صورت عدم وجود
     *
     * @param int $salonId
     * @param string $badgeType
     * @param \DateTime|null $expiresAt
     * @return BeautyBadge|null
     */
    public function assignBadgeIfNotExists(int $salonId, string $badgeType, ?\DateTime $expiresAt = null): ?BeautyBadge
    {
        // Check if badge already exists and is active
        // بررسی وجود و فعال بودن نشان
        $existingBadge = BeautyBadge::where('salon_id', $salonId)
            ->where('badge_type', $badgeType)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->first();
        
        if ($existingBadge) {
            return $existingBadge;
        }
        
        // Create new badge
        // ایجاد نشان جدید
        $badge = BeautyBadge::create([
            'salon_id' => $salonId,
            'badge_type' => $badgeType,
            'earned_at' => now(),
            'expires_at' => $expiresAt ? Carbon::instance($expiresAt) : null,
        ]);
        
        // Clear badge cache
        // پاک کردن cache نشان
        $this->clearBadgeCache($salonId);
        
        return $badge;
    }
    
    /**
     * Assign badge
     * تخصیص نشان
     *
     * @param int $salonId
     * @param string $badgeType
     * @param \DateTime|null $expiresAt
     * @return BeautyBadge
     */
    public function assignBadge(int $salonId, string $badgeType, ?\DateTime $expiresAt = null): BeautyBadge
    {
        return BeautyBadge::create([
            'salon_id' => $salonId,
            'badge_type' => $badgeType,
            'earned_at' => now(),
            'expires_at' => $expiresAt ? Carbon::instance($expiresAt) : null,
        ]);
    }
    
    /**
     * Revoke badge
     * لغو نشان
     *
     * @param int $salonId
     * @param string $badgeType
     * @return bool
     */
    public function revokeBadge(int $salonId, string $badgeType): bool
    {
        // Expire all active badges of this type
        // منقضی کردن تمام نشان‌های فعال این نوع
        $result = BeautyBadge::where('salon_id', $salonId)
            ->where('badge_type', $badgeType)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->update(['expires_at' => now()]) > 0;
        
        // Clear badge cache
        // پاک کردن cache نشان
        if ($result) {
            $this->clearBadgeCache($salonId);
        }
        
        return $result;
    }
    
    /**
     * Get active badges for a salon
     * دریافت نشان‌های فعال برای یک سالن
     *
     * @param int $salonId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveBadges(int $salonId)
    {
        $cacheKey = "beauty_badge_{$salonId}";
        $ttl = config('beautybooking.cache.badge_ttl', 3600); // 1 hour default
        
        return Cache::remember($cacheKey, $ttl, function () use ($salonId) {
            return BeautyBadge::where('salon_id', $salonId)
                ->active()
                ->get();
        });
    }
    
    /**
     * Clear badge cache for a salon
     * پاک کردن cache نشان برای یک سالن
     *
     * @param int $salonId
     * @return void
     */
    public function clearBadgeCache(int $salonId): void
    {
        $cacheKey = "beauty_badge_{$salonId}";
        Cache::forget($cacheKey);
    }
    
    /**
     * Invalidate badge cache for all salons belonging to a store
     * باطل کردن cache نشان برای تمام سالن‌های متعلق به یک فروشگاه
     *
     * This method should be called when store status changes
     * این متد باید زمانی فراخوانی شود که وضعیت فروشگاه تغییر می‌کند
     *
     * @param int $storeId
     * @return void
     */
    public static function invalidateBadgeCacheForStore(int $storeId): void
    {
        // Only proceed if BeautyBooking module is active
        // فقط در صورت فعال بودن ماژول BeautyBooking ادامه دهید
        if (!addon_published_status('BeautyBooking')) {
            return;
        }
        
        // Find all salons for this store and invalidate their badge caches
        // یافتن تمام سالن‌های این فروشگاه و باطل کردن cache نشان‌های آن‌ها
        $salons = BeautySalon::where('store_id', $storeId)->pluck('id');
        
        foreach ($salons as $salonId) {
            $cacheKey = "beauty_badge_{$salonId}";
            Cache::forget($cacheKey);
        }
        
        // Also recalculate badges for all salons of this store
        // همچنین محاسبه مجدد نشان‌ها برای تمام سالن‌های این فروشگاه
        $badgeService = app(self::class);
        foreach ($salons as $salonId) {
            $badgeService->calculateAndAssignBadges($salonId);
        }
    }
}

