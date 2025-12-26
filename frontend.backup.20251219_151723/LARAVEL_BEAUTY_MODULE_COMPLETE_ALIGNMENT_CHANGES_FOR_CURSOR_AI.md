# Laravel Beauty Module - Complete Alignment Changes for Cursor AI

این سند شامل تمام تغییرات لازم در سمت Laravel برای هماهنگ‌سازی کامل ماژول Beauty با فرانت‌اند React است.

## فهرست مطالب

1. [بررسی کلی](#بررسی-کلی)
2. [تغییرات API Routes](#تغییرات-api-routes)
3. [تغییرات Controllers](#تغییرات-controllers)
4. [تغییرات Response Format](#تغییرات-response-format)
5. [تغییرات Validation](#تغییرات-validation)
6. [تغییرات Pagination](#تغییرات-pagination)
7. [تغییرات Error Handling](#تغییرات-error-handling)
8. [ویژگی‌های موجود در Backend که در Frontend استفاده نشده](#ویژگی‌های-موجود-در-backend-که-در-frontend-استفاده-نشده)
9. [ویژگی‌های مورد نیاز Frontend که در Backend وجود ندارد](#ویژگی‌های-مورد-نیاز-frontend-که-در-backend-وجود-ندارد)

---

## بررسی کلی

### وضعیت فعلی
- Backend Laravel در مسیر: `/home/sepehr/Projects/6ammart-laravel/Modules/BeautyBooking/`
- Frontend React در مسیر: `/home/sepehr/Projects/6ammart-react/`
- API Base Path: `/api/v1/beautybooking/`
- Vendor API Base Path: `/api/v1/beautybooking/vendor/`

### مشکلات اصلی شناسایی شده
1. **عدم هماهنگی در Response Format**: برخی endpointها از `simpleListResponse` استفاده می‌کنند در حالی که React انتظار `listResponse` دارد
2. **تفاوت در Pagination**: Backend از `offset` استفاده می‌کند اما React گاهی `per_page` و `limit` را می‌فرستد
3. **تفاوت در Payment Method**: React گاهی `online` می‌فرستد که باید به `digital_payment` تبدیل شود
4. **Missing Fields در Response**: برخی فیلدهای مورد نیاز React در response وجود ندارد
5. **Missing Endpoints**: برخی endpointهای مورد نیاز React وجود ندارد

---

## تغییرات API Routes

### 1. Customer API Routes (`/Modules/BeautyBooking/Routes/api/v1/customer/api.php`)

#### تغییرات مورد نیاز:

**الف) Route برای Package Status:**
```php
// در حال حاضر این route در BeautyBookingController است اما باید در PackageController باشد
// فعلاً: Route::get('packages/{id}/status', [BeautyBookingController::class, 'getPackageStatus'])
// باید: Route::get('packages/{id}/status', [BeautyPackageController::class, 'getPackageStatus'])
```

**ب) Route برای Service Suggestions:**
```php
// این route موجود است اما باید مطمئن شویم که middleware درست است
Route::get('services/{id}/suggestions', [BeautyBookingController::class, 'getServiceSuggestions'])
    ->middleware('throttle:60,1')
    ->name('services.suggestions');
```

**ج) Route برای Category List:**
```php
// این route موجود است و درست است
Route::get('salons/category-list', [BeautyCategoryController::class, 'list'])
    ->name('salons.category-list');
```

---

## تغییرات Controllers

### 1. BeautySalonController (`/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautySalonController.php`)

#### تغییرات مورد نیاز:

**الف) متد `search()`:**
- ✅ استفاده از `simpleListResponse` - درست است
- ✅ Cache implementation - درست است
- ⚠️ باید مطمئن شویم که فیلد `image` در response کامل است

**ب) متد `show()`:**
- ✅ استفاده از `successResponse` - درست است
- ⚠️ باید مطمئن شویم که تمام relationships لود می‌شوند:
  - `store` ✅
  - `services` ✅
  - `staff` ✅
  - `badges` ✅
  - `reviews` ✅

**ج) متد `formatSalonForApi()`:**
```php
// باید این فیلدها را اضافه کنیم:
private function formatSalonForApi(BeautySalon $salon, bool $includeDetails = false): array
{
    $data = [
        // ... existing fields ...
        'phone' => $salon->store->phone ?? null, // اضافه شود
        'email' => $salon->store->email ?? null, // اضافه شود
        'opening_time' => $salon->store->opening_time ?? null, // اضافه شود
        'closing_time' => $salon->store->closing_time ?? null, // اضافه شود
        'is_open' => $this->isSalonOpen($salon), // اضافه شود - متد جدید
        'distance' => null, // اگر latitude/longitude در request باشد، محاسبه شود
    ];
    
    if ($includeDetails) {
        $data['services'] = $salon->services->map(function($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'duration_minutes' => $service->duration_minutes,
                'image' => $service->image ? asset('storage/' . $service->image) : null,
            ];
        });
        
        $data['staff'] = $salon->staff->map(function($staff) {
            return [
                'id' => $staff->id,
                'name' => $staff->name,
                'avatar' => $staff->avatar ? asset('storage/' . $staff->avatar) : null,
                'specializations' => $staff->specializations ?? [],
            ];
        });
    }
    
    return $data;
}

// متد جدید برای بررسی باز بودن سالن
private function isSalonOpen(BeautySalon $salon): bool
{
    $now = now();
    $dayOfWeek = strtolower($now->format('l')); // monday, tuesday, etc.
    $workingHours = $salon->working_hours ?? [];
    
    if (!isset($workingHours[$dayOfWeek])) {
        return false;
    }
    
    $dayHours = $workingHours[$dayOfWeek];
    if (!isset($dayHours['open']) || !isset($dayHours['close'])) {
        return false;
    }
    
    $currentTime = $now->format('H:i');
    return $currentTime >= $dayHours['open'] && $currentTime <= $dayHours['close'];
}
```

### 2. BeautyBookingController (`/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`)

#### تغییرات مورد نیاز:

**الف) متد `store()`:**
- ✅ تبدیل `online` به `digital_payment` - درست است
- ⚠️ باید validation برای `payment_gateway` اضافه شود:
```php
$validator = Validator::make($request->all(), [
    // ... existing validations ...
    'payment_gateway' => 'nullable|string|in:stripe,paypal,razorpay', // اضافه شود
    'callback_url' => 'nullable|url', // اضافه شود
    'payment_platform' => 'nullable|string|in:web,mobile', // اضافه شود
]);
```

**ب) متد `index()`:**
- ✅ استفاده از `offset` و `limit` - درست است
- ⚠️ باید فیلتر `type` را بهتر handle کنیم:
```php
->when($request->filled('type'), function ($query) use ($request) {
    if ($request->type === 'upcoming') {
        $query->upcoming();
    } elseif ($request->type === 'past') {
        $query->past();
    } elseif ($request->type === 'cancelled') {
        $query->where('status', 'cancelled');
    }
})
```

**ج) متد `formatBookingForApi()`:**
- ⚠️ باید فیلدهای بیشتری اضافه شود:
```php
private function formatBookingForApi(BeautyBooking $booking, bool $includeDetails = false): array
{
    $data = [
        // ... existing fields ...
        'salon' => [
            'id' => $booking->salon->id ?? null,
            'name' => $booking->salon->store->name ?? '',
            'address' => $booking->salon->store->address ?? null,
            'phone' => $booking->salon->store->phone ?? null,
            'image' => $booking->salon->store->image ? asset('storage/' . $booking->salon->store->image) : null,
        ],
        'can_cancel' => $booking->canCancel(), // اضافه شود
        'can_reschedule' => $booking->canReschedule(), // اگر متد وجود دارد
        'cancellation_deadline' => $booking->booking_date_time ? 
            $booking->booking_date_time->subHours(24)->format('Y-m-d H:i:s') : null, // اضافه شود
    ];
    
    return $data;
}
```

**د) متد `getPackageStatus()`:**
- ⚠️ این متد باید به `BeautyPackageController` منتقل شود:
```php
// در BeautyPackageController.php
public function getPackageStatus(Request $request, int $id): JsonResponse
{
    // کد موجود از BeautyBookingController را اینجا منتقل کنید
}
```

### 3. BeautyPackageController (`/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`)

#### تغییرات مورد نیاز:

**الف) متد `index()`:**
- ✅ استفاده از `per_page` و `limit` - درست است
- ⚠️ باید response format را یکسان کنیم:
```php
public function index(Request $request)
{
    // ... existing code ...
    
    // باید مطمئن شویم که response شامل این فیلدهاست:
    $formatted = $packages->getCollection()->map(function ($package) {
        return [
            'id' => $package->id,
            'name' => $package->name,
            'description' => $package->description,
            'sessions_count' => $package->sessions_count,
            'total_price' => $package->total_price,
            'validity_days' => $package->validity_days,
            'salon' => [
                'id' => $package->salon->id ?? null,
                'name' => $package->salon->store->name ?? '',
            ],
            'service' => [
                'id' => $package->service->id ?? null,
                'name' => $package->service->name ?? '',
            ],
            'image' => $package->image ? asset('storage/' . $package->image) : null,
        ];
    });
    
    $packages->setCollection($formatted->values());
    return $this->listResponse($packages, 'messages.data_retrieved_successfully');
}
```

**ب) متد `purchase()`:**
- ✅ تبدیل `online` به `digital_payment` - درست است
- ⚠️ باید response را کامل کنیم:
```php
return $this->successResponse('package_purchased_successfully', [
    'package_id' => $package->id,
    'package_name' => $package->name,
    'sessions_count' => $package->sessions_count,
    'total_price' => $package->total_price,
    'payment_status' => $request->payment_method === 'wallet' || $request->payment_method === 'cash_payment' 
        ? 'paid' 
        : 'pending',
    'usage_records' => $usageRecords->map(function($usage) {
        return [
            'session_number' => $usage->session_number,
            'status' => $usage->status,
        ];
    }),
], 201);
```

**ج) اضافه کردن متد `getPackageStatus()`:**
```php
// این متد باید از BeautyBookingController به اینجا منتقل شود
public function getPackageStatus(Request $request, int $id): JsonResponse
{
    // کد موجود از BeautyBookingController::getPackageStatus
}
```

### 4. BeautyGiftCardController (`/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php`)

#### تغییرات مورد نیاز:

**الف) متد `purchase()`:**
- ✅ تبدیل `online` به `digital_payment` - درست است
- ⚠️ باید response را کامل کنیم:
```php
return $this->successResponse('gift_card_purchased_successfully', [
    'gift_card' => [
        'id' => $giftCard->id,
        'code' => $giftCard->code,
        'amount' => $giftCard->amount,
        'expires_at' => $giftCard->expires_at->format('Y-m-d'),
        'status' => $giftCard->status,
        'salon_id' => $giftCard->salon_id,
        'salon_name' => $giftCard->salon->store->name ?? null, // اضافه شود
    ],
], 201);
```

**ب) متد `index()`:**
- ⚠️ باید pagination را اضافه کنیم:
```php
public function index(Request $request): JsonResponse
{
    $limit = $request->get('limit', 25);
    $offset = $request->get('offset', 0);
    $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

    $giftCards = $this->giftCard->where('purchased_by', $request->user()->id)
        ->with(['salon.store'])
        ->latest()
        ->paginate($limit, ['*'], 'page', $page);

    $formatted = $giftCards->getCollection()->map(function ($giftCard) {
        return [
            'id' => $giftCard->id,
            'code' => $giftCard->code,
            'amount' => $giftCard->amount,
            'expires_at' => $giftCard->expires_at->format('Y-m-d'),
            'status' => $giftCard->status,
            'salon' => $giftCard->salon ? [
                'id' => $giftCard->salon->id,
                'name' => $giftCard->salon->store->name ?? '',
            ] : null,
        ];
    });

    $giftCards->setCollection($formatted->values());
    return $this->listResponse($giftCards);
}
```

### 5. BeautyLoyaltyController (`/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php`)

#### تغییرات مورد نیاز:

**الف) متد `getCampaigns()`:**
- ✅ استفاده از `per_page` و `limit` - درست است
- ⚠️ باید response format را کامل کنیم:
```php
$formatted = $campaigns->getCollection()->map(function ($campaign) {
    return [
        'id' => $campaign->id,
        'name' => $campaign->name,
        'description' => $campaign->description,
        'type' => $campaign->type,
        'rules' => $campaign->rules,
        'start_date' => $campaign->start_date ? $campaign->start_date->format('Y-m-d') : null,
        'end_date' => $campaign->end_date ? $campaign->end_date->format('Y-m-d') : null,
        'salon' => $campaign->salon ? [
            'id' => $campaign->salon->id,
            'name' => $campaign->salon->store->name ?? '',
        ] : null,
        'is_active' => $campaign->isActive(),
    ];
});

$campaigns->setCollection($formatted->values());
return $this->listResponse($campaigns, 'messages.data_retrieved_successfully');
```

**ب) متد `redeem()`:**
- ⚠️ باید response را کامل کنیم:
```php
return $this->successResponse('points_redeemed_successfully', [
    'campaign_id' => $campaign->id,
    'campaign_name' => $campaign->name,
    'points_redeemed' => $request->points,
    'remaining_points' => $loyaltyService->getTotalPoints($user->id, $campaign->salon_id),
    'reward' => $reward,
    'wallet_balance' => $user->fresh()->wallet_balance, // اگر wallet_credit یا cashback باشد
]);
```

### 6. BeautyConsultationController (`/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`)

#### تغییرات مورد نیاز:

**الف) متد `list()`:**
- ✅ استفاده از `offset` و `limit` - درست است
- ✅ Response format - درست است

**ب) متد `book()`:**
- ✅ تبدیل `online` به `digital_payment` - درست است
- ⚠️ باید response را کامل کنیم:
```php
return $this->successResponse('consultation_booked_successfully', [
    'id' => $booking->id,
    'booking_reference' => $booking->booking_reference,
    'status' => $booking->status,
    'consultation' => [
        'id' => $consultation->id,
        'name' => $consultation->name,
        'price' => $consultation->price,
    ],
    'booking_date' => $booking->booking_date->format('Y-m-d'),
    'booking_time' => $booking->booking_time,
], 201);
```

### 7. BeautyRetailController (`/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`)

#### تغییرات مورد نیاز:

**الف) متد `listProducts()`:**
- ✅ استفاده از `offset` و `limit` - درست است
- ⚠️ باید فیلتر `category_id` را اضافه کنیم (در حال حاضر فقط `category` string است):
```php
if ($request->filled('category_id')) {
    $query->where('category_id', $request->category_id);
}
```

**ب) متد `createOrder()`:**
- ✅ تبدیل `online` به `digital_payment` - درست است
- ⚠️ باید response را کامل کنیم:
```php
return $this->successResponse('order_created_successfully', [
    'id' => $order->id,
    'order_reference' => $order->order_reference ?? 'RT-' . $order->id, // اضافه شود
    'total_amount' => $order->total_amount,
    'payment_status' => $order->payment_status,
    'status' => $order->status,
    'products' => $order->products->map(function($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'quantity' => $product->pivot->quantity,
            'price' => $product->price,
        ];
    }),
], 201);
```

### 8. BeautyReviewController (`/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`)

#### تغییرات مورد نیاز:

**الف) متد `store()`:**
- ✅ Handle FormData - درست است
- ✅ Upload attachments - درست است
- ⚠️ باید response را کامل کنیم:
```php
$reviewData = [
    'id' => $review->id,
    'booking_id' => $review->booking_id,
    'rating' => $review->rating,
    'comment' => $review->comment,
    'status' => $review->status,
    'attachments' => array_map(function ($path) {
        return asset('storage/' . $path);
    }, $attachments),
    'salon' => [
        'id' => $booking->salon->id ?? null,
        'name' => $booking->salon->store->name ?? '',
    ],
    'service' => [
        'id' => $booking->service->id ?? null,
        'name' => $booking->service->name ?? '',
    ],
    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
];
```

**ب) متد `index()`:**
- ✅ استفاده از `offset` و `limit` - درست است
- ✅ Response format - درست است

**ج) متد `getSalonReviews()`:**
- ✅ استفاده از `offset` و `limit` - درست است
- ⚠️ باید response format را کامل کنیم:
```php
$formatted = $reviews->getCollection()->map(function ($review) {
    return [
        'id' => $review->id,
        'rating' => $review->rating,
        'comment' => $review->comment,
        'attachments' => $review->attachments ? array_map(function($path) {
            return asset('storage/' . $path);
        }, $review->attachments) : [],
        'user' => [
            'id' => $review->user->id ?? null,
            'name' => $review->user->f_name . ' ' . $review->user->l_name ?? 'Anonymous',
            'image' => $review->user->image ? asset('storage/' . $review->user->image) : null,
        ],
        'service' => [
            'id' => $review->service->id ?? null,
            'name' => $review->service->name ?? '',
        ],
        'staff' => $review->staff ? [
            'id' => $review->staff->id,
            'name' => $review->staff->name,
        ] : null,
        'created_at' => $review->created_at->format('Y-m-d H:i:s'),
    ];
});

$reviews->setCollection($formatted->values());
return $this->listResponse($reviews);
```

---

## تغییرات Response Format

### مشکل اصلی:
React انتظار دارد که تمام responseها format یکسانی داشته باشند. باید از `BeautyApiResponse` trait به درستی استفاده کنیم.

### راه حل:

**الف) استفاده از `listResponse()` برای لیست‌ها:**
```php
// به جای:
return $this->simpleListResponse($items);

// استفاده کنیم:
return $this->listResponse($paginatedCollection, 'messages.data_retrieved_successfully');
```

**ب) استفاده از `successResponse()` برای single items:**
```php
return $this->successResponse('messages.data_retrieved_successfully', $formattedData);
```

**ج) استفاده از `errorResponse()` برای errors:**
```php
return $this->errorResponse([
    ['code' => 'validation', 'message' => translate('messages.field_required')]
], 400);
```

---

## تغییرات Pagination

### مشکل:
React گاهی `per_page` و گاهی `limit` می‌فرستد. باید هر دو را support کنیم.

### راه حل:
```php
$limit = $request->get('per_page', $request->get('limit', 25));
$offset = $request->get('offset', 0);
$page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

$items = Model::query()
    ->paginate($limit, ['*'], 'page', $page);
```

---

## تغییرات Error Handling

### مشکل:
برخی error messages به درستی translate نمی‌شوند.

### راه حل:
```php
// به جای:
return $this->errorResponse([
    ['code' => 'error', 'message' => 'Error message']
]);

// استفاده کنیم:
return $this->errorResponse([
    ['code' => 'error', 'message' => translate('messages.error_key')]
]);
```

---

## ویژگی‌های موجود در Backend که در Frontend استفاده نشده

### 1. Service Suggestions (Cross-selling)
- **Backend**: `GET /api/v1/beautybooking/services/{id}/suggestions`
- **Status**: ✅ موجود در Backend
- **Frontend**: ⚠️ Hook موجود است (`useGetServiceSuggestions`) اما استفاده نشده
- **Action**: باید در Frontend استفاده شود

### 2. Monthly Top Rated Salons
- **Backend**: `GET /api/v1/beautybooking/salons/monthly-top-rated`
- **Status**: ✅ موجود در Backend و Frontend
- **Action**: ✅ درست کار می‌کند

### 3. Trending Clinics
- **Backend**: `GET /api/v1/beautybooking/salons/trending-clinics`
- **Status**: ✅ موجود در Backend و Frontend
- **Action**: ✅ درست کار می‌کند

### 4. Package Status
- **Backend**: `GET /api/v1/beautybooking/packages/{id}/status`
- **Status**: ✅ موجود در Backend
- **Frontend**: ✅ Hook موجود است (`useGetPackageStatus`)
- **Action**: باید در Frontend استفاده شود

### 5. Booking Conversation
- **Backend**: `GET /api/v1/beautybooking/bookings/{id}/conversation`
- **Status**: ✅ موجود در Backend
- **Frontend**: ✅ Hook موجود است (`useGetBookingConversation`)
- **Action**: باید در Frontend استفاده شود

---

## ویژگی‌های مورد نیاز Frontend که در Backend وجود ندارد

### 1. Salon Search با فیلترهای پیشرفته
- **Frontend نیاز دارد**: فیلتر بر اساس `price_range`, `distance`, `amenities`
- **Backend**: ⚠️ فقط فیلترهای پایه دارد
- **Action**: باید فیلترهای بیشتری اضافه شود

### 2. Booking Reschedule
- **Frontend نیاز دارد**: امکان تغییر زمان رزرو
- **Backend**: ❌ وجود ندارد
- **Action**: باید endpoint جدید اضافه شود:
```php
// در BeautyBookingController
public function reschedule(Request $request, int $id): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'booking_date' => 'required|date|after_or_equal:today',
        'booking_time' => 'required|date_format:H:i',
    ]);

    $booking = $this->booking->where('user_id', $request->user()->id)->findOrFail($id);
    
    if (!$booking->canReschedule()) {
        return $this->errorResponse([
            ['code' => 'booking', 'message' => translate('cannot_reschedule_booking')]
        ]);
    }

    // Logic for rescheduling
    $booking = $this->bookingService->rescheduleBooking($booking, $request->all());

    return $this->successResponse('booking_rescheduled_successfully', $this->formatBookingForApi($booking));
}
```

### 3. Booking History با فیلترهای بیشتر
- **Frontend نیاز دارد**: فیلتر بر اساس `date_range`, `service_type`, `staff_id`
- **Backend**: ⚠️ فقط فیلتر `status` و `type` دارد
- **Action**: باید فیلترهای بیشتری اضافه شود

### 4. Package Usage History
- **Frontend نیاز دارد**: تاریخچه استفاده از پکیج
- **Backend**: ⚠️ در `getPackageStatus` موجود است اما endpoint جداگانه ندارد
- **Action**: باید endpoint جدید اضافه شود:
```php
// در BeautyPackageController
public function getUsageHistory(Request $request, int $id): JsonResponse
{
    $userId = $request->user()->id;
    $package = BeautyPackage::findOrFail($id);
    
    $usages = BeautyPackageUsage::where('package_id', $id)
        ->where('user_id', $userId)
        ->with('booking')
        ->orderBy('session_number', 'asc')
        ->get();
    
    return $this->successResponse('messages.data_retrieved_successfully', [
        'package' => $package,
        'usages' => $usages->map(function($usage) {
            return [
                'session_number' => $usage->session_number,
                'status' => $usage->status,
                'used_at' => $usage->used_at ? $usage->used_at->format('Y-m-d H:i:s') : null,
                'booking' => $usage->booking ? [
                    'id' => $usage->booking->id,
                    'booking_reference' => $usage->booking->booking_reference,
                    'booking_date' => $usage->booking->booking_date->format('Y-m-d'),
                ] : null,
            ];
        }),
    ]);
}
```

### 5. Retail Order Details
- **Frontend نیاز دارد**: جزئیات کامل سفارش خرده‌فروشی
- **Backend**: ❌ endpoint برای دریافت جزئیات وجود ندارد
- **Action**: باید endpoint جدید اضافه شود:
```php
// در BeautyRetailController
public function getOrderDetails(Request $request, int $id): JsonResponse
{
    $order = BeautyRetailOrder::where('user_id', $request->user()->id)
        ->with(['products', 'salon.store'])
        ->findOrFail($id);
    
    return $this->successResponse('messages.data_retrieved_successfully', [
        'id' => $order->id,
        'order_reference' => $order->order_reference ?? 'RT-' . $order->id,
        'total_amount' => $order->total_amount,
        'payment_status' => $order->payment_status,
        'status' => $order->status,
        'products' => $order->products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => $product->pivot->quantity,
                'price' => $product->price,
                'subtotal' => $product->pivot->quantity * $product->price,
            ];
        }),
        'salon' => [
            'id' => $order->salon->id,
            'name' => $order->salon->store->name ?? '',
            'address' => $order->salon->store->address ?? '',
        ],
        'shipping_address' => $order->shipping_address,
        'shipping_phone' => $order->shipping_phone,
        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
    ]);
}
```

### 6. Retail Order List
- **Frontend نیاز دارد**: لیست سفارشات خرده‌فروشی کاربر
- **Backend**: ❌ endpoint وجود ندارد
- **Action**: باید endpoint جدید اضافه شود:
```php
// در BeautyRetailController
public function getOrders(Request $request): JsonResponse
{
    $limit = $request->get('limit', 25);
    $offset = $request->get('offset', 0);
    $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

    $orders = BeautyRetailOrder::where('user_id', $request->user()->id)
        ->with(['salon.store'])
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->latest()
        ->paginate($limit, ['*'], 'page', $page);

    $formatted = $orders->getCollection()->map(function ($order) {
        return [
            'id' => $order->id,
            'order_reference' => $order->order_reference ?? 'RT-' . $order->id,
            'total_amount' => $order->total_amount,
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'salon' => [
                'id' => $order->salon->id,
                'name' => $order->salon->store->name ?? '',
            ],
            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
        ];
    });

    $orders->setCollection($formatted->values());
    return $this->listResponse($orders);
}
```

---

## خلاصه تغییرات ضروری

### اولویت بالا (Critical):
1. ✅ اضافه کردن فیلدهای missing در responseها
2. ✅ یکسان‌سازی response format
3. ✅ اضافه کردن endpoint برای Retail Order Details و List
4. ✅ اضافه کردن endpoint برای Package Usage History
5. ✅ اضافه کردن endpoint برای Booking Reschedule

### اولویت متوسط (Important):
1. ⚠️ بهبود فیلترها در Salon Search
2. ⚠️ بهبود فیلترها در Booking History
3. ⚠️ اضافه کردن فیلد `is_open` در Salon response
4. ⚠️ اضافه کردن فیلد `distance` در Salon response (اگر coordinates موجود باشد)

### اولویت پایین (Nice to have):
1. 📝 بهبود error messages
2. 📝 اضافه کردن caching برای endpointهای پر استفاده
3. 📝 اضافه کردن rate limiting بهتر

---

## فایل‌های مورد نیاز برای تغییر

### Controllers:
1. `/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautySalonController.php`
2. `/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyBookingController.php`
3. `/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyPackageController.php`
4. `/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyGiftCardController.php`
5. `/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyLoyaltyController.php`
6. `/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyConsultationController.php`
7. `/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyRetailController.php`
8. `/Modules/BeautyBooking/Http/Controllers/Api/Customer/BeautyReviewController.php`

### Routes:
1. `/Modules/BeautyBooking/Routes/api/v1/customer/api.php`

### Services (اگر نیاز باشد):
1. `/Modules/BeautyBooking/Services/BeautyBookingService.php` (برای reschedule)
2. `/Modules/BeautyBooking/Services/BeautyRankingService.php` (برای فیلترهای پیشرفته)

---

## نکات مهم برای پیاده‌سازی

1. **همیشه از `translate()` برای error messages استفاده کنید**
2. **همیشه از `BeautyApiResponse` trait استفاده کنید**
3. **همیشه pagination را با `offset` و `limit` handle کنید**
4. **همیشه `online` را به `digital_payment` تبدیل کنید**
5. **همیشه relationships را eager load کنید تا N+1 query نداشته باشیم**
6. **همیشه از transactions برای عملیات مهم استفاده کنید**
7. **همیشه validation را در controller انجام دهید**

---

## تست‌های مورد نیاز

بعد از اعمال تغییرات، باید این تست‌ها را انجام دهید:

1. ✅ تست تمام endpointهای customer API
2. ✅ تست pagination با `offset` و `limit`
3. ✅ تست response format
4. ✅ تست error handling
5. ✅ تست payment methods (wallet, digital_payment, cash_payment)
6. ✅ تست validation rules
7. ✅ تست relationships loading

---

**تاریخ ایجاد**: 2025-01-05
**آخرین به‌روزرسانی**: 2025-01-05
**نسخه**: 1.0

