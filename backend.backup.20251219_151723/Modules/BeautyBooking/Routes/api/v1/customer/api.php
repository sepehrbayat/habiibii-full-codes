<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\BeautyBooking\Http\Controllers\Api\Customer\BeautySalonController;
use Modules\BeautyBooking\Http\Controllers\Api\Customer\BeautyBookingController;
use Modules\BeautyBooking\Http\Controllers\Api\Customer\BeautyReviewController;
use Modules\BeautyBooking\Http\Controllers\Api\Customer\BeautyGiftCardController;
use Modules\BeautyBooking\Http\Controllers\Api\Customer\BeautyCategoryController;
use Modules\BeautyBooking\Http\Controllers\Api\Customer\BeautyConsultationController;
use Modules\BeautyBooking\Http\Controllers\Api\Customer\BeautyRetailController;
use Modules\BeautyBooking\Http\Controllers\Api\Customer\BeautyPackageController;
use Modules\BeautyBooking\Http\Controllers\Api\Customer\BeautyLoyaltyController;
use Modules\BeautyBooking\Http\Controllers\Api\Customer\BeautyNotificationController;
use Modules\BeautyBooking\Http\Controllers\Api\Customer\BeautyDashboardController;

/*
|--------------------------------------------------------------------------
| Customer API Routes
|--------------------------------------------------------------------------
|
| Here are the customer API routes for the Beauty Booking module
|
*/

Route::group(['prefix' => 'beautybooking', 'as' => 'beautybooking.', 'middleware' => ['localization']], function () {
    $publicMiddleware = ['module-check', 'throttle:120,1'];
    if (app()->environment('testing')) {
        $publicMiddleware = array_values(array_filter(
            $publicMiddleware,
            fn (string $middleware): bool => $middleware !== 'module-check'
        ));
    }

    // Public routes (no authentication required)
    // روت‌های عمومی (نیاز به احراز هویت ندارند)
    Route::group(['middleware' => $publicMiddleware], function () {
        // Salon search and details (120 requests per minute)
        // جستجو و جزئیات سالن (120 درخواست در دقیقه)
        Route::get('salons/search', [BeautySalonController::class, 'search'])->name('salons.search');
        Route::get('salons/popular', [BeautySalonController::class, 'popular'])->name('salons.popular');
        Route::get('salons/top-rated', [BeautySalonController::class, 'topRated'])->name('salons.top-rated');
        Route::get('salons/monthly-top-rated', [BeautySalonController::class, 'monthlyTopRated'])->name('salons.monthly-top-rated');
        Route::get('salons/trending-clinics', [BeautySalonController::class, 'trendingClinics'])->name('salons.trending-clinics');
        Route::get('salons/category-list', [BeautyCategoryController::class, 'list'])->name('salons.category-list');
        Route::get('salons/{id}', [BeautySalonController::class, 'show'])
            ->whereNumber('id')
            ->name('salons.show');
        
        // Salon reviews
        // نظرات سالن
        Route::get('reviews/{salon_id}', [BeautyReviewController::class, 'getSalonReviews'])->name('reviews.salon');
        
        // Service suggestions for cross-selling (60 requests per minute)
        // پیشنهادات خدمت برای فروش متقابل (60 درخواست در دقیقه)
        Route::get('services/{id}/suggestions', [BeautyBookingController::class, 'getServiceSuggestions'])
            ->middleware('throttle:60,1')
            ->name('services.suggestions');
        
        // Availability checking (30 requests per minute)
        // بررسی دسترسی (30 درخواست در دقیقه)
        Route::post('availability/check', [BeautyBookingController::class, 'checkAvailability'])
            ->middleware('throttle:30,1')
            ->name('availability.check');
    });
    
    // Authenticated routes
    // روت‌های احراز هویت شده
    Route::group(['middleware' => ['auth:api']], function () {
        // Dashboard summary (60 requests per minute)
        // خلاصه داشبورد (60 درخواست در دقیقه)
        Route::get('dashboard/summary', [BeautyDashboardController::class, 'summary'])
            ->middleware('throttle:60,1')
            ->name('dashboard.summary');
        
        // Wallet transactions for beauty bookings (60 requests per minute)
        // تراکنش‌های کیف پول برای رزروهای زیبایی (60 درخواست در دقیقه)
        Route::get('wallet/transactions', [BeautyDashboardController::class, 'walletTransactions'])
            ->middleware('throttle:60,1')
            ->name('wallet.transactions');
        
        // Notifications (60 requests per minute)
        // نوتیفیکیشن‌ها (60 درخواست در دقیقه)
        Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function () {
            Route::get('/', [BeautyNotificationController::class, 'index'])
                ->middleware('throttle:60,1')
                ->name('index');
            Route::post('mark-read', [BeautyNotificationController::class, 'markRead'])
                ->middleware('throttle:60,1')
                ->name('mark-read');
        });
        
        // Booking management
        // مدیریت رزرو
        Route::group(['prefix' => 'bookings', 'as' => 'bookings.'], function () {
            // Booking creation (10 requests per minute - critical operation)
            // ایجاد رزرو (10 درخواست در دقیقه - عملیات مهم)
            Route::post('/', [BeautyBookingController::class, 'store'])
                ->middleware('throttle:10,1')
                ->name('store');
            // List bookings (60 requests per minute)
            // لیست رزروها (60 درخواست در دقیقه)
            Route::get('/', [BeautyBookingController::class, 'index'])
                ->middleware('throttle:60,1')
                ->name('index');
            Route::get('{id}', [BeautyBookingController::class, 'show'])
                ->middleware('throttle:60,1')
                ->name('show');
            Route::get('{id}/conversation', [BeautyBookingController::class, 'getConversation'])
                ->middleware('throttle:60,1')
                ->name('conversation');
            Route::post('{id}/conversation', [BeautyBookingController::class, 'sendMessage'])
                ->middleware('throttle:30,1')
                ->name('conversation.send');
            Route::put('{id}/reschedule', [BeautyBookingController::class, 'reschedule'])
                ->middleware('throttle:20,1')
                ->name('reschedule');
            // Booking cancellation (10 requests per minute - critical operation)
            // لغو رزرو (10 درخواست در دقیقه - عملیات مهم)
            Route::put('{id}/cancel', [BeautyBookingController::class, 'cancel'])
                ->middleware('throttle:10,1')
                ->name('cancel');
        });
        
        // Packages
        // پکیج‌ها
        Route::group(['prefix' => 'packages', 'as' => 'packages.'], function () {
            Route::get('/', [BeautyPackageController::class, 'index'])
                ->middleware('throttle:60,1')
                ->name('index');
            Route::get('{id}', [BeautyPackageController::class, 'show'])
                ->middleware('throttle:60,1')
                ->name('show');
            // Package purchase (5 requests per minute - critical operation)
            // خرید پکیج (5 درخواست در دقیقه - عملیات مهم)
            Route::post('{id}/purchase', [BeautyPackageController::class, 'purchase'])
                ->middleware('throttle:5,1')
                ->name('purchase');
            Route::get('{id}/status', [BeautyPackageController::class, 'getPackageStatus'])
                ->middleware('throttle:60,1')
                ->name('status');
            Route::get('{id}/usage-history', [BeautyPackageController::class, 'getUsageHistory'])
                ->middleware('throttle:60,1')
                ->name('usage-history');
        });
        
        // Loyalty
        // وفاداری
        Route::group(['prefix' => 'loyalty', 'as' => 'loyalty.'], function () {
            Route::get('points', [BeautyLoyaltyController::class, 'getPoints'])
                ->middleware('throttle:60,1')
                ->name('points');
            Route::get('campaigns', [BeautyLoyaltyController::class, 'getCampaigns'])
                ->middleware('throttle:60,1')
                ->name('campaigns');
            // Points redemption (10 requests per minute - critical operation)
            // استفاده از امتیازها (10 درخواست در دقیقه - عملیات مهم)
            Route::post('redeem', [BeautyLoyaltyController::class, 'redeem'])
                ->middleware('throttle:10,1')
                ->name('redeem');
        });
        
        // Payment (5 requests per minute - critical operation)
        // پرداخت (5 درخواست در دقیقه - عملیات مهم)
        Route::post('payment', [BeautyBookingController::class, 'payment'])
            ->middleware('throttle:5,1')
            ->name('payment');
        
        // Reviews
        // نظرات
        Route::group(['prefix' => 'reviews', 'as' => 'reviews.'], function () {
            // Review creation (5 requests per minute)
            // ایجاد نظر (5 درخواست در دقیقه)
            Route::post('/', [BeautyReviewController::class, 'store'])
                ->middleware('throttle:5,1')
                ->name('store');
            Route::get('/', [BeautyReviewController::class, 'index'])
                ->middleware('throttle:60,1')
                ->name('index');
        });
        
        // Gift Cards
        // کارت‌های هدیه
        Route::group(['prefix' => 'gift-card', 'as' => 'gift-card.'], function () {
            // Gift card purchase (5 requests per minute - critical operation)
            // خرید کارت هدیه (5 درخواست در دقیقه - عملیات مهم)
            Route::post('purchase', [BeautyGiftCardController::class, 'purchase'])
                ->middleware('throttle:5,1')
                ->name('purchase');
            Route::post('redeem', [BeautyGiftCardController::class, 'redeem'])
                ->middleware('throttle:5,1')
                ->name('redeem');
            Route::get('list', [BeautyGiftCardController::class, 'index'])
                ->middleware('throttle:60,1')
                ->name('list');
        });
        
        // Consultations
        // مشاوره‌ها
        Route::group(['prefix' => 'consultations', 'as' => 'consultations.'], function () {
            Route::get('list', [BeautyConsultationController::class, 'list'])
                ->middleware('throttle:60,1')
                ->name('list');
            // Consultation booking (10 requests per minute)
            // رزرو مشاوره (10 درخواست در دقیقه)
            Route::post('book', [BeautyConsultationController::class, 'book'])
                ->middleware('throttle:10,1')
                ->name('book');
            Route::post('check-availability', [BeautyConsultationController::class, 'checkAvailability'])
                ->middleware('throttle:30,1')
                ->name('check-availability');
        });
        
        // Retail Products
        // محصولات خرده‌فروشی
        Route::group(['prefix' => 'retail', 'as' => 'retail.'], function () {
            Route::get('products', [BeautyRetailController::class, 'listProducts'])
                ->middleware('throttle:60,1')
                ->name('products.list');
            Route::get('orders', [BeautyRetailController::class, 'getOrders'])
                ->middleware('throttle:60,1')
                ->name('orders.list');
            Route::get('orders/{id}', [BeautyRetailController::class, 'getOrderDetails'])
                ->middleware('throttle:60,1')
                ->name('orders.show');
            // Retail order creation (10 requests per minute)
            // ایجاد سفارش خرده‌فروشی (10 درخواست در دقیقه)
            Route::post('orders', [BeautyRetailController::class, 'createOrder'])
                ->middleware('throttle:10,1')
                ->name('orders.create');
        });
    });
});

