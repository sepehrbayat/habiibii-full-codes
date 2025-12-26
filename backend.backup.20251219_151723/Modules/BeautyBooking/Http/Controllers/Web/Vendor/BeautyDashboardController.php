<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\Helpers;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyReview;
use Modules\BeautyBooking\Entities\BeautyTransaction;
use Carbon\Carbon;

/**
 * Beauty Dashboard Controller (Vendor)
 * کنترلر داشبورد (فروشنده)
 */
class BeautyDashboardController extends Controller
{
    /**
     * Constructor
     * سازنده
     */
    public function __construct()
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    }
    /**
     * Vendor dashboard
     * داشبورد فروشنده
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function dashboard(Request $request)
    {
        // Get vendor data (handles both vendor and vendor_employee)
        // دریافت داده‌های فروشنده (هم فروشنده و هم کارمند فروشنده را مدیریت می‌کند)
        $vendor = \App\CentralLogics\Helpers::get_vendor_data();
        
        if (!$vendor || $vendor === 0 || !is_object($vendor)) {
            abort(403, translate('messages.unauthorized_access'));
        }
        
        // Get store data to find store_id
        // دریافت داده‌های فروشگاه برای یافتن store_id
        $store = \App\CentralLogics\Helpers::get_store_data();
        
        if (!$store) {
            abort(404, translate('messages.store_not_found'));
        }
        
        $salon = BeautySalon::with('store')->where('store_id', $store->id)->first();
        
        // If salon doesn't exist, redirect to registration
        // اگر سالن وجود نداشت، به صفحه ثبت‌نام هدایت شود
        if (!$salon) {
            return redirect()->route('vendor.beautybooking.salon.register');
        }
        
        // Authorization check: Ensure salon belongs to vendor
        // بررسی مجوز: اطمینان از اینکه سالن متعلق به فروشنده است
        if ($salon->store->vendor_id !== $vendor->id) {
            abort(403, translate('messages.unauthorized_access'));
        }

        $totalBookings = BeautyBooking::where('salon_id', $salon->id)->count();
        $upcomingBookings = BeautyBooking::where('salon_id', $salon->id)
            ->upcoming()
            ->count();
        $todayBookings = BeautyBooking::where('salon_id', $salon->id)
            ->whereDate('booking_date', Carbon::today())
            ->count();
        $totalRevenue = BeautyBooking::where('salon_id', $salon->id)
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        $todayRevenue = BeautyBooking::where('salon_id', $salon->id)
            ->where('payment_status', 'paid')
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');
        $thisMonthRevenue = BeautyBooking::where('salon_id', $salon->id)
            ->where('payment_status', 'paid')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_amount');
        $avgRating = $salon->avg_rating ?? 0;
        $totalReviews = BeautyReview::where('salon_id', $salon->id)->approved()->count();
        $cancellationRate = $totalBookings > 0 
            ? (BeautyBooking::where('salon_id', $salon->id)->where('status', 'cancelled')->count() / $totalBookings) * 100 
            : 0;

        // Clinic-specific statistics (for clinics)
        // آمار مخصوص کلینیک (برای کلینیک‌ها)
        $clinicStats = null;
        if ($salon->business_type === 'clinic') {
            // Total unique patients (users who have bookings)
            // تعداد کل بیماران منحصر به فرد (کاربرانی که رزرو داشته‌اند)
            // Fixed: Use selectRaw for proper DISTINCT COUNT
            // اصلاح شده: استفاده از selectRaw برای COUNT(DISTINCT) صحیح
            $totalPatients = BeautyBooking::where('salon_id', $salon->id)
                ->selectRaw('COUNT(DISTINCT user_id) as count')
                ->value('count') ?? 0;
            
            // Total visits (completed bookings)
            // تعداد کل ویزیت‌ها (رزروهای تکمیل شده)
            $totalVisits = BeautyBooking::where('salon_id', $salon->id)
                ->where('status', 'completed')
                ->count();
            
            // Today's visits
            // ویزیت‌های امروز
            $todayVisits = BeautyBooking::where('salon_id', $salon->id)
                ->where('status', 'completed')
                ->whereDate('booking_date', Carbon::today())
                ->count();
            
            // Average visits per patient
            // میانگین ویزیت به ازای هر بیمار
            $avgVisitsPerPatient = $totalPatients > 0 ? round($totalVisits / $totalPatients, 2) : 0;
            
            $clinicStats = [
                'total_patients' => $totalPatients,
                'total_visits' => $totalVisits,
                'today_visits' => $todayVisits,
                'avg_visits_per_patient' => $avgVisitsPerPatient,
            ];
        }

        // Chart data (last 12 months)
        $labels = [];
        $bookingsData = [];
        $revenueData = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $monthBookings = BeautyBooking::where('salon_id', $salon->id)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $bookingsData[] = $monthBookings;

            $monthRevenue = BeautyBooking::where('salon_id', $salon->id)
                ->where('payment_status', 'paid')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_amount');
            $revenueData[] = round($monthRevenue, 2);
        }

        $recentBookings = BeautyBooking::where('salon_id', $salon->id)
            ->with(['user', 'service', 'staff'])
            ->latest()
            ->limit(10)
            ->get();

        // Badge status
        $badges = $salon->badges_list ?? [];

        // Booking statistics for partial
        // آمار رزروها برای partial
        $bookingStatistics = $this->getBookingData($request, $salon->id);
        
        // Revenue data for chart
        // داده‌های درآمد برای نمودار
        $data = self::dashboard_data($request, $salon->id);
        $revenue = $data['revenue'];
        $label = $data['label'];

        // Revenue breakdown by model
        // تفکیک درآمد بر اساس مدل
        $revenueBreakdown = $this->getRevenueBreakdown($salon->id);

        return view('beautybooking::vendor.dashboard', [
            'salon' => $salon,
            'totalBookings' => $totalBookings,
            'upcomingBookings' => $upcomingBookings,
            'todayBookings' => $todayBookings,
            'totalRevenue' => $totalRevenue,
            'todayRevenue' => $todayRevenue,
            'thisMonthRevenue' => $thisMonthRevenue,
            'avgRating' => $avgRating,
            'totalReviews' => $totalReviews,
            'cancellationRate' => $cancellationRate,
            'recentBookings' => $recentBookings,
            'badges' => $badges,
            'pendingCount' => $bookingStatistics['pendingCount'],
            'confirmedCount' => $bookingStatistics['confirmedCount'],
            'completedCount' => $bookingStatistics['completedCount'],
            'cancelledCount' => $bookingStatistics['cancelledCount'],
            'totalCount' => $bookingStatistics['totalCount'],
            'revenue' => $revenue,
            'label' => $label,
            'clinicStats' => $clinicStats,
            'revenueBreakdown' => $revenueBreakdown,
        ]);
    }

    /**
     * Get booking statistics (AJAX handler)
     * دریافت آمار رزروها (هندلر AJAX)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bookingStatistics(Request $request)
    {
        $vendor = \App\CentralLogics\Helpers::get_vendor_data();
        
        if (!$vendor || $vendor === 0 || !is_object($vendor)) {
            abort(403, translate('messages.unauthorized_access'));
        }
        
        $store = \App\CentralLogics\Helpers::get_store_data();
        
        if (!$store) {
            abort(404, translate('messages.store_not_found'));
        }
        
        $salon = BeautySalon::with('store')->where('store_id', $store->id)->first();
        
        if (!$salon || $salon->store->vendor_id !== $vendor->id) {
            abort(403, translate('messages.unauthorized_access'));
        }
        
        $data = $this->getBookingData($request, $salon->id);
        
        return response()->json([
            'booking_statistics' => view('beautybooking::vendor.partials._booking-statistics', [
                'pendingCount' => $data['pendingCount'],
                'confirmedCount' => $data['confirmedCount'],
                'completedCount' => $data['completedCount'],
                'cancelledCount' => $data['cancelledCount'],
                'totalCount' => $data['totalCount'],
            ])->render(),
        ], 200);
    }

    /**
     * Get revenue overview (AJAX handler)
     * دریافت نمای کلی درآمد (هندلر AJAX)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revenueOverview(Request $request)
    {
        $vendor = \App\CentralLogics\Helpers::get_vendor_data();
        
        if (!$vendor || $vendor === 0 || !is_object($vendor)) {
            abort(403, translate('messages.unauthorized_access'));
        }
        
        $store = \App\CentralLogics\Helpers::get_store_data();
        
        if (!$store) {
            abort(404, translate('messages.store_not_found'));
        }
        
        $salon = BeautySalon::with('store')->where('store_id', $store->id)->first();
        
        if (!$salon || $salon->store->vendor_id !== $vendor->id) {
            abort(403, translate('messages.unauthorized_access'));
        }
        
        $data = self::dashboard_data($request, $salon->id);
        $revenue = $data['revenue'];
        $label = $data['label'];
        $grossEarning = collect($revenue)->sum();

        return response()->json([
            'view' => view('beautybooking::vendor.partials._sale-chart', compact('revenue', 'label', 'grossEarning'))->render(),
            'grossEarning' => $grossEarning,
            'revenue' => array_map(function($val) {
                return number_format((float)$val, 2, '.', '');
            }, array_values($revenue)),
            'labels' => array_map(function($val) {
                return trim($val, '"');
            }, $label),
        ], 200);
    }

    /**
     * Get booking data based on filters
     * دریافت داده‌های رزرو بر اساس فیلترها
     *
     * @param Request $request
     * @param int $salonId
     * @return array
     */
    private function getBookingData(Request $request, int $salonId): array
    {
        $statisticsType = $request->get('statistics_type', 'all');

        $bookingQuery = BeautyBooking::where('salon_id', $salonId);
        
        if ($statisticsType == 'this_year') {
            $bookingQuery->whereYear('created_at', now()->year);
        } elseif ($statisticsType == 'this_month') {
            $bookingQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($statisticsType == 'this_week') {
            $bookingQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        }
        
        return [
            'pendingCount' => (clone $bookingQuery)->where('status', 'pending')->count(),
            'confirmedCount' => (clone $bookingQuery)->where('status', 'confirmed')->count(),
            'completedCount' => (clone $bookingQuery)->where('status', 'completed')->count(),
            'cancelledCount' => (clone $bookingQuery)->where('status', 'cancelled')->count(),
            'totalCount' => (clone $bookingQuery)->count(),
        ];
    }

    /**
     * Get dashboard data for revenue chart
     * دریافت داده‌های داشبورد برای نمودار درآمد
     *
     * @param Request|array $request
     * @param int $salonId
     * @return array
     */
    public static function dashboard_data($request, int $salonId): array
    {
        $months = array(
            '"' . translate('Jan') . '"',
            '"' . translate('Feb') . '"',
            '"' . translate('Mar') . '"',
            '"' . translate('Apr') . '"',
            '"' . translate('May') . '"',
            '"' . translate('Jun') . '"',
            '"' . translate('Jul') . '"',
            '"' . translate('Aug') . '"',
            '"' . translate('Sep') . '"',
            '"' . translate('Oct') . '"',
            '"' . translate('Nov') . '"',
            '"' . translate('Dec') . '"'
        );
        
        $days = array(
            '"' . translate('Mon') . '"',
            '"' . translate('Tue') . '"',
            '"' . translate('Wed') . '"',
            '"' . translate('Thu') . '"',
            '"' . translate('Fri') . '"',
            '"' . translate('Sat') . '"',
            '"' . translate('Sun') . '"',
        );
        
        $revenue = [];
        $revenueOverview = is_array($request) ? ($request['revenue_overview'] ?? 'all') : $request->get('revenue_overview', 'all');

        switch ($revenueOverview) {
            case "this_year":
                for ($i = 0; $i < 12; $i++) {
                    $month = $i + 1;
                    // Fixed: amount already represents net revenue after commission deduction
                    // اصلاح شده: amount از قبل نمایانگر درآمد خالص پس از کسر کمیسیون است
                    $revenue[$i] = BeautyTransaction::where('salon_id', $salonId)
                        ->whereIn('transaction_type', [
                            'commission',
                            'package_sale',
                            'cancellation_fee',
                            'consultation_fee',
                            'cross_selling',
                            'retail_sale',
                            'gift_card_sale'
                        ])
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', now()->format('Y'))
                        ->sum('amount');
                }
                $label = $months;
                break;

            case "this_week":
                $weekStartDate = now()->startOfWeek();
                
                for ($i = 0; $i < 7; $i++) {
                    $currentDate = $weekStartDate->copy()->addDays($i);
                    
                    // Fixed: amount already represents net revenue after commission deduction
                    // اصلاح شده: amount از قبل نمایانگر درآمد خالص پس از کسر کمیسیون است
                    $revenue[$i] = BeautyTransaction::where('salon_id', $salonId)
                        ->whereIn('transaction_type', [
                            'commission',
                            'package_sale',
                            'cancellation_fee',
                            'consultation_fee',
                            'cross_selling',
                            'retail_sale',
                            'gift_card_sale'
                        ])
                        ->whereDate('created_at', $currentDate->format('Y-m-d'))
                        ->sum('amount');
                }
                
                $label = $days;
                break;

            case "this_month":
                $start = now()->startOfMonth();
                $totalDays = now()->daysInMonth;
                $weeks = array(
                    '"Day 1-7"',
                    '"Day 8-14"',
                    '"Day 15-21"',
                    '"Day 22-' . $totalDays . '"',
                );

                for ($i = 0; $i < 4; $i++) {
                    $end = $start->copy()->addDays(6);
                    
                    if ($i == 3) {
                        $end = now()->endOfMonth();
                    }
                    
                    // Fixed: amount already represents net revenue after commission deduction
                    // اصلاح شده: amount از قبل نمایانگر درآمد خالص پس از کسر کمیسیون است
                    $revenue[$i] = BeautyTransaction::where('salon_id', $salonId)
                        ->whereIn('transaction_type', [
                            'commission',
                            'package_sale',
                            'cancellation_fee',
                            'consultation_fee',
                            'cross_selling',
                            'retail_sale',
                            'gift_card_sale'
                        ])
                        ->whereBetween('created_at', ["{$start->format('Y-m-d')} 00:00:00", "{$end->format('Y-m-d')} 23:59:59"])
                        ->sum('amount');
                    
                    $start = $end->copy()->addDay();
                }
                
                $label = $weeks;
                break;

            default:
                for ($i = 0; $i < 12; $i++) {
                    $month = $i + 1;
                    // Fixed: amount already represents net revenue after commission deduction
                    // اصلاح شده: amount از قبل نمایانگر درآمد خالص پس از کسر کمیسیون است
                    $revenue[$i] = BeautyTransaction::where('salon_id', $salonId)
                        ->whereIn('transaction_type', [
                            'commission',
                            'package_sale',
                            'cancellation_fee',
                            'consultation_fee',
                            'cross_selling',
                            'retail_sale',
                            'gift_card_sale'
                        ])
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', now()->format('Y'))
                        ->sum('amount');
                }
                $label = $months;
        }

        return [
            'revenue' => $revenue,
            'label' => $label,
        ];
    }

    /**
     * Get revenue breakdown by model
     * دریافت تفکیک درآمد بر اساس مدل
     *
     * @param int $salonId
     * @return array
     */
    private function getRevenueBreakdown(int $salonId): array
    {
        // Fixed: amount already represents net revenue after commission deduction
        // commission field is for tracking purposes only
        // اصلاح شده: amount از قبل نمایانگر درآمد خالص پس از کسر کمیسیون است
        // فیلد commission فقط برای ردیابی است
        $breakdown = BeautyTransaction::where('salon_id', $salonId)
            ->selectRaw('transaction_type, SUM(amount) as total')
            ->whereIn('transaction_type', [
                'commission',
                'subscription',
                'advertisement',
                'service_fee',
                'package_sale',
                'cancellation_fee',
                'consultation_fee',
                'cross_selling',
                'retail_sale',
                'gift_card_sale',
            ])
            ->groupBy('transaction_type')
            ->get()
            ->pluck('total', 'transaction_type')
            ->map(function ($value) {
                return (float)($value ?? 0);
            })
            ->toArray();

        // Ensure all revenue models are present (even if 0)
        // اطمینان از وجود تمام مدل‌های درآمدی (حتی اگر 0 باشند)
        $allModels = [
            'commission' => 0,
            'subscription' => 0,
            'advertisement' => 0,
            'service_fee' => 0,
            'package_sale' => 0,
            'cancellation_fee' => 0,
            'consultation_fee' => 0,
            'cross_selling' => 0,
            'retail_sale' => 0,
            'gift_card_sale' => 0,
        ];

        return array_merge($allModels, $breakdown);
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
        $store = \App\CentralLogics\Helpers::get_store_data();
        
        if (!$store) {
            abort(404, translate('messages.store_not_found'));
        }
        
        $salon = BeautySalon::with('store')->where('store_id', $store->id)->first();
        
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

