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

### تغییرات لازم:

#### 1.1. `BeautyBookingController.php` - متد `index()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
**خط فعلی:** خط 258-280

**کد فعلی:**
```php
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

**بررسی:** این کد درست است، اما باید مطمئن شویم که:
- اگر `offset = 0` و `limit = 25` → `page = 1` ✅
- اگر `offset = 25` و `limit = 25` → `page = 2` ✅
- اگر `offset = 50` و `limit = 25` → `page = 3` ✅

**تغییرات:** نیازی به تغییر نیست، اما باید تست شود.

#### 1.2. `BeautyReviewController.php` - متد `index()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`
**خط فعلی:** خط 200-214

**کد فعلی:**
```php
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

**بررسی:** این کد درست است.

#### 1.3. `BeautyReviewController.php` - متد `getSalonReviews()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`
**خط فعلی:** خط 237-255

**کد فعلی:**
```php
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

**بررسی:** این کد درست است.

#### 1.4. `BeautyGiftCardController.php` - متد `index()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php`
**خط فعلی:** خط 311-328

**کد فعلی:**
```php
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

**بررسی:** این کد درست است.

#### 1.5. `BeautyPackageController.php` - متد `index()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`
**خط فعلی:** خط 42-62

**بررسی React:**
```javascript
// در beautyApi.js خط 85-92
getPackages: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.service_id) queryParams.append("service_id", params.service_id);
  if (params.per_page) queryParams.append("per_page", params.per_page);
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/packages?${queryParams.toString()}`);
}
```

**کد فعلی Laravel:**
```php
$limit = $request->get('per_page', $request->get('limit', 25));
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

**بررسی:** این کد درست است و از هر دو `per_page` و `limit` پشتیبانی می‌کند.

#### 1.6. `BeautyConsultationController.php` - متد `list()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
**خط فعلی:** خط 58-120

**کد فعلی:**
```php
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

**بررسی:** این کد درست است.

**مشکل:** Response format از `response()->json()` استفاده می‌کند به جای `listResponse()` trait method.

**تغییرات:**
```php
// خط 112-119 را تغییر دهید:
// قبل:
return response()->json([
    'message' => translate('messages.data_retrieved_successfully'),
    'data' => $formatted->values(),
    'total' => $consultations->total(),
    'per_page' => $consultations->perPage(),
    'current_page' => $consultations->currentPage(),
    'last_page' => $consultations->lastPage(),
], 200);

// بعد:
return $this->listResponse($consultations->setCollection($formatted->values()));
```

#### 1.7. `BeautyRetailController.php` - متد `listProducts()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`
**خط فعلی:** خط 59-111

**کد فعلی:**
```php
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

**بررسی:** این کد درست است.

**مشکل:** Response format از `response()->json()` استفاده می‌کند به جای `listResponse()` trait method.

**تغییرات:**
```php
// خط 108-115 را تغییر دهید:
// قبل:
return response()->json([
    'message' => translate('messages.data_retrieved_successfully'),
    'data' => $formatted->values(),
    'total' => $products->total(),
    'per_page' => $products->perPage(),
    'current_page' => $products->currentPage(),
    'last_page' => $products->lastPage(),
], 200);

// بعد:
return $this->listResponse($products->setCollection($formatted->values()));
```

#### 1.8. `BeautyLoyaltyController.php` - متد `getCampaigns()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php`
**خط فعلی:** خط 83-100

**کد فعلی:**
```php
$limit = $request->get('per_page', $request->get('limit', 25));
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

**بررسی:** این کد درست است و از هر دو `per_page` و `limit` پشتیبانی می‌کند.

---

## 2. مشکلات Payment Method Values

### مشکل کلی:
React و Laravel باید از همان payment method values استفاده کنند. در برخی endpointها از `online` استفاده شده که باید به `digital_payment` تغییر کند.

### بررسی React:
```javascript
// React از این values استفاده می‌کند:
payment_method: "cash_payment" | "wallet" | "digital_payment"
```

### تغییرات لازم:

#### 2.1. `BeautyConsultationController.php` - متد `book()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
**خط فعلی:** خط 163

**کد فعلی:**
```php
'payment_method' => 'required|in:digital_payment,wallet,cash_payment',
```

**بررسی:** این کد درست است. اما در خط 190-192 تبدیل `online` به `digital_payment` وجود دارد که برای backward compatibility است.

**تغییرات:** نیازی به تغییر نیست، اما باید مطمئن شویم که React همیشه `digital_payment` ارسال می‌کند.

#### 2.2. `BeautyRetailController.php` - متد `createOrder()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`
**خط فعلی:** خط 161

**کد فعلی:**
```php
'payment_method' => 'required|in:digital_payment,wallet,cash_payment',
```

**بررسی:** این کد درست است. اما در خط 185-187 تبدیل `online` به `digital_payment` وجود دارد که برای backward compatibility است.

**تغییرات:** نیازی به تغییر نیست، اما باید مطمئن شویم که React همیشه `digital_payment` ارسال می‌کند.

---

## 3. مشکلات Response Format Consistency

### مشکل کلی:
برخی endpointها از `response()->json()` استفاده می‌کنند به جای trait methods. باید همه endpointها از `BeautyApiResponse` trait استفاده کنند.

### تغییرات لازم:

#### 3.1. `BeautyConsultationController.php` - متد `list()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
**خط فعلی:** خط 112-119

**تغییرات:**
```php
// قبل:
return response()->json([
    'message' => translate('messages.data_retrieved_successfully'),
    'data' => $formatted->values(),
    'total' => $consultations->total(),
    'per_page' => $consultations->perPage(),
    'current_page' => $consultations->currentPage(),
    'last_page' => $consultations->lastPage(),
], 200);

// بعد:
return $this->listResponse($consultations->setCollection($formatted->values()));
```

#### 3.2. `BeautyRetailController.php` - متد `listProducts()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`
**خط فعلی:** خط 108-115

**تغییرات:**
```php
// قبل:
return response()->json([
    'message' => translate('messages.data_retrieved_successfully'),
    'data' => $formatted->values(),
    'total' => $products->total(),
    'per_page' => $products->perPage(),
    'current_page' => $products->currentPage(),
    'last_page' => $products->lastPage(),
], 200);

// بعد:
return $this->listResponse($products->setCollection($formatted->values()));
```

---

## 4. مشکلات Request Parameters

### مشکل کلی:
برخی endpointها ممکن است parameter names متفاوتی داشته باشند.

### تغییرات لازم:

#### 4.1. `BeautyRetailController.php` - متد `listProducts()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`
**خط فعلی:** خط 63-64

**بررسی React:**
```javascript
// در beautyApi.js خط 162-169
getRetailProducts: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.category_id) queryParams.append("category_id", params.category_id);
  if (params.category) queryParams.append("category", params.category);
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/retail/products?${queryParams.toString()}`);
}
```

**کد فعلی Laravel:**
```php
'category' => 'nullable|string|max:100',
'category_id' => 'nullable|integer',
```

**بررسی:** این کد درست است و از هر دو `category` و `category_id` پشتیبانی می‌کند.

---

## 5. مشکلات Response Structure برای Specific Endpoints

### 5.1. `BeautyBookingController.php` - متد `store()` و `payment()`
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
**خط فعلی:** خط 220-223 و 427-430

**بررسی React:**
```javascript
// در BookingForm.js
if (response?.data?.redirect_url) {
  window.location.href = response.data.redirect_url;
} else {
  router.push(`/beauty/bookings/${response?.data?.id || response?.data?.booking?.id}`);
}
```

**کد فعلی Laravel:**
```php
return $this->successResponse('redirect_to_payment', [
    'redirect_url' => $paymentResult,
    'booking' => $this->formatBookingForApi($booking),
]);
```

**بررسی:** این کد درست است. React از `redirect_url` استفاده می‌کند و Laravel آن را برمی‌گرداند.

---

## 6. مشکلات Package Status Endpoint

### بررسی:
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
**متد:** `getPackageStatus()`
**خط فعلی:** خط 457-500

**بررسی React:**
```javascript
// در beautyApi.js خط 105-107
getPackageStatus: (id) => {
  return MainApi.get(`/api/v1/beautybooking/packages/${id}/status`);
}
```

**بررسی Laravel Route:**
```php
// در api.php خط 100-102
Route::get('{id}/status', [BeautyBookingController::class, 'getPackageStatus'])
    ->middleware('throttle:60,1')
    ->name('status');
```

**بررسی:** Route درست است و endpoint موجود است.

**Response Format:**
```php
return $this->successResponse('messages.data_retrieved_successfully', [
    'package_id' => $package->id,
    'package_name' => $package->name,
    'total_sessions' => $package->sessions_count,
    'remaining_sessions' => $remainingSessions,
    'used_sessions' => $package->sessions_count - $remainingSessions,
    'is_valid' => $isValid,
    'validity_days' => $package->validity_days,
    'usages' => $usages->map(function ($usage) {
        return [
            'session_number' => $usage->session_number,
            'used_at' => $usage->used_at->format('Y-m-d H:i:s'),
            'status' => $usage->status,
            'booking_id' => $usage->booking_id,
        ];
    }),
]);
```

**بررسی:** Response format درست است.

---

## 7. مشکلات Consultation Credit Percentage

### بررسی:
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
**متد:** `book()`
**خط فعلی:** خط 205-216

**فیچر:** Consultation می‌تواند به main service اعتبار شود (consultation credit percentage).

**بررسی React:**
```javascript
// در beautyApi.js خط 153-155
bookConsultation: (consultationData) => {
  return MainApi.post("/api/v1/beautybooking/consultations/book", consultationData);
}
```

**Request Parameters در Laravel:**
```php
'main_service_id' => 'nullable|integer|exists:beauty_services,id',
```

**بررسی:** این فیچر در Laravel موجود است و React باید `main_service_id` را ارسال کند اگر بخواهد consultation را به main service اعتبار کند.

**تغییرات:** نیازی به تغییر در Laravel نیست، اما باید مطمئن شویم که React از این parameter پشتیبانی می‌کند.

---

## 8. مشکلات Loyalty Campaign Types

### بررسی:
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php`
**متد:** `redeem()`
**خط فعلی:** خط 250-376

**فیچر:** Loyalty campaigns می‌توانند انواع مختلفی داشته باشند:
- `discount`: تخفیف درصدی یا مبلغی
- `wallet_credit`: اعتبار کیف پول
- `cashback`: بازگشت وجه
- `gift_card`: کارت هدیه
- `points`: فقط استفاده از امتیازها

**Response Format:**
```php
'reward' => [
    'type' => 'discount_percentage' | 'discount_amount' | 'wallet_credit' | 'cashback' | 'gift_card' | 'points_redeemed',
    'value' => ...,
    'description' => ...,
    // برای gift_card:
    'gift_card_id' => ...,
    'gift_card_code' => ...,
    'expires_at' => ...,
    // برای wallet_credit و cashback:
    'wallet_balance' => ...,
]
```

**بررسی:** این فیچر در Laravel کامل است و React باید از response structure استفاده کند.

**تغییرات:** نیازی به تغییر در Laravel نیست، اما باید مطمئن شویم که React از تمام reward types پشتیبانی می‌کند.

---

## 9. مشکلات Error Response Format

### بررسی:
**مسیر:** `Modules/BeautyBooking/Traits/BeautyApiResponse.php`
**متد:** `errorResponse()`
**خط فعلی:** خط 79-84

**کد فعلی:**
```php
protected function errorResponse(array $errors, int $status = 403): JsonResponse
{
    return response()->json([
        'errors' => $errors,
    ], $status);
}
```

**Format:**
```json
{
  "errors": [
    {
      "code": "validation",
      "message": "The salon_id field is required."
    }
  ]
}
```

**بررسی React:**
```javascript
// در beautyErrorHandler.js
export const getBeautyErrorMessage = (error) => {
  if (error?.response?.data?.errors?.length > 0) {
    return error.response.data.errors[0].message || error.response.data.errors[0];
  }
  if (error?.response?.data?.message) {
    return error.response.data.message;
  }
  return error?.message || "An error occurred";
};
```

**بررسی:** Error format درست است و React از آن پشتیبانی می‌کند.

---

## 10. فیچرهای Laravel که باید در React بررسی شوند

### 10.1. Package Status Endpoint
**وضعیت:** ✅ موجود در Laravel
**وضعیت React:** ⚠️ باید بررسی شود که hook و component برای نمایش package status وجود دارد

**Endpoint:** `GET /api/v1/beautybooking/packages/{id}/status`

**Response:**
```json
{
  "message": "Data retrieved successfully",
  "data": {
    "package_id": 1,
    "package_name": "Hair Care Package",
    "total_sessions": 5,
    "remaining_sessions": 3,
    "used_sessions": 2,
    "is_valid": true,
    "validity_days": 365,
    "usages": [
      {
        "session_number": 1,
        "used_at": "2024-01-15 10:00:00",
        "status": "completed",
        "booking_id": 100001
      }
    ]
  }
}
```

**تغییرات لازم در React:**
- بررسی وجود hook: `useGetPackageStatus.js`
- بررسی وجود component برای نمایش package status در `PackageDetails.js`

### 10.2. Consultation Credit Percentage
**وضعیت:** ✅ موجود در Laravel
**وضعیت React:** ⚠️ باید بررسی شود که در `ConsultationBooking.js` از `main_service_id` پشتیبانی می‌شود

**Request Parameter:** `main_service_id` (optional)

**تغییرات لازم در React:**
- بررسی `ConsultationBooking.js` که `main_service_id` را ارسال می‌کند

### 10.3. Loyalty Campaign Reward Types
**وضعیت:** ✅ موجود در Laravel
**وضعیت React:** ⚠️ باید بررسی شود که تمام reward types handle می‌شوند

**Reward Types:**
- `discount_percentage`
- `discount_amount`
- `wallet_credit`
- `cashback`
- `gift_card`
- `points_redeemed`

**تغییرات لازم در React:**
- بررسی `LoyaltyPoints.js` که تمام reward types را handle می‌کند

### 10.4. Booking Conversation
**وضعیت:** ✅ موجود در Laravel
**وضعیت React:** ⚠️ باید بررسی شود که component برای نمایش conversation وجود دارد

**Endpoint:** `GET /api/v1/beautybooking/bookings/{id}/conversation`

**Response:**
```json
{
  "message": "Data retrieved successfully",
  "data": {
    "conversation_id": 1,
    "messages": [
      {
        "id": 1,
        "sender_id": 1,
        "message": "Hello",
        "file": null,
        "created_at": "2024-01-20 10:00:00"
      }
    ]
  }
}
```

**تغییرات لازم در React:**
- بررسی وجود hook: `useGetBookingConversation.js`
- بررسی وجود component برای نمایش conversation در `BookingDetails.js`

### 10.5. Service Suggestions (Cross-selling)
**وضعیت:** ✅ موجود در Laravel
**وضعیت React:** ✅ موجود (ServiceSuggestions.js)

**بررسی:** این فیچر در React موجود است.

---

## 11. مشکلات Date/Time Format

### بررسی:
**Laravel انتظار دارد:**
- Date: `YYYY-MM-DD` (مثلاً `2024-01-20`)
- Time: `H:i` (مثلاً `10:00`)

**بررسی React:**
```javascript
// در BookingForm.js
date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
booking_time: "10:00", // باید H:i format باشد
```

**بررسی:** React از format درست استفاده می‌کند.

---

## 12. مشکلات File Upload (Review Attachments)

### بررسی:
**مسیر:** `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`
**متد:** `store()`
**خط فعلی:** خط 98-124

**کد فعلی:**
```php
if ($request->hasFile('attachments')) {
    $files = $request->file('attachments');
    if (!is_array($files)) {
        $files = [$files];
    }
    
    foreach ($files as $file) {
        if (!$file->isValid()) {
            continue;
        }
        
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $uploadedPath = Helpers::upload('beauty/reviews/', $extension, $file);
        if ($uploadedPath) {
            $attachments[] = $uploadedPath;
        }
    }
}
```

**بررسی React:**
```javascript
// در useSubmitReview.js
reviewData.attachments.forEach((file) => {
  formData.append("attachments[]", file);
});
```

**بررسی:** این کد درست است. React از `attachments[]` استفاده می‌کند و Laravel آن را درست handle می‌کند.

**Response Format:**
```php
'attachments' => array_map(function ($path) {
    return asset('storage/' . $path);
}, $attachments),
```

**بررسی:** Response شامل full URLs است که درست است.

---

## 13. خلاصه تغییرات ضروری

### تغییرات با اولویت بالا:

1. **Response Format Consistency:**
   - `BeautyConsultationController::list()` - استفاده از `listResponse()`
   - `BeautyRetailController::listProducts()` - استفاده از `listResponse()`

### تغییرات با اولویت متوسط:

1. **Pagination Format:** تمام endpointها درست هستند، اما باید تست شوند
2. **Payment Method Values:** تمام endpointها درست هستند
3. **Error Response Format:** درست است

### تغییرات با اولویت پایین:

1. **Documentation:** بهبود docblocks
2. **Error Messages:** بهبود error messages
3. **Validation:** بهبود validation rules

---

## 14. چک‌لیست نهایی

قبل از commit کردن تغییرات:

- [ ] تمام pagination endpoints از offset به page تبدیل می‌کنند ✅
- [ ] تمام payment methods از `digital_payment` استفاده می‌کنند ✅
- [ ] تمام responses از `BeautyApiResponse` trait استفاده می‌کنند (2 مورد باقی مانده)
- [ ] تمام file uploads درست handle می‌شوند ✅
- [ ] تمام date/time formats درست هستند ✅
- [ ] تمام parameter names با React هماهنگ هستند ✅
- [ ] تمام response structures با React هماهنگ هستند ✅
- [ ] تمام error formats با React هماهنگ هستند ✅

---

## 15. فایل‌های React برای مرجع

برای بررسی دقیق‌تر، این فایل‌ها را در React بررسی کنید:

1. `/home/sepehr/Projects/6ammart-react/src/api-manage/another-formated-api/beautyApi.js` - تمام API calls
2. `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/` - تمام hooks
3. `/home/sepehr/Projects/6ammart-react/src/components/home/module-wise-components/beauty/components/` - تمام components

---

## 16. فیچرهای Laravel که باید در React پیاده‌سازی شوند

### 16.1. Package Status Display
- Hook: `useGetPackageStatus.js` (باید بررسی شود)
- Component: نمایش در `PackageDetails.js` (باید بررسی شود)

### 16.2. Consultation Credit Percentage
- Component: `ConsultationBooking.js` باید `main_service_id` را پشتیبانی کند

### 16.3. Loyalty Reward Types
- Component: `LoyaltyPoints.js` باید تمام reward types را handle کند

### 16.4. Booking Conversation
- Hook: `useGetBookingConversation.js` (باید بررسی شود)
- Component: نمایش در `BookingDetails.js` (باید بررسی شود)

---

**نکته مهم:** این سند باید به‌روزرسانی شود هر زمان که تغییراتی در React یا Laravel ایجاد می‌شود.

