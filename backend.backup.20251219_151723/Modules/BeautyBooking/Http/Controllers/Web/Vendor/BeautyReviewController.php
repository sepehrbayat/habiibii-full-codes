<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyReview;
use App\CentralLogics\Helpers;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyReviewExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Review Controller (Vendor)
 * کنترلر نظرات (فروشنده)
 */
class BeautyReviewController extends Controller
{
    public function __construct(
        private BeautyReview $review
    ) {}

    /**
     * List all reviews for vendor's salon
     * لیست تمام نظرات سالن فروشنده
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $reviews = $this->review->where('salon_id', $salon->id)
            ->with(['user', 'salon', 'service', 'booking'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhereHas('salon', function($q) use ($key) {
                        $q->where('name', 'LIKE', '%' . $key . '%');
                    })->orWhereHas('service', function($q) use ($key) {
                        $q->where('name', 'LIKE', '%' . $key . '%');
                    });
                }
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::vendor.review.list', compact('reviews', 'salon'));
    }

    /**
     * Reply to a review
     * پاسخ به نظر
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function reply(Request $request, int $id): RedirectResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'reply' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $vendor = $request->vendor;
            $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

            $review = $this->review->where('salon_id', $salon->id)->findOrFail($id);
            
            $review->update([
                'reply' => $request->reply,
                'reply_date' => now(),
            ]);

            Toastr::success(translate('messages.reply_sent_successfully'));
        } catch (Exception $e) {
            \Log::error('Review reply failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_send_reply'));
        }

        return back();
    }

    /**
     * Export reviews
     * خروجی گرفتن از نظرات
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $reviews = $this->review->where('salon_id', $salon->id)
            ->with(['user', 'salon', 'service'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhereHas('salon', function($q) use ($key) {
                        $q->where('name', 'LIKE', '%' . $key . '%');
                    });
                }
            })
            ->latest()
            ->get();

        $data = [
            'fileName' => 'Reviews',
            'data' => $reviews,
            'search' => $request->search ?? null,
        ];

        if ($request->input('export_type') == 'csv') {
            return Excel::download(new BeautyReviewExport($data), 'Reviews.csv');
        }
        return Excel::download(new BeautyReviewExport($data), 'Reviews.xlsx');
    }
}

