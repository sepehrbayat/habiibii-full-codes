<?php

namespace App\Http\Controllers\Vendor;

use Carbon\Carbon;
use App\Models\Item;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\OrderTransaction;
use Illuminate\Support\Facades\DB;
use Modules\Rental\Entities\Trips;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $store = Helpers::get_store_data();
        
        // If store is null, redirect to error or show default dashboard
        // اگر store null باشد، به خطا هدایت شود یا داشبورد پیش‌فرض نمایش داده شود
        if (!$store) {
            return view('vendor-views.dashboard', [
                'data' => [],
                'earning' => [],
                'commission' => [],
                'params' => ['statistics_type' => 'overall'],
                'out_of_stock_count' => null,
                'item' => null
            ]);
        }
        
        // Check if vendor has selected a specific module via session
        // بررسی اینکه آیا فروشنده ماژول خاصی را از طریق session انتخاب کرده است
        $selectedModule = session('vendor_selected_module');
        
        // Check module access based on session or primary module
        // بررسی دسترسی ماژول بر اساس session یا ماژول اصلی
        if ($selectedModule === 'rental' && $store && method_exists($store, 'hasModuleAccess') && $store->hasModuleAccess('rental')) {
            return to_route('vendor.providerDashboard');
        }
        
        if ($selectedModule === 'beauty' && $store && method_exists($store, 'hasModuleAccess') && $store->hasModuleAccess('beauty') && addon_published_status('BeautyBooking') == 1) {
            return to_route('vendor.beautybooking.dashboard');
        }
        
        // Default behavior: redirect based on primary module
        // رفتار پیش‌فرض: هدایت بر اساس ماژول اصلی
        if($store && isset($store->module_type) && $store->module_type == 'rental'){
            return to_route('vendor.providerDashboard');
        }
        
        // Redirect to beauty booking dashboard if module is beauty
        // هدایت به داشبورد رزرو زیبایی در صورت ماژول beauty
        if($store && isset($store->module_type) && $store->module_type == 'beauty' && addon_published_status('BeautyBooking') == 1){
            return to_route('vendor.beautybooking.dashboard');
        }
        $params = [
            'statistics_type' => $request['statistics_type'] ?? 'overall'
        ];
        session()->put('dash_params', $params);

        $data = self::dashboard_order_stats_data();
        $earning = [];
        $commission = [];
        $from = Carbon::now()->startOfYear()->format('Y-m-d');
        $to = Carbon::now()->endOfYear()->format('Y-m-d');
        $storeData = Helpers::get_store_data();
        if (!$storeData) {
            return view('vendor-views.dashboard', [
                'data' => $data,
                'earning' => $earning,
                'commission' => $commission,
                'params' => $params,
                'out_of_stock_count' => null,
                'item' => null
            ]);
        }
        $store_earnings = OrderTransaction::NotRefunded()->where(['vendor_id' => $storeData->vendor_id])->select(
            DB::raw('IFNULL(sum(store_amount),0) as earning'),
            DB::raw('IFNULL(sum(admin_commission + admin_expense - delivery_fee_comission),0) as commission'),
            DB::raw('YEAR(created_at) year, MONTH(created_at) month')
        )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();
        for ($inc = 1; $inc <= 12; $inc++) {
            $earning[$inc] = 0;
            $commission[$inc] = 0;
            foreach ($store_earnings as $match) {
                if ($match['month'] == $inc) {
                    $earning[$inc] = $match['earning'];
                    $commission[$inc] = $match['commission'];
                }
            }
        }

        $top_sell = Item::orderBy("order_count", 'desc')
            ->take(6)
            ->get();
        $most_rated_items = Item::where('avg_rating' ,'>' ,0)
        ->orderBy('avg_rating','desc')
        ->take(6)
        ->get();
        $data['top_sell'] = $top_sell;
        $data['most_rated_items'] = $most_rated_items;

        $storeForStock = Helpers::get_store_data();
        if( $storeForStock && $storeForStock->storeConfig && $storeForStock->storeConfig->minimum_stock_for_warning > 0){
            $items=  Item::where('stock' ,'<=' , $storeForStock->storeConfig->minimum_stock_for_warning );
        } else{
            $items=  Item::where('stock',0 );
        }

        $out_of_stock_count=  ($storeForStock && $storeForStock->module && $storeForStock->module->module_type != 'food') ?  $items->orderby('stock')->latest()->count() : null;

        $item = null;
        if($out_of_stock_count == 1 ){
            $item= $items->orderby('stock')->latest()->first();
        }

        return view('vendor-views.dashboard', compact('data', 'earning', 'commission', 'params','out_of_stock_count','item'));
    }

    public function store_data()
    {
        $store= Helpers::get_store_data();
        if (!$store) {
            return response()->json([
                'new_pending_order' => 0,
                'new_confirmed_order' => 0,
            ], 200);
        }
        if($store->module_type == 'rental'){
            $type='trip';
            $new_pending_order=Trips::where(['checked' => 0])->where('provider_id', $store->id)->count();
            $new_confirmed_order = 0; // Initialize for rental module
            // توجه: برای ماژول rental، new_confirmed_order مقداردهی اولیه می‌شود

        } elseif($store->module_type == 'beauty' && addon_published_status('BeautyBooking') == 1){
            // Handle beauty booking module
            // مدیریت ماژول رزرو زیبایی
            $type='beauty_booking';
            $salon = $store->beautySalon;
            if($salon){
                $new_pending_order = \Modules\BeautyBooking\Entities\BeautyBooking::where(['checked' => 0])
                    ->where('salon_id', $salon->id)
                    ->where('status', 'pending')
                    ->count();
                $new_confirmed_order = \Modules\BeautyBooking\Entities\BeautyBooking::where(['checked' => 0])
                    ->where('salon_id', $salon->id)
                    ->whereIn('status', ['confirmed', 'accepted'])
                    ->count();
            } else {
                $new_pending_order = 0;
                $new_confirmed_order = 0;
            }
        } else{
            $new_pending_order = DB::table('orders')->where(['checked' => 0])->where('store_id', $store->id)->where('order_status','pending');
            if(config('order_confirmation_model') != 'store' && !$store->sub_self_delivery)
            {
                $new_pending_order = $new_pending_order->where('order_type', 'take_away');
            }
            $new_pending_order = $new_pending_order->count();
            $new_confirmed_order = DB::table('orders')->where(['checked' => 0])->where('store_id', $store->id)->whereIn('order_status',['confirmed', 'accepted'])->whereNotNull('confirmed')->count();
            $type= 'store_order';
        }

        return response()->json([
            'success' => 1,
            'data' => ['new_pending_order' => $new_pending_order, 'new_confirmed_order' => $new_confirmed_order?? 0, 'order_type' =>$type]
        ]);
    }

    public function order_stats(Request $request)
    {
        $params = session('dash_params');
        foreach ($params as $key => $value) {
            if ($key == 'statistics_type') {
                $params['statistics_type'] = $request['statistics_type'];
            }
        }
        session()->put('dash_params', $params);

        $data = self::dashboard_order_stats_data();
        return response()->json([
            'view' => view('vendor-views.partials._dashboard-order-stats', compact('data'))->render()
        ], 200);
    }

    public function dashboard_order_stats_data()
    {
        $params = session('dash_params');
        $today = $params['statistics_type'] == 'today' ? 1 : 0;
        $this_month = $params['statistics_type'] == 'this_month' ? 1 : 0;

        $confirmed = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->where(['store_id' => Helpers::get_store_id()])->whereIn('order_status',['confirmed', 'accepted'])->whereNotNull('confirmed')->StoreOrder()->NotDigitalOrder()->OrderScheduledIn(30)->count();

        $cooking = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->where(['order_status' => 'processing', 'store_id' => Helpers::get_store_id()])->StoreOrder()->NotDigitalOrder()->count();

        $ready_for_delivery = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->where(['order_status' => 'handover', 'store_id' => Helpers::get_store_id()])->StoreOrder()->NotDigitalOrder()->count();

        $item_on_the_way = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->ItemOnTheWay()->where(['store_id' => Helpers::get_store_id()])->StoreOrder()->NotDigitalOrder()->count();

        $delivered = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->where(['order_status' => 'delivered', 'store_id' => Helpers::get_store_id()])->StoreOrder()->NotDigitalOrder()->count();

        $refunded = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->where(['order_status' => 'refunded', 'store_id' => Helpers::get_store_id()])->StoreOrder()->NotDigitalOrder()->count();

        $scheduled = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->Scheduled()->where(['store_id' => Helpers::get_store_id()])->where(function($q){
            if(config('order_confirmation_model') == 'store')
            {
                $q->whereNotIn('order_status',['failed','canceled', 'refund_requested', 'refunded']);
            }
            else
            {
                $q->whereNotIn('order_status',['pending','failed','canceled', 'refund_requested', 'refunded'])->orWhere(function($query){
                    $query->where('order_status','pending')->where('order_type', 'take_away');
                });
            }

        })->StoreOrder()->NotDigitalOrder()->count();

        $all = Order::when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })->when($this_month, function ($query) {
            return $query->whereMonth('created_at', Carbon::now());
        })->where(['store_id' => Helpers::get_store_id()])
        ->where(function($query){
            $storeData = \App\CentralLogics\Helpers::get_store_data();
            $subSelfDelivery = $storeData ? ($storeData->sub_self_delivery ?? false) : false;
            return $query->whereNotIn('order_status',(config('order_confirmation_model') == 'store'|| $subSelfDelivery)?['failed','canceled', 'refund_requested', 'refunded']:['pending','failed','canceled', 'refund_requested', 'refunded'])
            ->orWhere(function($query){
                return $query->where('order_status','pending')->where('order_type', 'take_away');
            });
        })
        ->StoreOrder()->NotDigitalOrder()->count();

        $data = [
            'confirmed' => $confirmed,
            'cooking' => $cooking,
            'ready_for_delivery' => $ready_for_delivery,
            'item_on_the_way' => $item_on_the_way,
            'delivered' => $delivered,
            'refunded' => $refunded,
            'scheduled' => $scheduled,
            'all' => $all,
        ];

        return $data;
    }

    public function updateDeviceToken(Request $request)
    {
        $vendor = Vendor::find(Helpers::get_vendor_id());
        $vendor->firebase_token =  $request->token;

        $vendor->save();

        return response()->json(['Token successfully stored.']);
    }

    /**
     * Switch between accessible modules
     * تغییر بین ماژول‌های قابل دسترسی
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function switchModule(Request $request)
    {
        $request->validate([
            'module_type' => 'required|string',
        ]);

        $store = Helpers::get_store_data();
        $moduleType = $request->module_type;

        // Check if store exists before accessing methods
        // بررسی وجود store قبل از دسترسی به متدها
        if (!$store) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'errors' => [['code' => 'store_not_found', 'message' => translate('messages.store_not_found')]]
                ], 404);
            }
            Toastr::error(translate('messages.store_not_found'));
            return back();
        }

        // Validate module access
        // اعتبارسنجی دسترسی ماژول
        if (!$store->hasModuleAccess($moduleType)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'errors' => [['code' => 'module_access', 'message' => translate('messages.module_access_denied')]]
                ], 403);
            }
            Toastr::error(translate('messages.module_access_denied'));
            return back();
        }

        // Validate addon published status for modules that require it
        // اعتبارسنجی وضعیت انتشار افزونه برای ماژول‌هایی که نیاز دارند
        if ($moduleType === 'rental' && addon_published_status('Rental') != 1) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'errors' => [['code' => 'addon_not_published', 'message' => translate('messages.rental_addon_not_published')]]
                ], 403);
            }
            Toastr::error(translate('messages.rental_addon_not_published'));
            return back();
        }

        if ($moduleType === 'beauty' && addon_published_status('BeautyBooking') != 1) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'errors' => [['code' => 'addon_not_published', 'message' => translate('messages.beauty_addon_not_published')]]
                ], 403);
            }
            Toastr::error(translate('messages.beauty_addon_not_published'));
            return back();
        }

        // Store selected module in session
        // ذخیره ماژول انتخاب شده در session
        session(['vendor_selected_module' => $moduleType]);

        // Redirect to appropriate dashboard
        // هدایت به داشبورد مناسب
        $redirectUrl = null;
        switch ($moduleType) {
            case 'rental':
                $redirectUrl = route('vendor.providerDashboard');
                break;
            case 'beauty':
                $redirectUrl = route('vendor.beautybooking.dashboard');
                break;
            default:
                // Default to restaurant dashboard
                // پیش‌فرض به داشبورد رستوران
                session()->forget('vendor_selected_module');
                $redirectUrl = route('vendor.dashboard');
                break;
        }

        if ($redirectUrl) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => translate('messages.module_switched_successfully'),
                    'redirect_url' => $redirectUrl
                ], 200);
            }
            return redirect($redirectUrl);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'errors' => [['code' => 'invalid_module', 'message' => translate('messages.invalid_module_selection')]]
            ], 400);
        }
        Toastr::error(translate('messages.invalid_module_selection'));
        return back();
    }
}
