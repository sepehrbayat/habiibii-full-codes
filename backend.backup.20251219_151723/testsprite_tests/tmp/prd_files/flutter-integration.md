# Flutter App Integration Guide
# راهنمای یکپارچه‌سازی اپلیکیشن فلاتر

## Overview
# بررسی کلی

This document outlines the Flutter app integration requirements for the Beauty Booking module.
این سند الزامات یکپارچه‌سازی اپلیکیشن فلاتر برای ماژول رزرو زیبایی را شرح می‌دهد.

## API Client Layer
# لایه کلاینت API

### Required API Service Classes
# کلاس‌های سرویس API مورد نیاز

Create the following API service classes in your Flutter app:
کلاس‌های سرویس API زیر را در اپلیکیشن فلاتر خود ایجاد کنید:

1. **lib/services/beauty_booking_api.dart**
   - Main API client with base URL configuration
   - کلاینت API اصلی با پیکربندی URL پایه
   - Handles authentication headers
   - مدیریت هدرهای احراز هویت
   - Error handling and response parsing
   - مدیریت خطا و تجزیه پاسخ

2. **lib/services/beauty_salon_api.dart**
   - Salon search and details
   - جستجو و جزئیات سالن
   - Endpoints: `/api/v1/beautybooking/salons/search`, `/api/v1/beautybooking/salons/{id}`
   - Top-rated, trending, monthly reports
   - برترین‌ها، ترندها، گزارش‌های ماهانه

3. **lib/services/beauty_booking_service.dart**
   - Booking creation, listing, cancellation
   - ایجاد، لیست، لغو رزرو
   - Availability checking
   - بررسی دسترسی‌پذیری
   - Payment processing
   - پردازش پرداخت

4. **lib/services/beauty_payment_service.dart**
   - Payment gateway integration
   - یکپارچه‌سازی درگاه پرداخت
   - Wallet payments
   - پرداخت از کیف پول
   - Payment status tracking
   - ردیابی وضعیت پرداخت

5. **lib/services/beauty_loyalty_service.dart**
   - Points balance, campaigns, redemption
   - موجودی امتیازها، کمپین‌ها، استفاده
   - Endpoints: `/api/v1/beautybooking/loyalty/*`

6. **lib/services/beauty_gift_card_service.dart**
   - Gift card purchase, redeem, list
   - خرید، استفاده، لیست کارت هدیه
   - Endpoints: `/api/v1/beautybooking/gift-card/*`

7. **lib/services/beauty_retail_service.dart**
   - Product browsing, cart, orders
   - مرور محصولات، سبد خرید، سفارشات
   - Endpoints: `/api/v1/beautybooking/retail/*`

8. **lib/services/beauty_package_service.dart**
   - Package listing, purchase, status
   - لیست، خرید، وضعیت پکیج
   - Endpoints: `/api/v1/beautybooking/packages/*`

## State Management
# مدیریت وضعیت

### Recommended: BLoC Pattern
# توصیه شده: الگوی BLoC

Implement BLoC for each feature:
پیاده‌سازی BLoC برای هر ویژگی:

1. **lib/blocs/beauty_booking/**
   - `beauty_booking_bloc.dart` - Booking state management
   - `beauty_booking_event.dart` - Booking events
   - `beauty_booking_state.dart` - Booking states

2. **lib/blocs/beauty_salon/**
   - Salon search and selection
   - جستجو و انتخاب سالن
   - Ranking and filtering
   - رتبه‌بندی و فیلتر

3. **lib/blocs/beauty_loyalty/**
   - Points balance and campaigns
   - موجودی امتیازها و کمپین‌ها

4. **lib/blocs/beauty_retail/**
   - Shopping cart state
   - وضعیت سبد خرید

### Offline Support
# پشتیبانی آفلاین

- Cache salon/search results locally using `shared_preferences` or `hive`
- ذخیره نتایج سالن/جستجو به صورت محلی با استفاده از `shared_preferences` یا `hive`
- Save booking drafts before submission
- ذخیره پیش‌نویس رزرو قبل از ارسال
- Queue API calls when offline, sync when connection restored
- صف‌بندی درخواست‌های API هنگام آفلاین، همگام‌سازی هنگام بازگشت اتصال

## UI Components
# کامپوننت‌های UI

### Customer App
# اپلیکیشن مشتری

#### Booking Wizard (Multi-step)
# ویزارد رزرو (چند مرحله‌ای)

1. **Service Selection Screen**
   - List services by category
   - لیست خدمات بر اساس دسته‌بندی
   - Service details with price and duration
   - جزئیات خدمت با قیمت و مدت زمان

2. **Staff Selection Screen (Optional)**
   - List available staff
   - لیست کارمندان موجود
   - Staff profiles and specializations
   - پروفایل‌ها و تخصص‌های کارمندان

3. **Date/Time Selection Screen**
   - Calendar picker
   - انتخابگر تقویم
   - Available time slots grid
   - شبکه زمان‌های خالی
   - Integration with `BeautyCalendarService` API
   - یکپارچه‌سازی با API `BeautyCalendarService`

4. **Payment Method Selection**
   - Online payment, wallet, cash on arrival
   - پرداخت آنلاین، کیف پول، نقدی هنگام ورود
   - Price breakdown display
   - نمایش تفکیک قیمت

5. **Review & Confirm Screen**
   - Booking summary
   - خلاصه رزرو
   - Terms and conditions
   - شرایط و ضوابط
   - Confirm button
   - دکمه تأیید

#### Booking Management
# مدیریت رزرو

- Upcoming bookings list
- لیست رزروهای آینده
- Past bookings list
- لیست رزروهای گذشته
- Booking detail with timeline
- جزئیات رزرو با خط زمانی
- Cancel/Reschedule actions
- اقدامات لغو/تغییر زمان

#### Other Screens
# سایر صفحات

- Salon search with map/list views
- جستجوی سالن با نمایش نقشه/لیست
- Salon detail page
- صفحه جزئیات سالن
- Review submission
- ارسال نظر
- Gift card purchase/redeem
- خرید/استفاده کارت هدیه
- Loyalty points dashboard
- داشبورد امتیازهای وفاداری
- Retail shopping (if enabled)
- خرید خرده‌فروشی (در صورت فعال بودن)

### Vendor App
# اپلیکیشن فروشنده

- Dashboard with KPIs
- داشبورد با شاخص‌های کلیدی
- Calendar screen with drag-and-drop
- صفحه تقویم با drag-and-drop
- Booking management
- مدیریت رزرو
- Service/staff management
- مدیریت خدمت/کارمند
- Financial reports
- گزارش‌های مالی
- Badge status display
- نمایش وضعیت نشان
- Subscription purchase
- خرید اشتراک

### Admin App
# اپلیکیشن ادمین

- Dashboard with KPIs
- داشبورد با شاخص‌های کلیدی
- Salon management
- مدیریت سالن
- Booking management with calendar
- مدیریت رزرو با تقویم
- Review moderation
- نظارت بر نظرات
- Commission settings
- تنظیمات کمیسیون
- Reports with charts
- گزارش‌ها با نمودارها

## Navigation
# ناوبری

### Deep Linking
# لینک عمیق

Implement deep links for:
پیاده‌سازی لینک‌های عمیق برای:

- Salon detail: `beautybooking://salon/{id}`
- Booking confirmation: `beautybooking://booking/{id}`
- Gift card redemption: `beautybooking://gift-card/{code}`

### Bottom Tab Navigation
# ناوبری تب پایین

For customer app:
برای اپلیکیشن مشتری:

- Home (Search)
- Bookings
- Wallet
- Profile

## Payment Integration
# یکپارچه‌سازی پرداخت

- Integrate with existing payment gateway modules
- یکپارچه‌سازی با ماژول‌های درگاه پرداخت موجود
- Support wallet payments via API
- پشتیبانی از پرداخت کیف پول از طریق API
- Handle payment callbacks
- مدیریت callbackهای پرداخت
- Show payment status in booking details
- نمایش وضعیت پرداخت در جزئیات رزرو

## Localization
# بومی‌سازی

- Add Beauty Booking translation strings
- افزودن رشته‌های ترجمه رزرو زیبایی
- Support Persian and English
- پشتیبانی از فارسی و انگلیسی
- Use existing translation infrastructure
- استفاده از زیرساخت ترجمه موجود

## Push Notifications
# نوتیفیکیشن‌های پوش

### Notification Types
# انواع نوتیفیکیشن

Configure Firebase for:
پیکربندی Firebase برای:

- Booking confirmations
- تأیید رزرو
- Booking reminders (24h before)
- یادآوری رزرو (24 ساعت قبل)
- Booking cancellations
- لغو رزرو
- Review requests
- درخواست نظر
- Subscription expiry warnings
- هشدار انقضای اشتراک
- Badge awarded
- اعطای نشان

### Navigation on Tap
# ناوبری با لمس

Navigate to appropriate screen when notification is tapped:
انتقال به صفحه مناسب هنگام لمس نوتیفیکیشن:

- Booking notification → Booking detail screen
- Review request → Review submission screen
- Badge awarded → Badge status screen

## Testing
# تست

### Widget Tests
# تست ویجت

Test key widgets:
تست ویجت‌های کلیدی:

- Booking wizard steps
- مراحل ویزارد رزرو
- Calendar picker
- انتخابگر تقویم
- Payment forms
- فرم‌های پرداخت
- Review submission
- ارسال نظر

### Integration Tests
# تست یکپارچه

Test end-to-end flows:
تست جریان‌های end-to-end:

- Complete booking flow
- جریان کامل رزرو
- Payment processing
- پردازش پرداخت
- Offline mode
- حالت آفلاین

## Implementation Checklist
# چک‌لیست پیاده‌سازی

- [ ] Create API service classes
- [ ] Implement BLoC state management
- [ ] Build booking wizard UI
- [ ] Implement calendar picker
- [ ] Add payment integration
- [ ] Configure push notifications
- [ ] Add deep linking
- [ ] Implement offline support
- [ ] Add localization strings
- [ ] Write widget tests
- [ ] Write integration tests

