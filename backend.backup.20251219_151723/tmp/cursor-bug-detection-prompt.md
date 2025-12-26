# پرامپت تشخیص و رفع باگ‌های ماژول Beauty Booking

## دستورالعمل برای Cursor AI

لطفاً یک بررسی کامل و جامع از ماژول `Modules/BeautyBooking/` انجام دهید و تمام باگ‌ها، مشکلات امنیتی، خطاهای منطقی، و مسائل عملکردی را شناسایی کنید.

### مراحل بررسی:

#### 1. بررسی کد (Code Review)
- تمام فایل‌های PHP در ماژول را بررسی کنید
- به دنبال موارد زیر باشید:
  - خطاهای منطقی (Logic Errors)
  - مشکلات امنیتی (SQL Injection, XSS, Authorization)
  - خطاهای Type Hinting و Return Types
  - استفاده نادرست از Eloquent Relationships
  - مشکلات در Exception Handling
  - Race Conditions
  - Memory Leaks یا مشکلات Performance

#### 2. بررسی Database & Migrations
- بررسی Foreign Key Constraints
- بررسی Indexes (کمبود یا اضافی)
- بررسی Data Types (ناسازگاری)
- بررسی Migration Order
- بررسی Soft Deletes

#### 3. بررسی API Endpoints
- بررسی Validation Rules
- بررسی Authorization Checks
- بررسی Error Responses
- بررسی Rate Limiting
- بررسی Response Format Consistency

#### 4. بررسی Business Logic
- بررسی Commission Calculation
- بررسی Cancellation Fee Logic
- بررسی Package Usage Tracking
- بررسی Consultation Credit
- بررسی Ranking Algorithm
- بررسی Badge Assignment Logic

#### 5. بررسی Observers & Events
- بررسی Observer Registration
- بررسی Event Handling
- بررسی Cache Invalidation
- بررسی Statistics Updates

#### 6. بررسی Services
- بررسی Dependency Injection
- بررسی Transaction Safety
- بررسی Error Handling
- بررسی Caching Logic

#### 7. بررسی Tests
- بررسی Test Coverage
- بررسی Test Quality
- بررسی Missing Test Cases
- بررسی Test Data Setup

#### 8. بررسی Integration Points
- بررسی Wallet Integration
- بررسی Payment Gateway Integration
- بررسی Chat System Integration
- بررسی Notification System
- بررسی Zone Scope Integration

### خروجی مورد انتظار:

یک فایل Markdown با ساختار زیر ایجاد کنید:

```markdown
# گزارش باگ‌های ماژول Beauty Booking

## خلاصه
- تعداد کل باگ‌ها: X
- Critical: X
- High: X
- Medium: X
- Low: X

## باگ‌های Critical (اولویت 1)
### [BUG-001] عنوان باگ
- **فایل:** `path/to/file.php:line`
- **توضیحات:** شرح کامل مشکل
- **تأثیر:** چه مشکلی ایجاد می‌کند
- **راه حل پیشنهادی:** چگونه باید رفع شود
- **کد مشکل‌دار:**
```php
// کد مشکل‌دار
```
- **کد اصلاح شده:**
```php
// کد اصلاح شده
```

## باگ‌های High (اولویت 2)
...

## باگ‌های Medium (اولویت 3)
...

## باگ‌های Low (اولویت 4)
...

## مشکلات Performance
...

## مشکلات Security
...

## مشکلات Code Quality
...

## برنامه رفع باگ‌ها
1. [ ] BUG-001: عنوان باگ
2. [ ] BUG-002: عنوان باگ
...
```

### نکات مهم:

1. **اولویت‌بندی:** باگ‌ها را بر اساس شدت تأثیر اولویت‌بندی کنید
2. **مستندسازی:** برای هر باگ، کد مشکل‌دار و راه حل را ارائه دهید
3. **تست:** برای هر باگ، تست مورد نیاز برای تأیید رفع را مشخص کنید
4. **بررسی Cross-cutting:** مشکلاتی که در چند فایل تأثیر می‌گذارند را شناسایی کنید
5. **بررسی Edge Cases:** موارد خاص و مرزی را بررسی کنید

### فایل‌های کلیدی برای بررسی:

- `Modules/BeautyBooking/Services/BeautyBookingService.php`
- `Modules/BeautyBooking/Services/BeautyRankingService.php`
- `Modules/BeautyBooking/Services/BeautyCalendarService.php`
- `Modules/BeautyBooking/Services/BeautyCommissionService.php`
- `Modules/BeautyBooking/Services/BeautyRevenueService.php`
- `Modules/BeautyBooking/Services/BeautyBadgeService.php`
- `Modules/BeautyBooking/Observers/BeautyBookingObserver.php`
- `Modules/BeautyBooking/Observers/BeautyReviewObserver.php`
- `Modules/BeautyBooking/Http/Controllers/Api/Customer/*`
- `Modules/BeautyBooking/Http/Controllers/Api/Vendor/*`
- تمام Migration files
- تمام Model files

### شروع بررسی:

لطفاً بررسی را شروع کنید و نتایج را در فایل `tmp/beauty-booking-bugs-report.md` ذخیره کنید.

