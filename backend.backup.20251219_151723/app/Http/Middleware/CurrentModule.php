<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use App\Models\Module;

class CurrentModule
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
        // Force beauty module context based on URL to avoid cross-tab interference
        // تعیین ماژول زیبایی بر اساس مسیر برای جلوگیری از تداخل بین تب‌ها
        if (\Illuminate\Support\Facades\Request::is('admin/beautybooking*')) {
            $beautyModule = Module::with('translations')->where('module_type', 'beauty')->first();
            if ($beautyModule) {
                Config::set('module.current_module_id', $beautyModule->id);
                Config::set('module.current_module_type', $beautyModule->module_type);
                Config::set('module.current_module_name', $beautyModule->module_name);
            } else {
                // If beauty module is missing, reset to safe defaults to avoid stale state
                // در صورت نبود ماژول زیبایی، مقادیر را به حالت امن برگردانید تا وضعیت کهنه نماند
                Config::set('module.current_module_id', null);
                Config::set('module.current_module_type', 'settings');
                Config::set('module.current_module_name', null);
            }

            return $next($request);
        }

        if (request()->get('module_id')) {
            session()->put('current_module',request()->get('module_id'));
            Config::set('module.current_module_id', request()->get('module_id'));
        }else{
            Config::set('module.current_module_id', session()->get('current_module'));
        }

        $module_id = Config::get('module.current_module_id');
        $module_id = is_array($module_id)?null:$module_id;
        $module = isset($module_id)?Module::with('translations')->find($module_id):Module::with('translations')->active()->get()->first();

        if ($module) {
            Config::set('module.current_module_id', $module->id);
            Config::set('module.current_module_type', $module->module_type);
            Config::set('module.current_module_name', $module->module_name);
        }else{
            Config::set('module.current_module_id', null);
            Config::set('module.current_module_type', 'settings');
        }
        if (Request::is('backoffice/users*')) {
            Config::set('module.current_module_id', null);
            Config::set('module.current_module_type', 'users');
        }
        if (Request::is('backoffice/transactions*')) {
            Config::set('module.current_module_id', null);
            Config::set('module.current_module_type', 'transactions');
        }
        if (Request::is('backoffice/dispatch*')) {
            Config::set('module.current_module_id', null);
            Config::set('module.current_module_type', 'dispatch');
        }
        if (Request::is('backoffice/business-settings/*') || Request::is('taxvat/*')) {
            Config::set('module.current_module_id', null);
            Config::set('module.current_module_type', 'settings');
        }

        return $next($request);
    }
}
