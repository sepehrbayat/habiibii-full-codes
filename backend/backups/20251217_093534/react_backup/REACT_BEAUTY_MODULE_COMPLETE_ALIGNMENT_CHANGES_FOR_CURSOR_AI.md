# React Beauty Module - Complete Alignment Changes for Cursor AI

این سند شامل تمام تغییرات لازم در سمت React برای هماهنگ‌سازی کامل ماژول Beauty با بک‌اند Laravel است.

## فهرست مطالب

1. [بررسی کلی](#بررسی-کلی)
2. [تغییرات API Calls](#تغییرات-api-calls)
3. [تغییرات Hooks](#تغییرات-hooks)
4. [تغییرات Components](#تغییرات-components)
5. [تغییرات Pages](#تغییرات-pages)
6. [ویژگی‌های موجود در Backend که در Frontend استفاده نشده](#ویژگی‌های-موجود-در-backend-که-در-frontend-استفاده-نشده)
7. [ویژگی‌های مورد نیاز که باید توسعه داده شوند](#ویژگی‌های-مورد-نیاز-که-باید-توسعه-داده-شوند)
8. [مشکلات شناسایی شده](#مشکلات-شناسایی-شده)

---

## بررسی کلی

### وضعیت فعلی
- Backend Laravel در مسیر: `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/`
- Frontend React در مسیر: `/home/sepehr/Projects/6ammart-react/`
- API Base Path: `/api/v1/beautybooking/`
- Vendor API Base Path: `/api/v1/beautybooking/vendor/`

### مشکلات اصلی شناسایی شده
1. **عدم استفاده از برخی API endpoints**: برخی endpointهای موجود در Backend در Frontend استفاده نشده
2. **Missing Error Handling**: برخی error handlingها کامل نیست
3. **Missing Loading States**: برخی components loading state ندارند
4. **Missing Empty States**: برخی components empty state ندارند
5. **Inconsistent Data Format**: برخی components انتظار format خاصی دارند که با Backend هماهنگ نیست

---

## تغییرات API Calls

### 1. BeautyApi (`/src/api-manage/another-formated-api/beautyApi.js`)

#### تغییرات مورد نیاز:

**الف) اضافه کردن متد برای Retail Order Details:**
```javascript
// اضافه شود
getRetailOrderDetails: (id) => {
  return MainApi.get(`/api/v1/beautybooking/retail/orders/${id}`);
},

// اضافه شود
getRetailOrders: (params) => {
  const queryParams = new URLSearchParams();
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  if (params.status) queryParams.append("status", params.status);
  return MainApi.get(`/api/v1/beautybooking/retail/orders?${queryParams.toString()}`);
},
```

**ب) اضافه کردن متد برای Package Usage History:**
```javascript
// اضافه شود
getPackageUsageHistory: (id) => {
  return MainApi.get(`/api/v1/beautybooking/packages/${id}/usage-history`);
},
```

**ج) اضافه کردن متد برای Booking Reschedule:**
```javascript
// اضافه شود
rescheduleBooking: (id, rescheduleData) => {
  return MainApi.put(`/api/v1/beautybooking/bookings/${id}/reschedule`, rescheduleData);
},
```

**د) بهبود متد `getSalonDetails`:**
```javascript
// باید مطمئن شویم که response شامل تمام فیلدهای مورد نیاز است
getSalonDetails: (id) => {
  return MainApi.get(`/api/v1/beautybooking/salons/${id}`);
},
// Response باید شامل این فیلدها باشد:
// - phone
// - email
// - opening_time
// - closing_time
// - is_open
// - distance (اگر coordinates در request باشد)
```

**ه) بهبود متد `getPackages`:**
```javascript
// باید مطمئن شویم که response format درست است
getPackages: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.service_id) queryParams.append("service_id", params.service_id);
  if (params.per_page) queryParams.append("per_page", params.per_page);
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/packages?${queryParams.toString()}`);
},
// Response باید شامل pagination info باشد:
// - data (array)
// - total
// - per_page
// - current_page
// - last_page
```

**و) بهبود متد `getGiftCards`:**
```javascript
// باید مطمئن شویم که response شامل pagination است
getGiftCards: (params) => {
  const queryParams = new URLSearchParams();
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/gift-card/list?${queryParams.toString()}`);
},
// Response باید شامل pagination info باشد
```

**ز) بهبود متد `purchasePackage`:**
```javascript
// باید response را کامل handle کنیم
purchasePackage: (id, paymentMethod) => {
  const convertedPaymentMethod = paymentMethod === 'online' ? 'digital_payment' : paymentMethod;
  return MainApi.post(`/api/v1/beautybooking/packages/${id}/purchase`, {
    payment_method: convertedPaymentMethod,
  });
},
// Response باید شامل این فیلدها باشد:
// - package_id
// - package_name
// - sessions_count
// - total_price
// - payment_status
// - usage_records
```

**ح) بهبود متد `purchaseGiftCard`:**
```javascript
// باید response را کامل handle کنیم
purchaseGiftCard: (giftCardData) => {
  // Convert 'online' to 'digital_payment'
  if (giftCardData.payment_method === 'online') {
    giftCardData.payment_method = 'digital_payment';
  }
  return MainApi.post("/api/v1/beautybooking/gift-card/purchase", giftCardData);
},
// Response باید شامل این فیلدها باشد:
// - gift_card (object with id, code, amount, expires_at, status, salon_id, salon_name)
```

**ط) بهبود متد `getLoyaltyCampaigns`:**
```javascript
// باید response format را کامل handle کنیم
getLoyaltyCampaigns: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.per_page) queryParams.append("per_page", params.per_page);
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/loyalty/campaigns?${queryParams.toString()}`);
},
// Response باید شامل این فیلدها باشد:
// - data (array with id, name, description, type, rules, start_date, end_date, salon, is_active)
// - pagination info
```

**ی) بهبود متد `redeemLoyaltyPoints`:**
```javascript
// باید response را کامل handle کنیم
redeemLoyaltyPoints: (redeemData) => {
  return MainApi.post("/api/v1/beautybooking/loyalty/redeem", redeemData);
},
// Response باید شامل این فیلدها باشد:
// - campaign_id
// - campaign_name
// - points_redeemed
// - remaining_points
// - reward (object)
// - wallet_balance (if applicable)
```

**ک) بهبود متد `createRetailOrder`:**
```javascript
// باید response را کامل handle کنیم
createRetailOrder: (orderData) => {
  // Convert 'online' to 'digital_payment'
  if (orderData.payment_method === 'online') {
    orderData.payment_method = 'digital_payment';
  }
  return MainApi.post("/api/v1/beautybooking/retail/orders", orderData);
},
// Response باید شامل این فیلدها باشد:
// - id
// - order_reference
// - total_amount
// - payment_status
// - status
// - products (array)
```

**ل) بهبود متد `getSalonReviews`:**
```javascript
// باید response format را کامل handle کنیم
getSalonReviews: (salonId, params) => {
  const queryParams = new URLSearchParams();
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/reviews/${salonId}?${queryParams.toString()}`);
},
// Response باید شامل این فیلدها باشد:
// - data (array with id, rating, comment, attachments, user, service, staff, created_at)
// - pagination info
```

---

## تغییرات Hooks

### 1. Customer Hooks (`/src/api-manage/hooks/react-query/beauty/`)

#### تغییرات مورد نیاز:

**الف) اضافه کردن Hook برای Retail Order Details:**
```javascript
// ایجاد فایل: useGetRetailOrderDetails.js
import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getRetailOrderDetails = async (id) => {
  const { data } = await BeautyApi.getRetailOrderDetails(id);
  return data;
};

export default function useGetRetailOrderDetails(id, enabled = true) {
  return useQuery(
    ["beauty-retail-order-details", id],
    () => getRetailOrderDetails(id),
    {
      enabled: enabled && !!id,
      onError: onSingleErrorResponse,
    }
  );
}
```

**ب) اضافه کردن Hook برای Retail Orders List:**
```javascript
// ایجاد فایل: useGetRetailOrders.js
import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getRetailOrders = async (params) => {
  const { data } = await BeautyApi.getRetailOrders(params);
  return data;
};

export default function useGetRetailOrders(params, enabled = true) {
  return useQuery(
    ["beauty-retail-orders", params],
    () => getRetailOrders(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}
```

**ج) اضافه کردن Hook برای Package Usage History:**
```javascript
// ایجاد فایل: useGetPackageUsageHistory.js
import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getPackageUsageHistory = async (id) => {
  const { data } = await BeautyApi.getPackageUsageHistory(id);
  return data;
};

export default function useGetPackageUsageHistory(id, enabled = true) {
  return useQuery(
    ["beauty-package-usage-history", id],
    () => getPackageUsageHistory(id),
    {
      enabled: enabled && !!id,
      onError: onSingleErrorResponse,
    }
  );
}
```

**د) اضافه کردن Hook برای Booking Reschedule:**
```javascript
// ایجاد فایل: useRescheduleBooking.js
import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const rescheduleBooking = async ({ id, ...rescheduleData }) => {
  const { data } = await BeautyApi.rescheduleBooking(id, rescheduleData);
  return data;
};

export const useRescheduleBooking = () => {
  return useMutation("beauty-reschedule-booking", rescheduleBooking);
};
```

**ه) بهبود Hook `useGetPackages`:**
```javascript
// در useGetPackages.js
// باید مطمئن شویم که response format درست handle می‌شود
// Response باید شامل pagination info باشد
```

**و) بهبود Hook `usePurchasePackage`:**
```javascript
// در usePurchasePackage.js
// باید response را کامل handle کنیم
// Response باید شامل usage_records باشد
```

**ز) بهبود Hook `useGetGiftCards`:**
```javascript
// در useGetGiftCards.js
// باید pagination را handle کنیم
// Response باید شامل pagination info باشد
```

**ح) بهبود Hook `useGetLoyaltyCampaigns`:**
```javascript
// در useGetLoyaltyCampaigns.js
// باید response format را کامل handle کنیم
// Response باید شامل is_active و سایر فیلدها باشد
```

**ط) بهبود Hook `useRedeemLoyaltyPoints`:**
```javascript
// در useRedeemLoyaltyPoints.js
// باید response را کامل handle کنیم
// Response باید شامل reward و wallet_balance باشد
```

**ی) بهبود Hook `useCreateRetailOrder`:**
```javascript
// در useCreateRetailOrder.js
// باید response را کامل handle کنیم
// Response باید شامل order_reference و products باشد
```

**ک) بهبود Hook `useGetSalonReviews`:**
```javascript
// در useGetSalonReviews.js
// باید response format را کامل handle کنیم
// Response باید شامل user, service, staff باشد
```

**ل) استفاده از Hook `useGetServiceSuggestions`:**
```javascript
// ✅ این Hook استفاده شده است در BookingForm.js
// فایل: useGetServiceSuggestions.js
// استفاده: BookingForm.js خط 27 و 241
```

**م) استفاده از Hook `useGetBookingConversation`:**
```javascript
// ✅ این Hook استفاده شده است در BookingDetails.js
// فایل: useGetBookingConversation.js
// استفاده: BookingDetails.js خط 5 و 17
// ⚠️ اما component جداگانه برای نمایش conversation وجود ندارد
```

---

## تغییرات Components

### 1. Customer Components (`/src/components/home/module-wise-components/beauty/components/`)

#### تغییرات مورد نیاز:

**الف) بهبود `SalonDetails.js`:**
```javascript
// باید این فیلدها را نمایش دهیم:
// - phone
// - email
// - opening_time / closing_time
// - is_open (با badge یا indicator)
// - distance (اگر coordinates موجود باشد)

// باید از useGetSalonDetails استفاده کنیم و تمام فیلدها را نمایش دهیم
```

**ب) بهبود `BookingDetails.js`:**
```javascript
// باید این فیلدها را اضافه کنیم:
// - can_cancel (button برای cancel)
// - can_reschedule (button برای reschedule)
// - cancellation_deadline (نمایش deadline)
// - salon (object با تمام جزئیات)
// - conversation (استفاده از useGetBookingConversation)

// باید از useRescheduleBooking استفاده کنیم
```

**ج) بهبود `PackageDetails.js`:**
```javascript
// باید این فیلدها را اضافه کنیم:
// - usage_history (استفاده از useGetPackageUsageHistory)
// - remaining_sessions (از useGetPackageStatus)
// - is_valid (badge یا indicator)

// باید از useGetPackageStatus و useGetPackageUsageHistory استفاده کنیم
```

**د) بهبود `BookingList.js`:**
```javascript
// باید فیلترهای بیشتری اضافه کنیم:
// - date_range
// - service_type
// - staff_id

// باید empty state و loading state داشته باشد
```

**ه) بهبود `PackageList.js`:**
```javascript
// باید pagination را کامل handle کنیم
// باید empty state و loading state داشته باشد
// باید response format را درست handle کنیم
```

**و) بهبود `GiftCardList.js`:**
```javascript
// باید pagination را کامل handle کنیم
// باید empty state و loading state داشته باشد
// باید response format را درست handle کنیم
```

**ز) بهبود `RetailProducts.js`:**
```javascript
// باید فیلتر category_id را اضافه کنیم (در حال حاضر فقط category string است)
// باید pagination را کامل handle کنیم
```

**ح) اضافه کردن Component برای Retail Order Details:**
```javascript
// ایجاد فایل: RetailOrderDetails.js
// باید از useGetRetailOrderDetails استفاده کند
// باید تمام جزئیات سفارش را نمایش دهد
```

**ط) اضافه کردن Component برای Retail Order List:**
```javascript
// ایجاد فایل: RetailOrderList.js
// باید از useGetRetailOrders استفاده کند
// باید pagination و فیلتر status داشته باشد
```

**ی) بهبود `ServiceSuggestions.js`:**
```javascript
// ✅ این component استفاده شده است در BookingForm.js
// باید بررسی شود که آیا نیاز به بهبود دارد یا نه
// فایل: ServiceSuggestions.js
// استفاده: BookingForm.js خط 27 و 241
```

**ک) بهبود `ReviewForm.js`:**
```javascript
// باید response را کامل handle کنیم
// باید success message را نمایش دهیم
// باید error handling را بهبود دهیم
```

**ل) بهبود `ReviewList.js`:**
```javascript
// باید response format را کامل handle کنیم
// باید user, service, staff را نمایش دهیم
// باید pagination را handle کنیم
```

---

## تغییرات Pages

### 1. Customer Pages (`/pages/beauty/`)

#### تغییرات مورد نیاز:

**الف) بهبود `bookings/index.js`:**
```javascript
// باید فیلترهای بیشتری اضافه کنیم
// باید empty state و loading state داشته باشد
```

**ب) بهبود `bookings/[id]/index.js`:**
```javascript
// باید از useGetBookingConversation استفاده کنیم
// باید دکمه reschedule اضافه کنیم (اگر can_reschedule باشد)
// باید cancellation_deadline را نمایش دهیم
```

**ج) بهبود `packages/index.js`:**
```javascript
// باید pagination را کامل handle کنیم
// باید empty state و loading state داشته باشد
```

**د) بهبود `packages/[id]/index.js`:**
```javascript
// باید از useGetPackageStatus استفاده کنیم
// باید از useGetPackageUsageHistory استفاده کنیم
// باید usage history را نمایش دهیم
```

**ه) بهبود `gift-cards/index.js`:**
```javascript
// باید pagination را کامل handle کنیم
// باید empty state و loading state داشته باشد
```

**و) بهبود `retail/products/index.js`:**
```javascript
// باید فیلتر category_id را اضافه کنیم
// باید pagination را کامل handle کنیم
```

**ز) اضافه کردن Page برای Retail Order Details:**
```javascript
// ایجاد فایل: retail/orders/[id]/index.js
// باید از RetailOrderDetails component استفاده کند
```

**ح) اضافه کردن Page برای Retail Order List:**
```javascript
// ایجاد فایل: retail/orders/index.js
// باید از RetailOrderList component استفاده کند
```

---

## ویژگی‌های موجود در Backend که در Frontend استفاده شده

### 1. Service Suggestions (Cross-selling)
- **Backend**: `GET /api/v1/beautybooking/services/{id}/suggestions`
- **Frontend Hook**: ✅ موجود (`useGetServiceSuggestions`)
- **Frontend Component**: ✅ موجود (`ServiceSuggestions.js`)
- **Status**: ✅ **استفاده شده** در `BookingForm.js` (خط 27 و 241)
- **Action**: ✅ درست پیاده‌سازی شده است

### 2. Booking Conversation
- **Backend**: `GET /api/v1/beautybooking/bookings/{id}/conversation`
- **Frontend Hook**: ✅ موجود (`useGetBookingConversation`)
- **Frontend Component**: ⚠️ component جداگانه برای نمایش conversation وجود ندارد
- **Status**: ✅ **Hook استفاده شده** در `BookingDetails.js` (خط 5 و 17)
- **Action**: ⚠️ باید component جداگانه برای نمایش بهتر conversation ایجاد شود

### 3. Package Status
- **Backend**: `GET /api/v1/beautybooking/packages/{id}/status`
- **Frontend Hook**: ✅ موجود (`useGetPackageStatus`)
- **Frontend Component**: ✅ استفاده شده در `PackageDetails.js` (خط 7 و 20)
- **Status**: ✅ **استفاده شده**
- **Action**: ✅ درست پیاده‌سازی شده است

### 4. Monthly Top Rated Salons
- **Backend**: `GET /api/v1/beautybooking/salons/monthly-top-rated`
- **Frontend Hook**: ✅ موجود (در `beauty/index.js`)
- **Frontend Component**: ✅ استفاده شده
- **Status**: ✅ درست کار می‌کند

### 5. Trending Clinics
- **Backend**: `GET /api/v1/beautybooking/salons/trending-clinics`
- **Frontend Hook**: ✅ موجود (در `beauty/index.js`)
- **Frontend Component**: ✅ استفاده شده
- **Status**: ✅ درست کار می‌کند

---

## ویژگی‌های مورد نیاز که باید توسعه داده شوند

### 1. Retail Order Management
- **Status**: ❌ موجود نیست
- **Action**: باید توسعه داده شود:
  - Page برای لیست سفارشات: `/pages/beauty/retail/orders/index.js`
  - Page برای جزئیات سفارش: `/pages/beauty/retail/orders/[id]/index.js`
  - Component برای لیست: `RetailOrderList.js`
  - Component برای جزئیات: `RetailOrderDetails.js`
  - Hook: `useGetRetailOrders.js` و `useGetRetailOrderDetails.js`
  - API method: `getRetailOrders` و `getRetailOrderDetails`

### 2. Package Usage History
- **Status**: ❌ Hook موجود نیست (باید ایجاد شود)
- **Action**: باید توسعه داده شود:
  - ایجاد Hook: `useGetPackageUsageHistory.js`
  - اضافه کردن API method: `getPackageUsageHistory`
  - استفاده از Hook در `PackageDetails`
  - نمایش تاریخچه استفاده در `PackageDetails`

### 3. Booking Reschedule
- **Status**: ❌ موجود نیست
- **Action**: باید توسعه داده شود:
  - Hook: `useRescheduleBooking.js`
  - API method: `rescheduleBooking`
  - Button در `BookingDetails` برای reschedule
  - Modal یا Form برای انتخاب زمان جدید

### 4. Service Suggestions Integration
- **Status**: ✅ Component استفاده شده است در `BookingForm`
- **Action**: ✅ درست پیاده‌سازی شده است
  - ✅ استفاده از `ServiceSuggestions` در `BookingForm` (خط 27 و 241)
  - ⚠️ می‌تواند در `ServiceDetails` یا `SalonDetails` نیز استفاده شود

### 5. Booking Conversation Display
- **Status**: ✅ Hook استفاده شده است اما component جداگانه موجود نیست
- **Action**: باید توسعه داده شود:
  - ✅ Hook در `BookingDetails` استفاده شده است
  - ⚠️ Component جداگانه: `BookingConversation.js` باید ایجاد شود برای نمایش بهتر
  - استفاده component در `BookingDetails` برای نمایش بهتر conversation

### 6. Advanced Filters
- **Status**: ⚠️ فیلترهای پایه موجود است
- **Action**: باید توسعه داده شود:
  - فیلتر `date_range` در `BookingList`
  - فیلتر `service_type` در `BookingList`
  - فیلتر `staff_id` در `BookingList`
  - فیلتر `price_range` در `SalonList`
  - فیلتر `distance` در `SalonList`
  - فیلتر `amenities` در `SalonList`

### 7. Empty States
- **Status**: ⚠️ برخی components empty state ندارند
- **Action**: باید اضافه شود:
  - Empty state برای `BookingList`
  - Empty state برای `PackageList`
  - Empty state برای `GiftCardList`
  - Empty state برای `RetailOrderList`

### 8. Loading States
- **Status**: ⚠️ برخی components loading state ندارند
- **Action**: باید اضافه شود:
  - Loading skeleton برای `BookingList`
  - Loading skeleton برای `PackageList`
  - Loading skeleton برای `GiftCardList`
  - Loading skeleton برای `RetailOrderList`

---

## مشکلات شناسایی شده

### 1. Payment Method Conversion
- **مشکل**: React گاهی `online` می‌فرستد اما Backend `digital_payment` انتظار دارد
- **راه حل**: ✅ در `beautyApi.js` و `beautyVendorApi.js` تبدیل انجام می‌شود
- **Status**: ✅ درست است

### 2. Pagination Format
- **مشکل**: React گاهی `per_page` و گاهی `limit` می‌فرستد
- **راه حل**: ✅ Backend هر دو را support می‌کند
- **Status**: ✅ درست است

### 3. Response Format Inconsistency
- **مشکل**: برخی responses format یکسانی ندارند
- **راه حل**: ⚠️ باید در Backend یکسان شود
- **Status**: ⚠️ نیاز به تغییر در Backend

### 4. Missing Error Handling
- **مشکل**: برخی components error handling کامل ندارند
- **راه حل**: ⚠️ باید error handling اضافه شود
- **Status**: ⚠️ نیاز به تغییر

### 5. Missing Loading States
- **مشکل**: برخی components loading state ندارند
- **راه حل**: ⚠️ باید loading state اضافه شود
- **Status**: ⚠️ نیاز به تغییر

### 6. Missing Empty States
- **مشکل**: برخی components empty state ندارند
- **راه حل**: ⚠️ باید empty state اضافه شود
- **Status**: ⚠️ نیاز به تغییر

---

## خلاصه تغییرات ضروری

### اولویت بالا (Critical):
1. ✅ اضافه کردن Retail Order Management (List و Details)
2. ✅ استفاده از Service Suggestions در BookingForm - **انجام شده**
3. ✅ استفاده از Booking Conversation در BookingDetails - **Hook استفاده شده، component جداگانه نیاز است**
4. ✅ استفاده از Package Status در PackageDetails - **انجام شده**
5. ⚠️ اضافه کردن Package Usage History در PackageDetails - **Hook و API method نیاز است**
6. ✅ اضافه کردن Booking Reschedule functionality

### اولویت متوسط (Important):
1. ⚠️ بهبود فیلترها در BookingList و SalonList
2. ⚠️ اضافه کردن Empty States
3. ⚠️ اضافه کردن Loading States
4. ⚠️ بهبود Error Handling

### اولویت پایین (Nice to have):
1. 📝 بهبود UI/UX
2. 📝 اضافه کردن Animations
3. 📝 بهبود Performance

---

## فایل‌های مورد نیاز برای تغییر

### API Files:
1. `/src/api-manage/another-formated-api/beautyApi.js`

### Hooks:
1. `/src/api-manage/hooks/react-query/beauty/useGetRetailOrderDetails.js` (جدید)
2. `/src/api-manage/hooks/react-query/beauty/useGetRetailOrders.js` (جدید)
3. `/src/api-manage/hooks/react-query/beauty/useGetPackageUsageHistory.js` (جدید)
4. `/src/api-manage/hooks/react-query/beauty/useRescheduleBooking.js` (جدید)
5. `/src/api-manage/hooks/react-query/beauty/useGetPackages.js` (بهبود)
6. `/src/api-manage/hooks/react-query/beauty/usePurchasePackage.js` (بهبود)
7. `/src/api-manage/hooks/react-query/beauty/useGetGiftCards.js` (بهبود)
8. `/src/api-manage/hooks/react-query/beauty/useGetLoyaltyCampaigns.js` (بهبود)
9. `/src/api-manage/hooks/react-query/beauty/useRedeemLoyaltyPoints.js` (بهبود)
10. `/src/api-manage/hooks/react-query/beauty/useCreateRetailOrder.js` (بهبود)
11. `/src/api-manage/hooks/react-query/beauty/useGetSalonReviews.js` (بهبود)

### Components:
1. `/src/components/home/module-wise-components/beauty/components/SalonDetails.js` (بهبود)
2. `/src/components/home/module-wise-components/beauty/components/BookingDetails.js` (بهبود)
3. `/src/components/home/module-wise-components/beauty/components/PackageDetails.js` (بهبود)
4. `/src/components/home/module-wise-components/beauty/components/BookingList.js` (بهبود)
5. `/src/components/home/module-wise-components/beauty/components/PackageList.js` (بهبود)
6. `/src/components/home/module-wise-components/beauty/components/GiftCardList.js` (بهبود)
7. `/src/components/home/module-wise-components/beauty/components/RetailProducts.js` (بهبود)
8. `/src/components/home/module-wise-components/beauty/components/RetailOrderDetails.js` (جدید)
9. `/src/components/home/module-wise-components/beauty/components/RetailOrderList.js` (جدید)
10. `/src/components/home/module-wise-components/beauty/components/ServiceSuggestions.js` (استفاده)
11. `/src/components/home/module-wise-components/beauty/components/ReviewForm.js` (بهبود)
12. `/src/components/home/module-wise-components/beauty/components/ReviewList.js` (بهبود)
13. `/src/components/home/module-wise-components/beauty/components/BookingConversation.js` (جدید)

### Pages:
1. `/pages/beauty/bookings/index.js` (بهبود)
2. `/pages/beauty/bookings/[id]/index.js` (بهبود)
3. `/pages/beauty/packages/index.js` (بهبود)
4. `/pages/beauty/packages/[id]/index.js` (بهبود)
5. `/pages/beauty/gift-cards/index.js` (بهبود)
6. `/pages/beauty/retail/products/index.js` (بهبود)
7. `/pages/beauty/retail/orders/index.js` (جدید)
8. `/pages/beauty/retail/orders/[id]/index.js` (جدید)

---

## نکات مهم برای پیاده‌سازی

1. **همیشه از React Query برای data fetching استفاده کنید**
2. **همیشه loading state و error state را handle کنید**
3. **همیشه empty state را نمایش دهید**
4. **همیشه pagination را handle کنید**
5. **همیشه response format را validate کنید**
6. **همیشه از try-catch برای error handling استفاده کنید**
7. **همیشه از TypeScript یا PropTypes برای type checking استفاده کنید**

---

## تست‌های مورد نیاز

بعد از اعمال تغییرات، باید این تست‌ها را انجام دهید:

1. ✅ تست تمام API calls
2. ✅ تست pagination
3. ✅ تست error handling
4. ✅ تست loading states
5. ✅ تست empty states
6. ✅ تست form submissions
7. ✅ تست navigation
8. ✅ تست responsive design

---

**تاریخ ایجاد**: 2025-01-05
**آخرین به‌روزرسانی**: 2025-01-05
**نسخه**: 1.0

