<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyPackageUsage;
use Modules\BeautyBooking\Entities\BeautyLoyaltyPoint;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use Modules\BeautyBooking\Traits\BeautyApiResponse;

/**
 * Beauty Dashboard Controller (Customer API)
 * کنترلر داشبورد (API مشتری)
 */
class BeautyDashboardController extends Controller
{
    use BeautyApiResponse;

    public function __construct(
        private BeautyBooking $booking
    ) {}

    /**
     * Get dashboard summary
     * دریافت خلاصه داشبورد
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "total_bookings": 25,
     *     "upcoming_bookings": 3,
     *     "total_spent": 5000000,
     *     "active_packages": 2,
     *     "pending_consultations": 1,
     *     "gift_card_balance": 100000,
     *     "loyalty_points": 500
     *   }
     * }
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Total bookings count
        // تعداد کل رزروها
        $totalBookings = $this->booking->where('user_id', $user->id)->count();
        
        // Upcoming bookings count
        // تعداد رزروهای آینده
        $upcomingBookings = $this->booking->where('user_id', $user->id)
            ->upcoming()
            ->count();
        
        // Total spent (sum of paid/completed bookings)
        // کل هزینه (مجموع رزروهای پرداخت شده/تکمیل شده)
        $totalSpent = $this->booking->where('user_id', $user->id)
            ->whereIn('status', ['completed', 'confirmed'])
            ->whereIn('payment_status', ['paid', 'partially_paid'])
            ->sum('total_amount');
        
        // Active packages count
        // تعداد پکیج‌های فعال
        $activePackages = BeautyPackageUsage::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereHas('package', function($q) {
                $q->where('status', 1);
            })
            ->distinct('package_id')
            ->count();
        
        // Pending consultations count
        // تعداد مشاوره‌های در انتظار
        $pendingConsultations = $this->booking->where('user_id', $user->id)
            ->whereHas('service', function($q) {
                $q->whereIn('service_type', ['pre_consultation', 'post_consultation']);
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
        
        // Gift card balance (sum of active gift cards)
        // موجودی کارت هدیه (مجموع کارت‌های هدیه فعال)
        $giftCardBalance = BeautyGiftCard::where('purchased_by', $user->id)
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->sum('amount');
        
        // Loyalty points balance
        // موجودی امتیازهای وفاداری
        $loyaltyPoints = (float) BeautyLoyaltyPoint::where('user_id', $user->id)
            ->valid()
            ->earned()
            ->sum('points');
        
        $usedPointsRaw = BeautyLoyaltyPoint::where('user_id', $user->id)
            ->where('type', 'redeemed')
            ->sum('points');
        $usedPoints = abs((float) $usedPointsRaw);
        
        $availableLoyaltyPoints = max(0, $loyaltyPoints - $usedPoints);
        
        return $this->successResponse('messages.data_retrieved_successfully', [
            'total_bookings' => $totalBookings,
            'upcoming_bookings' => $upcomingBookings,
            'total_spent' => (float)$totalSpent,
            'active_packages' => $activePackages,
            'pending_consultations' => $pendingConsultations,
            'gift_card_balance' => (float)$giftCardBalance,
            'loyalty_points' => (int)$availableLoyaltyPoints,
        ]);
    }

    /**
     * Get wallet transactions for beauty bookings
     * دریافت تراکنش‌های کیف پول برای رزروهای زیبایی
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @queryParam limit integer optional Number of items per page (default: 25). Example: 25
     * @queryParam offset integer optional Offset for pagination (default: 0). Example: 0
     * @queryParam type string optional Filter by transaction type (beauty_booking, beauty_package_purchase, beauty_gift_card_purchase, etc.). Example: beauty_booking
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "transaction_id": "uuid",
     *       "transaction_type": "beauty_booking",
     *       "credit": 0,
     *       "debit": 100000,
     *       "balance": 500000,
     *       "created_at": "2024-01-20 10:00:00"
     *     }
     *   ],
     *   "total": 10,
     *   "per_page": 25,
     *   "current_page": 1,
     *   "last_page": 1
     * }
     */
    public function walletTransactions(Request $request): JsonResponse
    {
        $limit = $request->get('per_page', $request->get('limit', 25));
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $user = $request->user();
        
        // Get beauty-related wallet transactions
        // دریافت تراکنش‌های کیف پول مربوط به زیبایی
        $transactions = \App\Models\WalletTransaction::where('user_id', $user->id)
            ->where('transaction_type', 'like', '%beauty%')
            ->when($request->filled('type'), function ($query) use ($request) {
                // If specific type provided, filter by exact match or contains
                // اگر نوع خاصی ارائه شده، فیلتر بر اساس تطابق دقیق یا شامل
                $type = $request->type;
                if (strpos($type, 'beauty') === false) {
                    // If type doesn't contain 'beauty', prepend it
                    // اگر نوع شامل 'beauty' نیست، آن را اضافه کنید
                    $type = 'beauty_' . $type;
                }
                $query->where('transaction_type', 'like', '%' . $type . '%');
            })
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $formatted = $transactions->getCollection()->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'transaction_id' => $transaction->transaction_id,
                'transaction_type' => $transaction->transaction_type,
                'credit' => (float)($transaction->credit ?? 0),
                'debit' => (float)($transaction->debit ?? 0),
                'balance' => (float)($transaction->balance ?? 0),
                'reference' => $transaction->reference ?? null,
                'created_at' => $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        $transactions->setCollection($formatted->values());

        return $this->listResponse($transactions);
    }
}

