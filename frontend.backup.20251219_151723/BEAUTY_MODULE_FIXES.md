# تغییرات لازم برای تکمیل ماژول زیبایی

## 🔵 تغییرات در React (Frontend)

### 1. ایجاد صفحات Consultation (مشاوره)

#### صفحات:
- [ ] `pages/beauty/consultations/index.js` - لیست مشاوره‌های یک سالن
- [ ] `pages/beauty/consultations/book/index.js` - رزرو مشاوره

#### کامپوننت‌ها:
- [ ] `src/components/home/module-wise-components/beauty/components/ConsultationList.js`
- [ ] `src/components/home/module-wise-components/beauty/components/ConsultationCard.js`
- [ ] `src/components/home/module-wise-components/beauty/components/ConsultationBooking.js`

**نکته:** API و Hooks موجود است، فقط صفحات و کامپوننت‌ها نیاز است.

---

### 2. ایجاد صفحات Retail Products (محصولات خرده‌فروشی)

#### صفحات:
- [ ] `pages/beauty/retail/products/index.js` - لیست محصولات
- [ ] `pages/beauty/retail/checkout/index.js` - پرداخت محصولات

#### کامپوننت‌ها:
- [ ] `src/components/home/module-wise-components/beauty/components/RetailProducts.js`
- [ ] `src/components/home/module-wise-components/beauty/components/RetailProductCard.js`
- [ ] `src/components/home/module-wise-components/beauty/components/RetailCheckout.js`
- [ ] `src/components/home/module-wise-components/beauty/components/RetailCart.js`

**نکته:** API و Hooks موجود است، فقط صفحات و کامپوننت‌ها نیاز است.

---

### 3. ایجاد کامپوننت Review Submission

#### Hooks مورد نیاز:
- [ ] `src/api-manage/hooks/react-query/beauty/useSubmitReview.js`
- [ ] `src/api-manage/hooks/react-query/beauty/useGetUserReviews.js`

#### کامپوننت‌ها:
- [ ] `src/components/home/module-wise-components/beauty/components/ReviewForm.js`
- [ ] `src/components/home/module-wise-components/beauty/components/ReviewList.js`
- [ ] `src/components/home/module-wise-components/beauty/components/ReviewCard.js`

#### تغییرات در کامپوننت‌های موجود:
- [ ] اضافه کردن دکمه "Submit Review" در `BookingDetails.js`
- [ ] اضافه کردن بخش نظرات در `SalonDetails.js`

---

### 4. ایجاد کامپوننت Service Suggestions

#### Hook مورد نیاز:
- [ ] `src/api-manage/hooks/react-query/beauty/useGetServiceSuggestions.js`

#### کامپوننت:
- [ ] `src/components/home/module-wise-components/beauty/components/ServiceSuggestions.js`

#### تغییرات در کامپوننت‌های موجود:
- [ ] اضافه کردن ServiceSuggestions در `SalonDetails.js` هنگام انتخاب خدمت

---

### 5. ایجاد کامپوننت Availability Calendar

#### کامپوننت:
- [ ] `src/components/home/module-wise-components/beauty/components/AvailabilityCalendar.js`

#### تغییرات در کامپوننت‌های موجود:
- [ ] یکپارچه‌سازی AvailabilityCalendar با `BookingForm.js`
- [ ] نمایش تقویم ماهانه با slotهای موجود

---

### 6. اضافه کردن Navigation Links

#### تغییرات در `src/components/header/second-navbar/account-popover/menuData.js`:
```javascript
// اضافه کردن:
{
  id: 11,
  name: "beauty-bookings",
  icon: <CalendarTodayIcon />,
  path: "/beauty/bookings",
}
```

#### تغییرات در `src/components/header/BottomNav.js`:
```javascript
// اضافه کردن برای ماژول زیبایی:
{selectedModule?.module_type === "beauty" && (
  <CustomBottomNavigationAction
    label={t("My Bookings")}
    value="beauty-bookings"
    icon={<CalendarTodayIcon />}
  />
)}
```

#### تغییرات در `src/components/user-information/ProfileTab.js`:
- [ ] اضافه کردن فیلتر برای نمایش "beauty-bookings" فقط برای ماژول زیبایی

---

### 7. تکمیل Integration با Profile

#### تغییرات در `src/components/user-information/ProfileBody.js`:
```javascript
// اضافه کردن:
if (page === "beauty-consultations") {
  return <ConsultationList />;
}
if (page === "beauty-retail-orders") {
  return <RetailOrderList />;
}
if (page === "beauty-packages") {
  return <PackageList />;
}
```

---

### 8. بهبود کامپوننت‌های موجود

#### `SalonDetails.js`:
- [ ] اضافه کردن دکمه "Book Consultation"
- [ ] اضافه کردن بخش "Retail Products"
- [ ] اضافه کردن Service Suggestions
- [ ] بهبود نمایش Reviews

#### `BookingForm.js`:
- [ ] یکپارچه‌سازی با AvailabilityCalendar
- [ ] اضافه کردن Service Suggestions
- [ ] بهبود validation

#### `Beauty/index.js` (کامپوننت اصلی):
- [ ] اضافه کردن بخش "Trending Clinics"
- [ ] اضافه کردن بخش "Monthly Top Rated"
- [ ] بهبود layout

---

### 9. اضافه کردن Hooks ناقص

#### Hooks مورد نیاز:
- [ ] `useGetServiceSuggestions.js`
- [ ] `useSubmitReview.js`
- [ ] `useGetUserReviews.js`
- [ ] `useGetConsultationAvailability.js` (اختیاری - می‌توان از useCheckAvailability استفاده کرد)

---

### 10. بهبود Error Handling

#### تغییرات:
- [ ] اضافه کردن Error Boundaries
- [ ] بهبود error messages
- [ ] اضافه کردن retry mechanisms
- [ ] بهبود empty states

---

## 🔴 تغییرات در Laravel (Backend)

### 1. بررسی Response Format Consistency

#### فایل‌های مورد بررسی:
- [ ] `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautySalonController.php`
- [ ] `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
- [ ] `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`
- [ ] `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php`
- [ ] `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php`
- [ ] `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
- [ ] `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`
- [ ] `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`

#### تغییرات لازم:
- [ ] اطمینان از استفاده از `BeautyApiResponse` trait در همه controllers
- [ ] بررسی consistency در response structure
- [ ] بررسی pagination format (استفاده از offset یا page)
- [ ] بررسی error response format

---

### 2. بررسی Validation و Error Messages

#### تغییرات لازم:
- [ ] بررسی تمام validation rules
- [ ] اطمینان از وجود error messages مناسب
- [ ] بررسی translation keys
- [ ] اضافه کردن custom validation messages

---

### 3. بررسی API Documentation

#### تغییرات لازم:
- [ ] بررسی کامل بودن docblocks
- [ ] اضافه کردن examples در docblocks
- [ ] بررسی consistency در response examples
- [ ] اضافه کردن request examples

---

### 4. بررسی Response Structure

#### بررسی‌های لازم:
- [ ] بررسی اینکه همه endpointها response یکسانی دارند
- [ ] بررسی structure در success responses
- [ ] بررسی structure در error responses
- [ ] بررسی pagination metadata

---

### 5. بررسی Caching

#### تغییرات لازم:
- [ ] بررسی caching در endpointهای مناسب
- [ ] بررسی TTL values
- [ ] بررسی cache invalidation strategy
- [ ] اضافه کردن cache tags برای invalidation بهتر

---

### 6. بررسی Security

#### تغییرات لازم:
- [ ] بررسی rate limiting
- [ ] بررسی authorization checks
- [ ] بررسی input sanitization
- [ ] بررسی SQL injection prevention

---

### 7. بررسی Performance

#### تغییرات لازم:
- [ ] بررسی N+1 query problems
- [ ] بررسی eager loading
- [ ] بررسی database indexes
- [ ] بررسی query optimization

---

## 📊 خلاصه اولویت‌ها

### React - اولویت بالا (فوری):
1. ✅ صفحات Consultation
2. ✅ صفحات Retail Products
3. ✅ کامپوننت Review Submission
4. ✅ Navigation Links
5. ✅ Integration با Profile

### React - اولویت متوسط:
6. ✅ کامپوننت Service Suggestions
7. ✅ کامپوننت Availability Calendar
8. ✅ بهبود Error Handling

### Laravel - اولویت بالا:
1. ✅ بررسی Response Format Consistency
2. ✅ بررسی Validation
3. ✅ بررسی API Documentation

### Laravel - اولویت متوسط:
4. ✅ بررسی Caching
5. ✅ بررسی Performance
6. ✅ بررسی Security

---

## 🔗 فایل‌های کلیدی برای تغییر

### React:
- `pages/beauty/consultations/` (ایجاد)
- `pages/beauty/retail/` (ایجاد)
- `src/components/home/module-wise-components/beauty/components/` (اضافه کردن کامپوننت‌های جدید)
- `src/api-manage/hooks/react-query/beauty/` (اضافه کردن hooks)
- `src/components/header/second-navbar/account-popover/menuData.js`
- `src/components/header/BottomNav.js`
- `src/components/user-information/ProfileBody.js`
- `src/components/user-information/ProfileTab.js`

### Laravel:
- `Modules/BeautyBooking/Http/Controllers/Api/Customer/*.php` (بررسی و بهبود)
- `Modules/BeautyBooking/Routes/api/v1/customer/api.php` (بررسی)
- `Modules/BeautyBooking/Traits/BeautyApiResponse.php` (بررسی)






















