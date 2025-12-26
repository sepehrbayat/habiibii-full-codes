# تغییرات لازم در Laravel - ماژول زیبایی (برای Cursor AI)

**مسیر پروژه:** `/home/sepehr/Projects/6ammart-laravel/`

## 📋 خلاصه اجرایی

این سند شامل تمام تغییرات لازم در پروژه Laravel برای هماهنگی کامل با React frontend است. تمام تغییرات باید در مسیر `Modules/BeautyBooking/` انجام شود.

---

## 🔍 روش بررسی

قبل از اعمال هر تغییر:
1. فایل‌های React مربوطه را در `/home/sepehr/Projects/6ammart-react/` بررسی کنید
2. API calls و expected response format را در React چک کنید
3. تغییرات را در Laravel اعمال کنید
4. تست کنید که response format با React هماهنگ است

---

## 1. مشکلات Pagination Format

### مشکل:
React از `offset` و `limit` استفاده می‌کند، اما Laravel از `page` استفاده می‌کند. باید تبدیل صحیح انجام شود.

### فایل‌های React برای بررسی:
- `/home/sepehr/Projects/6ammart-react/src/api-manage/another-formated-api/beautyApi.js` - تمام API calls
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetBookings.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetUserReviews.js`

### تغییرات لازم:

#### 1.1. `BeautyBookingController.php` - متد `index()`
**خط فعلی:** خط 258-280
**مشکل:** تبدیل offset به page ممکن است نادرست باشد

**کد فعلی:**
```php
$page = $offset > 0 ? (int)floor($offset / $limit) + 1 : 1;
```

**کد صحیح:**
```php
// محاسبه صحیح page از offset
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

**بررسی:** مطمئن شوید که:
- اگر `offset = 0` و `limit = 25` → `page = 1`
- اگر `offset = 25` و `limit = 25` → `page = 2`
- اگر `offset = 50` و `limit = 25` → `page = 3`

#### 1.2. `BeautyReviewController.php` - متد `index()`
**خط فعلی:** خط 200-214
**مشکل:** همان مشکل pagination

**تغییرات:**
```php
// خط 205-207 را بررسی کنید
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

#### 1.3. `BeautyReviewController.php` - متد `getSalonReviews()`
**خط فعلی:** خط 237-255
**مشکل:** همان مشکل pagination

**تغییرات:**
```php
// خط 244-246 را بررسی کنید
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

#### 1.4. `BeautyGiftCardController.php` - متد `index()`
**خط فعلی:** خط 311-328
**مشکل:** همان مشکل pagination

**تغییرات:**
```php
// خط 318-320 را بررسی کنید
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

#### 1.5. `BeautyPackageController.php` - متد `index()`
**خط فعلی:** خط 42-62
**مشکل:** React از `per_page` استفاده می‌کند نه `limit`

**بررسی React:**
```javascript
// در beautyApi.js خط 85-90
getPackages: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.service_id) queryParams.append("service_id", params.service_id);
  if (params.per_page) queryParams.append("per_page", params.per_page);
  return MainApi.get(`/api/v1/beautybooking/packages?${queryParams.toString()}`);
}
```

**تغییرات لازم:**
```php
// در متد index() خط 44-45
$limit = $request->get('per_page', $request->get('limit', 25)); // پشتیبانی از هر دو
$offset = $request->get('offset', 0);
```

#### 1.6. `BeautyConsultationController.php` - متد `list()`
**خط فعلی:** خط 58-120
**مشکل:** pagination format

**تغییرات:**
```php
// خط 71-76 را بررسی کنید
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

#### 1.7. `BeautyRetailController.php` - متد `listProducts()`
**خط فعلی:** خط 59-111
**مشکل:** pagination format

**تغییرات:**
```php
// خط 72-77 را بررسی کنید
$limit = $request->get('limit', 25);
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

#### 1.8. `BeautyLoyaltyController.php` - متد `getCampaigns()`
**خط فعلی:** خط 83-100
**مشکل:** pagination format

**تغییرات:**
```php
// خط 85-90 را بررسی کنید
$limit = $request->get('per_page', $request->get('limit', 25)); // پشتیبانی از هر دو
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
```

---

## 2. مشکلات Response Format

### مشکل:
برخی endpointها ممکن است response format متفاوتی داشته باشند. باید همه endpointها از `BeautyApiResponse` trait استفاده کنند.

### فایل‌های React برای بررسی:
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetSalons.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetSalonDetails.js`

### تغییرات لازم:

#### 2.1. `BeautySalonController.php` - متد `search()`
**خط فعلی:** خط 126-130
**مشکل:** استفاده از `simpleListResponse` که ممکن است metadata را درست برنگرداند

**بررسی React:**
```javascript
// React انتظار دارد:
{
  message: "Data retrieved successfully",
  data: [...],
  total: 10
}
```

**کد فعلی:**
```php
return $this->simpleListResponse(
    $formattedSalons,
    'messages.data_retrieved_successfully',
    ['total' => $formattedSalons->count()]
);
```

**بررسی:** این کد درست است، اما مطمئن شوید که `total` در response وجود دارد.

#### 2.2. `BeautySalonController.php` - متد `show()`
**خط فعلی:** خط 160-163
**مشکل:** باید مطمئن شوید که response format درست است

**بررسی React:**
```javascript
// در useGetSalonDetails.js
const getSalonDetails = async (id) => {
  const { data } = await BeautyApi.getSalonDetails(id);
  return data; // React انتظار دارد data مستقیماً برگردد
};
```

**کد فعلی:**
```php
return $this->successResponse(
    'messages.data_retrieved_successfully',
    $this->formatSalonForApi($salon, true)
);
```

**بررسی:** این کد درست است. React از `response.data.data` استفاده می‌کند.

#### 2.3. `BeautyConsultationController.php` - متد `list()`
**خط فعلی:** خط 112-119
**مشکل:** استفاده از `response()->json()` به جای trait methods

**کد فعلی:**
```php
return response()->json([
    'message' => translate('messages.data_retrieved_successfully'),
    'data' => $formatted->values(),
    'total' => $consultations->total(),
    'per_page' => $consultations->perPage(),
    'current_page' => $consultations->currentPage(),
    'last_page' => $consultations->lastPage(),
], 200);
```

**تغییرات:**
```php
// استفاده از listResponse برای consistency
return $this->listResponse($consultations->setCollection($formatted->values()));
```

**یا اگر می‌خواهید format فعلی را نگه دارید:**
```php
// مطمئن شوید که format با React هماهنگ است
return response()->json([
    'message' => translate('messages.data_retrieved_successfully'),
    'data' => $formatted->values(),
    'total' => $consultations->total(),
    'per_page' => $consultations->perPage(),
    'current_page' => $consultations->currentPage(),
    'last_page' => $consultations->lastPage(),
], 200);
```

#### 2.4. `BeautyRetailController.php` - متد `listProducts()`
**خط فعلی:** خط 103-110
**مشکل:** همان مشکل `BeautyConsultationController`

**تغییرات:** همان تغییرات بالا

---

## 3. مشکلات Request Parameters

### مشکل:
برخی endpointها ممکن است parameter names متفاوتی داشته باشند.

### تغییرات لازم:

#### 3.1. `BeautyPackageController.php` - متد `index()`
**مشکل:** React از `per_page` استفاده می‌کند

**بررسی React:**
```javascript
// در beautyApi.js خط 85-90
if (params.per_page) queryParams.append("per_page", params.per_page);
```

**تغییرات:**
```php
// خط 44-45
$limit = $request->get('per_page', $request->get('limit', 25));
$offset = $request->get('offset', 0);
```

#### 3.2. `BeautyLoyaltyController.php` - متد `getCampaigns()`
**مشکل:** React از `per_page` استفاده می‌کند

**بررسی React:**
```javascript
// در beautyApi.js خط 128-132
getLoyaltyCampaigns: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.per_page) queryParams.append("per_page", params.per_page);
  return MainApi.get(`/api/v1/beautybooking/loyalty/campaigns?${queryParams.toString()}`);
}
```

**تغییرات:**
```php
// خط 85-86
$limit = $request->get('per_page', $request->get('limit', 25));
$offset = $request->get('offset', 0);
```

---

## 4. مشکلات Payment Method Values

### مشکل:
React و Laravel ممکن است payment method values متفاوتی داشته باشند.

### بررسی React:
```javascript
// در BookingForm.js خط 41
payment_method: "cash_payment",
```

### بررسی Laravel:
```php
// در BeautyBookingController.php خط 401
'payment_method' => 'required|in:wallet,digital_payment,cash_payment',
```

### بررسی Consultation:
```php
// در BeautyConsultationController.php خط 163
'payment_method' => 'required|in:online,wallet,cash_payment',
```

**مشکل:** Consultation از `online` استفاده می‌کند اما Booking از `digital_payment` استفاده می‌کند!

### تغییرات لازم:

#### 4.1. `BeautyConsultationController.php` - متد `book()`
**خط فعلی:** خط 163
**مشکل:** استفاده از `online` به جای `digital_payment`

**تغییرات:**
```php
// خط 163 را تغییر دهید:
'payment_method' => 'required|in:digital_payment,wallet,cash_payment',
```

**همچنین در متد `book()` خط 193:**
```php
// اگر از 'online' استفاده می‌کنید، آن را به 'digital_payment' تبدیل کنید
$bookingData['payment_method'] = $request->payment_method === 'online' 
    ? 'digital_payment' 
    : $request->payment_method;
```

#### 4.2. `BeautyRetailController.php` - متد `createOrder()`
**خط فعلی:** خط 156
**مشکل:** استفاده از `online` به جای `digital_payment`

**تغییرات:**
```php
// خط 156 را تغییر دهید:
'payment_method' => 'required|in:digital_payment,wallet,cash_payment',
```

**همچنین در متد `createOrder()` خط 205:**
```php
// اگر از 'online' استفاده می‌کنید، آن را به 'digital_payment' تبدیل کنید
case 'digital_payment':
    // یا اگر 'online' دارید:
    // case 'online':
    // case 'digital_payment':
```

---

## 5. مشکلات Date/Time Format

### مشکل:
React از format خاصی برای date/time استفاده می‌کند.

### بررسی React:
```javascript
// در BookingForm.js خط 68
date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
```

```javascript
// در BookingForm.js خط 84
booking_date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
```

### بررسی Laravel:
```php
// در BeautyBookingController.php خط 123
'date' => 'required|date|after_or_equal:today',
```

**بررسی:** Laravel باید `YYYY-MM-DD` را بپذیرد. این درست است.

### بررسی Time Format:
```javascript
// React از "H:i" استفاده می‌کند (مثلاً "10:00")
```

```php
// در BeautyBookingController.php خط 158
'booking_time' => 'required|date_format:H:i',
```

**بررسی:** این درست است.

### تغییرات لازم:

#### 5.1. `BeautyConsultationController.php` - متد `book()`
**خط فعلی:** خط 161
**بررسی:**
```php
'booking_time' => 'required|date_format:H:i',
```

**بررسی:** این درست است.

---

## 6. مشکلات File Upload (Review Attachments)

### مشکل:
React از FormData استفاده می‌کند و Laravel باید آن را درست handle کند.

### بررسی React:
```javascript
// در useSubmitReview.js خط 7-22
if (reviewData.attachments && reviewData.attachments.length > 0) {
  const formData = new FormData();
  formData.append("booking_id", reviewData.booking_id);
  formData.append("rating", reviewData.rating);
  if (reviewData.comment) {
    formData.append("comment", reviewData.comment);
  }
  reviewData.attachments.forEach((file) => {
    formData.append("attachments[]", file);
  });
  // Use MainApi directly to ensure proper FormData handling
  const { data } = await MainApi.post("/api/v1/beautybooking/reviews", formData, {
    headers: {
      "Content-Type": "multipart/form-data",
    },
  });
  return data;
}
```

### بررسی Laravel:
```php
// در BeautyReviewController.php خط 98-124
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

**بررسی:** این کد درست است. React از `attachments[]` استفاده می‌کند و Laravel آن را درست handle می‌کند.

### تغییرات لازم:

#### 6.1. `BeautyReviewController.php` - متد `store()`
**بررسی:** مطمئن شوید که:
1. Validation برای file types و size limits وجود دارد
2. Response شامل full URLs برای attachments است

**خط فعلی:** خط 158-160
```php
'attachments' => array_map(function ($path) {
    return asset('storage/' . $path);
}, $attachments),
```

**بررسی:** این درست است.

---

## 7. مشکلات Response Structure برای Specific Endpoints

### 7.1. `BeautyBookingController.php` - متد `store()`
**مشکل:** Response برای payment redirect

**بررسی React:**
```javascript
// در BookingForm.js خط 89-92
if (response?.data?.redirect_url) {
  window.location.href = response.data.redirect_url;
} else {
  router.push(`/beauty/bookings/${response?.data?.id || response?.data?.booking?.id}`);
}
```

**بررسی Laravel:**
```php
// خط 220-223
return $this->successResponse('redirect_to_payment', [
    'redirect_url' => $paymentResult,
    'booking' => $this->formatBookingForApi($booking),
]);
```

**مشکل:** React از `redirect_url` استفاده می‌کند اما Laravel ممکن است `redirect_url` یا `payment_link` برگرداند.

**تغییرات:**
```php
// خط 220-223 را بررسی کنید
// مطمئن شوید که همیشه 'redirect_url' استفاده می‌شود نه 'payment_link'
return $this->successResponse('redirect_to_payment', [
    'redirect_url' => $paymentResult, // نه 'payment_link'
    'booking' => $this->formatBookingForApi($booking),
]);
```

### 7.2. `BeautyBookingController.php` - متد `payment()`
**خط فعلی:** خط 427-430
**مشکل:** همان مشکل بالا

**تغییرات:**
```php
// خط 427-430 را بررسی کنید
return $this->successResponse('redirect_to_payment', [
    'redirect_url' => $paymentResult, // نه 'payment_link'
    'booking' => $this->formatBookingForApi($booking),
]);
```

---

## 8. مشکلات Service Suggestions Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 172-176
getServiceSuggestions: (serviceId, salonId) => {
  const queryParams = new URLSearchParams();
  if (salonId) queryParams.append("salon_id", salonId);
  return MainApi.get(`/api/v1/beautybooking/services/${serviceId}/suggestions?${queryParams.toString()}`);
}
```

### بررسی Laravel:
```php
// در api.php خط 50-52
Route::get('services/{id}/suggestions', [BeautyBookingController::class, 'getServiceSuggestions'])
    ->middleware('throttle:60,1')
    ->name('services.suggestions');
```

**بررسی:** Route درست است.

### بررسی Controller:
```php
// در BeautyBookingController.php خط 44-82
public function getServiceSuggestions(Request $request, int $id): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'salon_id' => 'nullable|integer|exists:beauty_salons,id',
    ]);
    // ...
}
```

**بررسی:** این درست است.

---

## 9. مشکلات Package Status Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 103-105
getPackageStatus: (id) => {
  return MainApi.get(`/api/v1/beautybooking/packages/${id}/status`);
}
```

### بررسی Laravel:
```php
// در api.php خط 100-102
Route::get('{id}/status', [BeautyBookingController::class, 'getPackageStatus'])
    ->middleware('throttle:60,1')
    ->name('status');
```

**مشکل:** Route در group `packages` است، پس URL درست است: `/api/v1/beautybooking/packages/{id}/status`

**بررسی:** این درست است.

---

## 10. مشکلات Gift Card Endpoints

### 10.1. Gift Card List Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 116-121
getGiftCards: (params) => {
  const queryParams = new URLSearchParams();
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/gift-card/list?${queryParams.toString()}`);
}
```

### بررسی Laravel:
```php
// در api.php خط 151-153
Route::get('list', [BeautyGiftCardController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('list');
```

**بررسی:** Route در group `gift-card` است، پس URL درست است: `/api/v1/beautybooking/gift-card/list`

### 10.2. Gift Card Purchase Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 108-110
purchaseGiftCard: (giftCardData) => {
  return MainApi.post("/api/v1/beautybooking/gift-card/purchase", giftCardData);
}
```

### بررسی Laravel:
```php
// در api.php خط 145-147
Route::post('purchase', [BeautyGiftCardController::class, 'purchase'])
    ->middleware('throttle:5,1')
    ->name('purchase');
```

**بررسی:** این درست است.

### 10.3. Gift Card Redeem Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 112-114
redeemGiftCard: (code) => {
  return MainApi.post("/api/v1/beautybooking/gift-card/redeem", { code });
}
```

### بررسی Laravel:
```php
// در api.php خط 148-150
Route::post('redeem', [BeautyGiftCardController::class, 'redeem'])
    ->middleware('throttle:5,1')
    ->name('redeem');
```

**بررسی:** این درست است.

---

## 11. مشکلات Loyalty Endpoints

### 11.1. Loyalty Points Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 124-126
getLoyaltyPoints: () => {
  return MainApi.get("/api/v1/beautybooking/loyalty/points");
}
```

### بررسی Laravel:
```php
// در api.php خط 108-110
Route::get('points', [BeautyLoyaltyController::class, 'getPoints'])
    ->middleware('throttle:60,1')
    ->name('points');
```

**بررسی:** این درست است.

### 11.2. Loyalty Redeem Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 135-137
redeemLoyaltyPoints: (redeemData) => {
  return MainApi.post("/api/v1/beautybooking/loyalty/redeem", redeemData);
}
```

### بررسی Laravel:
```php
// در api.php خط 116-118
Route::post('redeem', [BeautyLoyaltyController::class, 'redeem'])
    ->middleware('throttle:10,1')
    ->name('redeem');
```

**بررسی:** این درست است.

**بررسی Request Format:**
```javascript
// React ارسال می‌کند:
{
  campaign_id: 1,
  points: 100
}
```

```php
// Laravel انتظار دارد:
'campaign_id' => 'required|integer|exists:beauty_loyalty_campaigns,id',
'points' => 'required|integer|min:1',
```

**بررسی:** این درست است.

---

## 12. مشکلات Consultation Endpoints

### 12.1. Consultation List Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 140-147
getConsultations: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.consultation_type) queryParams.append("consultation_type", params.consultation_type);
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/consultations/list?${queryParams.toString()}`);
}
```

### بررسی Laravel:
```php
// در api.php خط 159-161
Route::get('list', [BeautyConsultationController::class, 'list'])
    ->middleware('throttle:60,1')
    ->name('list');
```

**بررسی:** این درست است.

### 12.2. Consultation Book Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 149-151
bookConsultation: (consultationData) => {
  return MainApi.post("/api/v1/beautybooking/consultations/book", consultationData);
}
```

### بررسی Laravel:
```php
// در api.php خط 164-166
Route::post('book', [BeautyConsultationController::class, 'book'])
    ->middleware('throttle:10,1')
    ->name('book');
```

**بررسی:** این درست است.

### 12.3. Consultation Check Availability Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 153-155
checkConsultationAvailability: (availabilityData) => {
  return MainApi.post("/api/v1/beautybooking/consultations/check-availability", availabilityData);
}
```

### بررسی Laravel:
```php
// در api.php خط 167-169
Route::post('check-availability', [BeautyConsultationController::class, 'checkAvailability'])
    ->middleware('throttle:30,1')
    ->name('check-availability');
```

**بررسی:** این درست است.

---

## 13. مشکلات Retail Endpoints

### 13.1. Retail Products List Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 158-165
getRetailProducts: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.category_id) queryParams.append("category_id", params.category_id);
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/retail/products?${queryParams.toString()}`);
}
```

### بررسی Laravel:
```php
// در api.php خط 175-177
Route::get('products', [BeautyRetailController::class, 'listProducts'])
    ->middleware('throttle:60,1')
    ->name('products.list');
```

**بررسی:** این درست است.

**مشکل:** React از `category_id` استفاده می‌کند اما Laravel از `category` (string) استفاده می‌کند!

### تغییرات لازم:

#### 13.1. `BeautyRetailController.php` - متد `listProducts()`
**خط فعلی:** خط 63
```php
'category' => 'nullable|string|max:100',
```

**تغییرات:**
```php
// پشتیبانی از هر دو category و category_id
'category' => 'nullable|string|max:100',
'category_id' => 'nullable|integer',
```

**و در query:**
```php
// خط 83-85
if ($request->filled('category')) {
    $query->where('category', $request->category);
}
if ($request->filled('category_id')) {
    $query->where('category_id', $request->category_id);
}
```

### 13.2. Retail Order Create Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 167-169
createRetailOrder: (orderData) => {
  return MainApi.post("/api/v1/beautybooking/retail/orders", orderData);
}
```

### بررسی Laravel:
```php
// در api.php خط 180-182
Route::post('orders', [BeautyRetailController::class, 'createOrder'])
    ->middleware('throttle:10,1')
    ->name('orders.create');
```

**بررسی:** این درست است.

---

## 14. مشکلات Review Endpoints

### 14.1. Submit Review Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 184-188
submitReview: (reviewData) => {
  // MainApi.post will handle FormData automatically if reviewData is FormData
  // Otherwise it will send as JSON
  return MainApi.post("/api/v1/beautybooking/reviews", reviewData);
}
```

### بررسی Laravel:
```php
// در api.php خط 132-134
Route::post('/', [BeautyReviewController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('store');
```

**بررسی:** این درست است.

### 14.2. Get Reviews Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 190-195
getReviews: (params) => {
  const queryParams = new URLSearchParams();
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/reviews?${queryParams.toString()}`);
}
```

### بررسی Laravel:
```php
// در api.php خط 135-137
Route::get('/', [BeautyReviewController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('index');
```

**بررسی:** این درست است.

### 14.3. Get Salon Reviews Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 44-46
getSalonReviews: (salonId) => {
  return MainApi.get(`/api/v1/beautybooking/reviews/${salonId}`);
}
```

### بررسی Laravel:
```php
// در api.php خط 42
Route::get('reviews/{salon_id}', [BeautyReviewController::class, 'getSalonReviews'])->name('reviews.salon');
```

**بررسی:** این درست است.

---

## 15. مشکلات Category Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 179-181
getCategories: () => {
  return MainApi.get("/api/v1/beautybooking/salons/category-list");
}
```

### بررسی Laravel:
```php
// در api.php خط 34
Route::get('salons/category-list', [BeautyCategoryController::class, 'list'])->name('salons.category-list');
```

**بررسی:** این درست است.

---

## 16. مشکلات Booking Conversation Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 76-78
getBookingConversation: (id) => {
  return MainApi.get(`/api/v1/beautybooking/bookings/${id}/conversation`);
}
```

### بررسی Laravel:
```php
// در api.php خط 76-78
Route::get('{id}/conversation', [BeautyBookingController::class, 'getConversation'])
    ->middleware('throttle:60,1')
    ->name('conversation');
```

**بررسی:** این درست است.

---

## 17. مشکلات Process Payment Endpoint

### بررسی React:
```javascript
// در beautyApi.js خط 80-82
processPayment: (paymentData) => {
  return MainApi.post("/api/v1/beautybooking/payment", paymentData);
}
```

### بررسی Laravel:
```php
// در api.php خط 123-125
Route::post('payment', [BeautyBookingController::class, 'payment'])
    ->middleware('throttle:5,1')
    ->name('payment');
```

**بررسی:** این درست است.

---

## 18. خلاصه تغییرات ضروری

### تغییرات با اولویت بالا:

1. **Pagination Format:** تمام endpointها باید offset را به page تبدیل کنند
2. **Payment Method Values:** Consultation و Retail باید از `digital_payment` استفاده کنند نه `online`
3. **Response Format:** Consultation و Retail باید از trait methods استفاده کنند
4. **Retail Category Parameter:** باید از `category_id` پشتیبانی کند

### تغییرات با اولویت متوسط:

1. **Package Pagination:** باید از `per_page` پشتیبانی کند
2. **Loyalty Pagination:** باید از `per_page` پشتیبانی کند
3. **Payment Redirect:** باید همیشه `redirect_url` استفاده شود

### تغییرات با اولویت پایین:

1. **Documentation:** بهبود docblocks
2. **Error Messages:** بهبود error messages
3. **Validation:** بهبود validation rules

---

## 19. چک‌لیست نهایی

قبل از commit کردن تغییرات:

- [ ] تمام pagination endpoints از offset به page تبدیل می‌کنند
- [ ] تمام payment methods از `digital_payment` استفاده می‌کنند
- [ ] تمام responses از `BeautyApiResponse` trait استفاده می‌کنند
- [ ] تمام file uploads درست handle می‌شوند
- [ ] تمام date/time formats درست هستند
- [ ] تمام parameter names با React هماهنگ هستند
- [ ] تمام response structures با React هماهنگ هستند
- [ ] تمام error formats با React هماهنگ هستند

---

## 20. فایل‌های React برای مرجع

برای بررسی دقیق‌تر، این فایل‌ها را در React بررسی کنید:

1. `/home/sepehr/Projects/6ammart-react/src/api-manage/another-formated-api/beautyApi.js` - تمام API calls
2. `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/` - تمام hooks
3. `/home/sepehr/Projects/6ammart-react/src/components/home/module-wise-components/beauty/components/` - تمام components

---

**نکته مهم:** این سند باید به‌روزرسانی شود هر زمان که تغییراتی در React یا Laravel ایجاد می‌شود.






















