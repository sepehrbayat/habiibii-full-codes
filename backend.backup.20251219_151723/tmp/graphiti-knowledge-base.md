# 6amMart Laravel - BeautyBooking Module Knowledge Base
# پایگاه دانش ماژول رزرو زیبایی - 6amMart Laravel

## Project Overview
## نمای کلی پروژه

- **Project Name**: 6amMart Laravel
- **Type**: Multi-vendor Marketplace Platform
- **Framework**: Laravel 10
- **PHP Version**: ^8.2
- **Architecture**: Modular Laravel Application
- **Location**: `/home/sepehr/Projects/6ammart-laravel`

## Active Modules
## ماژول‌های فعال

- Gateways
- Rental
- Controller
- DashboardController
- TaxModule
- **BeautyBooking** ✅ (Active)

## BeautyBooking Module
## ماژول رزرو زیبایی

### Status
### وضعیت

- **Completion**: 100% Complete
- **Implementation Phases**: 8 (All completed)
- **Module Enabled**: Yes (`modules_statuses.json`)
- **Routes Registered**: Yes
- **Policies Registered**: Yes
- **Deployment Ready**: Yes

### Core Entities (19 Total)
### موجودیت‌های اصلی (19 مورد)

1. **BeautySalon** - Main salon/clinic entity
2. **BeautyBooking** - Core booking entity
3. **BeautyService** - Services offered by salons
4. **BeautyStaff** - Staff members
5. **BeautyServiceCategory** - Service categories
6. **BeautyReview** - Customer reviews
7. **BeautyBadge** - Badges (Top Rated, Featured, Verified)
8. **BeautyPackage** - Multi-session packages
9. **BeautyGiftCard** - Gift cards
10. **BeautySubscription** - Monthly/annual subscriptions
11. **BeautyTransaction** - Financial transactions
12. **BeautyCalendarBlock** - Calendar blocks
13. **BeautyRetailProduct** - Retail products
14. **BeautyRetailOrder** - Retail orders
15. **BeautyLoyaltyPoint** - Loyalty points
16. **BeautyLoyaltyCampaign** - Loyalty campaigns
17. **BeautyCommissionSetting** - Commission settings
18. **BeautyServiceRelation** - Service relationships
19. **BeautyMonthlyReport** - Monthly reports

### Services (10 Total)
### سرویس‌ها (10 مورد)

1. **BeautyBookingService** - Core booking logic
2. **BeautyCalendarService** - Calendar and availability
3. **BeautyRankingService** - Search ranking algorithm
4. **BeautyBadgeService** - Badge evaluation
5. **BeautyCommissionService** - Commission calculation
6. **BeautyRevenueService** - Revenue tracking
7. **BeautySalonService** - Salon management
8. **BeautyLoyaltyService** - Loyalty points
9. **BeautyRetailService** - Retail sales
10. **BeautyCrossSellingService** - Cross-selling

### Controllers (54 Total)
### کنترلرها (54 مورد)

- **Admin**: 15 controllers
- **Vendor**: 15 controllers
- **Customer Web**: 3 controllers
- **Customer API**: 9 controllers
- **Vendor API**: 12 controllers

### Revenue Models (10 Total)
### مدل‌های درآمدی (10 مورد)

1. Variable Commission (5-20%)
2. Monthly/Annual Subscription
3. Advertising (Featured, Boost, Banners)
4. Service Fee (1-3% from customer)
5. Multi-Session Packages
6. Late Cancellation Fee
7. Consultation Service
8. Cross-Selling/Upsell
9. Retail Sales
10. Gift Cards & Loyalty Campaigns

### Key Features
### ویژگی‌های کلیدی

#### Booking Flow
#### جریان رزرو

1. Category Selection
2. Salon Selection (with ranking)
3. Service Selection
4. Staff/Time Selection
5. Availability Check
6. Price Calculation
7. Payment
8. Confirmation
9. Notification

#### Calendar System
#### سیستم تقویم

- Working Hours (JSON)
- Breaks (JSON)
- Holidays (JSON)
- Manual Blocks
- Staff-specific calendars

#### Badge System
#### سیستم نشان‌ها

- **Top Rated**: Rating > 4.5 AND minimum 50 bookings
- **Featured**: Active subscription
- **Verified**: Manual admin approval

#### Ranking Algorithm
#### الگوریتم رتبه‌بندی

Factors (in order):
1. Location (Haversine distance)
2. Featured/Boost status
3. Rating (weighted average)
4. Activity (last 30 days bookings)
5. Returning customer rate
6. Available time slots

### Integration Points
### نقاط یکپارچه‌سازی

- **Store Model**: `beautySalon()` relationship
- **User Model**: `beautyBookings()` relationship
- **Wallet System**: `beauty_booking` transaction type
- **Payment Gateways**: All existing gateways
- **Chat System**: Internal chat linked to bookings
- **Notifications**: Firebase push notifications
- **Zone Scope**: Zone-based filtering for admin
- **Report Filter**: Report filtering trait

### Code Standards
### استانداردهای کد

- ✅ Strict types enabled (`declare(strict_types=1)`)
- ✅ PSR-12 compliant
- ✅ Bilingual comments (Persian + English)
- ✅ PHPDoc required for all methods
- ✅ Type hints for all parameters
- ✅ Return types for all methods

### Testing
### تست‌ها

- **Feature Tests**: 13
- **Unit Tests**: 6
- **Browser Tests**: 2
- **API Contract Tests**: 1

### Observability
### قابلیت مشاهده

- **OpenTelemetry**: Enabled
- **Observe Agent**: Running on ports 4317, 4318
- **Instrumentation**: `OpenTelemetryInstrumentation` trait
- **Traced Operations**: Booking creation with attributes

### Database
### پایگاه داده

- **Migrations**: 32
- **Tables**: 19
- **Indexes**: All foreign keys, status columns, date columns
- **Soft Deletes**: BeautyBooking, BeautyReview, BeautySalon
- **Auto Increment Override**: beauty_bookings starts at 100000

### Key Patterns
### الگوهای کلیدی

#### Service Layer Pattern
#### الگوی لایه سرویس

```
Controller → Service → Model
```

Business logic in services, not controllers.

#### Dependency Injection
#### تزریق وابستگی

```php
public function __construct(
    private BeautyBookingService $bookingService
) {}
```

#### Relationship Safety
#### ایمنی روابط

```php
if (addon_published_status('BeautyBooking')) {
    return $this->hasMany(...);
}
```

#### API Response Format
#### فرمت پاسخ API

**Success**:
```php
response()->json([
    'message' => translate('...'),
    'data' => $data
], 200);
```

**Error**:
```php
response()->json([
    'errors' => Helpers::error_processor($validator)
], 403);
```

### Important Files
### فایل‌های مهم

- **Service Provider**: `Modules/BeautyBooking/Providers/BeautyBookingServiceProvider.php`
- **Route Provider**: `Modules/BeautyBooking/Providers/RouteServiceProvider.php`
- **Config**: `Modules/BeautyBooking/Config/config.php`
- **Main Booking Service**: `Modules/BeautyBooking/Services/BeautyBookingService.php`
- **Calendar Service**: `Modules/BeautyBooking/Services/BeautyCalendarService.php`
- **Ranking Service**: `Modules/BeautyBooking/Services/BeautyRankingService.php`

### Integration Files
### فایل‌های یکپارچه‌سازی

- `app/Models/Store.php` - beautySalon relationship
- `app/Models/User.php` - beautyBookings relationship
- `app/Scopes/ZoneScope.php` - Zone filtering
- `app/Providers/AuthServiceProvider.php` - Policies
- `app/CentralLogics/customer.php` - Wallet transactions

### Deployment
### استقرار

**Commands**:
```bash
php artisan module:publish BeautyBooking
php artisan migrate
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Environment Variables**:
- `OTEL_ENABLED=true`
- `OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4318`
- `OTEL_SERVICE_NAME=hooshex`
- `OTEL_BEAUTY_BOOKING_ENABLED=true`

### Documentation
### مستندات

- COMPLETION_SUMMARY.md
- implementation-status.md
- configuration.md
- deployment-checklist.md
- api-documentation.md
- flutter-integration.md
- admin-help.md
- OPENTELEMETRY_SETUP.md

## Knowledge Graph Relationships
## روابط گراف دانش

### Project Level
### سطح پروژه

- Project **has_module** → BeautyBooking
- Project **uses_framework** → Laravel 10
- Project **has_integration** → OpenTelemetry, Observe Agent

### Module Level
### سطح ماژول

- BeautyBooking **has_entities** → 19 entities
- BeautyBooking **has_services** → 10 services
- BeautyBooking **has_controllers** → 54 controllers
- BeautyBooking **has_revenue_models** → 10 models
- BeautyBooking **integrates_with** → Store, User, Wallet, Payment, Chat, Notifications, Zone, ReportFilter

### Entity Relationships
### روابط موجودیت‌ها

- BeautySalon **belongs_to** → Store, Zone
- BeautySalon **has_many** → BeautyBooking, BeautyService, BeautyStaff, BeautyReview
- BeautyBooking **belongs_to** → User, BeautySalon, BeautyService, BeautyStaff
- BeautyBooking **uses** → BeautyBookingService, BeautyCalendarService
- BeautyBooking **generates** → BeautyTransaction, BeautyLoyaltyPoint

### Service Dependencies
### وابستگی‌های سرویس

- BeautyBookingService **depends_on** → BeautyCalendarService, BeautyCommissionService
- BeautyBookingService **creates** → BeautyBooking
- BeautyBookingService **calculates** → booking_amounts, cancellation_fees
- BeautyCalendarService **checks** → availability, working_hours, holidays, overlaps
- BeautyRankingService **ranks** → BeautySalon

## Current Status Summary
## خلاصه وضعیت فعلی

✅ **Module Enabled**: Yes
✅ **Routes Registered**: Yes
✅ **Policies Registered**: Yes
✅ **Config Complete**: Yes
✅ **Tests Passing**: Yes
✅ **Code Quality**: PSR-12 compliant, strict types enabled
✅ **Documentation**: Complete
✅ **Deployment Ready**: Yes
✅ **OpenTelemetry Integrated**: Yes
✅ **Observe Agent Running**: Yes

---

**Last Updated**: 2025-11-28
**Created Timestamp**: 20251128-170306
**Version**: 1.0.0
**Graphiti Server**: http://localhost:8001/mcp/

