# 📊 تحلیل جامع فانکشن‌های ماژول BeautyBooking

## 🎯 خلاصه اجرایی

این گزارش تحلیل کامل ارتباطات و هماهنگی بین تمام فانکشن‌ها، سرویس‌ها، کنترلرها و مدل‌های ماژول BeautyBooking را ارائه می‌دهد.

---

## 📁 ساختار ماژول

### 1. **Entities (مدل‌ها)**
- `BeautySalon` - سالن/کلینیک
- `BeautyBooking` - رزرو
- `BeautyService` - خدمت
- `BeautyStaff` - کارمند
- `BeautyPackage` - پکیج چندجلسه‌ای
- `BeautyPackageUsage` - استفاده از پکیج
- `BeautyReview` - نظرات
- `BeautyBadge` - نشان‌ها
- `BeautyTransaction` - تراکنش‌های مالی
- `BeautySubscription` - اشتراک‌ها
- `BeautyGiftCard` - کارت هدیه
- `BeautyLoyaltyPoint` - امتیاز وفاداری
- `BeautyLoyaltyCampaign` - کمپین وفاداری
- `BeautyRetailProduct` - محصولات خرده‌فروشی
- `BeautyRetailOrder` - سفارشات خرده‌فروشی
- `BeautyServiceCategory` - دسته‌بندی خدمات
- `BeautyServiceRelation` - روابط بین خدمات (cross-selling)
- `BeautyCommissionSetting` - تنظیمات کمیسیون
- `BeautyCalendarBlock` - بلوک‌های تقویم
- `BeautyMonthlyReport` - گزارشات ماهانه

### 2. **Services (سرویس‌ها)**
- `BeautyBookingService` - منطق اصلی رزرو
- `BeautyCalendarService` - مدیریت تقویم و دسترسی‌پذیری
- `BeautyCommissionService` - محاسبه کمیسیون
- `BeautyRevenueService` - مدیریت 10 مدل درآمدی
- `BeautyRankingService` - الگوریتم رتبه‌بندی سالن‌ها
- `BeautyBadgeService` - مدیریت نشان‌ها
- `BeautySalonService` - آمار و اطلاعات سالن
- `BeautyLoyaltyService` - مدیریت امتیاز وفاداری
- `BeautyRetailService` - مدیریت فروش خرده‌فروشی
- `BeautyCrossSellingService` - پیشنهادات فروش متقابل

### 3. **Controllers**
- **Admin**: مدیریت پنل ادمین
- **Vendor**: مدیریت پنل فروشنده
- **Customer (Web)**: رابط وب مشتری
- **Customer (API)**: API مشتری
- **Vendor (API)**: API فروشنده

### 4. **Traits**
- `BeautyPushNotification` - ارسال نوتیفیکیشن
- `BeautyBookingLogic` - منطق مشترک رزرو
- `BeautyApiResponse` - فرمت پاسخ API
- `OpenTelemetryInstrumentation` - ردیابی و observability

---

## 🔗 نمودار وابستگی‌ها (Dependency Graph)

### **BeautyBookingService** (سرویس اصلی)
```
BeautyBookingService
├── BeautyCalendarService (بررسی دسترسی‌پذیری، بلاک کردن زمان)
├── BeautyCommissionService (محاسبه کمیسیون)
├── BeautyRevenueService (ثبت درآمد)
├── BeautyPackage (مدل)
├── BeautyPackageUsage (مدل)
└── Traits:
    ├── Payment (پرداخت)
    ├── BeautyPushNotification (نوتیفیکیشن)
    └── OpenTelemetryInstrumentation (ردیابی)
```

### **BeautyRevenueService**
```
BeautyRevenueService
├── BeautyRankingService (باطل کردن cache رتبه‌بندی)
└── BeautyTransaction (مدل)
```

### **BeautyRankingService**
```
BeautyRankingService
├── BeautyCalendarService (بررسی دسترسی‌پذیری برای امتیاز)
└── BeautySalon, BeautyBooking, BeautyService (مدل‌ها)
```

### **BeautyCrossSellingService**
```
BeautyCrossSellingService
├── BeautyRevenueService (ثبت درآمد cross-selling)
└── BeautyService, BeautyServiceRelation (مدل‌ها)
```

### **BeautyRetailService**
```
BeautyRetailService
├── BeautyRevenueService (ثبت درآمد فروش خرده‌فروشی)
└── BeautyRetailProduct, BeautyRetailOrder (مدل‌ها)
```

### **BeautyLoyaltyService**
```
BeautyLoyaltyService
├── BeautyRevenueService (محاسبه کمیسیون کمپین)
└── BeautyLoyaltyPoint, BeautyLoyaltyCampaign (مدل‌ها)
```

---

## 🔄 جریان اصلی رزرو (Booking Flow)

### 1. **ایجاد رزرو** (`BeautyBookingService::createBooking`)
```
1. اعتبارسنجی سالن (verified, active)
2. اعتبارسنجی خدمت (active, duration)
3. اعتبارسنجی کارمند (optional)
4. بررسی دسترسی‌پذیری (BeautyCalendarService::isTimeSlotAvailable)
   └── بررسی ساعات کاری
   └── بررسی تعطیلات
   └── بررسی بلوک‌های تقویم
   └── بررسی رزروهای موجود (با lockForUpdate برای جلوگیری از race condition)
5. محاسبه مبالغ:
   ├── قیمت پایه خدمت
   ├── خدمات اضافی (cross-selling)
   ├── هزینه مشاوره (consultation)
   ├── اعتبار مشاوره (consultation credit)
   ├── تخفیف کوپن
   ├── تخفیف پکیج
   ├── هزینه سرویس (service fee: 1-3%)
   ├── مالیات
   └── کمیسیون (BeautyCommissionService::calculateCommission)
6. ایجاد رزرو در دیتابیس
7. بلاک کردن زمان (BeautyCalendarService::blockTimeSlot)
8. ایجاد مکالمه چت (createBookingConversation)
9. به‌روزرسانی آمار سالن (BeautySalonService::updateBookingStatistics)
10. ارسال نوتیفیکیشن (BeautyPushNotification::sendBookingNotificationToAll)
```

### 2. **پرداخت** (`BeautyBookingService::processPayment`)
```
1. بررسی روش پرداخت:
   ├── wallet → processWalletPayment
   ├── digital_payment → processDigitalPayment
   └── cash_payment → processCashPayment
2. به‌روزرسانی وضعیت پرداخت
3. ثبت درآمد (BeautyRevenueService):
   ├── recordCommission
   ├── recordServiceFee
   ├── recordPackageSale (اگر از پکیج استفاده شده)
   ├── recordConsultationFee (اگر مشاوره است)
   └── recordCrossSellingRevenue (اگر خدمات اضافی دارد)
```

### 3. **تأیید رزرو** (`BeautyBookingService::updateBookingStatus`)
```
1. به‌روزرسانی وضعیت رزرو
2. اگر confirmed و paid:
   └── ثبت درآمد (مشابه processPayment)
3. اگر completed:
   ├── trackPackageUsage (ردیابی استفاده از پکیج)
   ├── awardLoyaltyPoints (امتیاز وفاداری)
   ├── unblockTimeSlot (آزاد کردن زمان)
   └── updateSalonStatistics (به‌روزرسانی آمار)
```

### 4. **لغو رزرو** (`BeautyBookingService::cancelBooking`)
```
1. محاسبه هزینه لغو (calculateCancellationFee)
2. به‌روزرسانی وضعیت رزرو
3. به‌روزرسانی آمار سالن
4. ثبت درآمد لغو (BeautyRevenueService::recordCancellationFee)
5. آزاد کردن زمان (BeautyCalendarService::unblockTimeSlotForBooking)
6. پرداخت بازگشت (processRefund)
7. ارسال نوتیفیکیشن
```

---

## 🛡️ مکانیزم‌های امنیتی و یکپارچگی داده

### 1. **Database Transactions**
تمام عملیات مالی و تغییرات مهم در `DB::transaction` قرار دارند:
- `createBooking` - ایجاد رزرو
- `processPayment` - پرداخت
- `updatePaymentStatus` - به‌روزرسانی وضعیت پرداخت
- `updateBookingStatus` - به‌روزرسانی وضعیت رزرو
- `cancelBooking` - لغو رزرو
- `markConsultationCreditApplied` - اعمال اعتبار مشاوره
- `trackPackageUsage` - ردیابی استفاده از پکیج
- `awardPointsForBooking` - اعطای امتیاز وفاداری
- `createOrder` (Retail) - ایجاد سفارش خرده‌فروشی

### 2. **Row-Level Locking (lockForUpdate)**
برای جلوگیری از race conditions:
- بررسی دسترسی‌پذیری زمان (`isTimeSlotAvailable`)
- بررسی موجودی محصول (`validateProductsWithLock`)
- بررسی اعتبار مشاوره (`markConsultationCreditApplied`)
- بررسی استفاده از پکیج (`trackPackageUsage`)
- بررسی امتیاز وفاداری (`awardPointsForBooking`)
- بررسی ثبت درآمد تکراری (`updatePaymentStatus`, `updateBookingStatus`)

### 3. **Duplicate Prevention**
- بررسی وجود تراکنش قبل از ثبت (`lockForUpdate` + `exists()`)
- بررسی وجود استفاده از پکیج قبل از ثبت
- بررسی وجود امتیاز وفاداری قبل از اعطا

---

## 📊 مدل‌های درآمدی (10 Revenue Models)

### 1. **Variable Commission** (کمیسیون متغیر)
- `BeautyCommissionService::calculateCommission`
- `BeautyRevenueService::recordCommission`
- بر اساس: دسته خدمت، سطح سالن، نشان‌ها

### 2. **Monthly/Annual Subscription** (اشتراک ماهانه/سالانه)
- `BeautyRevenueService::recordSubscription`
- برای: Featured Listing, Boost Ads, Banner Ads

### 3. **Advertising** (تبلیغات)
- `BeautyRevenueService::recordAdvertisement`
- انواع: Featured Listing, Boost Ads (7/30 روز), Homepage Banner, Banner Ads

### 4. **Service Fee** (هزینه سرویس)
- `BeautyRevenueService::recordServiceFee`
- از مشتری: 1-3% از مبلغ رزرو

### 5. **Multi-Session Packages** (پکیج‌های چندجلسه‌ای)
- `BeautyRevenueService::recordPackageSale`
- `BeautyBookingService::trackPackageUsage`
- تخفیف برای خرید چندجلسه

### 6. **Late Cancellation Fee** (هزینه لغو دیرهنگام)
- `BeautyBookingService::calculateCancellationFee`
- `BeautyRevenueService::recordCancellationFee`
- بر اساس زمان لغو: 0%, 50%, 100%

### 7. **Consultation Service** (خدمت مشاوره)
- `BeautyRevenueService::recordConsultationFee`
- `BeautyBookingService::calculateConsultationCredit`
- `BeautyBookingService::markConsultationCreditApplied`
- پیش/پس از خدمت

### 8. **Cross-Selling/Upsell** (فروش متقابل)
- `BeautyCrossSellingService::getSuggestedServices`
- `BeautyRevenueService::recordCrossSellingRevenue`
- پیشنهاد خدمات اضافی

### 9. **Retail Sales** (فروش خرده‌فروشی)
- `BeautyRetailService::createOrder`
- `BeautyRevenueService::recordRetailSale`
- فروش محصولات زیبایی

### 10. **Gift Cards & Loyalty Campaigns** (کارت هدیه و کمپین وفاداری)
- `BeautyRevenueService::recordGiftCardSale`
- `BeautyRevenueService::recordLoyaltyCampaignRevenue`
- `BeautyLoyaltyService::awardPointsForPurchase`
- `BeautyLoyaltyService::awardPointsForBooking`

---

## 🏆 سیستم نشان‌ها (Badge System)

### **BeautyBadgeService::calculateAndAssignBadges**
```
1. بررسی "Top Rated":
   ├── avg_rating >= 4.8 (قابل تنظیم از config: beautybooking.badge.top_rated.min_rating)
   ├── total_bookings >= 50 (قابل تنظیم از config: beautybooking.badge.top_rated.min_bookings)
   ├── cancellation_rate < 2.0% (قابل تنظیم از config: beautybooking.badge.top_rated.max_cancellation_rate)
   ├── فعالیت در 30 روز گذشته (قابل تنظیم از config: beautybooking.badge.top_rated.activity_days)
   └── assignBadgeIfNotExists('top_rated')

2. بررسی "Featured":
   ├── active subscription exists
   └── assignBadgeIfNotExists('featured')

3. بررسی "Verified":
   └── Manual admin approval (is_verified flag)
```

### **Cache Management**
- `getActiveBadges` - با cache
- `clearBadgeCache` - باطل کردن cache
- Cache TTL: 30 دقیقه

---

## 🔍 الگوریتم رتبه‌بندی (Ranking Algorithm)

### **BeautyRankingService::calculateRankingScore**
```
امتیاز کل = مجموع (امتیاز × وزن) / 100

1. Location (25%):
   └── calculateLocationScore (Haversine formula)
   └── نزدیک‌تر = امتیاز بالاتر

2. Featured/Boost (20%):
   └── calculateFeaturedScore
   └── Featured Listing > Boost Ads > Banner Ads > Top Rated > Verified

3. Rating (18%):
   └── calculateRatingScore (Bayesian average)
   └── نرمال‌سازی 0-5 به 0-1

4. Activity (10%):
   └── calculateActivityScore
   └── رزروهای 30 روز گذشته

5. Returning Rate (10%):
   └── calculateReturningRateScore
   └── نرخ مشتری برگشتی

6. Availability (5%):
   └── calculateAvailabilityScore
   └── تعداد زمان‌های خالی (7 روز آینده)

7. Cancellation Rate (7%):
   └── calculateCancellationRateScore
   └── نرخ لغو پایین‌تر = امتیاز بالاتر

8. Service Type Match (5%):
   └── calculateServiceTypeScore
   └── تطابق با فیلترهای جستجو
```

### **Cache Strategy**
- Ranking Score: TTL 30 دقیقه
- Search Results: TTL 5 دقیقه
- Invalidation: هنگام تغییر subscription، badge، rating

---

## 🔔 سیستم نوتیفیکیشن

### **BeautyPushNotification Trait**
```
sendBookingNotificationToAll()
├── sendBookingNotificationAdminPanel()
├── sendBookingNotificationSalonPanel()
├── sendBookingNotificationSalonApp()
└── sendBookingNotificationCustomer()
```

### **Events**
- `created` - رزرو ایجاد شد
- `confirmed` - رزرو تأیید شد
- `cancelled` - رزرو لغو شد
- `completed` - رزرو تکمیل شد
- `payment_received` - پرداخت دریافت شد
- `reminder` - یادآوری رزرو

---

## 🐛 مشکلات شناسایی شده و اصلاح شده

### 1. **مشکل: متد `rankSalonsByLocation` وجود نداشت**
- **موقعیت**: `BeautySalonController::search`
- **اصلاح**: استفاده از `getRankedSalons` به جای `rankSalonsByLocation`
- **وضعیت**: ✅ اصلاح شد

### 2. **Race Conditions (اصلاح شده در گذشته)**
- ✅ Revenue Recording Duplication
- ✅ Consultation Credit Double Application
- ✅ Retail Stock Race Condition
- ✅ Loyalty Points Duplicate Award

### 3. **Transaction Atomicity (اصلاح شده در گذشته)**
- ✅ Payment Status Update
- ✅ Booking Status Update
- ✅ Revenue Recording

### 4. **Pagination Bug (اصلاح شده در گذشته)**
- ✅ Offset to Page Calculation

---

## ✅ هماهنگی و Consistency

### **1. Naming Conventions**
- ✅ تمام مدل‌ها: `Beauty{Entity}`
- ✅ تمام سرویس‌ها: `Beauty{Feature}Service`
- ✅ تمام کنترلرها: `Beauty{Entity}Controller`
- ✅ تمام جدول‌ها: `beauty_{entity}`

### **2. Error Handling**
- ✅ استفاده از `translate()` برای تمام پیام‌ها
- ✅ استفاده از `Helpers::error_processor()` برای validation errors
- ✅ Try-catch برای عملیات خارجی

### **3. Response Format**
- ✅ API: `['message' => translate(...), 'data' => ...]`
- ✅ Error: `['errors' => Helpers::error_processor($validator)]`
- ✅ List: شامل `total`, `per_page`, `current_page`

### **4. Database Patterns**
- ✅ استفاده از `foreignId()` برای foreign keys
- ✅ استفاده از `DB::transaction` برای atomicity
- ✅ استفاده از `lockForUpdate` برای race conditions
- ✅ Indexes برای ستون‌های پرکاربرد

### **5. Service Patterns**
- ✅ Dependency Injection در constructor
- ✅ Type hints و return types
- ✅ PHPDoc با توضیحات دوزبانه
- ✅ Business logic در services، نه controllers

---

## 📈 Performance Optimizations

### **1. Caching**
- Ranking Scores: 30 دقیقه
- Search Results: 5 دقیقه
- Active Badges: 30 دقیقه

### **2. Eager Loading**
- استفاده از `with()` برای جلوگیری از N+1 queries
- مثال: `BeautyBooking::with(['salon', 'service', 'staff', 'user'])`

### **3. Database Indexes**
- Foreign keys
- Status columns
- Date columns
- Composite indexes برای query patterns رایج

---

## 🔐 Security & Authorization

### **1. Authentication**
- Customer APIs: `auth:api` (sanctum)
- Vendor APIs: `auth:api` (vendor guard)

### **2. Authorization**
- بررسی ownership در controllers
- `authorizeBookingOwnership` در Customer API
- `authorizeBookingAccess` در Vendor API

### **3. Validation**
- تمام inputs با `Validator::make()`
- File upload validation (type, size)
- Business rule validation (availability, stock, etc.)

---

## 📝 نتیجه‌گیری

### **نقاط قوت:**
1. ✅ ساختار منظم و modular
2. ✅ جداسازی concerns (Services, Controllers, Models)
3. ✅ استفاده صحیح از transactions و locking
4. ✅ مدیریت cache برای performance
5. ✅ Error handling جامع
6. ✅ Documentation کامل (دوزبانه)

### **پیشنهادات بهبود:**
1. 🔄 اضافه کردن Unit Tests برای تمام services
2. 🔄 اضافه کردن Integration Tests برای flows اصلی
3. 🔄 Monitoring و alerting برای errors
4. 🔄 Performance profiling برای queries سنگین
5. 🔄 Documentation برای API endpoints

### **وضعیت کلی:**
✅ **ماژول به خوبی ساختار یافته و هماهنگ است. تمام فانکشن‌ها با هم ارتباط دارند و consistency خوبی در کد وجود دارد.**

---

**تاریخ تحلیل**: 2025-01-28  
**نسخه ماژول**: 1.0  
**وضعیت**: ✅ Production Ready

