<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign;
use Modules\BeautyBooking\Entities\BeautyLoyaltyPoint;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\CentralLogics\Helpers;

/**
 * Beauty Loyalty Controller (Vendor API)
 * کنترلر وفاداری (API فروشنده)
 *
 * Handles loyalty campaign management for vendors
 * مدیریت کمپین‌های وفاداری برای فروشندگان
 */
class BeautyLoyaltyController extends Controller
{
    use BeautyApiResponse;

    /**
     * List loyalty campaigns
     * لیست کمپین‌های وفاداری
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listCampaigns(Request $request): JsonResponse
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

            $campaigns = BeautyLoyaltyCampaign::where('salon_id', $salon->id)
                ->when($request->has('search'), function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->orWhere('name', 'LIKE', '%' . $key . '%');
                    }
                })
                ->when($request->filled('status'), function ($query) use ($request) {
                    $query->where('is_active', $request->status == 'active');
                })
                ->latest()
                ->paginate($request->get('per_page', 15));

            return $this->listResponse($campaigns, 'messages.data_retrieved_successfully');
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'loyalty', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Get points issuance history
     * دریافت تاریخچه صدور امتیازها
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pointsHistory(Request $request): JsonResponse
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

            $points = BeautyLoyaltyPoint::whereHas('booking', function($q) use ($salon) {
                    $q->where('salon_id', $salon->id);
                })
                ->with(['user', 'booking', 'campaign'])
                ->when($request->filled('date_from'), function ($query) use ($request) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                })
                ->when($request->filled('date_to'), function ($query) use ($request) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                })
                ->latest()
                ->paginate($request->get('per_page', 15));

            return $this->listResponse($points, 'messages.data_retrieved_successfully');
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'loyalty', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Get campaign performance stats
     * دریافت آمار عملکرد کمپین
     *
     * @param Request $request
     * @param int $campaignId
     * @return JsonResponse
     */
    public function campaignStats(Request $request, int $campaignId): JsonResponse
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

            $campaign = BeautyLoyaltyCampaign::where('id', $campaignId)
                ->where('salon_id', $salon->id)
                ->firstOrFail();

            $totalPointsIssued = BeautyLoyaltyPoint::where('campaign_id', $campaignId)
                ->where('type', 'earned')
                ->sum('points');

            $totalPointsRedeemed = abs(BeautyLoyaltyPoint::where('campaign_id', $campaignId)
                ->where('type', 'redeemed')
                ->sum('points'));

            $totalUsers = BeautyLoyaltyPoint::where('campaign_id', $campaignId)
                ->where('type', 'earned')
                ->distinct('user_id')
                ->count('user_id');

            return $this->successResponse(
                'messages.data_retrieved_successfully',
                [
                    'campaign' => $campaign,
                    'total_points_issued' => $totalPointsIssued,
                    'total_points_redeemed' => $totalPointsRedeemed,
                    'total_users' => $totalUsers,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'loyalty', 'message' => $e->getMessage()],
            ], 500);
        }
    }
}

