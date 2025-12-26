# تغییرات کامل لازم در React - ماژول زیبایی (برای Cursor AI)

**مسیر پروژه:** `/home/sepehr/Projects/6ammart-react/`

## 📋 خلاصه اجرایی

این سند شامل تمام تغییرات لازم در پروژه React برای هماهنگی کامل با Laravel backend است. تمام تغییرات باید در مسیر `src/api-manage/` و `src/components/home/module-wise-components/beauty/` و `pages/beauty/` انجام شود. این سند به طور کامل و با جزئیات تمام ناهماهنگی‌ها، مشکلات و فیچرهای ناقص را پوشش می‌دهد.

---

## 🔍 روش بررسی

قبل از اعمال هر تغییر:
1. فایل‌های Laravel مربوطه را در `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/` بررسی کنید
2. API endpoints و expected request/response format را در Laravel چک کنید
3. تغییرات را در React اعمال کنید
4. تست کنید که request/response format با Laravel هماهنگ است

---

## 1. مشکلات Pagination Parameters

### مشکل کلی:
React از `offset` و `limit` استفاده می‌کند، اما باید مطمئن شویم که با Laravel هماهنگ است.

### فایل‌های Laravel برای بررسی:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php` - متد `index()`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php` - متد `index()`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php` - متد `index()`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php` - متد `index()`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php` - متد `getCampaigns()`

### تغییرات لازم:

#### 1.1. Hook `useGetBookings`
**فایل:** `src/api-manage/hooks/react-query/beauty/useGetBookings.js`

**وضعیت فعلی:** ✅ از `offset` و `limit` استفاده می‌کند

**نیاز به بررسی:**
- مطمئن شوید که response normalization به درستی کار می‌کند
- بررسی کنید که pagination metadata به درستی extract می‌شود

**کد فعلی:**
```javascript
const normalizeBookings = (response, params) => {
  const payload = response || {};
  const rawItems = payload.data ?? payload;
  const items = Array.isArray(rawItems) ? rawItems : rawItems?.data ?? [];

  const perPage =
    payload.per_page ??
    payload.pagination?.per_page ??
    params?.limit ??
    params?.per_page ??
    (items.length || 0);

  const total =
    payload.total ??
    payload.pagination?.total ??
    (typeof payload.count === "number" ? payload.count : items.length);

  const currentPage =
    payload.current_page ??
    payload.pagination?.current_page ??
    (params?.offset && perPage
      ? Math.floor(params.offset / perPage) + 1
      : 1);

  const lastPage =
    payload.last_page ??
    payload.pagination?.last_page ??
    (perPage ? Math.max(1, Math.ceil((total || items.length) / perPage)) : 1);

  return {
    data: items,
    total,
    per_page: perPage,
    current_page: currentPage,
    last_page: lastPage,
  };
};
```

**تغییرات لازم:**
- این normalization pattern باید در همه hooks استفاده شود
- مطمئن شوید که با Laravel response format هماهنگ است

#### 1.2. Hook `useGetPackages`
**فایل:** `src/api-manage/hooks/react-query/beauty/useGetPackages.js`

**نیاز به بررسی:**
- مطمئن شوید که از normalization pattern مشابه استفاده می‌کند
- بررسی کنید که pagination metadata به درستی extract می‌شود

#### 1.3. Hook `useGetLoyaltyCampaigns`
**فایل:** `src/api-manage/hooks/react-query/beauty/useGetLoyaltyCampaigns.js`

**نیاز به بررسی:**
- مطمئن شوید که از normalization pattern مشابه استفاده می‌کند
- بررسی کنید که pagination metadata به درستی extract می‌شود

#### 1.4. Hook `useGetConsultations`
**فایل:** `src/api-manage/hooks/react-query/beauty/useGetConsultations.js`

**نیاز به بررسی:**
- مطمئن شوید که از normalization pattern مشابه استفاده می‌کند
- بررسی کنید که pagination metadata به درستی extract می‌شود

#### 1.5. Hook `useGetRetailProducts`
**فایل:** `src/api-manage/hooks/react-query/beauty/useGetRetailProducts.js`

**نیاز به بررسی:**
- مطمئن شوید که از normalization pattern مشابه استفاده می‌کند
- بررسی کنید که pagination metadata به درستی extract می‌شود

#### 1.6. Hook `useGetRetailOrders`
**فایل:** `src/api-manage/hooks/react-query/beauty/useGetRetailOrders.js`

**نیاز به بررسی:**
- مطمئن شوید که از normalization pattern مشابه استفاده می‌کند
- بررسی کنید که pagination metadata به درستی extract می‌شود

#### 1.7. Hook `useGetGiftCards`
**فایل:** `src/api-manage/hooks/react-query/beauty/useGetGiftCards.js`

**نیاز به بررسی:**
- مطمئن شوید که از normalization pattern مشابه استفاده می‌کند
- بررسی کنید که pagination metadata به درستی extract می‌شود

#### 1.8. Hook `useGetReviews`
**فایل:** `src/api-manage/hooks/react-query/beauty/useGetReviews.js` (اگر وجود دارد)

**نیاز به بررسی:**
- مطمئن شوید که از normalization pattern مشابه استفاده می‌کند
- بررسی کنید که pagination metadata به درستی extract می‌شود

---

## 2. مشکلات Payment Method

### مشکل کلی:
React از `online` استفاده می‌کند، اما Laravel `digital_payment` را می‌پذیرد. React باید `online` را به `digital_payment` تبدیل کند.

### وضعیت فعلی:
- React در `beautyApi.js` تبدیل `online` به `digital_payment` را انجام می‌دهد ✅

### تغییرات لازم:

#### 2.1. API `beautyApi.js`
**فایل:** `src/api-manage/another-formated-api/beautyApi.js`

**وضعیت فعلی:** ✅ تبدیل `online` به `digital_payment` موجود است

**نیاز به بررسی:**
- مطمئن شوید که همه جاهایی که `payment_method` استفاده می‌شود، این تبدیل انجام می‌شود

**کد فعلی:**
```javascript
purchasePackage: (id, paymentMethod) => {
  // Convert 'online' to 'digital_payment' for Laravel compatibility
  const convertedPaymentMethod = paymentMethod === 'online' ? 'digital_payment' : paymentMethod;
  return MainApi.post(`/api/v1/beautybooking/packages/${id}/purchase`, {
    payment_method: convertedPaymentMethod,
  });
},
```

**تغییرات لازم:**
- مطمئن شوید که این تبدیل در همه متدهای payment انجام می‌شود:
  - `purchasePackage` ✅
  - `purchaseGiftCard` ✅
  - `createRetailOrder` ✅
  - `createBooking` ❌ (نیاز به بررسی)
  - `bookConsultation` ❌ (نیاز به بررسی)

#### 2.2. API `beautyVendorApi.js`
**فایل:** `src/api-manage/another-formated-api/beautyVendorApi.js`

**وضعیت فعلی:** ✅ تبدیل `online` به `digital_payment` موجود است در `purchaseSubscription`

**نیاز به بررسی:**
- مطمئن شوید که همه متدهای payment این تبدیل را دارند

---

## 3. مشکلات Request Parameters

### 3.1. Booking List Parameters
**Endpoint:** `GET /api/v1/beautybooking/bookings`

**پارامترهای Laravel:**
- `limit` ✅
- `offset` ✅
- `status` ✅
- `type` ✅ (upcoming/past/cancelled)
- `date_from` ✅
- `date_to` ✅
- `service_id` ✅
- `staff_id` ✅

**پارامترهای React:**
- `limit` ✅
- `offset` ✅
- `status` ✅
- `type` ✅
- `date_range` ❌ (باید به `date_from` و `date_to` تبدیل شود)
- `service_type` ❌ (باید به `service_id` تبدیل شود)
- `staff_id` ✅

**تغییرات لازم:**

**فایل:** `src/api-manage/another-formated-api/beautyApi.js`
**متد:** `getBookings()`

```javascript
getBookings: (params = {}) => {
  const queryParams = new URLSearchParams();
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  if (params.status) queryParams.append("status", params.status);
  if (params.type) queryParams.append("type", params.type);
  
  // تبدیل date_range به date_from و date_to
  if (params.date_range) {
    const dates = params.date_range.split(',');
    if (dates.length === 2) {
      queryParams.append("date_from", dates[0].trim());
      queryParams.append("date_to", dates[1].trim());
    }
  } else {
    if (params.date_from) queryParams.append("date_from", params.date_from);
    if (params.date_to) queryParams.append("date_to", params.date_to);
  }
  
  // تبدیل service_type به service_id
  if (params.service_type) {
    queryParams.append("service_id", params.service_type);
  } else if (params.service_id) {
    queryParams.append("service_id", params.service_id);
  }
  
  if (params.staff_id) queryParams.append("staff_id", params.staff_id);
  return MainApi.get(`/api/v1/beautybooking/bookings?${queryParams.toString()}`);
},
```

### 3.2. Retail Products Parameters
**Endpoint:** `GET /api/v1/beautybooking/retail/products`

**پارامترهای Laravel:**
- `salon_id` ✅ (required)
- `category` ✅ (optional)
- `category_id` ✅ (optional)
- `limit` ✅
- `offset` ✅

**پارامترهای React:**
- `salon_id` ✅
- `category_id` ✅
- `category` ✅
- `limit` ✅
- `offset` ✅

**وضعیت:** ✅ همه پارامترها هماهنگ هستند

**نیاز به بررسی:**
- مطمئن شوید که `salon_id` همیشه required است
- بررسی کنید که validation در frontend انجام می‌شود

**فایل:** `src/api-manage/another-formated-api/beautyApi.js`
**متد:** `getRetailProducts()`

```javascript
getRetailProducts: (params = {}) => {
  if (!params.salon_id) {
    throw new Error('salon_id is required');
  }
  
  const queryParams = new URLSearchParams();
  queryParams.append("salon_id", params.salon_id);
  if (params.category_id) queryParams.append("category_id", params.category_id);
  if (params.category) queryParams.append("category", params.category);
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/retail/products?${queryParams.toString()}`);
},
```

### 3.3. Consultation List Parameters
**Endpoint:** `GET /api/v1/beautybooking/consultations/list`

**پارامترهای Laravel:**
- `salon_id` ✅ (required)
- `consultation_type` ✅ (optional: pre_consultation/post_consultation)
- `limit` ✅
- `offset` ✅

**پارامترهای React:**
- `salon_id` ✅
- `consultation_type` ✅
- `limit` ✅
- `offset` ✅

**وضعیت:** ✅ همه پارامترها هماهنگ هستند

**نیاز به بررسی:**
- مطمئن شوید که `salon_id` همیشه required است
- بررسی کنید که validation در frontend انجام می‌شود

**فایل:** `src/api-manage/another-formated-api/beautyApi.js`
**متد:** `getConsultations()`

```javascript
getConsultations: (params) => {
  if (!params.salon_id) {
    throw new Error('salon_id is required');
  }
  
  const queryParams = new URLSearchParams();
  queryParams.append("salon_id", params.salon_id);
  if (params.consultation_type) queryParams.append("consultation_type", params.consultation_type);
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/consultations/list?${queryParams.toString()}`);
},
```

---

## 4. مشکلات Response Format

### 4.1. Response Normalization
**مشکل:** React باید response format Laravel را به درستی normalize کند.

### تغییرات لازم:

#### 4.1.1. ایجاد Utility Function برای Normalization
**فایل جدید:** `src/api-manage/utils/beautyResponseNormalizer.js`

```javascript
/**
 * Normalize beauty API response to consistent format
 * @param {Object} response - API response
 * @param {Object} params - Request parameters
 * @returns {Object} Normalized response
 */
export const normalizeBeautyResponse = (response, params = {}) => {
  const payload = response || {};
  const rawItems = payload.data ?? payload;
  const items = Array.isArray(rawItems) ? rawItems : rawItems?.data ?? [];

  const perPage =
    payload.per_page ??
    payload.pagination?.per_page ??
    params?.limit ??
    params?.per_page ??
    (items.length || 0);

  const total =
    payload.total ??
    payload.pagination?.total ??
    (typeof payload.count === "number" ? payload.count : items.length);

  const currentPage =
    payload.current_page ??
    payload.pagination?.current_page ??
    (params?.offset && perPage
      ? Math.floor(params.offset / perPage) + 1
      : 1);

  const lastPage =
    payload.last_page ??
    payload.pagination?.last_page ??
    (perPage ? Math.max(1, Math.ceil((total || items.length) / perPage)) : 1);

  return {
    data: items,
    total,
    per_page: perPage,
    current_page: currentPage,
    last_page: lastPage,
    message: payload.message,
  };
};

/**
 * Normalize single item response
 * @param {Object} response - API response
 * @returns {Object} Normalized response
 */
export const normalizeBeautyItemResponse = (response) => {
  const payload = response || {};
  return {
    data: payload.data ?? payload,
    message: payload.message,
  };
};
```

#### 4.1.2. استفاده از Utility Function در Hooks
**تغییرات لازم:**
- همه hooks باید از `normalizeBeautyResponse` استفاده کنند
- همه single item hooks باید از `normalizeBeautyItemResponse` استفاده کنند

---

## 5. مشکلات Error Handling

### 5.1. Error Response Format
**مشکل:** Laravel error response به این فرمت است:
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

#### 5.1.1. ایجاد Error Handler
**فایل:** `src/helper-functions/beautyErrorHandler.js`

**وضعیت فعلی:** ✅ فایل موجود است

**نیاز به بررسی:**
- مطمئن شوید که error handler به درستی Laravel error format را handle می‌کند
- بررسی کنید که error messages به درستی نمایش داده می‌شوند

**کد پیشنهادی:**
```javascript
export const handleBeautyError = (error) => {
  if (error.response?.data?.errors) {
    const errors = error.response.data.errors;
    if (Array.isArray(errors) && errors.length > 0) {
      return errors.map(err => ({
        code: err.code || 'error',
        message: err.message || 'An error occurred'
      }));
    }
  }
  
  return [{
    code: 'error',
    message: error.message || 'An error occurred'
  }];
};
```

#### 5.1.2. استفاده از Error Handler در Hooks
**تغییرات لازم:**
- همه hooks باید از `handleBeautyError` استفاده کنند
- مطمئن شوید که error messages به درستی به کاربر نمایش داده می‌شوند

---

## 6. مشکلات File Upload

### 6.1. Review Attachments
**Endpoint:** `POST /api/v1/beautybooking/reviews`

**مشکل:** React باید فایل‌ها را به صورت FormData ارسال کند.

### تغییرات لازم:

#### 6.1.1. API `beautyApi.js`
**فایل:** `src/api-manage/another-formated-api/beautyApi.js`
**متد:** `submitReview()`

**وضعیت فعلی:** ✅ از FormData استفاده می‌کند

**نیاز به بررسی:**
- مطمئن شوید که FormData به درستی ساخته می‌شود
- بررسی کنید که attachments به صورت array ارسال می‌شوند

**کد پیشنهادی:**
```javascript
submitReview: (reviewData) => {
  const formData = new FormData();
  formData.append('booking_id', reviewData.booking_id);
  formData.append('rating', reviewData.rating);
  if (reviewData.comment) {
    formData.append('comment', reviewData.comment);
  }
  
  // Handle attachments
  if (reviewData.attachments && Array.isArray(reviewData.attachments)) {
    reviewData.attachments.forEach((file, index) => {
      formData.append(`attachments[${index}]`, file);
    });
  } else if (reviewData.attachments) {
    // Single file
    formData.append('attachments[0]', reviewData.attachments);
  }
  
  return MainApi.post("/api/v1/beautybooking/reviews", formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
},
```

#### 6.1.2. Hook `useSubmitReview`
**فایل:** `src/api-manage/hooks/react-query/beauty/useSubmitReview.js`

**نیاز به بررسی:**
- مطمئن شوید که hook به درستی FormData را handle می‌کند
- بررسی کنید که error handling به درستی کار می‌کند

---

## 7. فیچرهای موجود در Laravel که در React ناقص هستند

### 7.1. Monthly Top Rated Salons
**Endpoint:** `GET /api/v1/beautybooking/salons/monthly-top-rated`

**وضعیت:** ✅ API موجود است در `beautyApi.js`
**وضعیت:** ✅ Hook موجود است (استفاده مستقیم از API)

**نیاز به بررسی:**
- مطمئن شوید که response format به درستی handle می‌شود
- بررسی کنید که `year` و `month` parameters به درستی ارسال می‌شوند

**فایل:** `src/api-manage/another-formated-api/beautyApi.js`
**متد:** `getMonthlyTopRatedSalons()`

**وضعیت:** ✅ موجود است

### 7.2. Trending Clinics
**Endpoint:** `GET /api/v1/beautybooking/salons/trending-clinics`

**وضعیت:** ✅ API موجود است در `beautyApi.js`
**وضعیت:** ✅ Hook موجود است (استفاده مستقیم از API)

**نیاز به بررسی:**
- مطمئن شوید که response format به درستی handle می‌شود
- بررسی کنید که `year` و `month` parameters به درستی ارسال می‌شوند

**فایل:** `src/api-manage/another-formated-api/beautyApi.js`
**متد:** `getTrendingClinics()`

**وضعیت:** ✅ موجود است

### 7.3. Package Status
**Endpoint:** `GET /api/v1/beautybooking/packages/{id}/status`

**وضعیت:** ✅ API موجود است در `beautyApi.js`
**وضعیت:** ✅ Hook موجود است (`useGetPackageStatus`)

**نیاز به بررسی:**
- مطمئن شوید که response format به درستی handle می‌شود

**فایل:** `src/api-manage/hooks/react-query/beauty/useGetPackageStatus.js`

**وضعیت:** ✅ موجود است

### 7.4. Package Usage History
**Endpoint:** `GET /api/v1/beautybooking/packages/{id}/usage-history`

**وضعیت:** ✅ API موجود است در `beautyApi.js`
**وضعیت:** ✅ Hook موجود است (`useGetPackageUsageHistory`)

**نیاز به بررسی:**
- مطمئن شوید که response format به درستی handle می‌شود

**فایل:** `src/api-manage/hooks/react-query/beauty/useGetPackageUsageHistory.js`

**وضعیت:** ✅ موجود است

### 7.5. Booking Conversation
**Endpoint:** `GET /api/v1/beautybooking/bookings/{id}/conversation`

**وضعیت:** ✅ API موجود است در `beautyApi.js`
**وضعیت:** ✅ Hook موجود است (`useGetBookingConversation`)

**نیاز به بررسی:**
- مطمئن شوید که response format به درستی handle می‌شود

**فایل:** `src/api-manage/hooks/react-query/beauty/useGetBookingConversation.js`

**وضعیت:** ✅ موجود است

---

## 8. فیچرهای موجود در React که در Laravel ناقص هستند

### 8.1. Service Suggestions
**Endpoint:** `GET /api/v1/beautybooking/services/{id}/suggestions`

**وضعیت:** ✅ API موجود است در `beautyApi.js`
**وضعیت:** ✅ Hook موجود است (`useGetServiceSuggestions`)

**نیاز به بررسی:**
- مطمئن شوید که response format به درستی handle می‌شود

**فایل:** `src/api-manage/hooks/react-query/beauty/useGetServiceSuggestions.js`

**وضعیت:** ✅ موجود است

### 8.2. Availability Check
**Endpoint:** `POST /api/v1/beautybooking/availability/check`

**وضعیت:** ✅ API موجود است در `beautyApi.js`
**وضعیت:** ✅ Hook موجود است (`useCheckAvailability`)

**نیاز به بررسی:**
- مطمئن شوید که request format با Laravel هماهنگ است
- بررسی کنید که response format به درستی handle می‌شود

**فایل:** `src/api-manage/hooks/react-query/beauty/useCheckAvailability.js`

**وضعیت:** ✅ موجود است

### 8.3. Consultation Availability Check
**Endpoint:** `POST /api/v1/beautybooking/consultations/check-availability`

**وضعیت:** ✅ API موجود است در `beautyApi.js`
**وضعیت:** ✅ Hook موجود است (`useCheckConsultationAvailability`)

**نیاز به بررسی:**
- مطمئن شوید که request format با Laravel هماهنگ است
- بررسی کنید که response format به درستی handle می‌شود

**فایل:** `src/api-manage/hooks/react-query/beauty/useCheckConsultationAvailability.js`

**وضعیت:** ✅ موجود است

---

## 9. مشکلات Vendor API

### 9.1. Vendor Booking List
**Endpoint:** `GET /api/v1/beautybooking/vendor/bookings/list/{all}`

**مشکل:** React باید `all` parameter را به درستی handle کند.

**وضعیت:** ✅ API موجود است در `beautyVendorApi.js`

**نیاز به بررسی:**
- مطمئن شوید که `all` parameter به درستی ارسال می‌شود
- بررسی کنید که وقتی `all='all'` است، status filter اعمال نمی‌شود

**فایل:** `src/api-manage/another-formated-api/beautyVendorApi.js`
**متد:** `listBookings()`

**کد فعلی:**
```javascript
listBookings: (params) => {
  const queryParams = new URLSearchParams();
  const allParam = params.all || 'all';
  if (params.status && params.all === 'all') queryParams.append("status", params.status);
  if (params.date_from) queryParams.append("date_from", params.date_from);
  if (params.date_to) queryParams.append("date_to", params.date_to);
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/vendor/bookings/list/${allParam}?${queryParams.toString()}`);
},
```

**مشکل:** Logic اشتباه است. اگر `all === 'all'` باشد، نباید status filter اعمال شود.

**تغییرات لازم:**
```javascript
listBookings: (params) => {
  const queryParams = new URLSearchParams();
  const allParam = params.all || 'all';
  
  // فقط اگر all !== 'all' باشد، status filter اعمال شود
  if (params.status && params.all !== 'all') {
    queryParams.append("status", params.status);
  }
  
  if (params.date_from) queryParams.append("date_from", params.date_from);
  if (params.date_to) queryParams.append("date_to", params.date_to);
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/vendor/bookings/list/${allParam}?${queryParams.toString()}`);
},
```

### 9.2. Vendor Booking Details
**Endpoint:** `GET /api/v1/beautybooking/vendor/bookings/details`

**وضعیت:** ✅ API موجود است در `beautyVendorApi.js`

**نیاز به بررسی:**
- مطمئن شوید که `booking_id` query parameter به درستی ارسال می‌شود

**فایل:** `src/api-manage/another-formated-api/beautyVendorApi.js`
**متد:** `getBookingDetails()`

**وضعیت:** ✅ موجود است

---

## 10. مشکلات کامپوننت‌ها

### 10.1. کامپوننت‌های موجود
**مسیر:** `src/components/home/module-wise-components/beauty/components/`

**کامپوننت‌های موجود:**
- `SalonList.js` ✅
- `SalonCard.js` ✅
- `SalonDetails.js` ✅
- `BookingList.js` ✅
- `BookingCard.js` ✅
- `BookingDetails.js` ✅
- `BookingForm.js` ✅
- `BookingCheckout.js` ✅
- `PackageList.js` ✅
- `PackageCard.js` ✅
- `PackageDetails.js` ✅
- `GiftCardList.js` ✅
- `LoyaltyPoints.js` ✅
- `ConsultationList.js` ✅
- `ConsultationCard.js` ✅
- `ConsultationBooking.js` ✅
- `RetailProducts.js` ✅
- `RetailProductCard.js` ✅
- `RetailCart.js` ✅
- `RetailCheckout.js` ✅
- `RetailOrderList.js` ✅
- `RetailOrderDetails.js` ✅
- `ReviewForm.js` ✅
- `ReviewList.js` ✅
- `ReviewCard.js` ✅
- `ServiceSuggestions.js` ✅
- `AvailabilityCalendar.js` ✅
- `TimeSlotPicker.js` ✅
- `BeautyErrorBoundary.js` ✅

**وضعیت:** ✅ همه کامپوننت‌های اصلی موجود هستند

### 10.2. کامپوننت‌های Vendor
**مسیر:** `src/components/home/module-wise-components/beauty/vendor/`

**کامپوننت‌های موجود:**
- `VendorDashboard.js` ✅
- `VendorBookingList.js` ✅
- `VendorBookingCard.js` ✅
- `VendorBookingDetails.js` ✅
- `ServiceList.js` ✅
- `ServiceCard.js` ✅
- `ServiceForm.js` ✅
- `StaffList.js` ✅
- `StaffCard.js` ✅
- `StaffForm.js` ✅
- `CalendarView.js` ✅
- `RetailProductList.js` ✅
- `RetailProductCard.js` ✅
- `RetailProductForm.js` ✅
- `RetailOrderList.js` ✅
- `RetailOrderCard.js` ✅
- و سایر کامپوننت‌ها...

**وضعیت:** ✅ همه کامپوننت‌های vendor موجود هستند

---

## 11. مشکلات صفحات

### 11.1. صفحات Customer
**مسیر:** `pages/beauty/`

**صفحات موجود:**
- `index.js` ✅
- `salons/index.js` ✅
- `salons/[id]/index.js` ✅
- `salons/popular/index.js` ✅
- `salons/top-rated/index.js` ✅
- `salons/trending-clinics/index.js` ✅
- `bookings/index.js` ✅
- `bookings/[id]/index.js` ✅
- `booking/create/index.js` ✅
- `booking/checkout/index.js` ✅
- `packages/index.js` ✅
- `packages/[id]/index.js` ✅
- `gift-cards/index.js` ✅
- `loyalty/index.js` ✅
- `consultations/index.js` ✅
- `consultations/book/index.js` ✅
- `retail/products/index.js` ✅
- `retail/checkout/index.js` ✅
- `retail/orders/index.js` ✅
- `retail/orders/[id]/index.js` ✅

**وضعیت:** ✅ همه صفحات customer موجود هستند

### 11.2. صفحات Vendor
**مسیر:** `pages/beauty/vendor/`

**صفحات موجود:**
- `dashboard/index.js` ✅
- `bookings/index.js` ✅
- `bookings/[id]/index.js` ✅
- `calendar/index.js` ✅
- `services/index.js` ✅
- `services/[id]/index.js` ✅
- `services/create.js` ✅
- `staff/index.js` ✅
- `staff/[id]/index.js` ✅
- `staff/create.js` ✅
- `retail/products/index.js` ✅
- `retail/products/create.js` ✅
- `retail/orders/index.js` ✅
- `packages/index.js` ✅
- `gift-cards/index.js` ✅
- `gift-cards/redemptions.js` ✅
- `loyalty/index.js` ✅
- `loyalty/campaigns/[id]/stats.js` ✅
- `loyalty/points-history.js` ✅
- `finance/index.js` ✅
- `finance/transactions.js` ✅
- `profile/index.js` ✅
- `profile/documents.js` ✅
- `profile/working-hours.js` ✅
- `profile/holidays.js` ✅
- `subscription/index.js` ✅
- `subscription/history.js` ✅
- `register/index.js` ✅

**وضعیت:** ✅ همه صفحات vendor موجود هستند

---

## 12. مشکلات Validation

### 12.1. Frontend Validation
**مشکل:** React باید validation را در frontend انجام دهد قبل از ارسال به backend.

### تغییرات لازم:

#### 12.1.1. Booking Form Validation
**فایل:** `src/components/home/module-wise-components/beauty/components/BookingForm.js`

**نیاز به بررسی:**
- مطمئن شوید که همه required fields validate می‌شوند
- بررسی کنید که date validation به درستی کار می‌کند
- بررسی کنید که time validation به درستی کار می‌کند

#### 12.1.2. Review Form Validation
**فایل:** `src/components/home/module-wise-components/beauty/components/ReviewForm.js`

**نیاز به بررسی:**
- مطمئن شوید که rating required است
- بررسی کنید که file size validation انجام می‌شود
- بررسی کنید که file type validation انجام می‌شود

#### 12.1.3. Consultation Booking Validation
**فایل:** `src/components/home/module-wise-components/beauty/components/ConsultationBooking.js`

**نیاز به بررسی:**
- مطمئن شوید که همه required fields validate می‌شوند
- بررسی کنید که date validation به درستی کار می‌کند

---

## 13. مشکلات State Management

### 13.1. React Query Configuration
**مشکل:** React Query باید به درستی configure شود.

### تغییرات لازم:

#### 13.1.1. Query Client Configuration
**نیاز به بررسی:**
- مطمئن شوید که query client به درستی configure شده است
- بررسی کنید که retry logic به درستی کار می‌کند
- بررسی کنید که cache time به درستی تنظیم شده است

#### 13.1.2. Error Handling در Hooks
**نیاز به بررسی:**
- مطمئن شوید که همه hooks error handling دارند
- بررسی کنید که error messages به درستی نمایش داده می‌شوند

---

## 14. مشکلات UI/UX

### 14.1. Loading States
**مشکل:** React باید loading states را به درستی نمایش دهد.

### تغییرات لازم:

#### 14.1.1. Skeleton Loaders
**کامپوننت‌های موجود:**
- `SalonCardSkeleton.js` ✅
- `PackageCardSkeleton.js` ✅
- `BookingCardSkeleton.js` ✅

**نیاز به بررسی:**
- مطمئن شوید که همه جاهایی که data loading می‌شود، skeleton loader نمایش داده می‌شود

#### 14.1.2. Error States
**نیاز به بررسی:**
- مطمئن شوید که error states به درستی نمایش داده می‌شوند
- بررسی کنید که error messages کاربرپسند هستند

#### 14.1.3. Empty States
**نیاز به بررسی:**
- مطمئن شوید که empty states به درستی نمایش داده می‌شوند
- بررسی کنید که empty state messages مناسب هستند

---

## 15. چک‌لیست نهایی

### 15.1. API Integration
- [ ] همه API endpoints با Laravel هماهنگ هستند
- [ ] همه request parameters به درستی ارسال می‌شوند
- [ ] همه response formats به درستی handle می‌شوند
- [ ] همه error responses به درستی handle می‌شوند

### 15.2. Hooks
- [ ] همه hooks از normalization pattern استفاده می‌کنند
- [ ] همه hooks error handling دارند
- [ ] همه hooks loading states دارند

### 15.3. Components
- [ ] همه کامپوننت‌ها به درستی data را نمایش می‌دهند
- [ ] همه کامپوننت‌ها loading states دارند
- [ ] همه کامپوننت‌ها error states دارند
- [ ] همه کامپوننت‌ها empty states دارند

### 15.4. Pages
- [ ] همه صفحات به درستی route می‌شوند
- [ ] همه صفحات data را به درستی fetch می‌کنند
- [ ] همه صفحات error handling دارند

### 15.5. Validation
- [ ] همه forms frontend validation دارند
- [ ] همه validation messages کاربرپسند هستند

---

## 16. مرجع فایل‌های Laravel برای هماهنگی

### فایل‌های Routes:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Routes/api/v1/customer/api.php` - تمام API routes
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Routes/api/v1/vendor/api.php` - vendor API routes

### فایل‌های Controllers:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautySalonController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyCategoryController.php`

### فایل‌های مهم دیگر:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Traits/BeautyApiResponse.php` - response format
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Config/config.php` - settings

---

## 17. نکات مهم

1. **همیشه قبل از اعمال تغییرات، از کد فعلی backup بگیرید**
2. **تست کنید که همه API calls به درستی کار می‌کنند**
3. **مطمئن شوید که request/response formats با Laravel هماهنگ هستند**
4. **بررسی کنید که error handling به درستی کار می‌کند**
5. **تست کنید که file uploads به درستی کار می‌کنند**
6. **بررسی کنید که loading states به درستی نمایش داده می‌شوند**

---

## 18. خلاصه تغییرات

### تغییرات ضروری:
1. اضافه کردن پشتیبانی از `date_range` parameter در booking list (تبدیل به `date_from` و `date_to`)
2. اضافه کردن پشتیبانی از `service_type` parameter در booking list (تبدیل به `service_id`)
3. اصلاح logic در `listBookings` برای vendor API
4. ایجاد utility function برای response normalization
5. بهبود error handling در همه hooks
6. بهبود file upload handling در review submission

### تغییرات توصیه شده:
1. بهبود validation در forms
2. بهبود loading states
3. بهبود error messages
4. بهبود empty states
5. بهبود UI/UX

---

**تاریخ ایجاد:** 2025-01-05
**آخرین به‌روزرسانی:** 2025-01-05

