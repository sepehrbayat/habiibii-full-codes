# تغییرات کامل لازم در React - ماژول زیبایی (برای Cursor AI)

**مسیر پروژه:** `/home/sepehr/Projects/6ammart-react/`

## 📋 خلاصه اجرایی

این سند شامل تمام تغییرات لازم در پروژه React برای هماهنگی کامل با Laravel backend است. تمام تغییرات باید در مسیر `src/api-manage/` و `src/components/home/module-wise-components/beauty/` انجام شود. این سند به طور کامل و با جزئیات تمام ناهماهنگی‌ها، مشکلات و فیچرهای ناقص را پوشش می‌دهد.

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
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/packages?${queryParams.toString()}`);
}
```

**بررسی Laravel:**
```php
// در BeautyPackageController.php خط 44-45
$limit = $request->get('per_page', $request->get('limit', 25));
$offset = $request->get('offset', 0);
```

**بررسی:** این کد درست است. Laravel از هر دو `per_page` و `limit` پشتیبانی می‌کند.

#### 1.2. `beautyApi.js` - متد `getLoyaltyCampaigns()`
**مسیر:** `src/api-manage/another-formated-api/beautyApi.js`
**خط فعلی:** خط 130-137

**کد فعلی:**
```javascript
getLoyaltyCampaigns: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.per_page) queryParams.append("per_page", params.per_page);
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/loyalty/campaigns?${queryParams.toString()}`);
}
```

**بررسی Laravel:**
```php
// در BeautyLoyaltyController.php خط 85-86
$limit = $request->get('per_page', $request->get('limit', 25));
$offset = $request->get('offset', 0);
```

**بررسی:** این کد درست است.

#### 1.3. `beautyApi.js` - متد `getConsultations()`
**مسیر:** `src/api-manage/another-formated-api/beautyApi.js`
**خط فعلی:** خط 144-151

**کد فعلی:**
```javascript
getConsultations: (params) => {
  const queryParams = new URLSearchParams();
  if (params.salon_id) queryParams.append("salon_id", params.salon_id);
  if (params.consultation_type) queryParams.append("consultation_type", params.consultation_type);
  if (params.limit) queryParams.append("limit", params.limit);
  if (params.offset) queryParams.append("offset", params.offset);
  return MainApi.get(`/api/v1/beautybooking/consultations/list?${queryParams.toString()}`);
}
```

**بررسی:** این کد درست است.

#### 1.4. `beautyApi.js` - متد `getRetailProducts()`
**مسیر:** `src/api-manage/another-formated-api/beautyApi.js`
**خط فعلی:** خط 162-170

**کد فعلی:**
```javascript
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

**بررسی:** این کد درست است و از هر دو `category_id` و `category` پشتیبانی می‌کند.

---

## 2. مشکلات Payment Method Values

### مشکل کلی:
React و Laravel باید از همان payment method values استفاده کنند.

### بررسی Laravel:
```php
// در BeautyBookingController.php
'payment_method' => 'required|in:wallet,digital_payment,cash_payment',

// در BeautyConsultationController.php
'payment_method' => 'required|in:digital_payment,wallet,cash_payment',

// در BeautyRetailController.php
'payment_method' => 'required|in:digital_payment,wallet,cash_payment',
```

### تغییرات لازم:

#### 2.1. `BookingForm.js` - استفاده از payment_method
**مسیر:** `src/components/home/module-wise-components/beauty/components/BookingForm.js`

**بررسی:** باید مطمئن شویم که از `digital_payment` استفاده می‌کند نه `online`.

**تغییرات:**
```javascript
// اگر از 'online' استفاده می‌کنید، به 'digital_payment' تغییر دهید
payment_method: "digital_payment", // نه 'online'
```

#### 2.2. `ConsultationBooking.js` - استفاده از payment_method
**مسیر:** `src/components/home/module-wise-components/beauty/components/ConsultationBooking.js`

**بررسی:** باید مطمئن شویم که از `digital_payment` استفاده می‌کند.

**تغییرات:**
```javascript
// اگر از 'online' استفاده می‌کنید، به 'digital_payment' تغییر دهید
payment_method: "digital_payment", // نه 'online'
```

#### 2.3. `RetailCheckout.js` - استفاده از payment_method
**مسیر:** `src/components/home/module-wise-components/beauty/components/RetailCheckout.js`

**بررسی:** باید مطمئن شویم که از `digital_payment` استفاده می‌کند.

**تغییرات:**
```javascript
// اگر از 'online' استفاده می‌کنید، به 'digital_payment' تغییر دهید
payment_method: "digital_payment", // نه 'online'
```

---

## 3. مشکلات Response Structure Handling

### مشکل کلی:
React باید response structure را درست handle کند.

### بررسی Laravel Response Format:
```php
// از BeautyApiResponse trait
{
  "message": "Data retrieved successfully",
  "data": [...]
}

// برای paginated responses:
{
  "message": "Data retrieved successfully",
  "data": [...],
  "total": 10,
  "per_page": 25,
  "current_page": 1,
  "last_page": 1
}
```

### تغییرات لازم:

#### 3.1. تمام Hooks - Response Handling
**مشکل:** برخی hooks ممکن است response structure را درست handle نکنند.

**بررسی:**
```javascript
// در useGetSalons.js
const getSalons = async (params) => {
  const { data } = await BeautyApi.searchSalons(params);
  return data; // این 'data' از axios response است که شامل { message, data } است
};
```

**بررسی:** اگر Laravel `{ message, data }` برمی‌گرداند، و axios آن را در `response.data` قرار می‌دهد، پس `data` شامل `{ message, data }` است.

**مشکل احتمالی:** اگر hook از `data.data` استفاده می‌کند، باید بررسی شود.

**بررسی در Components:**
```javascript
// در BookingForm.js
const salon = salonData?.data || salonData;
```

**بررسی:** این کد درست است. اگر `salonData` شامل `{ message, data }` باشد، `salonData.data` استفاده می‌شود.

**تغییرات:** نیازی به تغییر نیست، اما باید مطمئن شویم که همه components از این pattern استفاده می‌کنند.

---

## 4. مشکلات Error Handling

### مشکل کلی:
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

### بررسی React:
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

**بررسی:** این کد درست است.

### تغییرات لازم:

#### 4.1. تمام Components - Error Handling
**بررسی:** باید مطمئن شویم که همه components از `getBeautyErrorMessage` استفاده می‌کنند.

**فایل‌های احتمالی:**
- `BookingForm.js`
- `ConsultationBooking.js`
- `RetailCheckout.js`
- `PackageDetails.js`
- `GiftCardList.js`
- `LoyaltyPoints.js`
- `ReviewForm.js`

**تغییرات:**
```javascript
// قبل:
onError: (error) => {
  toast.error(error?.response?.data?.message || "Failed to perform action");
}

// بعد:
import { getBeautyErrorMessage } from "../../../../../helper-functions/beautyErrorHandler";

onError: (error) => {
  toast.error(getBeautyErrorMessage(error));
}
```

---

## 5. مشکلات Date/Time Format

### مشکل کلی:
React باید date/time را در format درست ارسال کند.

### بررسی Laravel:
```php
// در BeautyBookingController.php
'date' => 'required|date|after_or_equal:today',
'booking_time' => 'required|date_format:H:i',
```

### بررسی React:
```javascript
// در BookingForm.js
date: formData.booking_date ? formData.booking_date.format("YYYY-MM-DD") : "",
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

### مشکل کلی:
React باید فایل‌ها را درست ارسال کند.

### بررسی Laravel:
```php
// در BeautyReviewController.php
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
// در useSubmitReview.js
reviewData.attachments.forEach((file) => {
  formData.append("attachments[]", file); // ✅ درست: استفاده از attachments[]
});
```

**بررسی:** این کد درست است. Laravel از `attachments[]` پشتیبانی می‌کند.

### تغییرات لازم:

#### 6.1. `ReviewForm.js` - بررسی File Types
**مسیر:** `src/components/home/module-wise-components/beauty/components/ReviewForm.js`
**خط فعلی:** خط 27

**کد فعلی:**
```javascript
const imageFiles = files.filter((file) => file.type.startsWith("image/"));
```

**بررسی:** این درست است.

---

## 7. مشکلات Response Handling برای Payment Redirect

### مشکل کلی:
React باید payment redirect response را درست handle کند.

### بررسی Laravel:
```php
// در BeautyBookingController.php
return $this->successResponse('redirect_to_payment', [
    'redirect_url' => $paymentResult,
    'booking' => $this->formatBookingForApi($booking),
]);
```

### بررسی React:
```javascript
// در BookingForm.js
if (response?.data?.redirect_url) {
  window.location.href = response.data.redirect_url;
} else {
  router.push(`/beauty/bookings/${response?.data?.id || response?.data?.booking?.id}`);
}
```

**بررسی:** این کد درست است. React از `redirect_url` استفاده می‌کند.

**نکته:** مطمئن شوید که Laravel همیشه `redirect_url` استفاده می‌کند نه `payment_link`.

---

## 8. فیچرهای Laravel که باید در React پیاده‌سازی شوند

### 8.1. Package Status Endpoint
**وضعیت Laravel:** ✅ موجود
**وضعیت React:** ⚠️ باید بررسی شود

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

**تغییرات لازم:**

#### 8.1.1. ایجاد Hook: `useGetPackageStatus.js`
**مسیر:** `src/api-manage/hooks/react-query/beauty/useGetPackageStatus.js`

**کد:**
```javascript
import { useQuery } from "react-query";
import { BeautyApi } from "../../another-formated-api/beautyApi";

const useGetPackageStatus = (packageId, options = {}) => {
  return useQuery(
    ["beauty-package-status", packageId],
    () => BeautyApi.getPackageStatus(packageId),
    {
      enabled: !!packageId && options.enabled !== false,
      ...options,
    }
  );
};

export default useGetPackageStatus;
```

#### 8.1.2. به‌روزرسانی `PackageDetails.js`
**مسیر:** `src/components/home/module-wise-components/beauty/components/PackageDetails.js`

**تغییرات:**
```javascript
import useGetPackageStatus from "../../../../../api-manage/hooks/react-query/beauty/useGetPackageStatus";

// در component:
const { data: packageStatus, isLoading: statusLoading } = useGetPackageStatus(packageId);

// نمایش package status:
{packageStatus?.data && (
  <Box>
    <Typography variant="h6">Package Status</Typography>
    <Typography>Total Sessions: {packageStatus.data.total_sessions}</Typography>
    <Typography>Remaining Sessions: {packageStatus.data.remaining_sessions}</Typography>
    <Typography>Used Sessions: {packageStatus.data.used_sessions}</Typography>
    <Typography>Valid: {packageStatus.data.is_valid ? "Yes" : "No"}</Typography>
    {packageStatus.data.usages && packageStatus.data.usages.length > 0 && (
      <Box>
        <Typography variant="subtitle1">Usage History</Typography>
        {packageStatus.data.usages.map((usage, index) => (
          <Box key={index}>
            <Typography>Session {usage.session_number}: {usage.used_at}</Typography>
          </Box>
        ))}
      </Box>
    )}
  </Box>
)}
```

### 8.2. Consultation Credit Percentage
**وضعیت Laravel:** ✅ موجود
**وضعیت React:** ⚠️ باید بررسی شود

**Request Parameter:** `main_service_id` (optional)

**تغییرات لازم:**

#### 8.2.1. به‌روزرسانی `ConsultationBooking.js`
**مسیر:** `src/components/home/module-wise-components/beauty/components/ConsultationBooking.js`

**تغییرات:**
```javascript
// اضافه کردن state برای main_service_id
const [mainServiceId, setMainServiceId] = useState(null);

// در form submission:
const bookingData = {
  salon_id: salonId,
  consultation_id: consultationId,
  booking_date: selectedDate.format("YYYY-MM-DD"),
  booking_time: selectedTime,
  staff_id: selectedStaffId,
  payment_method: paymentMethod,
  main_service_id: mainServiceId || undefined, // اضافه کردن این parameter
};

// در UI: اضافه کردن select برای main service (اگر نیاز باشد)
{/* Optional: Select main service for credit application */}
<FormControl fullWidth>
  <InputLabel>Main Service (Optional - for credit)</InputLabel>
  <Select
    value={mainServiceId || ""}
    onChange={(e) => setMainServiceId(e.target.value)}
  >
    <MenuItem value="">None</MenuItem>
    {/* Populate with available services */}
  </Select>
</FormControl>
```

### 8.3. Loyalty Reward Types
**وضعیت Laravel:** ✅ موجود
**وضعیت React:** ⚠️ باید بررسی شود

**Reward Types:**
- `discount_percentage`
- `discount_amount`
- `wallet_credit`
- `cashback`
- `gift_card`
- `points_redeemed`

**تغییرات لازم:**

#### 8.3.1. به‌روزرسانی `LoyaltyPoints.js`
**مسیر:** `src/components/home/module-wise-components/beauty/components/LoyaltyPoints.js`

**تغییرات:**
```javascript
// در onSuccess callback برای redeem:
onSuccess: (response) => {
  const reward = response?.data?.reward;
  if (reward) {
    switch (reward.type) {
      case 'discount_percentage':
        toast.success(`${reward.value}% discount: ${reward.description}`);
        break;
      case 'discount_amount':
        toast.success(`${reward.value} discount: ${reward.description}`);
        break;
      case 'wallet_credit':
        toast.success(`${reward.value} added to wallet. New balance: ${reward.wallet_balance}`);
        break;
      case 'cashback':
        toast.success(`${reward.value} cashback added. New balance: ${reward.wallet_balance}`);
        break;
      case 'gift_card':
        toast.success(`Gift card created: ${reward.gift_card_code}. Amount: ${reward.value}`);
        // نمایش modal با gift card details
        break;
      case 'points_redeemed':
        toast.success(`${reward.points} points redeemed: ${reward.description}`);
        break;
      default:
        toast.success("Points redeemed successfully");
    }
  } else {
    toast.success("Points redeemed successfully");
  }
}
```

### 8.4. Booking Conversation
**وضعیت Laravel:** ✅ موجود
**وضعیت React:** ⚠️ باید بررسی شود

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

**تغییرات لازم:**

#### 8.4.1. ایجاد Hook: `useGetBookingConversation.js`
**مسیر:** `src/api-manage/hooks/react-query/beauty/useGetBookingConversation.js`

**کد:**
```javascript
import { useQuery } from "react-query";
import { BeautyApi } from "../../another-formated-api/beautyApi";

const useGetBookingConversation = (bookingId, options = {}) => {
  return useQuery(
    ["beauty-booking-conversation", bookingId],
    () => BeautyApi.getBookingConversation(bookingId),
    {
      enabled: !!bookingId && options.enabled !== false,
      ...options,
    }
  );
};

export default useGetBookingConversation;
```

#### 8.4.2. به‌روزرسانی `BookingDetails.js`
**مسیر:** `src/components/home/module-wise-components/beauty/components/BookingDetails.js`

**تغییرات:**
```javascript
import useGetBookingConversation from "../../../../../api-manage/hooks/react-query/beauty/useGetBookingConversation";

// در component:
const { data: conversation, isLoading: conversationLoading } = useGetBookingConversation(bookingId);

// نمایش conversation:
{conversation?.data && (
  <Box>
    <Typography variant="h6">Conversation</Typography>
    {conversation.data.messages && conversation.data.messages.length > 0 ? (
      <Box>
        {conversation.data.messages.map((message) => (
          <Box key={message.id}>
            <Typography>{message.message}</Typography>
            <Typography variant="caption">{message.created_at}</Typography>
          </Box>
        ))}
      </Box>
    ) : (
      <Typography>No messages yet</Typography>
    )}
  </Box>
)}
```

---

## 9. مشکلات Response Handling برای Specific Endpoints

### 9.1. Package Purchase Response
**بررسی Laravel:**
```php
// در BeautyPackageController.php
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

**بررسی React:**
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/PackageDetails.js`
- `pages/beauty/packages/[id]/index.js`

**بررسی:** باید مطمئن شویم که components از این response structure استفاده می‌کنند.

### 9.2. Gift Card Response
**بررسی Laravel:**
```php
// در BeautyGiftCardController.php
return $this->successResponse('gift_card_redeemed_successfully', [
    'amount' => $giftCard->amount,
    'salon_id' => $giftCard->salon_id,
    'wallet_balance' => $request->user()->fresh()->wallet_balance,
]);
```

**بررسی React:**
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/GiftCardList.js`
- `pages/beauty/gift-cards/index.js`

**بررسی:** باید مطمئن شویم که components از این response structure استفاده می‌کنند.

### 9.3. Loyalty Points Response
**بررسی Laravel:**
```php
// در BeautyLoyaltyController.php
return $this->successResponse(
    'messages.data_retrieved_successfully',
    [
        'total_points' => $totalPoints,
        'used_points' => $usedPoints,
        'available_points' => max(0, $availablePoints),
    ]
);
```

**بررسی React:**
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/LoyaltyPoints.js`
- `pages/beauty/loyalty/index.js`

**بررسی:** باید مطمئن شویم که components از این response structure استفاده می‌کنند.

### 9.4. Consultation Response
**بررسی Laravel:**
```php
// در BeautyConsultationController.php
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

**بررسی React:**
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/ConsultationList.js`
- `src/components/home/module-wise-components/beauty/components/ConsultationCard.js`

**بررسی:** باید مطمئن شویم که components از این response structure استفاده می‌کنند.

### 9.5. Retail Products Response
**بررسی Laravel:**
```php
// در BeautyRetailController.php
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

**بررسی React:**
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/RetailProducts.js`
- `src/components/home/module-wise-components/beauty/components/RetailProductCard.js`

**بررسی:** باید مطمئن شویم که components از این response structure استفاده می‌کنند.

### 9.6. Review Response
**بررسی Laravel:**
```php
// در BeautyReviewController.php
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

**بررسی React:**
**فایل‌های احتمالی:**
- `src/components/home/module-wise-components/beauty/components/ReviewForm.js`
- `src/components/home/module-wise-components/beauty/components/ReviewCard.js`

**بررسی:** باید مطمئن شویم که components از `attachments` array استفاده می‌کنند.

---

## 10. مشکلات Booking List Response

### بررسی Laravel:
```php
// در BeautyBookingController.php - متد index()
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

## 11. مشکلات Category Response

### بررسی Laravel:
```php
// در BeautyCategoryController.php
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

## 12. خلاصه تغییرات ضروری

### تغییرات با اولویت بالا:

1. **Error Handling:** تمام components باید error responses را درست handle کنند
2. **Payment Method Values:** Consultation و Retail باید از `digital_payment` استفاده کنند
3. **Package Status:** ایجاد hook و component برای نمایش package status
4. **Booking Conversation:** ایجاد hook و component برای نمایش conversation
5. **Consultation Credit:** اضافه کردن support برای `main_service_id`
6. **Loyalty Rewards:** handle کردن تمام reward types

### تغییرات با اولویت متوسط:

1. **Response Structure:** بررسی consistency در response handling
2. **Date/Time Format:** مطمئن شوید که time format `H:i` است
3. **File Upload:** بررسی file type validation

### تغییرات با اولویت پایین:

1. **Documentation:** بهبود comments
2. **Type Safety:** اضافه کردن PropTypes یا TypeScript
3. **Error Messages:** بهبود user-friendly error messages

---

## 13. چک‌لیست نهایی

قبل از commit کردن تغییرات:

- [ ] تمام error handling از `getBeautyErrorMessage` استفاده می‌کند
- [ ] تمام payment methods از `digital_payment` استفاده می‌کنند
- [ ] تمام pagination endpoints `offset` ارسال می‌کنند
- [ ] تمام date formats `YYYY-MM-DD` هستند
- [ ] تمام time formats `H:i` هستند
- [ ] تمام file uploads از FormData استفاده می‌کنند
- [ ] تمام response structures درست handle می‌شوند
- [ ] تمام components از pagination metadata استفاده می‌کنند
- [ ] Package status hook و component موجود است
- [ ] Booking conversation hook و component موجود است
- [ ] Consultation credit percentage support موجود است
- [ ] Loyalty reward types handle می‌شوند

---

## 14. فایل‌های Laravel برای مرجع

برای بررسی دقیق‌تر، این فایل‌ها را در Laravel بررسی کنید:

1. `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Routes/api/v1/customer/api.php` - تمام routes
2. `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/` - تمام controllers
3. `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Traits/BeautyApiResponse.php` - response format

---

**نکته مهم:** این سند باید به‌روزرسانی شود هر زمان که تغییراتی در React یا Laravel ایجاد می‌شود.

