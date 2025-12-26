# Beauty Booking Module - Deployment Checklist
# چک‌لیست استقرار ماژول رزرو زیبایی

## Pre-Deployment
# قبل از استقرار

### Code Quality
# کیفیت کد

- [ ] Run StyleCI or PHP CS Fixer: `composer fix-style` or `./vendor/bin/php-cs-fixer fix`
- [ ] Run PHPUnit tests: `php artisan test --filter BeautyBooking`
- [ ] Run Dusk browser tests: `php artisan dusk --filter BeautyBooking`
- [ ] Check for lint errors: `./vendor/bin/phpstan analyse Modules/BeautyBooking`
- [ ] Verify all PHPDoc comments are bilingual (Persian + English)
- [ ] Ensure all controllers are thin (delegate to services)
- [ ] Verify no hardcoded values (use config/constants)

### Database
# پایگاه داده

- [ ] Backup production database
- [ ] Test migrations on staging: `php artisan migrate --path=Modules/BeautyBooking/Database/Migrations`
- [ ] Test rollback: `php artisan migrate:rollback --step=32`
- [ ] Verify seeders: `php artisan db:seed --class=Modules\\BeautyBooking\\Database\\Seeders\\BeautyBookingDatabaseSeeder`
- [ ] Check all foreign key constraints are in place
- [ ] Verify indexes on hot query columns (status, dates, foreign keys)
- [ ] Test auto-increment starting point for booking tables (100000)

### Configuration
# پیکربندی

- [ ] Verify `modules_statuses.json` includes BeautyBooking with status: 1
- [ ] Check `Config/config.php` has all required settings
- [ ] Verify environment variables are set:
  - `BEAUTY_BOOKING_COMMISSION_DEFAULT`
  - `BEAUTY_BOOKING_SERVICE_FEE_PERCENTAGE`
  - `BEAUTY_BOOKING_RANKING_WEIGHT_*`
- [ ] Test config caching: `php artisan config:cache`

### Routes & Permissions
# مسیرها و مجوزها

- [ ] Verify routes are registered in `RouteServiceProvider`
- [ ] Test admin routes: `/admin/beautybooking/*`
- [ ] Test vendor routes: `/vendor/beautybooking/*`
- [ ] Test customer routes: `/beautybooking/*`
- [ ] Test API routes: `/api/v1/beautybooking/*`
- [ ] Verify permissions are seeded for admin/vendor/customer roles
- [ ] Test menu visibility with permission checks

### Assets & Views
# دارایی‌ها و نمایش‌ها

- [ ] Build frontend assets: `npm run build` or `npm run dev`
- [ ] Verify all Blade views exist and compile without errors
- [ ] Test responsive design on mobile/tablet/desktop
- [ ] Verify translation keys exist in `resources/lang/en/` and `resources/lang/fa/`
- [ ] Test file uploads (salon documents, review images)

## Deployment Steps
# مراحل استقرار

### 1. Module Activation
# فعال‌سازی ماژول

```bash
# Enable module
php artisan module:publish BeautyBooking

# Run migrations
php artisan migrate --path=Modules/BeautyBooking/Database/Migrations

# Seed default data
php artisan db:seed --class=Modules\\BeautyBooking\\Database\\Seeders\\BeautyBookingDatabaseSeeder

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. Permissions & Roles
# مجوزها و نقش‌ها

- [ ] Assign Beauty Booking permissions to admin role
- [ ] Assign Beauty Booking permissions to vendor role
- [ ] Verify customer permissions (usually public)
- [ ] Test permission checks in controllers

### 3. Menu Integration
# یکپارچه‌سازی منو

- [ ] Verify admin sidebar includes Beauty Booking section
- [ ] Verify vendor sidebar includes Beauty Booking section
- [ ] Test menu item visibility with permissions
- [ ] Verify active state highlighting works

### 4. API Configuration
# پیکربندی API

- [ ] Test API authentication (Sanctum tokens)
- [ ] Verify rate limiting is configured
- [ ] Test pagination on list endpoints
- [ ] Verify error response format
- [ ] Test file upload endpoints

### 5. Payment Integration
# یکپارچه‌سازی پرداخت

- [ ] Test wallet payment flow
- [ ] Test digital payment gateway integration
- [ ] Test cash on arrival flow
- [ ] Verify transaction recording in `beauty_transactions`

### 6. Notification Setup
# راه‌اندازی اعلان‌ها

- [ ] Configure Firebase for push notifications
- [ ] Test booking confirmation notifications
- [ ] Test booking reminder notifications (24h before)
- [ ] Test cancellation notifications
- [ ] Verify email notifications (if enabled)

## Post-Deployment
# پس از استقرار

### Verification
# تأیید

- [ ] Test complete booking flow (customer → vendor → admin)
- [ ] Test salon registration and approval
- [ ] Test calendar availability checking
- [ ] Test commission calculation for all 10 revenue models
- [ ] Test badge auto-assignment (Top Rated, Featured, Verified)
- [ ] Test ranking algorithm with sample salons
- [ ] Test cancellation fee calculation
- [ ] Test package redemption
- [ ] Test loyalty points earning/redeeming
- [ ] Test gift card issuance/redemption
- [ ] Test subscription purchase and renewal
- [ ] Test financial reports (admin and vendor)

### Performance
# عملکرد

- [ ] Monitor database query performance
- [ ] Check N+1 query issues (use eager loading)
- [ ] Verify caching is working (ranking, badges, search results)
- [ ] Test search performance with large dataset
- [ ] Monitor API response times

### Security
# امنیت

- [ ] Verify input validation on all endpoints
- [ ] Test SQL injection prevention
- [ ] Test file upload validation (type, size, content)
- [ ] Verify authorization checks (vendor can only access own data)
- [ ] Test CSRF protection on web forms
- [ ] Verify API authentication (tokens, rate limiting)

### Monitoring
# نظارت

- [ ] Set up error logging for Beauty Booking module
- [ ] Monitor booking creation rate
- [ ] Track commission calculations
- [ ] Monitor cancellation rates
- [ ] Track badge assignments
- [ ] Monitor API usage and rate limit hits

## Rollback Plan
# برنامه بازگشت

If issues occur:

```bash
# Rollback migrations
php artisan migrate:rollback --step=32

# Disable module
# Edit modules_statuses.json: set BeautyBooking status to 0

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Support & Documentation
# پشتیبانی و مستندات

- [ ] Admin user guide for salon approval workflow
- [ ] Vendor guide for calendar and booking management
- [ ] Customer guide for booking process
- [ ] API documentation for mobile app developers
- [ ] Troubleshooting guide for common issues

## Success Criteria
# معیارهای موفقیت

- [ ] All tests pass
- [ ] No critical errors in logs
- [ ] Booking flow works end-to-end
- [ ] Commission calculations are accurate
- [ ] Badges are assigned correctly
- [ ] Ranking algorithm produces expected results
- [ ] All 10 revenue models are functional
- [ ] Admin, vendor, and customer portals are accessible
- [ ] API endpoints return correct responses
- [ ] Mobile app can integrate successfully

