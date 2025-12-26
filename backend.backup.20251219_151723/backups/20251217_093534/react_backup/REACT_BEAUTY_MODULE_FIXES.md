# تغییرات لازم در React - ماژول زیبایی

**مسیر پروژه:** `/home/sepehr/Projects/6ammart-react/`

## 📋 خلاصه

این فایل شامل تمام تغییرات و کامپوننت‌های ناقص در پروژه React برای ماژول زیبایی است.

---

## 🔗 فایل‌های Laravel که باید چک شوند

قبل از شروع تغییرات در React، این فایل‌ها را در پروژه Laravel بررسی کنید تا مطمئن شوید که API endpoints و response format با backend هماهنگ است:

### فایل‌های Routes در Laravel:
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Routes/api/v1/customer/api.php` - بررسی تمام endpointها و parameters
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Routes/api/v1/vendor/api.php` - برای reference (اگر نیاز باشد)

### فایل‌های Controllers در Laravel:
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautySalonController.php` - بررسی response format و parameters
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php` - بررسی request validation و response
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php` - بررسی purchase flow
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php` - بررسی redeem flow
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php` - بررسی redeem flow و reward structure
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php` - بررسی consultation booking flow
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php` - بررسی retail order flow
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php` - بررسی review submission و file upload
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyCategoryController.php` - بررسی category structure

### فایل‌های مهم دیگر در Laravel:
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Traits/BeautyApiResponse.php` - بررسی response methods و format
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Config/config.php` - بررسی cache TTL و settings
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Entities/BeautySalon.php` - بررسی relationships و attributes
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Entities/BeautyBooking.php` - بررسی status values و relationships
- [ ] `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Entities/BeautyReview.php` - بررسی attachment structure

### نکات مهم برای هماهنگی:
1. **Request Parameters:** بررسی اینکه Laravel چه parameters انتظار دارد (query params vs body params)
2. **Response Structure:** بررسی structure دقیق response (data, message, errors)
3. **Pagination:** Laravel از `offset` و `limit` استفاده می‌کند و آن را به `page` تبدیل می‌کند
4. **File Upload:** بررسی اینکه Laravel چگونه فایل‌ها را دریافت می‌کند (FormData, multipart/form-data)
5. **Date Format:** Laravel انتظار `YYYY-MM-DD` برای dates دارد
6. **Time Format:** Laravel انتظار `H:i` یا `H:i:s` برای times دارد
7. **Payment Methods:** بررسی values مجاز برای payment_method
8. **Status Values:** بررسی تمام status values ممکن (booking status, payment status, etc.)
9. **Error Codes:** بررسی error codes که Laravel برمی‌گرداند
10. **Validation Rules:** بررسی validation rules برای هر endpoint

### مثال‌های هماهنگی:

#### Request Format برای Booking:
```javascript
// React باید این format را ارسال کند:
{
  salon_id: 1,
  service_id: 2,
  staff_id: 3, // optional
  booking_date: "2024-01-20", // YYYY-MM-DD
  booking_time: "10:00", // H:i
  payment_method: "cash_payment", // wallet, digital_payment, cash_payment
  notes: "Optional notes"
}
```

#### Response Format از Laravel:
```json
{
  "message": "Booking created successfully",
  "data": {
    "id": 100001,
    "booking_reference": "BB-100001",
    "status": "pending",
    "total_amount": 100000,
    ...
  }
}
```

#### Error Response Format:
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

---

## 1. ایجاد صفحات Consultation (مشاوره)

### 1.1. ایجاد صفحه لیست مشاوره‌ها

**فایل:** `pages/beauty/consultations/index.js`

**ساختار:**
```javascript
import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../src/components/layout/MainLayout";
import SEO from "../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import ConsultationList from "../../../src/components/home/module-wise-components/beauty/components/ConsultationList";
import CustomContainer from "../../../src/components/container";
import { getServerSideProps } from "../../index";

const Consultations = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Beauty Consultations` : "Loading..."}
        image={`${getImageUrl(
          { value: configData?.logo_storage },
          "business_logo_url",
          configData
        )}/${configData?.fav_icon}`}
        businessName={configData?.business_name}
        configData={configData}
      />
      <MainLayout configData={configData} landingPageData={landingPageData}>
        <CustomContainer>
          <ConsultationList />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default Consultations;
export { getServerSideProps };
```

### 1.2. ایجاد صفحه رزرو مشاوره

**فایل:** `pages/beauty/consultations/book/index.js`

**ساختار:**
```javascript
import React from "react";
import CssBaseline from "@mui/material/CssBaseline";
import MainLayout from "../../../../src/components/layout/MainLayout";
import SEO from "../../../../src/components/seo";
import { getImageUrl } from "utils/CustomFunctions";
import ConsultationBooking from "../../../../src/components/home/module-wise-components/beauty/components/ConsultationBooking";
import CustomContainer from "../../../../src/components/container";
import { getServerSideProps } from "../../../index";

const BookConsultation = ({ configData, landingPageData }) => {
  return (
    <>
      <CssBaseline />
      <SEO
        title={configData ? `Book Consultation` : "Loading..."}
        image={`${getImageUrl(
          { value: configData?.logo_storage },
          "business_logo_url",
          configData
        )}/${configData?.fav_icon}`}
        businessName={configData?.business_name}
        configData={configData}
      />
      <MainLayout configData={configData} landingPageData={landingPageData}>
        <CustomContainer>
          <ConsultationBooking />
        </CustomContainer>
      </MainLayout>
    </>
  );
};

export default BookConsultation;
export { getServerSideProps };
```

### 1.3. ایجاد کامپوننت ConsultationList

**فایل:** `src/components/home/module-wise-components/beauty/components/ConsultationList.js`

**فایل‌های Laravel برای چک:**
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php` - متد `list()`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Routes/api/v1/customer/api.php` - بررسی route و parameters

**ویژگی‌ها:**
- دریافت salon_id از query parameter
- نمایش لیست مشاوره‌ها
- فیلتر بر اساس consultation_type (pre_consultation, post_consultation) - **مهم:** این parameter را Laravel انتظار دارد
- استفاده از hook: `useGetConsultations`
- بررسی response structure از Laravel

### 1.4. ایجاد کامپوننت ConsultationCard

**فایل:** `src/components/home/module-wise-components/beauty/components/ConsultationCard.js`

**ویژگی‌ها:**
- نمایش اطلاعات مشاوره
- نمایش قیمت و مدت زمان
- دکمه "Book Consultation"

### 1.5. ایجاد کامپوننت ConsultationBooking

**فایل:** `src/components/home/module-wise-components/beauty/components/ConsultationBooking.js`

**فایل‌های Laravel برای چک:**
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php` - متدهای `book()` و `checkAvailability()`
- بررسی validation rules برای: `salon_id`, `consultation_id`, `booking_date`, `booking_time`, `payment_method`
- بررسی format تاریخ: `YYYY-MM-DD` و زمان: `H:i`

**ویژگی‌ها:**
- فرم رزرو مشاوره
- انتخاب salon_id و consultation_id
- انتخاب تاریخ و زمان - **مهم:** format باید `YYYY-MM-DD` و `H:i` باشد
- انتخاب staff (اختیاری)
- انتخاب payment_method - **مهم:** values مجاز: `online`, `wallet`, `cash_payment`
- انتخاب main_service_id (اختیاری - برای consultation credit)
- استفاده از hook: `useBookConsultation`
- استفاده از hook: `useCheckConsultationAvailability`

---

## 2. ایجاد صفحات Retail Products (محصولات خرده‌فروشی)

### 2.1. ایجاد صفحه لیست محصولات

**فایل:** `pages/beauty/retail/products/index.js`

**ساختار:** مشابه صفحات دیگر با استفاده از `RetailProducts` component

### 2.2. ایجاد صفحه پرداخت محصولات

**فایل:** `pages/beauty/retail/checkout/index.js`

**ساختار:** مشابه صفحات دیگر با استفاده از `RetailCheckout` component

### 2.3. ایجاد کامپوننت RetailProducts

**فایل:** `src/components/home/module-wise-components/beauty/components/RetailProducts.js`

**ویژگی‌ها:**
- دریافت salon_id از query parameter
- نمایش لیست محصولات
- فیلتر بر اساس category
- استفاده از hook: `useGetRetailProducts`
- نمایش stock quantity
- اضافه کردن به سبد خرید

### 2.4. ایجاد کامپوننت RetailProductCard

**فایل:** `src/components/home/module-wise-components/beauty/components/RetailProductCard.js`

**ویژگی‌ها:**
- نمایش تصویر محصول
- نمایش نام و توضیحات
- نمایش قیمت
- نمایش stock status
- دکمه "Add to Cart"

### 2.5. ایجاد کامپوننت RetailCart

**فایل:** `src/components/home/module-wise-components/beauty/components/RetailCart.js`

**ویژگی‌ها:**
- نمایش محصولات در سبد
- امکان تغییر quantity
- امکان حذف محصول
- نمایش مجموع قیمت
- دکمه "Proceed to Checkout"

### 2.6. ایجاد کامپوننت RetailCheckout

**فایل:** `src/components/home/module-wise-components/beauty/components/RetailCheckout.js`

**فایل‌های Laravel برای چک:**
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php` - متد `createOrder()`
- بررسی validation rules:
  - `salon_id` (required)
  - `products` (required, array)
  - `products.*.product_id` (required)
  - `products.*.quantity` (required, min:1)
  - `payment_method` (required: online, wallet, cash_payment)
  - `shipping_address` (optional)
  - `shipping_phone` (optional)
  - `shipping_fee` (optional, numeric)
  - `discount` (optional, numeric)

**ویژگی‌ها:**
- نمایش خلاصه سفارش
- فرم آدرس ارسال (shipping_address, shipping_phone)
- انتخاب payment_method - **مهم:** values مجاز: `online`, `wallet`, `cash_payment`
- محاسبه shipping_fee
- اعمال discount (اختیاری)
- استفاده از hook: `useCreateRetailOrder`
- بررسی response structure از Laravel

---

## 3. ایجاد کامپوننت Review Submission

### 3.1. ایجاد Hook useSubmitReview

**فایل:** `src/api-manage/hooks/react-query/beauty/useSubmitReview.js`

**فایل‌های Laravel برای چک:**
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php` - متد `store()`
- بررسی request validation: `booking_id`, `rating`, `comment`, `attachments` (optional)
- بررسی file upload format - Laravel انتظار `multipart/form-data` دارد

**ساختار:**
```javascript
import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const submitReview = async (reviewData) => {
  // مهم: اگر attachments دارید، باید FormData استفاده کنید
  const formData = new FormData();
  formData.append('booking_id', reviewData.booking_id);
  formData.append('rating', reviewData.rating);
  formData.append('comment', reviewData.comment);
  if (reviewData.attachments) {
    reviewData.attachments.forEach((file) => {
      formData.append('attachments[]', file);
    });
  }
  
  const { data } = await BeautyApi.submitReview(formData);
  return data;
};

export const useSubmitReview = () => {
  return useMutation("beauty-submit-review", submitReview);
};
```

### 3.2. ایجاد Hook useGetUserReviews

**فایل:** `src/api-manage/hooks/react-query/beauty/useGetUserReviews.js`

**ساختار:**
```javascript
import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getUserReviews = async (params) => {
  const { data } = await BeautyApi.getReviews(params);
  return data;
};

export default function useGetUserReviews(params, enabled = true) {
  return useQuery(
    ["beauty-user-reviews", params],
    () => getUserReviews(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}
```

### 3.3. ایجاد کامپوننت ReviewForm

**فایل:** `src/components/home/module-wise-components/beauty/components/ReviewForm.js`

**ویژگی‌ها:**
- دریافت booking_id از props
- فرم rating (1-5 stars)
- فیلد comment
- آپلود تصاویر (attachments) - multiple files
- استفاده از hook: `useSubmitReview`
- validation
- نمایش success/error messages

### 3.4. ایجاد کامپوننت ReviewList

**فایل:** `src/components/home/module-wise-components/beauty/components/ReviewList.js`

**ویژگی‌ها:**
- نمایش لیست نظرات کاربر
- استفاده از hook: `useGetUserReviews`
- pagination
- فیلتر بر اساس status

### 3.5. ایجاد کامپوننت ReviewCard

**فایل:** `src/components/home/module-wise-components/beauty/components/ReviewCard.js`

**ویژگی‌ها:**
- نمایش rating
- نمایش comment
- نمایش attachments (تصاویر)
- نمایش تاریخ
- نمایش salon/service name

### 3.6. تغییرات در BookingDetails.js

**فایل:** `src/components/home/module-wise-components/beauty/components/BookingDetails.js`

**تغییرات:**
- اضافه کردن دکمه "Submit Review" اگر booking completed است و review ندارد
- اضافه کردن modal برای ReviewForm
- نمایش review موجود اگر وجود دارد

---

## 4. ایجاد کامپوننت Service Suggestions

### 4.1. ایجاد Hook useGetServiceSuggestions

**فایل:** `src/api-manage/hooks/react-query/beauty/useGetServiceSuggestions.js`

**فایل‌های Laravel برای چک:**
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php` - متد `getServiceSuggestions()`
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Routes/api/v1/customer/api.php` - بررسی route: `services/{id}/suggestions`
- بررسی query parameters: `salon_id` (optional)

**ساختار:**
```javascript
import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getServiceSuggestions = async (serviceId, salonId) => {
  // مهم: بررسی کنید که Laravel چه parameters انتظار دارد
  const { data } = await BeautyApi.getServiceSuggestions(serviceId, salonId);
  return data;
};

export default function useGetServiceSuggestions(serviceId, salonId, enabled = true) {
  return useQuery(
    ["beauty-service-suggestions", serviceId, salonId],
    () => getServiceSuggestions(serviceId, salonId),
    {
      enabled: enabled && !!serviceId,
      onError: onSingleErrorResponse,
    }
  );
}
```

### 4.2. ایجاد کامپوننت ServiceSuggestions

**فایل:** `src/components/home/module-wise-components/beauty/components/ServiceSuggestions.js`

**ویژگی‌ها:**
- دریافت serviceId و salonId از props
- استفاده از hook: `useGetServiceSuggestions`
- نمایش لیست پیشنهادات
- دکمه "Add to Booking" برای هر پیشنهاد
- نمایش قیمت و مدت زمان

### 4.3. تغییرات در SalonDetails.js

**فایل:** `src/components/home/module-wise-components/beauty/components/SalonDetails.js`

**تغییرات:**
- اضافه کردن ServiceSuggestions component
- نمایش پیشنهادات هنگام انتخاب یک خدمت
- امکان اضافه کردن پیشنهادات به booking

---

## 5. ایجاد کامپوننت Availability Calendar

### 5.1. ایجاد کامپوننت AvailabilityCalendar

**فایل:** `src/components/home/module-wise-components/beauty/components/AvailabilityCalendar.js`

**فایل‌های Laravel برای چک:**
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php` - متد `checkAvailability()`
- بررسی request parameters: `salon_id`, `service_id`, `date` (YYYY-MM-DD), `staff_id` (optional)
- بررسی response structure: `available_slots` array با format `H:i`

**ویژگی‌ها:**
- دریافت salon_id, service_id, staff_id از props
- نمایش تقویم ماهانه
- استفاده از hook: `useCheckAvailability` برای هر روز - **مهم:** date format باید `YYYY-MM-DD` باشد
- نمایش slotهای موجود در هر روز - **مهم:** format زمان از Laravel: `H:i`
- امکان انتخاب تاریخ
- نمایش loading state برای هر روز
- استفاده از DatePicker یا Calendar component

**نکته:** می‌توان از `@mui/x-date-pickers` استفاده کرد.

---

## 6. اضافه کردن Navigation Links

### 6.1. تغییرات در menuData.js

**فایل:** `src/components/header/second-navbar/account-popover/menuData.js`

**تغییرات:**
```javascript
// اضافه کردن import:
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import SpaIcon from "@mui/icons-material/Spa";
import ShoppingBagIcon from "@mui/icons-material/ShoppingBag";

// اضافه کردن به menuData array:
{
  id: 11,
  name: "beauty-bookings",
  icon: <CalendarTodayIcon />,
  path: "/beauty/bookings",
},
// اگر loyalty points برای beauty جدا باشد:
{
  id: 12,
  name: "beauty-loyalty",
  icon: <LoyaltyIcon />,
  path: "/beauty/loyalty",
},
```

**نکته:** باید conditional rendering اضافه شود تا فقط برای ماژول زیبایی نمایش داده شود.

### 6.2. تغییرات در BottomNav.js

**فایل:** `src/components/header/BottomNav.js`

**تغییرات:**
```javascript
// اضافه کردن import:
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";

// اضافه کردن در BottomNavigation:
{selectedModule?.module_type === "beauty" && (
  <CustomBottomNavigationAction
    label={t("My Bookings")}
    value="beauty-bookings"
    icon={
      <Badge color="error">
        <CalendarTodayIcon />
      </Badge>
    }
  />
)}
```

**نکته:** باید routing logic را هم تغییر دهید تا `beauty-bookings` به `/beauty/bookings` هدایت شود.

### 6.3. تغییرات در ProfileTab.js

**فایل:** `src/components/user-information/ProfileTab.js`

**تغییرات:**
```javascript
// اضافه کردن فیلتر برای ماژول زیبایی:
const { selectedModule } = useSelector((state) => state.utilsData);

// در map function:
{tabMenu?.map((item, index) => {
  // اضافه کردن فیلتر:
  if (item.id === 11 && selectedModule?.module_type !== "beauty") {
    return null;
  }
  // ... rest of code
})}
```

---

## 7. تکمیل Integration با Profile

### 7.1. تغییرات در ProfileBody.js

**فایل:** `src/components/user-information/ProfileBody.js`

**تغییرات:**
```javascript
// اضافه کردن imports:
import ConsultationList from "components/home/module-wise-components/beauty/components/ConsultationList";
import RetailOrderList from "components/home/module-wise-components/beauty/components/RetailOrderList";

// اضافه کردن cases:
if (page === "beauty-consultations") {
  return <ConsultationList />;
}
if (page === "beauty-retail-orders") {
  return <RetailOrderList />;
}
```

### 7.2. ایجاد کامپوننت RetailOrderList

**فایل:** `src/components/home/module-wise-components/beauty/components/RetailOrderList.js`

**ویژگی‌ها:**
- نمایش لیست سفارش‌های خرده‌فروشی کاربر
- استفاده از API endpoint مناسب (اگر وجود دارد)
- یا استفاده از order API عمومی با filter برای retail orders

---

## 8. بهبود کامپوننت‌های موجود

### 8.1. تغییرات در SalonDetails.js

**فایل:** `src/components/home/module-wise-components/beauty/components/SalonDetails.js`

**تغییرات:**
- [ ] اضافه کردن دکمه "Book Consultation"
- [ ] اضافه کردن بخش "Retail Products" با لینک به `/beauty/retail/products?salon_id=${salonDetails.id}`
- [ ] اضافه کردن ServiceSuggestions component
- [ ] بهبود نمایش Reviews (اضافه کردن pagination)
- [ ] اضافه کردن دکمه "View All Reviews"

### 8.2. تغییرات در BookingForm.js

**فایل:** `src/components/home/module-wise-components/beauty/components/BookingForm.js`

**تغییرات:**
- [ ] یکپارچه‌سازی با AvailabilityCalendar
- [ ] اضافه کردن Service Suggestions
- [ ] بهبود validation messages
- [ ] اضافه کردن real-time availability checking

### 8.3. تغییرات در Beauty/index.js

**فایل:** `src/components/home/module-wise-components/beauty/index.js`

**تغییرات:**
- [ ] اضافه کردن بخش "Trending Clinics"
- [ ] اضافه کردن بخش "Monthly Top Rated"
- [ ] بهبود layout و spacing
- [ ] اضافه کردن banners یا promotional sections

---

## 9. اضافه کردن Hooks ناقص

### 9.1. useGetServiceSuggestions.js

**فایل:** `src/api-manage/hooks/react-query/beauty/useGetServiceSuggestions.js`

**کد کامل:**
```javascript
import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getServiceSuggestions = async (serviceId, salonId) => {
  const { data } = await BeautyApi.getServiceSuggestions(serviceId, salonId);
  return data;
};

export default function useGetServiceSuggestions(serviceId, salonId, enabled = true) {
  return useQuery(
    ["beauty-service-suggestions", serviceId, salonId],
    () => getServiceSuggestions(serviceId, salonId),
    {
      enabled: enabled && !!serviceId,
      onError: onSingleErrorResponse,
    }
  );
}
```

### 9.2. useSubmitReview.js

**فایل:** `src/api-manage/hooks/react-query/beauty/useSubmitReview.js`

**کد کامل:**
```javascript
import { useMutation } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";

const submitReview = async (reviewData) => {
  const { data } = await BeautyApi.submitReview(reviewData);
  return data;
};

export const useSubmitReview = () => {
  return useMutation("beauty-submit-review", submitReview);
};
```

### 9.3. useGetUserReviews.js

**فایل:** `src/api-manage/hooks/react-query/beauty/useGetUserReviews.js`

**کد کامل:**
```javascript
import { useQuery } from "react-query";
import { BeautyApi } from "../../../another-formated-api/beautyApi";
import { onSingleErrorResponse } from "../../../api-error-response/ErrorResponses";

const getUserReviews = async (params) => {
  const { data } = await BeautyApi.getReviews(params);
  return data;
};

export default function useGetUserReviews(params, enabled = true) {
  return useQuery(
    ["beauty-user-reviews", params],
    () => getUserReviews(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}
```

---

## 10. بهبود Error Handling

### 10.1. اضافه کردن Error Boundaries

**فایل:** `src/components/home/module-wise-components/beauty/components/BeautyErrorBoundary.js` (ایجاد جدید)

**ویژگی‌ها:**
- Catch errors در کامپوننت‌های زیبایی
- نمایش error message مناسب
- دکمه retry

### 10.2. بهبود Error Messages

**تغییرات در تمام کامپوننت‌ها:**
- [ ] اضافه کردن error messages مناسب
- [ ] استفاده از toast برای نمایش errors
- [ ] اضافه کردن retry mechanisms

### 10.3. بهبود Empty States

**تغییرات در تمام کامپوننت‌های List:**
- [ ] اضافه کردن empty state messages
- [ ] اضافه کردن empty state illustrations
- [ ] اضافه کردن action buttons در empty states

---

## 11. بهبود Loading States

### 11.1. اضافه کردن Skeleton Loaders

**فایل‌های مورد نیاز:**
- `src/components/home/module-wise-components/beauty/components/SalonCardSkeleton.js`
- `src/components/home/module-wise-components/beauty/components/BookingCardSkeleton.js`
- `src/components/home/module-wise-components/beauty/components/PackageCardSkeleton.js`

---

## 12. بهبود Responsive Design

### 12.1. بررسی تمام کامپوننت‌ها

**تغییرات:**
- [ ] بررسی responsive بودن در mobile
- [ ] بررسی responsive بودن در tablet
- [ ] بهبود grid layouts
- [ ] بهبود spacing در mobile

---

## 13. اضافه کردن لینک‌ها در Header/Footer

### 13.1. تغییرات در NavLinks.js

**فایل:** `src/components/header/second-navbar/NavLinks.js`

**تغییرات:**
- [ ] اضافه کردن لینک "Beauty Salons" برای ماژول زیبایی
- [ ] اضافه کردن لینک "Packages" برای ماژول زیبایی

### 13.2. تغییرات در RouteLinks.js

**فایل:** `src/components/footer/footer-middle/RouteLinks.js`

**تغییرات:**
- [ ] اضافه کردن لینک‌های مربوط به ماژول زیبایی در footer

---

## 14. بهبود API Integration

### 14.1. بررسی beautyApi.js

**فایل:** `src/api-manage/another-formated-api/beautyApi.js`

**فایل‌های Laravel برای چک:**
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Routes/api/v1/customer/api.php` - بررسی تمام routes
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Http/Controllers/Api/Customer/*.php` - بررسی request/response format

**تغییرات:**
- [ ] بررسی اینکه همه API calls درست هستند - **مهم:** مطمئن شوید که URLs با Laravel routes match می‌کنند
- [ ] بررسی error handling - بررسی error response format از Laravel
- [ ] بررسی response parsing - بررسی structure دقیق response
- [ ] بررسی request format - مطمئن شوید که date/time formats درست هستند
- [ ] بررسی file uploads - استفاده از FormData برای attachments

---

## 15. Checklist برای هر کامپوننت جدید

برای هر کامپوننت جدید:
- [ ] Import های لازم
- [ ] استفاده از hooks مناسب
- [ ] Error handling
- [ ] Loading states
- [ ] Empty states
- [ ] Responsive design
- [ ] PropTypes یا TypeScript types
- [ ] استفاده از translation (i18n)

---

## 📝 دستورالعمل برای Cursor AI

### مراحل کار:
1. برای هر بخش، ابتدا فایل‌های مربوطه را بررسی کنید
2. کامپوننت‌ها را به ترتیب اولویت ایجاد کنید
3. از کامپوننت‌های موجود به عنوان template استفاده کنید
4. اطمینان حاصل کنید که imports درست هستند
5. تست کنید که کامپوننت‌ها کار می‌کنند

### ترتیب پیشنهادی:
1. ابتدا Hooks ناقص را ایجاد کنید
2. سپس کامپوننت‌های ساده (Card, List) را ایجاد کنید
3. سپس صفحات را ایجاد کنید
4. در آخر Navigation و Integration را انجام دهید

### نکات مهم:
- از ساختار موجود در پروژه پیروی کنید
- از Material-UI components استفاده کنید
- از styled-components برای styling استفاده کنید
- از i18n برای translations استفاده کنید
- از react-query برای data fetching استفاده کنید

---

## 🔗 فایل‌های مرجع

برای الگوگیری از ساختار:
- `src/components/home/module-wise-components/rental/` - برای ساختار ماژول
- `src/components/my-orders/` - برای ساختار لیست سفارش‌ها
- `pages/rental/` - برای ساختار صفحات
- `src/api-manage/hooks/react-query/order/` - برای ساختار hooks

---

## 📋 خلاصه فایل‌های مورد نیاز

### صفحات جدید (7 صفحه):
1. `pages/beauty/consultations/index.js`
2. `pages/beauty/consultations/book/index.js`
3. `pages/beauty/retail/products/index.js`
4. `pages/beauty/retail/checkout/index.js`

### کامپوننت‌های جدید (15 کامپوننت):
1. `ConsultationList.js`
2. `ConsultationCard.js`
3. `ConsultationBooking.js`
4. `RetailProducts.js`
5. `RetailProductCard.js`
6. `RetailCart.js`
7. `RetailCheckout.js`
8. `RetailOrderList.js`
9. `ReviewForm.js`
10. `ReviewList.js`
11. `ReviewCard.js`
12. `ServiceSuggestions.js`
13. `AvailabilityCalendar.js`
14. `BeautyErrorBoundary.js`
15. Skeleton loaders (3 فایل)

### Hooks جدید (3 hook):
1. `useGetServiceSuggestions.js`
2. `useSubmitReview.js`
3. `useGetUserReviews.js`

### تغییرات در فایل‌های موجود (8 فایل):
1. `menuData.js`
2. `BottomNav.js`
3. `ProfileTab.js`
4. `ProfileBody.js`
5. `SalonDetails.js`
6. `BookingForm.js`
7. `Beauty/index.js`
8. `BookingDetails.js`

---

## 📚 مرجع فایل‌های Laravel برای هماهنگی

### فایل‌های Routes:
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Routes/api/v1/customer/api.php` - تمام API routes

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
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Entities/BeautySalon.php` - data structure
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Entities/BeautyBooking.php` - status values
- `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/Entities/BeautyReview.php` - attachment structure

