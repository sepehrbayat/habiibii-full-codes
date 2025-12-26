# Flutter–Laravel Integration (Beauty Booking Customer)
# یکپارچه‌سازی فلاتر با لاراول (مشتری رزرو زیبایی)

## Scope & Base URL | دامنه و آدرس پایه
- Customer-facing Beauty Booking APIs only. | فقط APIهای مشتری رزرو زیبایی.
- Base path: `/api/v1/beautybooking`. | مسیر پایه: `/api/v1/beautybooking`.
- Example full URL: `https://your-domain.com/api/v1/beautybooking/salons/search`. | مثال کامل: `https://your-domain.com/api/v1/beautybooking/salons/search`.

## Auth & Headers | احراز هویت و هدرها
- Bearer token from main auth (`/api/v1/auth/login`). | توکن Bearer از ورود اصلی.
- Required headers: \
  `Authorization: Bearer <token>` (when logged in), `Accept: application/json`, `Content-Type: application/json` (or `multipart/form-data` for uploads). \
  هدرهای الزامی: `Authorization` (پس از ورود)، `Accept: application/json`, `Content-Type`.
- Optional: `Accept-Language` for localization. | اختیاری: `Accept-Language` برای بومی‌سازی.

## Error & Pagination | خطا و صفحه‌بندی
- Error shape: \
  ```json
  {"errors":[{"code":"validation","message":"The salon_id field is required."}]}
  ```
  ساختار خطا یکسان است.
- Common HTTP codes: 200/201 success, 401 auth, 403 validation/forbidden, 404 not found. | کدهای متداول.
- Pagination params: `page`, `per_page` (default 15) or `limit`/`offset`. Response includes `total`, `per_page`, `current_page`, `last_page`. | صفحه‌بندی استاندارد.

## Rate Limits (key) | محدودیت درخواست
- Public search: 120 rpm. | جستجوی عمومی: ۱۲۰ در دقیقه.
- Authenticated GETs: 60 rpm. | GETهای لاگین: ۶۰ در دقیقه.
- Critical ops: booking create 10 rpm; cancel 5 rpm; payment 5 rpm; package purchase 5 rpm; loyalty redeem 10 rpm. | عملیات حساس.

## Endpoint Map (Customer) | نقشه اندپوینت‌ها
- Public: \
  `GET /salons/search` (query: search, latitude, longitude, category_id, business_type, min_rating, radius) \
  `GET /salons/{id}` \
  `GET /salons/category-list` \
  `GET /salons/popular` \
  `GET /salons/top-rated` \
  `GET /salons/monthly-top-rated` \
  `GET /salons/trending-clinics` \
  `GET /reviews/{salon_id}`
- Authenticated: \
  `POST /availability/check` \
  `GET /services/{id}/suggestions` \
  Bookings: `POST /bookings`, `GET /bookings`, `GET /bookings/{id}`, `GET /bookings/{id}/conversation`, `PUT /bookings/{id}/reschedule`, `PUT /bookings/{id}/cancel` \
  Payment: `POST /payment` \
  Reviews: `POST /reviews`, `GET /reviews` \
  Packages: `GET /packages`, `GET /packages/{id}`, `POST /packages/{id}/purchase`, `GET /packages/{id}/status`, `GET /packages/{id}/usage-history` \
  Loyalty: `GET /loyalty/points`, `GET /loyalty/campaigns`, `POST /loyalty/redeem` \
  Gift cards: `POST /gift-card/purchase`, `POST /gift-card/redeem`, `GET /gift-card/list` \
  Retail: `GET /retail/products`, `POST /retail/orders`, `GET /retail/orders`, `GET /retail/orders/{id}` \
  Consultations: `GET /consultations/list`, `POST /consultations/book`, `POST /consultations/check-availability`

## Flutter Setup (Dio) | راه‌اندازی در فلاتر
```dart
final dio = Dio(BaseOptions(
  baseUrl: '$baseUrl/api/v1/beautybooking',
  connectTimeout: const Duration(seconds: 20),
  receiveTimeout: const Duration(seconds: 20),
  headers: {'Accept': 'application/json'},
))
  ..interceptors.add(InterceptorsWrapper(
    onRequest: (options, handler) {
      final token = authStore.token;
      if (token != null) options.headers['Authorization'] = 'Bearer $token';
      return handler.next(options);
    },
  ));
```

## Core Flows (Examples) | جریان‌های اصلی

### 1) Search Salons | جستجوی سالن
```dart
final res = await dio.get('/salons/search', queryParameters: {
  'search': 'hair',
  'latitude': 35.6892,
  'longitude': 51.3890,
  'radius': 15
});
final salons = res.data['data'] as List;
```

### 2) Check Availability | بررسی دسترسی
```dart
final res = await dio.post('/availability/check', data: {
  'salon_id': 1,
  'service_id': 10,
  'date': '2025-12-20',
  'staff_id': 3,
});
final slots = res.data['data']['available_slots'] as List;
```

### 3) Create Booking | ایجاد رزرو
```dart
final res = await dio.post('/bookings', data: {
  'salon_id': 1,
  'service_id': 10,
  'staff_id': 3,
  'booking_date': '2025-12-21',
  'booking_time': '10:00',
  'payment_method': 'cash_payment', // or wallet | digital_payment
});
final bookingId = res.data['data']['id'];
```

### 4) List Bookings (paginated) | لیست رزروها
```dart
final res = await dio.get('/bookings', queryParameters: {
  'status': 'upcoming',
  'page': 1,
  'per_page': 15,
});
final items = res.data['data'] as List;
final total = res.data['total'];
```

### 5) Cancel Booking | لغو رزرو
```dart
await dio.put('/bookings/$bookingId/cancel', data: {
  'cancellation_reason': 'Change of plans',
});
```

### 6) Submit Review (with attachments) | ثبت نظر همراه فایل
```dart
final form = FormData.fromMap({
  'booking_id': bookingId,
  'rating': 5,
  'comment': 'Great service!',
  'attachments': [
    await MultipartFile.fromFile('/path/to/photo.jpg',
        filename: 'photo.jpg', contentType: MediaType('image', 'jpeg'))
  ],
});
await dio.post('/reviews', data: form,
  options: Options(contentType: 'multipart/form-data'));
```

### 7) Packages & Loyalty | پکیج و وفاداری
- List packages: `GET /packages` (filters: salon_id, service_id, per_page). \
  دریافت لیست پکیج‌ها.
- Buy package: `POST /packages/{id}/purchase` with `payment_method` (`wallet`, `digital_payment`, `cash_payment`). \
  خرید پکیج.
- Loyalty points: `GET /loyalty/points`, campaigns `GET /loyalty/campaigns`, redeem `POST /loyalty/redeem`. \
  امتیاز و کمپین وفاداری.

### 8) Gift Cards & Retail | کارت هدیه و خرده‌فروشی
- Buy gift card: `POST /gift-card/purchase` (`salon_id`, `amount`, `expires_at`). \
  خرید کارت هدیه.
- Redeem gift card: `POST /gift-card/redeem` (`code`). \
  استفاده از کارت هدیه.
- Retail order: `POST /retail/orders` with `products` array and `payment_method`. \
  سفارش خرده‌فروشی.

## Testing Tips | نکات تست
- Use real tokens from auth endpoints; handle 401 by re-login. | از توکن واقعی استفاده کنید.
- Respect throttling; backoff on 429. | به محدودیت‌ها احترام بگذارید.
- Validate time slot availability before creating bookings. | قبل از رزرو، دسترسی زمان را بررسی کنید.
- For uploads, always send `multipart/form-data`. | برای آپلود از `multipart/form-data` استفاده کنید.

