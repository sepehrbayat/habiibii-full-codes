<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Services\BeautyBookingService;
use Modules\BeautyBooking\Traits\BeautyPushNotification;
use Modules\BeautyBooking\Traits\BeautyApiResponse;

/**
 * Beauty Booking Controller (Vendor API)
 * کنترلر رزرو (API فروشنده)
 */
class BeautyBookingController extends Controller
{
    use BeautyPushNotification, BeautyApiResponse;

    public function __construct(
        private BeautyBooking $booking,
        private BeautyBookingService $bookingService
    ) {}

    /**
     * List vendor bookings
     * لیست رزروهای فروشنده
     *
     * @param Request $request
     * @param string $all Status filter or "all" for all bookings. Example: "confirmed" or "all"
     * @return JsonResponse
     * 
     * @queryParam all string Status filter (pending/confirmed/completed/cancelled) or "all". Example: all
     * @queryParam status string Alternative status filter. Example: confirmed
     * @queryParam date_from date Filter bookings from this date. Example: 2024-01-01
     * @queryParam date_to date Filter bookings to this date. Example: 2024-01-31
     * @queryParam limit integer Items per page (default: 25). Example: 25
     * @queryParam offset integer Page offset (default: 0). Example: 0
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 100001,
     *       "booking_reference": "BB-100001",
     *       "user": {...},
     *       "service": {...},
     *       "staff": {...},
     *       "status": "confirmed",
     *       "booking_date": "2024-01-20",
     *       "booking_time": "10:00"
     *     }
     *   ],
     *   "total": 50,
     *   "per_page": 25,
     *   "current_page": 1,
     *   "last_page": 2
     * }
     */
    public function list(Request $request, string $all = 'all'): JsonResponse
    {
        $limit = $request->get('limit', 25);
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        // Fixed: paginate() expects page number (1, 2, 3...), not offset
        // اصلاح شده: paginate() شماره صفحه را انتظار دارد (1, 2, 3...)، نه offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $bookings = $this->booking->where('salon_id', $salon->id)
            ->with(['user', 'service', 'staff'])
            ->when($all !== 'all', function ($query) use ($all) {
                $query->where('status', $all);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('booking_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('booking_date', '<=', $request->date_to);
            })
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return $this->listResponse($bookings);
    }

    /**
     * Get booking details
     * دریافت جزئیات رزرو
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @queryParam booking_id integer required Booking ID. Example: 100001
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 100001,
     *     "booking_reference": "BB-100001",
     *     "user": {...},
     *     "salon": {...},
     *     "service": {...},
     *     "staff": {...},
     *     "status": "confirmed",
     *     "payment_status": "paid",
     *     "booking_date": "2024-01-20",
     *     "booking_time": "10:00",
     *     "total_amount": 100000
     *   }
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "validation",
     *       "message": "The booking_id field is required."
     *     }
     *   ]
     * }
     * 
     * @response 404 {
     *   "errors": [
     *     {
     *       "code": "not_found",
     *       "message": "Booking not found"
     *     }
     *   ]
     * }
     */
    public function details(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'booking_id' => 'required|integer|exists:beauty_bookings,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $booking = $this->booking->where('salon_id', $salon->id)
            ->with(['user', 'salon.store', 'service', 'staff', 'review'])
            ->findOrFail($request->booking_id);

        // Authorization check: Ensure booking belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به سالن فروشنده است
        $this->authorizeBookingAccess($booking, $salon);

        return $this->successResponse('messages.data_retrieved_successfully', $booking);
    }

    /**
     * Confirm booking
     * تأیید رزرو
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @bodyParam booking_id integer required Booking ID to confirm. Example: 100001
     * 
     * @response 200 {
     *   "message": "Booking confirmed successfully",
     *   "data": {
     *     "id": 100001,
     *     "booking_reference": "BB-100001",
     *     "status": "confirmed",
     *     "confirmed_at": "2024-01-15 10:00:00"
     *   }
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "validation",
     *       "message": "The booking_id field is required."
     *     },
     *     {
     *       "code": "booking",
     *       "message": "Booking cannot be confirmed. Current status: completed"
     *     }
     *   ]
     * }
     */
    public function confirm(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'booking_id' => 'required|integer|exists:beauty_bookings,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $booking = $this->booking->where('salon_id', $salon->id)->findOrFail($request->booking_id);
        
        // Authorization check: Ensure booking belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به سالن فروشنده است
        $this->authorizeBookingAccess($booking, $salon);

        try {
            $booking = $this->bookingService->updateBookingStatus($booking, 'confirmed');

            // Send notification
            // ارسال نوتیفیکیشن
            self::sendBookingNotificationToAll($booking, 'booking_confirmed');

            return $this->successResponse('booking_confirmed_successfully', $booking);
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'booking', 'message' => $e->getMessage()],
            ]);
        }
    }

    /**
     * Mark payment as paid (for cash payments)
     * علامت‌گذاری پرداخت به عنوان پرداخت شده (برای پرداخت‌های نقدی)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function markPaid(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'booking_id' => 'required|integer|exists:beauty_bookings,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $booking = $this->booking->where('salon_id', $salon->id)->findOrFail($request->booking_id);
        
        // Authorization check: Ensure booking belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به سالن فروشنده است
        $this->authorizeBookingAccess($booking, $salon);
        
        // Only allow marking as paid if payment method is cash_payment
        // فقط در صورت پرداخت نقدی اجازه علامت‌گذاری به عنوان پرداخت شده
        if ($booking->payment_method !== 'cash_payment') {
            return $this->errorResponse([
                ['code' => 'payment', 'message' => translate('can_only_mark_cash_payments_as_paid')],
            ]);
        }

        try {
            $booking = $this->bookingService->updatePaymentStatus($booking, 'paid');

            // Send notification
            // ارسال نوتیفیکیشن
            self::sendBookingNotificationToAll($booking, 'payment_received');

            return $this->successResponse('payment_marked_as_paid_successfully', $booking);
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'payment', 'message' => $e->getMessage()],
            ]);
        }
    }

    /**
     * Mark booking as completed
     * علامت‌گذاری رزرو به عنوان تکمیل شده
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function complete(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'booking_id' => 'required|integer|exists:beauty_bookings,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $booking = $this->booking->where('salon_id', $salon->id)->findOrFail($request->booking_id);
        
        // Authorization check: Ensure booking belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به سالن فروشنده است
        $this->authorizeBookingAccess($booking, $salon);
        
        // Only allow completing confirmed bookings
        // فقط اجازه تکمیل رزروهای تأیید شده
        if ($booking->status !== 'confirmed') {
            return $this->errorResponse([
                ['code' => 'booking', 'message' => translate('can_only_complete_confirmed_bookings')],
            ]);
        }

        try {
            $booking = $this->bookingService->updateBookingStatus($booking, 'completed');

            // Send notification
            // ارسال نوتیفیکیشن
            self::sendBookingNotificationToAll($booking, 'booking_completed');

            return $this->successResponse('booking_completed_successfully', $booking);
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'booking', 'message' => $e->getMessage()],
            ]);
        }
    }

    /**
     * Cancel booking
     * لغو رزرو
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cancel(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'booking_id' => 'required|integer|exists:beauty_bookings,id',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $booking = $this->booking->where('salon_id', $salon->id)->findOrFail($request->booking_id);
        
        // Authorization check: Ensure booking belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به سالن فروشنده است
        $this->authorizeBookingAccess($booking, $salon);

        try {
            // Vendor cancellation: no fee to customer (full refund)
            // لغو فروشنده: بدون جریمه برای مشتری (بازگشت وجه کامل)
            $booking = $this->bookingService->cancelBooking($booking, $request->cancellation_reason, 'salon');

            // Send notification
            // ارسال نوتیفیکیشن
            self::sendBookingNotificationToAll($booking, 'booking_cancelled');

            return $this->successResponse('booking_cancelled_successfully', $booking);
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'booking', 'message' => $e->getMessage()],
            ]);
        }
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
            abort(404, translate('messages.salon_not_found'));
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
}

