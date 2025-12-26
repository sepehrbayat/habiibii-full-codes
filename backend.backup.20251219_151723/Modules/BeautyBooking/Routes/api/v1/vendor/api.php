<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\BeautyBooking\Http\Controllers\Api\Vendor\BeautyBookingController;
use Modules\BeautyBooking\Http\Controllers\Api\Vendor\BeautyStaffController;
use Modules\BeautyBooking\Http\Controllers\Api\Vendor\BeautyServiceController;
use Modules\BeautyBooking\Http\Controllers\Api\Vendor\BeautyCalendarController;
use Modules\BeautyBooking\Http\Controllers\Api\Vendor\BeautyVendorController;
use Modules\BeautyBooking\Http\Controllers\Api\Vendor\BeautyRetailController;
use Modules\BeautyBooking\Http\Controllers\Api\Vendor\BeautySubscriptionController;
use Modules\BeautyBooking\Http\Controllers\Api\Vendor\BeautyFinanceController;
use Modules\BeautyBooking\Http\Controllers\Api\Vendor\BeautyBadgeController;
use Modules\BeautyBooking\Http\Controllers\Api\Vendor\BeautyPackageController as VendorBeautyPackageController;
use Modules\BeautyBooking\Http\Controllers\Api\Vendor\BeautyGiftCardController;
use Modules\BeautyBooking\Http\Controllers\Api\Vendor\BeautyLoyaltyController;

/*
|--------------------------------------------------------------------------
| Vendor API Routes
|--------------------------------------------------------------------------
|
| Here are the vendor API routes for the Beauty Booking module
|
*/

Route::group(['as' => 'beautybooking.', 'middleware' => ['localization']], function () {
    
    // Vendor routes (require vendor authentication)
    // روت‌های فروشنده (نیاز به احراز هویت فروشنده دارند)
    Route::group(['middleware' => ['vendor.api']], function () {
        // Booking Management
        // مدیریت رزرو
        Route::group(['prefix' => 'bookings', 'as' => 'bookings.'], function () {
            Route::get('list/{all}', [BeautyBookingController::class, 'list'])
                ->middleware('throttle:60,1')
                ->name('list');
            Route::get('details', [BeautyBookingController::class, 'details'])
                ->middleware('throttle:60,1')
                ->name('details');
            // Booking confirmation (10 requests per minute)
            // تأیید رزرو (10 درخواست در دقیقه)
            Route::put('confirm', [BeautyBookingController::class, 'confirm'])
                ->middleware('throttle:10,1')
                ->name('confirm');
            Route::put('complete', [BeautyBookingController::class, 'complete'])
                ->middleware('throttle:10,1')
                ->name('complete');
            Route::put('mark-paid', [BeautyBookingController::class, 'markPaid'])
                ->middleware('throttle:10,1')
                ->name('mark_paid');
            Route::put('cancel', [BeautyBookingController::class, 'cancel'])
                ->middleware('throttle:5,1')
                ->name('cancel');
        });
        
        // Staff Management
        // مدیریت کارمندان
        Route::group(['prefix' => 'staff', 'as' => 'staff.'], function () {
            Route::get('list', [BeautyStaffController::class, 'list'])
                ->middleware('throttle:60,1')
                ->name('list');
            Route::post('create', [BeautyStaffController::class, 'store'])
                ->middleware('throttle:10,1')
                ->name('store');
            Route::post('update/{id}', [BeautyStaffController::class, 'update'])
                ->middleware('throttle:10,1')
                ->name('update');
            Route::get('details/{id}', [BeautyStaffController::class, 'details'])
                ->middleware('throttle:60,1')
                ->name('details');
            Route::delete('delete/{id}', [BeautyStaffController::class, 'destroy'])
                ->middleware('throttle:5,1')
                ->name('delete');
            Route::get('status/{id}', [BeautyStaffController::class, 'status'])
                ->middleware('throttle:10,1')
                ->name('status');
        });
        
        // Service Management
        // مدیریت خدمات
        Route::group(['prefix' => 'service', 'as' => 'service.'], function () {
            Route::get('list', [BeautyServiceController::class, 'list'])
                ->middleware('throttle:60,1')
                ->name('list');
            Route::post('create', [BeautyServiceController::class, 'store'])
                ->middleware('throttle:10,1')
                ->name('store');
            Route::post('update/{id}', [BeautyServiceController::class, 'update'])
                ->middleware('throttle:10,1')
                ->name('update');
            Route::get('details/{id}', [BeautyServiceController::class, 'details'])
                ->middleware('throttle:60,1')
                ->name('details');
            Route::delete('delete/{id}', [BeautyServiceController::class, 'destroy'])
                ->middleware('throttle:5,1')
                ->name('delete');
            Route::get('status/{id}', [BeautyServiceController::class, 'status'])
                ->middleware('throttle:10,1')
                ->name('status');
        });
        
        // Calendar Management
        // مدیریت تقویم
        Route::group(['prefix' => 'calendar', 'as' => 'calendar.'], function () {
            Route::get('availability', [BeautyCalendarController::class, 'getAvailability'])
                ->middleware('throttle:60,1')
                ->name('availability');
            Route::post('blocks/create', [BeautyCalendarController::class, 'createBlock'])
                ->middleware('throttle:30,1')
                ->name('blocks.create');
            Route::delete('blocks/delete/{id}', [BeautyCalendarController::class, 'deleteBlock'])
                ->middleware('throttle:30,1')
                ->name('blocks.delete');
        });
        
        // Salon Registration & Onboarding
        // ثبت‌نام و راه‌اندازی سالن
        Route::group(['prefix' => 'salon', 'as' => 'salon.'], function () {
            // Salon registration (5 requests per minute - critical operation)
            // ثبت‌نام سالن (5 درخواست در دقیقه - عملیات مهم)
            Route::post('register', [BeautyVendorController::class, 'register'])
                ->middleware('throttle:5,1')
                ->name('register');
            Route::post('documents/upload', [BeautyVendorController::class, 'uploadDocuments'])
                ->middleware('throttle:10,1')
                ->name('documents.upload');
            Route::post('working-hours/update', [BeautyVendorController::class, 'updateWorkingHours'])
                ->middleware('throttle:10,1')
                ->name('working-hours.update');
            Route::post('holidays/manage', [BeautyVendorController::class, 'manageHolidays'])
                ->middleware('throttle:10,1')
                ->name('holidays.manage');
        });
        
        // Profile Management
        // مدیریت پروفایل
        Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
            Route::get('/', [BeautyVendorController::class, 'profile'])
                ->middleware('throttle:60,1')
                ->name('index');
            Route::post('update', [BeautyVendorController::class, 'profileUpdate'])
                ->middleware('throttle:10,1')
                ->name('update');
        });
        
        // Retail Management
        // مدیریت خرده‌فروشی
        Route::group(['prefix' => 'retail', 'as' => 'retail.'], function () {
            Route::get('products', [BeautyRetailController::class, 'listProducts'])
                ->middleware('throttle:60,1')
                ->name('products.list');
            Route::post('products', [BeautyRetailController::class, 'storeProduct'])
                ->middleware('throttle:10,1')
                ->name('products.store');
            Route::get('orders', [BeautyRetailController::class, 'listOrders'])
                ->middleware('throttle:60,1')
                ->name('orders.list');
        });
        
        // Subscription & Advertisement Management
        // مدیریت اشتراک و تبلیغات
        Route::group(['prefix' => 'subscription', 'as' => 'subscription.'], function () {
            Route::get('plans', [BeautySubscriptionController::class, 'getPlans'])
                ->middleware('throttle:60,1')
                ->name('plans');
            // Subscription purchase (5 requests per minute - critical operation)
            // خرید اشتراک (5 درخواست در دقیقه - عملیات مهم)
            Route::post('purchase', [BeautySubscriptionController::class, 'purchase'])
                ->middleware('throttle:5,1')
                ->name('purchase');
            Route::get('history', [BeautySubscriptionController::class, 'history'])
                ->middleware('throttle:60,1')
                ->name('history');
        });
        
        // Finance & Reports
        // مالی و گزارش‌ها
        Route::group(['prefix' => 'finance', 'as' => 'finance.'], function () {
            Route::get('payout-summary', [BeautyFinanceController::class, 'payoutSummary'])
                ->middleware('throttle:60,1')
                ->name('payout-summary');
            Route::get('transactions', [BeautyFinanceController::class, 'transactionHistory'])
                ->middleware('throttle:60,1')
                ->name('transactions');
        });
        
        // Badge Status
        // وضعیت نشان
        Route::get('badge/status', [BeautyBadgeController::class, 'status'])
            ->middleware('throttle:60,1')
            ->name('badge.status');
        
        // Package Management
        // مدیریت پکیج
        Route::group(['prefix' => 'packages', 'as' => 'packages.'], function () {
            Route::get('list', [VendorBeautyPackageController::class, 'list'])
                ->middleware('throttle:60,1')
                ->name('list');
            Route::get('usage-stats', [VendorBeautyPackageController::class, 'usageStats'])
                ->middleware('throttle:60,1')
                ->name('usage-stats');
        });
        
        // Gift Card Management
        // مدیریت کارت هدیه
        Route::group(['prefix' => 'gift-cards', 'as' => 'gift-cards.'], function () {
            Route::get('list', [BeautyGiftCardController::class, 'list'])
                ->middleware('throttle:60,1')
                ->name('list');
            Route::get('redemption-history', [BeautyGiftCardController::class, 'redemptionHistory'])
                ->middleware('throttle:60,1')
                ->name('redemption-history');
        });
        
        // Loyalty Campaign Management
        // مدیریت کمپین‌های وفاداری
        Route::group(['prefix' => 'loyalty', 'as' => 'loyalty.'], function () {
            Route::get('campaigns', [BeautyLoyaltyController::class, 'listCampaigns'])
                ->middleware('throttle:60,1')
                ->name('campaigns');
            Route::get('points-history', [BeautyLoyaltyController::class, 'pointsHistory'])
                ->middleware('throttle:60,1')
                ->name('points-history');
            Route::get('campaign/{id}/stats', [BeautyLoyaltyController::class, 'campaignStats'])
                ->middleware('throttle:60,1')
                ->name('campaign.stats');
        });
    });
});

