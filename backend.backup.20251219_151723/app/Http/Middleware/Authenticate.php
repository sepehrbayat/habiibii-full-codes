<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     * رسیدگی به درخواست ورودی
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        // Skip default auth for vendor API routes that rely on auth_token
        // رد کردن احراز هویت پیش‌فرض برای روت‌های API فروشنده که از auth_token استفاده می‌کنند
        if (
            $request->route()?->gatherMiddleware()
            && in_array('vendor.api', $request->route()->gatherMiddleware(), true)
        ) {
            return $next($request);
        }

        // Skip when vendorType header is present (vendor token auth handled separately)
        // در صورت وجود هدر vendorType، احراز هویت پاسپورت را رد می‌کنیم چون توکن فروشنده جداگانه بررسی می‌شود
        if ($request->hasHeader('vendorType')) {
            return $next($request);
        }

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if($request->is('api/*')) {
            return route('authentication-failed');
        }
        else if ($request->is('admin/*') || $request->is('vendor/*'))
        {
            return route('home');
            // return route('admin.auth.login');
        }
        // else if ($request->is('vendor/*'))
        // {
        //     return route('vendor.auth.login');
        // }
        else
        {
            return route('home');
        }
    }
}
