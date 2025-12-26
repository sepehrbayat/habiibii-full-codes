<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use Modules\BeautyBooking\Entities\BeautyRetailOrder;
use Modules\BeautyBooking\Entities\BeautyReview;

/**
 * Beauty Dashboard Controller (Customer Web)
 * کنترلر داشبورد (وب مشتری)
 */
class BeautyDashboardController extends Controller
{
    public function __construct(
        private BeautyBooking $booking
    ) {}

    /**
     * Customer dashboard
     * داشبورد مشتری
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        $upcomingBookings = $this->booking->where('user_id', $user->id)
            ->upcoming()
            ->with(['salon.store', 'service', 'staff'])
            ->latest()
            ->limit(5)
            ->get();

        return view('beautybooking::customer.dashboard.index', compact('upcomingBookings'));
    }

    /**
     * My bookings
     * رزروهای من
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function bookings(Request $request)
    {
        $user = $request->user();
        
        $bookings = $this->booking->where('user_id', $user->id)
            ->with(['salon.store', 'service', 'staff', 'review'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::customer.dashboard.bookings', compact('bookings'));
    }

    /**
     * Booking detail
     * جزئیات رزرو
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function bookingDetail(int $id, Request $request)
    {
        $user = $request->user();
        
        $booking = $this->booking->where('user_id', $user->id)
            ->with(['salon.store', 'service', 'staff', 'review', 'transaction'])
            ->findOrFail($id);

        return view('beautybooking::customer.dashboard.booking-detail', compact('booking'));
    }

    /**
     * Wallet transactions
     * تراکنش‌های کیف پول
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function wallet(Request $request)
    {
        $user = $request->user();
        
        $transactions = \App\Models\WalletTransaction::where('user_id', $user->id)
            ->where('transaction_type', 'like', '%beauty%')
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::customer.dashboard.wallet', compact('transactions'));
    }

    /**
     * Gift cards
     * کارت‌های هدیه
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function giftCards(Request $request)
    {
        $user = $request->user();
        
        $giftCards = BeautyGiftCard::where('purchaser_id', $user->id)
            ->with('salon.store')
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::customer.dashboard.gift-cards', compact('giftCards'));
    }

    /**
     * Loyalty points
     * امتیازات وفاداری
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function loyalty(Request $request)
    {
        $user = $request->user();
        
        // Get loyalty points balance from bookings
        $totalPoints = \Modules\BeautyBooking\Entities\BeautyLoyaltyPoint::where('user_id', $user->id)
            ->sum('points');

        $pointsHistory = \Modules\BeautyBooking\Entities\BeautyLoyaltyPoint::where('user_id', $user->id)
            ->with(['booking', 'campaign'])
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::customer.dashboard.loyalty', compact('totalPoints', 'pointsHistory'));
    }

    /**
     * Consultations
     * مشاوره‌ها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function consultations(Request $request)
    {
        $user = $request->user();
        
        $consultations = $this->booking->where('user_id', $user->id)
            ->whereHas('service', function($q) {
                $q->whereIn('service_type', ['pre_consultation', 'post_consultation']);
            })
            ->with(['salon.store', 'service'])
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::customer.dashboard.consultations', compact('consultations'));
    }

    /**
     * Reviews
     * نظرات
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function reviews(Request $request)
    {
        $user = $request->user();
        
        $reviews = BeautyReview::where('user_id', $user->id)
            ->with(['salon.store', 'booking.service'])
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::customer.dashboard.reviews', compact('reviews'));
    }

    /**
     * Retail orders
     * سفارشات خرده فروشی
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function retailOrders(Request $request)
    {
        $user = $request->user();
        
        $orders = BeautyRetailOrder::where('user_id', $user->id)
            ->with(['salon.store', 'items.product'])
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::customer.dashboard.retail-orders', compact('orders'));
    }
}

