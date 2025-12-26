<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Traits\AddonHelper;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use App\CentralLogics\Helpers;

class AppServiceProvider extends ServiceProvider
{
    use AddonHelper;
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        try
        {
            $addonRoutes = $this->get_addon_admin_routes();
            $paymentStatus = $this->get_payment_publish_status();
            
            // Ensure we always set arrays, never null
            // اطمینان از اینکه همیشه آرایه تنظیم می‌شود، نه null
            Config::set('addon_admin_routes', is_array($addonRoutes) ? $addonRoutes : []);
            Config::set('get_payment_publish_status', is_array($paymentStatus) ? $paymentStatus : []);
            
            Paginator::useBootstrap();
            foreach(Helpers::get_view_keys() as $key=>$value)
            {
                view()->share($key, $value);
            }
        }
        catch(\Exception $e)
        {
            // Ensure config is always set to an array, even if there's an exception
            // این اطمینان می‌دهد که config همیشه به یک آرایه تنظیم شود، حتی در صورت خطا
            Config::set('addon_admin_routes', []);
            Config::set('get_payment_publish_status', []);
        }

    }
}
