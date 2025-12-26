<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Config;
use App\Models\Module;

class ModuleCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $except = [
            'api/v1/customer*', 'api/v1/banners', 'api/v1/stores/get-stores/*', 'api/v1/coupon/list', 'api/v1/categories', 'api/v1/items/reviews/submit', 'api/v1/rider/reviews/submit'
        ];

        foreach ($except as $except) {
            if ($request->fullUrlIs($except) || $request->is($except)) {
                if(!$request->hasHeader('moduleId')) {
                    return $next($request);
                }
            }
        }

        // Check if it's an API route by path first (not just expectsJson, as AJAX requests also expect JSON)
        // بررسی اینکه آیا روت API است بر اساس مسیر (نه فقط expectsJson، چون درخواست‌های AJAX نیز JSON انتظار دارند)
        if ($request->is('api/*')) {
            // Auto-detect module for beautybooking routes
            // تشخیص خودکار ماژول برای route‌های beautybooking
            if ($request->is('api/v1/beautybooking*')) {
                $beautyModule = Module::where('module_name', 'beauty_booking')
                    ->orWhere('module_type', 'beauty')
                    ->first();
                
                if ($beautyModule) {
                    // Set module automatically for beautybooking routes
                    // تنظیم خودکار ماژول برای route‌های beautybooking
                    Config::set('module.current_module_data', $beautyModule);
                    Config::set('module.current_module_id', $beautyModule->id);
                    return $next($request);
                }
            }
            
            // API route - check header
            // روت API - بررسی header
            if(!$request->hasHeader('moduleId'))
            {
                $errors = [];
                array_push($errors, ['code' => 'moduleId', 'message' => translate('messages.module_id_required')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
            $module = Module::find($request->header('moduleId'));
            if(!$module) {
                $errors = [];
                array_push($errors, ['code' => 'moduleId', 'message' => translate('messages.not_found')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
            Config::set('module.current_module_data', $module);
        } else {
            // Web route - check session or query parameter
            // روت وب - بررسی session یا query parameter
            $moduleId = $request->get('module_id') ?? session()->get('current_module');
            
            if ($moduleId) {
                $module = Module::find($moduleId);
                if ($module) {
                    session()->put('current_module', $moduleId);
                    Config::set('module.current_module_id', $moduleId);
                    Config::set('module.current_module_data', $module);
                } else {
                    // If module not found, try to get first active module
                    // اگر ماژول پیدا نشد، اولین ماژول فعال را دریافت کن
                    $module = Module::active()->first();
                    if ($module) {
                        session()->put('current_module', $module->id);
                        Config::set('module.current_module_id', $module->id);
                        Config::set('module.current_module_data', $module);
                    }
                }
            } else {
                // No module specified - try to get first active module
                // ماژولی مشخص نشده - اولین ماژول فعال را دریافت کن
                $module = Module::active()->first();
                if ($module) {
                    session()->put('current_module', $module->id);
                    Config::set('module.current_module_id', $module->id);
                    Config::set('module.current_module_data', $module);
                }
            }
        }
        
        return $next($request);
    }
}
