<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Illuminate\Routing\Controller;

/**
 * Beauty Banner Controller (Admin)
 * کنترلر بنرهای تبلیغاتی (ادمین)
 */
class BeautyBannerController extends Controller
{
    /**
     * Promotion banners page
     * صفحه بنرهای پروموشن
     */
    public function promotion()
    {
        return view('beautybooking::admin.banner.promotion');
    }

    /**
     * Coupon banners page
     * صفحه بنرهای کوپن
     */
    public function coupon()
    {
        return view('beautybooking::admin.banner.coupon');
    }

    /**
     * Push notification page
     * صفحه پوش نوتیفیکیشن
     */
    public function push()
    {
        return view('beautybooking::admin.banner.push-notification');
    }

    /**
     * Advertisement banners page
     * صفحه بنرهای تبلیغاتی
     */
    public function advertisement()
    {
        return view('beautybooking::admin.banner.advertisement');
    }
}

