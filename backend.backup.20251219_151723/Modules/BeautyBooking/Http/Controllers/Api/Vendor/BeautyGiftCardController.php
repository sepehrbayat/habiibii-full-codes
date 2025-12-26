<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\CentralLogics\Helpers;

/**
 * Beauty Gift Card Controller (Vendor API)
 * کنترلر کارت هدیه (API فروشنده)
 *
 * Handles gift card tracking for vendors
 * مدیریت ردیابی کارت هدیه برای فروشندگان
 */
class BeautyGiftCardController extends Controller
{
    use BeautyApiResponse;

    /**
     * List gift cards
     * لیست کارت‌های هدیه
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
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

            $giftCards = BeautyGiftCard::where('salon_id', $salon->id)
                ->with(['purchaser', 'redeemer'])
                ->when($request->has('search'), function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->orWhere('code', 'LIKE', '%' . $key . '%');
                    }
                })
                ->when($request->filled('status'), function ($query) use ($request) {
                    $query->where('status', $request->status);
                })
                ->latest()
                ->paginate($request->get('per_page', 15));

            return $this->listResponse($giftCards, 'messages.data_retrieved_successfully');
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'gift_card', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Get redemption history
     * دریافت تاریخچه استفاده
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function redemptionHistory(Request $request): JsonResponse
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

            $giftCards = BeautyGiftCard::where('salon_id', $salon->id)
                ->where('status', 'redeemed')
                ->with(['purchaser', 'redeemer'])
                ->when($request->filled('date_from'), function ($query) use ($request) {
                    $query->whereDate('redeemed_at', '>=', $request->date_from);
                })
                ->when($request->filled('date_to'), function ($query) use ($request) {
                    $query->whereDate('redeemed_at', '<=', $request->date_to);
                })
                ->latest('redeemed_at')
                ->paginate($request->get('per_page', 15));

            return $this->listResponse($giftCards, 'messages.data_retrieved_successfully');
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'gift_card', 'message' => $e->getMessage()],
            ], 500);
        }
    }
}

