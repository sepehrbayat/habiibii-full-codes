<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyTransaction;
use Modules\BeautyBooking\Entities\BeautySubscription;
use Modules\BeautyBooking\Services\BeautyRevenueService;

/**
 * Beauty Dashboard Controller (Admin)
 * کنترلر داشبورد (ادمین)
 */
class BeautyDashboardController extends Controller
{
    public function __construct(
        private BeautyRevenueService $revenueService
    ) {
        // Disable ONLY_FULL_GROUP_BY SQL mode to allow GROUP BY with all selected columns
        // غیرفعال کردن حالت ONLY_FULL_GROUP_BY برای اجازه GROUP BY با تمام ستون‌های انتخاب شده
        // This matches the pattern used in Rental module
        // این با الگوی استفاده شده در ماژول Rental هماهنگ است
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    }

    /**
     * Admin dashboard
     * داشبورد ادمین
     *
     * @param Request $request
     * @return Application|Factory|View|JsonResponse
     */
    public function dashboard(Request $request): Application|Factory|View|JsonResponse
    {
        $zone_id = $request->get('zone_id', 'all');
        $statistics_type = $request->get('statistics_type', 'all');
        $booking_overview = $request->get('booking_overview', 'all');
        $commission_overview = $request->get('commission_overview', 'all');

        // Booking statistics query with zone and time filtering
        // کوئری آمار رزرو با فیلتر منطقه و زمان
        $bookingQuery = BeautyBooking::when($zone_id != 'all', function ($query) use ($zone_id) {
            return $query->where('zone_id', $zone_id);
        });

        if ($statistics_type == 'this_year') {
            $bookingQuery->whereYear('created_at', now()->year);
        } elseif ($statistics_type == 'this_month') {
            $bookingQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($statistics_type == 'this_week') {
            $bookingQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        // Booking counts by status
        // تعداد رزروها بر اساس وضعیت
        $totalCount = (clone $bookingQuery)->count();
        $pendingCount = (clone $bookingQuery)->pending()->count();
        $confirmedCount = (clone $bookingQuery)->confirmed()->count();
        $completedCount = (clone $bookingQuery)->completed()->count();
        $cancelledCount = (clone $bookingQuery)->cancelled()->count();
        
        $zoneName = $zone_id == 'all' ? translate('All') : Zone::where('id', $zone_id)->value('name') ?? 'All';

        // Get dashboard data - pass zone_id explicitly to ensure consistency
        // دریافت داده‌های داشبورد - ارسال zone_id به صورت صریح برای اطمینان از سازگاری
        // Create request array with zone_id to maintain consistent filtering
        // ایجاد آرایه request با zone_id برای حفظ فیلترسازی سازگار
        $requestData = [
            'zone_id' => $zone_id,
            'commission_overview' => $commission_overview,
        ];
        $data = self::dashboard_data($requestData);
        $total_sell = $data['total_sell'];
        $commission = $data['commission'];
        $total_subs = $data['total_subs'];
        $topCustomers = $data['top_customers'];
        $topSalons = $data['top_salons'];
        $label = $data['label'];
        $grossEarning = collect($total_sell)->sum();

        // Handle AJAX requests
        // مدیریت درخواست‌های AJAX
        if ($request->ajax()) {
            return response()->json([
                'booking_statistics' => view('beautybooking::admin.partials.booking-statistics', compact('pendingCount', 'confirmedCount', 'completedCount', 'cancelledCount', 'totalCount'))->render(),
                'top_salons' => view('beautybooking::admin.partials.top-salons', compact('topSalons'))->render(),
                'top_customers' => view('beautybooking::admin.partials.top-customers', compact('topCustomers'))->render(),
                'sale_chart' => view('beautybooking::admin.partials.sale-chart')->render(),
                'by_booking_status' => view('beautybooking::admin.partials.by-booking-status', compact('pendingCount', 'confirmedCount', 'completedCount', 'cancelledCount', 'totalCount'))->render(),
                'zoneName' => $zoneName,
                'pendingCount' => $pendingCount,
                'confirmedCount' => $confirmedCount,
                'completedCount' => $completedCount,
                'cancelledCount' => $cancelledCount,
                'totalCount' => $totalCount,
                'total_sell' => array_map(function($val) {
                    return number_format((float)$val, 2, '.', '');
                }, array_values($total_sell)),
                'commission' => array_map(function($val) {
                    return number_format((float)$val, 2, '.', '');
                }, array_values($commission)),
                'total_subs' => array_map(function($val) {
                    return number_format((float)$val, 2, '.', '');
                }, array_values($total_subs)),
                'labels' => array_map(function($val) {
                    return trim($val, '"');
                }, $label),
                'grossEarning' => number_format((float)$grossEarning, 2, '.', '')
            ], 200);
        }

        // Set module_type for layout sidebar inclusion
        // تنظیم module_type برای شامل کردن سایدبار در layout
        $module_type = 'beauty';
        
        return view('beautybooking::admin.dashboard', compact(
            'pendingCount',
            'confirmedCount',
            'completedCount',
            'cancelledCount',
            'totalCount',
            'zoneName',
            'total_sell',
            'commission',
            'total_subs',
            'topCustomers',
            'topSalons',
            'label',
            'grossEarning',
            'module_type'
        ));
    }

    /**
     * Get booking statistics by status
     * دریافت آمار رزرو بر اساس وضعیت
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function byBookingStatus(Request $request): JsonResponse
    {
        $zone_id = $request->get('zone_id', 'all');
        $type = $request->get('booking_overview', 'all');

        $bookingQuery = BeautyBooking::when($zone_id != 'all', function ($query) use ($zone_id) {
            return $query->where('zone_id', $zone_id);
        });

        if ($type == 'this_year') {
            $bookingQuery->whereYear('created_at', now()->year);
        } elseif ($type == 'this_month') {
            $bookingQuery->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($type == 'this_week') {
            $bookingQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        $totalCount = $bookingQuery->count();
        $pendingCount = (clone $bookingQuery)->pending()->count();
        $confirmedCount = (clone $bookingQuery)->confirmed()->count();
        $completedCount = (clone $bookingQuery)->completed()->count();
        $cancelledCount = (clone $bookingQuery)->cancelled()->count();

        return response()->json([
            'view' => view('beautybooking::admin.partials.by-booking-status', compact('pendingCount', 'confirmedCount', 'completedCount', 'cancelledCount', 'totalCount'))->render(),
            'pendingCount' => $pendingCount,
            'confirmedCount' => $confirmedCount,
            'completedCount' => $completedCount,
            'cancelledCount' => $cancelledCount,
            'totalCount' => $totalCount
        ], 200);
    }

    /**
     * Get commission overview chart data
     * دریافت داده‌های نمودار نمای کلی کمیسیون
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function commissionOverview(Request $request): JsonResponse
    {
        $data = self::dashboard_data($request);
        $total_sell = $data['total_sell'];
        $commission = $data['commission'];
        $total_subs = $data['total_subs'];
        $label = $data['label'];
        $grossEarning = collect($total_sell)->sum();

        return response()->json([
            'view' => view('beautybooking::admin.partials.sale-chart')->render(),
            'grossEarning' => $grossEarning,
            'total_sell' => array_map(function($val) {
                return number_format((float)$val, 2, '.', '');
            }, array_values($total_sell)),
            'commission' => array_map(function($val) {
                return number_format((float)$val, 2, '.', '');
            }, array_values($commission)),
            'total_subs' => array_map(function($val) {
                return number_format((float)$val, 2, '.', '');
            }, array_values($total_subs)),
            'labels' => array_map(function($val) {
                return trim($val, '"');
            }, $label)
        ], 200);
    }

    /**
     * Get dashboard data helper method
     * متد کمک‌کننده دریافت داده‌های داشبورد
     *
     * @param Request|array $request
     * @return array
     */
    public static function dashboard_data($request): array
    {
        // Helper function to get value from Request object or array
        // تابع کمک‌کننده برای دریافت مقدار از Request object یا array
        $getValue = function($key, $default = null) use ($request) {
            if ($request instanceof Request) {
                return $request->get($key, $default);
            }
            return $request[$key] ?? $default;
        };

        // Top customers by booking count
        // مشتریان برتر بر اساس تعداد رزرو
        // Using withCount relationship method for better SQL compatibility
        // استفاده از متد withCount برای سازگاری بهتر SQL
        $zoneId = $getValue('zone_id', 'all');
        $topCustomers = User::withCount(['beautyBookings as booking_count' => function($query) use ($zoneId) {
                if (is_numeric($zoneId)) {
                    $query->where('zone_id', $zoneId);
                }
            }])
            ->when(is_numeric($zoneId), function ($q) use ($zoneId) {
                // Filter by zone if bookings have zone_id
                // فیلتر بر اساس منطقه اگر رزروها zone_id داشته باشند
                return $q->whereHas('beautyBookings', function($query) use ($zoneId) {
                    $query->where('zone_id', $zoneId);
                });
            })
            ->having('booking_count', '>', 0)
            ->orderBy('booking_count', 'desc')
            ->take(5)
            ->get();

        // Top salons by booking count
        // سالن‌های برتر بر اساس تعداد رزرو
        $topSalons = BeautySalon::with(['store'])
            ->when(is_numeric($zoneId), function ($q) use ($zoneId) {
                return $q->where('beauty_salons.zone_id', $zoneId);
            })
            ->withCount(['bookings as booking_count' => function($query) use ($zoneId) {
                if (is_numeric($zoneId)) {
                    $query->where('zone_id', $zoneId);
                }
            }])
            ->having('booking_count', '>', 0)
            ->orderBy('booking_count', 'desc')
            ->take(5)
            ->get();

        // Chart labels
        // برچسب‌های نمودار
        $months = array(
            '"'.translate('Jan').'"',
            '"'.translate('Feb').'"',
            '"'.translate('Mar').'"',
            '"'.translate('Apr').'"',
            '"'.translate('May').'"',
            '"'.translate('Jun').'"',
            '"'.translate('Jul').'"',
            '"'.translate('Aug').'"',
            '"'.translate('Sep').'"',
            '"'.translate('Oct').'"',
            '"'.translate('Nov').'"',
            '"'.translate('Dec').'"'
        );
        $days = array(
            '"'.translate('Mon').'"',
            '"'.translate('Tue').'"',
            '"'.translate('Wed').'"',
            '"'.translate('Thu').'"',
            '"'.translate('Fri').'"',
            '"'.translate('Sat').'"',
            '"'.translate('Sun').'"',
        );
        
        $total_sell = [];
        $commission = [];
        $total_subs = [];

        $commission_overview = $getValue('commission_overview', 'all');

        switch ($commission_overview) {
            case "this_year":
                for ($i = 0; $i < 12; $i++) {
                    $month = $i + 1; // Month number (1-12)
                    $total_sell[$i] = BeautyTransaction::when(is_numeric($zoneId), function ($q) use ($zoneId) {
                            return $q->where('zone_id', $zoneId);
                        })
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', now()->format('Y'))
                        ->where('transaction_type', '!=', 'refund')
                        ->sum('amount');

                    $commission[$i] = BeautyTransaction::when(is_numeric($zoneId), function ($q) use ($zoneId) {
                            return $q->where('zone_id', $zoneId);
                        })
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', now()->format('Y'))
                        ->where('transaction_type', 'commission')
                        ->sum('amount');

                    $total_subs[$i] = BeautySubscription::when(is_numeric($zoneId), function($q) use ($zoneId){
                        return $q->whereHas('salon', function($query) use ($zoneId){
                            return $query->where('zone_id', $zoneId);
                        });
                    })
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', now()->format('Y'))
                    ->where('status', 'active')
                    ->sum('amount_paid');
                }
                $label = $months;
                break;

            case "this_week":
                $weekStartDate = now()->startOfWeek();

                for ($i = 0; $i < 7; $i++) {
                    $currentDate = $weekStartDate->copy()->addDays($i);

                    $total_sell[$i] = BeautyTransaction::when(is_numeric($zoneId), function ($q) use ($zoneId) {
                            return $q->where('zone_id', $zoneId);
                        })
                        ->whereDate('created_at', $currentDate->format('Y-m-d'))
                        ->where('transaction_type', '!=', 'refund')
                        ->sum('amount');

                    $commission[$i] = BeautyTransaction::when(is_numeric($zoneId), function ($q) use ($zoneId) {
                            return $q->where('zone_id', $zoneId);
                        })
                        ->whereDate('created_at', $currentDate->format('Y-m-d'))
                        ->where('transaction_type', 'commission')
                        ->sum('amount');

                    $total_subs[$i] = BeautySubscription::when(is_numeric($zoneId), function($q) use ($zoneId){
                        return $q->whereHas('salon', function($query) use ($zoneId){
                            return $query->where('zone_id', $zoneId);
                        });
                    })
                    ->whereDate('created_at', $currentDate->format('Y-m-d'))
                    ->where('status', 'active')
                    ->sum('amount_paid');
                }

                $label = $days;
                break;

            case "this_month":
                $start = now()->startOfMonth();
                $total_days = now()->daysInMonth;
                $weeks = array(
                    '"Day 1-7"',
                    '"Day 8-14"',
                    '"Day 15-21"',
                    '"Day 22-' . $total_days . '"',
                );

                for ($i = 0; $i < 4; $i++) {
                    $end = $start->copy()->addDays(6);

                    if ($i == 3) {
                        $end = now()->endOfMonth();
                    }

                    $total_sell[$i] = BeautyTransaction::when(is_numeric($zoneId), function ($q) use ($zoneId) {
                            return $q->where('zone_id', $zoneId);
                        })
                        ->whereBetween('created_at', ["{$start->format('Y-m-d')} 00:00:00", "{$end->format('Y-m-d')} 23:59:59"])
                        ->where('transaction_type', '!=', 'refund')
                        ->sum('amount');

                    $commission[$i] = BeautyTransaction::when(is_numeric($zoneId), function ($q) use ($zoneId) {
                            return $q->where('zone_id', $zoneId);
                        })
                        ->whereBetween('created_at', ["{$start->format('Y-m-d')} 00:00:00", "{$end->format('Y-m-d')} 23:59:59"])
                        ->where('transaction_type', 'commission')
                        ->sum('amount');

                    $total_subs[$i] = BeautySubscription::when(is_numeric($zoneId), function($q) use ($zoneId){
                        return $q->whereHas('salon', function($query) use ($zoneId){
                            return $query->where('zone_id', $zoneId);
                        });
                    })
                    ->whereBetween('created_at', ["{$start->format('Y-m-d')} 00:00:00", "{$end->format('Y-m-d')} 23:59:59"])
                    ->where('status', 'active')
                    ->sum('amount_paid');

                    $start = $end->copy()->addDay();
                }

                $label = $weeks;
                break;

            default:
                for ($i = 0; $i < 12; $i++) {
                    $month = $i + 1; // Month number (1-12)
                    $total_sell[$i] = BeautyTransaction::when(is_numeric($zoneId), function ($q) use ($zoneId) {
                            return $q->where('zone_id', $zoneId);
                        })
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', now()->format('Y'))
                        ->where('transaction_type', '!=', 'refund')
                        ->sum('amount');

                    $commission[$i] = BeautyTransaction::when(is_numeric($zoneId), function ($q) use ($zoneId) {
                            return $q->where('zone_id', $zoneId);
                        })
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', now()->format('Y'))
                        ->where('transaction_type', 'commission')
                        ->sum('amount');

                    $total_subs[$i] = BeautySubscription::when(is_numeric($zoneId), function($q) use ($zoneId){
                        return $q->whereHas('salon', function($query) use ($zoneId){
                            return $query->where('zone_id', $zoneId);
                        });
                    })
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', now()->format('Y'))
                    ->where('status', 'active')
                    ->sum('amount_paid');
                }
                $label = $months;
        }

        $dash_data['top_salons'] = $topSalons;
        $dash_data['top_customers'] = $topCustomers;
        $dash_data['total_sell'] = $total_sell;
        $dash_data['commission'] = $commission;
        $dash_data['total_subs'] = $total_subs;
        $dash_data['label'] = $label;

        return $dash_data;
    }
}