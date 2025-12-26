<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign;
use Modules\BeautyBooking\Entities\BeautyLoyaltyPoint;
use Modules\BeautyBooking\Entities\BeautySalon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyLoyaltyExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Loyalty Controller (Admin)
 * کنترلر وفاداری زیبایی (ادمین)
 */
class BeautyLoyaltyController extends Controller
{
    public function __construct(
        private BeautyLoyaltyCampaign $campaign,
        private BeautyLoyaltyPoint $loyaltyPoint
    ) {}

    /**
     * List all loyalty campaigns
     * لیست تمام کمپین‌های وفاداری
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        $campaigns = $this->campaign->with(['salon.store'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->where('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->salon_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->active();
                }
            })
            ->latest()
            ->paginate(config('default_pagination'));

        // Get statistics
        // دریافت آمار
        $totalPointsIssued = $this->loyaltyPoint->where('type', 'earned')->sum('points');
        $totalPointsRedeemed = $this->loyaltyPoint->where('type', 'redeemed')->sum('points');
        $activeCampaigns = $this->campaign->active()->count();

        $salons = BeautySalon::with('store')->get();

        return view('beautybooking::admin.loyalty.index', compact(
            'campaigns',
            'totalPointsIssued',
            'totalPointsRedeemed',
            'activeCampaigns',
            'salons'
        ));
    }

    /**
     * Export loyalty campaigns
     * خروجی گرفتن از کمپین‌های وفاداری
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $campaigns = $this->campaign->with(['salon.store'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->where('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->salon_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->active();
                }
            })
            ->latest()
            ->get();

        $data = [
            'fileName' => 'Loyalty Campaigns',
            'data' => $campaigns,
            'search' => $request->search ?? null,
        ];

        // Use input() to properly read query parameter type
        // استفاده از input() برای خواندن صحیح پارامتر type از query string
        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautyLoyaltyExport($data), 'Loyalty_Campaigns.csv');
        }
        return Excel::download(new BeautyLoyaltyExport($data), 'Loyalty_Campaigns.xlsx');
    }

    /**
     * Toggle loyalty campaign active status
     * تغییر وضعیت فعال کمپین وفاداری
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function status(int $id): RedirectResponse
    {
        $campaign = $this->campaign->findOrFail($id);
        $campaign->update(['is_active' => !$campaign->is_active]);
        
        Toastr::success(translate('messages.status_updated_successfully'));
        return back();
    }
}

