<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Beauty Booking Service Provider
 * سرویس پروایدر ماژول رزرو زیبایی
 */
class BeautyBookingServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'BeautyBooking';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'beautybooking';

    /**
     * Boot the application events.
     * اجرای رویدادهای برنامه
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->registerObservers();
        $this->registerScheduledCommands();
    }
    
    /**
     * Register model observers
     * ثبت Observer های مدل
     *
     * @return void
     */
    protected function registerObservers(): void
    {
        \Modules\BeautyBooking\Entities\BeautyReview::observe(
            \Modules\BeautyBooking\Observers\BeautyReviewObserver::class
        );
        
        \Modules\BeautyBooking\Entities\BeautyBooking::observe(
            \Modules\BeautyBooking\Observers\BeautyBookingObserver::class
        );
        
        // Register Store observer for badge cache invalidation
        // ثبت Observer Store برای باطل کردن cache نشان
        // Only register if module is published
        // فقط در صورت انتشار ماژول ثبت کنید
        if (addon_published_status('BeautyBooking')) {
            \App\Models\Store::observe(
                \Modules\BeautyBooking\Observers\StoreObserver::class
            );
        }
    }
    
    /**
     * Register scheduled commands
     * ثبت دستورات زمان‌بندی شده
     *
     * @return void
     */
    protected function registerScheduledCommands(): void
    {
        // Scheduling is handled via scheduleBeautyBookingCommands() method
        // زمان‌بندی از طریق متد scheduleBeautyBookingCommands() انجام می‌شود
        // This method is called from app/Console/Kernel.php for proper Laravel integration
        // این متد از app/Console/Kernel.php برای یکپارچه‌سازی صحیح Laravel فراخوانی می‌شود
    }
    
    /**
     * Schedule BeautyBooking module commands
     * زمان‌بندی دستورات ماژول BeautyBooking
     *
     * This method should be called from app/Console/Kernel.php schedule() method
     * این متد باید از متد schedule() در app/Console/Kernel.php فراخوانی شود
     *
     * @param Schedule $schedule
     * @return void
     */
    public static function scheduleBeautyBookingCommands(Schedule $schedule): void
    {
        // Only register if module is published
        // فقط در صورت انتشار ماژول ثبت کنید
        if (!addon_published_status('BeautyBooking')) {
            return;
        }
        
        // Send booking reminders every hour
        // ارسال یادآوری رزرو هر ساعت
        $schedule->command('beautybooking:send-reminders')
            ->hourly()
            ->withoutOverlapping();
        
        // Generate monthly reports on the 1st of each month
        // تولید گزارش‌های ماهانه در روز اول هر ماه
        $schedule->command('beautybooking:generate-monthly-reports')
            ->monthlyOn(1, '00:00')
            ->withoutOverlapping();
        
        // Update expired subscriptions and recalculate badges daily
        // به‌روزرسانی اشتراک‌های منقضی شده و محاسبه مجدد نشان‌ها روزانه
        $schedule->command('beautybooking:update-expired-subscriptions')
            ->daily()
            ->withoutOverlapping();
        
        // Auto-renew subscriptions expiring soon (daily at 3 AM)
        // تمدید خودکار اشتراک‌های در حال انقضا (روزانه ساعت 3 صبح)
        $schedule->command('beautybooking:auto-renew-subscriptions')
            ->dailyAt('03:00')
            ->withoutOverlapping();
    }

    /**
     * Register the service provider.
     * ثبت سرویس پروایدر
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     * ثبت تنظیمات
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     * ثبت ویوها
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     * ثبت ترجمه‌ها
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     * دریافت سرویس‌های ارائه شده توسط پروایدر
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Get publishable view paths.
     * دریافت مسیرهای قابل انتشار ویوها
     *
     * @return array
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}

