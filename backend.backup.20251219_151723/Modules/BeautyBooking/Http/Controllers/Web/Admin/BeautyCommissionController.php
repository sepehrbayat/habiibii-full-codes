<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use App\CentralLogics\Helpers;
use Modules\BeautyBooking\Entities\BeautyCommissionSetting;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Http\Requests\BeautyCommissionSettingStoreRequest;

/**
 * Beauty Commission Controller (Admin)
 * کنترلر تنظیمات کمیسیون (ادمین)
 */
class BeautyCommissionController extends Controller
{
    public function __construct(
        private BeautyCommissionSetting $commissionSetting
    ) {}

    /**
     * Index - List all commission settings
     * نمایش لیست تمام تنظیمات کمیسیون
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $settings = $this->commissionSetting->with('category')
            ->latest()
            ->paginate(config('default_pagination'));
        
        $categories = BeautyServiceCategory::where('status', 1)->get();

        return view('beautybooking::admin.commission.index', compact('settings', 'categories'));
    }

    /**
     * Store new commission setting
     * ذخیره تنظیمات کمیسیون جدید
     *
     * @param BeautyCommissionSettingStoreRequest $request
     * @return RedirectResponse
     */
    public function store(BeautyCommissionSettingStoreRequest $request): RedirectResponse
    {
        $this->commissionSetting->create([
            'service_category_id' => $request->service_category_id,
            'salon_level' => $request->salon_level,
            'commission_percentage' => $request->commission_percentage,
            'min_commission' => $request->min_commission ?? 0,
            'max_commission' => $request->max_commission,
            'status' => $request->status ?? 1,
        ]);

        Toastr::success(translate('messages.commission_setting_added_successfully'));
        return back();
    }

    /**
     * Update commission setting
     * به‌روزرسانی تنظیمات کمیسیون
     *
     * @param BeautyCommissionSettingStoreRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(BeautyCommissionSettingStoreRequest $request, int $id): RedirectResponse
    {
        $setting = $this->commissionSetting->findOrFail($id);

        $setting->update([
            'service_category_id' => $request->service_category_id,
            'salon_level' => $request->salon_level,
            'commission_percentage' => $request->commission_percentage,
            'min_commission' => $request->min_commission ?? 0,
            'max_commission' => $request->max_commission,
            'status' => $request->status ?? $setting->status,
        ]);

        Toastr::success(translate('messages.commission_setting_updated_successfully'));
        return back();
    }

    /**
     * Toggle commission setting status
     * تغییر وضعیت تنظیمات کمیسیون
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function status(int $id): RedirectResponse
    {
        $setting = $this->commissionSetting->findOrFail($id);
        $setting->update(['status' => !$setting->status]);

        Toastr::success(translate('messages.status_updated_successfully'));
        return back();
    }

    /**
     * Delete commission setting
     * حذف تنظیمات کمیسیون
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $setting = $this->commissionSetting->findOrFail($id);
        $setting->delete();

        Toastr::success(translate('messages.commission_setting_deleted_successfully'));
        return back();
    }

    /**
     * Update business settings (service fee, subscription pricing, etc.)
     * به‌روزرسانی تنظیمات کسب‌وکار (هزینه سرویس، قیمت‌گذاری اشتراک و غیره)
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateBusinessSettings(Request $request): RedirectResponse
    {
        // Update service fee percentage
        // به‌روزرسانی درصد هزینه سرویس
        if ($request->has('beauty_booking_service_fee_percentage')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_service_fee_percentage'],
                ['value' => $request->beauty_booking_service_fee_percentage]
            );
        }

        // Update subscription pricing
        // به‌روزرسانی قیمت‌گذاری اشتراک
        if ($request->has('beauty_booking_subscription_featured_7_days')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_subscription_featured_7_days'],
                ['value' => $request->beauty_booking_subscription_featured_7_days]
            );
        }
        if ($request->has('beauty_booking_subscription_featured_30_days')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_subscription_featured_30_days'],
                ['value' => $request->beauty_booking_subscription_featured_30_days]
            );
        }
        if ($request->has('beauty_booking_subscription_boost_7_days')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_subscription_boost_7_days'],
                ['value' => $request->beauty_booking_subscription_boost_7_days]
            );
        }
        if ($request->has('beauty_booking_subscription_boost_30_days')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_subscription_boost_30_days'],
                ['value' => $request->beauty_booking_subscription_boost_30_days]
            );
        }
        if ($request->has('beauty_booking_subscription_banner_homepage')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_subscription_banner_homepage'],
                ['value' => $request->beauty_booking_subscription_banner_homepage]
            );
        }
        if ($request->has('beauty_booking_subscription_banner_category')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_subscription_banner_category'],
                ['value' => $request->beauty_booking_subscription_banner_category]
            );
        }
        if ($request->has('beauty_booking_subscription_banner_search_results')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_subscription_banner_search_results'],
                ['value' => $request->beauty_booking_subscription_banner_search_results]
            );
        }
        if ($request->has('beauty_booking_subscription_dashboard_monthly')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_subscription_dashboard_monthly'],
                ['value' => $request->beauty_booking_subscription_dashboard_monthly]
            );
        }
        if ($request->has('beauty_booking_subscription_dashboard_yearly')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_subscription_dashboard_yearly'],
                ['value' => $request->beauty_booking_subscription_dashboard_yearly]
            );
        }

        // Update package commission settings
        // به‌روزرسانی تنظیمات کمیسیون پکیج
        if ($request->has('beauty_booking_package_commission_on_total')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_package_commission_on_total'],
                ['value' => $request->beauty_booking_package_commission_on_total]
            );
        }

        // Update cancellation fee settings
        // به‌روزرسانی تنظیمات هزینه لغو
        if ($request->has('beauty_booking_cancellation_no_fee_hours')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_cancellation_no_fee_hours'],
                ['value' => $request->beauty_booking_cancellation_no_fee_hours]
            );
        }
        if ($request->has('beauty_booking_cancellation_partial_fee_hours')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_cancellation_partial_fee_hours'],
                ['value' => $request->beauty_booking_cancellation_partial_fee_hours]
            );
        }
        if ($request->has('beauty_booking_cancellation_fee_partial')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_cancellation_fee_partial'],
                ['value' => $request->beauty_booking_cancellation_fee_partial]
            );
        }

        // Update consultation commission
        // به‌روزرسانی کمیسیون مشاوره
        if ($request->has('beauty_booking_consultation_commission')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_consultation_commission'],
                ['value' => $request->beauty_booking_consultation_commission]
            );
        }

        // Update cross-selling commission
        // به‌روزرسانی کمیسیون فروش متقابل
        if ($request->has('beauty_booking_cross_selling_commission')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_cross_selling_commission'],
                ['value' => $request->beauty_booking_cross_selling_commission]
            );
        }
        if ($request->has('beauty_booking_cross_selling_enabled')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_cross_selling_enabled'],
                ['value' => $request->beauty_booking_cross_selling_enabled]
            );
        }

        // Update retail commission
        // به‌روزرسانی کمیسیون خرده‌فروشی
        if ($request->has('beauty_booking_retail_commission')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_retail_commission'],
                ['value' => $request->beauty_booking_retail_commission]
            );
        }
        if ($request->has('beauty_booking_retail_enabled')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_retail_enabled'],
                ['value' => $request->beauty_booking_retail_enabled]
            );
        }

        // Update gift card commission
        // به‌روزرسانی کمیسیون کارت هدیه
        if ($request->has('beauty_booking_gift_card_commission')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_gift_card_commission'],
                ['value' => $request->beauty_booking_gift_card_commission]
            );
        }

        // Update loyalty commission
        // به‌روزرسانی کمیسیون وفاداری
        if ($request->has('beauty_booking_loyalty_commission')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_loyalty_commission'],
                ['value' => $request->beauty_booking_loyalty_commission]
            );
        }
        if ($request->has('beauty_booking_loyalty_enabled')) {
            Helpers::businessUpdateOrInsert(
                ['key' => 'beauty_booking_loyalty_enabled'],
                ['value' => $request->beauty_booking_loyalty_enabled]
            );
        }

        Toastr::success(translate('messages.settings_updated_successfully'));
        return back();
    }
}

