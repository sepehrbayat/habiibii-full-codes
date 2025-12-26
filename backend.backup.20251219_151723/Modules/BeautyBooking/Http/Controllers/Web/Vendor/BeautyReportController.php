<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyTransaction;

/**
 * Beauty Report Controller (Vendor)
 * کنترلر گزارش‌ها (فروشنده)
 */
class BeautyReportController extends Controller
{
    /**
     * Financial reports
     * گزارش‌های مالی
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function financial(Request $request)
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $query = BeautyTransaction::where('salon_id', $salon->id);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        $transactions = $query->with('booking')->latest()->paginate(config('default_pagination'));

        $totalRevenue = BeautyTransaction::where('salon_id', $salon->id)
            ->where('status', 'completed')
            ->sum('amount');

        return view('beautybooking::vendor.report.financial', compact('transactions', 'salon', 'totalRevenue'));
    }
}

