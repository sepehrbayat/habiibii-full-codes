<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautySalon;

/**
 * Beauty Admin Calendar Controller
 * کنترلر تقویم ادمین برای مدیریت رزروهای زیبایی
 */
class BeautyAdminCalendarController extends Controller
{
    /**
     * Show staff/booking calendar for admin
     * نمایش تقویم رزرو و پرسنل برای ادمین
     *
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request): Renderable
    {
        $eventsUrl = route('admin.beautybooking.staff.calendar.events');
        $salons = BeautySalon::with('store')->get();

        return view('beautybooking::admin.staff.calendar', compact('eventsUrl', 'salons'));
    }

    /**
     * Return booking events for FullCalendar
     * بازگرداندن رویدادهای رزرو برای FullCalendar
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function events(Request $request): JsonResponse
    {
        $start = Carbon::parse($request->get('start', now()->startOfMonth()->toDateString()));
        $end = Carbon::parse($request->get('end', now()->endOfMonth()->toDateString()));

        $bookings = BeautyBooking::with(['service', 'user', 'staff', 'salon.store'])
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', (int) $request->salon_id);
            })
            ->whereBetween('booking_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $events = $bookings->map(function (BeautyBooking $booking) {
            $startDateTime = $this->resolveBookingStartDateTime($booking);
            $durationMinutes = $booking->service->duration_minutes ?? 60;

            return [
                'id' => $booking->id,
                'title' => ($booking->service->name ?? '') . ' - ' . (($booking->staff->name ?? null) ?: translate('messages.No_Staff')),
                'start' => $startDateTime->toIso8601String(),
                'end' => $startDateTime->copy()->addMinutes($durationMinutes)->toIso8601String(),
                'color' => $this->getEventColor($booking->status),
                'extendedProps' => [
                    'booking_reference' => $booking->booking_reference,
                    'status' => $booking->status,
                    'service_name' => $booking->service->name ?? '',
                    'customer_name' => trim(($booking->user->f_name ?? '') . ' ' . ($booking->user->l_name ?? '')),
                    'salon_name' => $booking->salon->store->name ?? '',
                ],
                'url' => route('admin.beautybooking.booking.view', $booking->id),
            ];
        });

        return response()->json($events);
    }

    /**
     * Map booking status to event color
     * نگاشت وضعیت رزرو به رنگ رویداد
     *
     * @param string $status
     * @return string
     */
    private function getEventColor(string $status): string
    {
        return match ($status) {
            'pending' => '#FFA500',
            'confirmed' => '#28A745',
            'completed' => '#007BFF',
            'cancelled' => '#DC3545',
            default => '#6C757D',
        };
    }

    /**
     * تبدیل امن تاریخ و زمان شروع رزرو
     * Resolve booking start date and time safely
     *
     * @param BeautyBooking $booking رزرو در حال تبدیل به رویداد تقویم
     * @return Carbon تاریخ و زمان رزرو پس از تجزیه
     */
    private function resolveBookingStartDateTime(BeautyBooking $booking): Carbon
    {
        if ($booking->booking_date_time instanceof Carbon) {
            return $booking->booking_date_time->copy();
        }

        $bookingDate = $booking->booking_date instanceof Carbon
            ? $booking->booking_date->format('Y-m-d')
            : (string) $booking->booking_date;

        $bookingTime = trim((string) ($booking->booking_time ?? ''));
        $dateTimeString = trim($bookingDate . ' ' . ($bookingTime !== '' ? $bookingTime : '00:00'));

        return Carbon::parse($dateTimeString);
    }
}


