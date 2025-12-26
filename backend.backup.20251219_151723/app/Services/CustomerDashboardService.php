<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Customer Dashboard Service
 * سرویس داشبورد مشتری
 *
 * Centralizes dashboard data aggregation logic for customer dashboard
 * متمرکز کردن منطق تجمیع داده‌های داشبورد برای داشبورد مشتری
 */
class CustomerDashboardService
{
    /**
     * Constructor
     * سازنده
     */
    public function __construct()
    {
        // Disable ONLY_FULL_GROUP_BY SQL mode to allow GROUP BY with all selected columns
        // غیرفعال کردن حالت ONLY_FULL_GROUP_BY برای اجازه GROUP BY با تمام ستون‌های انتخاب شده
        // This matches the pattern used in Admin and Vendor beauty dashboards
        // این با الگوی استفاده شده در داشبوردهای زیبایی Admin و Vendor هماهنگ است
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    }
    /**
     * Get beauty statistics for a user
     * دریافت آمار زیبایی برای یک کاربر
     *
     * @param User $user
     * @return array
     */
    public function getBeautyStatistics(User $user): array
    {
        // Check if BeautyBooking module is active
        // بررسی اینکه آیا ماژول BeautyBooking فعال است
        if (!addon_published_status('BeautyBooking')) {
            return [];
        }

        // Cache key
        // کلید کش
        $cacheKey = "customer_dashboard_beauty_stats_{$user->id}";

        // Try to get from cache
        // تلاش برای دریافت از کش
        return Cache::remember($cacheKey, 300, function () use ($user) {
            try {
                // Use string class names to prevent ClassNotFoundException if module is disabled
                // استفاده از نام کلاس به صورت رشته برای جلوگیری از ClassNotFoundException در صورت غیرفعال بودن ماژول
                $bookingClass = 'Modules\BeautyBooking\Entities\BeautyBooking';
                
                // Check if class exists before using it (with autoload=true to allow Laravel autoloader)
                // بررسی وجود کلاس قبل از استفاده از آن (با autoload=true برای اجازه دادن به Laravel autoloader)
                if (!class_exists($bookingClass, true)) {
                    Log::warning('BeautyBooking class not found, returning empty stats');
                    return [];
                }
                
                // Use optimized single query for multiple stats
                // استفاده از یک کوئری بهینه برای چندین آمار
                $stats = DB::select("
                    SELECT 
                        (SELECT COUNT(*) FROM beauty_bookings WHERE user_id = ? AND booking_date >= CURDATE() AND status NOT IN ('cancelled', 'completed', 'no_show')) as upcoming_bookings,
                        (SELECT COUNT(*) FROM beauty_bookings WHERE user_id = ?) as total_bookings,
                        (SELECT COUNT(*) FROM beauty_bookings WHERE user_id = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())) as bookings_this_month,
                        (SELECT COUNT(*) FROM beauty_bookings WHERE user_id = ? AND YEAR(created_at) = YEAR(CURDATE())) as bookings_this_year,
                        (SELECT COALESCE(SUM(total_amount), 0) FROM beauty_bookings WHERE user_id = ? AND payment_status = 'paid') as total_spent,
                        0 as active_packages_placeholder,
                        (SELECT COALESCE(SUM(amount), 0) FROM beauty_gift_cards WHERE (purchased_by = ? OR redeemed_by = ?) AND status = 'active' AND (expires_at IS NULL OR expires_at > NOW())) as gift_card_balance,
                        (SELECT COALESCE(SUM(points), 0) FROM beauty_loyalty_points WHERE user_id = ?) as loyalty_points_balance,
                        (SELECT COUNT(*) FROM beauty_bookings WHERE user_id = ? AND status = 'completed' AND NOT EXISTS (SELECT 1 FROM beauty_reviews WHERE booking_id = beauty_bookings.id)) as pending_reviews,
                        (SELECT COUNT(*) FROM beauty_bookings b 
                         INNER JOIN beauty_services s ON b.service_id = s.id 
                         WHERE b.user_id = ? AND s.service_type IN ('pre_consultation', 'post_consultation') AND b.status IN ('pending', 'confirmed')) as active_consultations,
                        (SELECT COUNT(*) FROM beauty_retail_orders WHERE user_id = ? AND status != 'delivered') as retail_orders_count
                ", [
                    $user->id, // upcoming_bookings
                    $user->id, // total_bookings
                    $user->id, // bookings_this_month
                    $user->id, // bookings_this_year
                    $user->id, // total_spent
                    $user->id, // gift_card_balance (purchased_by)
                    $user->id, // gift_card_balance (redeemed_by)
                    $user->id, // loyalty_points_balance
                    $user->id, // pending_reviews
                    $user->id, // active_consultations
                    $user->id, // retail_orders_count
                ]);

                $result = $stats[0] ?? (object)[
                    'upcoming_bookings' => 0,
                    'total_bookings' => 0,
                    'bookings_this_month' => 0,
                    'bookings_this_year' => 0,
                    'total_spent' => 0,
                    'gift_card_balance' => 0,
                    'loyalty_points_balance' => 0,
                    'pending_reviews' => 0,
                    'active_consultations' => 0,
                    'retail_orders_count' => 0,
                ];

                // Calculate active packages separately (complex query)
                // محاسبه پکیج‌های فعال به صورت جداگانه (کوئری پیچیده)
                $packageUsageClass = 'Modules\BeautyBooking\Entities\BeautyPackageUsage';
                $packageClass = 'Modules\BeautyBooking\Entities\BeautyPackage';
                
                $activePackagesCount = 0;
                try {
                    // Get all packages that have been used by this user
                    // دریافت تمام پکیج‌هایی که توسط این کاربر استفاده شده‌اند
                    $packageIds = $packageUsageClass::where('user_id', $user->id)
                        ->where('status', 'used')
                        ->distinct()
                        ->pluck('package_id');
                    
                    if ($packageIds->isNotEmpty()) {
                        $packages = $packageClass::whereIn('id', $packageIds)->get();
                        
                        foreach ($packages as $package) {
                            $usedCount = $packageUsageClass::where('package_id', $package->id)
                                ->where('user_id', $user->id)
                                ->where('status', 'used')
                                ->count();
                            
                            // Calculate expiry date from package creation
                            // محاسبه تاریخ انقضا از زمان ایجاد پکیج
                            $expiryDate = Carbon::parse($package->created_at)->addDays($package->validity_days);
                            
                            // Package is active if: used sessions < total sessions AND not expired
                            // پکیج فعال است اگر: جلسات استفاده شده < کل جلسات و منقضی نشده باشد
                            if ($usedCount < $package->sessions_count && $expiryDate->isFuture()) {
                                $activePackagesCount++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Active packages calculation error: ' . $e->getMessage());
                }

                $statsArray = [
                    'upcoming_bookings' => (int)($result->upcoming_bookings ?? 0),
                    'total_bookings' => (int)($result->total_bookings ?? 0),
                    'bookings_this_month' => (int)($result->bookings_this_month ?? 0),
                    'bookings_this_year' => (int)($result->bookings_this_year ?? 0),
                    'total_spent' => (float)($result->total_spent ?? 0),
                    'active_packages' => $activePackagesCount,
                    'gift_card_balance' => (float)($result->gift_card_balance ?? 0),
                    'loyalty_points_balance' => (int)($result->loyalty_points_balance ?? 0),
                    'pending_reviews' => (int)($result->pending_reviews ?? 0),
                    'active_consultations' => (int)($result->active_consultations ?? 0),
                    'retail_orders_count' => (int)($result->retail_orders_count ?? 0),
                ];
                
                return $statsArray;
            } catch (\Exception $e) {
                Log::error('Beauty dashboard statistics error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get beauty widgets data
     * دریافت داده‌های ویجت‌های زیبایی
     *
     * @param User $user
     * @return array
     */
    public function getBeautyWidgets(User $user): array
    {
        if (!addon_published_status('BeautyBooking')) {
            return [];
        }

        $cacheKey = "customer_dashboard_beauty_widgets_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            try {
                // Use string class names to prevent ClassNotFoundException if module is disabled
                // استفاده از نام کلاس به صورت رشته برای جلوگیری از ClassNotFoundException در صورت غیرفعال بودن ماژول
                $bookingClass = 'Modules\BeautyBooking\Entities\BeautyBooking';
                $packageClass = 'Modules\BeautyBooking\Entities\BeautyPackage';
                $packageUsageClass = 'Modules\BeautyBooking\Entities\BeautyPackageUsage';
                $giftCardClass = 'Modules\BeautyBooking\Entities\BeautyGiftCard';
                $loyaltyPointClass = 'Modules\BeautyBooking\Entities\BeautyLoyaltyPoint';
                $reviewClass = 'Modules\BeautyBooking\Entities\BeautyReview';
                $retailOrderClass = 'Modules\BeautyBooking\Entities\BeautyRetailOrder';

                // Check if classes exist before using them (with autoload=true to allow Laravel autoloader)
                // بررسی وجود کلاس‌ها قبل از استفاده از آن‌ها (با autoload=true برای اجازه دادن به Laravel autoloader)
                if (!class_exists($bookingClass, true) || !class_exists($packageClass, true)) {
                    Log::warning('BeautyBooking classes not found, returning empty widgets');
                    return [];
                }

                // Upcoming bookings
                // رزروهای آینده
                $upcomingBookings = $bookingClass::where('user_id', $user->id)
                    ->upcoming()
                    ->with(['salon.store', 'service', 'staff'])
                    ->latest()
                    ->limit(5)
                    ->get();

                // Active packages with usage info
                // پکیج‌های فعال با اطلاعات استفاده
                $activePackages = $packageClass::whereHas('usages', function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->where('status', 'used');
                    })
                    ->with(['salon.store', 'service'])
                    ->get()
                    ->filter(function($package) use ($user, $packageUsageClass) {
                        $usedCount = $packageUsageClass::where('package_id', $package->id)
                            ->where('user_id', $user->id)
                            ->where('status', 'used')
                            ->count();
                        return $usedCount < $package->sessions_count;
                    })
                    ->take(5);

                // Active gift cards
                // کارت‌های هدیه فعال
                $activeGiftCards = $giftCardClass::where(function($q) use ($user) {
                        $q->where('purchased_by', $user->id)
                          ->orWhere('redeemed_by', $user->id);
                    })
                    ->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    })
                    ->with('salon.store')
                    ->latest()
                    ->limit(5)
                    ->get();

                // Recent loyalty points
                // امتیازات وفاداری اخیر
                $recentLoyaltyPoints = $loyaltyPointClass::where('user_id', $user->id)
                    ->with(['booking', 'campaign'])
                    ->latest()
                    ->limit(10)
                    ->get();

                // Pending reviews (completed bookings without reviews)
                // نظرات در انتظار (رزروهای تکمیل شده بدون نظر)
                $pendingReviews = $bookingClass::where('user_id', $user->id)
                    ->where('status', 'completed')
                    ->whereDoesntHave('review')
                    ->with(['salon.store', 'service'])
                    ->latest()
                    ->limit(5)
                    ->get();

                // Recent retail orders
                // سفارشات خرده‌فروشی اخیر
                $recentRetailOrders = $retailOrderClass::where('user_id', $user->id)
                    ->with(['salon.store'])
                    ->latest()
                    ->limit(5)
                    ->get();

                return [
                    'upcoming_bookings' => $upcomingBookings,
                    'active_packages' => $activePackages,
                    'active_gift_cards' => $activeGiftCards,
                    'recent_loyalty_points' => $recentLoyaltyPoints,
                    'pending_reviews' => $pendingReviews,
                    'recent_retail_orders' => $recentRetailOrders,
                ];
            } catch (\Exception $e) {
                Log::error('Beauty dashboard widgets error: ' . $e->getMessage());
                // Return empty collections instead of empty array to match view expectations
                // برگرداندن collection های خالی به جای آرایه خالی برای مطابقت با انتظارات view
                return [
                    'upcoming_bookings' => collect([]),
                    'active_packages' => collect([]),
                    'active_gift_cards' => collect([]),
                    'recent_loyalty_points' => collect([]),
                    'pending_reviews' => collect([]),
                    'recent_retail_orders' => collect([]),
                ];
            }
        });
    }

    /**
     * Get beauty activity feed
     * دریافت فید فعالیت‌های زیبایی
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getBeautyActivityFeed(User $user, int $limit = 10): Collection
    {
        if (!addon_published_status('BeautyBooking')) {
            return collect([]);
        }

        $cacheKey = "customer_dashboard_beauty_activity_{$user->id}_{$limit}";

        return Cache::remember($cacheKey, 120, function () use ($user, $limit) {
            try {
                // Use string class names to prevent ClassNotFoundException if module is disabled
                // استفاده از نام کلاس به صورت رشته برای جلوگیری از ClassNotFoundException در صورت غیرفعال بودن ماژول
                $bookingClass = 'Modules\BeautyBooking\Entities\BeautyBooking';
                $reviewClass = 'Modules\BeautyBooking\Entities\BeautyReview';
                $giftCardClass = 'Modules\BeautyBooking\Entities\BeautyGiftCard';
                $retailOrderClass = 'Modules\BeautyBooking\Entities\BeautyRetailOrder';
                $loyaltyPointClass = 'Modules\BeautyBooking\Entities\BeautyLoyaltyPoint';
                
                // Check if classes exist before using them (with autoload=true to allow Laravel autoloader)
                // بررسی وجود کلاس‌ها قبل از استفاده از آن‌ها (با autoload=true برای اجازه دادن به Laravel autoloader)
                if (!class_exists($bookingClass, true)) {
                    Log::warning('BeautyBooking classes not found, returning empty activity');
                    return collect([]);
                }
                
                $activities = collect();

                // Recent bookings
                // رزروهای اخیر
                $recentBookings = $bookingClass::where('user_id', $user->id)
                    ->with(['salon.store', 'service'])
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function($booking) {
                        return [
                            'type' => 'booking',
                            'title' => translate('New Booking Created'),
                            'description' => translate('Booking') . ' #' . $booking->booking_reference,
                            'date' => $booking->created_at,
                            'data' => $booking,
                        ];
                    });

                // Recent reviews
                // نظرات اخیر
                $recentReviews = $reviewClass::where('user_id', $user->id)
                    ->with(['salon.store', 'booking'])
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function($review) {
                        return [
                            'type' => 'review',
                            'title' => translate('Review Submitted'),
                            'description' => translate('You submitted a review'),
                            'date' => $review->created_at,
                            'data' => $review,
                        ];
                    });

                // Recent gift cards
                // کارت‌های هدیه اخیر
                $recentGiftCards = $giftCardClass::where(function($q) use ($user) {
                        $q->where('purchased_by', $user->id)
                          ->orWhere('redeemed_by', $user->id);
                    })
                    ->with('salon.store')
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function($giftCard) use ($user) {
                        $isPurchase = $giftCard->purchased_by === $user->id;
                        return [
                            'type' => 'gift_card',
                            'title' => $isPurchase ? translate('Gift Card Purchased') : translate('Gift Card Redeemed'),
                            'description' => translate('Gift Card') . ' #' . $giftCard->code,
                            'date' => $isPurchase ? $giftCard->created_at : $giftCard->redeemed_at,
                            'data' => $giftCard,
                        ];
                    });

                // Recent retail orders
                // سفارشات خرده‌فروشی اخیر
                $recentRetailOrders = $retailOrderClass::where('user_id', $user->id)
                    ->with(['salon.store'])
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function($order) {
                        return [
                            'type' => 'retail_order',
                            'title' => translate('Retail Order Placed'),
                            'description' => translate('Order') . ' #' . $order->order_reference,
                            'date' => $order->created_at,
                            'data' => $order,
                        ];
                    });

                // Recent loyalty points
                // امتیازات وفاداری اخیر
                $recentLoyaltyPoints = $loyaltyPointClass::where('user_id', $user->id)
                    ->with(['booking', 'campaign'])
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(function($point) {
                        return [
                            'type' => 'loyalty_point',
                            'title' => $point->points > 0 ? translate('Loyalty Points Earned') : translate('Loyalty Points Redeemed'),
                            'description' => abs($point->points) . ' ' . translate('points'),
                            'date' => $point->created_at,
                            'data' => $point,
                        ];
                    });

                // Merge and sort by date
                // ادغام و مرتب‌سازی بر اساس تاریخ
                $activities = $activities
                    ->merge($recentBookings)
                    ->merge($recentReviews)
                    ->merge($recentGiftCards)
                    ->merge($recentRetailOrders)
                    ->merge($recentLoyaltyPoints)
                    ->sortByDesc('date')
                    ->take($limit);

                return $activities->values();
            } catch (\Exception $e) {
                Log::error('Beauty activity feed error: ' . $e->getMessage());
                return collect([]);
            }
        });
    }

    /**
     * Get chart data for beauty bookings
     * دریافت داده‌های نمودار برای رزروهای زیبایی
     *
     * @param User $user
     * @return array
     */
    public function getBeautyChartData(User $user): array
    {
        if (!addon_published_status('BeautyBooking')) {
            return [];
        }

        try {
            // Use string class names to prevent ClassNotFoundException if module is disabled
            // استفاده از نام کلاس به صورت رشته برای جلوگیری از ClassNotFoundException در صورت غیرفعال بودن ماژول
            $bookingClass = 'Modules\BeautyBooking\Entities\BeautyBooking';
            $loyaltyPointClass = 'Modules\BeautyBooking\Entities\BeautyLoyaltyPoint';
            
            // Check if classes exist before using them (with autoload=true to allow Laravel autoloader)
            // بررسی وجود کلاس‌ها قبل از استفاده از آن‌ها (با autoload=true برای اجازه دادن به Laravel autoloader)
            if (!class_exists($bookingClass, true)) {
                Log::warning('BeautyBooking classes not found, returning empty chart data');
                return [];
            }
            
            // Booking trends (last 12 months)
            // روند رزروها (12 ماه گذشته)
            $bookingTrends = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $count = $bookingClass::where('user_id', $user->id)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
                $bookingTrends[] = [
                    'month' => $date->format('M Y'),
                    'count' => $count,
                ];
            }

            // Spending trends (last 12 months)
            // روند هزینه‌ها (12 ماه گذشته)
            $spendingTrends = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $amount = $bookingClass::where('user_id', $user->id)
                    ->where('payment_status', 'paid')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_amount');
                $spendingTrends[] = [
                    'month' => $date->format('M Y'),
                    'amount' => (float)$amount,
                ];
            }

            // Most used services (top 5)
            // بیشترین خدمات استفاده شده (5 مورد برتر)
            $mostUsedServices = $bookingClass::where('user_id', $user->id)
                ->select('service_id', DB::raw('COUNT(*) as count'))
                ->groupBy('service_id')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->with('service')
                ->get()
                ->map(function($item) {
                    return [
                        'service_name' => $item->service->name ?? translate('Unknown Service'),
                        'count' => $item->count,
                    ];
                });

            // Favorite salons (top 5)
            // سالن‌های مورد علاقه (5 مورد برتر)
            $favoriteSalons = $bookingClass::where('user_id', $user->id)
                ->select('salon_id', DB::raw('COUNT(*) as count'))
                ->groupBy('salon_id')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->with('salon.store')
                ->get()
                ->map(function($item) {
                    return [
                        'salon_name' => $item->salon->store->name ?? translate('Unknown Salon'),
                        'count' => $item->count,
                    ];
                });

            // Loyalty points earned over time (last 12 months)
            // امتیازات وفاداری کسب شده در طول زمان (12 ماه گذشته)
            $loyaltyTrends = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $points = $loyaltyPointClass::where('user_id', $user->id)
                    ->where('points', '>', 0)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('points');
                $loyaltyTrends[] = [
                    'month' => $date->format('M Y'),
                    'points' => (int)$points,
                ];
            }

            return [
                'booking_trends' => $bookingTrends,
                'spending_trends' => $spendingTrends,
                'most_used_services' => $mostUsedServices,
                'favorite_salons' => $favoriteSalons,
                'loyalty_trends' => $loyaltyTrends,
            ];
        } catch (\Exception $e) {
            Log::error('Beauty chart data error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear cache for user
     * پاک کردن کش برای کاربر
     *
     * @param User $user
     * @return void
     */
    public function clearCache(User $user): void
    {
        Cache::forget("customer_dashboard_beauty_stats_{$user->id}");
        Cache::forget("customer_dashboard_beauty_widgets_{$user->id}");
        Cache::forget("customer_dashboard_beauty_activity_{$user->id}_10");
    }
}

