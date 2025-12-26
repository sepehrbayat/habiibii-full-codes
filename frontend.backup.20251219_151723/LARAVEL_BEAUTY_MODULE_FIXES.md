# تغییرات لازم در Laravel - ماژول زیبایی

**مسیر پروژه:** `/home/sepehr/Projects/6ammart-laravel/`

## 📋 خلاصه

این فایل شامل تمام تغییرات و بهبودهای لازم در پروژه Laravel برای ماژول زیبایی است. تمام تغییرات باید در مسیر `Modules/BeautyBooking/` انجام شود.

---

## 🔗 فایل‌های React که باید چک شوند

قبل از شروع تغییرات در Laravel، این فایل‌ها را در پروژه React بررسی کنید تا مطمئن شوید که response format و API structure با frontend هماهنگ است:

### فایل‌های API در React:
- [ ] `/home/sepehr/Projects/6ammart-react/src/api-manage/another-formated-api/beautyApi.js` - بررسی اینکه چه API calls انتظار می‌رود
- [ ] `/home/sepehr/Projects/6ammart-react/src/api-manage/ApiRoutes.js` - بررسی route definitions

### فایل‌های Hooks در React:
- [ ] `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetSalons.js` - بررسی expected response format
- [ ] `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetBookings.js` - بررسی pagination format (offset vs page)
- [ ] `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useCreateBooking.js` - بررسی request format
- [ ] `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetPackages.js` - بررسی response structure
- [ ] `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetGiftCards.js` - بررسی response structure
- [ ] `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetLoyaltyPoints.js` - بررسی response structure
- [ ] `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetConsultations.js` - بررسی request parameters
- [ ] `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetRetailProducts.js` - بررسی request parameters

### نکات مهم برای هماهنگی:
1. **Pagination Format:** React از `offset` و `limit` استفاده می‌کند. Laravel باید این را به `page` تبدیل کند.
2. **Response Structure:** همه responses باید شامل `{ message: string, data: any }` باشند.
3. **Error Format:** همه errors باید شامل `{ errors: [{ code: string, message: string }] }` باشند.
4. **File Uploads:** بررسی اینکه React چگونه فایل‌ها را ارسال می‌کند (FormData).
5. **Date Format:** بررسی format تاریخ که React ارسال می‌کند (YYYY-MM-DD).

---

## 1. بررسی و بهبود Response Format Consistency

### مشکل:
برخی از endpointها ممکن است response format متفاوتی داشته باشند. باید همه endpointها از `BeautyApiResponse` trait استفاده کنند و response format یکسانی داشته باشند.

### فایل‌های مورد بررسی:

#### 1.1. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautySalonController.php`
**فایل‌های React برای چک:**
- `/home/sepehr/Projects/6ammart-react/src/api-manage/another-formated-api/beautyApi.js` - متدهای `searchSalons`, `getSalonDetails`, `getPopularSalons`, `getTopRatedSalons`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetSalons.js` - بررسی expected response format

**تغییرات لازم:**
- [ ] بررسی استفاده از `BeautyApiResponse` trait
- [ ] بررسی consistency در response methods (`successResponse`, `listResponse`, `simpleListResponse`)
- [ ] بررسی pagination format (استفاده از offset یا page) - **مهم:** React از offset استفاده می‌کند
- [ ] اطمینان از اینکه همه responses شامل `message` و `data` هستند

**مثال:**
```php
// باید همه responses به این شکل باشند:
return $this->successResponse('messages.data_retrieved_successfully', $data);
// یا
return $this->listResponse($paginatedData, 'messages.data_retrieved_successfully');
```

#### 1.2. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
**فایل‌های React برای چک:**
- `/home/sepehr/Projects/6ammart-react/src/api-manage/another-formated-api/beautyApi.js` - متدهای `createBooking`, `getBookings`, `cancelBooking`, `checkAvailability`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useCreateBooking.js` - بررسی request format
- `/home/sepehr/Projects/6ammart-react/src/components/home/module-wise-components/beauty/components/BookingForm.js` - بررسی fields ارسالی

**تغییرات لازم:**
- [ ] بررسی consistency در response format
- [ ] بررسی error responses
- [ ] بررسی payment redirect responses
- [ ] بررسی request validation - **مهم:** مطمئن شوید که date format `YYYY-MM-DD` و time format `H:i` است

#### 1.3. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`
**تغییرات لازم:**
- [ ] بررسی response format در `index()`, `show()`, `purchase()`
- [ ] بررسی error handling

#### 1.4. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php`
**تغییرات لازم:**
- [ ] بررسی response format
- [ ] بررسی error messages

#### 1.5. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php`
**تغییرات لازم:**
- [ ] بررسی response format
- [ ] بررسی reward structure در redeem response

#### 1.6. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
**تغییرات لازم:**
- [ ] بررسی response format در `list()`, `book()`, `checkAvailability()`
- [ ] بررسی validation errors

#### 1.7. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`
**تغییرات لازم:**
- [ ] بررسی response format در `listProducts()`, `createOrder()`
- [ ] بررسی payment processing responses

#### 1.8. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`
**فایل‌های React برای چک:**
- `/home/sepehr/Projects/6ammart-react/src/api-manage/another-formated-api/beautyApi.js` - متد `submitReview`
- `/home/sepehr/Projects/6ammart-react/src/components/home/module-wise-components/beauty/components/ReviewForm.js` - بررسی file upload format (اگر وجود دارد)

**تغییرات لازم:**
- [ ] بررسی response format
- [ ] بررسی file upload handling - **مهم:** React از FormData استفاده می‌کند
- [ ] بررسی attachment URLs در response - باید full URL باشد
- [ ] بررسی validation برای attachments (file types, size limits)

---

## 2. بررسی و بهبود Validation

### مشکل:
بررسی consistency در validation rules و error messages.

### تغییرات لازم:

#### 2.1. بررسی تمام Validation Rules
**فایل‌ها:**
- تمام Controller methods که `Validator::make()` استفاده می‌کنند

**تغییرات:**
- [ ] اطمینان از اینکه همه validation rules مناسب هستند
- [ ] بررسی custom validation messages
- [ ] بررسی translation keys

#### 2.2. بررسی Error Messages
**تغییرات:**
- [ ] اطمینان از استفاده از `translate()` برای همه error messages
- [ ] بررسی consistency در error codes
- [ ] بررسی error message format

**مثال:**
```php
// باید به این شکل باشد:
return $this->errorResponse([
    ['code' => 'validation', 'message' => translate('messages.field_required')],
]);
```

---

## 3. بهبود API Documentation

### مشکل:
برخی docblocks ممکن است ناقص باشند.

### تغییرات لازم:

#### 3.1. بررسی Docblocks
**فایل‌ها:**
- تمام Controller methods

**تغییرات:**
- [ ] اطمینان از وجود `@param` برای همه parameters
- [ ] اطمینان از وجود `@return` برای همه methods
- [ ] اضافه کردن `@queryParam` یا `@bodyParam` برای API documentation
- [ ] اضافه کردن `@response` examples

**مثال:**
```php
/**
 * Get salon details
 * 
 * @param int $id Salon ID
 * @return JsonResponse
 * 
 * @response 200 {
 *   "message": "Data retrieved successfully",
 *   "data": {
 *     "id": 1,
 *     "name": "Salon Name",
 *     ...
 *   }
 * }
 */
```

---

## 4. بررسی Pagination Format

### مشکل:
بررسی consistency در pagination (استفاده از offset یا page).

### تغییرات لازم:

#### 4.1. بررسی Pagination در تمام Controllers
**فایل‌های React برای چک:**
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetBookings.js` - بررسی pagination params
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetUserReviews.js` - بررسی pagination format

**فایل‌ها:**
- `BeautyBookingController.php` - `index()`
- `BeautyReviewController.php` - `index()`, `getSalonReviews()`
- `BeautyGiftCardController.php` - `index()`
- `BeautyConsultationController.php` - `list()`
- `BeautyRetailController.php` - `listProducts()`

**تغییرات:**
- [ ] بررسی اینکه همه از offset استفاده می‌کنند (مطابق با React) - **مهم:** React از `offset` و `limit` استفاده می‌کند
- [ ] بررسی محاسبه page number از offset
- [ ] اطمینان از اینکه pagination metadata شامل `total`, `per_page`, `current_page` است
- [ ] بررسی response structure - باید شامل `data`, `total`, `per_page`, `current_page`, `last_page` باشد

**مثال:**
```php
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;
$paginatedData = $query->paginate($limit, ['*'], 'page', $page);
```

---

## 5. بررسی Caching Strategy

### مشکل:
بررسی caching در endpointهای مناسب.

### تغییرات لازم:

#### 5.1. بررسی Caching در Controllers
**فایل‌ها:**
- `BeautySalonController.php` - `search()`, `popular()`, `topRated()`
- `BeautyCategoryController.php` - `list()`

**تغییرات:**
- [ ] بررسی TTL values (باید از config استفاده شود)
- [ ] بررسی cache keys (باید unique باشند)
- [ ] بررسی cache invalidation strategy

**مثال:**
```php
$ttl = config('beautybooking.cache.search_ttl', 300);
$cacheKey = 'beauty_search_' . md5(json_encode($params));
$data = Cache::remember($cacheKey, $ttl, function() { ... });
```

---

## 6. بررسی Performance

### مشکل:
بررسی N+1 queries و eager loading.

### تغییرات لازم:

#### 6.1. بررسی Eager Loading
**فایل‌ها:**
- تمام Controllers که relationships استفاده می‌کنند

**تغییرات:**
- [ ] بررسی استفاده از `with()` برای relationships
- [ ] بررسی N+1 query problems
- [ ] اضافه کردن eager loading در جایی که لازم است

**مثال:**
```php
$salons = $this->salon->with(['store', 'badges', 'services'])
    ->where('status', 1)
    ->get();
```

#### 6.2. بررسی Database Indexes
**تغییرات:**
- [ ] بررسی وجود indexes برای foreign keys
- [ ] بررسی وجود indexes برای frequently queried columns
- [ ] بررسی composite indexes

---

## 7. بررسی Security

### مشکل:
بررسی authorization و input validation.

### تغییرات لازم:

#### 7.1. بررسی Authorization
**فایل‌ها:**
- تمام Controllers که user-specific data برمی‌گردانند

**تغییرات:**
- [ ] اطمینان از اینکه user فقط به data خودش دسترسی دارد
- [ ] بررسی `authorizeBookingOwnership()` در BookingController
- [ ] بررسی authorization checks در همه methods

#### 7.2. بررسی Input Sanitization
**تغییرات:**
- [ ] بررسی SQL injection prevention
- [ ] بررسی XSS prevention
- [ ] بررسی file upload security

---

## 8. بررسی Error Handling

### مشکل:
بررسی consistency در error handling.

### تغییرات لازم:

#### 8.1. بررسی Try-Catch Blocks
**فایل‌ها:**
- تمام Controllers

**تغییرات:**
- [ ] اطمینان از وجود try-catch در operations مهم
- [ ] بررسی error logging
- [ ] بررسی error response format

**مثال:**
```php
try {
    // operation
} catch (\Exception $e) {
    \Log::error('Operation failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    return $this->errorResponse([
        ['code' => 'operation', 'message' => translate('messages.operation_failed')],
    ], 500);
}
```

---

## 9. بررسی Translation Keys

### مشکل:
بررسی وجود translation keys برای همه messages.

### تغییرات لازم:

#### 9.1. بررسی Translation Files
**فایل‌ها:**
- `Modules/BeautyBooking/Resources/lang/en/`
- `Modules/BeautyBooking/Resources/lang/fa/`

**تغییرات:**
- [ ] بررسی وجود همه translation keys
- [ ] اطمینان از consistency در naming
- [ ] اضافه کردن translation keys ناقص

---

## 10. بررسی Database Transactions

### مشکل:
بررسی استفاده از transactions در operations مهم.

### تغییرات لازم:

#### 10.1. بررسی Transactions
**فایل‌ها:**
- `BeautyBookingController.php` - `store()`, `cancel()`
- `BeautyPackageController.php` - `purchase()`
- `BeautyGiftCardController.php` - `purchase()`, `redeem()`
- `BeautyLoyaltyController.php` - `redeem()`
- `BeautyRetailController.php` - `createOrder()`

**تغییرات:**
- [ ] اطمینان از استفاده از `DB::transaction()` در operations مهم
- [ ] بررسی rollback در case of errors
- [ ] بررسی commit در success cases

---

## 11. بررسی File Upload Handling

### مشکل:
بررسی consistency در file upload handling.

### تغییرات لازم:

#### 11.1. بررسی File Uploads
**فایل‌ها:**
- `BeautyReviewController.php` - `store()` (attachments)

**تغییرات:**
- [ ] بررسی file validation
- [ ] بررسی file size limits
- [ ] بررسی file type validation
- [ ] بررسی storage path

---

## 12. بررسی Response Structure برای Frontend

### مشکل:
بررسی اینکه response structure با نیازهای React هماهنگ است.

### تغییرات لازم:

#### 12.1. بررسی Response Structure
**تغییرات:**
- [ ] اطمینان از اینکه همه list responses شامل `data` array هستند
- [ ] اطمینان از اینکه pagination metadata شامل `total`, `per_page`, `current_page` است
- [ ] بررسی nested data structure (مثل `salon.store.name`)

---

## 13. Checklist برای هر Controller

برای هر Controller زیر، این موارد را بررسی کنید:

### BeautySalonController
- [ ] Response format consistency
- [ ] Caching strategy
- [ ] Eager loading
- [ ] Error handling
- [ ] Documentation

### BeautyBookingController
- [ ] Response format consistency
- [ ] Transaction usage
- [ ] Authorization checks
- [ ] Payment handling
- [ ] Error handling
- [ ] Documentation

### BeautyPackageController
- [ ] Response format consistency
- [ ] Transaction usage
- [ ] Payment handling
- [ ] Error handling
- [ ] Documentation

### BeautyGiftCardController
- [ ] Response format consistency
- [ ] Transaction usage
- [ ] Code generation uniqueness
- [ ] Error handling
- [ ] Documentation

### BeautyLoyaltyController
- [ ] Response format consistency
- [ ] Transaction usage
- [ ] Reward calculation
- [ ] Error handling
- [ ] Documentation

### BeautyConsultationController
- [ ] Response format consistency
- [ ] Validation
- [ ] Error handling
- [ ] Documentation

### BeautyRetailController
- [ ] Response format consistency
- [ ] Transaction usage
- [ ] Stock validation
- [ ] Payment handling
- [ ] Error handling
- [ ] Documentation

### BeautyReviewController
- [ ] Response format consistency
- [ ] File upload handling
- [ ] Authorization checks
- [ ] Error handling
- [ ] Documentation

### BeautyCategoryController
- [ ] Response format consistency
- [ ] Caching strategy
- [ ] Error handling
- [ ] Documentation

---

## 14. فایل‌های خاص برای بررسی

### 14.1. `Modules/BeautyBooking/Traits/BeautyApiResponse.php`
**بررسی:**
- [ ] اطمینان از اینکه همه methods درست کار می‌کنند
- [ ] بررسی response structure
- [ ] بررسی error response format

### 14.2. `Modules/BeautyBooking/Routes/api/v1/customer/api.php`
**بررسی:**
- [ ] اطمینان از اینکه همه routes درست register شده‌اند
- [ ] بررسی middleware assignments
- [ ] بررسی rate limiting

### 14.3. `Modules/BeautyBooking/Config/config.php`
**بررسی:**
- [ ] بررسی cache TTL values
- [ ] بررسی default settings
- [ ] بررسی feature flags

---

## 15. تست و Validation

### تغییرات لازم:
- [ ] اجرای unit tests
- [ ] اجرای integration tests
- [ ] تست manual تمام endpointها
- [ ] بررسی response times
- [ ] بررسی memory usage

---

## 📝 دستورالعمل برای Cursor AI

برای هر بخش:
1. فایل مربوطه را باز کنید
2. کد موجود را بررسی کنید
3. تغییرات لازم را اعمال کنید
4. اطمینان حاصل کنید که کد قبلی کار می‌کند
5. تست کنید

**نکته مهم:** قبل از اعمال تغییرات، از کد فعلی backup بگیرید.

---

## 🔍 فایل‌های کلیدی برای بررسی

1. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautySalonController.php`
2. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
3. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`
4. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php`
5. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php`
6. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
7. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`
8. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`
9. `Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyCategoryController.php`
10. `Modules/BeautyBooking/Traits/BeautyApiResponse.php`
11. `Modules/BeautyBooking/Routes/api/v1/customer/api.php`
12. `Modules/BeautyBooking/Config/config.php`

---

## 📚 مرجع فایل‌های React برای هماهنگی

### فایل‌های API:
- `/home/sepehr/Projects/6ammart-react/src/api-manage/another-formated-api/beautyApi.js` - تمام API calls
- `/home/sepehr/Projects/6ammart-react/src/api-manage/ApiRoutes.js` - route definitions

### فایل‌های Hooks:
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetSalons.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetBookings.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useCreateBooking.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetPackages.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetGiftCards.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetLoyaltyPoints.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetConsultations.js`
- `/home/sepehr/Projects/6ammart-react/src/api-manage/hooks/react-query/beauty/useGetRetailProducts.js`

### فایل‌های کامپوننت (برای بررسی request format):
- `/home/sepehr/Projects/6ammart-react/src/components/home/module-wise-components/beauty/components/BookingForm.js`
- `/home/sepehr/Projects/6ammart-react/src/components/home/module-wise-components/beauty/components/ReviewForm.js` (اگر وجود دارد)

### نکات مهم برای هماهنگی:
1. **Request Format:** بررسی کنید که React چه format ارسال می‌کند
2. **Response Format:** مطمئن شوید که Laravel همان format را برمی‌گرداند
3. **Error Handling:** بررسی error response format
4. **Pagination:** React از `offset` و `limit` استفاده می‌کند

