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
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php` - متد `list()`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php` - متد `listProducts()`

### تغییرات لازم:

#### 1.1. `beautyApi.js` - متد `getPackages()`
**مسیر:** `src/api-manage/another-formated-api/beautyApi.js`
**خط فعلی:** خط 85-92

**کد فعلی:**
```javascript
getPackages: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.service_id) queryParams.append("service_id", params.service_id);
  if (params.per_page) queryParams.append("per_page", params.per_page);
  if (params.limit) queryParams.append("limit", params.limit); // پشتیبانی از هر دو
  if (params.offset) queryParams.append("offset", params.offset); // اضافه کردن offset
  return MainApi.get(`/api/v1/beautybooking/packages?${queryParams.toString()}`);
},
```

**بررسی:** 
- [ ] کد فعلی درست است و از `limit` و `offset` استفاده می‌کند ✅
- [ ] Laravel از `per_page` و `limit` هر دو پشتیبانی می‌کند ✅

**نتیجه:** نیازی به تغییر نیست. ✅

---

#### 1.2. `beautyApi.js` - متد `getLoyaltyCampaigns()`
**مسیر:** `src/api-manage/another-formated-api/beautyApi.js`
**خط فعلی:** خط 130-136

**کد فعلی:**
```javascript
getLoyaltyCampaigns: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.per_page) queryParams.append("per_page", params.per_page);
  if (params.limit) queryParams.append("limit", params.limit); // پشتیبانی از هر دو
  if (params.offset) queryParams.append("offset", params.offset); // اضافه کردن offset
  return MainApi.get(`/api/v1/beautybooking/loyalty/campaigns?${queryParams.toString()}`);
},
```

**بررسی:**
- [ ] کد فعلی درست است و از `limit` و `offset` استفاده می‌کند ✅
- [ ] Laravel از `per_page` و `limit` هر دو پشتیبانی می‌کند ✅

**نتیجه:** نیازی به تغییر نیست. ✅

---

## 2. مشکلات Payment Method Naming

### مشکل کلی:
React در برخی جاها از `online` استفاده می‌کند، اما Laravel انتظار `digital_payment` دارد. Laravel در برخی controllers تبدیل می‌کند، اما باید مطمئن شویم که همه جا هماهنگ است.

### فایل‌های Laravel برای بررسی:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php` - متد `store()`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php` - متد `purchase()`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php` - متد `purchase()`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php` - متد `book()`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php` - متد `createOrder()`

### تغییرات لازم:

#### 2.1. `BookingForm.js`
**مسیر:** `src/components/home/module-wise-components/beauty/components/BookingForm.js`
**خط فعلی:** خط 42

**کد فعلی:**
```javascript
payment_method: "cash_payment",
```

**بررسی:**
- [ ] باید مطمئن شویم که هنگام submit، `online` به `digital_payment` تبدیل می‌شود
- [ ] یا اینکه در UI فقط `digital_payment` نمایش داده شود

**تغییر لازم:**
```javascript
const handleSubmit = (e) => {
  e.preventDefault();
  
  // Convert 'online' to 'digital_payment' for Laravel compatibility
  const paymentMethod = formData.payment_method === 'online' 
    ? 'digital_payment' 
    : formData.payment_method;

  createBooking(
    {
      ...formData,
      booking_date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
      payment_method: paymentMethod, // Use converted value
    },
    // ...
  );
};
```

---

#### 2.2. `ConsultationBooking.js`
**مسیر:** `src/components/home/module-wise-components/beauty/components/ConsultationBooking.js`
**خط فعلی:** خط 46 و 92

**کد فعلی:**
```javascript
payment_method: "cash_payment",
// ...
payment_method: formData.payment_method,
```

**بررسی:**
- [ ] Laravel در `BeautyConsultationController::book()` تبدیل می‌کند، اما بهتر است در React هم تبدیل کنیم

**تغییر لازم:**
```javascript
const bookingPayload = {
  salon_id: formData.salon_id,
  consultation_id: formData.consultation_id,
  booking_date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
  booking_time: formData.booking_time,
  payment_method: formData.payment_method === 'online' ? 'digital_payment' : formData.payment_method,
};
```

---

#### 2.3. `RetailCheckout.js`
**مسیر:** `src/components/home/module-wise-components/beauty/components/RetailCheckout.js`
**خط فعلی:** خط 35 و 65

**کد فعلی:**
```javascript
payment_method: "cash_payment",
// ...
payment_method: formData.payment_method,
```

**بررسی:**
- [ ] Laravel در `BeautyRetailController::createOrder()` تبدیل می‌کند، اما بهتر است در React هم تبدیل کنیم

**تغییر لازم:**
```javascript
const orderData = {
  salon_id: parseInt(salon_id),
  products: cart.map((item) => ({
    product_id: item.product_id,
    quantity: item.quantity,
  })),
  payment_method: formData.payment_method === 'online' ? 'digital_payment' : formData.payment_method,
};
```

---

#### 2.4. `PackageDetails.js` یا کامپوننت Purchase Package
**مسیر:** `src/components/home/module-wise-components/beauty/components/PackageDetails.js`
**خط فعلی:** خط 28-30

**بررسی:**
- [ ] باید مطمئن شویم که `paymentMethod` به `digital_payment` تبدیل می‌شود اگر `online` باشد

**تغییر لازم:**
```javascript
const handlePurchase = (paymentMethod) => {
  const convertedPaymentMethod = paymentMethod === 'online' ? 'digital_payment' : paymentMethod;
  purchasePackage(
    { id: packageId, paymentMethod: convertedPaymentMethod },
    // ...
  );
};
```

---

#### 2.5. `beautyApi.js` - متد `purchasePackage()`
**مسیر:** `src/api-manage/another-formated-api/beautyApi.js`
**خط فعلی:** خط 99-102

**کد فعلی:**
```javascript
purchasePackage: (id, paymentMethod) => {
  return MainApi.post(`/api/v1/beautybooking/packages/${id}/purchase`, {
    payment_method: paymentMethod,
  });
},
```

**بررسی:**
- [ ] می‌توانیم تبدیل را در اینجا انجام دهیم یا در کامپوننت

**تغییر اختیاری:**
```javascript
purchasePackage: (id, paymentMethod) => {
  // Convert 'online' to 'digital_payment' for Laravel compatibility
  const convertedPaymentMethod = paymentMethod === 'online' ? 'digital_payment' : paymentMethod;
  return MainApi.post(`/api/v1/beautybooking/packages/${id}/purchase`, {
    payment_method: convertedPaymentMethod,
  });
},
```

---

## 3. مشکلات Date/Time Format

### مشکل کلی:
Laravel انتظار دارد dates به صورت `YYYY-MM-DD` و times به صورت `H:i` ارسال شوند.

### فایل‌های Laravel برای بررسی:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php` - validation rules

### بررسی:

#### 3.1. `BookingForm.js`
**مسیر:** `src/components/home/module-wise-components/beauty/components/BookingForm.js`
**خط فعلی:** خط 69 و 85

**کد فعلی:**
```javascript
date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
// ...
booking_date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
```

**بررسی:**
- [ ] Date format درست است ✅
- [ ] باید مطمئن شویم که `booking_time` به صورت `H:i` ارسال می‌شود

**بررسی `booking_time`:**
- [ ] باید مطمئن شویم که time picker فقط ساعت و دقیقه را برمی‌گرداند (نه ثانیه)

---

#### 3.2. `ConsultationBooking.js`
**مسیر:** `src/components/home/module-wise-components/beauty/components/ConsultationBooking.js`
**خط فعلی:** خط 74 و 90

**کد فعلی:**
```javascript
booking_date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
// ...
booking_date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
```

**بررسی:**
- [ ] Date format درست است ✅
- [ ] باید مطمئن شویم که `booking_time` به صورت `H:i` ارسال می‌شود

---

## 4. مشکلات Response Structure Handling

### مشکل کلی:
React باید response structure از Laravel را به درستی handle کند.

### فایل‌های Laravel برای بررسی:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Traits/BeautyApiResponse.php`

### بررسی:

#### 4.1. Response Structure برای Success Responses
**Laravel Format:**
```json
{
  "message": "Data retrieved successfully",
  "data": { ... }
}
```

**React Usage:**
- [ ] باید مطمئن شویم که hooks از `response.data` استفاده می‌کنند

**بررسی Hooks:**
- [ ] `useGetBookings.js` - استفاده از `data` ✅
- [ ] `useGetPackages.js` - استفاده از `data` ✅
- [ ] `useGetSalonDetails.js` - باید بررسی شود
- [ ] `useCreateBooking.js` - باید بررسی شود

---

#### 4.2. Response Structure برای Paginated Lists
**Laravel Format:**
```json
{
  "message": "Data retrieved successfully",
  "data": [...],
  "total": 100,
  "per_page": 25,
  "current_page": 1,
  "last_page": 4
}
```

**React Usage:**
- [ ] باید مطمئن شویم که components از `total`, `per_page`, `current_page`, `last_page` استفاده می‌کنند

**بررسی:**
- [ ] `useGetBookings.js` - باید بررسی شود که pagination metadata را handle می‌کند
- [ ] `useGetPackages.js` - باید بررسی شود که pagination metadata را handle می‌کند
- [ ] `useGetGiftCards.js` - باید بررسی شود که pagination metadata را handle می‌کند

---

#### 4.3. Response Structure برای Error Responses
**Laravel Format:**
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

**React Usage:**
- [ ] باید مطمئن شویم که error handler از `errors` array استفاده می‌کند

**بررسی:**
- [ ] `beautyErrorHandler.js` - باید بررسی شود که `errors` array را handle می‌کند

---

## 5. مشکلات File Upload (Review Attachments)

### مشکل کلی:
React باید فایل‌ها را به صورت `FormData` با `attachments[]` ارسال کند.

### فایل‌های Laravel برای بررسی:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php` - متد `store()`

### بررسی:

#### 5.1. `useSubmitReview.js`
**مسیر:** `src/api-manage/hooks/react-query/beauty/useSubmitReview.js`
**خط فعلی:** خط 1-34

**کد فعلی:**
```javascript
const submitReview = async (reviewData) => {
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
    // ...
  }
};
```

**بررسی:**
- [ ] کد فعلی درست است و از `attachments[]` استفاده می‌کند ✅
- [ ] Laravel این format را می‌پذیرد ✅

**نتیجه:** نیازی به تغییر نیست. ✅

---

## 6. فیچرهای موجود در Laravel که در React توسعه داده نشده‌اند

### 6.1. Service Suggestions (Cross-selling)
**وضعیت در Laravel:** ✅ موجود - `GET /api/v1/beautybooking/services/{id}/suggestions`
**وضعیت در React:** ✅ Hook موجود است (`useGetServiceSuggestions`)

**بررسی:**
- [ ] Hook موجود است: `src/api-manage/hooks/react-query/beauty/useGetServiceSuggestions.js` ✅
- [ ] API call موجود است: `beautyApi.js::getServiceSuggestions()` ✅
- [ ] کامپوننت موجود است: `ServiceSuggestions.js` ✅

**نتیجه:** همه چیز موجود است. ✅

---

### 6.2. Package Status (Remaining Sessions)
**وضعیت در Laravel:** ✅ موجود - `GET /api/v1/beautybooking/packages/{id}/status`
**وضعیت در React:** ✅ Hook موجود است (`useGetPackageStatus`)

**بررسی:**
- [ ] Hook موجود است: `src/api-manage/hooks/react-query/beauty/useGetPackageStatus.js` ✅
- [ ] API call موجود است: `beautyApi.js::getPackageStatus()` ✅
- [ ] UI component نیاز به بررسی دارد

**تغییر لازم:**
- [ ] اضافه کردن نمایش package status در `PackageDetails.js` یا صفحه package details

---

### 6.3. Booking Conversation
**وضعیت در Laravel:** ✅ موجود - `GET /api/v1/beautybooking/bookings/{id}/conversation`
**وضعیت در React:** ✅ Hook موجود است (`useGetBookingConversation`)

**بررسی:**
- [ ] Hook موجود است: `src/api-manage/hooks/react-query/beauty/useGetBookingConversation.js` ✅
- [ ] API call موجود است: `beautyApi.js::getBookingConversation()` ✅
- [ ] UI component نیاز به بررسی دارد

**تغییر لازم:**
- [ ] اضافه کردن نمایش conversation در `BookingDetails.js`

---

### 6.4. Consultation Credit Application
**وضعیت در Laravel:** ✅ موجود - در `BeautyConsultationController::book()` با `main_service_id` و `consultation_credit_percentage`
**وضعیت در React:** ⚠️ ناقص

**بررسی:**
- [ ] `ConsultationBooking.js` دارای `main_service_id` field است ✅
- [ ] باید مطمئن شویم که این feature به درستی کار می‌کند

**نتیجه:** Feature موجود است اما نیاز به تست دارد.

---

### 6.5. Retail Order Management
**وضعیت در Laravel:** ✅ موجود - `POST /api/v1/beautybooking/retail/orders`
**وضعیت در React:** ✅ موجود

**بررسی:**
- [ ] API call موجود است: `beautyApi.js::createRetailOrder()` ✅
- [ ] Hook موجود است: `useCreateRetailOrder.js` ✅
- [ ] Component موجود است: `RetailCheckout.js` ✅
- [ ] صفحه موجود است: `pages/beauty/retail/checkout/index.js` ✅

**نتیجه:** همه چیز موجود است. ✅

---

## 7. مشکلات Navigation و Menu Integration

### مشکل کلی:
بررسی اینکه navigation links برای beauty module به درستی اضافه شده‌اند.

### تغییرات لازم:

#### 7.1. `menuData.js`
**مسیر:** `src/components/header/second-navbar/account-popover/menuData.js`

**بررسی:**
- [ ] منوی "My Beauty Bookings" اضافه شده است
- [ ] منوی "Beauty Packages" اضافه شده است
- [ ] منوی "Gift Cards" اضافه شده است
- [ ] منوی "Loyalty Points" اضافه شده است

**تغییر لازم:**
```javascript
// اضافه کردن menu items برای beauty module
{
  label: "My Beauty Bookings",
  path: "/beauty/bookings",
  icon: "beauty-bookings",
  module: "beauty"
},
{
  label: "Beauty Packages",
  path: "/beauty/packages",
  icon: "beauty-packages",
  module: "beauty"
},
{
  label: "Gift Cards",
  path: "/beauty/gift-cards",
  icon: "gift-cards",
  module: "beauty"
},
{
  label: "Loyalty Points",
  path: "/beauty/loyalty",
  icon: "loyalty-points",
  module: "beauty"
},
```

---

#### 7.2. `BottomNav.js`
**مسیر:** `src/components/header/BottomNav.js`

**بررسی:**
- [ ] "My Bookings" برای beauty module اضافه شده است

**تغییر لازم:**
```javascript
// اضافه کردن bottom nav item برای beauty bookings
{
  label: "My Bookings",
  path: "/beauty/bookings",
  icon: "beauty-bookings",
  module: "beauty"
},
```

---

#### 7.3. `ProfileTab.js`
**مسیر:** `src/components/user-information/ProfileTab.js`

**بررسی:**
- [ ] تب "Beauty Bookings" اضافه شده است
- [ ] تب "Beauty Packages" اضافه شده است
- [ ] تب "Beauty Consultations" اضافه شده است
- [ ] تب "Retail Orders" اضافه شده است

**تغییر لازم:**
```javascript
// اضافه کردن tabs برای beauty module
{
  label: "Beauty Bookings",
  value: "beauty-bookings",
  module: "beauty"
},
{
  label: "Beauty Packages",
  value: "beauty-packages",
  module: "beauty"
},
{
  label: "Beauty Consultations",
  value: "beauty-consultations",
  module: "beauty"
},
{
  label: "Retail Orders",
  value: "beauty-retail-orders",
  module: "beauty"
},
```

---

#### 7.4. `ProfileBody.js`
**مسیر:** `src/components/user-information/ProfileBody.js`

**بررسی:**
- [ ] case برای `page === "beauty-bookings"` اضافه شده است ✅
- [ ] case برای `page === "beauty-consultations"` اضافه شده است
- [ ] case برای `page === "beauty-retail-orders"` اضافه شده است
- [ ] case برای `page === "beauty-packages"` اضافه شده است

**تغییر لازم:**
```javascript
case "beauty-consultations":
  return <ConsultationList />;
case "beauty-retail-orders":
  return <RetailOrderList />;
case "beauty-packages":
  return <PackageList />;
```

---

## 8. مشکلات Error Handling

### مشکل کلی:
بررسی اینکه error handling به درستی انجام می‌شود.

### بررسی:

#### 8.1. `beautyErrorHandler.js`
**مسیر:** `src/helper-functions/beautyErrorHandler.js`

**بررسی:**
- [ ] Error handler از `errors` array استفاده می‌کند
- [ ] Error handler از `code` و `message` استفاده می‌کند

**بررسی لازم:**
```javascript
export const getBeautyErrorMessage = (error) => {
  if (error?.response?.data?.errors) {
    const errors = error.response.data.errors;
    if (Array.isArray(errors) && errors.length > 0) {
      return errors[0].message || errors[0].code;
    }
  }
  return error?.response?.data?.message || error?.message || "An error occurred";
};
```

---

## 9. مشکلات Request Parameters

### مشکل کلی:
بررسی اینکه React چه parameters ارسال می‌کند و با Laravel هماهنگ است.

### بررسی:

#### 9.1. `beautyApi.js::searchSalons()`
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

#### 9.2. `beautyApi.js::createBooking()`
**React API Call:**
```javascript
createBooking: (bookingData) => {
  // salon_id, service_id, staff_id, booking_date, booking_time, payment_method, notes
}
```

**Laravel Validation:**
- باید بررسی شود که تمام فیلدهای لازم وجود دارد

**بررسی:** باید با `BeautyBookingStoreRequest.php` مقایسه شود.

---

#### 9.3. `beautyApi.js::getRetailProducts()`
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
```

**بررسی:** همه parameters هماهنگ هستند. ✅

---

## 10. مشکلات Response Data Access

### مشکل کلی:
بررسی اینکه React از response data به درستی استفاده می‌کند.

### بررسی:

#### 10.1. Hooks که از `data` استفاده می‌کنند
**بررسی:**
- [ ] `useGetBookings.js` - `const { data } = await BeautyApi.getBookings(params); return data;` ✅
- [ ] `useGetPackages.js` - `const { data } = await BeautyApi.getPackages(params); return data;` ✅
- [ ] `useCreateBooking.js` - `const { data } = await BeautyApi.createBooking(bookingData); return data;` ✅

**بررسی:** همه hooks از `data` استفاده می‌کنند. ✅

---

#### 10.2. Components که از response استفاده می‌کنند
**بررسی:**
- [ ] `BookingForm.js` - `const salon = salonData?.data || salonData;` ✅
- [ ] `ConsultationBooking.js` - `const consultations = consultationsData?.data || [];` ✅

**بررسی:** همه components از `data` استفاده می‌کنند. ✅

---

## 11. فیچرهای ناقص در React

### 11.1. Review Submission UI
**وضعیت:** ✅ Hook موجود است، اما UI component نیاز به بررسی دارد

**بررسی:**
- [ ] `useSubmitReview.js` موجود است ✅
- [ ] `ReviewForm.js` موجود است ✅
- [ ] باید مطمئن شویم که در صفحه booking details لینک "Submit Review" وجود دارد

**تغییر لازم:**
- [ ] اضافه کردن لینک "Submit Review" در `BookingDetails.js`

---

### 11.2. Package Status Display
**وضعیت:** ✅ Hook موجود است، اما UI component نیاز به بررسی دارد

**بررسی:**
- [ ] `useGetPackageStatus.js` موجود است ✅
- [ ] باید در `PackageDetails.js` یا صفحه package details نمایش داده شود

**تغییر لازم:**
- [ ] اضافه کردن نمایش package status در `PackageDetails.js`

---

### 11.3. Booking Conversation Display
**وضعیت:** ✅ Hook موجود است، اما UI component نیاز به بررسی دارد

**بررسی:**
- [ ] `useGetBookingConversation.js` موجود است ✅
- [ ] باید در `BookingDetails.js` نمایش داده شود

**تغییر لازم:**
- [ ] اضافه کردن نمایش conversation در `BookingDetails.js`

---

## 12. خلاصه تغییرات لازم

### تغییرات با اولویت بالا:

1. **Payment Method Conversion:**
   - [ ] تبدیل `online` به `digital_payment` در `BookingForm.js`
   - [ ] تبدیل `online` به `digital_payment` در `ConsultationBooking.js`
   - [ ] تبدیل `online` به `digital_payment` در `RetailCheckout.js`
   - [ ] تبدیل `online` به `digital_payment` در `PackageDetails.js` یا کامپوننت purchase

2. **Navigation Integration:**
   - [ ] اضافه کردن menu items در `menuData.js`
   - [ ] اضافه کردن bottom nav item در `BottomNav.js`
   - [ ] اضافه کردن tabs در `ProfileTab.js`
   - [ ] اضافه کردن cases در `ProfileBody.js`

3. **UI Components:**
   - [ ] اضافه کردن لینک "Submit Review" در `BookingDetails.js`
   - [ ] اضافه کردن نمایش package status در `PackageDetails.js`
   - [ ] اضافه کردن نمایش conversation در `BookingDetails.js`

### تغییرات با اولویت متوسط:

4. **Error Handling:**
   - [ ] بررسی `beautyErrorHandler.js` برای handle کردن `errors` array

5. **Response Structure:**
   - [ ] بررسی اینکه همه hooks و components از response structure به درستی استفاده می‌کنند

### تغییرات با اولویت پایین:

6. **Documentation:**
   - [ ] اضافه کردن JSDoc comments در hooks
   - [ ] اضافه کردن PropTypes در components

---

## 13. فایل‌های مرجع Laravel

برای هماهنگی کامل، این فایل‌های Laravel را بررسی کنید:

### Controllers:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautySalonController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyCategoryController.php`

### Traits:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Traits/BeautyApiResponse.php`

### Routes:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Routes/api/v1/customer/api.php`

### Config:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Config/config.php`

---

## 14. نکات مهم برای Implementation

1. **همیشه قبل از تغییر، فایل Laravel مربوطه را بررسی کنید**
2. **تست کنید که request format با Laravel هماهنگ است**
3. **از `digital_payment` به جای `online` استفاده کنید (یا تبدیل کنید)**
4. **Dates را به صورت `YYYY-MM-DD` و times را به صورت `H:i` ارسال کنید**
5. **File uploads را به صورت `FormData` با `attachments[]` ارسال کنید**
6. **Response structure را به درستی handle کنید (`data`, `message`, `errors`)**
7. **Pagination metadata را handle کنید (`total`, `per_page`, `current_page`, `last_page`)**
8. **Error handling را با `errors` array انجام دهید**

---

**تاریخ ایجاد:** 2025-01-XX
**آخرین به‌روزرسانی:** 2025-01-XX

