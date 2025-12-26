# تغییرات کامل لازم در Laravel - ماژول زیبایی (برای Cursor AI)

**مسیر پروژه:** `/home/sepehr/Projects/6ammart-laravel/`

## 📋 خلاصه اجرایی

این سند شامل تمام تغییرات لازم در پروژه Laravel برای هماهنگی کامل با React frontend است. تمام تغییرات باید در مسیر `Modules/BeautyBooking/` انجام شود. این سند به طور کامل و با جزئیات تمام ناهماهنگی‌ها، مشکلات و فیچرهای ناقص را پوشش می‌دهد.

---

## 🔍 روش بررسی

قبل از اعمال هر تغییر:
1. فایل‌های React مربوطه را در `/home/sepehr/Projects/6ammart-react/src/api-manage/` بررسی کنید
2. API endpoints و expected request/response format را در React چک کنید
3. تغییرات را در Laravel اعمال کنید
4. تست کنید که request/response format با React هماهنگ است

---

## 1. مشکلات Pagination Parameters

### مشکل کلی:
React از `offset` و `limit` استفاده می‌کند، اما Laravel باید از `per_page` و `page` استفاده کند. Laravel باید هر دو را پشتیبانی کند.

### وضعیت فعلی:
- Laravel در اکثر کنترلرها از `offset` و `limit` پشتیبانی می‌کند ✅
- اما باید مطمئن شویم که همه کنترلرها این پشتیبانی را دارند

### تغییرات لازم:

#### 1.1. کنترلر Booking (`BeautyBookingController.php`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`

**وضعیت فعلی:** ✅ پشتیبانی از offset و limit موجود است (خط 265-272)

**نیاز به بررسی:**
- مطمئن شوید که محاسبه `page` از `offset` به درستی انجام می‌شود
- تست کنید که با `offset=0, limit=25` صفحه اول برگردانده می‌شود

#### 1.2. کنترلر Package (`BeautyPackageController.php`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`

**وضعیت فعلی:** ✅ پشتیبانی از offset و limit موجود است (خط 45-50)

**نیاز به بررسی:**
- مطمئن شوید که response format شامل `per_page`, `current_page`, `last_page` است

#### 1.3. کنترلر Loyalty (`BeautyLoyaltyController.php`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php`

**وضعیت فعلی:** ✅ پشتیبانی از offset و limit موجود است (خط 84-89)

**نیاز به بررسی:**
- مطمئن شوید که response format با React هماهنگ است

#### 1.4. کنترلر Retail (`BeautyRetailController.php`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`

**وضعیت فعلی:** ✅ پشتیبانی از offset و limit موجود است (خط 73-78)

**نیاز به بررسی:**
- مطمئن شوید که response format شامل تمام فیلدهای pagination است

#### 1.5. کنترلر Consultation (`BeautyConsultationController.php`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`

**وضعیت فعلی:** ✅ پشتیبانی از offset و limit موجود است (خط 70-75)

**نیاز به بررسی:**
- مطمئن شوید که response format با React هماهنگ است

#### 1.6. کنترلر Review (`BeautyReviewController.php`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`

**نیاز به بررسی:**
- مطمئن شوید که متد `index()` از offset و limit پشتیبانی می‌کند
- بررسی کنید که response format شامل pagination metadata است

#### 1.7. کنترلر Gift Card (`BeautyGiftCardController.php`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php`

**نیاز به بررسی:**
- مطمئن شوید که متد `index()` از offset و limit پشتیبانی می‌کند
- بررسی کنید که response format شامل pagination metadata است

---

## 2. مشکلات Response Format

### مشکل کلی:
React انتظار دارد که response format همیشه شامل `data`, `message`, و pagination metadata باشد.

### تغییرات لازم:

#### 2.1. استفاده از `BeautyApiResponse` Trait
**فایل:** `Modules/BeautyBooking/Traits/BeautyApiResponse.php`

**وضعیت فعلی:** ✅ Trait موجود است و متدهای زیر را دارد:
- `successResponse()` - برای پاسخ‌های موفق
- `listResponse()` - برای لیست‌های paginated
- `simpleListResponse()` - برای لیست‌های غیر-paginated
- `errorResponse()` - برای خطاها
- `validationErrorResponse()` - برای خطاهای validation

**نیاز به بررسی:**
- مطمئن شوید که همه کنترلرها از این trait استفاده می‌کنند
- بررسی کنید که همه متدها response format یکسانی برمی‌گردانند

#### 2.2. Response Format برای List Endpoints
**مشکل:** React انتظار دارد که لیست‌ها همیشه شامل این فیلدها باشند:
```json
{
  "message": "...",
  "data": [...],
  "total": 100,
  "per_page": 25,
  "current_page": 1,
  "last_page": 4
}
```

**تغییرات لازم:**
- مطمئن شوید که همه متدهای `index()` و `list()` از `listResponse()` استفاده می‌کنند
- بررسی کنید که `paginate()` به درستی استفاده می‌شود

---

## 3. مشکلات Payment Method

### مشکل کلی:
React از `online` استفاده می‌کند، اما Laravel باید `digital_payment` را بپذیرد. Laravel باید هر دو را پشتیبانی کند.

### وضعیت فعلی:
- Laravel در اکثر کنترلرها تبدیل `online` به `digital_payment` را انجام می‌دهد ✅

### تغییرات لازم:

#### 3.1. کنترلر Booking (`BeautyBookingController.php`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`

**وضعیت فعلی:** ✅ تبدیل `online` به `digital_payment` موجود است (خط 200-204)

**نیاز به بررسی:**
- مطمئن شوید که validation rule شامل `online` نیست (فقط `digital_payment` باشد)
- بررسی کنید که تبدیل قبل از validation انجام می‌شود

#### 3.2. کنترلر Package (`BeautyPackageController.php`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`

**وضعیت فعلی:** ✅ تبدیل `online` به `digital_payment` موجود است (خط 152-156)

**نیاز به بررسی:**
- مطمئن شوید که validation rule شامل `online` نیست

#### 3.3. کنترلر Gift Card (`BeautyGiftCardController.php`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php`

**وضعیت فعلی:** ✅ تبدیل `online` به `digital_payment` موجود است (خط 179-183)

**نیاز به بررسی:**
- مطمئن شوید که validation rule شامل `online` نیست

#### 3.4. کنترلر Consultation (`BeautyConsultationController.php`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`

**وضعیت فعلی:** ✅ تبدیل `online` به `digital_payment` موجود است (خط 150-154)

**نیاز به بررسی:**
- مطمئن شوید که validation rule شامل `online` نیست

#### 3.5. کنترلر Retail (`BeautyRetailController.php`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`

**وضعیت فعلی:** ✅ تبدیل `online` به `digital_payment` موجود است (خط 150-154)

**نیاز به بررسی:**
- مطمئن شوید که validation rule شامل `online` نیست

#### 3.6. کنترلر Payment (`BeautyBookingController.php` - متد `payment()`)
**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`

**وضعیت فعلی:** ✅ تبدیل `online` به `digital_payment` موجود است (خط 420-422)

**نیاز به بررسی:**
- مطمئن شوید که validation rule شامل `online` نیست

---

## 4. مشکلات Request Parameters

### 4.1. Parameter Naming
**مشکل:** React ممکن است از نام‌های مختلفی برای پارامترها استفاده کند.

### تغییرات لازم:

#### 4.1.1. Salon Search Parameters
**Endpoint:** `GET /api/v1/beautybooking/salons/search`

**پارامترهای React:**
- `search` ✅
- `latitude` ✅
- `longitude` ✅
- `category_id` ✅
- `business_type` ✅
- `min_rating` ✅
- `radius` ✅

**وضعیت:** ✅ همه پارامترها پشتیبانی می‌شوند

#### 4.1.2. Booking List Parameters
**Endpoint:** `GET /api/v1/beautybooking/bookings`

**پارامترهای React:**
- `limit` ✅
- `offset` ✅
- `status` ✅
- `type` ✅ (upcoming/past/cancelled)
- `date_range` ❌ (در React استفاده می‌شود اما در Laravel `date_from` و `date_to` است)
- `service_type` ❌ (در React استفاده می‌شود اما در Laravel `service_id` است)
- `staff_id` ✅

**تغییرات لازم:**
- اضافه کردن پشتیبانی از `date_range` (می‌تواند به `date_from` و `date_to` تبدیل شود)
- اضافه کردن پشتیبانی از `service_type` (اگر منظور `service_id` است)

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
**متد:** `index()` (خط 263)

```php
// اضافه کردن پشتیبانی از date_range
if ($request->filled('date_range')) {
    // Parse date_range (format: "2024-01-01,2024-01-31")
    $dates = explode(',', $request->date_range);
    if (count($dates) === 2) {
        $query->whereDate('booking_date', '>=', $dates[0])
              ->whereDate('booking_date', '<=', $dates[1]);
    }
}

// اضافه کردن پشتیبانی از service_type (اگر منظور service_id است)
if ($request->filled('service_type')) {
    $query->where('service_id', $request->service_type);
}
```

#### 4.1.3. Package List Parameters
**Endpoint:** `GET /api/v1/beautybooking/packages`

**پارامترهای React:**
- `salon_id` ✅
- `service_id` ✅
- `per_page` ✅ (همچنین `limit` پشتیبانی می‌شود)
- `limit` ✅
- `offset` ✅

**وضعیت:** ✅ همه پارامترها پشتیبانی می‌شوند

#### 4.1.4. Loyalty Campaigns Parameters
**Endpoint:** `GET /api/v1/beautybooking/loyalty/campaigns`

**پارامترهای React:**
- `salon_id` ✅
- `per_page` ✅ (همچنین `limit` پشتیبانی می‌شود)
- `limit` ✅
- `offset` ✅

**وضعیت:** ✅ همه پارامترها پشتیبانی می‌شوند

#### 4.1.5. Consultation List Parameters
**Endpoint:** `GET /api/v1/beautybooking/consultations/list`

**پارامترهای React:**
- `salon_id` ✅
- `consultation_type` ✅
- `limit` ✅
- `offset` ✅

**وضعیت:** ✅ همه پارامترها پشتیبانی می‌شوند

#### 4.1.6. Retail Products Parameters
**Endpoint:** `GET /api/v1/beautybooking/retail/products`

**پارامترهای React:**
- `salon_id` ✅
- `category_id` ✅
- `category` ✅ (همچنین `category_id` پشتیبانی می‌شود)
- `limit` ✅
- `offset` ✅

**وضعیت:** ✅ همه پارامترها پشتیبانی می‌شوند

#### 4.1.7. Retail Orders Parameters
**Endpoint:** `GET /api/v1/beautybooking/retail/orders`

**پارامترهای React:**
- `limit` ✅
- `offset` ✅
- `status` ✅

**وضعیت:** ✅ همه پارامترها پشتیبانی می‌شوند

---

## 5. مشکلات Response Data Structure

### 5.1. Salon Response Format
**Endpoint:** `GET /api/v1/beautybooking/salons/{id}`

**مشکل:** React انتظار دارد که response شامل تمام فیلدهای لازم باشد.

**تغییرات لازم:**
- بررسی کنید که `formatSalonForApi()` تمام فیلدهای مورد نیاز React را برمی‌گرداند
- مطمئن شوید که `is_open` به درستی محاسبه می‌شود
- بررسی کنید که `distance` به درستی محاسبه می‌شود (اگر latitude/longitude ارائه شده باشد)

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautySalonController.php`
**متد:** `formatSalonForApi()` (خط 382)

**فیلدهای مورد نیاز React:**
- `id` ✅
- `name` ✅
- `business_type` ✅
- `avg_rating` ✅
- `total_reviews` ✅
- `total_bookings` ✅
- `is_verified` ✅
- `is_featured` ✅
- `badges` ✅
- `latitude` ✅
- `longitude` ✅
- `address` ✅
- `image` ✅
- `phone` ✅
- `email` ✅
- `opening_time` ✅
- `closing_time` ✅
- `is_open` ✅
- `distance` ✅
- `store` ✅ (nested object)
- `services` ✅ (when includeDetails=true)
- `staff` ✅ (when includeDetails=true)
- `working_hours` ✅ (when includeDetails=true)
- `reviews` ✅ (when includeDetails=true)

### 5.2. Booking Response Format
**Endpoint:** `GET /api/v1/beautybooking/bookings/{id}`

**مشکل:** React انتظار دارد که response شامل تمام فیلدهای لازم باشد.

**تغییرات لازم:**
- بررسی کنید که `formatBookingForApi()` تمام فیلدهای مورد نیاز React را برمی‌گرداند

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
**متد:** `formatBookingForApi()` (باید بررسی شود)

**فیلدهای مورد نیاز React:**
- `id` ✅
- `booking_reference` ✅
- `status` ✅
- `booking_date` ✅
- `booking_time` ✅
- `total_amount` ✅
- `payment_status` ✅
- `payment_method` ✅
- `salon` ✅ (nested object)
- `service` ✅ (nested object)
- `staff` ✅ (nested object or null)
- `review` ✅ (nested object or null)
- `conversation` ✅ (nested object or null)
- `created_at` ✅
- `updated_at` ✅

### 5.3. Package Response Format
**Endpoint:** `GET /api/v1/beautybooking/packages/{id}`

**مشکل:** React انتظار دارد که response شامل تمام فیلدهای لازم باشد.

**تغییرات لازم:**
- بررسی کنید که `formatPackage()` تمام فیلدهای مورد نیاز React را برمی‌گرداند

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`
**متد:** `formatPackage()` (باید بررسی شود)

**فیلدهای مورد نیاز React:**
- `id` ✅
- `name` ✅
- `description` ✅
- `sessions_count` ✅
- `total_price` ✅
- `salon` ✅ (nested object)
- `service` ✅ (nested object)
- `status` ✅
- `expires_at` ✅ (if applicable)

### 5.4. Review Response Format
**Endpoint:** `POST /api/v1/beautybooking/reviews`

**مشکل:** React انتظار دارد که response شامل تمام فیلدهای لازم باشد.

**تغییرات لازم:**
- بررسی کنید که response شامل `attachments` با URL کامل باشد
- مطمئن شوید که `attachments` به صورت array از URL strings برگردانده می‌شود

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`
**متد:** `store()` (خط 65)

**وضعیت فعلی:** ✅ Response شامل attachments با URL کامل است (خط 158-160)

---

## 6. مشکلات Error Handling

### مشکل کلی:
React انتظار دارد که error response همیشه به این فرمت باشد:
```json
{
  "errors": [
    {
      "code": "validation",
      "message": "Error message"
    }
  ]
}
```

### تغییرات لازم:

#### 6.1. استفاده از `BeautyApiResponse` Trait
**وضعیت:** ✅ همه کنترلرها از `errorResponse()` و `validationErrorResponse()` استفاده می‌کنند

**نیاز به بررسی:**
- مطمئن شوید که همه error responses از این فرمت استفاده می‌کنند
- بررسی کنید که error codes به درستی تنظیم شده‌اند

#### 6.2. Error Codes
**مشکل:** React ممکن است از error codes خاصی استفاده کند.

**تغییرات لازم:**
- بررسی کنید که error codes با React هماهنگ هستند
- مطمئن شوید که error messages به درستی translate می‌شوند

---

## 7. فیچرهای موجود در React که در Laravel ناقص هستند

### 7.1. Service Suggestions
**Endpoint:** `GET /api/v1/beautybooking/services/{id}/suggestions`

**وضعیت:** ✅ موجود است

**نیاز به بررسی:**
- مطمئن شوید که response format با React هماهنگ است
- بررسی کنید که `salon_id` parameter به درستی handle می‌شود

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
**متد:** `getServiceSuggestions()` (خط 45)

### 7.2. Availability Check
**Endpoint:** `POST /api/v1/beautybooking/availability/check`

**وضعیت:** ✅ موجود است

**نیاز به بررسی:**
- مطمئن شوید که response format شامل `available_slots` array است
- بررسی کنید که `service_duration_minutes` در response موجود است

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
**متد:** `checkAvailability()` (خط 119)

### 7.3. Consultation Availability Check
**Endpoint:** `POST /api/v1/beautybooking/consultations/check-availability`

**وضعیت:** ✅ موجود است

**نیاز به بررسی:**
- مطمئن شوید که response format با React هماهنگ است

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
**متد:** `checkAvailability()` (باید بررسی شود)

### 7.4. Booking Conversation
**Endpoint:** `GET /api/v1/beautybooking/bookings/{id}/conversation`

**وضعیت:** ✅ موجود است

**نیاز به بررسی:**
- مطمئن شوید که response format شامل تمام فیلدهای لازم است

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
**متد:** `getConversation()` (خط 335)

### 7.5. Package Status
**Endpoint:** `GET /api/v1/beautybooking/packages/{id}/status`

**وضعیت:** ✅ موجود است

**نیاز به بررسی:**
- مطمئن شوید که response format شامل تمام فیلدهای لازم است

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`
**متد:** `getPackageStatus()` (باید بررسی شود)

### 7.6. Package Usage History
**Endpoint:** `GET /api/v1/beautybooking/packages/{id}/usage-history`

**وضعیت:** ✅ موجود است

**نیاز به بررسی:**
- مطمئن شوید که response format شامل تمام فیلدهای لازم است

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`
**متد:** `getUsageHistory()` (باید بررسی شود)

---

## 8. فیچرهای موجود در Laravel که در React ناقص هستند

### 8.1. Monthly Top Rated Salons
**Endpoint:** `GET /api/v1/beautybooking/salons/monthly-top-rated`

**وضعیت:** ✅ موجود است در Laravel
**وضعیت:** ✅ موجود است در React

**نیاز به بررسی:**
- مطمئن شوید که response format شامل `year` و `month` در meta است

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautySalonController.php`
**متد:** `monthlyTopRated()` (خط 233)

### 8.2. Trending Clinics
**Endpoint:** `GET /api/v1/beautybooking/salons/trending-clinics`

**وضعیت:** ✅ موجود است در Laravel
**وضعیت:** ✅ موجود است در React

**نیاز به بررسی:**
- مطمئن شوید که response format شامل `year` و `month` در meta است

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautySalonController.php`
**متد:** `trendingClinics()` (خط 300)

---

## 9. مشکلات Vendor API

### 9.1. Vendor Booking List
**Endpoint:** `GET /api/v1/beautybooking/vendor/bookings/list/{all}`

**مشکل:** React از `all` parameter استفاده می‌کند که می‌تواند `'all'` یا status خاص باشد.

**وضعیت:** ✅ موجود است

**نیاز به بررسی:**
- مطمئن شوید که logic به درستی کار می‌کند
- بررسی کنید که وقتی `all='all'` است، status filter اعمال نمی‌شود

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Vendor/BeautyBookingController.php`
**متد:** `list()` (باید بررسی شود)

### 9.2. Vendor Booking Details
**Endpoint:** `GET /api/v1/beautybooking/vendor/bookings/details`

**مشکل:** React از `booking_id` query parameter استفاده می‌کند.

**وضعیت:** ✅ موجود است

**نیاز به بررسی:**
- مطمئن شوید که response format شامل تمام فیلدهای لازم است

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Vendor/BeautyBookingController.php`
**متد:** `details()` (باید بررسی شود)

---

## 10. مشکلات Validation

### 10.1. Date Validation
**مشکل:** React ممکن است تاریخ‌ها را به فرمت‌های مختلفی ارسال کند.

**تغییرات لازم:**
- مطمئن شوید که همه date validations از `date` rule استفاده می‌کنند
- بررسی کنید که `after_or_equal:today` برای booking dates استفاده می‌شود

### 10.2. Time Validation
**مشکل:** React ممکن است زمان‌ها را به فرمت‌های مختلفی ارسال کند.

**تغییرات لازم:**
- مطمئن شوید که همه time validations از `date_format:H:i` استفاده می‌کنند

### 10.3. File Upload Validation
**مشکل:** React ممکن است فایل‌ها را به صورت FormData ارسال کند.

**تغییرات لازم:**
- مطمئن شوید که همه file upload validations از `file` rule استفاده می‌کنند
- بررسی کنید که max file size به درستی تنظیم شده است

**فایل:** `Modules/BeautyBooking/Http/Requests/BeautyReviewStoreRequest.php`
**نیاز به بررسی:**
- مطمئن شوید که validation rules برای attachments به درستی تنظیم شده‌اند

---

## 11. مشکلات File Upload

### 11.1. Review Attachments
**Endpoint:** `POST /api/v1/beautybooking/reviews`

**مشکل:** React ممکن است فایل‌ها را به صورت array یا single file ارسال کند.

**وضعیت فعلی:** ✅ Laravel هر دو را handle می‌کند (خط 98-104)

**نیاز به بررسی:**
- مطمئن شوید که file upload path به درستی تنظیم شده است
- بررسی کنید که file URLs در response به درستی برگردانده می‌شوند

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`
**متد:** `store()` (خط 95-124)

### 11.2. Staff Avatar Upload
**Endpoint:** `POST /api/v1/beautybooking/vendor/staff/create`

**وضعیت:** ✅ موجود است

**نیاز به بررسی:**
- مطمئن شوید که file upload به درستی کار می‌کند

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Vendor/BeautyStaffController.php`
**متد:** `store()` (باید بررسی شود)

### 11.3. Service Image Upload
**Endpoint:** `POST /api/v1/beautybooking/vendor/service/create`

**وضعیت:** ✅ موجود است

**نیاز به بررسی:**
- مطمئن شوید که file upload به درستی کار می‌کند

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Vendor/BeautyServiceController.php`
**متد:** `store()` (باید بررسی شود)

### 11.4. Retail Product Image Upload
**Endpoint:** `POST /api/v1/beautybooking/vendor/retail/products`

**وضعیت:** ✅ موجود است

**نیاز به بررسی:**
- مطمئن شوید که file upload به درستی کار می‌کند

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Vendor/BeautyRetailController.php`
**متد:** `storeProduct()` (باید بررسی شود)

---

## 12. مشکلات Payment Processing

### 12.1. Digital Payment Redirect
**مشکل:** React انتظار دارد که در صورت digital payment، redirect URL برگردانده شود.

**وضعیت فعلی:** ✅ Laravel redirect URL را برمی‌گرداند (خط 225-231)

**نیاز به بررسی:**
- مطمئن شوید که response format شامل `redirect_url` است
- بررسی کنید که booking data نیز در response موجود است

**فایل:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
**متد:** `store()` (خط 225-231)

### 12.2. Payment Gateway Options
**مشکل:** React ممکن است `payment_gateway`, `callback_url`, `payment_platform` را ارسال کند.

**وضعیت فعلی:** ✅ Laravel این پارامترها را می‌پذیرد (خط 218-221)

**نیاز به بررسی:**
- مطمئن شوید که این پارامترها به درستی به payment service پاس داده می‌شوند

---

## 13. مشکلات Notification

### 13.1. Push Notifications
**وضعیت:** ✅ Laravel از `BeautyPushNotification` trait استفاده می‌کند

**نیاز به بررسی:**
- مطمئن شوید که notifications به درستی ارسال می‌شوند
- بررسی کنید که notification payload با React هماهنگ است

**فایل:** `Modules/BeautyBooking/Traits/BeautyPushNotification.php`
**نیاز به بررسی:**
- بررسی کنید که notification structure به درستی تنظیم شده است

---

## 14. مشکلات Cache

### 14.1. Cache TTL Configuration
**مشکل:** React ممکن است انتظار داشته باشد که cache به درستی کار کند.

**وضعیت فعلی:** ✅ Laravel از cache استفاده می‌کند

**نیاز به بررسی:**
- مطمئن شوید که cache keys به درستی generate می‌شوند
- بررسی کنید که cache invalidation به درستی کار می‌کند

**فایل:** `Modules/BeautyBooking/Config/config.php`
**نیاز به بررسی:**
- بررسی کنید که cache TTL values به درستی تنظیم شده‌اند

---

## 15. مشکلات Database Queries

### 15.1. Eager Loading
**مشکل:** React ممکن است انتظار داشته باشد که nested relationships به درستی load شوند.

**وضعیت فعلی:** ✅ Laravel از eager loading استفاده می‌کند

**نیاز به بررسی:**
- مطمئن شوید که همه relationships به درستی eager load می‌شوند
- بررسی کنید که N+1 query problem وجود ندارد

---

## 16. چک‌لیست نهایی

### 16.1. API Endpoints
- [ ] همه endpoints با React هماهنگ هستند
- [ ] همه request parameters به درستی handle می‌شوند
- [ ] همه response formats یکسان هستند

### 16.2. Error Handling
- [ ] همه error responses از فرمت استاندارد استفاده می‌کنند
- [ ] همه error codes به درستی تنظیم شده‌اند
- [ ] همه error messages translate می‌شوند

### 16.3. Validation
- [ ] همه validations به درستی کار می‌کنند
- [ ] همه validation messages translate می‌شوند

### 16.4. File Upload
- [ ] همه file uploads به درستی کار می‌کنند
- [ ] همه file URLs در response به درستی برگردانده می‌شوند

### 16.5. Payment Processing
- [ ] همه payment methods به درستی کار می‌کنند
- [ ] redirect URLs به درستی برگردانده می‌شوند

### 16.6. Pagination
- [ ] همه list endpoints از pagination پشتیبانی می‌کنند
- [ ] همه pagination responses شامل metadata هستند

---

## 17. مرجع فایل‌های React برای هماهنگی

### فایل‌های API:
- `/home/sepehr/Projects/6ammart-react/src/api-manage/another-formated-api/beautyApi.js` - تمام API calls
- `/home/sepehr/Projects/6ammart-react/src/api-manage/another-formated-api/beautyVendorApi.js` - vendor API calls
- `/home/sepehr/Projects/6ammart-react/src/api-manage/ApiRoutes.js` - route definitions

### فایل‌های Hooks:
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/` - تمام hooks

---

## 18. نکات مهم

1. **همیشه قبل از اعمال تغییرات، از کد فعلی backup بگیرید**
2. **تست کنید که همه endpoints به درستی کار می‌کنند**
3. **مطمئن شوید که response formats با React هماهنگ هستند**
4. **بررسی کنید که error handling به درستی کار می‌کند**
5. **تست کنید که file uploads به درستی کار می‌کنند**
6. **بررسی کنید که payment processing به درستی کار می‌کند**

---

## 19. خلاصه تغییرات

### تغییرات ضروری:
1. اضافه کردن پشتیبانی از `date_range` parameter در booking list
2. اضافه کردن پشتیبانی از `service_type` parameter در booking list
3. بررسی و اصلاح همه response formats
4. بررسی و اصلاح همه error responses
5. بررسی و اصلاح همه file upload handlers

### تغییرات توصیه شده:
1. بهبود cache strategy
2. بهبود error messages
3. بهبود validation rules
4. بهبود documentation

---

**تاریخ ایجاد:** 2025-01-05
**آخرین به‌روزرسانی:** 2025-01-05

