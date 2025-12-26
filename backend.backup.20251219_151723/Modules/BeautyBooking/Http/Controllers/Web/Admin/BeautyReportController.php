<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyTransaction;
use Modules\BeautyBooking\Entities\BeautyMonthlyReport;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyPackageUsage;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign;
use Modules\BeautyBooking\Entities\BeautyLoyaltyPoint;
use Modules\BeautyBooking\Services\BeautyRevenueService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Beauty Report Controller (Admin)
 * کنترلر گزارش‌ها (ادمین)
 */
class BeautyReportController extends Controller
{
    public function __construct(
        private BeautyRevenueService $revenueService
    ) {}

    /**
     * Financial reports
     * گزارش‌های مالی
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function financial(Request $request)
    {
        $query = BeautyTransaction::query();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        $transactions = $query->with(['salon', 'booking'])->latest()->paginate(config('default_pagination'));

        $totalCommission = BeautyTransaction::where('transaction_type', 'commission')
            ->sum('commission');
        
        $totalServiceFee = BeautyTransaction::where('transaction_type', 'service_fee')
            ->sum('service_fee');

        return view('beautybooking::admin.report.financial', compact('transactions', 'totalCommission', 'totalServiceFee'));
    }

    /**
     * Monthly summary report
     * گزارش خلاصه ماهانه
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function monthlySummary(Request $request)
    {
        $year = (int) $request->get('year', (int) now()->year);

        $labels = [];
        $bookingSeries = [];
        $revenueSeries = [];
        $serviceFeeSeries = [];
        $commissionSeries = [];
        $cancellationRateSeries = [];
        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = Carbon::create($year, $month, 1)->format('M');

            $baseQuery = BeautyBooking::whereYear('booking_date', $year)
                ->whereMonth('booking_date', $month);

            $bookingCount = (clone $baseQuery)->count();
            $completedRevenue = (clone $baseQuery)->where('status', 'completed')->sum('total_amount');
            $serviceFee = (clone $baseQuery)->sum('service_fee');
            $commission = (clone $baseQuery)->sum('commission_amount');
            $cancellations = (clone $baseQuery)->where('status', 'cancelled')->count();
            $newCustomers = (clone $baseQuery)->distinct('user_id')->count('user_id');
            $cancellationRate = $bookingCount > 0 ? round(($cancellations / $bookingCount) * 100, 2) : 0.0;

            $bookingSeries[] = $bookingCount;
            $revenueSeries[] = (float) $completedRevenue;
            $serviceFeeSeries[] = (float) $serviceFee;
            $commissionSeries[] = (float) $commission;
            $cancellationRateSeries[] = $cancellationRate;

            $monthlyData[] = [
                'label' => Carbon::create($year, $month, 1)->format('F'),
                'bookings' => $bookingCount,
                'revenue' => $completedRevenue,
                'service_fee' => $serviceFee,
                'commission' => $commission,
                'cancellations' => $cancellations,
                'cancellation_rate' => $cancellationRate,
                'new_customers' => $newCustomers,
            ];
        }

        $totals = [
            'bookings' => array_sum($bookingSeries),
            'revenue' => array_sum($revenueSeries),
            'service_fee' => array_sum($serviceFeeSeries),
            'commission' => array_sum($commissionSeries),
            'avg_cancellation_rate' => count($cancellationRateSeries) > 0
                ? round(array_sum($cancellationRateSeries) / count($cancellationRateSeries), 2)
                : 0.0,
        ];

        $chartData = [
            'labels' => $labels,
            'bookings' => $bookingSeries,
            'revenue' => $revenueSeries,
            'service_fee' => $serviceFeeSeries,
            'commission' => $commissionSeries,
            'cancellation_rate' => $cancellationRateSeries,
        ];

        return view('beautybooking::admin.report.monthly-summary', compact(
            'year',
            'monthlyData',
            'chartData',
            'totals'
        ));
    }

    /**
     * Top Rated Salons list
     * لیست سالن‌های دارای رتبه بالا
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function topRated(Request $request)
    {
        $year = (int) $request->get('year', (int) Carbon::now()->subMonth()->year);
        $month = (int) $request->get('month', (int) Carbon::now()->subMonth()->month);
        
        // Try to get monthly report from database
        // تلاش برای دریافت گزارش ماهانه از دیتابیس
        $monthlyReport = BeautyMonthlyReport::byType('top_rated_salons')
            ->byPeriod($year, $month)
            ->first();
        
        if ($monthlyReport && !empty($monthlyReport->salon_ids)) {
            // Get salons from monthly report
            // دریافت سالن‌ها از گزارش ماهانه
            $salons = BeautySalon::whereIn('id', $monthlyReport->salon_ids)
                ->with(['store', 'badges'])
                ->get()
                ->sortBy(function($salon) use ($monthlyReport) {
                    return array_search($salon->id, $monthlyReport->salon_ids);
                })
                ->values();
        } else {
            // Fallback to real-time calculation
            // بازگشت به محاسبه در زمان واقعی
            $salons = BeautySalon::topRated()
                ->with(['store', 'badges'])
                ->orderByDesc('avg_rating')
                ->limit(50)
                ->get();
        }

        return view('beautybooking::admin.report.top-rated', compact('salons', 'year', 'month', 'monthlyReport'));
    }

    /**
     * Trending Clinics list
     * لیست کلینیک‌های ترند
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function trending(Request $request)
    {
        $year = (int) $request->get('year', (int) Carbon::now()->subMonth()->year);
        $month = (int) $request->get('month', (int) Carbon::now()->subMonth()->month);
        
        // Try to get monthly report from database
        // تلاش برای دریافت گزارش ماهانه از دیتابیس
        $monthlyReport = BeautyMonthlyReport::byType('trending_clinics')
            ->byPeriod($year, $month)
            ->first();
        
        if ($monthlyReport && !empty($monthlyReport->salon_ids)) {
            // Get clinics from monthly report
            // دریافت کلینیک‌ها از گزارش ماهانه
            $salons = BeautySalon::whereIn('id', $monthlyReport->salon_ids)
                ->where('business_type', 'clinic')
                ->with(['store', 'badges'])
                ->get()
                ->sortBy(function($salon) use ($monthlyReport) {
                    return array_search($salon->id, $monthlyReport->salon_ids);
                })
                ->values();
        } else {
            // Fallback to real-time calculation
            // بازگشت به محاسبه در زمان واقعی
            $dateFrom = Carbon::create($year, $month, 1)->startOfMonth();
            $dateTo = Carbon::create($year, $month, 1)->endOfMonth();
            
            $salons = BeautySalon::where('business_type', 'clinic')
                ->whereHas('bookings', function($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('booking_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
                      ->where('status', '!=', 'cancelled');
                })
                ->withCount(['bookings' => function($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('booking_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
                      ->where('status', '!=', 'cancelled');
                }])
                ->with(['store', 'badges'])
                ->orderByDesc('bookings_count')
                ->limit(50)
                ->get();
        }

        return view('beautybooking::admin.report.trending', compact('salons', 'year', 'month', 'monthlyReport'));
    }

    /**
     * Revenue breakdown by model
     * تفکیک درآمد بر اساس مدل
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function revenueBreakdown(Request $request)
    {
        $dateFrom = $request->filled('date_from') 
            ? Carbon::parse($request->date_from) 
            : Carbon::now()->startOfMonth();
        
        $dateTo = $request->filled('date_to') 
            ? Carbon::parse($request->date_to) 
            : Carbon::now()->endOfMonth();

        $revenueByModel = $this->revenueService->getRevenueBreakdown($dateFrom, $dateTo);
        
        $totalRevenue = array_sum($revenueByModel);

        return view('beautybooking::admin.report.revenue-breakdown', compact(
            'revenueByModel',
            'totalRevenue',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Package usage report
     * گزارش استفاده از پکیج‌ها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function packageUsage(Request $request)
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : Carbon::now()->subDays(30);

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : Carbon::now();

        $packageSalesQuery = BeautyBooking::whereNotNull('package_id')
            ->whereBetween('booking_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')]);

        $packagesSold = (int) $packageSalesQuery->count();
        $packageRevenue = (float) $packageSalesQuery->sum('total_amount');

        $sessionsRedeemed = (int) BeautyPackageUsage::whereBetween('used_at', [$dateFrom, $dateTo])
            ->where('status', 'used')
            ->count();

        $pendingSessions = (int) BeautyPackageUsage::where('status', 'pending')->count();

        $stats = [
            'total_packages' => BeautyPackage::count(),
            'active_packages' => BeautyPackage::where('status', 1)->count(),
            'packages_sold' => $packagesSold,
            'package_revenue' => $packageRevenue,
            'sessions_redeemed' => $sessionsRedeemed,
            'pending_sessions' => $pendingSessions,
        ];

        $topPackages = BeautyBooking::with(['package.salon.store'])
            ->select('package_id',
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(total_amount) as total_revenue')
            )
            ->whereNotNull('package_id')
            ->whereBetween('booking_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
            ->groupBy('package_id')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        $usageTrendRaw = BeautyPackageUsage::selectRaw('DATE(used_at) as usage_date, COUNT(*) as total')
            ->where('status', 'used')
            ->whereBetween('used_at', [$dateFrom, $dateTo])
            ->groupBy('usage_date')
            ->orderBy('usage_date')
            ->get();

        $usageTrend = [
            'labels' => $usageTrendRaw->pluck('usage_date')->map(fn ($date) => Carbon::parse($date)->format('M d')),
            'data' => $usageTrendRaw->pluck('total'),
        ];

        return view('beautybooking::admin.report.package-usage', compact(
            'stats',
            'topPackages',
            'usageTrend',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Loyalty campaign statistics
     * گزارش آمار کمپین‌های وفاداری
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function loyaltyStats(Request $request)
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : Carbon::now()->subDays(30);

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : Carbon::now();

        $pointsEarned = (int) BeautyLoyaltyPoint::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('points', '>', 0)
            ->sum('points');

        $pointsRedeemed = (int) abs(BeautyLoyaltyPoint::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('points', '<', 0)
            ->sum('points'));

        $activeCampaigns = BeautyLoyaltyCampaign::active()->count();
        $totalParticipants = BeautyLoyaltyCampaign::sum('total_participants');

        $topCampaigns = BeautyLoyaltyCampaign::with('salon.store')
            ->withSum([
                'loyaltyPoints' => function ($query) use ($dateFrom, $dateTo) {
                    $query->where('points', '>', 0)
                        ->whereBetween('created_at', [$dateFrom, $dateTo]);
                }
            ], 'points')
            ->get()
            ->map(function ($campaign) {
                $campaign->points_earned = (int) ($campaign->loyalty_points_sum_points ?? 0);
                return $campaign;
            })
            ->sortByDesc(function ($campaign) {
                return $campaign->points_earned;
            })
            ->take(10)
            ->values();

        $pointsTrendRaw = BeautyLoyaltyPoint::selectRaw('DATE(created_at) as trend_date, SUM(points) as total_points')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('trend_date')
            ->orderBy('trend_date')
            ->get();

        $pointsTrend = [
            'labels' => $pointsTrendRaw->pluck('trend_date')->map(fn ($date) => Carbon::parse($date)->format('M d'))->values()->toArray(),
            'data' => $pointsTrendRaw->pluck('total_points')->values()->toArray(),
        ];

        $stats = [
            'points_earned' => $pointsEarned,
            'points_redeemed' => $pointsRedeemed,
            'active_campaigns' => $activeCampaigns,
            'total_participants' => $totalParticipants,
            'conversion_rate' => $pointsEarned > 0
                ? round(($pointsRedeemed / $pointsEarned) * 100, 2)
                : 0.0,
        ];

        return view('beautybooking::admin.report.loyalty-stats', compact(
            'stats',
            'pointsTrend',
            'topCampaigns',
            'dateFrom',
            'dateTo'
        ));
    }
}

