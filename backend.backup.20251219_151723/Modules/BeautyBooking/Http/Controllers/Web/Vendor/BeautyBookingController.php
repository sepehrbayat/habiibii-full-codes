<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Services\BeautyBookingService;
use Modules\BeautyBooking\Traits\BeautyPushNotification;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyBookingExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Booking Controller (Vendor)
 * کنترلر رزرو (فروشنده)
 */
class BeautyBookingController extends Controller
{
    use BeautyPushNotification;

    public function __construct(
        private BeautyBooking $booking,
        private BeautyBookingService $bookingService
    ) {}

    /**
     * Index - List all bookings
     * نمایش لیست تمام رزروها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $bookings = $this->booking->where('salon_id', $salon->id)
            ->with(['user', 'service', 'staff'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('booking_reference', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('booking_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('booking_date', '<=', $request->date_to);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::vendor.booking.index', compact('bookings', 'salon'));
    }

    /**
     * Show booking details
     * نمایش جزئیات رزرو
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function show(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);
        
        $booking = $this->booking->where('salon_id', $salon->id)
            ->with(['user', 'salon', 'service', 'staff', 'review'])
            ->findOrFail($id);

        // Authorization check: Ensure booking belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به سالن فروشنده است
        $this->authorizeBookingAccess($booking, $salon);

        return view('beautybooking::vendor.booking.show', compact('booking', 'salon'));
    }

    /**
     * Confirm booking
     * تأیید رزرو
     *
     * @param int $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function confirm(int $id, Request $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $booking = $this->booking->where('salon_id', $salon->id)->findOrFail($id);
        
        // Authorization check: Ensure booking belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به سالن فروشنده است
        $this->authorizeBookingAccess($booking, $salon);

        $this->bookingService->updateBookingStatus($booking, 'confirmed');

        // Send notification
        // ارسال نوتیفیکیشن
        self::sendBookingNotificationToAll($booking, 'booking_confirmed');

        Toastr::success(translate('messages.booking_confirmed_successfully'));
        return back();
    }

    /**
     * Mark payment as paid (for cash payments)
     * علامت‌گذاری پرداخت به عنوان پرداخت شده (برای پرداخت‌های نقدی)
     *
     * @param int $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function markPaid(int $id, Request $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $booking = $this->booking->where('salon_id', $salon->id)->findOrFail($id);
        
        // Authorization check: Ensure booking belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به سالن فروشنده است
        $this->authorizeBookingAccess($booking, $salon);
        
        // Only allow marking as paid if payment method is cash_payment
        // فقط در صورت پرداخت نقدی اجازه علامت‌گذاری به عنوان پرداخت شده
        if ($booking->payment_method !== 'cash_payment') {
            Toastr::error(translate('can_only_mark_cash_payments_as_paid'));
            return back();
        }

        $this->bookingService->updatePaymentStatus($booking, 'paid');

        // Send notification
        // ارسال نوتیفیکیشن
        self::sendBookingNotificationToAll($booking, 'payment_received');

        Toastr::success(translate('messages.payment_marked_as_paid_successfully'));
        return back();
    }

    /**
     * Mark booking as completed
     * علامت‌گذاری رزرو به عنوان تکمیل شده
     *
     * @param int $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function complete(int $id, Request $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $booking = $this->booking->where('salon_id', $salon->id)->findOrFail($id);
        
        // Authorization check: Ensure booking belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به سالن فروشنده است
        $this->authorizeBookingAccess($booking, $salon);
        
        // Only allow completing confirmed bookings
        // فقط اجازه تکمیل رزروهای تأیید شده
        if ($booking->status !== 'confirmed') {
            Toastr::error(translate('can_only_complete_confirmed_bookings'));
            return back();
        }

        $this->bookingService->updateBookingStatus($booking, 'completed');

        // Send notification
        // ارسال نوتیفیکیشن
        self::sendBookingNotificationToAll($booking, 'booking_completed');

        Toastr::success(translate('messages.booking_completed_successfully'));
        return back();
    }

    /**
     * Cancel booking
     * لغو رزرو
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $booking = $this->booking->where('salon_id', $salon->id)->findOrFail($id);
        
        // Authorization check: Ensure booking belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به سالن فروشنده است
        $this->authorizeBookingAccess($booking, $salon);

        // Vendor cancellation: no fee to customer (full refund)
        // لغو فروشنده: بدون جریمه برای مشتری (بازگشت وجه کامل)
        $this->bookingService->cancelBooking($booking, $request->cancellation_reason, 'salon');

        // Send notification
        // ارسال نوتیفیکیشن
        self::sendBookingNotificationToAll($booking, 'booking_cancelled');

        Toastr::success(translate('messages.booking_cancelled_successfully'));
        return back();
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
        
        // Authorization check: Ensure salon belongs to vendor
        // بررسی مجوز: اطمینان از اینکه سالن متعلق به فروشنده است
        if ($salon->store->vendor_id !== $vendor->id) {
            abort(403, translate('messages.unauthorized_access'));
        }
        
        return $salon;
    }

    /**
     * Authorize booking access
     * مجوز دسترسی به رزرو
     *
     * @param BeautyBooking $booking
     * @param BeautySalon $salon
     * @return void
     */
    private function authorizeBookingAccess(BeautyBooking $booking, BeautySalon $salon): void
    {
        if ($booking->salon_id !== $salon->id) {
            abort(403, translate('messages.unauthorized_access'));
        }
    }

    /**
     * Generate invoice for booking
     * تولید فاکتور برای رزرو
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function generateInvoice(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);
        
        $booking = $this->booking->where('salon_id', $salon->id)
            ->with([
                'user',
                'salon.store',
                'service.category',
                'staff',
                'package'
            ])->findOrFail($id);

        $this->authorizeBookingAccess($booking, $salon);

        return view('beautybooking::vendor.booking.invoice', compact('booking'));
    }

    /**
     * Print invoice for booking
     * چاپ فاکتور برای رزرو
     *
     * @param int $id
     * @param Request $request
     * @return string
     */
    public function printInvoice(int $id, Request $request): string
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);
        
        $booking = $this->booking->where('salon_id', $salon->id)
            ->with([
                'user',
                'salon.store',
                'service.category',
                'staff',
                'package'
            ])->findOrFail($id);

        $this->authorizeBookingAccess($booking, $salon);

        return view('beautybooking::vendor.booking.invoice-print', compact('booking'))->render();
    }

    /**
     * Export bookings
     * خروجی گرفتن از رزروها
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $bookings = $this->booking->where('salon_id', $salon->id)
            ->with(['user', 'service', 'staff'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('booking_reference', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('booking_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('booking_date', '<=', $request->date_to);
            })
            ->latest()
            ->get();

        $data = [
            'fileName' => 'Bookings',
            'data' => $bookings,
            'search' => $request->search ?? null,
        ];

        // Use input() to properly read query parameter type
        // استفاده از input() برای خواندن صحیح پارامتر type از query string
        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautyBookingExport($data), 'Bookings.csv');
        }
        return Excel::download(new BeautyBookingExport($data), 'Bookings.xlsx');
    }
}

