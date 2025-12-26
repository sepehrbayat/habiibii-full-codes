<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\CentralLogics\Helpers;

/**
 * Beauty Badge Controller (Vendor API)
 * کنترلر نشان (API فروشنده)
 *
 * Handles badge status and criteria for vendors
 * مدیریت وضعیت و معیارهای نشان برای فروشندگان
 */
class BeautyBadgeController extends Controller
{
    use BeautyApiResponse;

    /**
     * Get badge status
     * دریافت وضعیت نشان‌ها
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $vendor = $request->vendor;
            $storeId = Helpers::get_store_id() ?? $vendor->store_id;
            $salon = BeautySalon::firstOrCreate(
                ['store_id' => $storeId],
                [
                    'zone_id' => $vendor->store->zone_id ?? null,
                    'business_type' => 'salon',
                    'verification_status' => 0,
                    'is_verified' => false,
                ]
            );

            if (!$salon) {
                return $this->errorResponse([
                    ['code' => 'salon', 'message' => translate('messages.salon_not_found')]
                ], 404);
            }

            $currentBadges = $salon->badges_list ?? [];

            $criteria = [
                'top_rated' => [
                    'name' => translate('Top Rated'),
                    'current_rating' => $salon->avg_rating ?? 0,
                    'required_rating' => config('beautybooking.badge.top_rated.min_rating', 4.8),
                    'current_bookings' => $salon->total_bookings ?? 0,
                    'required_bookings' => config('beautybooking.badge.top_rated.min_bookings', 50),
                    'has_badge' => in_array('top_rated', $currentBadges),
                ],
                'featured' => [
                    'name' => translate('Featured'),
                    'has_subscription' => $salon->activeSubscription !== null,
                    'has_badge' => in_array('featured', $currentBadges),
                ],
                'verified' => [
                    'name' => translate('Verified'),
                    'is_verified' => $salon->is_verified ?? false,
                    'has_badge' => in_array('verified', $currentBadges),
                ],
            ];

            return $this->successResponse(
                'messages.data_retrieved_successfully',
                [
                    'current_badges' => $currentBadges,
                    'criteria' => $criteria,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'badge', 'message' => $e->getMessage()],
            ], 500);
        }
    }
}

