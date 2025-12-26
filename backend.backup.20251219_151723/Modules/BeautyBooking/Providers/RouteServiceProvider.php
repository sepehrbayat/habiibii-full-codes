<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Route Service Provider
 * سرویس پروایدر روت‌ها
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     * فضای نام ماژول برای تولید URL به اکشن‌ها
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\BeautyBooking\Http\Controllers';

    /**
     * Called before routes are registered.
     * فراخوانی قبل از ثبت روت‌ها
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     * تعریف روت‌های برنامه
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     * تعریف روت‌های وب برای برنامه
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->prefix('admin')
            ->as('admin.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('BeautyBooking', '/Routes/web/admin/admin.php'));

        Route::middleware('web')
            ->prefix('vendor-panel')
            ->as('vendor.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('BeautyBooking', '/Routes/web/vendor/routes.php'));

        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('BeautyBooking', '/Routes/web/customer/routes.php'));
    }

    /**
     * Define the "api" routes for the application.
     * تعریف روت‌های API برای برنامه
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api/v1')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('BeautyBooking', '/Routes/api/v1/customer/api.php'));

        Route::prefix('api/v1/beautybooking/vendor')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('BeautyBooking', '/Routes/api/v1/vendor/api.php'));
    }
}

