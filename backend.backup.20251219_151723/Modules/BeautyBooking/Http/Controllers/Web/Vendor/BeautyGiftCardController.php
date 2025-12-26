<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyGiftCardExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Gift Card Controller (Vendor)
 * کنترلر کارت هدیه (فروشنده)
 */
class BeautyGiftCardController extends Controller
{
    public function __construct(
        private BeautyGiftCard $giftCard
    ) {}

    /**
     * Index - List all gift cards
     * نمایش لیست تمام کارت‌های هدیه
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $giftCards = $this->giftCard->where('salon_id', $salon->id)
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
            ->paginate(config('default_pagination'));

        return view('beautybooking::vendor.gift-card.index', compact('giftCards', 'salon'));
    }

    /**
     * Show gift card details
     * نمایش جزئیات کارت هدیه
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function view(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $giftCard = $this->giftCard->where('salon_id', $salon->id)
            ->with(['purchaser', 'redeemer', 'salon'])
            ->findOrFail($id);

        return view('beautybooking::vendor.gift-card.view', compact('giftCard', 'salon'));
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
     * Export gift cards
     * خروجی گرفتن از کارت‌های هدیه
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $giftCards = $this->giftCard->where('salon_id', $salon->id)
            ->with(['purchaser', 'redeemer'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('code', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()
            ->get();

        $data = [
            'fileName' => 'Gift Cards',
            'data' => $giftCards,
            'search' => $request->search ?? null,
        ];

        // Use input() to properly read query parameter type
        // استفاده از input() برای خواندن صحیح پارامتر type از query string
        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautyGiftCardExport($data), 'Gift_Cards.csv');
        }
        return Excel::download(new BeautyGiftCardExport($data), 'Gift_Cards.xlsx');
    }
}

