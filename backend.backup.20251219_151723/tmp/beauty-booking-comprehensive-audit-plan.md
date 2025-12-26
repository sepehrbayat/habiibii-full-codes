# پلن جامع بررسی و رفع باگ‌های ماژول Beauty Booking
## Comprehensive Audit and Bug Fix Plan for Beauty Booking Module

**تاریخ:** 2025-01-23  
**Date:** 2025-01-23

---

## خلاصه اجرایی / Executive Summary

این سند یک پلن جامع برای بررسی کامل ماژول Beauty Booking و شناسایی تمام باگ‌ها، نقص‌های منطقی، و جاافتادگی‌ها است. هدف از این بررسی اطمینان از صحت کامل پیاده‌سازی تمام ویژگی‌ها و منطق‌های کسب‌وکار است.

This document is a comprehensive plan for complete audit of the Beauty Booking module to identify all bugs, logic flaws, and gaps. The goal is to ensure complete correctness of all features and business logic implementations.

---

## فاز 1: بررسی باگ‌های منطقی شناسایی شده / Phase 1: Review Identified Logic Bugs

### [BUG-LOGIC-001] Missing Exception in Package Validation
**اولویت:** High  
**فایل:** `Modules/BeautyBooking/Services/BeautyBookingService.php:81-83`

**مشکل:**
```php
if (!$package->isValidForUser($userId)) {
    // هیچ exception throw نمی‌شود!
}
```

**تأثیر:**
- اگر پکیج معتبر نباشد (منقضی شده، تمام جلسات استفاده شده، غیرفعال)، رزرو همچنان ایجاد می‌شود
- این باعث می‌شود که کاربر بتواند از پکیج‌های نامعتبر استفاده کند

**راه حل:**
```php
if (!$package->isValidForUser($userId)) {
    throw new \Exception(translate('messages.package_not_valid_or_expired'));
}
```

**تست:**
- تست رزرو با پکیج منقضی شده
- تست رزرو با پکیج تمام شده
- تست رزرو با پکیج غیرفعال

---

### [BUG-LOGIC-002] Gift Card Not Applied in Booking Discount Calculation
**اولویت:** Medium  
**فایل:** `Modules/BeautyBooking/Services/BeautyBookingService.php:413-457`

**مشکل:**
- متد `calculateDiscount()` فقط کوپن و پکیج را در نظر می‌گیرد
- Gift Card در محاسبه تخفیف در نظر گرفته نشده است

**تأثیر:**
- کاربر نمی‌تواند از Gift Card در رزرو استفاده کند
- Gift Card فقط به wallet اضافه می‌شود، اما در محاسبه مبلغ رزرو اعمال نمی‌شود

**راه حل:**
- اضافه کردن منطق اعمال Gift Card در `calculateDiscount()`
- یا استفاده از wallet balance که شامل gift card است

**بررسی نیاز:**
- بررسی اینکه آیا Gift Card باید مستقیماً در booking اعمال شود یا از wallet استفاده شود
- اگر از wallet استفاده می‌شود، باید بررسی شود که wallet payment در booking درست کار می‌کند

---

### [BUG-LOGIC-003] Package Usage Tracking Race Condition
**اولویت:** Medium  
**فایل:** `Modules/BeautyBooking/Services/BeautyBookingService.php:1059-1109`

**مشکل:**
- متد `trackPackageUsage()` در `updateBookingStatus()` فراخوانی می‌شود
- اگر دو booking همزمان به status 'completed' تغییر کنند، ممکن است session_number یکسان تخصیص داده شود

**تأثیر:**
- احتمال duplicate session_number
- احتمال استفاده بیش از حد از پکیج

**راه حل:**
- استفاده از database lock یا unique constraint
- یا استفاده از atomic increment

**تست:**
- تست concurrent booking completion
- تست package usage tracking با multiple bookings

---

### [BUG-LOGIC-004] Service Fee Calculation Base
**اولویت:** Medium  
**فایل:** `Modules/BeautyBooking/Services/BeautyBookingService.php:214-268`

**مشکل:**
```php
// Line 222: Service fee calculated on basePrice only
$serviceFee = $basePrice * ($serviceFeePercentage / 100);

// Line 256: Total includes additionalServicesAmount but service fee was calculated on basePrice only
$total = $basePrice + $additionalServicesAmount + $serviceFee + $taxAmount - $discount - $consultationCredit;
```

**تأثیر:**
- Service fee باید بر اساس `basePrice + additionalServicesAmount` محاسبه شود، نه فقط `basePrice`
- این باعث می‌شود که service fee کمتر از مقدار واقعی محاسبه شود

**راه حل:**
```php
// Calculate service fee on total base (basePrice + additionalServicesAmount)
$totalBaseForFee = $basePrice + $additionalServicesAmount;
$serviceFee = $totalBaseForFee * ($serviceFeePercentage / 100);
```

**تست:**
- تست booking با additional services
- بررسی صحت محاسبه service fee

---

### [BUG-LOGIC-005] Package Discount Logic Inconsistency
**اولویت:** Low  
**فایل:** `Modules/BeautyBooking/Services/BeautyBookingService.php:527-559`

**مشکل:**
- در `calculatePackageDiscount()`، فقط `discount_percentage` بررسی می‌شود
- اما در `isValidForUser()`، بررسی می‌شود که آیا کاربر جلسات باقیمانده دارد یا نه
- این دو بررسی هماهنگ نیستند

**تأثیر:**
- ممکن است discount برای پکیج‌های تمام شده یا منقضی شده اعمال شود

**راه حل:**
- اضافه کردن بررسی `isValidForUser()` در `calculatePackageDiscount()`
- یا اطمینان از اینکه validation در `createBooking()` قبل از محاسبه discount انجام می‌شود

**تست:**
- تست discount برای پکیج تمام شده
- تست discount برای پکیج منقضی شده

---

## فاز 2: بررسی منطق کسب‌وکار / Phase 2: Business Logic Review

### 2.1 Booking Flow Validation

#### بررسی مراحل رزرو:
- [ ] انتخاب خدمت: بررسی وجود service و active بودن
- [ ] انتخاب سالن: بررسی verification و active بودن
- [ ] بررسی دسترسی‌پذیری: بررسی working hours, holidays, existing bookings
- [ ] محاسبه مبلغ: بررسی صحت محاسبه base price, service fee, tax, discount, commission
- [ ] پرداخت: بررسی payment methods و wallet balance
- [ ] تأیید: بررسی vendor confirmation logic

#### موارد نیازمند بررسی:
1. **Availability Check Timing:**
   - آیا availability check قبل از payment انجام می‌شود؟
   - آیا race condition بین check و booking creation وجود دارد؟

2. **Payment Status Management:**
   - آیا payment status به درستی به‌روز می‌شود؟
   - آیا refund logic درست است؟

3. **Booking Status Transitions:**
   - آیا تمام transition های مجاز پیاده‌سازی شده‌اند؟
   - آیا validation برای هر transition وجود دارد؟

---

### 2.2 Revenue Models Validation

#### بررسی 10 مدل درآمدی:

1. **کمیسیون (Commission):**
   - [ ] محاسبه صحیح بر اساس category و salon level
   - [ ] اعمال تخفیف برای Top Rated
   - [ ] ثبت در BeautyTransaction
   - [ ] تسویه حساب با vendor

2. **اشتراک (Subscription):**
   - [ ] ثبت subscription payment
   - [ ] تمدید خودکار
   - [ ] انقضای subscription و revoke badge

3. **تبلیغات (Advertisement):**
   - [ ] ثبت درآمد از Featured Listing
   - [ ] ثبت درآمد از Boost Ads
   - [ ] ثبت درآمد از Banner Ads
   - [ ] زمان‌بندی و expiration

4. **Service Fee:**
   - [ ] محاسبه صحیح (1-3% از base price)
   - [ ] ثبت در BeautyTransaction
   - [ ] نمایش شفاف در invoice

5. **پکیج‌ها (Packages):**
   - [ ] محاسبه کمیسیون از کل مبلغ پکیج
   - [ ] tracking استفاده از جلسات
   - [ ] validation expiry و remaining sessions

6. **جریمه لغو (Cancellation Fee):**
   - [ ] محاسبه بر اساس زمان لغو (24h, 2h thresholds)
   - [ ] تقسیم بین platform و vendor
   - [ ] refund logic

7. **Featured Listing:**
   - [ ] ثبت payment
   - [ ] اعمال در ranking algorithm
   - [ ] expiration handling

8. **مشاوره (Consultation):**
   - [ ] محاسبه کمیسیون از consultation booking
   - [ ] اعمال credit به main service
   - [ ] validation برای prevent double application

9. **Cross-selling/Upsell:**
   - [ ] ثبت درآمد از additional services
   - [ ] محاسبه کمیسیون
   - [ ] validation برای prevent duplicate

10. **Retail Sales:**
    - [ ] ثبت درآمد از product sales
    - [ ] محاسبه کمیسیون
    - [ ] integration با order system

---

### 2.3 Ranking Algorithm Validation

#### بررسی فاکتورهای رتبه‌بندی:

1. **Location (25%):**
   - [ ] محاسبه فاصله با Haversine formula
   - [ ] normalization بر اساس thresholds
   - [ ] handling برای missing location data

2. **Featured/Boost (20%):**
   - [ ] بررسی active subscriptions
   - [ ] priority: Featured Listing > Boost Ads > Banner Ads > Top Rated > Verified
   - [ ] expiration handling

3. **Rating (18%):**
   - [ ] normalization از 0-5 به 0-1
   - [ ] weighted average calculation
   - [ ] minimum review count consideration

4. **Activity (10%):**
   - [ ] bookings در 30 روز گذشته
   - [ ] normalization بر اساس max_bookings
   - [ ] فقط confirmed/completed bookings

5. **Returning Rate (10%):**
   - [ ] محاسبه نرخ مشتریان برگشتی
   - [ ] normalization بر اساس expected rate
   - [ ] minimum bookings requirement

6. **Availability (5%):**
   - [ ] محاسبه available slots در 7 روز آینده
   - [ ] normalization بر اساس total possible slots
   - [ ] error handling

7. **Cancellation Rate (7%):**
   - [ ] محاسبه نرخ لغو
   - [ ] inverse scoring (lower is better)
   - [ ] thresholds: 0%, 2%, 5%, 10%, 20%+

8. **Service Type Match (5%):**
   - [ ] تطابق با فیلترهای کاربر
   - [ ] category matching
   - [ ] service type matching

#### موارد نیازمند بررسی:
- [ ] Cache invalidation در صورت تغییر salon data
- [ ] Performance optimization برای large datasets
- [ ] Configurable weights از admin panel

---

### 2.4 Badge System Validation

#### بررسی معیارهای Badge:

1. **Top Rated Badge:**
   - [ ] Rating >= 4.8
   - [ ] Minimum 50 bookings
   - [ ] Cancellation rate < 2%
   - [ ] Activity در 30 روز گذشته
   - [ ] Auto-assignment و auto-revocation

2. **Featured Badge:**
   - [ ] Active subscription check
   - [ ] Auto-revocation در صورت expiration
   - [ ] Manual assignment توسط admin

3. **Verified Badge:**
   - [ ] Manual assignment توسط admin
   - [ ] Document verification
   - [ ] No auto-revocation

#### موارد نیازمند بررسی:
- [ ] Observer برای auto-update badges
- [ ] Performance برای bulk badge calculation
- [ ] Cache invalidation

---

## فاز 3: بررسی یکپارچگی / Phase 3: Integration Review

### 3.1 Integration با سیستم موجود

#### Store Model:
- [ ] Relationship `beautySalon()` تعریف شده
- [ ] Scope `beautySalons()` تعریف شده
- [ ] Zone filtering صحیح است

#### User Model:
- [ ] Relationship `beautyBookings()` تعریف شده
- [ ] Wallet integration صحیح است

#### Payment Gateway:
- [ ] Hooks `beauty_booking_payment_success()` و `beauty_booking_payment_fail()` تعریف شده
- [ ] Integration با تمام payment methods
- [ ] Refund handling

#### Chat System:
- [ ] Conversation creation برای هر booking
- [ ] Message storage برای dispute resolution
- [ ] Integration با existing chat system

#### Notification System:
- [ ] Push notifications برای تمام events
- [ ] Email notifications
- [ ] SMS notifications (optional)

---

### 3.2 API Endpoints Validation

#### Customer API:
- [ ] Search salons با ranking
- [ ] Get salon details
- [ ] Check availability
- [ ] Create booking
- [ ] My bookings (filter by status)
- [ ] Cancel booking
- [ ] Submit review
- [ ] Redeem gift card
- [ ] Package status

#### Vendor API:
- [ ] My bookings (filter by status, date)
- [ ] Confirm booking
- [ ] Cancel booking
- [ ] Calendar availability
- [ ] Service management
- [ ] Staff management
- [ ] Financial reports

#### Admin API:
- [ ] Salon management
- [ ] Review moderation
- [ ] Commission settings
- [ ] Reports

---

## فاز 4: بررسی امنیت / Phase 4: Security Review

### 4.1 Authorization Checks

- [ ] Customer can only access their own bookings
- [ ] Vendor can only access their salon's bookings
- [ ] Admin can access all bookings
- [ ] Package validation: user owns the package
- [ ] Gift card validation: user can redeem their gift card

### 4.2 Input Validation

- [ ] All API inputs validated
- [ ] SQL injection prevention (parameter binding)
- [ ] XSS prevention
- [ ] File upload validation (documents, images)

### 4.3 Data Protection

- [ ] Sensitive data not exposed in API responses
- [ ] Payment information encrypted
- [ ] Personal information protected

---

## فاز 5: بررسی Performance / Phase 5: Performance Review

### 5.1 Database Optimization

- [ ] Indexes on frequently queried columns
- [ ] Eager loading to prevent N+1 queries
- [ ] Query optimization
- [ ] Pagination for large datasets

### 5.2 Caching

- [ ] Ranking scores cached
- [ ] Search results cached
- [ ] Cache invalidation strategy
- [ ] Cache TTL configuration

### 5.3 Background Jobs

- [ ] Monthly reports generation
- [ ] Booking reminders
- [ ] Subscription expiration
- [ ] Badge recalculation

---

## فاز 6: بررسی Edge Cases / Phase 6: Edge Cases Review

### 6.1 Booking Edge Cases

- [ ] Concurrent bookings برای same time slot
- [ ] Booking cancellation در حال پرداخت
- [ ] Payment failure بعد از booking creation
- [ ] Timezone handling
- [ ] Daylight saving time handling

### 6.2 Package Edge Cases

- [ ] Concurrent package usage tracking
- [ ] Package expiry در middle of usage
- [ ] Package purchase و immediate usage
- [ ] Multiple packages برای same service

### 6.3 Revenue Edge Cases

- [ ] Negative amounts
- [ ] Zero amounts
- [ ] Very large amounts
- [ ] Currency precision
- [ ] Rounding errors

---

## چک‌لیست نهایی / Final Checklist

### کد / Code
- [ ] تمام Syntax Errors بررسی شده
- [ ] تمام Return Type Hints اضافه شده
- [ ] تمام Relationships تعریف شده
- [ ] تمام Scopes کار می‌کنند
- [ ] تمام Services تست شده

### منطق کسب‌وکار / Business Logic
- [ ] Booking flow کامل و صحیح
- [ ] تمام 10 مدل درآمدی پیاده‌سازی شده
- [ ] Ranking algorithm صحیح
- [ ] Badge system خودکار
- [ ] Package usage tracking صحیح
- [ ] Consultation credit logic صحیح

### یکپارچگی / Integration
- [ ] Store Model Integration
- [ ] User Model Integration
- [ ] Wallet System Integration
- [ ] Payment Gateway Integration
- [ ] Chat System Integration
- [ ] Notification System Integration

### امنیت / Security
- [ ] Authorization checks
- [ ] Input validation
- [ ] Data protection

### Performance
- [ ] Database optimization
- [ ] Caching strategy
- [ ] Background jobs

---

## اولویت‌بندی رفع باگ‌ها / Bug Fix Priority

### Critical (فوری):
1. [BUG-LOGIC-001] Missing Exception in Package Validation

### High (بالا):
2. [BUG-LOGIC-002] Gift Card Not Applied in Booking Discount
3. [BUG-LOGIC-003] Package Usage Tracking Race Condition

### Medium (متوسط):
4. بررسی و بهبود edge cases
5. بهبود error handling
6. بهبود logging

### Low (پایین):
7. Code cleanup
8. Documentation improvements
9. Performance optimizations

---

## برنامه اجرایی / Execution Plan

### هفته 1: رفع باگ‌های Critical و High
- رفع BUG-LOGIC-001
- بررسی و رفع BUG-LOGIC-002
- رفع BUG-LOGIC-003
- تست‌های مربوطه

### هفته 2: بررسی منطق کسب‌وکار
- بررسی کامل booking flow
- بررسی تمام 10 مدل درآمدی
- بررسی ranking algorithm
- بررسی badge system

### هفته 3: بررسی یکپارچگی و امنیت
- بررسی integration points
- بررسی API endpoints
- بررسی security
- تست‌های integration

### هفته 4: بهینه‌سازی و بهبود
- Performance optimization
- Edge cases handling
- Code cleanup
- Documentation

---

**تاریخ تکمیل:** در حال بررسی  
**Completion Date:** Under Review

