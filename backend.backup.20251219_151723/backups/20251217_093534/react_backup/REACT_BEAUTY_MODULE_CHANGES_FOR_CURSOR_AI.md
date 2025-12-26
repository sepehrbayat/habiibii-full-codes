# تغییرات لازم در React - ماژول زیبایی (برای Cursor AI)

**مسیر پروژه:** `/home/sepehr/Projects/6ammart-react/`

## 📋 خلاصه اجرایی

این سند شامل تمام تغییرات لازم در پروژه React برای هماهنگی کامل با Laravel backend است. تمام تغییرات باید در مسیر `src/api-manage/` و `src/components/home/module-wise-components/beauty/` انجام شود.

---

## 🔍 روش بررسی

قبل از اعمال هر تغییر:
1. فایل‌های Laravel مربوطه را در `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/` بررسی کنید
2. API endpoints و expected request/response format را در Laravel چک کنید
3. تغییرات را در React اعمال کنید
4. تست کنید که request/response format با Laravel هماهنگ است

---

## 1. مشکلات Pagination Parameters

### مشکل:
React از `offset` و `limit` استفاده می‌کند، اما باید مطمئن شویم که با Laravel هماهنگ است.

### فایل‌های Laravel برای بررسی:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php` - متد `index()`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php` - متد `index()`

### تغییرات لازم:

#### 1.1. `beautyApi.js` - متد `getPackages()`
**خط فعلی:** خط 85-91
**مشکل:** استفاده از `per_page` به جای `limit`

**کد فعلی:**
```javascript
getPackages: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.service_id) queryParams.append("service_id", params.service_id);
  if (params.per_page) queryParams.append("per_page", params.per_page);
  return MainApi.get(`/api/v1/beautybooking/packages?${queryParams.toString()}`);
}
```

**بررسی Laravel:**
```php
// در BeautyPackageController.php خط 44-45
$limit = $request->get('per_page', $request->get('limit', 25));
$offset = $request->get('offset', 0);
```

**بررسی:** Laravel از `per_page` پشتیبانی می‌کند، پس این درست است. اما باید مطمئن شویم که `offset` هم ارسال می‌شود.

**تغییرات:**
```javascript
getPackages: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.service_id) queryParams.append("service_id", params.service_id);
  if (params.per_page) queryParams.append("per_page", params.per_page);
  if (params.limit) queryParams.append("limit", params.limit); // پشتیبانی از هر دو
  if (params.offset) queryParams.append("offset", params.offset); // اضافه کردن offset
  return MainApi.get(`/api/v1/beautybooking/packages?${queryParams.toString()}`);
}
```

#### 1.2. `beautyApi.js` - متد `getLoyaltyCampaigns()`
**خط فعلی:** خط 128-133
**مشکل:** استفاده از `per_page` اما عدم ارسال `offset`

**کد فعلی:**
```javascript
getLoyaltyCampaigns: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.per_page) queryParams.append("per_page", params.per_page);
  return MainApi.get(`/api/v1/beautybooking/loyalty/campaigns?${queryParams.toString()}`);
}
```

**تغییرات:**
```javascript
getLoyaltyCampaigns: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.per_page) queryParams.append("per_page", params.per_page);
  if (params.limit) queryParams.append("limit", params.limit); // پشتیبانی از هر دو
  if (params.offset) queryParams.append("offset", params.offset); // اضافه کردن offset
  return MainApi.get(`/api/v1/beautybooking/loyalty/campaigns?${queryParams.toString()}`);
}
```

#### 1.3. `beautyApi.js` - متد `getConsultations()`
**خط فعلی:** خط 140-147
**بررسی:** `offset` و `limit` ارسال می‌شوند، این درست است.

#### 1.4. `beautyApi.js` - متد `getRetailProducts()`
**خط فعلی:** خط 158-165
**بررسی:** `offset` و `limit` ارسال می‌شوند، این درست است.

---

## 2. مشکلات Payment Method Values

### مشکل:
React و Laravel باید از همان payment method values استفاده کنند.

### بررسی Laravel:
```php
// در BeautyBookingController.php خط 401
'payment_method' => 'required|in:wallet,digital_payment,cash_payment',
```

```php
// در BeautyConsultationController.php خط 163
'payment_method' => 'required|in:online,wallet,cash_payment', // ❌ مشکل: 'online' به جای 'digital_payment'
```

```php
// در BeautyRetailController.php خط 156
'payment_method' => 'required|in:online,wallet,cash_payment', // ❌ مشکل: 'online' به جای 'digital_payment'
```

### تغییرات لازم:

#### 2.1. `BookingForm.js` - استفاده از payment_method
**خط فعلی:** خط 41
**کد فعلی:**
```javascript
payment_method: "cash_payment",
```

**بررسی:** این درست است. React از `cash_payment`, `wallet`, `digital_payment` استفاده می‌کند.

**نکته:** اگر Laravel از `online` استفاده می‌کند، باید در React هم تبدیل کنیم یا Laravel را تغییر دهیم. بهتر است Laravel را تغییر دهیم.

#### 2.2. Consultation Booking Components
**بررسی:** اگر کامپوننت‌های consultation از `online` استفاده می‌کنند، باید به `digital_payment` تغییر دهند.

**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/ConsultationBooking.js`

**تغییرات:**
```javascript
// اگر از 'online' استفاده می‌کنید، به 'digital_payment' تغییر دهید
payment_method: "digital_payment", // نه 'online'
```

#### 2.3. Retail Order Components
**بررسی:** اگر کامپوننت‌های retail از `online` استفاده می‌کنند، باید به `digital_payment` تغییر دهند.

**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/RetailCheckout.js`

**تغییرات:**
```javascript
// اگر از 'online' استفاده می‌کنید، به 'digital_payment' تغییر دهید
payment_method: "digital_payment", // نه 'online'
```

---

## 3. مشکلات Response Structure Handling

### مشکل:
React باید response structure را درست handle کند.

### بررسی Laravel Response Format:
```php
// از BeautyApiResponse trait
{
  "message": "Data retrieved successfully",
  "data": [...]
}
```

### تغییرات لازم:

#### 3.1. تمام Hooks - Response Handling
**مشکل:** برخی hooks ممکن است response structure را درست handle نکنند.

**بررسی:**
```javascript
// در useGetSalons.js خط 5-8
const getSalons = async (params) => {
  const { data } = await BeautyApi.searchSalons(params);
  return data; // این 'data' از axios response است که شامل { message, data } است
};
```

**بررسی:** اگر Laravel `{ message, data }` برمی‌گرداند، و axios آن را در `response.data` قرار می‌دهد، پس `data` شامل `{ message, data }` است.

**مشکل احتمالی:** اگر hook از `data.data` استفاده می‌کند، باید بررسی شود.

**بررسی در Components:**
```javascript
// در BookingForm.js خط 45
const salon = salonData?.data || salonData;
```

**بررسی:** این کد درست است. اگر `salonData` شامل `{ message, data }` باشد، `salonData.data` استفاده می‌شود.

**تغییرات:** نیازی به تغییر نیست، اما باید مطمئن شویم که همه components از این pattern استفاده می‌کنند.

---

## 4. مشکلات Retail Category Parameter

### مشکل:
React از `category_id` استفاده می‌کند اما Laravel از `category` (string) استفاده می‌کند.

### بررسی React:
```javascript
// در beautyApi.js خط 161
if (params.category_id) queryParams.append("category_id", params.category_id);
```

### بررسی Laravel:
```php
// در BeautyRetailController.php خط 63
'category' => 'nullable|string|max:100',
```

**مشکل:** Laravel از `category` (string) استفاده می‌کند اما React از `category_id` (integer) استفاده می‌کند.

### تغییرات لازم:

#### 4.1. `beautyApi.js` - متد `getRetailProducts()`
**خط فعلی:** خط 158-165
**تغییرات:**
```javascript
getRetailProducts: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.category_id) queryParams.append("category_id", params.category_id);
  if (params.category) queryParams.append("category", params.category); // پشتیبانی از هر دو
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/retail/products?${queryParams.toString()}`);
}
```

**نکته:** بهتر است Laravel را تغییر دهیم تا از `category_id` پشتیبانی کند، اما در React هم می‌توانیم از هر دو پشتیبانی کنیم.

---

## 5. مشکلات Date/Time Format

### مشکل:
React باید date/time را در format درست ارسال کند.

### بررسی Laravel:
```php
// در BeautyBookingController.php خط 123
'date' => 'required|date|after_or_equal:today',
```

```php
// در BeautyBookingController.php خط 158
'booking_time' => 'required|date_format:H:i',
```

### بررسی React:
```javascript
// در BookingForm.js خط 68
date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
```

```javascript
// در BookingForm.js خط 84
booking_date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
```

**بررسی:** React از `YYYY-MM-DD` استفاده می‌کند، این درست است.

**بررسی Time Format:**
```javascript
// باید "H:i" format باشد (مثلاً "10:00")
booking_time: "10:00", // نه "10:00:00"
```

**بررسی:** مطمئن شوید که time format `H:i` است نه `H:i:s`.

---

## 6. مشکلات File Upload (Review Attachments)

### مشکل:
React باید فایل‌ها را درست ارسال کند.

### بررسی Laravel:
```php
// در BeautyReviewController.php خط 98-124
if ($request->hasFile('attachments')) {
    $files = $request->file('attachments');
    if (!is_array($files)) {
        $files = [$files];
    }
    // ...
}
```

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
    formData.append("attachments[]", file); // ✅ درست: استفاده از attachments[]
  });
  // ...
}
```

**بررسی:** این کد درست است. Laravel از `attachments[]` پشتیبانی می‌کند.

### تغییرات لازم:

#### 6.1. `useSubmitReview.js` - بررسی File Types
**بررسی:** مطمئن شوید که فقط image files ارسال می‌شوند.

**کد فعلی در ReviewForm.js:**
```javascript
// خط 27
const imageFiles = files.filter((file) => file.type.startsWith("image/"));
```

**بررسی:** این درست است.

---

## 7. مشکلات Response Handling برای Payment Redirect

### مشکل:
React باید payment redirect response را درست handle کند.

### بررسی Laravel:
```php
// در BeautyBookingController.php خط 220-223
return $this->successResponse('redirect_to_payment', [
    'redirect_url' => $paymentResult,
    'booking' => $this->formatBookingForApi($booking),
]);
```

### بررسی React:
```javascript
// در BookingForm.js خط 89-92
if (response?.data?.redirect_url) {
  window.location.href = response.data.redirect_url;
} else {
  router.push(`/beauty/bookings/${response?.data?.id || response?.data?.booking?.id}`);
}
```

**بررسی:** این کد درست است. React از `redirect_url` استفاده می‌کند.

**نکته:** مطمئن شوید که Laravel همیشه `redirect_url` استفاده می‌کند نه `payment_link`.

---

## 8. مشکلات Booking Response Structure

### مشکل:
React باید booking response structure را درست handle کند.

### بررسی Laravel:
```php
// در BeautyBookingController.php - متد formatBookingForApi()
$data = [
    'id' => $booking->id,
    'booking_reference' => $booking->booking_reference ?? '',
    'booking_date' => $bookingDate,
    'booking_time' => $bookingTime ?? '',
    // ...
];
```

### بررسی React:
```javascript
// در BookingForm.js خط 92
router.push(`/beauty/bookings/${response?.data?.id || response?.data?.booking?.id}`);
```

**بررسی:** React از `response.data.id` یا `response.data.booking.id` استفاده می‌کند.

**مشکل احتمالی:** اگر Laravel مستقیماً booking object را برمی‌گرداند، باید `response.data.id` استفاده شود.

**بررسی:** در Laravel، `formatBookingForApi()` یک array برمی‌گرداند، پس `response.data.id` درست است.

---

## 9. مشکلات Package Purchase Response

### مشکل:
React باید package purchase response را درست handle کند.

### بررسی Laravel:
```php
// در BeautyPackageController.php خط 300-312
return $this->successResponse(
    'messages.package_purchased_successfully',
    [
        'package_id' => $package->id,
        'package_name' => $package->name,
        'sessions_count' => $package->sessions_count,
        'total_price' => $package->total_price,
        'validity_days' => $package->validity_days,
        'remaining_sessions' => $package->sessions_count,
        'payment_method' => $request->payment_method,
        'payment_status' => $request->payment_method === 'digital_payment' ? 'pending' : 'paid',
    ]
);
```

### بررسی React:
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/PackageDetails.js`
- `pages/beauty/packages/[id]/index.js`

**بررسی:** باید مطمئن شویم که components از این response structure استفاده می‌کنند.

---

## 10. مشکلات Gift Card Response

### مشکل:
React باید gift card response را درست handle کند.

### بررسی Laravel:
```php
// در BeautyGiftCardController.php - متد redeem() خط 125-129
return $this->successResponse('gift_card_redeemed_successfully', [
    'amount' => $giftCard->amount,
    'salon_id' => $giftCard->salon_id,
    'wallet_balance' => $request->user()->fresh()->wallet_balance,
]);
```

### بررسی React:
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/GiftCardList.js`
- `pages/beauty/gift-cards/index.js`

**بررسی:** باید مطمئن شویم که components از این response structure استفاده می‌کنند.

---

## 11. مشکلات Loyalty Points Response

### مشکل:
React باید loyalty points response را درست handle کند.

### بررسی Laravel:
```php
// در BeautyLoyaltyController.php - متد getPoints() خط 53-60
return $this->successResponse(
    'messages.data_retrieved_successfully',
    [
        'total_points' => $totalPoints,
        'used_points' => $usedPoints,
        'available_points' => max(0, $availablePoints),
    ]
);
```

### بررسی React:
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/LoyaltyPoints.js`
- `pages/beauty/loyalty/index.js`

**بررسی:** باید مطمئن شویم که components از این response structure استفاده می‌کنند.

---

## 12. مشکلات Loyalty Redeem Response

### مشکل:
React باید loyalty redeem response را درست handle کند.

### بررسی Laravel:
```php
// در BeautyLoyaltyController.php - متد redeem() خط 382-391
return $this->successResponse(
    'messages.points_redeemed_successfully',
    [
        'campaign_id' => $campaign->id,
        'campaign_name' => $campaign->name,
        'points_redeemed' => $request->points,
        'remaining_points' => $loyaltyService->getTotalPoints($user->id, $campaign->salon_id),
        'reward' => $reward,
    ]
);
```

### بررسی React:
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/LoyaltyPoints.js`

**بررسی:** باید مطمئن شویم که components از `reward` object استفاده می‌کنند.

---

## 13. مشکلات Consultation Response

### مشکل:
React باید consultation response را درست handle کند.

### بررسی Laravel:
```php
// در BeautyConsultationController.php - متد list() خط 93-108
$formatted = $consultations->getCollection()->map(function ($consultation) {
    return [
        'id' => $consultation->id,
        'name' => $consultation->name,
        'description' => $consultation->description,
        'duration_minutes' => $consultation->duration_minutes,
        'price' => $consultation->price,
        'image' => $consultation->image ? asset('storage/' . $consultation->image) : null,
        'service_type' => $consultation->service_type,
        'consultation_credit_percentage' => $consultation->consultation_credit_percentage,
        'category' => $consultation->category ? [
            'id' => $consultation->category->id,
            'name' => $consultation->category->name,
        ] : null,
    ];
});
```

### بررسی React:
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/ConsultationList.js`
- `src/components/home/module-wise-components/beauty/components/ConsultationCard.js`

**بررسی:** باید مطمئن شویم که components از این response structure استفاده می‌کنند.

---

## 14. مشکلات Retail Products Response

### مشکل:
React باید retail products response را درست handle کند.

### بررسی Laravel:
```php
// در BeautyRetailController.php - متد listProducts() خط 89-99
$formatted = $products->getCollection()->map(function ($product) {
    return [
        'id' => $product->id,
        'name' => $product->name,
        'description' => $product->description,
        'price' => $product->price,
        'image' => $product->image ? asset('storage/' . $product->image) : null,
        'category' => $product->category,
        'stock_quantity' => $product->stock_quantity,
    ];
});
```

### بررسی React:
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/RetailProducts.js`
- `src/components/home/module-wise-components/beauty/components/RetailProductCard.js`

**بررسی:** باید مطمئن شویم که components از این response structure استفاده می‌کنند.

---

## 15. مشکلات Review Response

### مشکل:
React باید review response را درست handle کند.

### بررسی Laravel:
```php
// در BeautyReviewController.php - متد store() خط 152-162
$reviewData = [
    'id' => $review->id,
    'booking_id' => $review->booking_id,
    'rating' => $review->rating,
    'comment' => $review->comment,
    'status' => $review->status,
    'attachments' => array_map(function ($path) {
        return asset('storage/' . $path);
    }, $attachments),
    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
];
```

### بررسی React:
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/ReviewForm.js`
- `src/components/home/module-wise-components/beauty/components/ReviewCard.js`

**بررسی:** باید مطمئن شویم که components از `attachments` array استفاده می‌کنند.

---

## 16. مشکلات Error Handling

### مشکل:
React باید error responses را درست handle کند.

### بررسی Laravel:
```php
// از BeautyApiResponse trait
protected function errorResponse(array $errors, int $status = 403): JsonResponse
{
    return response()->json([
        'errors' => $errors,
    ], $status);
}
```

### بررسی React:
```javascript
// در BookingForm.js خط 95-97
onError: (error) => {
  toast.error(error?.response?.data?.message || "Failed to create booking");
}
```

**مشکل:** React از `error.response.data.message` استفاده می‌کند اما Laravel `errors` array برمی‌گرداند.

### تغییرات لازم:

#### 16.1. تمام Components - Error Handling
**تغییرات:**
```javascript
onError: (error) => {
  const errorMessage = error?.response?.data?.errors?.[0]?.message 
    || error?.response?.data?.message 
    || "Failed to perform action";
  toast.error(errorMessage);
}
```

**یا بهتر است یک helper function ایجاد کنیم:**
```javascript
// در helper-functions/beautyErrorHandler.js
export const getBeautyErrorMessage = (error) => {
  if (error?.response?.data?.errors?.length > 0) {
    return error.response.data.errors[0].message;
  }
  if (error?.response?.data?.message) {
    return error.response.data.message;
  }
  return "An error occurred";
};
```

---

## 17. مشکلات Salon Details Response

### مشکل:
React باید salon details response را درست handle کند.

### بررسی Laravel:
```php
// در BeautySalonController.php - متد formatSalonForApi() خط 376-440
$data = [
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
    'store' => [
        'id' => $salon->store->id ?? null,
        'name' => $salon->store->name ?? '',
        // ...
    ],
    // اگر includeDetails = true:
    'services' => [...],
    'staff' => [...],
    'working_hours' => $salon->working_hours,
    'reviews' => [...],
];
```

### بررسی React:
```javascript
// در BookingForm.js خط 45-47
const salon = salonData?.data || salonData;
const services = salon?.services || [];
const staff = salon?.staff || [];
```

**بررسی:** این کد درست است.

---

## 18. مشکلات Booking List Response

### مشکل:
React باید booking list response را درست handle کند.

### بررسی Laravel:
```php
// در BeautyBookingController.php - متد index() خط 282
return $this->listResponse($bookings);
```

**Response format:**
```json
{
  "message": "Data retrieved successfully",
  "data": [...],
  "total": 10,
  "per_page": 25,
  "current_page": 1,
  "last_page": 1
}
```

### بررسی React:
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/BookingList.js`
- `pages/beauty/bookings/index.js`

**بررسی:** باید مطمئن شویم که components از pagination metadata استفاده می‌کنند.

---

## 19. مشکلات Category Response

### مشکل:
React باید category response را درست handle کند.

### بررسی Laravel:
```php
// در BeautyCategoryController.php - متد list() خط 65-78
$formatted = $categories->map(function ($category) {
    return [
        'id' => $category->id,
        'name' => $category->name,
        'image' => $category->image_full_url ?? null,
        'children' => $category->children->map(function ($child) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'image' => $child->image_full_url ?? null,
            ];
        }),
    ];
});
```

### بررسی React:
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/SalonFilters.js`

**بررسی:** باید مطمئن شویم که components از nested `children` structure استفاده می‌کنند.

---

## 20. خلاصه تغییرات ضروری

### تغییرات با اولویت بالا:

1. **Error Handling:** تمام components باید error responses را درست handle کنند
2. **Payment Method Values:** Consultation و Retail باید از `digital_payment` استفاده کنند
3. **Pagination Parameters:** Package و Loyalty باید `offset` ارسال کنند
4. **Retail Category:** باید از `category_id` پشتیبانی کند

### تغییرات با اولویت متوسط:

1. **Response Structure:** بررسی consistency در response handling
2. **Date/Time Format:** مطمئن شوید که time format `H:i` است
3. **File Upload:** بررسی file type validation

### تغییرات با اولویت پایین:

1. **Documentation:** بهبود comments
2. **Type Safety:** اضافه کردن PropTypes یا TypeScript
3. **Error Messages:** بهبود user-friendly error messages

---

## 21. چک‌لیست نهایی

قبل از commit کردن تغییرات:

- [ ] تمام error handling از `errors` array استفاده می‌کند
- [ ] تمام payment methods از `digital_payment` استفاده می‌کنند
- [ ] تمام pagination endpoints `offset` ارسال می‌کنند
- [ ] تمام date formats `YYYY-MM-DD` هستند
- [ ] تمام time formats `H:i` هستند
- [ ] تمام file uploads از FormData استفاده می‌کنند
- [ ] تمام response structures درست handle می‌شوند
- [ ] تمام components از pagination metadata استفاده می‌کنند

---

## 22. فایل‌های Laravel برای مرجع

برای بررسی دقیق‌تر، این فایل‌ها را در Laravel بررسی کنید:

1. `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Routes/api/v1/customer/api.php` - تمام routes
2. `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/` - تمام controllers
3. `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Traits/BeautyApiResponse.php` - response format

---

**نکته مهم:** این سند باید به‌روزرسانی شود هر زمان که تغییراتی در React یا Laravel ایجاد می‌شود.






















