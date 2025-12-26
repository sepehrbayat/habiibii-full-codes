<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     * نگاشت Policy های برنامه
     *
     * @var array
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     * ثبت سرویس‌های احراز هویت / مجوزدهی
     *
     * @return void
     */
    public function boot()
    {
        // Register BeautyBooking policies conditionally only if module is published
        // ثبت Policy های BeautyBooking به صورت شرطی فقط در صورت انتشار ماژول
        // This prevents ClassNotFoundException when module is disabled
        // این از ClassNotFoundException زمانی که ماژول غیرفعال است جلوگیری می‌کند
        if (addon_published_status('BeautyBooking')) {
            $this->policies = array_merge($this->policies, [
                \Modules\BeautyBooking\Entities\BeautySalon::class => \App\Policies\BeautySalonPolicy::class,
                \Modules\BeautyBooking\Entities\BeautyBooking::class => \App\Policies\BeautyBookingPolicy::class,
                \Modules\BeautyBooking\Entities\BeautyReview::class => \App\Policies\BeautyReviewPolicy::class,
            ]);
        }

        $this->registerPolicies();

        //
    }
}
