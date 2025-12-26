# Vendor API Contract

API contract documentation defining request/response schemas, validation rules, error codes, and status codes.

## Table of Contents

1. [Request/Response Schemas](#requestresponse-schemas)
2. [Validation Rules](#validation-rules)
3. [Error Codes](#error-codes)
4. [Status Codes](#status-codes)
5. [Rate Limits](#rate-limits)

---

## Request/Response Schemas

### Booking Management

#### List Bookings Request
```typescript
GET /bookings/list/{all}
Query Parameters:
{
  status?: string;        // Optional: pending|confirmed|completed|cancelled
  date_from?: string;     // Optional: YYYY-MM-DD
  date_to?: string;       // Optional: YYYY-MM-DD
  limit?: number;         // Optional: default 25
  offset?: number;         // Optional: default 0
}
```

#### List Bookings Response
```typescript
{
  message: string;
  data: Booking[];
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}

interface Booking {
  id: number;
  booking_reference: string;
  user: User;
  service: Service;
  staff: Staff | null;
  status: 'pending' | 'confirmed' | 'completed' | 'cancelled' | 'no_show';
  payment_status: 'paid' | 'unpaid' | 'partially_paid';
  booking_date: string;      // YYYY-MM-DD
  booking_time: string;       // HH:MM
  total_amount: number;
}
```

#### Confirm Booking Request
```typescript
PUT /bookings/confirm
Body:
{
  booking_id: number;     // Required
}
```

#### Confirm Booking Response
```typescript
{
  message: string;
  data: {
    id: number;
    booking_reference: string;
    status: 'confirmed';
    confirmed_at: string;  // YYYY-MM-DD HH:MM:SS
  };
}
```

---

### Staff Management

#### Create Staff Request
```typescript
POST /staff/create
Content-Type: multipart/form-data
Body:
{
  name: string;                    // Required, max 255
  email: string;                   // Required, email format
  phone: string;                   // Required
  avatar?: File;                   // Optional, image file
  status?: number;                 // Optional, default 1 (0=inactive, 1=active)
  specializations?: string[];      // Optional, array of strings
  working_hours?: object;           // Optional, JSON object
  breaks?: object[];                // Optional, array of objects
  days_off?: string[];             // Optional, array of day names
}
```

#### Create Staff Response
```typescript
{
  message: string;
  data: {
    id: number;
    name: string;
    email: string;
    phone: string;
    avatar: string | null;
    status: number;
    specializations: string[];
    working_hours: object;
    breaks: object[];
    days_off: string[];
  };
}
```

---

### Service Management

#### Create Service Request
```typescript
POST /service/create
Content-Type: multipart/form-data
Body:
{
  category_id: number;             // Required, exists:beauty_service_categories,id
  name: string;                    // Required, max 255
  description?: string;             // Optional
  duration_minutes: number;         // Required, integer, min:1
  price: number;                    // Required, numeric, min:0
  image?: File;                     // Optional, image file
  status?: number;                  // Optional, default 1
  staff_ids?: number[];             // Optional, array of staff IDs
}
```

#### Create Service Response
```typescript
{
  message: string;
  data: {
    id: number;
    category_id: number;
    name: string;
    description: string | null;
    duration_minutes: number;
    price: number;
    image: string | null;
    status: number;
    category: ServiceCategory;
    staff: Staff[];
  };
}
```

---

### Calendar Management

#### Get Availability Request
```typescript
GET /calendar/availability
Query Parameters:
{
  date: string;                    // Required, YYYY-MM-DD
  staff_id?: number;                // Optional, exists:beauty_staff,id
  service_id?: number;               // Optional, exists:beauty_services,id
}
```

#### Get Availability Response
```typescript
{
  message: string;
  data: {
    date: string;                   // YYYY-MM-DD
    duration_minutes: number;
    available_slots: string[];      // Array of time strings (HH:MM)
  };
}
```

#### Create Calendar Block Request
```typescript
POST /calendar/blocks/create
Body:
{
  date: string;                    // Required, YYYY-MM-DD
  start_time: string;               // Required, HH:MM
  end_time: string;                 // Required, HH:MM, after:start_time
  type: string;                     // Required, in:break,holiday,manual_block
  reason?: string;                  // Optional, max 500
  staff_id?: number;                // Optional, exists:beauty_staff,id
}
```

---

### Salon Registration

#### Register Salon Request
```typescript
POST /salon/register
Body:
{
  business_type: string;           // Required, in:salon,clinic
  license_number: string;           // Required, max 100
  license_expiry: string;           // Required, date, after:today
  working_hours: Array<{             // Required, array
    day: string;                     // Required, day name
    open: string;                    // Required, HH:MM
    close: string;                   // Required, HH:MM
  }>;
}
```

#### Register Salon Response
```typescript
{
  message: string;
  data: {
    id: number;
    business_type: string;
    license_number: string;
    license_expiry: string;
    verification_status: number;    // 0=pending, 1=approved, 2=rejected
    is_verified: boolean;
    working_hours: object;
  };
}
```

---

### Subscription Management

#### Purchase Subscription Request
```typescript
POST /subscription/purchase
Body:
{
  subscription_type: string;        // Required, in:featured_listing,boost_ads,banner_ads,dashboard_subscription
  duration_days: number;             // Required, integer
  ad_position?: string;              // Optional, required for banner_ads
  banner_image?: string;             // Optional, base64 or URL for banner_ads
  payment_method: string;           // Required, in:cash_payment,wallet,digital_payment
  payment_gateway?: string;          // Optional, for digital_payment
  payment_platform?: string;        // Optional, for digital_payment
  callback_url?: string;            // Optional, for digital_payment
}
```

#### Purchase Subscription Response (Cash/Wallet)
```typescript
{
  message: string;
  data: {
    id: number;
    subscription_type: string;
    status: 'active';
    start_date: string;              // YYYY-MM-DD
    end_date: string;                // YYYY-MM-DD
    amount_paid: number;
  };
}
```

#### Purchase Subscription Response (Digital Payment)
```typescript
{
  message: string;
  data: {
    subscription_id: number;
    redirect_url: string;           // Payment gateway URL
  };
}
```

---

## Validation Rules

### Common Validation Patterns

#### Required Fields
- Use `required` rule for mandatory fields
- Example: `'booking_id' => 'required|integer'`

#### Type Validation
- **Integer:** `integer`
- **Numeric:** `numeric|min:0`
- **String:** `string|max:255`
- **Email:** `email`
- **Date:** `date` or `date_format:Y-m-d`
- **Time:** `date_format:H:i`
- **Boolean:** `boolean`

#### Existence Validation
- **Foreign Key:** `exists:table_name,column_name`
- Example: `'staff_id' => 'exists:beauty_staff,id'`

#### Array Validation
- **Array:** `array`
- **Array Items:** `array.*.field` for nested validation

#### File Validation
- **Image:** `image|mimes:jpeg,png,jpg|max:2048`
- **Multiple Files:** `array` with file validation

### Validation Error Response Format
```json
{
  "errors": [
    {
      "code": "validation",
      "message": "The booking_id field is required."
    },
    {
      "code": "validation",
      "message": "The staff_id must be an integer."
    }
  ]
}
```

---

## Error Codes

### Error Code Reference

| Code | Description | HTTP Status |
|------|-------------|-------------|
| `validation` | Validation error | 403 |
| `booking` | Booking-related error | 403 |
| `staff` | Staff-related error | 403 |
| `service` | Service-related error | 403 |
| `calendar` | Calendar-related error | 403 |
| `salon` | Salon-related error | 403 |
| `payment` | Payment-related error | 403 |
| `subscription` | Subscription-related error | 403 |
| `finance` | Finance-related error | 403 |
| `not_found` | Resource not found | 404 |
| `unauthorized` | Unauthorized access | 403 |
| `wallet` | Wallet-related error | 403 |
| `upload` | File upload error | 500 |
| `registration` | Registration error | 500 |

### Common Error Messages

#### Validation Errors
- `"The {field} field is required."`
- `"The {field} must be an integer."`
- `"The {field} must be a valid email address."`
- `"The {field} must be a date."`

#### Business Logic Errors
- `"Booking cannot be confirmed. Current status: {status}"`
- `"Can only complete confirmed bookings"`
- `"Can only mark cash payments as paid"`
- `"Cannot delete staff with bookings"`
- `"Cannot delete service with bookings"`
- `"Salon already registered"`
- `"Insufficient wallet balance"`

---

## Status Codes

### HTTP Status Code Reference

| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | Successful GET, PUT requests |
| 201 | Created | Successful POST requests (resource created) |
| 400 | Bad Request | Invalid request data |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Authorization failed or validation error |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed (alternative to 403) |
| 500 | Internal Server Error | Server error |

### Booking Status Values
- `pending`: Initial status after creation
- `confirmed`: Salon confirmed the booking
- `completed`: Service completed
- `cancelled`: Booking cancelled
- `no_show`: Customer didn't show up

### Payment Status Values
- `paid`: Payment completed
- `unpaid`: Payment not completed
- `partially_paid`: Partial payment received

---

## Rate Limits

### Rate Limit Configuration

| Endpoint Group | Rate Limit | Endpoints |
|----------------|------------|-----------|
| **High Frequency** | 60/min | List, details, profile endpoints |
| **Medium Frequency** | 30/min | Calendar block operations |
| **Low Frequency** | 10/min | Create, update operations |
| **Critical Operations** | 5/min | Delete, subscription purchase, salon registration |

### Rate Limit Headers

Response headers include rate limit information:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1701600000
```

### Rate Limit Exceeded Response

When rate limit is exceeded:
```json
{
  "errors": [
    {
      "code": "rate_limit",
      "message": "Too many requests. Please try again later."
    }
  ]
}
```
HTTP Status: **429 Too Many Requests**

---

## Data Types

### Date Formats
- **Date:** `YYYY-MM-DD` (e.g., `2024-01-20`)
- **Time:** `HH:MM` (e.g., `10:00`)
- **DateTime:** `YYYY-MM-DD HH:MM:SS` (e.g., `2024-01-20 10:00:00`)

### Number Formats
- **Integer:** Whole numbers (e.g., `100001`)
- **Decimal:** Decimal numbers with 2 decimal places (e.g., `100000.00`)
- **Currency:** Stored as integer (smallest currency unit) or decimal

### Boolean Formats
- **Boolean:** `true` or `false`
- **Status Integer:** `0` (inactive) or `1` (active)

### Array Formats
- **JSON Array:** `["item1", "item2"]`
- **JSON Object Array:** `[{"key": "value"}, {"key": "value"}]`

---

## File Upload Specifications

### Supported File Types
- **Images:** JPEG, PNG, JPG
- **Documents:** PDF

### File Size Limits
- **Images:** Maximum 2MB (2048 KB)
- **Documents:** Maximum 5MB (5120 KB)

### Upload Format
- Use `multipart/form-data` content type
- Single file: `file` field name
- Multiple files: `files[]` array field name

### Upload Response
```json
{
  "message": "File uploaded successfully",
  "data": {
    "file_path": "beauty/staff/avatar_123456.png",
    "url": "http://example.com/storage/beauty/staff/avatar_123456.png"
  }
}
```

---

**Last Updated:** 2025-12-03  
**Version:** 1.0  
**Status:** ✅ Complete Contract Documentation

