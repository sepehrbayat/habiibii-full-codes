<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyTransaction;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\CentralLogics\Helpers;
use Carbon\Carbon;

/**
 * Beauty Finance Controller (Vendor API)
 * کنترلر مالی (API فروشنده)
 *
 * Handles financial reports and payout summaries for vendors
 * مدیریت گزارش‌های مالی و خلاصه پرداخت‌ها برای فروشندگان
 */
class BeautyFinanceController extends Controller
{
    use BeautyApiResponse;

    public function __construct(
        private BeautyTransaction $transaction
    ) {}

    /**
     * Get payout summary
     * دریافت خلاصه پرداخت
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function payoutSummary(Request $request): JsonResponse
    {
        try {
            $vendor = $request->vendor;
            $salon = BeautySalon::firstOrCreate(
                ['store_id' => $vendor->store_id],
                [
                    'zone_id' => $vendor->store->zone_id ?? null,
                    'business_type' => 'salon',
                    'verification_status' => 0,
                    'is_verified' => false,
                ]
            );

            // Calculate totals
            // محاسبه مجموع‌ها
            $totalRevenue = $this->transaction->where('salon_id', $salon->id)
                ->where('transaction_type', '!=', 'refund')
                ->sum('amount');
            
            $totalCommission = $this->transaction->where('salon_id', $salon->id)
                ->sum('commission');
            
            $totalServiceFee = $this->transaction->where('salon_id', $salon->id)
                ->sum('service_fee');
            
            // Ensure values are numeric before calculations (PHP 8.4+ requires int|float, not string)
            // اطمینان از عددی بودن مقادیر قبل از محاسبات (PHP 8.4+ نیاز به int|float دارد، نه string)
            $totalRevenue = (float)($totalRevenue ?? 0);
            $totalCommission = (float)($totalCommission ?? 0);
            $totalServiceFee = (float)($totalServiceFee ?? 0);
            
            $netPayout = $totalRevenue - $totalCommission - $totalServiceFee;

            // Revenue breakdown by model
            // تفکیک درآمد بر اساس مدل
            $revenueBreakdown = $this->transaction->where('salon_id', $salon->id)
                ->selectRaw('transaction_type, SUM(amount) as total')
                ->groupBy('transaction_type')
                ->get()
                ->pluck('total', 'transaction_type')
                ->map(function ($value) {
                    return (float)($value ?? 0);
                });

            return $this->successResponse(
                'messages.data_retrieved_successfully',
                [
                    'total_revenue' => round($totalRevenue, 2),
                    'total_commission' => round($totalCommission, 2),
                    'total_service_fee' => round($totalServiceFee, 2),
                    'net_payout' => round($netPayout, 2),
                    'revenue_breakdown' => $revenueBreakdown,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'finance', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Get transaction history
     * دریافت تاریخچه تراکنش‌ها
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function transactionHistory(Request $request): JsonResponse
    {
        try {
            $vendor = $request->vendor;
            $salon = BeautySalon::firstOrCreate(
                ['store_id' => $vendor->store_id],
                [
                    'zone_id' => $vendor->store->zone_id ?? null,
                    'business_type' => 'salon',
                    'verification_status' => 0,
                    'is_verified' => false,
                ]
            );

            $transactions = $this->transaction->where('salon_id', $salon->id)
                ->when($request->filled('date_from'), function ($query) use ($request) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                })
                ->when($request->filled('date_to'), function ($query) use ($request) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                })
                ->when($request->filled('transaction_type'), function ($query) use ($request) {
                    $query->where('transaction_type', $request->transaction_type);
                })
                ->latest()
                ->paginate($request->get('per_page', 15));

            return $this->listResponse($transactions, 'messages.data_retrieved_successfully');
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'finance', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Get vendor salon
     * دریافت سالن فروشنده
     *
     * @param mixed $vendor
     * @return BeautySalon|null
     */
    private function getVendorSalon($vendor): ?BeautySalon
    {
        $storeId = Helpers::get_store_id();
        return BeautySalon::where('store_id', $storeId)->first();
    }
}

