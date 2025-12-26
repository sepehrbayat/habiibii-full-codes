<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\SubscriptionPackage;
use Modules\BeautyBooking\Entities\BeautySubscription;
use Modules\BeautyBooking\Entities\BeautySalon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautySubscriptionExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Subscription Controller (Admin)
 * کنترلر اشتراک زیبایی (ادمین)
 */
class BeautySubscriptionController extends Controller
{
    public function __construct(
        private BeautySubscription $subscription
    ) {}

    /**
     * List active subscriptions
     * لیست اشتراک‌های فعال
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        $subscriptions = $this->subscription->with(['salon.store'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->whereHas('salon.store', function($storeQuery) use ($key) {
                        $storeQuery->where('name', 'LIKE', '%' . $key . '%');
                    });
                }
            })
            ->when($request->filled('subscription_type'), function ($query) use ($request) {
                $query->where('subscription_type', $request->subscription_type);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->active();
                } elseif ($request->status === 'expired') {
                    $query->expired();
                } else {
                    $query->where('status', $request->status);
                }
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $salons = BeautySalon::with('store')->get();

        return view('beautybooking::admin.subscription.index', compact('subscriptions', 'salons'));
    }

    /**
     * Banner ads management
     * مدیریت تبلیغات بنر
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function ads(Request $request)
    {
        $ads = $this->subscription->with(['salon.store'])
            ->whereIn('subscription_type', ['banner_homepage', 'banner_category', 'banner_search_results'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->whereHas('salon.store', function($storeQuery) use ($key) {
                        $storeQuery->where('name', 'LIKE', '%' . $key . '%');
                    });
                }
            })
            ->when($request->filled('ad_position'), function ($query) use ($request) {
                $query->where('ad_position', $request->ad_position);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::admin.subscription.ads', compact('ads'));
    }

    /**
     * List available beauty subscription plans
     * لیست پلن‌های اشتراک مخصوص زیبایی
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function plans()
    {
        $plans = SubscriptionPackage::where(function ($q) {
                $q->where('module_type', 'beauty')->orWhere('module_type', 'all')->orWhereNull('module_type');
            })
            ->orderByDesc('default')
            ->orderBy('price')
            ->get();

        return view('beautybooking::admin.subscription.plans', compact('plans'));
    }

    /**
     * Export subscriptions
     * خروجی گرفتن از اشتراک‌ها
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $subscriptions = $this->subscription->with(['salon.store'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->whereHas('salon.store', function($storeQuery) use ($key) {
                        $storeQuery->where('name', 'LIKE', '%' . $key . '%');
                    });
                }
            })
            ->when($request->filled('subscription_type'), function ($query) use ($request) {
                $query->where('subscription_type', $request->subscription_type);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->active();
                } elseif ($request->status === 'expired') {
                    $query->expired();
                } else {
                    $query->where('status', $request->status);
                }
            })
            ->latest()
            ->get();

        $data = [
            'fileName' => 'Subscriptions',
            'data' => $subscriptions,
            'search' => $request->search ?? null,
        ];

        // Use input() to properly read query parameter type
        // استفاده از input() برای خواندن صحیح پارامتر type از query string
        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautySubscriptionExport($data), 'Subscriptions.csv');
        }
        return Excel::download(new BeautySubscriptionExport($data), 'Subscriptions.xlsx');
    }
}

