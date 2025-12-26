# Beauty Booking Module - Configuration Documentation
# مستندات پیکربندی ماژول رزرو زیبایی

## Table of Contents
## فهرست مطالب

1. [Module Enablement](#module-enablement)
2. [Menu Setup](#menu-setup)
3. [Permission Configuration](#permission-configuration)
4. [Config Options](#config-options)
5. [Environment Variables](#environment-variables)
6. [Database Setup](#database-setup)
7. [Cache Configuration](#cache-configuration)

---

## Module Enablement
## فعال‌سازی ماژول

### Step 1: Enable Module in modules_statuses.json
### مرحله 1: فعال‌سازی ماژول در modules_statuses.json

Edit `modules_statuses.json` in the project root:

```json
{
    "BeautyBooking": {
        "status": 1,
        "is_published": 1
    }
}
```

### Step 2: Publish Module
### مرحله 2: انتشار ماژول

Run the following command:

```bash
php artisan module:publish BeautyBooking
```

### Step 3: Run Migrations
### مرحله 3: اجرای Migration ها

```bash
php artisan migrate --path=Modules/BeautyBooking/Database/Migrations
```

### Step 4: Run Seeders
### مرحله 4: اجرای Seeder ها

```bash
php artisan db:seed --class=Modules\\BeautyBooking\\Database\\Seeders\\BeautyBookingDatabaseSeeder
```

### Step 5: Clear Cache
### مرحله 5: پاک کردن Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Menu Setup
## راه‌اندازی منو

### Admin Menu
### منوی ادمین

The module automatically adds menu items to the admin panel. Ensure the following permissions are assigned to admin roles:

- `beauty_salon` - Salon management
- `beauty_category` - Category management
- `beauty_booking` - Booking management
- `beauty_review` - Review management
- `beauty_package` - Package management
- `beauty_gift_card` - Gift card management
- `beauty_retail` - Retail management
- `beauty_loyalty` - Loyalty management
- `beauty_subscription` - Subscription management
- `beauty_commission` - Commission settings
- `beauty_report` - Reports

### Vendor Menu
### منوی فروشنده

The module automatically adds menu items to the vendor panel. Vendors with active salons will see:

- Dashboard
- Staff Management
- Service Management
- Calendar Management
- Booking Management
- Subscription Management
- Package Management
- Gift Card Management
- Retail Management
- Loyalty Management
- Finance Management
- Reports

---

## Permission Configuration
## پیکربندی مجوزها

### Admin Permissions
### مجوزهای ادمین

Permissions are automatically seeded by `BeautyBookingDatabaseSeeder`. To manually assign:

1. Go to Admin Panel → Roles & Permissions
2. Select admin role
3. Enable Beauty Booking module permissions

### Vendor Permissions
### مجوزهای فروشنده

Vendors automatically have access to their salon's data. No additional permission configuration needed.

---

## Config Options
## گزینه‌های Config

All configuration options are in `Modules/BeautyBooking/Config/config.php`.

### Commission Settings
### تنظیمات کمیسیون

```php
'commission' => [
    'default_percentage' => 10.0, // Default commission percentage
    'min_percentage' => 5.0,      // Minimum commission
    'max_percentage' => 20.0,     // Maximum commission
],
```

### Service Fee Settings
### تنظیمات هزینه سرویس

```php
'service_fee' => [
    'percentage' => 2.0,          // Service fee percentage (1-3%)
    'min_amount' => 1000,         // Minimum service fee
    'max_amount' => 10000,        // Maximum service fee
],
```

### Tax Settings
### تنظیمات مالیات

```php
'tax' => [
    'percentage' => 0.0,          // Tax percentage (0-100%)
    'enabled' => false,           // Enable/disable tax
],
```

### Ranking Algorithm Weights
### وزن‌های الگوریتم رتبه‌بندی

```php
'ranking' => [
    'weights' => [
        'location' => 25.0,       // Location distance weight
        'featured' => 20.0,       // Featured/Boost weight
        'rating' => 18.0,         // Rating weight
        'activity' => 10.0,       // Activity weight
        'returning_rate' => 10.0, // Returning customer rate weight
        'availability' => 5.0,    // Availability weight
        'cancellation_rate' => 7.0, // Cancellation rate weight
        'service_type_match' => 5.0, // Service type match weight
    ],
],
```

### Badge Criteria
### معیارهای نشان

```php
'badge' => [
    'top_rated' => [
        'min_rating' => 4.8,              // Minimum rating for Top Rated badge
        'min_bookings' => 50,              // Minimum bookings required
        'max_cancellation_rate' => 2.0,    // Maximum cancellation rate (%)
        'activity_days' => 30,             // Activity check period (days)
    ],
    'featured' => [
        'requires_subscription' => true,   // Requires active subscription
    ],
],
```

### Cancellation Fee Rules
### قوانین هزینه لغو

```php
'cancellation' => [
    'free_cancellation_hours' => 24,      // Free cancellation before booking (hours)
    'partial_fee_hours' => 2,             // Partial fee cancellation window (hours)
    'partial_fee_percentage' => 50.0,     // Partial fee percentage
],
```

### Consultation Settings
### تنظیمات مشاوره

```php
'consultation' => [
    'enabled' => true,                     // Enable consultation feature
    'default_duration_minutes' => 15,      // Default consultation duration
    'default_price' => 50000,              // Default consultation price
    'credit_percentage' => 50.0,           // Credit percentage for consultation
],
```

### Cross-Selling Settings
### تنظیمات فروش متقابل

```php
'cross_selling' => [
    'enabled' => true,                     // Enable cross-selling
    'max_suggestions' => 5,                // Maximum service suggestions
],
```

### Retail Settings
### تنظیمات خرده‌فروشی

```php
'retail' => [
    'enabled' => true,                     // Enable retail feature
    'low_stock_threshold' => 5,            // Low stock alert threshold
],
```

### Subscription Pricing
### قیمت‌گذاری اشتراک

```php
'subscription' => [
    'featured' => [
        '7_days' => 50000,                 // Featured listing 7 days price
        '30_days' => 150000,               // Featured listing 30 days price
    ],
    'boost' => [
        '7_days' => 75000,                 // Boost ads 7 days price
        '30_days' => 200000,               // Boost ads 30 days price
    ],
    'banner' => [
        'homepage' => 100000,              // Homepage banner price
        'category' => 75000,               // Category page banner price
        'search_results' => 60000,         // Search results banner price
    ],
    'dashboard' => [
        'monthly' => 50000,                // Dashboard subscription monthly price
        'yearly' => 500000,                // Dashboard subscription yearly price
    ],
],
```

### Package Settings
### تنظیمات پکیج

```php
'package' => [
    'enabled' => true,                     // Enable package feature
    'min_sessions' => 4,                   // Minimum sessions in package
    'max_sessions' => 8,                   // Maximum sessions in package
    'default_validity_days' => 90,         // Default package validity (days)
    'max_validity_days' => 365,            // Maximum package validity (days)
],
```

### Gift Card Settings
### تنظیمات کارت هدیه

```php
'gift_card' => [
    'enabled' => true,                     // Enable gift card feature
    'min_amount' => 10000,                 // Minimum gift card amount
    'max_amount' => 1000000,               // Maximum gift card amount
    'default_validity_days' => 365,        // Default gift card validity (days)
],
```

### Booking Settings
### تنظیمات رزرو

```php
'booking' => [
    'auto_confirm' => false,               // Auto-confirm bookings (if false, requires salon approval)
    'reminder_hours' => 24,                // Booking reminder hours before appointment
    'reference_prefix' => 'BB-',           // Booking reference prefix
],
```

### Review Settings
### تنظیمات نظر

```php
'review' => [
    'moderation_required' => true,         // Require admin moderation for reviews
    'min_rating' => 1,                     // Minimum rating (1-5)
    'max_rating' => 5,                     // Maximum rating (1-5)
    'allow_attachments' => true,           // Allow review attachments
    'max_attachments' => 5,                // Maximum attachments per review
],
```

### Notification Settings
### تنظیمات نوتیفیکیشن

```php
'notification' => [
    'enabled' => true,                     // Enable notifications
    'channels' => [
        'push' => true,                    // Push notifications
        'email' => true,                   // Email notifications
        'sms' => false,                    // SMS notifications (optional)
    ],
],
```

### Payment Settings
### تنظیمات پرداخت

```php
'payment' => [
    'methods' => [
        'online' => true,                  // Online payment
        'wallet' => true,                  // Wallet payment
        'cash_on_arrival' => true,         // Cash on arrival
    ],
    'default_method' => 'online',         // Default payment method
],
```

### Loyalty Campaign Settings
### تنظیمات کمپین وفاداری

```php
'loyalty' => [
    'enabled' => true,                     // Enable loyalty campaigns
    'default_points_expiry_days' => 365,   // Default points expiry (days)
    'default_commission_percentage' => 5.0, // Default commission percentage (5-10%)
],
```

### Cache Settings
### تنظیمات Cache

```php
'cache' => [
    'ranking_score_ttl' => 1800,           // Ranking score cache TTL (seconds) - 30 minutes
    'badge_ttl' => 3600,                   // Badge cache TTL (seconds) - 1 hour
    'search_ttl' => 300,                   // Search results cache TTL (seconds) - 5 minutes
],
```

---

## Environment Variables
## متغیرهای محیطی

Add the following to your `.env` file:

```env
# Beauty Booking Module Settings
BEAUTY_BOOKING_NOTIFICATION_ENABLED=true
BEAUTY_BOOKING_NOTIFICATION_PUSH=true
BEAUTY_BOOKING_NOTIFICATION_EMAIL=true
BEAUTY_BOOKING_NOTIFICATION_SMS=false

BEAUTY_BOOKING_PAYMENT_ONLINE=true
BEAUTY_BOOKING_PAYMENT_WALLET=true
BEAUTY_BOOKING_PAYMENT_CASH=true
BEAUTY_BOOKING_PAYMENT_DEFAULT=online

BEAUTY_BOOKING_LOYALTY_ENABLED=true
BEAUTY_BOOKING_LOYALTY_POINTS_EXPIRY=365
BEAUTY_BOOKING_LOYALTY_COMMISSION=5.0

# Cache TTL (in seconds)
BEAUTY_BOOKING_CACHE_RANKING_TTL=1800
BEAUTY_BOOKING_CACHE_BADGE_TTL=3600
BEAUTY_BOOKING_CACHE_SEARCH_TTL=300
```

---

## Database Setup
## راه‌اندازی دیتابیس

### Required Tables
### جداول مورد نیاز

The module creates the following tables:

1. `beauty_salons` - Salon information
2. `beauty_staff` - Staff members
3. `beauty_service_categories` - Service categories
4. `beauty_services` - Services
5. `beauty_bookings` - Bookings
6. `beauty_calendar_blocks` - Calendar blocks
7. `beauty_badges` - Badges
8. `beauty_reviews` - Reviews
9. `beauty_subscriptions` - Subscriptions
10. `beauty_packages` - Packages
11. `beauty_package_usages` - Package usage tracking
12. `beauty_gift_cards` - Gift cards
13. `beauty_commission_settings` - Commission settings
14. `beauty_transactions` - Financial transactions
15. `beauty_retail_products` - Retail products
16. `beauty_retail_orders` - Retail orders
17. `beauty_loyalty_campaigns` - Loyalty campaigns
18. `beauty_loyalty_points` - Loyalty points
19. `beauty_monthly_reports` - Monthly reports

### Auto-Increment Starting Points
### نقاط شروع Auto-Increment

- `beauty_bookings` table starts from 100000

### Foreign Key Constraints
### محدودیت‌های Foreign Key

All foreign keys have proper constraints:
- `onDelete('cascade')` for dependent records
- `onDelete('set null')` for optional relationships

---

## Cache Configuration
## پیکربندی Cache

### Cache Drivers
### درایورهای Cache

The module uses Laravel's default cache driver. Configure in `config/cache.php`:

```php
'default' => env('CACHE_DRIVER', 'file'),
```

Recommended drivers:
- **Production**: `redis` or `memcached`
- **Development**: `file` or `array`

### Cache Keys
### کلیدهای Cache

The module uses the following cache key patterns:

- `beauty_ranking_{salon_id}_{hash}` - Ranking scores
- `beauty_badge_{salon_id}` - Badge status
- `beauty_search_{hash}` - Search results
- `beauty_categories_list` - Category list

### Cache Invalidation
### باطل‌سازی Cache

Cache is automatically invalidated when:
- Salon data is updated
- Badges are assigned/revoked
- New bookings are created
- Reviews are added/updated

Manual cache clearing:

```bash
php artisan cache:clear
```

---

## Troubleshooting
## عیب‌یابی

### Module Not Appearing in Menu
### ماژول در منو ظاهر نمی‌شود

1. Check `modules_statuses.json` - ensure `status: 1` and `is_published: 1`
2. Clear cache: `php artisan cache:clear`
3. Check permissions in admin role

### Routes Not Working
### روت‌ها کار نمی‌کنند

1. Run `php artisan route:clear`
2. Run `php artisan route:cache` (production only)
3. Check `RouteServiceProvider` registration

### Database Errors
### خطاهای دیتابیس

1. Ensure all migrations are run: `php artisan migrate`
2. Check foreign key constraints
3. Verify database user has proper permissions

### Cache Issues
### مشکلات Cache

1. Clear cache: `php artisan cache:clear`
2. Check cache driver configuration
3. Verify cache directory permissions (if using file driver)

---

## Best Practices
## بهترین روش‌ها

1. **Always use Form Requests** for validation
2. **Use eager loading** (`with()`) to prevent N+1 queries
3. **Clear cache** after configuration changes
4. **Test migrations** on staging before production
5. **Monitor rate limits** to prevent abuse
6. **Regular backups** of database
7. **Review logs** for errors and warnings

---

## Support
## پشتیبانی

For issues or questions:
- Check documentation files in `Modules/BeautyBooking/Documentation/`
- Review implementation status in `implementation-status.md`
- Check API documentation in `api-documentation.md`

