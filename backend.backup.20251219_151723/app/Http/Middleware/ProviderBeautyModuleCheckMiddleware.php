<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\CentralLogics\Helpers;

/**
 * Provider Beauty Module Check Middleware
 * میدلور بررسی دسترسی به ماژول زیبایی برای فروشنده
 *
 * Checks if the vendor's store has access to the beauty module
 * بررسی اینکه آیا فروشگاه فروشنده به ماژول زیبایی دسترسی دارد
 */
class ProviderBeautyModuleCheckMiddleware
{
    /**
     * Handle an incoming request.
     * مدیریت درخواست ورودی
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $store = Helpers::get_store_data();
        
        if (!$store) {
            return abort(404);
        }

        // Check if store has access to beauty module
        // بررسی اینکه آیا فروشگاه به ماژول زیبایی دسترسی دارد
        if ($store->hasModuleAccess('beauty') && addon_published_status('BeautyBooking') == 1) {
            return $next($request);
        }

        return abort(404);
    }
}

