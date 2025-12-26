# Beauty Booking Module - API Configuration for React Frontend

## Backend Server Configuration

### Laravel Development Server Port
The Laravel backend runs on **port 8000** by default.

**Start Laravel Server:**
```bash
php artisan serve
# Server will start at: http://localhost:8000
```

### API Base URL

**Development Environment:**
```
http://localhost:8000/api/v1/beautybooking/
```

**Production Environment:**
```
https://your-domain.com/api/v1/beautybooking/
```

---

## React Frontend Configuration

### Environment Variables

Create a `.env` file in your React project root:

```env
# Development
REACT_APP_API_BASE_URL=http://localhost:8000/api/v1/beautybooking

# Production (update with your actual domain)
# REACT_APP_API_BASE_URL=https://your-domain.com/api/v1/beautybooking
```

### Axios Configuration Example

```javascript
// src/config/api.js
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api/v1/beautybooking',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 30000, // 30 seconds
});

// Add token to requests if available
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Handle response errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Handle unauthorized - redirect to login
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

### Usage Example

```javascript
// src/services/bookingService.js
import api from '../config/api';

export const bookingService = {
  // Get all bookings
  getBookings: async (params = {}) => {
    const response = await api.get('/bookings', { params });
    return response.data;
  },

  // Create booking
  createBooking: async (bookingData) => {
    const response = await api.post('/bookings', bookingData);
    return response.data;
  },

  // Get booking details
  getBooking: async (id) => {
    const response = await api.get(`/bookings/${id}`);
    return response.data;
  },

  // Cancel booking
  cancelBooking: async (id, reason) => {
    const response = await api.put(`/bookings/${id}/cancel`, {
      cancellation_reason: reason,
    });
    return response.data;
  },
};
```

---

## CORS Configuration

### Laravel CORS Setup

Make sure Laravel CORS is configured to allow requests from your React frontend.

**File:** `config/cors.php`

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',  // React dev server default
        'http://localhost:3001',  // Alternative React port
        'http://127.0.0.1:3000',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

**Or use environment variables:**

```env
# .env
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:3001
```

---

## Authentication Endpoints

### Login
```
POST http://localhost:8000/api/v1/auth/login
```

**Request Body:**
```json
{
  "email": "customer@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "f_name": "John",
    "l_name": "Doe",
    "email": "customer@example.com"
  }
}
```

### Sign Up
```
POST http://localhost:8000/api/v1/auth/sign-up
```

### Using the Token

Include the token in all authenticated API requests:
```
Authorization: Bearer {token}
```

---

## Complete API Endpoint List

All endpoints are relative to the base URL: `http://localhost:8000/api/v1/beautybooking/`

### Public Endpoints (No Authentication)
- `GET /salons/search` - Search salons
- `GET /salons/popular` - Get popular salons
- `GET /salons/top-rated` - Get top-rated salons
- `GET /salons/monthly-top-rated` - Get monthly top-rated salons
- `GET /salons/trending-clinics` - Get trending clinics
- `GET /salons/category-list` - Get categories list
- `GET /salons/{id}` - Get salon details
- `GET /reviews/{salon_id}` - Get salon reviews

### Authenticated Endpoints (Require Bearer Token)
- `GET /services/{id}/suggestions` - Get service suggestions
- `POST /availability/check` - Check availability
- `POST /bookings/` - Create booking
- `GET /bookings/` - List bookings
- `GET /bookings/{id}` - Get booking details
- `GET /bookings/{id}/conversation` - Get booking conversation
- `PUT /bookings/{id}/reschedule` - Reschedule booking
- `PUT /bookings/{id}/cancel` - Cancel booking
- `POST /payment` - Process payment
- `GET /packages/` - List packages
- `GET /packages/{id}` - Get package details
- `POST /packages/{id}/purchase` - Purchase package
- `GET /packages/{id}/status` - Get package status
- `GET /packages/{id}/usage-history` - Get package usage history
- `GET /loyalty/points` - Get loyalty points
- `GET /loyalty/campaigns` - Get loyalty campaigns
- `POST /loyalty/redeem` - Redeem loyalty points
- `POST /reviews/` - Create review
- `GET /reviews/` - List user reviews
- `POST /gift-card/purchase` - Purchase gift card
- `POST /gift-card/redeem` - Redeem gift card
- `GET /gift-card/list` - List gift cards
- `GET /consultations/list` - List consultations
- `POST /consultations/book` - Book consultation
- `POST /consultations/check-availability` - Check consultation availability
- `GET /retail/products` - List retail products
- `GET /retail/orders` - List retail orders
- `GET /retail/orders/{id}` - Get retail order details
- `POST /retail/orders` - Create retail order

---

## Testing the Connection

### Using cURL

```bash
# Test public endpoint
curl http://localhost:8000/api/v1/beautybooking/salons/search

# Test authenticated endpoint (replace {token} with actual token)
curl -H "Authorization: Bearer {token}" \
     http://localhost:8000/api/v1/beautybooking/bookings
```

### Using Postman

1. Set base URL: `http://localhost:8000/api/v1/beautybooking`
2. For authenticated endpoints, add header:
   - Key: `Authorization`
   - Value: `Bearer {your_token}`

---

## Troubleshooting

### CORS Errors
If you see CORS errors in browser console:
1. Check `config/cors.php` in Laravel
2. Verify your React app origin is in `allowed_origins`
3. Clear Laravel config cache: `php artisan config:clear`

### Connection Refused
If you get "Connection refused" error:
1. Verify Laravel server is running: `php artisan serve`
2. Check the port (should be 8000)
3. Verify firewall isn't blocking the port

### 404 Not Found
If endpoints return 404:
1. Verify routes are registered: `php artisan route:list | grep beautybooking`
2. Clear route cache: `php artisan route:clear`
3. Check module is enabled in `modules_statuses.json`

### 401 Unauthorized
If you get 401 errors:
1. Verify token is included in Authorization header
2. Check token hasn't expired
3. Verify user is authenticated: `php artisan tinker` then `User::find(1)`

---

## Important Notes

1. **Port**: Laravel backend runs on port **8000** by default
2. **Base URL**: All Beauty Booking API endpoints are under `/api/v1/beautybooking/`
3. **Authentication**: Most endpoints require Bearer token authentication
4. **CORS**: Must be configured to allow React frontend origin
5. **Rate Limiting**: Endpoints have rate limits (see main checklist document)

---

**Last Updated**: 2025-01-28
**Version**: 1.0

