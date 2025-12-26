<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use Modules\BeautyBooking\Entities\BeautySalon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyGiftCardExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Gift Card Controller (Admin)
 * کنترلر کارت هدیه زیبایی (ادمین)
 */
class BeautyGiftCardController extends Controller
{
    public function __construct(
        private BeautyGiftCard $giftCard
    ) {}

    /**
     * List all gift cards
     * لیست تمام کارت‌های هدیه
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        $giftCards = $this->giftCard->with(['salon.store', 'purchaser', 'redeemer'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->where(function($q) use ($key) {
                        $q->where('code', 'LIKE', '%' . $key . '%')
                          ->orWhereHas('purchaser', function($userQuery) use ($key) {
                              $userQuery->where('f_name', 'LIKE', '%' . $key . '%')
                                       ->orWhere('l_name', 'LIKE', '%' . $key . '%')
                                       ->orWhere('phone', 'LIKE', '%' . $key . '%');
                          });
                    });
                }
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->salon_id);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $salons = BeautySalon::with('store')->get();

        return view('beautybooking::admin.gift-card.index', compact('giftCards', 'salons'));
    }

    /**
     * View gift card details
     * مشاهده جزئیات کارت هدیه
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function view(int $id)
    {
        $giftCard = $this->giftCard->with(['salon.store', 'purchaser', 'redeemer'])->findOrFail($id);
        return view('beautybooking::admin.gift-card.view', compact('giftCard'));
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
        $giftCards = $this->giftCard->with(['salon.store', 'purchaser', 'redeemer'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->where(function($q) use ($key) {
                        $q->where('code', 'LIKE', '%' . $key . '%')
                          ->orWhereHas('purchaser', function($userQuery) use ($key) {
                              $userQuery->where('f_name', 'LIKE', '%' . $key . '%')
                                       ->orWhere('l_name', 'LIKE', '%' . $key . '%')
                                       ->orWhere('phone', 'LIKE', '%' . $key . '%');
                          });
                    });
                }
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->salon_id);
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

