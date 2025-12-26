<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BusinessSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Modules\Rental\Entities\Trips;

class SystemController extends Controller
{

    public function store_data()
    {
        // Initialize variables with default values
        // مقداردهی اولیه متغیرها با مقادیر پیش‌فرض
        $new_order = 0;
        $type = 'store_order';
        $module_id = 0;
        
        // Check for new store orders
        // بررسی سفارشات جدید فروشگاه
        // Fixed: Use single query instead of count() + first() to prevent race conditions
        // اصلاح شده: استفاده از یک کوئری به جای count() + first() برای جلوگیری از race condition
        $storeOrder = Order::StoreOrder()->where(['checked' => 0])->latest()->first(['module_id']);
        if($storeOrder && $storeOrder->module_id){
            $new_order = 1;
            $type = 'store_order';
            $module_id = $storeOrder->module_id;
        }
        // Check for new parcel orders (only if store order doesn't have valid module_id)
        // بررسی سفارشات جدید پست (فقط اگر سفارش فروشگاه module_id معتبر نداشته باشد)
        // Fixed: Removed count() check and use single first() query to prevent race conditions
        // اصلاح شده: حذف بررسی count() و استفاده از یک کوئری first() برای جلوگیری از race condition
        if($new_order == 0){
            $parcelOrder = Order::ParcelOrder()->where(['checked' => 0])->latest()->first(['module_id']);
            if($parcelOrder && $parcelOrder->module_id){
                $new_order = 1;
                $type = 'parcel';
                $module_id = $parcelOrder->module_id;
            }
        }
        // Check for new rental trips (only if no valid order found yet)
        // بررسی سفرهای جدید اجاره (فقط اگر هنوز سفارش معتبری یافت نشده باشد)
        // Fixed: Removed count() check and use single first() query to prevent race conditions
        // اصلاح شده: حذف بررسی count() و استفاده از یک کوئری first() برای جلوگیری از race condition
        if($new_order == 0 && addon_published_status('Rental')){
            $trip = Trips::where(['checked' => 0])->latest()->first(['module_id']);
            if($trip && $trip->module_id){
                $new_order = 1;
                $type = 'trip';
                $module_id = $trip->module_id;
            }
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'new_order' => $new_order,
                'type' => $type,
                'module_id' => $module_id
            ]
        ]);
    }

    public function settings()
    {
        return view('admin-views.settings');
    }

    public function settings_update(Request $request)
    {
        $request->validate([
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|unique:admins,email,' . auth('admin')->id(),
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:admins,phone,' . auth('admin')->id(),
        ], [
            'f_name.required' => translate('messages.first_name_is_required'),
            'l_name.required' => translate('messages.Last name is required!'),
        ]);

        $admin = Admin::find(auth('admin')->id());

        if ($request->has('image')) {
            $image_name = Helpers::update('admin/', $admin->image, 'png', $request->file('image'));
        } else {
            $image_name = $admin['image'];
        }


        $admin->f_name = $request->f_name;
        $admin->l_name = $request->l_name;
        $admin->email = $request->email;
        $admin->phone = $request->phone;
        $admin->image = $image_name;
        $admin->save();
        Toastr::success(translate('messages.admin_updated_successfully'));
        return back();
    }

    public function settings_password_update(Request $request)
    {
        $request->validate([
            'password' => ['required','same:confirm_password', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            'confirm_password' => 'required',
        ]);

        $admin = Admin::find(auth('admin')->id());
        $admin->password = bcrypt($request['password']);
        $login_remember_token= Str::random(60);
        $admin->login_remember_token =  $login_remember_token;
        $admin->save();
        session(['login_remember_token' => $login_remember_token]);
        Toastr::success(translate('messages.admin_password_updated_successfully'));
        return back();
    }

    public function maintenance_mode()
    {
        $maintenance_mode = BusinessSetting::where('key', 'maintenance_mode')->first();
        if (isset($maintenance_mode) == false) {
            Helpers::businessInsert([
                'key' => 'maintenance_mode',
                'value' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            Helpers::businessUpdateOrInsert(['key' => 'maintenance_mode'], [
                'value' => $maintenance_mode->value == 1 ? 0 : 1
            ]);
        }

        if (isset($maintenance_mode) && $maintenance_mode->value) {
            return response()->json(['message' => translate('Maintenance is off.')]);
        }
        return response()->json(['message' => translate('Maintenance is on.')]);
    }

    public function landing_page()
    {
        $landing_page = BusinessSetting::where('key', 'landing_page')->first();
        if (isset($landing_page) == false) {
            Helpers::businessInsert([
                'key' => 'landing_page',
                'value' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            Helpers::businessUpdateOrInsert(['key' => 'landing_page'], [
                   'value' => $landing_page->value == 1 ? 0 : 1
               ]);
        }

        if (isset($landing_page) && $landing_page->value) {
            return response()->json(['message' => translate('landing_page_is_off.')]);
        }
        return response()->json(['message' => translate('landing_page_is_on.')]);
    }
    public function system_currency(Request $request)
    {
        $currency_check=Helpers::checkCurrency($request['currency']);
        if( $currency_check !== true ){
        return response()->json(['data'=> translate($currency_check) ],200);
        }
        return response()->json([],200);
    }
}
