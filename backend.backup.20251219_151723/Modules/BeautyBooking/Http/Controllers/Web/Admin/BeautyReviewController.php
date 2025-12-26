<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautyReview;
use Modules\BeautyBooking\Services\BeautySalonService;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyReviewExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Review Controller (Admin)
 * کنترلر نظرات (ادمین)
 */
class BeautyReviewController extends Controller
{
    public function __construct(
        private BeautyReview $review,
        private BeautySalonService $salonService
    ) {}

    /**
     * List all reviews with filters
     * لیست تمام نظرات با فیلترها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        $reviews = $this->review->with(['user', 'salon', 'service', 'staff', 'booking'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('comment', 'LIKE', '%' . $key . '%');
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

        return view('beautybooking::admin.review.index', compact('reviews'));
    }

    /**
     * View review details
     * مشاهده جزئیات نظر
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function view(int $id)
    {
        $review = $this->review->with([
            'user',
            'salon.store',
            'service.category',
            'staff',
            'booking'
        ])->findOrFail($id);

        return view('beautybooking::admin.review.view', compact('review'));
    }

    /**
     * Approve review
     * تأیید نظر
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function approve(int $id): RedirectResponse
    {
        $review = $this->review->findOrFail($id);
        $review->update(['status' => 'approved']);

        // Update salon rating statistics and recalculate badges
        // به‌روزرسانی آمار رتبه‌بندی سالن و محاسبه مجدد نشان‌ها
        $this->salonService->updateRatingStatistics($review->salon_id);

        Toastr::success(translate('messages.review_approved_successfully'));
        return back();
    }

    /**
     * Reject review
     * رد نظر
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $review = $this->review->findOrFail($id);
        $review->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        Toastr::success(translate('messages.review_rejected_successfully'));
        return back();
    }

    /**
     * Delete review
     * حذف نظر
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $review = $this->review->findOrFail($id);
        $salonId = $review->salon_id;
        $review->delete();

        // Update salon rating statistics after deletion
        // به‌روزرسانی آمار رتبه‌بندی سالن پس از حذف
        $this->salonService->updateRatingStatistics($salonId);

        Toastr::success(translate('messages.review_deleted_successfully'));
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
        $reviews = $this->review->with(['user', 'salon', 'service', 'staff', 'booking'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('comment', 'LIKE', '%' . $key . '%');
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
            'fileName' => 'Reviews',
            'data' => $reviews,
            'search' => $request->search ?? null,
        ];

        // Use input() to properly read query parameter type
        // استفاده از input() برای خواندن صحیح پارامتر type از query string
        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautyReviewExport($data), 'Reviews.csv');
        }
        return Excel::download(new BeautyReviewExport($data), 'Reviews.xlsx');
    }
}

