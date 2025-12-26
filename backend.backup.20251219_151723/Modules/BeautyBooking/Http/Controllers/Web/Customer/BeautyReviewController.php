<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyReview;
use Modules\BeautyBooking\Http\Requests\BeautyReviewStoreRequest;
use App\CentralLogics\Helpers;
use Brian2694\Toastr\Facades\Toastr;

/**
 * Beauty Review Controller (Customer Web)
 * کنترلر نظرات (وب مشتری)
 */
class BeautyReviewController extends Controller
{
    public function __construct(
        private BeautyReview $review
    ) {}

    /**
     * Show review creation form
     * نمایش فرم ایجاد نظر
     *
     * @param int $bookingId
     * @return View|RedirectResponse
     */
    public function create(int $bookingId): View|RedirectResponse
    {
        $booking = BeautyBooking::where('user_id', auth('customer')->id())
            ->with(['salon.store', 'service', 'staff'])
            ->findOrFail($bookingId);
        
        // Authorization check: Ensure booking belongs to current user
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به کاربر فعلی است
        if ($booking->user_id !== auth('customer')->id()) {
            Toastr::error(translate('messages.unauthorized_access'));
            return redirect()->route('beauty-booking.my-bookings.index');
        }

        // Check if booking is completed
        // بررسی تکمیل بودن رزرو
        if ($booking->status !== 'completed') {
            Toastr::error(translate('can_only_review_completed_bookings'));
            return redirect()->route('beauty-booking.my-bookings.show', $bookingId);
        }

        // Check if already reviewed
        // بررسی وجود نظر قبلی
        if ($booking->review) {
            Toastr::error(translate('booking_already_reviewed'));
            return redirect()->route('beauty-booking.my-bookings.show', $bookingId);
        }

        return view('beautybooking::customer.reviews.create', compact('booking'));
    }

    /**
     * Store new review
     * ذخیره نظر جدید
     *
     * @param BeautyReviewStoreRequest $request
     * @return RedirectResponse
     */
    public function store(BeautyReviewStoreRequest $request): RedirectResponse
    {
        $booking = BeautyBooking::where('user_id', auth('customer')->id())
            ->findOrFail($request->booking_id);
        
        // Authorization check: Ensure booking belongs to current user
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به کاربر فعلی است
        if ($booking->user_id !== auth('customer')->id()) {
            Toastr::error(translate('messages.unauthorized_access'));
            return redirect()->route('beauty-booking.my-bookings.index');
        }

        // Check if booking is completed
        // بررسی تکمیل بودن رزرو
        if ($booking->status !== 'completed') {
            Toastr::error(translate('can_only_review_completed_bookings'));
            return redirect()->route('beauty-booking.my-bookings.show', $booking->id);
        }

        // Check if already reviewed
        // بررسی وجود نظر قبلی
        if ($booking->review) {
            Toastr::error(translate('booking_already_reviewed'));
            return redirect()->route('beauty-booking.my-bookings.show', $booking->id);
        }

        try {
            // Upload attachments
            // آپلود فایل‌های ضمیمه
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    // Get actual file extension from uploaded file
                    // دریافت پسوند واقعی فایل از فایل آپلود شده
                    $extension = $file->getClientOriginalExtension() ?: 'png';
                    $attachments[] = Helpers::upload('beauty/reviews/', $extension, $file);
                }
            }

            // Check if auto-publish is enabled
            // بررسی فعال بودن auto-publish
            $autoPublish = config('beautybooking.review.auto_publish', false);
            $status = $autoPublish ? 'approved' : 'pending';

            $review = $this->review->create([
                'booking_id' => $booking->id,
                'user_id' => auth('customer')->id(),
                'salon_id' => $booking->salon_id,
                'service_id' => $booking->service_id,
                'staff_id' => $booking->staff_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'attachments' => $attachments,
                'status' => $status,
            ]);

            // If auto-publish, update salon rating statistics immediately
            // در صورت auto-publish، فوراً آمار رتبه‌بندی سالن را به‌روزرسانی کنید
            if ($autoPublish) {
                $salonService = app(\Modules\BeautyBooking\Services\BeautySalonService::class);
                $salonService->updateRatingStatistics($booking->salon_id);
            }

            Toastr::success(translate('review_submitted_successfully'));
            return redirect()->route('beauty-booking.my-bookings.show', $booking->id);
        } catch (\Exception $e) {
            Toastr::error(translate('failed_to_submit_review'));
            return redirect()->back()->withInput();
        }
    }
}

