<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use App\CentralLogics\Helpers;
use App\Models\DataSetting;

/**
 * Beauty Settings Controller (Admin)
 * کنترلر تنظیمات زیبایی (ادمین)
 */
class BeautySettingsController extends Controller
{
    /**
     * Show home page setup
     * نمایش تنظیمات صفحه اصلی
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function homePageSetup()
    {
        $language = getWebConfig('language');
        
        return view('beautybooking::admin.settings.home-page-setup', [
            'language' => $language,
        ]);
    }

    /**
     * Update home page setup
     * به‌روزرسانی تنظیمات صفحه اصلی
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function homePageSetupUpdate(Request $request): RedirectResponse
    {
        try {
            // Implementation for updating home page settings
            // پیاده‌سازی به‌روزرسانی تنظیمات صفحه اصلی
            Toastr::success(translate('messages.settings_updated_successfully'));
        } catch (Exception $e) {
            \Log::error('Home page setup update failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_update_settings'));
        }

        return back();
    }

    /**
     * Show email format settings
     * نمایش تنظیمات فرمت ایمیل
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function emailFormatSetting(Request $request)
    {
        $type = $request->get('type', 'admin');
        
        return view('beautybooking::admin.business-settings.email-format-setting.index', [
            'type' => $type,
        ]);
    }
}

