<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Services\BeautyBadgeService;

/**
 * Beauty Badge Controller (Vendor)
 * کنترلر نشان (فروشنده)
 */
class BeautyBadgeController extends Controller
{
    public function __construct(
        private BeautyBadgeService $badgeService
    ) {}

    /**
     * Index - Badge status
     * وضعیت نشان‌ها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        // Get current badges
        // دریافت نشان‌های فعلی
        $currentBadges = $salon->badges_list ?? [];

        // Get badge criteria progress
        // دریافت پیشرفت معیارهای نشان
        $criteria = [
            'top_rated' => [
                'name' => translate('Top Rated'),
                'description' => translate('Rating > 4.5 AND minimum 50 bookings'),
                'current_rating' => $salon->avg_rating ?? 0,
                'required_rating' => config('beautybooking.badge.top_rated.min_rating', 4.5),
                'current_bookings' => $salon->total_bookings ?? 0,
                'required_bookings' => config('beautybooking.badge.top_rated.min_bookings', 50),
                'progress' => $this->calculateProgress($salon->avg_rating ?? 0, config('beautybooking.badge.top_rated.min_rating', 4.5), $salon->total_bookings ?? 0, config('beautybooking.badge.top_rated.min_bookings', 50)),
                'has_badge' => in_array('top_rated', $currentBadges),
            ],
            'featured' => [
                'name' => translate('Featured'),
                'description' => translate('Active subscription required'),
                'has_subscription' => $salon->activeSubscription ? true : false,
                'has_badge' => in_array('featured', $currentBadges),
            ],
            'verified' => [
                'name' => translate('Verified'),
                'description' => translate('Manual admin approval'),
                'is_verified' => $salon->is_verified ?? false,
                'has_badge' => in_array('verified', $currentBadges),
            ],
        ];

        return view('beautybooking::vendor.badge.index', compact('salon', 'currentBadges', 'criteria'));
    }

    /**
     * Show badge details
     * نمایش جزئیات نشان
     *
     * @param string $badgeType
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function details(string $badgeType, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $currentBadges = $salon->badges_list ?? [];
        $hasBadge = in_array($badgeType, $currentBadges);

        // Get badge criteria
        $criteria = [
            'top_rated' => [
                'name' => translate('Top Rated'),
                'description' => translate('Rating > 4.5 AND minimum 50 bookings'),
                'current_rating' => $salon->avg_rating ?? 0,
                'required_rating' => config('beautybooking.badge.top_rated.min_rating', 4.5),
                'current_bookings' => $salon->total_bookings ?? 0,
                'required_bookings' => config('beautybooking.badge.top_rated.min_bookings', 50),
                'progress' => $this->calculateProgress($salon->avg_rating ?? 0, config('beautybooking.badge.top_rated.min_rating', 4.5), $salon->total_bookings ?? 0, config('beautybooking.badge.top_rated.min_bookings', 50)),
            ],
            'featured' => [
                'name' => translate('Featured'),
                'description' => translate('Active subscription required'),
                'has_subscription' => $salon->activeSubscription ? true : false,
            ],
            'verified' => [
                'name' => translate('Verified'),
                'description' => translate('Manual admin approval'),
                'is_verified' => $salon->is_verified ?? false,
            ],
        ];

        if (!isset($criteria[$badgeType])) {
            abort(404, translate('messages.badge_not_found'));
        }

        return view('beautybooking::vendor.badge.details', compact('salon', 'badgeType', 'hasBadge', 'criteria'));
    }

    /**
     * Calculate progress percentage
     * محاسبه درصد پیشرفت
     *
     * @param float $currentRating
     * @param float $requiredRating
     * @param int $currentBookings
     * @param int $requiredBookings
     * @return int
     */
    private function calculateProgress(float $currentRating, float $requiredRating, int $currentBookings, int $requiredBookings): int
    {
        $ratingProgress = min(100, ($currentRating / $requiredRating) * 100);
        $bookingsProgress = min(100, ($currentBookings / $requiredBookings) * 100);
        return (int) (($ratingProgress + $bookingsProgress) / 2);
    }

    /**
     * Get vendor's salon with authorization check
     * دریافت سالن فروشنده با بررسی مجوز
     *
     * @param object $vendor
     * @return BeautySalon
     */
    private function getVendorSalon(object $vendor): BeautySalon
    {
        $salon = BeautySalon::where('store_id', $vendor->store_id)->first();
        
        if (!$salon) {
            abort(403, translate('messages.salon_not_found'));
        }
        
        if ($salon->store->vendor_id !== $vendor->id) {
            abort(403, translate('messages.unauthorized_access'));
        }
        
        return $salon;
    }
}

