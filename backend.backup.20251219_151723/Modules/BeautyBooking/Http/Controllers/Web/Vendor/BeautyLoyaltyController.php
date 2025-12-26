<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign;
use Modules\BeautyBooking\Http\Requests\BeautyLoyaltyCampaignStoreRequest;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyLoyaltyExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Loyalty Controller (Vendor)
 * کنترلر وفاداری (فروشنده)
 */
class BeautyLoyaltyController extends Controller
{
    public function __construct(
        private BeautyLoyaltyCampaign $campaign
    ) {}

    /**
     * Index - List all loyalty campaigns
     * نمایش لیست تمام کمپین‌های وفاداری
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $campaigns = $this->campaign->where('salon_id', $salon->id)
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
            ->paginate(config('default_pagination'));

        // Statistics
        $totalPointsIssued = $campaigns->sum('total_points_issued');
        $totalParticipants = $campaigns->sum('total_participants');

        return view('beautybooking::vendor.loyalty.index', compact('campaigns', 'salon', 'totalPointsIssued', 'totalParticipants'));
    }

    /**
     * Show create form
     * نمایش فرم ایجاد کمپین
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        return view('beautybooking::vendor.loyalty.create', compact('salon'));
    }

    /**
     * Store new campaign
     * ذخیره کمپین جدید
     *
     * @param BeautyLoyaltyCampaignStoreRequest $request
     * @return RedirectResponse
     */
    public function store(BeautyLoyaltyCampaignStoreRequest $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        try {
            $this->campaign->create([
                'salon_id' => $salon->id,
                'name' => $request->name,
                'type' => $request->type,
                'points_value' => $request->points_value,
                'start_date' => Carbon::parse($request->start_date),
                'end_date' => $request->end_date ? Carbon::parse($request->end_date) : null,
                'is_active' => $request->is_active,
            ]);

            Toastr::success(translate('messages.campaign_created_successfully'));
            return redirect()->route('vendor.beautybooking.loyalty.index');
        } catch (Exception $e) {
            \Log::error('Campaign creation failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_create_campaign'));
        }

        return back();
    }

    /**
     * Show edit form
     * نمایش فرم ویرایش کمپین
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $campaign = $this->campaign->where('salon_id', $salon->id)->findOrFail($id);

        return view('beautybooking::vendor.loyalty.edit', compact('campaign', 'salon'));
    }

    /**
     * Update campaign
     * به‌روزرسانی کمپین
     *
     * @param int $id
     * @param BeautyLoyaltyCampaignStoreRequest $request
     * @return RedirectResponse
     */
    public function update(int $id, BeautyLoyaltyCampaignStoreRequest $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $campaign = $this->campaign->where('salon_id', $salon->id)->findOrFail($id);

        try {
            $campaign->update([
                'name' => $request->name,
                'type' => $request->type,
                'points_value' => $request->points_value,
                'start_date' => Carbon::parse($request->start_date),
                'end_date' => $request->end_date ? Carbon::parse($request->end_date) : null,
                'is_active' => $request->is_active,
            ]);

            Toastr::success(translate('messages.campaign_updated_successfully'));
            return redirect()->route('vendor.beautybooking.loyalty.index');
        } catch (Exception $e) {
            \Log::error('Campaign update failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_update_campaign'));
        }

        return back();
    }

    /**
     * Show campaign details
     * نمایش جزئیات کمپین
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function view(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $campaign = $this->campaign->where('salon_id', $salon->id)
            ->with(['loyaltyPoints.user'])
            ->findOrFail($id);

        return view('beautybooking::vendor.loyalty.view', compact('campaign', 'salon'));
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
     * Export loyalty campaigns
     * خروجی گرفتن از کمپین‌های وفاداری
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $campaigns = $this->campaign->where('salon_id', $salon->id)
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
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
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(int $id, Request $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $campaign = $this->campaign->where('salon_id', $salon->id)->findOrFail($id);
        $campaign->update(['is_active' => !$campaign->is_active]);
        
        Toastr::success(translate('messages.status_updated_successfully'));
        return back();
    }
}

