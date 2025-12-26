<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Models\Vendor;
use App\Models\VendorEmployee;
use App\Models\Store;
use Modules\BeautyBooking\Entities\BeautySalon;

class VendorTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token=$request->bearerToken();
        if(strlen($token)<1)
        {
            return response()->json([
                'errors' => [
                    ['code' => 'auth-001', 'message' => 'Unauthorized.']
                ]
            ], 401);
        }
        if (!$request->hasHeader('vendorType')) {
            $errors = [];
            array_push($errors, ['code' => 'vendor_type', 'message' => translate('messages.vendor_type_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $vendor_type= $request->header('vendorType');
        $isRegisterRoute = $request->is('api/v1/beautybooking/vendor/salon/register');

        if($vendor_type == 'owner'){
            $vendor = Vendor::where('auth_token', $token)->first();
            if(!isset($vendor))
            {
                return response()->json([
                    'errors' => [
                        ['code' => 'auth-001', 'message' => 'Unauthorized.']
                    ]
                ], 401);
            }
            $request['vendor']=$vendor;
            auth()->guard('vendor')->setUser($vendor);

            if (!$vendor->store_id) {
                $store = $vendor->store()->first();
                if (!$store) {
                    $store = Store::factory()->create(['vendor_id' => $vendor->id]);
                }
                $vendor->forceFill(['store_id' => $store->id])->save();
            }

            if (!$isRegisterRoute && $vendor->store_id && !BeautySalon::where('store_id', $vendor->store_id)->exists()) {
                BeautySalon::create([
                    'store_id' => $vendor->store_id,
                    'zone_id' => $vendor->store->zone_id ?? null,
                    'business_type' => 'salon',
                    'verification_status' => 0,
                    'is_verified' => false,
                ]);
            }
            Config::set('module.current_module_data', $vendor->stores[0]->module);
        }elseif($vendor_type == 'employee'){
            $vendor = VendorEmployee::where('auth_token', $token)->first();
            if(!isset($vendor))
            {
                return response()->json([
                    'errors' => [
                        ['code' => 'auth-001', 'message' => 'Unauthorized.']
                    ]
                ], 401);
            }
            $request['vendor']=$vendor->vendor;
            $request['vendor_employee']=$vendor;
            Config::set('module.current_module_data', $vendor->vendor->stores[0]->module);
        }
        return $next($request);
    }
}
