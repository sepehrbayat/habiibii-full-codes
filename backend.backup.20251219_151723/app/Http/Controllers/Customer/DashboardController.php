<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CustomerDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Customer Dashboard Controller
 * کنترلر داشبورد مشتری
 *
 * Main customer dashboard that aggregates data from all modules
 * داشبورد اصلی مشتری که داده‌ها را از تمام ماژول‌ها جمع‌آوری می‌کند
 */
class DashboardController extends Controller
{
    public function __construct(
        private CustomerDashboardService $dashboardService
    ) {
        // Disable ONLY_FULL_GROUP_BY SQL mode to allow GROUP BY with all selected columns
        // غیرفعال کردن حالت ONLY_FULL_GROUP_BY برای اجازه GROUP BY با تمام ستون‌های انتخاب شده
        // This matches the pattern used in Admin and Vendor beauty dashboards
        // این با الگوی استفاده شده در داشبوردهای زیبایی Admin و Vendor هماهنگ است
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    }

    /**
     * Display customer dashboard
     * نمایش داشبورد مشتری
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Initialize beauty data as empty array
        // مقداردهی اولیه داده‌های زیبایی به عنوان آرایه خالی
        $beautyData = [];
        $beautyStats = [];
        $beautyWidgets = [];
        $beautyActivity = [];
        $beautyCharts = [];

        // Check if BeautyBooking module is active
        // بررسی اینکه آیا ماژول BeautyBooking فعال است
        if (addon_published_status('BeautyBooking')) {
            try {
                $beautyStats = $this->dashboardService->getBeautyStatistics($user);
                $beautyWidgets = $this->dashboardService->getBeautyWidgets($user);
                $beautyActivity = $this->dashboardService->getBeautyActivityFeed($user, 10);
                $beautyCharts = $this->dashboardService->getBeautyChartData($user);
                
                $beautyData = [
                    'stats' => $beautyStats,
                    'widgets' => $beautyWidgets,
                    'activity' => $beautyActivity,
                    'charts' => $beautyCharts,
                ];
            } catch (\Exception $e) {
                Log::error('Beauty dashboard data error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                // Set empty defaults to prevent view errors
                // تنظیم مقادیر پیش‌فرض خالی برای جلوگیری از خطاهای view
                $beautyStats = [];
                $beautyWidgets = [
                    'upcoming_bookings' => collect([]),
                    'active_packages' => collect([]),
                    'active_gift_cards' => collect([]),
                    'recent_loyalty_points' => collect([]),
                    'pending_reviews' => collect([]),
                    'recent_retail_orders' => collect([]),
                ];
                $beautyActivity = collect([]);
                $beautyCharts = [];
                $beautyData = [
                    'stats' => $beautyStats,
                    'widgets' => $beautyWidgets,
                    'activity' => $beautyActivity,
                    'charts' => $beautyCharts,
                ];
            }
        } else {
            // Ensure variables are set even when module is disabled
            // اطمینان از تنظیم متغیرها حتی زمانی که ماژول غیرفعال است
            $beautyStats = [];
            $beautyWidgets = [
                'upcoming_bookings' => collect([]),
                'active_packages' => collect([]),
                'active_gift_cards' => collect([]),
                'recent_loyalty_points' => collect([]),
                'pending_reviews' => collect([]),
                'recent_retail_orders' => collect([]),
            ];
            $beautyActivity = collect([]);
            $beautyCharts = [];
            $beautyData = [];
        }

        return view('customer.dashboard.index', compact('beautyData', 'beautyStats', 'beautyWidgets', 'beautyActivity', 'beautyCharts'));
    }

    /**
     * Get beauty statistics (AJAX endpoint)
     * دریافت آمار زیبایی (نقطه پایانی AJAX)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBeautyStats(Request $request): JsonResponse
    {
        if (!addon_published_status('BeautyBooking')) {
            return response()->json(['stats' => []], 200);
        }

        try {
            $user = $request->user();
            $stats = $this->dashboardService->getBeautyStatistics($user);
            
            return response()->json(['stats' => $stats], 200);
        } catch (\Exception $e) {
            Log::error('Beauty stats AJAX error: ' . $e->getMessage());
            return response()->json(['stats' => [], 'error' => translate('Failed to load statistics')], 500);
        }
    }

    /**
     * Get beauty activity feed (AJAX endpoint)
     * دریافت فید فعالیت‌های زیبایی (نقطه پایانی AJAX)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBeautyActivity(Request $request): JsonResponse
    {
        if (!addon_published_status('BeautyBooking')) {
            return response()->json(['activity' => []], 200);
        }

        try {
            $user = $request->user();
            $limit = (int)($request->get('limit', 10));
            $activity = $this->dashboardService->getBeautyActivityFeed($user, $limit);
            
            return response()->json(['activity' => $activity], 200);
        } catch (\Exception $e) {
            Log::error('Beauty activity AJAX error: ' . $e->getMessage());
            return response()->json(['activity' => [], 'error' => translate('Failed to load activity')], 500);
        }
    }

    /**
     * Get beauty bookings widget (AJAX endpoint)
     * دریافت ویجت رزروهای زیبایی (نقطه پایانی AJAX)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBeautyBookings(Request $request): JsonResponse
    {
        if (!addon_published_status('BeautyBooking')) {
            return response()->json(['bookings' => []], 200);
        }

        try {
            $user = $request->user();
            $widgets = $this->dashboardService->getBeautyWidgets($user);
            
            return response()->json([
                'bookings' => $widgets['upcoming_bookings'] ?? [],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Beauty bookings AJAX error: ' . $e->getMessage());
            return response()->json(['bookings' => [], 'error' => translate('Failed to load bookings')], 500);
        }
    }
}

