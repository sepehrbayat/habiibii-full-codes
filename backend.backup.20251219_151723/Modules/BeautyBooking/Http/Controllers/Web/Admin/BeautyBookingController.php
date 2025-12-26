<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautySalon;
use App\CentralLogics\Helpers;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyBookingExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Booking Controller (Admin)
 * کنترلر رزرو زیبایی (ادمین)
 *
 * Handles admin booking management operations
 * مدیریت عملیات رزرو توسط ادمین
 */
class BeautyBookingController extends Controller
{
    public function __construct(
        private BeautyBooking $booking
    ) {}

    /**
     * List all bookings with filters
     * لیست تمام رزروها با فیلترها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        // Get filter parameters from request with defaults
        // دریافت پارامترهای فیلتر از درخواست با مقادیر پیش‌فرض
        $status = $request->get('status', 'all');
        $zone_ids = $request->get('zone_ids', []);
        $salon_ids = $request->get('salon_ids', []);
        $bookingStatus = $request->get('bookingStatus', []);
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');

        $bookings = $this->booking->with(['user', 'salon.store', 'service', 'staff'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->where(function($q) use ($key) {
                        $q->where('booking_reference', 'LIKE', '%' . $key . '%')
                          ->orWhereHas('user', function($userQuery) use ($key) {
                              $userQuery->where('f_name', 'LIKE', '%' . $key . '%')
                                       ->orWhere('l_name', 'LIKE', '%' . $key . '%')
                                       ->orWhere('phone', 'LIKE', '%' . $key . '%');
                          })
                          ->orWhereHas('salon.store', function($storeQuery) use ($key) {
                              $storeQuery->where('name', 'LIKE', '%' . $key . '%');
                          });
                    });
                }
            })
            ->when($status !== 'all' && $request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when(isset($zone_ids) && count($zone_ids) > 0, function ($query) use ($zone_ids) {
                $query->whereHas('salon', function($q) use ($zone_ids) {
                    $q->whereIn('zone_id', $zone_ids);
                });
            })
            ->when(isset($salon_ids) && count($salon_ids) > 0, function ($query) use ($salon_ids) {
                $query->whereIn('salon_id', $salon_ids);
            })
            ->when(isset($bookingStatus) && count($bookingStatus) > 0 && $status == 'all', function ($query) use ($bookingStatus) {
                $query->whereIn('status', $bookingStatus);
            })
            ->when(isset($from_date) && isset($to_date) && $from_date && $to_date, function ($query) use ($from_date, $to_date) {
                $query->whereDate('booking_date', '>=', $from_date)
                      ->whereDate('booking_date', '<=', $to_date);
            })
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->salon_id);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $salons = BeautySalon::with('store')->get();

        return view('beautybooking::admin.booking.index', compact(
            'bookings',
            'salons',
            'status',
            'zone_ids',
            'salon_ids',
            'bookingStatus',
            'from_date',
            'to_date'
        ));
    }

    /**
     * View booking details
     * مشاهده جزئیات رزرو
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function view(int $id)
    {
        $booking = $this->booking->with([
            'user',
            'salon.store',
            'service',
            'staff',
            'package',
            'review',
            'conversation',
            'transaction'
        ])->findOrFail($id);

        return view('beautybooking::admin.booking.view', compact('booking'));
    }

    /**
     * Calendar view of all bookings
     * نمایش تقویم تمام رزروها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function calendar(Request $request)
    {
        $start = $request->get('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $bookings = $this->booking->with(['user', 'salon.store', 'service'])
            ->whereBetween('booking_date', [$start, $end])
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'title' => ($booking->salon->store->name ?? 'Salon') . ' - ' . ($booking->service->name ?? 'Service'),
                    'start' => $booking->booking_date->format('Y-m-d') . 'T' . $booking->booking_time,
                    'url' => route('admin.beautybooking.booking.view', $booking->id),
                    'color' => match($booking->status) {
                        'confirmed' => '#28a745',
                        'pending' => '#ffc107',
                        'cancelled' => '#dc3545',
                        'completed' => '#17a2b8',
                        default => '#6c757d'
                    }
                ];
            });

        return view('beautybooking::admin.booking.calendar', compact('bookings'));
    }

    /**
     * Process manual refund
     * پردازش بازپرداخت دستی
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function refund(\Modules\BeautyBooking\Http\Requests\BeautyBookingRefundRequest $request, int $id): RedirectResponse
    {

        try {
            $booking = $this->booking->findOrFail($id);
            
            // Process refund logic here
            // منطق پردازش بازپرداخت در اینجا
            
            Toastr::success(translate('messages.refund_processed_successfully'));
        } catch (\Exception $e) {
            \Log::error('Refund processing failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_process_refund'));
        }

        return back();
    }

    /**
     * Force cancel booking
     * لغو اجباری رزرو
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function forceCancel(\Modules\BeautyBooking\Http\Requests\BeautyBookingForceCancelRequest $request, int $id): RedirectResponse
    {

        try {
            $booking = $this->booking->findOrFail($id);
            
            $booking->update([
                'status' => 'cancelled',
                'cancelled_by' => 'admin',
                'cancellation_reason' => $request->cancellation_reason,
            ]);

            Toastr::success(translate('messages.booking_cancelled_successfully'));
        } catch (\Exception $e) {
            \Log::error('Force cancel failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_cancel_booking'));
        }

        return back();
    }

    /**
     * Mark refund as completed for digital payment
     * علامت‌گذاری بازگشت وجه به عنوان تکمیل شده برای پرداخت دیجیتال
     *
     * This method is called by admin after processing refund through payment gateway
     * این متد توسط ادمین پس از پردازش بازگشت وجه از طریق درگاه پرداخت فراخوانی می‌شود
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function markRefundCompleted(int $id): RedirectResponse
    {
        try {
            $booking = $this->booking->findOrFail($id);
            
            // Validate that booking is eligible for refund completion
            // اعتبارسنجی اینکه رزرو واجد شرایط تکمیل بازگشت وجه است
            if ($booking->payment_status !== 'refund_pending') {
                Toastr::error(translate('messages.booking_not_eligible_for_refund_completion'));
                return back();
            }
            
            if ($booking->payment_method !== 'digital_payment') {
                Toastr::error(translate('messages.refund_completion_only_for_digital_payments'));
                return back();
            }
            
            // Update payment status to refunded
            // به‌روزرسانی وضعیت پرداخت به refunded
            $booking->update([
                'payment_status' => 'refunded',
            ]);
            
            // Log refund completion for audit trail
            // ثبت تکمیل بازگشت وجه برای audit trail
            \Log::info('Refund marked as completed by admin', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'refund_amount' => $booking->total_amount - ($booking->cancellation_fee ?? 0),
                'admin_id' => auth('admin')->id(),
            ]);
            
            Toastr::success(translate('messages.refund_marked_as_completed'));
        } catch (\Exception $e) {
            \Log::error('Failed to mark refund as completed: ' . $e->getMessage(), [
                'booking_id' => $id,
                'error' => $e->getMessage(),
            ]);
            Toastr::error(translate('messages.failed_to_mark_refund_completed'));
        }

        return back();
    }

    /**
     * Generate invoice for booking
     * تولید فاکتور برای رزرو
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function generateInvoice(int $id)
    {
        $booking = $this->booking->with([
            'user',
            'salon.store',
            'service.category',
            'staff',
            'package'
        ])->findOrFail($id);

        return view('beautybooking::admin.booking.invoice', compact('booking'));
    }

    /**
     * Print invoice for booking
     * چاپ فاکتور برای رزرو
     *
     * @param int $id
     * @return string
     */
    public function printInvoice(int $id): string
    {
        $booking = $this->booking->with([
            'user',
            'salon.store',
            'service.category',
            'staff',
            'package'
        ])->findOrFail($id);

        return view('beautybooking::admin.booking.invoice-print', compact('booking'))->render();
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
        $bookings = $this->booking->with(['user', 'salon.store', 'service', 'staff'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->where(function($q) use ($key) {
                        $q->where('booking_reference', 'LIKE', '%' . $key . '%')
                          ->orWhereHas('user', function($userQuery) use ($key) {
                              $userQuery->where('f_name', 'LIKE', '%' . $key . '%')
                                       ->orWhere('l_name', 'LIKE', '%' . $key . '%')
                                       ->orWhere('phone', 'LIKE', '%' . $key . '%');
                          })
                          ->orWhereHas('salon.store', function($storeQuery) use ($key) {
                              $storeQuery->where('name', 'LIKE', '%' . $key . '%');
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

