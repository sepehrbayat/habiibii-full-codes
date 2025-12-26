# Vendor API Documentation

Complete API documentation for Beauty Booking vendor endpoints.

## Table of Contents

1. [Authentication](#authentication)
2. [Base URL](#base-url)
3. [Response Format](#response-format)
4. [Error Handling](#error-handling)
5. [Pagination](#pagination)
6. [Booking Management](#booking-management)
7. [Staff Management](#staff-management)
8. [Service Management](#service-management)
9. [Calendar Management](#calendar-management)
10. [Salon Registration & Profile](#salon-registration--profile)
11. [Retail Management](#retail-management)
12. [Subscription Management](#subscription-management)
13. [Finance & Reports](#finance--reports)
14. [Badge Management](#badge-management)
15. [Package Management](#package-management)
16. [Gift Card Management](#gift-card-management)
17. [Loyalty Campaign Management](#loyalty-campaign-management)

---

## Authentication

### Overview
All vendor API endpoints require vendor authentication via `vendor.api` middleware.

### Authentication Method
Include vendor authentication token in request headers:
```
Authorization: Bearer {vendor_token}
```

### Middleware
- **Name:** `vendor.api`
- **Purpose:** Validates vendor authentication and sets `$request->vendor`

### Access Control
- Vendors can only access their own salon data
- Authorization checks ensure data isolation between vendors

---

## Base URL

```
/api/v1/beautybooking/vendor/
```

All endpoints are prefixed with this base URL.

---

## Response Format

### Success Response
```json
{
  "message": "translated_message",
  "data": {...}
}
```

### Paginated Response
```json
{
  "message": "translated_message",
  "data": [...],
  "total": 100,
  "per_page": 25,
  "current_page": 1,
  "last_page": 4
}
```

### Error Response
```json
{
  "errors": [
    {
      "code": "error_code",
      "message": "error_message"
    }
  ]
}
```

---

## Error Handling

### HTTP Status Codes
- **200 OK:** Successful GET, PUT requests
- **201 Created:** Successful POST requests (resource created)
- **400 Bad Request:** Invalid request data
- **401 Unauthorized:** Authentication required
- **403 Forbidden:** Authorization failed or validation error
- **404 Not Found:** Resource not found
- **500 Internal Server Error:** Server error

### Error Codes
- `validation`: Validation error
- `booking`: Booking-related error
- `staff`: Staff-related error
- `service`: Service-related error
- `calendar`: Calendar-related error
- `salon`: Salon-related error
- `payment`: Payment-related error
- `subscription`: Subscription-related error
- `finance`: Finance-related error
- `not_found`: Resource not found
- `unauthorized`: Unauthorized access

---

## Pagination

### Parameters
- `limit` (integer, default: 25): Number of items per page
- `offset` (integer, default: 0): Page offset

### Usage
```
GET /api/v1/beautybooking/vendor/bookings/list/all?limit=25&offset=0
```

### Response
Paginated responses include:
- `data`: Array of items
- `total`: Total number of items
- `per_page`: Items per page
- `current_page`: Current page number
- `last_page`: Last page number

---

## Booking Management

### 1. List Bookings

**Endpoint:** `GET /bookings/list/{all}`

**Description:**  
List vendor bookings with optional status filtering  
لیست رزروهای فروشنده با فیلتر وضعیت اختیاری

**Path Parameters:**
- `all` (string): Status filter (`pending`, `confirmed`, `completed`, `cancelled`) or `all` for all bookings

**Query Parameters:**
- `status` (string, optional): Alternative status filter
- `date_from` (date, optional): Filter bookings from this date (format: YYYY-MM-DD)
- `date_to` (date, optional): Filter bookings to this date (format: YYYY-MM-DD)
- `limit` (integer, optional, default: 25): Items per page
- `offset` (integer, optional, default: 0): Page offset

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 100001,
      "booking_reference": "BB-100001",
      "user": {
        "id": 1,
        "f_name": "John",
        "l_name": "Doe"
      },
      "service": {
        "id": 1,
        "name": "Haircut"
      },
      "staff": {
        "id": 1,
        "name": "Jane Smith"
      },
      "status": "confirmed",
      "booking_date": "2024-01-20",
      "booking_time": "10:00",
      "total_amount": 100000
    }
  ],
  "total": 50,
  "per_page": 25,
  "current_page": 1,
  "last_page": 2
}
```

---

### 2. Get Booking Details

**Endpoint:** `GET /bookings/details`

**Description:**  
Get detailed information about a specific booking  
دریافت اطلاعات تفصیلی یک رزرو خاص

**Query Parameters:**
- `booking_id` (integer, required): Booking ID

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": {
    "id": 100001,
    "booking_reference": "BB-100001",
    "user": {...},
    "salon": {...},
    "service": {...},
    "staff": {...},
    "status": "confirmed",
    "payment_status": "paid",
    "booking_date": "2024-01-20",
    "booking_time": "10:00",
    "total_amount": 100000,
    "review": {...}
  }
}
```

**Error Response (404):**
```json
{
  "errors": [
    {
      "code": "not_found",
      "message": "Booking not found"
    }
  ]
}
```

---

### 3. Confirm Booking

**Endpoint:** `PUT /bookings/confirm`

**Description:**  
Confirm a pending booking  
تأیید یک رزرو در انتظار

**Request Body:**
```json
{
  "booking_id": 100001
}
```

**Rate Limit:** 10 requests per minute

**Response Example:**
```json
{
  "message": "Booking confirmed successfully",
  "data": {
    "id": 100001,
    "booking_reference": "BB-100001",
    "status": "confirmed",
    "confirmed_at": "2024-01-15 10:00:00"
  }
}
```

**Error Response (403):**
```json
{
  "errors": [
    {
      "code": "booking",
      "message": "Booking cannot be confirmed. Current status: completed"
    }
  ]
}
```

---

### 4. Mark Booking as Completed

**Endpoint:** `PUT /bookings/complete`

**Description:**  
Mark a confirmed booking as completed  
علامت‌گذاری یک رزرو تأیید شده به عنوان تکمیل شده

**Request Body:**
```json
{
  "booking_id": 100001
}
```

**Rate Limit:** 10 requests per minute

**Response Example:**
```json
{
  "message": "Booking completed successfully",
  "data": {
    "id": 100001,
    "status": "completed",
    "completed_at": "2024-01-20 11:00:00"
  }
}
```

**Error Response (403):**
```json
{
  "errors": [
    {
      "code": "booking",
      "message": "Can only complete confirmed bookings"
    }
  ]
}
```

---

### 5. Mark Payment as Paid

**Endpoint:** `PUT /bookings/mark-paid`

**Description:**  
Mark cash payment as paid (for cash_payment bookings only)  
علامت‌گذاری پرداخت نقدی به عنوان پرداخت شده (فقط برای رزروهای پرداخت نقدی)

**Request Body:**
```json
{
  "booking_id": 100001
}
```

**Rate Limit:** 10 requests per minute

**Response Example:**
```json
{
  "message": "Payment marked as paid successfully",
  "data": {
    "id": 100001,
    "payment_status": "paid"
  }
}
```

**Error Response (403):**
```json
{
  "errors": [
    {
      "code": "payment",
      "message": "Can only mark cash payments as paid"
    }
  ]
}
```

---

### 6. Cancel Booking

**Endpoint:** `PUT /bookings/cancel`

**Description:**  
Cancel a booking (vendor cancellation: full refund to customer)  
لغو یک رزرو (لغو فروشنده: بازگشت وجه کامل به مشتری)

**Request Body:**
```json
{
  "booking_id": 100001,
  "cancellation_reason": "Salon closed for maintenance"
}
```

**Rate Limit:** 5 requests per minute

**Response Example:**
```json
{
  "message": "Booking cancelled successfully",
  "data": {
    "id": 100001,
    "status": "cancelled",
    "cancellation_reason": "Salon closed for maintenance"
  }
}
```

---

## Staff Management

### 1. List Staff

**Endpoint:** `GET /staff/list`

**Description:**  
List all staff members for the vendor's salon  
لیست تمام کارمندان سالن فروشنده

**Query Parameters:**
- `limit` (integer, optional, default: 25): Items per page
- `offset` (integer, optional, default: 0): Page offset

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "phone": "+1234567890",
      "status": 1,
      "specializations": ["Haircut", "Coloring"],
      "working_hours": {...},
      "breaks": {...}
    }
  ],
  "total": 10,
  "per_page": 25,
  "current_page": 1,
  "last_page": 1
}
```

---

### 2. Create Staff

**Endpoint:** `POST /staff/create`

**Description:**  
Create a new staff member  
ایجاد کارمند جدید

**Request Body (multipart/form-data):**
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "phone": "+1234567890",
  "avatar": "file",
  "status": 1,
  "specializations": ["Haircut", "Coloring"],
  "working_hours": {
    "monday": {"open": "09:00", "close": "18:00"},
    "tuesday": {"open": "09:00", "close": "18:00"}
  },
  "breaks": [],
  "days_off": []
}
```

**Rate Limit:** 10 requests per minute

**Response Example (201):**
```json
{
  "message": "Staff added successfully",
  "data": {
    "id": 1,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1234567890",
    "status": 1
  }
}
```

---

### 3. Update Staff

**Endpoint:** `POST /staff/update/{id}`

**Description:**  
Update staff member information  
به‌روزرسانی اطلاعات کارمند

**Path Parameters:**
- `id` (integer): Staff ID

**Request Body (multipart/form-data, all fields optional):**
```json
{
  "name": "Jane Doe",
  "email": "jane.doe@example.com",
  "phone": "+1234567890",
  "avatar": "file",
  "status": 1,
  "specializations": ["Haircut", "Coloring", "Styling"],
  "working_hours": {...},
  "breaks": {...},
  "days_off": []
}
```

**Rate Limit:** 10 requests per minute

**Response Example:**
```json
{
  "message": "Staff updated successfully",
  "data": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane.doe@example.com"
  }
}
```

---

### 4. Get Staff Details

**Endpoint:** `GET /staff/details/{id}`

**Description:**  
Get detailed information about a staff member  
دریافت اطلاعات تفصیلی یک کارمند

**Path Parameters:**
- `id` (integer): Staff ID

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": {
    "id": 1,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1234567890",
    "avatar": "path/to/avatar.png",
    "status": 1,
    "specializations": ["Haircut", "Coloring"],
    "working_hours": {...},
    "services": [...]
  }
}
```

---

### 5. Delete Staff

**Endpoint:** `DELETE /staff/delete/{id}`

**Description:**  
Delete a staff member (only if no bookings exist)  
حذف یک کارمند (فقط در صورت عدم وجود رزرو)

**Path Parameters:**
- `id` (integer): Staff ID

**Rate Limit:** 5 requests per minute

**Response Example:**
```json
{
  "message": "Staff deleted successfully"
}
```

**Error Response (403):**
```json
{
  "errors": [
    {
      "code": "staff",
      "message": "Cannot delete staff with bookings"
    }
  ]
}
```

---

### 6. Toggle Staff Status

**Endpoint:** `GET /staff/status/{id}`

**Description:**  
Toggle staff active/inactive status  
تغییر وضعیت فعال/غیرفعال کارمند

**Path Parameters:**
- `id` (integer): Staff ID

**Rate Limit:** 10 requests per minute

**Response Example:**
```json
{
  "message": "Status updated successfully",
  "data": {
    "id": 1,
    "status": 0
  }
}
```

---

## Service Management

### 1. List Services

**Endpoint:** `GET /service/list`

**Description:**  
List all services for the vendor's salon  
لیست تمام خدمات سالن فروشنده

**Query Parameters:**
- `limit` (integer, optional, default: 25): Items per page
- `offset` (integer, optional, default: 0): Page offset

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Haircut",
      "description": "Professional haircut service",
      "duration_minutes": 30,
      "price": 50000,
      "status": 1,
      "category": {
        "id": 1,
        "name": "Hair Services"
      }
    }
  ],
  "total": 15,
  "per_page": 25,
  "current_page": 1,
  "last_page": 1
}
```

---

### 2. Create Service

**Endpoint:** `POST /service/create`

**Description:**  
Create a new service  
ایجاد خدمت جدید

**Request Body (multipart/form-data):**
```json
{
  "category_id": 1,
  "name": "Haircut",
  "description": "Professional haircut service",
  "duration_minutes": 30,
  "price": 50000,
  "image": "file",
  "status": 1,
  "staff_ids": [1, 2]
}
```

**Rate Limit:** 10 requests per minute

**Response Example (201):**
```json
{
  "message": "Service added successfully",
  "data": {
    "id": 1,
    "name": "Haircut",
    "category": {...},
    "staff": [...]
  }
}
```

---

### 3. Update Service

**Endpoint:** `POST /service/update/{id}`

**Description:**  
Update service information  
به‌روزرسانی اطلاعات خدمت

**Path Parameters:**
- `id` (integer): Service ID

**Request Body (multipart/form-data, all fields optional):**
```json
{
  "category_id": 1,
  "name": "Premium Haircut",
  "description": "Premium haircut service",
  "duration_minutes": 45,
  "price": 75000,
  "image": "file",
  "status": 1,
  "staff_ids": [1, 2, 3]
}
```

**Rate Limit:** 10 requests per minute

**Response Example:**
```json
{
  "message": "Service updated successfully",
  "data": {
    "id": 1,
    "name": "Premium Haircut",
    "price": 75000
  }
}
```

---

### 4. Get Service Details

**Endpoint:** `GET /service/details/{id}`

**Description:**  
Get detailed information about a service  
دریافت اطلاعات تفصیلی یک خدمت

**Path Parameters:**
- `id` (integer): Service ID

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": {
    "id": 1,
    "name": "Haircut",
    "description": "Professional haircut service",
    "duration_minutes": 30,
    "price": 50000,
    "category": {...},
    "staff": [...]
  }
}
```

---

### 5. Delete Service

**Endpoint:** `DELETE /service/delete/{id}`

**Description:**  
Delete a service (only if no bookings exist)  
حذف یک خدمت (فقط در صورت عدم وجود رزرو)

**Path Parameters:**
- `id` (integer): Service ID

**Rate Limit:** 5 requests per minute

**Response Example:**
```json
{
  "message": "Service deleted successfully"
}
```

**Error Response (403):**
```json
{
  "errors": [
    {
      "code": "service",
      "message": "Cannot delete service with bookings"
    }
  ]
}
```

---

### 6. Toggle Service Status

**Endpoint:** `GET /service/status/{id}`

**Description:**  
Toggle service active/inactive status  
تغییر وضعیت فعال/غیرفعال خدمت

**Path Parameters:**
- `id` (integer): Service ID

**Rate Limit:** 10 requests per minute

**Response Example:**
```json
{
  "message": "Status updated successfully",
  "data": {
    "id": 1,
    "status": 0
  }
}
```

---

## Calendar Management

### 1. Get Availability

**Endpoint:** `GET /calendar/availability`

**Description:**  
Get available time slots for a specific date  
دریافت بازه‌های زمانی موجود برای یک تاریخ خاص

**Query Parameters:**
- `date` (date, required): Date to check (format: YYYY-MM-DD)
- `staff_id` (integer, optional): Staff ID to check availability
- `service_id` (integer, optional): Service ID (used to determine duration)

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": {
    "date": "2024-01-20",
    "duration_minutes": 60,
    "available_slots": [
      "09:00",
      "10:00",
      "11:00",
      "14:00",
      "15:00"
    ]
  }
}
```

---

### 2. Create Calendar Block

**Endpoint:** `POST /calendar/blocks/create`

**Description:**  
Create a calendar block (holiday, break, or manual block)  
ایجاد بلاک تقویم (تعطیلات، استراحت، یا بلاک دستی)

**Request Body:**
```json
{
  "date": "2024-01-20",
  "start_time": "12:00",
  "end_time": "13:00",
  "type": "break",
  "reason": "Lunch break",
  "staff_id": 1
}
```

**Block Types:**
- `break`: Break time
- `holiday`: Holiday
- `manual_block`: Manual block

**Rate Limit:** 30 requests per minute

**Response Example (201):**
```json
{
  "message": "Calendar block created successfully",
  "data": {
    "id": 1,
    "date": "2024-01-20",
    "start_time": "12:00",
    "end_time": "13:00",
    "type": "break"
  }
}
```

---

### 3. Delete Calendar Block

**Endpoint:** `DELETE /calendar/blocks/delete/{id}`

**Description:**  
Delete a calendar block  
حذف بلاک تقویم

**Path Parameters:**
- `id` (integer): Calendar block ID

**Rate Limit:** 30 requests per minute

**Response Example:**
```json
{
  "message": "Calendar block deleted successfully"
}
```

---

## Salon Registration & Profile

### 1. Register Salon

**Endpoint:** `POST /salon/register`

**Description:**  
Register a new salon (onboarding)  
ثبت‌نام سالن جدید (فرآیند ورود)

**Request Body:**
```json
{
  "business_type": "salon",
  "license_number": "LIC123456",
  "license_expiry": "2025-12-31",
  "working_hours": [
    {
      "day": "monday",
      "open": "09:00",
      "close": "18:00"
    },
    {
      "day": "tuesday",
      "open": "09:00",
      "close": "18:00"
    }
  ]
}
```

**Rate Limit:** 5 requests per minute

**Response Example (201):**
```json
{
  "message": "Salon registered successfully",
  "data": {
    "id": 1,
    "business_type": "salon",
    "license_number": "LIC123456",
    "verification_status": 0,
    "is_verified": false
  }
}
```

**Error Response (403):**
```json
{
  "errors": [
    {
      "code": "salon",
      "message": "Salon already registered"
    }
  ]
}
```

---

### 2. Upload Documents

**Endpoint:** `POST /salon/documents/upload`

**Description:**  
Upload salon documents (license, certificates, etc.)  
آپلود مدارک سالن (مجوز، گواهینامه‌ها و غیره)

**Request Body (multipart/form-data):**
```
documents[]: file1.pdf
documents[]: file2.jpg
```

**Rate Limit:** 10 requests per minute

**Response Example:**
```json
{
  "message": "Documents uploaded successfully",
  "data": {
    "documents": [
      "beauty/salons/documents/file1.pdf",
      "beauty/salons/documents/file2.jpg"
    ],
    "uploaded_count": 2
  }
}
```

---

### 3. Update Working Hours

**Endpoint:** `POST /salon/working-hours/update`

**Description:**  
Update salon working hours  
به‌روزرسانی ساعات کاری سالن

**Request Body:**
```json
{
  "working_hours": [
    {
      "day": "monday",
      "open": "09:00",
      "close": "18:00"
    },
    {
      "day": "tuesday",
      "open": "09:00",
      "close": "18:00"
    }
  ]
}
```

**Rate Limit:** 10 requests per minute

**Response Example:**
```json
{
  "message": "Working hours updated successfully",
  "data": {
    "id": 1,
    "working_hours": {...}
  }
}
```

---

### 4. Manage Holidays

**Endpoint:** `POST /salon/holidays/manage`

**Description:**  
Add, remove, or replace holidays  
افزودن، حذف یا جایگزینی تعطیلات

**Request Body:**
```json
{
  "action": "add",
  "holidays": ["2024-01-01", "2024-12-25"]
}
```

**Actions:**
- `add`: Add holidays to existing list
- `remove`: Remove holidays from existing list
- `replace`: Replace entire holiday list

**Rate Limit:** 10 requests per minute

**Response Example:**
```json
{
  "message": "Holidays updated successfully",
  "data": {
    "holidays": ["2024-01-01", "2024-12-25"],
    "total_count": 2
  }
}
```

---

### 5. Get Profile

**Endpoint:** `GET /profile`

**Description:**  
Get vendor profile with salon information  
دریافت پروفایل فروشنده با اطلاعات سالن

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": {
    "salon": {
      "id": 1,
      "business_type": "salon",
      "is_verified": true,
      "is_featured": false
    },
    "store": {...},
    "badges": [...],
    "active_subscriptions": [...]
  }
}
```

---

### 6. Update Profile

**Endpoint:** `POST /profile/update`

**Description:**  
Update vendor profile information  
به‌روزرسانی اطلاعات پروفایل فروشنده

**Request Body (all fields optional):**
```json
{
  "license_number": "LIC123456",
  "license_expiry": "2025-12-31",
  "business_type": "salon",
  "working_hours": {...},
  "holidays": [...]
}
```

**Rate Limit:** 10 requests per minute

**Response Example:**
```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "license_number": "LIC123456"
  }
}
```

---

## Retail Management

### 1. List Products

**Endpoint:** `GET /retail/products`

**Description:**  
List retail products for the vendor's salon  
لیست محصولات خرده‌فروشی سالن فروشنده

**Query Parameters:**
- `limit` (integer, optional, default: 25): Items per page

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Shampoo",
      "description": "Premium shampoo",
      "price": 50000,
      "stock_quantity": 100,
      "status": 1
    }
  ],
  "total": 20,
  "per_page": 25,
  "current_page": 1,
  "last_page": 1
}
```

---

### 2. Create Product

**Endpoint:** `POST /retail/products`

**Description:**  
Create a new retail product  
ایجاد محصول خرده‌فروشی جدید

**Request Body (multipart/form-data):**
```json
{
  "name": "Shampoo",
  "description": "Premium shampoo",
  "price": 50000,
  "image": "file",
  "category": "Hair Care",
  "stock_quantity": 100,
  "min_stock_level": 10,
  "status": true
}
```

**Rate Limit:** 10 requests per minute

**Response Example (201):**
```json
{
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "name": "Shampoo",
    "price": 50000
  }
}
```

---

### 3. List Orders

**Endpoint:** `GET /retail/orders`

**Description:**  
List retail orders for the vendor's salon  
لیست سفارشات خرده‌فروشی سالن فروشنده

**Query Parameters:**
- `status` (string, optional): Filter by order status
- `limit` (integer, optional, default: 25): Items per page

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "user": {...},
      "product": {...},
      "quantity": 2,
      "total_amount": 100000,
      "status": "completed"
    }
  ],
  "total": 15,
  "per_page": 25,
  "current_page": 1,
  "last_page": 1
}
```

---

## Subscription Management

### 1. Get Plans

**Endpoint:** `GET /subscription/plans`

**Description:**  
Get available subscription plans  
دریافت پلان‌های اشتراک موجود

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": {
    "plans": {
      "featured_listing": {
        "7": {"duration_days": 7, "price": 50000},
        "30": {"duration_days": 30, "price": 150000}
      },
      "boost_ads": {
        "7": {"duration_days": 7, "price": 75000},
        "30": {"duration_days": 30, "price": 200000}
      },
      "banner_ads": {
        "homepage": {"duration_days": 30, "price": 100000},
        "category": {"duration_days": 30, "price": 75000}
      }
    },
    "active_subscriptions": [...]
  }
}
```

---

### 2. Purchase Subscription

**Endpoint:** `POST /subscription/purchase`

**Description:**  
Purchase a subscription plan  
خرید پلان اشتراک

**Request Body:**
```json
{
  "subscription_type": "featured_listing",
  "duration_days": 30,
  "ad_position": null,
  "banner_image": null,
  "payment_method": "wallet",
  "payment_gateway": "stripe",
  "payment_platform": "web",
  "callback_url": "https://example.com/callback"
}
```

**Subscription Types:**
- `featured_listing`: Featured listing subscription
- `boost_ads`: Boost ads subscription
- `banner_ads`: Banner ads subscription
- `dashboard_subscription`: Dashboard subscription

**Payment Methods:**
- `cash_payment`: Cash payment
- `wallet`: Wallet payment
- `digital_payment`: Digital payment (returns redirect URL)

**Rate Limit:** 5 requests per minute

**Response Example (201) - Cash/Wallet:**
```json
{
  "message": "Subscription purchased successfully",
  "data": {
    "id": 1,
    "subscription_type": "featured_listing",
    "status": "active",
    "start_date": "2024-01-01",
    "end_date": "2024-01-31"
  }
}
```

**Response Example - Digital Payment:**
```json
{
  "message": "Redirect to payment gateway",
  "data": {
    "subscription_id": 1,
    "redirect_url": "https://payment-gateway.com/checkout/..."
  }
}
```

---

### 3. Get Subscription History

**Endpoint:** `GET /subscription/history`

**Description:**  
Get subscription purchase history  
دریافت تاریخچه خرید اشتراک‌ها

**Query Parameters:**
- `per_page` (integer, optional, default: 15): Items per page

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "subscription_type": "featured_listing",
      "status": "active",
      "start_date": "2024-01-01",
      "end_date": "2024-01-31",
      "amount_paid": 150000
    }
  ],
  "total": 5,
  "per_page": 15,
  "current_page": 1,
  "last_page": 1
}
```

---

## Finance & Reports

### 1. Get Payout Summary

**Endpoint:** `GET /finance/payout-summary`

**Description:**  
Get financial payout summary  
دریافت خلاصه پرداخت مالی

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": {
    "total_revenue": 5000000,
    "total_commission": 500000,
    "total_service_fee": 100000,
    "net_payout": 4400000,
    "revenue_breakdown": {
      "booking": 4000000,
      "retail": 800000,
      "subscription": 200000
    }
  }
}
```

---

### 2. Get Transaction History

**Endpoint:** `GET /finance/transactions`

**Description:**  
Get transaction history  
دریافت تاریخچه تراکنش‌ها

**Query Parameters:**
- `date_from` (date, optional): Filter from date
- `date_to` (date, optional): Filter to date
- `transaction_type` (string, optional): Filter by transaction type
- `per_page` (integer, optional, default: 15): Items per page

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "transaction_type": "booking",
      "amount": 100000,
      "commission": 10000,
      "created_at": "2024-01-15 10:00:00"
    }
  ],
  "total": 100,
  "per_page": 15,
  "current_page": 1,
  "last_page": 7
}
```

---

## Badge Management

### 1. Get Badge Status

**Endpoint:** `GET /badge/status`

**Description:**  
Get current badge status and criteria  
دریافت وضعیت و معیارهای نشان فعلی

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": {
    "current_badges": ["top_rated", "verified"],
    "criteria": {
      "top_rated": {
        "name": "Top Rated",
        "current_rating": 4.8,
        "required_rating": 4.8,
        "current_bookings": 75,
        "required_bookings": 50,
        "has_badge": true
      },
      "featured": {
        "name": "Featured",
        "has_subscription": true,
        "has_badge": false
      },
      "verified": {
        "name": "Verified",
        "is_verified": true,
        "has_badge": true
      }
    }
  }
}
```

---

## Package Management

### 1. List Packages

**Endpoint:** `GET /packages/list`

**Description:**  
List all packages for the vendor's salon  
لیست تمام پکیج‌های سالن فروشنده

**Query Parameters:**
- `search` (string, optional): Search by package name
- `per_page` (integer, optional, default: 15): Items per page

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Hair Care Package",
      "total_sessions": 5,
      "used_sessions": 2,
      "price": 400000,
      "service": {...}
    }
  ],
  "total": 10,
  "per_page": 15,
  "current_page": 1,
  "last_page": 1
}
```

---

### 2. Get Usage Stats

**Endpoint:** `GET /packages/usage-stats`

**Description:**  
Get package usage statistics  
دریافت آمار استفاده از پکیج

**Query Parameters:**
- `package_id` (integer, required): Package ID

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": {
    "package": {
      "id": 1,
      "name": "Hair Care Package",
      "total_sessions": 5
    },
    "total_redemptions": 2,
    "remaining_sessions": 3
  }
}
```

---

## Gift Card Management

### 1. List Gift Cards

**Endpoint:** `GET /gift-cards/list`

**Description:**  
List all gift cards for the vendor's salon  
لیست تمام کارت‌های هدیه سالن فروشنده

**Query Parameters:**
- `search` (string, optional): Search by gift card code
- `status` (string, optional): Filter by status
- `per_page` (integer, optional, default: 15): Items per page

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "code": "GC-ABC123",
      "amount": 100000,
      "balance": 50000,
      "status": "active",
      "purchaser": {...},
      "redeemer": null
    }
  ],
  "total": 20,
  "per_page": 15,
  "current_page": 1,
  "last_page": 2
}
```

---

### 2. Get Redemption History

**Endpoint:** `GET /gift-cards/redemption-history`

**Description:**  
Get gift card redemption history  
دریافت تاریخچه استفاده از کارت هدیه

**Query Parameters:**
- `date_from` (date, optional): Filter from date
- `date_to` (date, optional): Filter to date
- `per_page` (integer, optional, default: 15): Items per page

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "code": "GC-ABC123",
      "amount": 100000,
      "redeemed_at": "2024-01-15 10:00:00",
      "purchaser": {...},
      "redeemer": {...}
    }
  ],
  "total": 10,
  "per_page": 15,
  "current_page": 1,
  "last_page": 1
}
```

---

## Loyalty Campaign Management

### 1. List Campaigns

**Endpoint:** `GET /loyalty/campaigns`

**Description:**  
List loyalty campaigns for the vendor's salon  
لیست کمپین‌های وفاداری سالن فروشنده

**Query Parameters:**
- `search` (string, optional): Search by campaign name
- `status` (string, optional): Filter by status (`active` or `inactive`)
- `per_page` (integer, optional, default: 15): Items per page

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "New Year Campaign",
      "points_per_booking": 10,
      "points_per_amount": 100,
      "is_active": true
    }
  ],
  "total": 5,
  "per_page": 15,
  "current_page": 1,
  "last_page": 1
}
```

---

### 2. Get Points History

**Endpoint:** `GET /loyalty/points-history`

**Description:**  
Get loyalty points issuance history  
دریافت تاریخچه صدور امتیازهای وفاداری

**Query Parameters:**
- `date_from` (date, optional): Filter from date
- `date_to` (date, optional): Filter to date
- `per_page` (integer, optional, default: 15): Items per page

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "user": {...},
      "booking": {...},
      "points": 10,
      "type": "earned",
      "created_at": "2024-01-15 10:00:00"
    }
  ],
  "total": 50,
  "per_page": 15,
  "current_page": 1,
  "last_page": 4
}
```

---

### 3. Get Campaign Stats

**Endpoint:** `GET /loyalty/campaign/{id}/stats`

**Description:**  
Get campaign performance statistics  
دریافت آمار عملکرد کمپین

**Path Parameters:**
- `id` (integer): Campaign ID

**Rate Limit:** 60 requests per minute

**Response Example:**
```json
{
  "message": "Data retrieved successfully",
  "data": {
    "campaign": {
      "id": 1,
      "name": "New Year Campaign"
    },
    "total_points_issued": 1000,
    "total_points_redeemed": 500,
    "total_users": 50
  }
}
```

---

## Rate Limiting

Rate limits are configured per endpoint group:

- **High-frequency endpoints (60/min):** List endpoints, detail endpoints, profile endpoints
- **Medium-frequency endpoints (30/min):** Calendar block operations
- **Low-frequency endpoints (10/min):** Create, update operations
- **Critical operations (5/min):** Delete operations, subscription purchase, salon registration

---

## Common Patterns

### File Uploads
Use `multipart/form-data` for file uploads:
- Staff avatar: `POST /staff/create` with `avatar` field
- Service image: `POST /service/create` with `image` field
- Documents: `POST /salon/documents/upload` with `documents[]` array

### Date Formats
- Date: `YYYY-MM-DD` (e.g., `2024-01-20`)
- Time: `HH:MM` (e.g., `10:00`)
- DateTime: `YYYY-MM-DD HH:MM:SS` (e.g., `2024-01-20 10:00:00`)

### Filtering
Most list endpoints support:
- `search`: Text search
- `status`: Status filter
- `date_from` / `date_to`: Date range filter

---

**Last Updated:** 2025-12-03  
**Version:** 1.0  
**Status:** ✅ Complete Documentation

