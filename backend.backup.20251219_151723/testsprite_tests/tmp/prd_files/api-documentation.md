# Beauty Booking API Documentation
## مستندات API رزرو زیبایی

### Base URL
```
/api/v1/beautybooking
```

### Authentication
Most endpoints require authentication using Bearer token:
```
Authorization: Bearer {token}
```

---

## Customer API Endpoints

### Public Endpoints (No Authentication Required)

#### 1. Search Salons
**Endpoint:** `GET /salons/search`

**Description:** Search for salons with ranking algorithm

**Query Parameters:**
- `search` (string, optional): Search query
- `latitude` (float, optional): User latitude
- `longitude` (float, optional): User longitude
- `category_id` (integer, optional): Filter by category
- `business_type` (string, optional): Filter by type (salon/clinic)
- `min_rating` (float, optional): Minimum rating filter
- `radius` (float, optional): Search radius in kilometers

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Salon Name",
            "business_type": "salon",
            "avg_rating": 4.5,
            "total_reviews": 100,
            "total_bookings": 500,
            "is_verified": true,
            "is_featured": false,
            "badges": ["top_rated"],
            "latitude": 35.6892,
            "longitude": 51.3890
        }
    ],
    "total": 10
}
```

#### 2. Get Salon Details
**Endpoint:** `GET /salons/{id}`

**Description:** Get detailed information about a salon

**Response:**
```json
{
    "data": {
        "id": 1,
        "name": "Salon Name",
        "services": [...],
        "staff": [...],
        "working_hours": {...},
        "reviews": [...]
    }
}
```

#### 3. Get Categories List
**Endpoint:** `GET /salons/category-list`

**Description:** Get list of all service categories

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Hair Services",
            "image": "https://...",
            "children": [
                {
                    "id": 2,
                    "name": "Haircut",
                    "image": "https://..."
                }
            ]
        }
    ]
}
```

#### 4. Get Popular Salons
**Endpoint:** `GET /salons/popular`

**Description:** Get list of popular salons

**Response:**
```json
{
    "data": [...]
}
```

#### 5. Get Top Rated Salons
**Endpoint:** `GET /salons/top-rated`

**Description:** Get list of top rated salons

**Response:**
```json
{
    "data": [...]
}
```

#### 6. Get Salon Reviews
**Endpoint:** `GET /reviews/{salon_id}`

**Description:** Get reviews for a salon

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "user_name": "John Doe",
            "rating": 5,
            "comment": "Great service!",
            "created_at": "2024-01-15 10:00:00"
        }
    ]
}
```

---

### Authenticated Endpoints

#### 7. Create Booking
**Endpoint:** `POST /bookings`

**Authentication:** Required

**Request Body:**
```json
{
    "salon_id": 1,
    "service_id": 1,
    "staff_id": 1,
    "booking_date": "2024-01-20",
    "booking_time": "10:00",
    "payment_method": "cash_payment"
}
```

**Response:**
```json
{
    "message": "Booking created successfully",
    "data": {
        "id": 100001,
        "booking_reference": "BB-100001",
        "status": "pending",
        "total_amount": 100000
    }
}
```

#### 8. Get My Bookings
**Endpoint:** `GET /bookings`

**Authentication:** Required

**Query Parameters:**
- `status` (string, optional): Filter by status (pending, confirmed, completed, cancelled)
- `limit` (integer, optional): Number of results per page
- `offset` (integer, optional): Page offset

**Response:**
```json
{
    "data": [...],
    "total": 10,
    "per_page": 25,
    "current_page": 1
}
```

#### 9. Get Booking Details
**Endpoint:** `GET /bookings/{id}`

**Authentication:** Required

**Response:**
```json
{
    "data": {
        "id": 100001,
        "booking_reference": "BB-100001",
        "salon": {...},
        "service": {...},
        "status": "confirmed",
        "total_amount": 100000
    }
}
```

#### 10. Cancel Booking
**Endpoint:** `PUT /bookings/{id}/cancel`

**Authentication:** Required

**Request Body:**
```json
{
    "cancellation_reason": "Change of plans"
}
```

**Response:**
```json
{
    "message": "Booking cancelled successfully",
    "data": {...}
}
```

#### 11. Check Availability
**Endpoint:** `POST /availability/check`

**Authentication:** Required

**Request Body:**
```json
{
    "salon_id": 1,
    "service_id": 1,
    "date": "2024-01-20",
    "staff_id": 1
}
```

**Response:**
```json
{
    "data": {
        "available_slots": ["09:00", "10:00", "11:00", ...]
    }
}
```

#### 12. Process Payment
**Endpoint:** `POST /payment`

**Authentication:** Required

**Request Body:**
```json
{
    "booking_id": 100001,
    "payment_method": "digital_payment"
}
```

**Response:**
```json
{
    "message": "Payment processed successfully",
    "data": {
        "payment_link": "https://..."
    }
}
```

#### 13. Create Review
**Endpoint:** `POST /reviews`

**Authentication:** Required

**Request Body:**
```json
{
    "booking_id": 100001,
    "rating": 5,
    "comment": "Great service!",
    "attachments": []
}
```

**Response:**
```json
{
    "message": "Review created successfully",
    "data": {...}
}
```

#### 14. Get My Reviews
**Endpoint:** `GET /reviews`

**Authentication:** Required

**Response:**
```json
{
    "data": [...]
}
```

---

## Vendor API Endpoints

### All endpoints require vendor authentication

#### 15. Get Bookings List
**Endpoint:** `GET /bookings/list/{all}`

**Query Parameters:**
- `all` (string): "all" for all bookings, or status filter
- `status` (string, optional): Filter by status
- `date_from` (date, optional): Filter from date
- `date_to` (date, optional): Filter to date

**Response:**
```json
{
    "data": [...],
    "total": 50
}
```

#### 16. Confirm Booking
**Endpoint:** `PUT /bookings/confirm`

**Request Body:**
```json
{
    "booking_id": 100001
}
```

**Response:**
```json
{
    "message": "Booking confirmed successfully",
    "data": {...}
}
```

#### 17. Complete Booking
**Endpoint:** `PUT /bookings/complete`

**Request Body:**
```json
{
    "booking_id": 100001
}
```

**Response:**
```json
{
    "message": "Booking completed successfully",
    "data": {...}
}
```

#### 18. Cancel Booking
**Endpoint:** `PUT /bookings/cancel`

**Request Body:**
```json
{
    "booking_id": 100001,
    "cancellation_reason": "Salon closed"
}
```

**Response:**
```json
{
    "message": "Booking cancelled successfully",
    "data": {...}
}
```

#### 19. Get Staff List
**Endpoint:** `GET /staff/list`

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "specializations": ["Haircut", "Hair Color"]
        }
    ]
}
```

#### 20. Create Staff
**Endpoint:** `POST /staff/create`

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "09123456789",
    "specializations": ["Haircut"],
    "working_hours": {...}
}
```

**Response:**
```json
{
    "message": "Staff created successfully",
    "data": {...}
}
```

#### 21. Get Service List
**Endpoint:** `GET /service/list`

**Response:**
```json
{
    "data": [...],
    "total": 20
}
```

#### 22. Create Service
**Endpoint:** `POST /service/create`

**Request Body:**
```json
{
    "name": "Haircut",
    "category_id": 1,
    "description": "Professional haircut",
    "duration_minutes": 60,
    "price": 100000,
    "image": "file",
    "status": true
}
```

**Response:**
```json
{
    "message": "Service created successfully",
    "data": {...}
}
```

#### 23. Get Calendar Availability
**Endpoint:** `GET /calendar/availability`

**Query Parameters:**
- `date` (date, required): Date to check
- `staff_id` (integer, optional): Staff ID

**Response:**
```json
{
    "data": {
        "available_slots": ["09:00", "10:00", ...]
    }
}
```

#### 24. List Packages
**Endpoint:** `GET /packages`

**Description:** Get list of available packages

**Query Parameters:**
- `salon_id` (integer, optional): Filter by salon
- `service_id` (integer, optional): Filter by service
- `per_page` (integer, optional): Items per page (default: 15)

**Response:**
```json
{
    "message": "Data retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "5 Session Package",
            "sessions_count": 5,
            "total_price": 500000,
            "discount_percentage": 10.00,
            "validity_days": 90,
            "salon": {...},
            "service": {...}
        }
    ],
    "total": 10,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
}
```

#### 25. Get Package Details
**Endpoint:** `GET /packages/{id}`

**Description:** Get detailed information about a package

**Response:**
```json
{
    "message": "Data retrieved successfully",
    "data": {
        "id": 1,
        "name": "5 Session Package",
        "sessions_count": 5,
        "total_price": 500000,
        "discount_percentage": 10.00,
        "validity_days": 90,
        "salon": {...},
        "service": {...}
    }
}
```

#### 26. Purchase Package
**Endpoint:** `POST /packages/{id}/purchase`

**Description:** Purchase a package

**Request Body:**
```json
{
    "payment_method": "wallet"
}
```

**Payment Methods:**
- `wallet`: Pay from wallet balance
- `digital_payment`: Pay via payment gateway
- `cash_payment`: Pay at salon

**Response:**
```json
{
    "message": "Package purchased successfully",
    "data": {
        "package_id": 1,
        "package_name": "5 Session Package",
        "sessions_count": 5,
        "total_price": 500000,
        "validity_days": 90,
        "remaining_sessions": 5,
        "payment_method": "wallet",
        "payment_status": "paid"
    }
}
```

#### 27. Get Package Status
**Endpoint:** `GET /packages/{id}/status`

**Description:** Get package status and remaining sessions for the authenticated user

**Response:**
```json
{
    "message": "Data retrieved successfully",
    "data": {
        "package_id": 1,
        "package_name": "5 Session Package",
        "total_sessions": 5,
        "remaining_sessions": 3,
        "used_sessions": 2,
        "is_valid": true,
        "validity_days": 90,
        "usages": [
            {
                "session_number": 1,
                "used_at": "2024-01-15 10:00:00",
                "status": "used",
                "booking_id": 123
            }
        ]
    }
}
```

#### 28. Get Loyalty Points Balance
**Endpoint:** `GET /loyalty/points`

**Description:** Get loyalty points balance for the authenticated user

**Response:**
```json
{
    "message": "Data retrieved successfully",
    "data": {
        "total_points": 500,
        "used_points": 100,
        "available_points": 400
    }
}
```

#### 29. List Loyalty Campaigns
**Endpoint:** `GET /loyalty/campaigns`

**Description:** Get list of active loyalty campaigns

**Query Parameters:**
- `salon_id` (integer, optional): Filter by salon
- `per_page` (integer, optional): Items per page (default: 15)

**Response:**
```json
{
    "message": "Data retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Earn Points Campaign",
            "type": "points",
            "description": "Earn 1 point per 1000 spent",
            "start_date": "2024-01-01",
            "end_date": "2024-12-31",
            "salon": {...}
        }
    ],
    "total": 5,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
}
```

#### 30. Redeem Loyalty Points
**Endpoint:** `POST /loyalty/redeem`

**Description:** Redeem loyalty points for a campaign reward

**Request Body:**
```json
{
    "campaign_id": 1,
    "points": 100
}
```

**Response:**
```json
{
    "message": "Points redeemed successfully",
    "data": {
        "campaign_id": 1,
        "campaign_name": "Earn Points Campaign",
        "points_redeemed": 100,
        "remaining_points": 300,
        "reward": {
            "type": "wallet_credit",
            "value": 10000,
            "description": "10000 added to wallet",
            "wallet_balance": 110000
        }
    }
}
```

**Reward Types:**
- `discount_percentage`: Discount percentage for next booking
- `discount_amount`: Fixed discount amount for next booking
- `wallet_credit`: Credit added to wallet
- `cashback`: Cashback amount added to wallet
- `gift_card`: Gift card created (includes gift_card_id, gift_card_code, expires_at)
- `points_redeemed`: Points redeemed confirmation

**Example Response for Gift Card Campaign:**
```json
{
    "message": "Points redeemed successfully",
    "data": {
        "campaign_id": 1,
        "campaign_name": "Gift Card Campaign",
        "points_redeemed": 5000,
        "remaining_points": 5000,
        "reward": {
            "type": "gift_card",
            "gift_card_id": 123,
            "gift_card_code": "GCABCD1234",
            "value": 50000,
            "description": "Gift card worth 50000 created",
            "expires_at": "2025-12-31"
        }
    }
}
```

**Error Responses:**
- `400` - Campaign not active, expired, or not started
- `400` - Insufficient loyalty points
- `400` - Points exceed maximum or below minimum per redemption
- `400` - Campaign redemption limit reached
- `400` - User redemption limit reached
- `400` - Gift card amount below minimum (for gift card campaigns)

---

## Error Responses

All endpoints return errors in the following format:

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

### HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden (Validation errors, Authorization errors)
- `404` - Not Found
- `500` - Internal Server Error

---

## Rate Limiting

Sensitive endpoints have rate limiting:
- Booking creation: 10 requests per minute
- Payment processing: 5 requests per minute
- Package purchase: 5 requests per minute
- Gift card purchase: 5 requests per minute
- Subscription purchase: 5 requests per minute
- Points redemption: 10 requests per minute
- Booking cancellation: 5 requests per minute
- Review creation: 5 requests per minute
- Search endpoints: 120 requests per minute (public), 60 requests per minute (authenticated)
- General GET endpoints: 60 requests per minute
- General POST/PUT endpoints: 10-30 requests per minute (varies by endpoint)

---

## Caching

The following endpoints are cached:
- Salon search results: 5 minutes
- Category list: 24 hours
- Popular/Top Rated salons: 1 hour
- Ranking scores: 30 minutes
- Badge evaluations: 1 hour

Cache is automatically invalidated when data changes.

---

## Additional Customer Endpoints

### Gift Card Endpoints

#### Purchase Gift Card
**Endpoint:** `POST /gift-card/purchase`

**Request Body:**
```json
{
    "salon_id": 1,
    "amount": 50000,
    "expires_at": "2024-12-31"
}
```

**Response:**
```json
{
    "message": "Gift card purchased successfully",
    "data": {
        "code": "GC-ABC123",
        "amount": 50000,
        "expires_at": "2024-12-31"
    }
}
```

#### Redeem Gift Card
**Endpoint:** `POST /gift-card/redeem`

**Request Body:**
```json
{
    "code": "GC-ABC123"
}
```

**Response:**
```json
{
    "message": "Gift card redeemed successfully",
    "data": {
        "code": "GC-ABC123",
        "amount": 50000,
        "redeemed_at": "2024-01-15 10:00:00"
    }
}
```

#### List Gift Cards
**Endpoint:** `GET /gift-card/list`

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "code": "GC-ABC123",
            "amount": 50000,
            "status": "active",
            "expires_at": "2024-12-31"
        }
    ]
}
```

### Consultation Endpoints

#### List Consultations
**Endpoint:** `GET /consultations/list`

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "salon_id": 1,
            "type": "pre_booking",
            "duration_minutes": 15,
            "price": 50000
        }
    ]
}
```

#### Book Consultation
**Endpoint:** `POST /consultations/book`

**Request Body:**
```json
{
    "salon_id": 1,
    "consultation_id": 1,
    "date": "2024-01-20",
    "time": "10:00"
}
```

**Response:**
```json
{
    "message": "Consultation booked successfully",
    "data": {...}
}
```

#### Check Consultation Availability
**Endpoint:** `POST /consultations/check-availability`

**Request Body:**
```json
{
    "salon_id": 1,
    "consultation_id": 1,
    "date": "2024-01-20"
}
```

**Response:**
```json
{
    "data": {
        "available_slots": ["09:00", "10:00", "11:00"]
    }
}
```

### Retail Endpoints

#### List Retail Products
**Endpoint:** `GET /retail/products`

**Query Parameters:**
- `salon_id` (integer, optional): Filter by salon
- `category` (string, optional): Filter by category
- `per_page` (integer, optional): Items per page

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Hair Shampoo",
            "price": 50000,
            "stock_quantity": 10,
            "image": "https://...",
            "salon": {...}
        }
    ]
}
```

#### Create Retail Order
**Endpoint:** `POST /retail/orders`

**Request Body:**
```json
{
    "salon_id": 1,
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        }
    ],
    "payment_method": "wallet"
}
```

**Response:**
```json
{
    "message": "Retail order created successfully",
    "data": {
        "order_id": 1,
        "order_number": "RO-100001",
        "total_amount": 100000,
        "status": "pending"
    }
}
```

---

## Additional Vendor Endpoints

### Salon Registration

#### Register Salon
**Endpoint:** `POST /salon/register`

**Request Body:**
```json
{
    "business_type": "salon",
    "license_number": "LIC123456",
    "license_expiry": "2025-12-31",
    "working_hours": {
        "monday": {"open": "09:00", "close": "18:00"},
        "tuesday": {"open": "09:00", "close": "18:00"}
    }
}
```

**Response:**
```json
{
    "message": "Salon registered successfully. Waiting for admin approval.",
    "data": {
        "salon_id": 1,
        "verification_status": "pending"
    }
}
```

#### Upload Documents
**Endpoint:** `POST /salon/documents/upload`

**Request Body:**
- `documents` (array, required): Array of files (PDF, JPEG, PNG, JPG)
- Max 10 files, 5MB each

**Response:**
```json
{
    "message": "Documents uploaded successfully",
    "data": {
        "documents": ["path/to/doc1.pdf", "path/to/doc2.jpg"],
        "uploaded_count": 2
    }
}
```

#### Update Working Hours
**Endpoint:** `POST /salon/working-hours/update`

**Request Body:**
```json
{
    "working_hours": {
        "monday": {"open": "09:00", "close": "18:00"},
        "tuesday": {"open": "09:00", "close": "18:00"}
    }
}
```

**Response:**
```json
{
    "message": "Working hours updated successfully",
    "data": {...}
}
```

#### Manage Holidays
**Endpoint:** `POST /salon/holidays/manage`

**Request Body:**
```json
{
    "holidays": ["2024-01-01", "2024-03-21", "2024-04-01"]
}
```

**Response:**
```json
{
    "message": "Holidays updated successfully",
    "data": {...}
}
```

### Profile Management

#### Get Profile
**Endpoint:** `GET /profile`

**Response:**
```json
{
    "data": {
        "salon": {...},
        "store": {...},
        "badges": [...],
        "subscriptions": [...]
    }
}
```

#### Update Profile
**Endpoint:** `POST /profile/update`

**Request Body:**
```json
{
    "working_hours": {...},
    "holidays": [...]
}
```

**Response:**
```json
{
    "message": "Profile updated successfully",
    "data": {...}
}
```

### Subscription Management

#### Get Subscription Plans
**Endpoint:** `GET /subscription/plans`

**Response:**
```json
{
    "data": {
        "featured_listing": {
            "7": {"duration_days": 7, "price": 50000},
            "30": {"duration_days": 30, "price": 150000}
        },
        "boost_ads": {
            "7": {"duration_days": 7, "price": 75000},
            "30": {"duration_days": 30, "price": 200000}
        }
    }
}
```

#### Purchase Subscription
**Endpoint:** `POST /subscription/purchase`

**Request Body:**
```json
{
    "subscription_type": "featured_listing",
    "duration_days": 30,
    "payment_method": "digital_payment",
    "payment_gateway": "stripe",
    "payment_platform": "web"
}
```

**Response:**
```json
{
    "message": "Subscription purchased successfully",
    "data": {
        "subscription_id": 1,
        "redirect_url": "https://payment-gateway.com/..."
    }
}
```

#### Get Subscription History
**Endpoint:** `GET /subscription/history`

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "subscription_type": "featured_listing",
            "start_date": "2024-01-01",
            "end_date": "2024-01-31",
            "status": "active",
            "amount_paid": 150000
        }
    ]
}
```

### Retail Management

#### List Products
**Endpoint:** `GET /retail/products`

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Hair Shampoo",
            "price": 50000,
            "stock_quantity": 10,
            "status": "active"
        }
    ]
}
```

#### Create Product
**Endpoint:** `POST /retail/products`

**Request Body:**
```json
{
    "name": "Hair Shampoo",
    "description": "Professional hair shampoo",
    "price": 50000,
    "stock_quantity": 10,
    "category": "skincare",
    "image": "file"
}
```

**Response:**
```json
{
    "message": "Product created successfully",
    "data": {...}
}
```

#### List Orders
**Endpoint:** `GET /retail/orders`

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "order_number": "RO-100001",
            "user": {...},
            "items": [...],
            "total_amount": 100000,
            "status": "pending"
        }
    ]
}
```

---

## Error Codes

### Common Error Codes

| Code | Message | Description |
|------|---------|-------------|
| `validation` | Validation failed | Request validation errors |
| `unauthorized` | Unauthorized access | Authentication required or invalid token |
| `forbidden` | Forbidden | User doesn't have permission |
| `not_found` | Resource not found | Requested resource doesn't exist |
| `booking` | Booking error | Booking-related errors (not available, already exists, etc.) |
| `payment` | Payment error | Payment processing errors |
| `package` | Package error | Package-related errors (expired, invalid, etc.) |
| `loyalty` | Loyalty error | Loyalty points errors (insufficient points, etc.) |
| `gift_card` | Gift card error | Gift card errors (invalid code, expired, etc.) |
| `subscription` | Subscription error | Subscription errors (invalid plan, etc.) |

### Error Response Examples

#### Validation Error (403)
```json
{
    "errors": [
        {
            "code": "validation",
            "message": "The salon_id field is required."
        },
        {
            "code": "validation",
            "message": "The booking_date must be a date after today."
        }
    ]
}
```

#### Unauthorized (401)
```json
{
    "errors": [
        {
            "code": "unauthorized",
            "message": "Authentication required"
        }
    ]
}
```

#### Not Found (404)
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

#### Booking Error (403)
```json
{
    "errors": [
        {
            "code": "booking",
            "message": "Time slot is not available"
        }
    ]
}
```

---

## Authentication

### Customer API Authentication

1. Register/Login via main app authentication
2. Receive Bearer token
3. Include token in Authorization header:
   ```
   Authorization: Bearer {token}
   ```

### Vendor API Authentication

1. Vendor login via vendor API
2. Receive vendor token
3. Include token in Authorization header:
   ```
   Authorization: Bearer {vendor_token}
   ```

### Token Expiration

- Tokens expire after 24 hours of inactivity
- Refresh token endpoint available (if implemented in main app)

---

## Pagination

All list endpoints support pagination:

**Query Parameters:**
- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 15, max: 100)
- `limit` (integer): Alternative to per_page
- `offset` (integer): Alternative to page

**Response Format:**
```json
{
    "data": [...],
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7,
    "from": 1,
    "to": 15
}
```

---

## Filtering & Sorting

### Filtering

Most list endpoints support filtering via query parameters:

**Examples:**
- `?status=confirmed` - Filter by status
- `?salon_id=1` - Filter by salon
- `?date_from=2024-01-01&date_to=2024-01-31` - Date range filter
- `?min_rating=4.0` - Minimum rating filter

### Sorting

Sorting is supported via query parameters:

**Examples:**
- `?sort_by=booking_date&sort_order=desc` - Sort by booking date descending
- `?sort_by=total_amount&sort_order=asc` - Sort by total amount ascending

**Default Sorting:**
- Most endpoints default to `created_at DESC` (newest first)

---

## File Uploads

### Supported Formats

- **Images**: JPEG, PNG, JPG (max 2MB)
- **Documents**: PDF, JPEG, PNG, JPG (max 5MB)

### Upload Endpoints

- Service images: `POST /service/create` or `POST /service/update/{id}`
- Staff avatars: `POST /staff/create` or `POST /staff/update/{id}`
- Review attachments: `POST /reviews`
- Salon documents: `POST /salon/documents/upload`
- Banner images: `POST /subscription/purchase` (for banner ads)

### Upload Format

Use `multipart/form-data` for file uploads:

```
Content-Type: multipart/form-data

image: [file]
```

---

## Webhooks (Future)

Payment webhooks will be available for:
- Booking payment success/failure
- Subscription payment success/failure
- Package payment success/failure

Webhook endpoints will be documented separately when implemented.

