# تغییرات کامل لازم در Laravel - ماژول زیبایی (برای Cursor AI)

**مسیر پروژه:** `/home/sepehr/Projects/6ammart-laravel/`

## 📋 خلاصه اجرایی

این سند شامل تمام تغییرات لازم در پروژه Laravel برای هماهنگی کامل با React frontend است. تمام تغییرات باید در مسیر `Modules/BeautyBooking/` انجام شود. این سند به طور کامل و با جزئیات تمام ناهماهنگی‌ها، مشکلات و فیچرهای ناقص را پوشش می‌دهد.

---

## 🔍 روش بررسی

قبل از اعمال هر تغییر:
1. فایل‌های React مربوطه را در `/home/sepehr/Projects/6ammart-react/` بررسی کنید
2. API calls و expected response format را در React چک کنید
3. تغییرات را در Laravel اعمال کنید
4. تست کنید که response format با React هماهنگ است

---

## 1. مشکلات Pagination Format

### مشکل کلی:
React از `offset` و `limit` استفاده می‌کند، اما Laravel از `page` استفاده می‌کند. باید تبدیل صحیح انجام شود.

### فایل‌های React برای بررسی:
- `/home/sepehr/Projects/6ammart-react/src/api-manage/another-formated-api/beautyApi.js` - تمام API calls
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetBookings.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetUserReviews.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetPackages.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetGiftCards.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetLoyaltyCampaigns.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetConsultations.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetRetailProducts.js`

### تغییرات لازم:

#### 1.1. `BeautyBookingController.php` - متد `index()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
**خط فعلی:** خط 256-280

**بررسی:** کد فعلی درست است، اما باید مطمئن شویم که:
- [ ] `limit` و `offset` از request گرفته می‌شوند
- [ ] تبدیل `offset` به `page` به درستی انجام می‌شود
- [ ] Response شامل `total`, `per_page`, `current_page`, `last_page` است

**کد فعلی (درست است):**
```php
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

**تأیید:** این کد درست است و نیازی به تغییر ندارد.

---

#### 1.2. `BeautyPackageController.php` - متد `index()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`
**خط فعلی:** خط 42-59

**مشکل:** React از `limit` و `offset` استفاده می‌کند، اما Laravel از `per_page` هم پشتیبانی می‌کند.

**کد فعلی:**
```php
$limit = $request->get('per_page', $request->get('limit', 25));
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

**بررسی:** این کد درست است و از هر دو `per_page` و `limit` پشتیبانی می‌کند. نیازی به تغییر ندارد.

---

#### 1.3. `BeautyGiftCardController.php` - متد `index()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php`
**خط فعلی:** خط 311-327

**بررسی:** 
- [ ] اطمینان از استفاده از `limit` و `offset`
- [ ] تبدیل صحیح به `page`
- [ ] Response format صحیح

**کد فعلی (درست است):**
```php
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

---

#### 1.4. `BeautyLoyaltyController.php` - متد `getCampaigns()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php`
**خط فعلی:** خط 83-99

**بررسی:**
- [ ] اطمینان از استفاده از `limit` و `offset`
- [ ] تبدیل صحیح به `page`
- [ ] Response format صحیح

**کد فعلی (درست است):**
```php
$limit = $request->get('per_page', $request->get('limit', 25));
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

---

#### 1.5. `BeautyConsultationController.php` - متد `list()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
**خط فعلی:** خط 58-113

**بررسی:**
- [ ] اطمینان از استفاده از `limit` و `offset`
- [ ] تبدیل صحیح به `page`
- [ ] Response format صحیح

**کد فعلی (درست است):**
```php
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

---

#### 1.6. `BeautyRetailController.php` - متد `listProducts()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`
**خط فعلی:** خط 59-109

**بررسی:**
- [ ] اطمینان از استفاده از `limit` و `offset`
- [ ] تبدیل صحیح به `page`
- [ ] Response format صحیح

**کد فعلی (درست است):**
```php
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

---

#### 1.7. `BeautyReviewController.php` - متد `index()` و `getSalonReviews()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`
**خط فعلی:** خط 198-214 و 237-254

**بررسی:**
- [ ] اطمینان از استفاده از `limit` و `offset`
- [ ] تبدیل صحیح به `page`
- [ ] Response format صحیح

**کد فعلی (درست است):**
```php
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

---

## 2. مشکلات Response Format

### مشکل کلی:
React انتظار دارد که response format یکسان باشد. باید مطمئن شویم که همه controllers از `BeautyApiResponse` trait استفاده می‌کنند.

### بررسی:

#### 2.1. بررسی استفاده از `BeautyApiResponse` trait
**فایل:** `Modules/BeautyBooking/Traits/BeautyApiResponse.php`

**بررسی در تمام Controllers:**
- [ ] `BeautySalonController` - استفاده می‌کند ✅
- [ ] `BeautyBookingController` - استفاده می‌کند ✅
- [ ] `BeautyPackageController` - استفاده می‌کند ✅
- [ ] `BeautyGiftCardController` - استفاده می‌کند ✅
- [ ] `BeautyLoyaltyController` - استفاده می‌کند ✅
- [ ] `BeautyConsultationController` - استفاده می‌کند ✅
- [ ] `BeautyRetailController` - استفاده می‌کند ✅
- [ ] `BeautyReviewController` - استفاده می‌کند ✅
- [ ] `BeautyCategoryController` - استفاده می‌کند ✅

**نتیجه:** همه controllers از trait استفاده می‌کنند. ✅

---

#### 2.2. بررسی Response Structure برای Paginated Lists
**مشکل:** React انتظار دارد response شامل `data`, `total`, `per_page`, `current_page`, `last_page` باشد.

**بررسی:**
- [ ] `BeautyBookingController::index()` - استفاده از `listResponse()` ✅
- [ ] `BeautyPackageController::index()` - استفاده از `listResponse()` ✅
- [ ] `BeautyGiftCardController::index()` - استفاده از `listResponse()` ✅
- [ ] `BeautyLoyaltyController::getCampaigns()` - استفاده از `listResponse()` ✅
- [ ] `BeautyConsultationController::list()` - استفاده از `listResponse()` ✅
- [ ] `BeautyRetailController::listProducts()` - استفاده از `listResponse()` ✅
- [ ] `BeautyReviewController::index()` - استفاده از `listResponse()` ✅
- [ ] `BeautyReviewController::getSalonReviews()` - استفاده از `listResponse()` ✅

**نتیجه:** همه از `listResponse()` استفاده می‌کنند که format صحیح را برمی‌گرداند. ✅

---

#### 2.3. بررسی Response Structure برای Single Items
**مشکل:** React انتظار دارد response شامل `message` و `data` باشد.

**بررسی:**
- [ ] `BeautySalonController::show()` - استفاده از `successResponse()` ✅
- [ ] `BeautyBookingController::show()` - استفاده از `successResponse()` ✅
- [ ] `BeautyPackageController::show()` - استفاده از `successResponse()` ✅
- [ ] `BeautyBookingController::store()` - استفاده از `successResponse()` ✅
- [ ] `BeautyPackageController::purchase()` - استفاده از `successResponse()` ✅
- [ ] `BeautyGiftCardController::purchase()` - استفاده از `successResponse()` ✅
- [ ] `BeautyGiftCardController::redeem()` - استفاده از `successResponse()` ✅
- [ ] `BeautyLoyaltyController::getPoints()` - استفاده از `successResponse()` ✅
- [ ] `BeautyLoyaltyController::redeem()` - استفاده از `successResponse()` ✅
- [ ] `BeautyConsultationController::book()` - استفاده از `successResponse()` ✅
- [ ] `BeautyRetailController::createOrder()` - استفاده از `successResponse()` ✅
- [ ] `BeautyReviewController::store()` - استفاده از `successResponse()` ✅

**نتیجه:** همه از `successResponse()` استفاده می‌کنند که format صحیح را برمی‌گرداند. ✅

---

#### 2.4. بررسی Error Response Format
**مشکل:** React انتظار دارد error response شامل `errors` array با `code` و `message` باشد.

**بررسی:**
- [ ] همه controllers از `errorResponse()` استفاده می‌کنند ✅
- [ ] همه validation errors از `validationErrorResponse()` استفاده می‌کنند ✅

**نتیجه:** همه error responses format صحیح دارند. ✅

---

## 3. مشکلات Payment Method Naming

### مشکل کلی:
React در برخی جاها از `online` استفاده می‌کند، اما Laravel انتظار `digital_payment` دارد.

### فایل‌های React برای بررسی:
- `/home/sepehr/Projects/6ammart-react/src/components/home/module-wise-components/beauty/components/ConsultationBooking.js`
- `/home/sepehr/Projects/6ammart-react/src/components/home/module-wise-components/beauty/components/RetailCheckout.js`

### تغییرات لازم:

#### 3.1. `BeautyConsultationController.php` - متد `book()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
**خط فعلی:** خط 182-186

**کد فعلی (درست است):**
```php
// Convert 'online' to 'digital_payment' for backward compatibility
$paymentMethod = $request->payment_method === 'online' 
    ? 'digital_payment' 
    : $request->payment_method;
```

**بررسی:** این کد درست است و از `online` به `digital_payment` تبدیل می‌کند. ✅

---

#### 3.2. `BeautyRetailController.php` - متد `createOrder()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`
**خط فعلی:** خط 177-181

**کد فعلی (درست است):**
```php
// Convert 'online' to 'digital_payment' for backward compatibility
$paymentMethod = $request->payment_method === 'online' 
    ? 'digital_payment' 
    : $request->payment_method;
```

**بررسی:** این کد درست است و از `online` به `digital_payment` تبدیل می‌کند. ✅

---

#### 3.3. بررسی Validation Rules
**بررسی:** همه controllers که `payment_method` را validate می‌کنند باید `online` را هم بپذیرند یا به `digital_payment` تبدیل کنند.

**فایل‌های بررسی:**
- [ ] `BeautyBookingController::store()` - validation rule: `'payment_method' => 'required|in:wallet,digital_payment,cash_payment'` - باید `online` را هم بپذیرد یا تبدیل کند
- [ ] `BeautyPackageController::purchase()` - validation rule: `'payment_method' => 'required|string|in:wallet,digital_payment,cash_payment'` - باید `online` را هم بپذیرد یا تبدیل کند
- [ ] `BeautyGiftCardController::purchase()` - validation rule: `'payment_method' => 'required|in:wallet,digital_payment,cash_payment'` - باید `online` را هم بپذیرد یا تبدیل کند
- [ ] `BeautyConsultationController::book()` - validation rule: `'payment_method' => 'required|in:digital_payment,wallet,cash_payment'` - باید `online` را هم بپذیرد یا تبدیل کند
- [ ] `BeautyRetailController::createOrder()` - validation rule: `'payment_method' => 'required|in:digital_payment,wallet,cash_payment'` - باید `online` را هم بپذیرد یا تبدیل کند

**تغییرات لازم:**

##### 3.3.1. `BeautyBookingController.php` - متد `store()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
**خط فعلی:** خط 194

**تغییر لازم:**
```php
// قبل از validation، تبدیل 'online' به 'digital_payment'
if ($request->payment_method === 'online') {
    $request->merge(['payment_method' => 'digital_payment']);
}
```

یا در validation rule:
```php
'payment_method' => 'required|in:wallet,digital_payment,cash_payment,online',
```

و سپس در کد:
```php
$paymentMethod = $request->payment_method === 'online' 
    ? 'digital_payment' 
    : $request->payment_method;
```

---

##### 3.3.2. `BeautyPackageController.php` - متد `purchase()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`
**خط فعلی:** خط 143-151

**تغییر لازم:**
```php
// قبل از validation، تبدیل 'online' به 'digital_payment'
if ($request->payment_method === 'online') {
    $request->merge(['payment_method' => 'digital_payment']);
}
```

یا در validation rule:
```php
'payment_method' => 'required|string|in:wallet,digital_payment,cash_payment,online',
```

و سپس در کد:
```php
$paymentMethod = $request->payment_method === 'online' 
    ? 'digital_payment' 
    : $request->payment_method;
```

---

##### 3.3.3. `BeautyGiftCardController.php` - متد `purchase()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php`
**خط فعلی:** خط 180-184

**تغییر لازم:**
```php
// قبل از validation، تبدیل 'online' به 'digital_payment'
if ($request->payment_method === 'online') {
    $request->merge(['payment_method' => 'digital_payment']);
}
```

یا در validation rule:
```php
'payment_method' => 'required|in:wallet,digital_payment,cash_payment,online',
```

و سپس در کد:
```php
$paymentMethod = $request->payment_method === 'online' 
    ? 'digital_payment' 
    : $request->payment_method;
```

---

## 4. مشکلات Request Parameters

### مشکل کلی:
بررسی اینکه React چه parameters ارسال می‌کند و Laravel چه parameters انتظار دارد.

### بررسی:

#### 4.1. `BeautySalonController::search()`
**React API Call:**
```javascript
searchSalons: (params) => {
  // search, latitude, longitude, category_id, business_type, min_rating, radius
}
```

**Laravel Validation:**
```php
'search' => 'nullable|string|max:255',
'latitude' => 'nullable|numeric',
'longitude' => 'nullable|numeric',
'category_id' => 'nullable|integer|exists:beauty_service_categories,id',
'business_type' => 'nullable|in:salon,clinic',
'min_rating' => 'nullable|numeric|min:0|max:5',
'radius' => 'nullable|numeric|min:1|max:100',
```

**بررسی:** همه parameters هماهنگ هستند. ✅

---

#### 4.2. `BeautyBookingController::store()`
**React API Call:**
```javascript
createBooking: (bookingData) => {
  // salon_id, service_id, staff_id, booking_date, booking_time, payment_method
}
```

**Laravel Validation (BeautyBookingStoreRequest):**
- باید بررسی شود که تمام فیلدهای لازم وجود دارد

**بررسی:** باید فایل `BeautyBookingStoreRequest.php` بررسی شود.

---

#### 4.3. `BeautyPackageController::index()`
**React API Call:**
```javascript
getPackages: (params) => {
  // salon_id, service_id, per_page, limit, offset
}
```

**Laravel:**
```php
$request->filled('salon_id')
$request->filled('service_id')
$limit = $request->get('per_page', $request->get('limit', 25));
$offset = $request->get('offset', 0);
```

**بررسی:** همه parameters هماهنگ هستند. ✅

---

#### 4.4. `BeautyRetailController::listProducts()`
**React API Call:**
```javascript
getRetailProducts: (params) => {
  // salon_id, category_id, category, limit, offset
}
```

**Laravel Validation:**
```php
'salon_id' => 'required|integer|exists:beauty_salons,id',
'category' => 'nullable|string|max:100',
'category_id' => 'nullable|integer',
'limit' => 'nullable|integer|min:1|max:100',
'offset' => 'nullable|integer|min:0',
```

**بررسی:** همه parameters هماهنگ هستند. ✅

---

## 5. مشکلات File Upload (Review Attachments)

### مشکل کلی:
React فایل‌ها را به صورت `FormData` با `attachments[]` ارسال می‌کند.

### بررسی:

#### 5.1. `BeautyReviewController::store()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`
**خط فعلی:** خط 94-124

**کد فعلی:**
```php
if ($request->hasFile('attachments')) {
    $files = $request->file('attachments');
    if (!is_array($files)) {
        $files = [$files];
    }
    // ...
}
```

**React API Call:**
```javascript
formData.append("attachments[]", file);
```

**بررسی:** 
- [ ] Laravel باید `attachments[]` را به صورت array دریافت کند
- [ ] کد فعلی درست است و از `$request->file('attachments')` استفاده می‌کند که array را برمی‌گرداند ✅

**نکته:** Laravel به صورت خودکار `attachments[]` را به array تبدیل می‌کند. کد فعلی درست است.

---

## 6. فیچرهای موجود در Laravel که در React توسعه داده نشده‌اند

### 6.1. Service Suggestions (Cross-selling)
**وضعیت در Laravel:** ✅ موجود
**وضعیت در React:** ✅ Hook موجود است (`useGetServiceSuggestions`)

**بررسی:**
- [ ] API endpoint موجود است: `GET /api/v1/beautybooking/services/{id}/suggestions`
- [ ] React hook موجود است
- [ ] کامپوننت UI نیاز به بررسی دارد

**نتیجه:** API و Hook موجود است، فقط UI component نیاز به بررسی دارد.

---

### 6.2. Package Status (Remaining Sessions)
**وضعیت در Laravel:** ✅ موجود
**وضعیت در React:** ✅ Hook موجود است (`useGetPackageStatus`)

**بررسی:**
- [ ] API endpoint موجود است: `GET /api/v1/beautybooking/packages/{id}/status`
- [ ] React hook موجود است
- [ ] UI component نیاز به بررسی دارد

**نتیجه:** API و Hook موجود است، فقط UI component نیاز به بررسی دارد.

---

### 6.3. Booking Conversation
**وضعیت در Laravel:** ✅ موجود
**وضعیت در React:** ✅ Hook موجود است (`useGetBookingConversation`)

**بررسی:**
- [ ] API endpoint موجود است: `GET /api/v1/beautybooking/bookings/{id}/conversation`
- [ ] React hook موجود است
- [ ] UI component نیاز به بررسی دارد

**نتیجه:** API و Hook موجود است، فقط UI component نیاز به بررسی دارد.

---

## 7. مشکلات Date/Time Format

### مشکل کلی:
React باید dates را به صورت `YYYY-MM-DD` و times را به صورت `H:i` ارسال کند.

### بررسی:

#### 7.1. `BeautyBookingController::checkAvailability()`
**Laravel Validation:**
```php
'date' => 'required|date|after_or_equal:today',
```

**React API Call:**
```javascript
checkAvailability: (availabilityData) => {
  // salon_id, service_id, date, staff_id
}
```

**بررسی:** باید مطمئن شویم که React date را به صورت `YYYY-MM-DD` ارسال می‌کند.

---

#### 7.2. `BeautyBookingController::store()`
**Laravel Validation:**
```php
'booking_date' => 'required|date|after:today',
'booking_time' => 'required|date_format:H:i',
```

**React API Call:**
```javascript
createBooking: (bookingData) => {
  // booking_date, booking_time
}
```

**بررسی:** باید مطمئن شویم که React:
- `booking_date` را به صورت `YYYY-MM-DD` ارسال می‌کند
- `booking_time` را به صورت `H:i` ارسال می‌کند

---

## 8. مشکلات Response Data Structure

### مشکل کلی:
بررسی اینکه structure داده‌های برگشتی از Laravel با آنچه React انتظار دارد هماهنگ است.

### بررسی:

#### 8.1. Salon Data Structure
**Laravel Response (`BeautySalonController::formatSalonForApi()`):**
```php
[
    'id' => $salon->id,
    'name' => $salon->store->name ?? '',
    'business_type' => $salon->business_type,
    'avg_rating' => $salon->avg_rating,
    'total_reviews' => $salon->total_reviews,
    'total_bookings' => $salon->total_bookings,
    'is_verified' => $salon->is_verified,
    'is_featured' => $salon->is_featured,
    'badges' => $salon->badges_list ?? [],
    'latitude' => $salon->store->latitude ?? null,
    'longitude' => $salon->store->longitude ?? null,
    'address' => $salon->store->address ?? null,
    'image' => $salon->store->image ? asset('storage/' . $salon->store->image) : null,
    'store' => [...],
]
```

**React Usage:**
- باید بررسی شود که React از این structure استفاده می‌کند

---

#### 8.2. Booking Data Structure
**Laravel Response (`BeautyBookingController::formatBookingForApi()`):**
```php
[
    'id' => $booking->id,
    'booking_reference' => $booking->booking_reference ?? '',
    'booking_date' => $bookingDate,
    'booking_time' => $bookingTime ?? '',
    'booking_date_time' => $bookingDateTime,
    'status' => $booking->status ?? 'pending',
    'payment_status' => $booking->payment_status ?? 'unpaid',
    'total_amount' => $booking->total_amount ?? 0.0,
    'salon_name' => $booking->salon?->store?->name ?? '',
    'service_name' => $booking->service?->name ?? '',
]
```

**React Usage:**
- باید بررسی شود که React از این structure استفاده می‌کند

---

## 9. مشکلات Caching

### مشکل کلی:
بررسی caching strategy و TTL values.

### بررسی:

#### 9.1. `BeautySalonController::search()`
**کد فعلی:**
```php
$ttl = config('beautybooking.cache.search_ttl', 300);
$cacheKey = 'beauty_search_' . md5(json_encode([...]));
```

**بررسی:**
- [ ] TTL از config استفاده می‌کند ✅
- [ ] Cache key unique است ✅

---

#### 9.2. `BeautySalonController::popular()`
**کد فعلی:**
```php
$ttl = config('beautybooking.cache.popular_salons_ttl', 3600);
$cacheKey = 'beauty_salons_popular';
```

**بررسی:**
- [ ] TTL از config استفاده می‌کند ✅
- [ ] Cache key مناسب است ✅

---

#### 9.3. `BeautyCategoryController::list()`
**کد فعلی:**
```php
$ttl = config('beautybooking.cache.categories_ttl', 86400);
$cacheKey = 'beauty_categories_list';
```

**بررسی:**
- [ ] TTL از config استفاده می‌کند ✅
- [ ] Cache key مناسب است ✅

---

## 10. مشکلات Authorization

### مشکل کلی:
بررسی authorization checks در تمام endpoints.

### بررسی:

#### 10.1. User-specific Data
**بررسی:**
- [ ] `BeautyBookingController::index()` - فقط bookings کاربر فعلی را برمی‌گرداند ✅
- [ ] `BeautyBookingController::show()` - بررسی ownership ✅
- [ ] `BeautyBookingController::cancel()` - بررسی ownership ✅
- [ ] `BeautyGiftCardController::index()` - فقط gift cards کاربر فعلی را برمی‌گرداند ✅
- [ ] `BeautyReviewController::index()` - فقط reviews کاربر فعلی را برمی‌گرداند ✅

---

## 11. خلاصه تغییرات لازم

### تغییرات با اولویت بالا:

1. **Payment Method Conversion:**
   - [ ] اضافه کردن تبدیل `online` به `digital_payment` در `BeautyBookingController::store()`
   - [ ] اضافه کردن تبدیل `online` به `digital_payment` در `BeautyPackageController::purchase()`
   - [ ] اضافه کردن تبدیل `online` به `digital_payment` در `BeautyGiftCardController::purchase()`

2. **Request Validation:**
   - [ ] بررسی `BeautyBookingStoreRequest.php` برای اطمینان از validation rules

### تغییرات با اولویت متوسط:

3. **Response Structure Documentation:**
   - [ ] بررسی docblocks در تمام controllers
   - [ ] اطمینان از وجود examples در docblocks

4. **Error Messages:**
   - [ ] بررسی translation keys
   - [ ] اطمینان از وجود error messages مناسب

### تغییرات با اولویت پایین:

5. **Performance Optimization:**
   - [ ] بررسی N+1 queries
   - [ ] بررسی eager loading

6. **Testing:**
   - [ ] بررسی وجود tests
   - [ ] اطمینان از coverage مناسب

---

## 12. فایل‌های مرجع React

برای هماهنگی کامل، این فایل‌های React را بررسی کنید:

### API Files:
- `/home/sepehr/Projects/6ammart-react/src/api-manage/another-formated-api/beautyApi.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/ApiRoutes.js`

### Hook Files:
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetSalons.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetBookings.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useCreateBooking.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetPackages.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/usePurchasePackage.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetGiftCards.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useRedeemGiftCard.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetLoyaltyPoints.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetLoyaltyCampaigns.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useRedeemLoyaltyPoints.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetConsultations.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useBookConsultation.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetRetailProducts.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useCreateRetailOrder.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useSubmitReview.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetUserReviews.js`

---

## 13. نکات مهم برای Implementation

1. **همیشه قبل از تغییر، فایل React مربوطه را بررسی کنید**
2. **تست کنید که response format با React هماهنگ است**
3. **از `BeautyApiResponse` trait استفاده کنید**
4. **از `offset` و `limit` برای pagination استفاده کنید و به `page` تبدیل کنید**
5. **`online` را به `digital_payment` تبدیل کنید**
6. **Dates را به صورت `YYYY-MM-DD` و times را به صورت `H:i` بپذیرید**
7. **File uploads را به صورت `attachments[]` array بپذیرید**
8. **Authorization checks را در تمام user-specific endpoints انجام دهید**

---

**تاریخ ایجاد:** 2025-01-XX
**آخرین به‌روزرسانی:** 2025-01-XX

