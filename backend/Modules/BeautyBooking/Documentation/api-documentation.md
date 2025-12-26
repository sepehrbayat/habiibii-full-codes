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

#### 5a. Get Monthly Top Rated Salons
**Endpoint:** `GET /salons/monthly-top-rated`

**Description:** Get list of monthly top rated salons from monthly reports

**Query Parameters:**
- `month` (integer, optional): Month number (1-12), defaults to previous month
- `year` (integer, optional): Year, defaults to previous month's year

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Salon Name",
            "avg_rating": 4.8,
            "total_bookings": 500,
            "rank": 1
        }
    ]
}
```

#### 5b. Get Trending Clinics
**Endpoint:** `GET /salons/trending-clinics`

**Description:** Get list of trending clinics from monthly reports

**Query Parameters:**
- `month` (integer, optional): Month number (1-12), defaults to previous month
- `year` (integer, optional): Year, defaults to previous month's year

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Clinic Name",
            "bookings_count": 300,
            "avg_rating": 4.7,
            "rank": 1
        }
    ]
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

**cURL Example:**
```bash
curl -X POST "https://your-domain.com/api/v1/beautybooking/bookings" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "salon_id": 1,
    "service_id": 1,
    "staff_id": 1,
    "booking_date": "2024-01-20",
    "booking_time": "10:00",
    "payment_method": "cash_payment"
  }'
```

**Success Response (201):**
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

**Error Response (403) - Validation Error:**
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

**Error Response (403) - Time Slot Not Available:**
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
        "staff": {...},
        "status": "confirmed",
        "payment_status": "paid"
    }
}
```

#### 9a. Get Booking Conversation
**Endpoint:** `GET /bookings/{id}/conversation`

**Authentication:** Required

**Description:** Get conversation/chat for a booking

**Response:**
```json
{
    "data": {
        "conversation_id": 123,
        "messages": [...]
    }
}
```

#### 9b. Get Service Suggestions
**Endpoint:** `GET /services/{id}/suggestions`

**Authentication:** Required

**Description:** Get cross-selling/upsell service suggestions for a service

**Response:**
```json
{
    "data": [
        {
            "id": 2,
            "name": "Complementary Service",
            "relation_type": "complementary",
            "price": 50000
        }
    ]
}
```

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
    "message": "Booking marked as completed",
    "data": {
        "id": 100001,
        "status": "completed"
    }
}
```

#### 17a. Mark Booking as Paid
**Endpoint:** `PUT /bookings/mark-paid`

**Request Body:**
```json
{
    "booking_id": 100001
}
```

**Response:**
```json
{
    "message": "Booking marked as paid",
    "data": {
        "id": 100001,
        "payment_status": "paid"
    }
}
```

#### 17b. Get Booking Details (Vendor)
**Endpoint:** `GET /bookings/details`

**Query Parameters:**
- `booking_id` (integer, required): Booking ID

**Response:**
```json
{
    "data": {
        "id": 100001,
        "booking_reference": "BB-100001",
        "customer": {...},
        "service": {...},
        "staff": {...},
        "status": "confirmed"
    }
}
```

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
            "specializations": ["haircut", "styling"],
            "status": "active"
        }
    ]
}
```

#### 19a. Get Staff Details
**Endpoint:** `GET /staff/details/{id}`

**Response:**
```json
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "specializations": [...],
        "working_hours": {...},
        "bookings_count": 50
    }
}
```

#### 19b. Update Staff
**Endpoint:** `POST /staff/update/{id}`

**Request Body:**
```json
{
    "name": "John Doe",
    "specializations": ["haircut", "styling"],
    "working_hours": {...}
}
```

**Response:**
```json
{
    "message": "Staff updated successfully",
    "data": {...}
}
```

#### 19c. Delete Staff
**Endpoint:** `DELETE /staff/delete/{id}`

**Response:**
```json
{
    "message": "Staff deleted successfully"
}
```

#### 19d. Toggle Staff Status
**Endpoint:** `GET /staff/status/{id}`

**Response:**
```json
{
    "message": "Staff status updated",
    "data": {
        "id": 1,
        "status": "inactive"
    }
}
```

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
    "category_id": 1,
    "name": "Haircut",
    "description": "Professional haircut",
    "price": 100000,
    "duration_minutes": 30
}
```

**Response:**
```json
{
    "message": "Service created successfully",
    "data": {
        "id": 1,
        "name": "Haircut"
    }
}
```

#### 22a. Update Service
**Endpoint:** `POST /service/update/{id}`

**Request Body:**
```json
{
    "name": "Premium Haircut",
    "price": 150000
}
```

**Response:**
```json
{
    "message": "Service updated successfully",
    "data": {...}
}
```

#### 22b. Get Service Details
**Endpoint:** `GET /service/details/{id}`

**Response:**
```json
{
    "data": {
        "id": 1,
        "name": "Haircut",
        "category": {...},
        "bookings_count": 100
    }
}
```

#### 22c. Delete Service
**Endpoint:** `DELETE /service/delete/{id}`

**Response:**
```json
{
    "message": "Service deleted successfully"
}
```

#### 22d. Toggle Service Status
**Endpoint:** `GET /service/status/{id}`

**Response:**
```json
{
    "message": "Service status updated",
    "data": {
        "id": 1,
        "status": "inactive"
    }
}
```

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
- `date` (string, required): Date in Y-m-d format
- `staff_id` (integer, optional): Filter by staff member

**Response:**
```json
{
    "data": {
        "date": "2024-01-20",
        "available_slots": [
            "09:00", "10:00", "11:00", "14:00", "15:00"
        ]
    }
}
```

#### 23a. Create Calendar Block
**Endpoint:** `POST /calendar/blocks/create`

**Request Body:**
```json
{
    "staff_id": 1,
    "block_type": "holiday",
    "start_date": "2024-01-01",
    "end_date": "2024-01-01",
    "reason": "New Year Holiday"
}
```

**Response:**
```json
{
    "message": "Calendar block created successfully",
    "data": {
        "id": 1,
        "block_type": "holiday"
    }
}
```

#### 23b. Delete Calendar Block
**Endpoint:** `DELETE /calendar/blocks/delete/{id}`

**Response:**
```json
{
    "message": "Calendar block deleted successfully"
}
```

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

### Rate Limit Overview

Sensitive endpoints have rate limiting to prevent abuse and ensure fair usage:

| Endpoint Type | Rate Limit | Window |
|---------------|-----------|--------|
| Booking creation | 10 requests | per minute |
| Payment processing | 5 requests | per minute |
| Package purchase | 5 requests | per minute |
| Gift card purchase | 5 requests | per minute |
| Subscription purchase | 5 requests | per minute |
| Points redemption | 10 requests | per minute |
| Booking cancellation | 5 requests | per minute |
| Review creation | 5 requests | per minute |
| Search endpoints (public) | 120 requests | per minute |
| Search endpoints (authenticated) | 60 requests | per minute |
| General GET endpoints | 60 requests | per minute |
| General POST/PUT endpoints | 10-30 requests | per minute (varies by endpoint) |

### Rate Limit Headers

When making requests, the API includes rate limit information in response headers:

```
X-RateLimit-Limit: 10
X-RateLimit-Remaining: 7
X-RateLimit-Reset: 1640000000
```

- `X-RateLimit-Limit`: Maximum number of requests allowed in the window
- `X-RateLimit-Remaining`: Number of requests remaining in the current window
- `X-RateLimit-Reset`: Unix timestamp when the rate limit window resets

### Handling Rate Limit Errors

When the rate limit is exceeded, you'll receive a `429 Too Many Requests` response:

**Response (429):**
```json
{
    "errors": [
        {
            "code": "rate_limit_exceeded",
            "message": "Too many requests. Please try again later."
        }
    ]
}
```

**Response Headers:**
```
HTTP/1.1 429 Too Many Requests
X-RateLimit-Limit: 10
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1640000000
Retry-After: 60
```

**Best Practices:**
- Monitor `X-RateLimit-Remaining` header to avoid hitting limits
- Implement exponential backoff when receiving 429 responses
- Use `Retry-After` header value to determine when to retry
- Cache responses when possible to reduce API calls

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
    "products": [
        {
            "product_id": 1,
            "quantity": 2
        }
    ],
    "payment_method": "digital_payment"
}
```

**Response:**
```json
{
    "message": "Retail order created successfully",
    "data": {
        "order_id": 1,
        "total_amount": 200000
    }
}
```

### Retail Management (Vendor)

#### List Products (Vendor)
**Endpoint:** `GET /retail/products`

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Hair Shampoo",
            "price": 50000,
            "stock": 100
        }
    ]
}
```

#### Create Product (Vendor)
**Endpoint:** `POST /retail/products`

**Request Body:**
```json
{
    "name": "Hair Shampoo",
    "description": "Premium hair shampoo",
    "price": 50000,
    "stock": 100,
    "image": [file]
}
```

**Response:**
```json
{
    "message": "Product created successfully",
    "data": {
        "id": 1,
        "name": "Hair Shampoo"
    }
}
```

#### List Retail Orders (Vendor)
**Endpoint:** `GET /retail/orders`

**Query Parameters:**
- `status` (string, optional): Filter by order status
- `date_from` (string, optional): Start date
- `date_to` (string, optional): End date

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "customer": {...},
            "products": [...],
            "total_amount": 200000,
            "status": "completed"
        }
    ]
}
```

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

### Finance & Reports

#### Get Payout Summary
**Endpoint:** `GET /finance/payout-summary`

**Query Parameters:**
- `date_from` (string, optional): Start date (Y-m-d)
- `date_to` (string, optional): End date (Y-m-d)

**Response:**
```json
{
    "data": {
        "total_revenue": 5000000,
        "total_commission": 500000,
        "total_service_fee": 100000,
        "net_payout": 4400000
    }
}
```

#### Get Transaction History
**Endpoint:** `GET /finance/transactions`

**Query Parameters:**
- `transaction_type` (string, optional): Filter by type
- `date_from` (string, optional): Start date
- `date_to` (string, optional): End date
- `limit` (integer, optional): Results per page
- `offset` (integer, optional): Page offset

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "transaction_type": "commission",
            "amount": 10000,
            "status": "completed",
            "created_at": "2024-01-15 10:00:00"
        }
    ],
    "total": 50
}
```

### Badge Status

#### Get Badge Status
**Endpoint:** `GET /badge/status`

**Response:**
```json
{
    "data": {
        "badges": [
            {
                "badge_type": "top_rated",
                "status": "active",
                "earned_at": "2024-01-01"
            },
            {
                "badge_type": "featured",
                "status": "active",
                "expires_at": "2024-01-31"
            }
        ]
    }
}
```

### Package Management (Vendor)

#### List Packages
**Endpoint:** `GET /packages/list`

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Haircut Package",
            "total_sessions": 10,
            "used_sessions": 3,
            "price": 800000
        }
    ]
}
```

#### Get Package Usage Statistics
**Endpoint:** `GET /packages/usage-stats`

**Response:**
```json
{
    "data": {
        "total_packages": 5,
        "active_packages": 3,
        "total_sessions_sold": 50,
        "total_sessions_used": 15,
        "revenue": 4000000
    }
}
```

### Gift Card Management (Vendor)

#### List Gift Cards
**Endpoint:** `GET /gift-cards/list`

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "code": "GC-ABC123",
            "amount": 200000,
            "balance": 150000,
            "status": "active"
        }
    ]
}
```

#### Get Gift Card Redemption History
**Endpoint:** `GET /gift-cards/redemption-history`

**Query Parameters:**
- `date_from` (string, optional): Start date
- `date_to` (string, optional): End date

**Response:**
```json
{
    "data": [
        {
            "gift_card_id": 1,
            "code": "GC-ABC123",
            "redeemed_amount": 50000,
            "redeemed_at": "2024-01-15 10:00:00",
            "customer": {...}
        }
    ]
}
```

### Loyalty Campaign Management (Vendor)

#### List Loyalty Campaigns
**Endpoint:** `GET /loyalty/campaigns`

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "New Year Campaign",
            "points_per_booking": 10,
            "points_per_amount": 1,
            "status": "active"
        }
    ]
}
```

#### Get Points History
**Endpoint:** `GET /loyalty/points-history`

**Query Parameters:**
- `date_from` (string, optional): Start date
- `date_to` (string, optional): End date

**Response:**
```json
{
    "data": [
        {
            "booking_id": 100001,
            "points_awarded": 10,
            "awarded_at": "2024-01-15 10:00:00"
        }
    ]
}
```

#### Get Campaign Statistics
**Endpoint:** `GET /loyalty/campaign/{id}/stats`

**Response:**
```json
{
    "data": {
        "campaign_id": 1,
        "total_points_awarded": 500,
        "total_points_redeemed": 200,
        "active_users": 50,
        "total_bookings": 100
    }
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

#### Obtaining API Token

1. **Register/Login**: Use the main application authentication endpoints
   - Registration: `POST /api/v1/auth/register`
   - Login: `POST /api/v1/auth/login`

2. **Receive Bearer Token**: The authentication response includes a token:
   ```json
   {
     "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
     "user": {
       "id": 1,
       "name": "John Doe",
       "email": "john@example.com"
     }
   }
   ```

3. **Include Token in Requests**: Add the token to the Authorization header:
   ```
   Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
   ```

#### Example cURL Request
```bash
curl -X GET "https://your-domain.com/api/v1/beautybooking/salons/popular" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Accept: application/json"
```

### Vendor API Authentication

#### Obtaining Vendor Token

1. **Vendor Login**: Use the vendor authentication endpoint
   - Login: `POST /api/v1/vendor/auth/login`
   - Request Body:
     ```json
     {
       "email": "vendor@example.com",
       "password": "password123"
     }
     ```

2. **Receive Vendor Token**: The response includes a vendor token:
   ```json
   {
     "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
     "vendor": {
       "id": 1,
       "store_id": 1,
       "name": "Salon Name"
     }
   }
   ```

3. **Include Token in Requests**: Add the token to the Authorization header:
   ```
   Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
   ```

#### Example cURL Request
```bash
curl -X GET "https://your-domain.com/api/v1/beautybooking/vendor/bookings/list/all" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Accept: application/json"
```

### Token Expiration

- **Token Lifetime**: Tokens expire after 24 hours of inactivity
- **Refresh Token**: If refresh token endpoint is implemented in main app, use it to obtain new tokens
- **Token Refresh Endpoint**: `POST /api/v1/auth/refresh` (if available)
- **Handling Expired Tokens**: When a token expires, you'll receive a `401 Unauthorized` response:
  ```json
  {
    "errors": [
      {
        "code": "unauthorized",
        "message": "Token has expired"
      }
    ]
  }
  ```
  In this case, you should re-authenticate to obtain a new token.

### Authentication Errors

| HTTP Status | Error Code | Description | Solution |
|-------------|------------|-------------|----------|
| 401 | `unauthorized` | No token provided | Include Authorization header |
| 401 | `token_expired` | Token has expired | Re-authenticate to get new token |
| 401 | `token_invalid` | Token is invalid or malformed | Check token format and re-authenticate |
| 403 | `forbidden` | Token valid but insufficient permissions | Check user/vendor permissions |

---

## Pagination

All list endpoints support pagination with consistent response format.

### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Page number (1-based) |
| `per_page` | integer | 15 | Items per page (max: 100) |
| `limit` | integer | 15 | Alternative to per_page |
| `offset` | integer | 0 | Alternative to page (0-based) |

### Response Format

**Paginated Response:**
```json
{
    "message": "Data retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Item 1"
        },
        {
            "id": 2,
            "name": "Item 2"
        }
    ],
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7,
    "from": 1,
    "to": 15
}
```

**Response Fields:**
- `data`: Array of items for the current page
- `total`: Total number of items across all pages
- `per_page`: Number of items per page
- `current_page`: Current page number
- `last_page`: Last page number
- `from`: Starting item number for current page
- `to`: Ending item number for current page

### Pagination Examples

**Example 1: Get first page (default)**
```bash
GET /api/v1/beautybooking/bookings
```

**Example 2: Get second page with 25 items per page**
```bash
GET /api/v1/beautybooking/bookings?page=2&per_page=25
```

**Example 3: Using limit and offset**
```bash
GET /api/v1/beautybooking/bookings?limit=25&offset=25
```

**cURL Example:**
```bash
curl -X GET "https://your-domain.com/api/v1/beautybooking/bookings?page=2&per_page=25" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Pagination Best Practices

1. **Default Page Size**: Use default `per_page=15` unless you need more items
2. **Maximum Page Size**: Don't exceed `per_page=100` to avoid performance issues
3. **Page Navigation**: Use `current_page` and `last_page` to build pagination UI
4. **Empty Results**: When `total=0`, the `data` array will be empty
5. **Out of Range**: Requesting a page beyond `last_page` returns an empty `data` array

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

