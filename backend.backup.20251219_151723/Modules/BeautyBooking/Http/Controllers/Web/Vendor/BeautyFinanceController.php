<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyTransaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyTransactionExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Finance Controller (Vendor)
 * کنترلر مالی (فروشنده)
 */
class BeautyFinanceController extends Controller
{
    public function __construct(
        private BeautyTransaction $transaction
    ) {}

    /**
     * Index - Payout summaries
     * خلاصه پرداخت‌ها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        // Get transactions for the salon
        // دریافت تراکنش‌های سالن
        $transactions = $this->transaction->where('salon_id', $salon->id)
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        // Calculate totals
        // محاسبه مجموع‌ها
        $totalRevenue = $this->transaction->where('salon_id', $salon->id)->sum('amount');
        $totalCommission = $this->transaction->where('salon_id', $salon->id)->sum('commission');
        $totalServiceFee = $this->transaction->where('salon_id', $salon->id)->sum('service_fee');
        $netPayout = $totalRevenue - $totalCommission;

        // Monthly breakdown for chart
        // تقسیم‌بندی ماهانه برای نمودار
        $labels = [];
        $revenueData = [];
        $commissionData = [];
        $payoutData = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $monthTransactions = $this->transaction->where('salon_id', $salon->id)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->get();

            $monthRevenue = $monthTransactions->sum('amount');
            $monthCommission = $monthTransactions->sum('commission');
            
            $revenueData[] = round($monthRevenue, 2);
            $commissionData[] = round($monthCommission, 2);
            $payoutData[] = round($monthRevenue - $monthCommission, 2);
        }

        return view('beautybooking::vendor.finance.index', compact(
            'salon',
            'transactions',
            'totalRevenue',
            'totalCommission',
            'totalServiceFee',
            'netPayout',
            'labels',
            'revenueData',
            'commissionData',
            'payoutData'
        ));
    }

    /**
     * Show transaction details
     * نمایش جزئیات تراکنش
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function details(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $transaction = $this->transaction->where('salon_id', $salon->id)
            ->with(['booking', 'salon'])
            ->findOrFail($id);

        return view('beautybooking::vendor.finance.details', compact('transaction', 'salon'));
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

    /**
     * Export financial transactions
     * خروجی گرفتن از تراکنش‌های مالی
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $transactions = $this->transaction->where('salon_id', $salon->id)
            ->with(['booking', 'salon'])
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
            ->get();

        $data = [
            'fileName' => 'Financial Transactions',
            'data' => $transactions,
            'search' => $request->search ?? null,
        ];

        // Use input() to properly read query parameter type
        // استفاده از input() برای خواندن صحیح پارامتر type از query string
        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautyTransactionExport($data), 'Financial_Transactions.csv');
        }
        return Excel::download(new BeautyTransactionExport($data), 'Financial_Transactions.xlsx');
    }
}

