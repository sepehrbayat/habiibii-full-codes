<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\CentralLogics\Helpers;

/**
 * Beauty Package Controller (Vendor API)
 * کنترلر پکیج (API فروشنده)
 *
 * Handles package management for vendors
 * مدیریت پکیج برای فروشندگان
 */
class BeautyPackageController extends Controller
{
    use BeautyApiResponse;

    /**
     * List packages
     * لیست پکیج‌ها
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

            $packages = BeautyPackage::where('salon_id', $salon->id)
                ->with('service')
                ->when($request->has('search'), function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->orWhere('name', 'LIKE', '%' . $key . '%');
                    }
                })
                ->latest()
                ->paginate($request->get('per_page', 15));

            return $this->listResponse($packages, 'messages.data_retrieved_successfully');
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'package', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Get package usage stats
     * دریافت آمار استفاده از پکیج
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function usageStats(Request $request): JsonResponse
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

            $packageId = $request->get('package_id');
            if (!$packageId) {
                return $this->validationErrorResponse(
                    Validator::make(['package_id' => null], ['package_id' => 'required'])
                );
            }

            $package = BeautyPackage::where('id', $packageId)
                ->where('salon_id', $salon->id)
                ->firstOrFail();

            $totalRedemptions = $package->usages()->where('status', 'used')->count();
            $remainingSessions = $package->sessions_count - $totalRedemptions;

            return $this->successResponse(
                'messages.data_retrieved_successfully',
                [
                    'package' => $package,
                    'total_redemptions' => $totalRedemptions,
                    'remaining_sessions' => $remainingSessions,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'package', 'message' => $e->getMessage()],
            ], 500);
        }
    }
}

