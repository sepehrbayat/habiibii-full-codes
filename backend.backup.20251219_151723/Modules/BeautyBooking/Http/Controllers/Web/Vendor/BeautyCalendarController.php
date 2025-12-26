<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;
use Modules\BeautyBooking\Http\Requests\BeautyCalendarBlockRequest;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Carbon\Carbon;

/**
 * Beauty Calendar Controller (Vendor)
 * کنترلر تقویم (فروشنده)
 */
class BeautyCalendarController extends Controller
{
    public function __construct(
        private BeautyCalendarService $calendarService
    ) {}

    /**
     * Calendar index view
     * نمایش تقویم
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        return view('beautybooking::vendor.calendar.index', compact('salon'));
    }

    /**
     * Get bookings for calendar (JSON for FullCalendar)
     * دریافت رزروها برای تقویم (JSON برای FullCalendar)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBookings(Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $startDate = $request->input('start', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end', Carbon::now()->endOfMonth()->toDateString());

        $bookings = BeautyBooking::where('salon_id', $salon->id)
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->with(['user', 'service', 'staff'])
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'title' => $booking->service->name . ' - ' . ($booking->user->f_name ?? 'Guest'),
                    'start' => $booking->booking_date_time,
                    'end' => Carbon::parse($booking->booking_date_time)->addMinutes($booking->service->duration_minutes),
                    'color' => $this->getBookingColor($booking->status),
                    'extendedProps' => [
                        'booking_reference' => $booking->booking_reference,
                        'status' => $booking->status,
                        'service_name' => $booking->service->name,
                        'customer_name' => ($booking->user->f_name ?? '') . ' ' . ($booking->user->l_name ?? ''),
                    ],
                ];
            });

        return response()->json($bookings);
    }

    /**
     * Create calendar block
     * ایجاد بلاک تقویم
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function createBlock(BeautyCalendarBlockRequest $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        try {
            $this->calendarService->blockTimeSlot(
                $salon->id,
                $request->staff_id,
                $request->date,
                $request->start_time,
                $request->end_time,
                $request->type,
                $request->reason
            );

            Toastr::success(translate('messages.calendar_block_created_successfully'));
        } catch (Exception $e) {
            \Log::error('Calendar block creation failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_create_calendar_block'));
        }

        return back();
    }

    /**
     * Delete calendar block
     * حذف بلاک تقویم
     *
     * @param int $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteBlock(int $id, Request $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $block = BeautyCalendarBlock::where('salon_id', $salon->id)->findOrFail($id);
        
        // Authorization check: Ensure block belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه بلاک متعلق به سالن فروشنده است
        if ($block->salon_id !== $salon->id) {
            abort(403, translate('messages.unauthorized_access'));
        }
        
        $this->calendarService->unblockTimeSlot($block->id);
        
        Toastr::success(translate('messages.calendar_block_deleted_successfully'));
        return back();
    }

    /**
     * Get booking color based on status
     * دریافت رنگ رزرو بر اساس وضعیت
     *
     * @param string $status
     * @return string
     */
    private function getBookingColor(string $status): string
    {
        return match($status) {
            'pending' => '#FFA500', // Orange
            'confirmed' => '#28A745', // Green
            'completed' => '#007BFF', // Blue
            'cancelled' => '#DC3545', // Red
            default => '#6C757D', // Gray
        };
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
}

