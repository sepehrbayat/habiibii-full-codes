<?php

declare(strict_types=1);

/**
 * Beauty Booking Module Configuration
 * تنظیمات ماژول رزرو زیبایی
 *
 * All configurable settings for the Beauty Booking module
 * تمام تنظیمات قابل پیکربندی برای ماژول رزرو زیبایی
 */

return [
    'name' => 'BeautyBooking',
    
    /**
     * Commission Settings
     * تنظیمات کمیسیون
     */
    'commission' => [
        'default_percentage' => (float)env('BEAUTY_BOOKING_COMMISSION_DEFAULT', 10.0), // Default commission percentage
        'min_percentage' => (float)env('BEAUTY_BOOKING_COMMISSION_MIN', 5.0), // Minimum commission percentage
        'max_percentage' => (float)env('BEAUTY_BOOKING_COMMISSION_MAX', 20.0), // Maximum commission percentage
        'min_amount' => (float)env('BEAUTY_BOOKING_COMMISSION_MIN_AMOUNT', 0), // Minimum commission amount
        'max_amount' => env('BEAUTY_BOOKING_COMMISSION_MAX_AMOUNT', null) !== null ? (float)env('BEAUTY_BOOKING_COMMISSION_MAX_AMOUNT') : null, // Maximum commission amount (null = no limit)
        'top_rated_discount' => (float)env('BEAUTY_BOOKING_COMMISSION_TOP_RATED_DISCOUNT', 2.0), // Commission discount for Top Rated salons (percentage points)
        
        // Commission by service type
        // کمیسیون بر اساس نوع خدمت
        'by_service_type' => [
            'salon' => 10.0, // Beauty salon services: 10-20%
            'clinic' => 7.5, // Clinic services: 5-10%
        ],
    ],
    
    /**
     * Service Fee Settings
     * تنظیمات هزینه سرویس
     * 
     * Service fee is charged to customers (1-3% of booking amount)
     * هزینه سرویس از مشتری دریافت می‌شود (1-3٪ از مبلغ رزرو)
     */
    'service_fee' => [
        'percentage' => (float)env('BEAUTY_BOOKING_SERVICE_FEE_PERCENTAGE', 2.0), // Default: 2%
        'min_percentage' => (float)env('BEAUTY_BOOKING_SERVICE_FEE_MIN', 1.0), // Minimum: 1%
        'max_percentage' => (float)env('BEAUTY_BOOKING_SERVICE_FEE_MAX', 3.0), // Maximum: 3%
        'fixed_amount' => env('BEAUTY_BOOKING_SERVICE_FEE_FIXED', null) !== null ? (float)env('BEAUTY_BOOKING_SERVICE_FEE_FIXED') : null, // Fixed amount (null = use percentage)
    ],
    
    /**
     * Tax Settings
     * تنظیمات مالیات
     */
    'tax' => [
        'percentage' => (float)env('BEAUTY_BOOKING_TAX_PERCENTAGE', 0.0), // Tax percentage (default: 0%)
        'included_in_price' => (bool)env('BEAUTY_BOOKING_TAX_INCLUDED', false), // Whether tax is included in base price
    ],
    
    /**
     * Ranking Algorithm Weights
     * وزن‌های الگوریتم رتبه‌بندی
     * 
     * Total must equal 100%
     * مجموع باید برابر 100٪ باشد
     */
    'ranking' => [
        'weights' => [
            'location' => (float)env('BEAUTY_BOOKING_RANKING_WEIGHT_LOCATION', 25.0), // Location distance: 25%
            'featured' => (float)env('BEAUTY_BOOKING_RANKING_WEIGHT_FEATURED', 20.0), // Featured/Boost status: 20%
            'rating' => (float)env('BEAUTY_BOOKING_RANKING_WEIGHT_RATING', 18.0), // Rating: 18%
            'activity' => (float)env('BEAUTY_BOOKING_RANKING_WEIGHT_ACTIVITY', 10.0), // Activity (30 days): 10%
            'returning_rate' => (float)env('BEAUTY_BOOKING_RANKING_WEIGHT_RETURNING', 10.0), // Returning customer rate: 10%
            'availability' => (float)env('BEAUTY_BOOKING_RANKING_WEIGHT_AVAILABILITY', 5.0), // Available time slots: 5%
            'cancellation_rate' => (float)env('BEAUTY_BOOKING_RANKING_WEIGHT_CANCELLATION', 7.0), // Cancellation rate (lower is better): 7%
            'service_type_match' => (float)env('BEAUTY_BOOKING_RANKING_WEIGHT_SERVICE_TYPE', 5.0), // Service type matching: 5%
        ],
        
        // Location scoring thresholds (in kilometers)
        // آستانه‌های امتیازدهی موقعیت (به کیلومتر)
        'location_thresholds' => [
            'excellent' => (int)env('BEAUTY_BOOKING_RANKING_LOCATION_EXCELLENT', 10), // 0-10km: score 1.0
            'good' => (int)env('BEAUTY_BOOKING_RANKING_LOCATION_GOOD', 20), // 10-20km: score 0.8
            'fair' => (int)env('BEAUTY_BOOKING_RANKING_LOCATION_FAIR', 50), // 20-50km: score 0.6
            'poor' => (int)env('BEAUTY_BOOKING_RANKING_LOCATION_POOR', 50), // 50+km: score 0.4
        ],
        
        // Activity scoring
        // امتیازدهی فعالیت
        'activity' => [
            'days' => (int)env('BEAUTY_BOOKING_RANKING_ACTIVITY_DAYS', 30), // Number of days to consider
            'max_bookings' => (int)env('BEAUTY_BOOKING_RANKING_ACTIVITY_MAX', 50), // Bookings for max score (1.0)
        ],
        
        // Availability calculation
        // محاسبه دسترسی
        'availability' => [
            'days_ahead' => (int)env('BEAUTY_BOOKING_RANKING_AVAILABILITY_DAYS', 7), // Days to check ahead
            'slot_interval' => (int)env('BEAUTY_BOOKING_RANKING_AVAILABILITY_INTERVAL', 30), // Slot interval in minutes
            'min_duration' => (int)env('BEAUTY_BOOKING_RANKING_AVAILABILITY_MIN_DURATION', 30), // Minimum service duration
        ],
        
        // Returning customer rate
        // نرخ مشتری برگشتی
        'returning_rate' => [
            'expected_rate' => (float)env('BEAUTY_BOOKING_RANKING_RETURNING_RATE', 0.3), // Expected returning rate (30%)
        ],
        
        // Rating scoring
        // امتیازدهی رتبه‌بندی
        'rating' => [
            'min_reviews' => (int)env('BEAUTY_BOOKING_RANKING_MIN_REVIEWS', 5), // Minimum reviews for full rating score
            'global_average' => (float)env('BEAUTY_BOOKING_RANKING_GLOBAL_AVG', 3.5), // Global average rating for Bayesian adjustment
        ],
    ],
    
    /**
     * Badge Criteria
     * معیارهای نشان‌ها
     */
    'badge' => [
        'top_rated' => [
            'min_rating' => (float)env('BEAUTY_BOOKING_BADGE_TOP_RATED_MIN_RATING', 4.8), // Minimum rating: 4.8 (as per requirements)
            'min_bookings' => (int)env('BEAUTY_BOOKING_BADGE_TOP_RATED_MIN_BOOKINGS', 50), // Minimum bookings: 50
            'max_cancellation_rate' => (float)env('BEAUTY_BOOKING_BADGE_TOP_RATED_MAX_CANCELLATION', 2.0), // Max cancellation rate: 2%
            'activity_days' => (int)env('BEAUTY_BOOKING_BADGE_TOP_RATED_ACTIVITY_DAYS', 30), // Must be active in last 30 days
        ],
        'featured' => [
            'requires_subscription' => (bool)env('BEAUTY_BOOKING_BADGE_FEATURED_REQUIRES_SUB', true), // Requires active subscription
        ],
        'verified' => [
            'manual_approval' => (bool)env('BEAUTY_BOOKING_BADGE_VERIFIED_MANUAL', true), // Manual admin approval required
        ],
    ],
    
    /**
     * Cancellation Fee Settings
     * تنظیمات جریمه لغو
     */
    'cancellation_fee' => [
        'time_thresholds' => [
            'no_fee_hours' => (int)env('BEAUTY_BOOKING_CANCELLATION_NO_FEE_HOURS', 24), // No fee if cancelled 24+ hours before
            'partial_fee_hours' => (int)env('BEAUTY_BOOKING_CANCELLATION_PARTIAL_FEE_HOURS', 2), // Partial fee if cancelled 2-24 hours before
        ],
        'fee_percentages' => [
            'no_fee' => (float)env('BEAUTY_BOOKING_CANCELLATION_FEE_NONE', 0.0), // 0% fee (24+ hours)
            'partial' => (float)env('BEAUTY_BOOKING_CANCELLATION_FEE_PARTIAL', 50.0), // 50% fee (2-24 hours)
            'full' => (float)env('BEAUTY_BOOKING_CANCELLATION_FEE_FULL', 100.0), // 100% fee (< 2 hours)
        ],
    ],
    
    /**
     * Consultation Service Settings
     * تنظیمات سرویس مشاوره
     */
    'consultation' => [
        'commission_percentage' => (float)env('BEAUTY_BOOKING_CONSULTATION_COMMISSION', 10.0), // Commission percentage for consultations
        'credit_to_main_service' => (bool)env('BEAUTY_BOOKING_CONSULTATION_CREDIT_ENABLED', true), // Allow credit to main service
        'max_credit_percentage' => (float)env('BEAUTY_BOOKING_CONSULTATION_MAX_CREDIT', 100.0), // Maximum credit percentage (100% = full credit)
    ],
    
    /**
     * Cross-selling/Upsell Settings
     * تنظیمات فروش متقابل/افزایش فروش
     */
    'cross_selling' => [
        'commission_percentage' => (float)env('BEAUTY_BOOKING_CROSS_SELLING_COMMISSION', 10.0), // Commission percentage for cross-sold services
        'enabled' => (bool)env('BEAUTY_BOOKING_CROSS_SELLING_ENABLED', true), // Enable cross-selling feature
        'max_suggestions' => (int)env('BEAUTY_BOOKING_CROSS_SELLING_MAX_SUGGESTIONS', 5), // Maximum number of suggestions
    ],
    
    /**
     * Retail Sales Settings
     * تنظیمات فروش خرده‌فروشی
     */
    'retail' => [
        'commission_percentage' => (float)env('BEAUTY_BOOKING_RETAIL_COMMISSION', 10.0), // Commission percentage for retail sales
        'enabled' => (bool)env('BEAUTY_BOOKING_RETAIL_ENABLED', true), // Enable retail sales feature
    ],
    
    /**
     * Subscription/Advertisement Settings
     * تنظیمات اشتراک/تبلیغات
     */
    'subscription' => [
        'featured' => [
            '7_days' => (int)env('BEAUTY_BOOKING_SUBSCRIPTION_FEATURED_7_DAYS', 50000), // Featured listing 7 days price
            '30_days' => (int)env('BEAUTY_BOOKING_SUBSCRIPTION_FEATURED_30_DAYS', 150000), // Featured listing 30 days price
        ],
        'boost' => [
            '7_days' => (int)env('BEAUTY_BOOKING_SUBSCRIPTION_BOOST_7_DAYS', 75000), // Boost ads 7 days price
            '30_days' => (int)env('BEAUTY_BOOKING_SUBSCRIPTION_BOOST_30_DAYS', 200000), // Boost ads 30 days price
        ],
        'banner' => [
            'homepage' => (int)env('BEAUTY_BOOKING_SUBSCRIPTION_BANNER_HOMEPAGE', 100000), // Homepage banner price per month
            'category' => (int)env('BEAUTY_BOOKING_SUBSCRIPTION_BANNER_CATEGORY', 75000), // Category page banner price per month
            'search_results' => (int)env('BEAUTY_BOOKING_SUBSCRIPTION_BANNER_SEARCH_RESULTS', 60000), // Search results banner price per month
        ],
        'dashboard' => [
            'monthly' => (int)env('BEAUTY_BOOKING_SUBSCRIPTION_DASHBOARD_MONTHLY', 50000), // Advanced dashboard monthly price
            'yearly' => (int)env('BEAUTY_BOOKING_SUBSCRIPTION_DASHBOARD_YEARLY', 500000), // Advanced dashboard yearly price
        ],
    ],
    
    /**
     * Package Settings
     * تنظیمات پکیج
     */
    'package' => [
        'min_sessions' => (int)env('BEAUTY_BOOKING_PACKAGE_MIN_SESSIONS', 4), // Minimum sessions in a package
        'max_sessions' => (int)env('BEAUTY_BOOKING_PACKAGE_MAX_SESSIONS', 8), // Maximum sessions in a package
        'default_validity_days' => (int)env('BEAUTY_BOOKING_PACKAGE_DEFAULT_VALIDITY', 90), // Default validity in days
        'commission_on_total' => (bool)env('BEAUTY_BOOKING_PACKAGE_COMMISSION_TOTAL', true), // Commission on total package price
    ],
    
    /**
     * Gift Card Settings
     * تنظیمات کارت هدیه
     */
    'gift_card' => [
        'commission_percentage' => (float)env('BEAUTY_BOOKING_GIFT_CARD_COMMISSION', 5.0), // Commission percentage (5-10%)
        'min_amount' => (int)env('BEAUTY_BOOKING_GIFT_CARD_MIN_AMOUNT', 10000), // Minimum gift card amount
        'max_amount' => (int)env('BEAUTY_BOOKING_GIFT_CARD_MAX_AMOUNT', 1000000), // Maximum gift card amount
        'validity_days' => (int)env('BEAUTY_BOOKING_GIFT_CARD_VALIDITY', 365), // Default validity in days
    ],
    
    /**
     * Booking Settings
     * تنظیمات رزرو
     */
    'booking' => [
        'min_advance_hours' => (int)env('BEAUTY_BOOKING_MIN_ADVANCE_HOURS', 2), // Minimum hours in advance for booking
        'max_advance_days' => (int)env('BEAUTY_BOOKING_MAX_ADVANCE_DAYS', 90), // Maximum days in advance for booking
        'auto_confirm' => (bool)env('BEAUTY_BOOKING_AUTO_CONFIRM', false), // Auto-confirm bookings (false = requires salon approval)
        'reminder_hours_before' => (int)env('BEAUTY_BOOKING_REMINDER_HOURS', 24), // Send reminder 24 hours before booking
    ],
    
    /**
     * Review Settings
     * تنظیمات نظرات
     */
    'review' => [
        'require_completion' => (bool)env('BEAUTY_BOOKING_REVIEW_REQUIRE_COMPLETION', true), // Require booking completion before review
        'moderation_enabled' => (bool)env('BEAUTY_BOOKING_REVIEW_MODERATION', true), // Enable review moderation
        'auto_publish' => (bool)env('BEAUTY_BOOKING_REVIEW_AUTO_PUBLISH', false), // Auto-publish reviews (false = requires admin approval)
    ],
    
    /**
     * Notification Settings
     * تنظیمات نوتیفیکیشن
     */
    'notification' => [
        'enabled' => (bool)env('BEAUTY_BOOKING_NOTIFICATION_ENABLED', true), // Enable notifications
        'channels' => [
            'push' => (bool)env('BEAUTY_BOOKING_NOTIFICATION_PUSH', true), // Push notifications
            'email' => (bool)env('BEAUTY_BOOKING_NOTIFICATION_EMAIL', true), // Email notifications
            'sms' => (bool)env('BEAUTY_BOOKING_NOTIFICATION_SMS', false), // SMS notifications (optional)
        ],
    ],
    
    /**
     * Payment Settings
     * تنظیمات پرداخت
     */
    'payment' => [
        'methods' => [
            'online' => (bool)env('BEAUTY_BOOKING_PAYMENT_ONLINE', true), // Online payment
            'wallet' => (bool)env('BEAUTY_BOOKING_PAYMENT_WALLET', true), // Wallet payment
            'cash_on_arrival' => (bool)env('BEAUTY_BOOKING_PAYMENT_CASH', true), // Cash on arrival
        ],
        'default_method' => env('BEAUTY_BOOKING_PAYMENT_DEFAULT', 'online'), // Default payment method
    ],
    
    /**
     * Loyalty Campaign Settings
     * تنظیمات کمپین‌های وفاداری
     */
    'loyalty' => [
        'enabled' => (bool)env('BEAUTY_BOOKING_LOYALTY_ENABLED', true), // Enable loyalty campaigns
        'default_points_expiry_days' => (int)env('BEAUTY_BOOKING_LOYALTY_POINTS_EXPIRY', 365), // Default points expiry in days
        'default_commission_percentage' => (float)env('BEAUTY_BOOKING_LOYALTY_COMMISSION', 5.0), // Default commission percentage (5-10%)
    ],
    
    /**
     * Cache Settings
     * تنظیمات Cache
     */
    'cache' => [
        'ranking_score_ttl' => (int)env('BEAUTY_BOOKING_CACHE_RANKING_TTL', 1800), // Ranking score cache TTL in seconds (30 minutes)
        'badge_ttl' => (int)env('BEAUTY_BOOKING_CACHE_BADGE_TTL', 3600), // Badge cache TTL in seconds (1 hour)
        'search_ttl' => (int)env('BEAUTY_BOOKING_CACHE_SEARCH_TTL', 300), // Search results cache TTL in seconds (5 minutes)
        'categories_ttl' => (int)env('BEAUTY_BOOKING_CACHE_CATEGORIES_TTL', 86400), // Categories cache TTL in seconds (24 hours)
        'popular_salons_ttl' => (int)env('BEAUTY_BOOKING_CACHE_POPULAR_SALONS_TTL', 3600), // Popular salons cache TTL in seconds (1 hour)
        'top_rated_salons_ttl' => (int)env('BEAUTY_BOOKING_CACHE_TOP_RATED_SALONS_TTL', 3600), // Top rated salons cache TTL in seconds (1 hour)
    ],
    
    /**
     * Feature Flags
     * پرچم‌های ویژگی
     * 
     * Control feature visibility for staged rollout
     * کنترل نمایش ویژگی‌ها برای rollout مرحله‌ای
     */
    'features' => [
        'booking' => ['enabled' => (bool)env('BEAUTY_BOOKING_FEATURE_BOOKING', true)],
        'salon' => ['enabled' => (bool)env('BEAUTY_BOOKING_FEATURE_SALON', true)],
        'subscription' => ['enabled' => (bool)env('BEAUTY_BOOKING_FEATURE_SUBSCRIPTION', true)],
        'retail' => ['enabled' => (bool)env('BEAUTY_BOOKING_FEATURE_RETAIL', true)],
        'loyalty' => ['enabled' => (bool)env('BEAUTY_BOOKING_FEATURE_LOYALTY', true)],
        'reports' => ['enabled' => (bool)env('BEAUTY_BOOKING_FEATURE_REPORTS', true)],
    ],
];
