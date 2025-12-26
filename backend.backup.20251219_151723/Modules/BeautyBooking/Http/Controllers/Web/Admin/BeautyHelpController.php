<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Beauty Help Controller (Admin)
 * کنترلر راهنما (ادمین)
 *
 * Handles admin help documentation pages
 * مدیریت صفحات مستندات راهنمای ادمین
 */
class BeautyHelpController extends Controller
{
    /**
     * Help index page
     * صفحه فهرست راهنما
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('beautybooking::admin.help.index');
    }

    /**
     * Salon approval help page
     * صفحه راهنمای تأیید سالن
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function salonApproval()
    {
        return view('beautybooking::admin.help.salon-approval');
    }

    /**
     * Commission configuration help page
     * صفحه راهنمای تنظیمات کمیسیون
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function commissionConfiguration()
    {
        return view('beautybooking::admin.help.commission-configuration');
    }

    /**
     * Subscription management help page
     * صفحه راهنمای مدیریت اشتراک
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function subscriptionManagement()
    {
        return view('beautybooking::admin.help.subscription-management');
    }

    /**
     * Review moderation help page
     * صفحه راهنمای مدیریت نظرات
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function reviewModeration()
    {
        return view('beautybooking::admin.help.review-moderation');
    }

    /**
     * Report generation help page
     * صفحه راهنمای تولید گزارش
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function reportGeneration()
    {
        return view('beautybooking::admin.help.report-generation');
    }
}

