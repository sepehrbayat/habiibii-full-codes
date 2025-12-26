<p align="center"><strong>6amMart | Beauty & Clinic Booking Module</strong><br/>ماژول رزرو زیبایی و کلینیک ۶ام‌مارت</p>

## Overview | نمای کلی
- BeautyBooking is layered on the existing 6amMart platform: users, stores/vendors, payments, zones, notifications, wallet, Passport.  
- این ماژول بر بستر امکانات فعلی ۶ام‌مارت (کاربر، فروشگاه/وندر، پرداخت، زون، اعلان، کیف‌پول، پاسپورت) توسعه یافته است.

## Prerequisites | پیش‌نیازها
- PHP 8.2+، MySQL/MariaDB، Composer، Node/Yarn (برای build دارایی‌های فرانت).  
- فایل env اصلی (`.env`) با تنظیمات DB، CACHE، QUEUE، MAIL، PASSPORT کامل باشد.  
- Cron/queue فعال برای اعلان‌ها، گزارش ماهانه، تمدید اشتراک.

## Installation | نصب
1) PHP deps: `composer install`  
2) JS deps (اختیاری برای build): `npm install && npm run prod`  
3) Migrate: `php artisan migrate`  
4) Seed (اختیاری ولی پیشنهادی):  
   ```bash
   php artisan db:seed --class=Database\\Seeders\\DatabaseSeeder
   # شامل BeautyBookingDatabaseSeeder؛ داده حجیم تست را می‌توان با BeautyBookingTestDataSeeder اضافه کرد
   ```  
5) Demo Data for Testing (اختیاری - برای تست داشبورد مشتری):
   ```bash
   # ایجاد داده کامل برای مشتری john@customer.com
   php artisan db:seed --class=Modules\\BeautyBooking\\Database\\Seeders\\BeautyCustomerDemoSeeder
   
   # ایجاد سالن‌ها و کلینیک‌های متنوع برای انتخاب
   php artisan db:seed --class=Modules\\BeautyBooking\\Database\\Seeders\\BeautyCustomerChoicesSeeder
   ```
   **Demo User Credentials:** `john@customer.com` / password (از seeder اصلی)
6) Passport keys: `php artisan passport:install`

## Running | اجرا
```bash
php artisan serve --host 0.0.0.0 --port 8000
```
- **API Base URL:** `http://localhost:8000/api/v1/beautybooking/`
- **Customer API:** `Modules/BeautyBooking/Routes/api/v1/customer/api.php`  
- **Vendor API:** `Modules/BeautyBooking/Routes/api/v1/vendor/api.php`  
- **Web (Admin/Vendor/Customer):** `Modules/BeautyBooking/Routes/web/...`  
- ماژول در صورت فعال بودن در `modules_statuses.json` بارگذاری می‌شود.

### Key Customer API Endpoints | نقاط پایانی مهم API مشتری
- **Dashboard Summary:** `GET /api/v1/beautybooking/dashboard/summary` - Total bookings, upcoming, spent, packages, consultations, gift cards, loyalty points
- **Notifications:** `GET /api/v1/beautybooking/notifications` - List with pagination, unread count
- **Mark Notifications Read:** `POST /api/v1/beautybooking/notifications/mark-read` - Mark notifications as read
- **Wallet Transactions:** `GET /api/v1/beautybooking/wallet/transactions` - Filtered by `transaction_type` containing "beauty"
- **Bookings:** `GET /api/v1/beautybooking/bookings?type=upcoming|past|cancelled` - List with pagination
- **Booking Conversation:** `GET /api/v1/beautybooking/bookings/{id}/conversation` - Get messages (ascending order)
- **Send Message:** `POST /api/v1/beautybooking/bookings/{id}/conversation` - Send message with optional file attachment
- **Cancel Booking:** `PUT /api/v1/beautybooking/bookings/{id}/cancel` - Enforces 24-hour cancellation rule
- **Reschedule Booking:** `PUT /api/v1/beautybooking/bookings/{id}/reschedule` - Enforces 24-hour reschedule rule with availability validation
- **Service Suggestions:** `GET /api/v1/beautybooking/services/{id}/suggestions?salon_id={id}` - Cross-selling suggestions
- **Loyalty Points:** `GET /api/v1/beautybooking/loyalty/points` - Total, used, and available points

## Tests | تست‌ها
- PHPUnit: `php artisan test`  
- Pest: `./vendor/bin/pest`  
- Smoke: رزرو/سالن/سرویس/کارمند/تقویم/خرده‌فروشی/پکیج/کارت هدیه/اشتراک/وفاداری.
- تست‌ها از اتصال DB جاری (env) استفاده می‌کنند.

## Architecture & Integration | معماری و یکپارچه‌سازی
- Auth/Passport: از همان کلیدها و تنظیمات اصلی استفاده می‌کند.  
- Vendors/Stores: هر سالن به Store/Vendor لینک است؛ کاربران رزرو را ایجاد می‌کنند.  
- Payments/Wallet: مقادیر `service_fee` و `commission_amount` جدا ذخیره می‌شود؛ payout سالن بر اساس کمیسیون انجام می‌شود.  
- Zones: ZoneScope روی کوئری‌های سالن/رزرو اعمال می‌شود.  
- Notifications: از helperهای موجود برای Push/Email استفاده می‌کند.  
- Reporting: گزارش‌های مالی/ماهانه/Badge/Ranking در Services و Commands پیاده‌سازی شده‌اند.

## Module Structure | ساختار ماژول
- Models: `Modules/BeautyBooking/Entities`  
- Migrations: `Modules/BeautyBooking/Database/Migrations` (پیشوند همه جداول `beauty_`)  
- Factories/Seeders: `Modules/BeautyBooking/Database/{Factories,Seeders}`  
- Controllers: `Http/Controllers/{Api,Web}/{Admin,Vendor,Customer}`  
- Services: منطق اصلی (Booking, Calendar, Commission, Ranking, Revenue, Loyalty, Retail)  
- Traits: ابزار مشترک (BookingLogic, PushNotification, ApiResponse)  
- Resources: زبان‌ها (fa/en), Blade views, assets  
- Tests: Feature/Unit/Browser در `Modules/BeautyBooking/Tests` + تست‌های اصلی در `tests/`

## Data & Business Rules | داده و قواعد تجاری
- جداول کلیدی: beauty_salons, beauty_services, beauty_staff, beauty_bookings, beauty_packages, beauty_gift_cards, beauty_subscriptions, beauty_loyalty_*, beauty_retail_* ، beauty_transactions, user_notifications.  
- Status flows: booking (pending→confirmed→completed/cancelled/no_show)، subscription (active/expired/pending), gift cards (active/redeemed/expired).  
- Amounts: `total_amount` شامل base + service_fee + commission_amount؛ کمیسیون برای سهم پلتفرم و payout سالن جدا ذخیره می‌شود.

### Business Rules | قواعد تجاری
- **Cancellation Window:** Customers cannot cancel bookings within 24 hours of the booking time. Returns 422 with code `cancellation_window_passed`.
- **Reschedule Window:** Customers cannot reschedule bookings within 24 hours (configurable via `beautybooking.reschedule_threshold_hours`). Returns 422 with code `reschedule_window_passed`.
- **Availability Validation:** All booking creation and rescheduling operations validate slot availability. Returns 422 with code `slot_unavailable` and includes `available_slots` if possible.
- **Service Fee:** 1-3% of base price (configurable) charged to customer.
- **Commission:** Variable commission (5-20%) based on service category and salon level, deducted from salon payout.

## Deployment Checklist | چک‌لیست استقرار
- `php artisan migrate`  
- (اختیاری) `php artisan db:seed --class=Database\\Seeders\\DatabaseSeeder`  
- `php artisan passport:install` در اولین استقرار  
- فعال بودن ماژول در `modules_statuses.json`  
- راه‌اندازی cron/queue برای jobs ماژول  
- در صورت نیاز `php artisan storage:link`

## Common Issues & Fixes | مشکلات رایج و راه‌حل‌ها

### Type Errors
- **Issue:** `TypeError: abs(): Argument #1 ($num) must be of type int|float, string given`
- **Fix:** Cast database sum results to float before using `abs()`: `abs((float)$sumResult)`
- **Locations:** `BeautyLoyaltyController::getPoints()`, `BeautyDashboardController::summary()`

### Missing Tables
- **Issue:** `Table 'user_notifications' doesn't exist`
- **Fix:** Run migration: `php artisan migrate` (includes `2025_12_10_160000_create_user_notifications_table.php`)

### Service Suggestions TypeError
- **Issue:** `Argument #3 ($salonId) must be of type ?int, string given`
- **Fix:** Cast `salon_id` query parameter to int: `$salonId = $request->salon_id ? (int)$request->salon_id : null`

### Null Pointer Exceptions
- **Issue:** `Attempt to read property "value" on null` in `helpers.php::schedule_order()`
- **Fix:** Add null check: `return $setting ? (bool)$setting->value : false;`

## Testing Guide | راهنمای تست

### Using Demo Data
1. Run `BeautyCustomerDemoSeeder` to create comprehensive test data for `john@customer.com`
2. Run `BeautyCustomerChoicesSeeder` to add diverse salons and clinics
3. Login with demo credentials and test all dashboard features:
   - Bookings (upcoming, past, cancelled)
   - Packages (active packages count)
   - Consultations (pending consultations)
   - Gift Cards (balance)
   - Loyalty Points (total, used, available)
   - Wallet Transactions (filtered by beauty transaction types)
   - Notifications (with read/unread status)

### API Testing
- Use Postman/Insomnia with Bearer token authentication
- Base URL: `http://localhost:8000/api/v1/beautybooking/`
- All endpoints support pagination via `limit`/`offset` parameters
- Error responses include specific error codes for frontend handling

## Support | پشتیبانی
- برای گزارش باگ یا نیاز پشتیبانی: نسخه PHP/DB، نام شاخه، و آخرین کامیت را اعلام کنید.  
- در صورت مواجهه با مشکل مایگریشن/سید، ترتیب اجرای مایگریشن‌های beauty و core را بررسی کنید.
- برای مشکلات API، لاگ Laravel را در `storage/logs/laravel.log` بررسی کنید.