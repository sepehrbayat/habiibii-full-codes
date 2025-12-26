<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BeautyBooking\Entities\BeautyBooking;

/**
 * Beauty Refund Controller (Admin)
 * کنترلر بازپرداخت رزرو (ادمین)
 */
class BeautyRefundController extends Controller
{
    public function __construct(
        private BeautyBooking $booking
    ) {
    }

    /**
     * List refundable bookings
     * لیست رزروهای قابل بازپرداخت
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        $refundStatus = $request->get('refund_status', 'all');

        $bookings = $this->booking->with(['user', 'salon.store', 'service'])
            ->where(function ($query) {
                $query->whereIn('payment_status', ['refund_pending', 'refunded', 'partial_refund'])
                      ->orWhere(function ($q) {
                          $q->where('status', 'cancelled')->where('payment_status', 'paid');
                      });
            })
            ->when($refundStatus !== 'all', function ($query) use ($refundStatus) {
                $query->where('payment_status', $refundStatus);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::admin.refund.index', compact('bookings', 'refundStatus'));
    }
}

