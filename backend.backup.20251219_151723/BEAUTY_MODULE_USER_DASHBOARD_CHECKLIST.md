# Beauty Booking Module - User Dashboard Backend Features Checklist

## Overview
This document provides a comprehensive checklist of all backend features available for the Beauty Booking module user dashboard. The React frontend team should verify that all these features are implemented in the user dashboard.

**Important Notes:**
- The Beauty Booking module must have a **completely independent dashboard** separate from other modules
- All API endpoints are prefixed with `/api/v1/beautybooking/`
- All endpoints require authentication via `auth:api` middleware (except public routes)
- All endpoints support pagination with `limit`, `offset`, `per_page` parameters
- All responses follow standard format: `{message, data, total, per_page, current_page, last_page}`

---

## 1. Dashboard Overview ✅

### 1.1 Dashboard Homepage
- [ ] Display upcoming bookings (next 5 bookings)
- [ ] Show booking summary cards (total bookings, pending, confirmed, completed)
- [ ] Display quick stats (total spent, loyalty points, gift cards balance)
- [ ] Show recent activity feed

**Backend Endpoint:**
- `GET /api/v1/beautybooking/bookings/` with `type=upcoming` filter

**Data Available:**
- Upcoming bookings with salon, service, staff details
- Booking status, date, time, amount
- Payment status

---

## 2. Booking Management ✅

### 2.1 List Bookings
- [ ] Display all user bookings with pagination
- [ ] Filter by status (pending, confirmed, completed, cancelled)
- [ ] Filter by type (upcoming, past, cancelled)
- [ ] Filter by date range (date_from, date_to)
- [ ] Filter by service_id or staff_id
- [ ] Sort by latest bookings first
- [ ] Show booking reference, salon name, service name, date, time, status, payment status

**Backend Endpoint:**
- `GET /api/v1/beautybooking/bookings/`

**Query Parameters:**
- `status` (string): Filter by booking status
- `type` (string): Filter by type (upcoming, past, cancelled)
- `service_id` (integer): Filter by service
- `staff_id` (integer): Filter by staff
- `date_from` (date): Start date filter
- `date_to` (date): End date filter
- `limit` (integer): Items per page (default: 25)
- `offset` (integer): Pagination offset (default: 0)

### 2.2 Booking Details
- [ ] Display full booking details
- [ ] Show salon information (name, address, phone, image)
- [ ] Show service details (name, duration, price)
- [ ] Show staff information (if assigned)
- [ ] Display booking reference, date, time, status
- [ ] Show payment information (method, status, amount)
- [ ] Display cancellation deadline (24 hours before booking)
- [ ] Show cancellation fee information
- [ ] Display review status (if reviewed)
- [ ] Show conversation/messages link (if available)
- [ ] Display action buttons (cancel, reschedule, pay, review)

**Backend Endpoint:**
- `GET /api/v1/beautybooking/bookings/{id}`

**Response Includes:**
- Full booking details with relationships (salon, service, staff, review, conversation)
- `can_cancel` (boolean): Whether booking can be cancelled
- `can_reschedule` (boolean): Whether booking can be rescheduled
- `cancellation_deadline` (datetime): Last time to cancel without fee

### 2.3 Create Booking
- [ ] Multi-step booking wizard/form
- [ ] Step 1: Select salon (with search/filter)
- [ ] Step 2: Select service category and service
- [ ] Step 3: Select staff (optional)
- [ ] Step 4: Select date and time (with availability check)
- [ ] Step 5: Select payment method (wallet, digital_payment, cash_payment)
- [ ] Step 6: Review and confirm booking
- [ ] Handle payment redirect for digital_payment
- [ ] Display booking confirmation with reference number

**Backend Endpoint:**
- `POST /api/v1/beautybooking/bookings/`

**Request Body:**
- `salon_id` (integer, required)
- `service_id` (integer, required)
- `staff_id` (integer, optional)
- `booking_date` (date, required, must be future date)
- `booking_time` (time, required, format: H:i)
- `payment_method` (string, required: wallet/digital_payment/cash_payment)
- `payment_gateway` (string, optional: stripe/paypal/razorpay)
- `callback_url` (url, optional)
- `payment_platform` (string, optional: web/mobile)

**Response:**
- Booking details on success
- Redirect URL for digital payment (if payment_method is digital_payment)

### 2.4 Cancel Booking
- [ ] Cancel booking button (only if `can_cancel` is true)
- [ ] Show cancellation reason input (optional)
- [ ] Display cancellation fee warning (if applicable)
- [ ] Confirm cancellation dialog
- [ ] Show success message with cancellation details
- [ ] Update booking status in UI

**Backend Endpoint:**
- `PUT /api/v1/beautybooking/bookings/{id}/cancel`

**Request Body:**
- `cancellation_reason` (string, optional)

**Business Rules:**
- Cannot cancel within 24 hours of booking time
- Cancellation fee calculated based on time remaining
- Full refund if cancelled more than 24 hours before

### 2.5 Reschedule Booking
- [ ] Reschedule button (only if `can_reschedule` is true)
- [ ] Date picker for new booking date
- [ ] Time slot selector (with availability check)
- [ ] Staff selector (optional, if original staff not available)
- [ ] Notes field (optional)
- [ ] Confirm reschedule dialog
- [ ] Show success message

**Backend Endpoint:**
- `PUT /api/v1/beautybooking/bookings/{id}/reschedule`

**Request Body:**
- `booking_date` (date, required, must be today or future)
- `booking_time` (time, required, format: H:i)
- `staff_id` (integer, optional)
- `notes` (string, optional, max: 500)

### 2.6 Process Payment
- [ ] Payment button for unpaid bookings
- [ ] Payment method selector (wallet, digital_payment, cash_payment)
- [ ] Payment gateway selector (for digital_payment: stripe, paypal, razorpay)
- [ ] Handle wallet payment (check balance, confirm)
- [ ] Handle digital payment redirect
- [ ] Handle cash payment confirmation
- [ ] Show payment status after processing

**Backend Endpoint:**
- `POST /api/v1/beautybooking/payment`

**Request Body:**
- `booking_id` (integer, required)
- `payment_method` (string, required: wallet/digital_payment/cash_payment)
- `payment_gateway` (string, optional: stripe/paypal/razorpay)
- `callback_url` (url, optional)
- `payment_platform` (string, optional: web/mobile)

**Response:**
- Redirect URL for digital payment (if payment_method is digital_payment)
- Payment confirmation for wallet/cash

### 2.7 Booking Conversation
- [ ] View conversation/messages for booking
- [ ] Display message history
- [ ] Send new message (if chat feature implemented)
- [ ] Show sender information
- [ ] Display file attachments (if any)
- [ ] Real-time message updates (if WebSocket implemented)

**Backend Endpoint:**
- `GET /api/v1/beautybooking/bookings/{id}/conversation`

**Response Includes:**
- Conversation ID
- Messages array with sender_id, message, file, created_at

---

## 3. Availability & Service Suggestions ✅

### 3.1 Check Availability
- [ ] Availability checker before booking
- [ ] Date picker
- [ ] Time slot selector (shows only available slots)
- [ ] Staff selector (optional)
- [ ] Display service duration
- [ ] Real-time availability updates

**Backend Endpoint:**
- `POST /api/v1/beautybooking/availability/check`

**Request Body:**
- `salon_id` (integer, required)
- `service_id` (integer, required)
- `date` (date, required, must be today or future)
- `staff_id` (integer, optional)

**Response:**
- `available_slots` (array): List of available time slots
- `service_duration_minutes` (integer): Service duration

### 3.2 Service Suggestions (Cross-Selling)
- [ ] Display suggested services after service selection
- [ ] Show related/complementary services
- [ ] Filter by salon (optional)
- [ ] Display service details (name, price, duration)
- [ ] Quick add to booking option

**Backend Endpoint:**
- `GET /api/v1/beautybooking/services/{id}/suggestions`

**Query Parameters:**
- `salon_id` (integer, optional)

**Response:**
- List of suggested services with details

---

## 4. Gift Cards Management ✅

### 4.1 List Gift Cards
- [ ] Display all purchased gift cards
- [ ] Show gift card code, amount, expiry date, status
- [ ] Filter by status (active, redeemed, expired)
- [ ] Show salon name (if salon-specific)
- [ ] Display pagination

**Backend Endpoint:**
- `GET /api/v1/beautybooking/gift-card/list`

**Query Parameters:**
- `limit` (integer, default: 25)
- `offset` (integer, default: 0)

**Response:**
- List of gift cards with code, amount, expires_at, status, salon

### 4.2 Purchase Gift Card
- [ ] Gift card purchase form
- [ ] Salon selector (optional, for salon-specific gift cards)
- [ ] Amount input (minimum: 1)
- [ ] Payment method selector (wallet, digital_payment, cash_payment)
- [ ] Display gift card code after purchase
- [ ] Show expiry date
- [ ] Copy gift card code button

**Backend Endpoint:**
- `POST /api/v1/beautybooking/gift-card/purchase`

**Request Body:**
- `salon_id` (integer, optional)
- `amount` (numeric, required, min: 1)
- `payment_method` (string, required: wallet/digital_payment/cash_payment)

**Response:**
- Gift card details with code, amount, expires_at, status

### 4.3 Redeem Gift Card
- [ ] Gift card redemption form
- [ ] Gift card code input
- [ ] Validate code before submission
- [ ] Show redemption confirmation
- [ ] Display amount added to wallet
- [ ] Show updated wallet balance

**Backend Endpoint:**
- `POST /api/v1/beautybooking/gift-card/redeem`

**Request Body:**
- `code` (string, required, max: 50)

**Response:**
- Amount redeemed, salon_id, wallet_balance

---

## 5. Loyalty Points Management ✅

### 5.1 View Loyalty Points Balance
- [ ] Display total points earned
- [ ] Show used points
- [ ] Display available points
- [ ] Points history link

**Backend Endpoint:**
- `GET /api/v1/beautybooking/loyalty/points`

**Response:**
- `total_points` (integer)
- `used_points` (integer)
- `available_points` (integer)

### 5.2 List Loyalty Campaigns
- [ ] Display active loyalty campaigns
- [ ] Filter by salon (optional)
- [ ] Show campaign details (name, description, type, rules)
- [ ] Display campaign validity dates
- [ ] Show campaign status (active/inactive)

**Backend Endpoint:**
- `GET /api/v1/beautybooking/loyalty/campaigns`

**Query Parameters:**
- `salon_id` (integer, optional)
- `limit` (integer, default: 25)
- `offset` (integer, default: 0)

**Response:**
- List of campaigns with details, type, rules, dates, salon

### 5.3 Redeem Loyalty Points
- [ ] Campaign selector
- [ ] Points input (with min/max validation)
- [ ] Display reward preview
- [ ] Confirm redemption dialog
- [ ] Show reward details after redemption
- [ ] Display updated points balance
- [ ] Handle different reward types:
  - Discount percentage/amount
  - Wallet credit
  - Cashback
  - Gift card
  - Points redeemed

**Backend Endpoint:**
- `POST /api/v1/beautybooking/loyalty/redeem`

**Request Body:**
- `campaign_id` (integer, required)
- `points` (integer, required, min: 1)

**Response:**
- Campaign details, points redeemed, remaining points, reward details

---

## 6. Packages Management ✅

### 6.1 List Packages
- [ ] Display available packages
- [ ] Filter by salon (optional)
- [ ] Filter by service (optional)
- [ ] Show package details (name, sessions count, total price, discount)
- [ ] Display salon information
- [ ] Show service details

**Backend Endpoint:**
- `GET /api/v1/beautybooking/packages/`

**Query Parameters:**
- `salon_id` (integer, optional)
- `service_id` (integer, optional)
- `limit` (integer, default: 25)
- `offset` (integer, default: 0)

**Response:**
- List of packages with salon, service, sessions_count, total_price

### 6.2 Package Details
- [ ] Display full package information
- [ ] Show sessions count
- [ ] Display total price and discount
- [ ] Show salon and service details
- [ ] Display purchase button

**Backend Endpoint:**
- `GET /api/v1/beautybooking/packages/{id}`

**Response:**
- Full package details with all relationships

### 6.3 Purchase Package
- [ ] Package purchase form
- [ ] Payment method selector (wallet, digital_payment, cash_payment)
- [ ] Confirm purchase dialog
- [ ] Show purchase confirmation
- [ ] Display package status after purchase

**Backend Endpoint:**
- `POST /api/v1/beautybooking/packages/{id}/purchase`

**Request Body:**
- `payment_method` (string, required: wallet/digital_payment/cash_payment)

**Response:**
- Package purchase confirmation with status

### 6.4 Package Status & Usage History
- [ ] Display purchased packages
- [ ] Show package status (active, expired, used)
- [ ] Display remaining sessions
- [ ] Show usage history
- [ ] Filter by status

**Backend Endpoints:**
- `GET /api/v1/beautybooking/packages/{id}/status`
- `GET /api/v1/beautybooking/packages/{id}/usage-history`

**Response:**
- Package status, remaining sessions, usage history

---

## 7. Reviews Management ✅

### 7.1 Create Review
- [ ] Review form for completed bookings
- [ ] Rating selector (1-5 stars)
- [ ] Comment textarea (optional, max: 1000 characters)
- [ ] Image upload (multiple images, optional)
- [ ] Submit review button
- [ ] Show review submission confirmation
- [ ] Display review status (pending/approved/rejected)

**Backend Endpoint:**
- `POST /api/v1/beautybooking/reviews/`

**Request Body (FormData):**
- `booking_id` (integer, required)
- `rating` (integer, required, 1-5)
- `comment` (string, optional, max: 1000)
- `attachments[]` (file array, optional): Image files

**Business Rules:**
- Can only review completed bookings
- Can only review once per booking
- Reviews require admin moderation (status: pending)

### 7.2 List Reviews
- [ ] Display all user reviews
- [ ] Show review details (rating, comment, attachments, status)
- [ ] Display salon and booking information
- [ ] Filter by status (pending, approved, rejected)
- [ ] Show review date

**Backend Endpoint:**
- `GET /api/v1/beautybooking/reviews/`

**Query Parameters:**
- `limit` (integer, default: 25)
- `offset` (integer, default: 0)

**Response:**
- List of reviews with salon, booking, rating, comment, attachments, status

---

## 8. Consultations Management ✅

### 8.1 List Consultations
- [ ] Display available consultations for a salon
- [ ] Filter by consultation type (pre_consultation, post_consultation)
- [ ] Show consultation details (name, price, duration)
- [ ] Display salon information

**Backend Endpoint:**
- `GET /api/v1/beautybooking/consultations/list`

**Query Parameters:**
- `salon_id` (integer, required)
- `consultation_type` (string, optional: pre_consultation/post_consultation)
- `limit` (integer, default: 25)
- `offset` (integer, default: 0)

**Response:**
- List of consultations with details

### 8.2 Book Consultation
- [ ] Consultation booking form
- [ ] Date and time selector
- [ ] Availability check
- [ ] Payment method selector
- [ ] Confirm booking
- [ ] Show booking confirmation

**Backend Endpoint:**
- `POST /api/v1/beautybooking/consultations/book`

**Request Body:**
- `salon_id` (integer, required)
- `service_id` (integer, required) - consultation service ID
- `booking_date` (date, required)
- `booking_time` (time, required)
- `payment_method` (string, required)

### 8.3 Check Consultation Availability
- [ ] Availability checker for consultations
- [ ] Date picker
- [ ] Time slot selector

**Backend Endpoint:**
- `POST /api/v1/beautybooking/consultations/check-availability`

**Request Body:**
- `salon_id` (integer, required)
- `service_id` (integer, required)
- `date` (date, required)

---

## 9. Retail Products & Orders ✅

### 9.1 List Retail Products
- [ ] Display products for a salon
- [ ] Filter by category
- [ ] Show product details (name, description, price, image, stock)
- [ ] Display product availability
- [ ] Add to cart functionality

**Backend Endpoint:**
- `GET /api/v1/beautybooking/retail/products`

**Query Parameters:**
- `salon_id` (integer, required)
- `category` (string, optional)
- `category_id` (integer, optional)
- `limit` (integer, default: 25)
- `offset` (integer, default: 0)

**Response:**
- List of products with details, price, stock, image

### 9.2 Create Retail Order
- [ ] Shopping cart
- [ ] Product quantity selector
- [ ] Order summary
- [ ] Payment method selector
- [ ] Delivery address (if applicable)
- [ ] Confirm order
- [ ] Show order confirmation

**Backend Endpoint:**
- `POST /api/v1/beautybooking/retail/orders`

**Request Body:**
- `salon_id` (integer, required)
- `items` (array, required): Array of {product_id, quantity}
- `payment_method` (string, required)
- `delivery_address` (string, optional)

**Response:**
- Order details with items, total, status

### 9.3 List Retail Orders
- [ ] Display all retail orders
- [ ] Show order details (items, total, status, date)
- [ ] Filter by status
- [ ] Display salon information

**Backend Endpoint:**
- `GET /api/v1/beautybooking/retail/orders`

**Query Parameters:**
- `limit` (integer, default: 25)
- `offset` (integer, default: 0)

**Response:**
- List of orders with items, salon, total, status

### 9.4 Retail Order Details
- [ ] Display full order details
- [ ] Show all order items with quantities
- [ ] Display order status
- [ ] Show payment information
- [ ] Display delivery information (if applicable)

**Backend Endpoint:**
- `GET /api/v1/beautybooking/retail/orders/{id}`

**Response:**
- Full order details with items, salon, payment, delivery

---

## 10. Wallet Transactions ✅

### 10.1 View Wallet Transactions
- [ ] Display wallet transaction history
- [ ] Filter by transaction type
- [ ] Show transaction details (amount, type, date, reference)
- [ ] Display wallet balance
- [ ] Show pagination

**Backend Endpoint:**
- Note: Uses existing wallet system endpoint
- Filter by `transaction_type` containing 'beauty'

**Data Available:**
- Transaction history for beauty-related transactions
- Wallet balance from user model

---

## 11. Salon Search & Discovery ✅

### 11.1 Search Salons
- [ ] Salon search with filters
- [ ] Filter by category
- [ ] Filter by location (latitude, longitude, radius)
- [ ] Search by name/keywords
- [ ] Display salon list with ranking
- [ ] Show salon details (name, rating, address, image, badges)

**Backend Endpoint:**
- `GET /api/v1/beautybooking/salons/search` (Public)

**Query Parameters:**
- `search` (string, optional)
- `category_id` (integer, optional)
- `latitude` (float, optional)
- `longitude` (float, optional)
- `radius` (integer, optional, in km)

**Response:**
- List of salons with ranking, rating, location, badges

### 11.2 Popular Salons
- [ ] Display popular salons list
- [ ] Show salon ranking
- [ ] Display salon details

**Backend Endpoint:**
- `GET /api/v1/beautybooking/salons/popular` (Public)

### 11.3 Top Rated Salons
- [ ] Display top-rated salons
- [ ] Show ratings and reviews count
- [ ] Display salon details

**Backend Endpoint:**
- `GET /api/v1/beautybooking/salons/top-rated` (Public)

### 11.4 Monthly Top Rated Salons
- [ ] Display monthly top-rated salons
- [ ] Show monthly rankings
- [ ] Display salon details

**Backend Endpoint:**
- `GET /api/v1/beautybooking/salons/monthly-top-rated` (Public)

### 11.5 Trending Clinics
- [ ] Display trending clinics
- [ ] Show trending indicators
- [ ] Display clinic details

**Backend Endpoint:**
- `GET /api/v1/beautybooking/salons/trending-clinics` (Public)

### 11.6 Salon Details
- [ ] Display full salon information
- [ ] Show services list
- [ ] Display staff list
- [ ] Show working hours
- [ ] Display reviews
- [ ] Show badges and ratings
- [ ] Display location and contact info
- [ ] Show images/gallery

**Backend Endpoint:**
- `GET /api/v1/beautybooking/salons/{id}` (Public)

**Response:**
- Full salon details with services, staff, reviews, working hours, badges

### 11.7 Salon Reviews
- [ ] Display salon reviews
- [ ] Show ratings distribution
- [ ] Filter by rating
- [ ] Display review details with attachments

**Backend Endpoint:**
- `GET /api/v1/beautybooking/reviews/{salon_id}` (Public)

**Response:**
- List of reviews for the salon

### 11.8 Category List
- [ ] Display service categories
- [ ] Show category hierarchy
- [ ] Display category details

**Backend Endpoint:**
- `GET /api/v1/beautybooking/salons/category-list` (Public)

**Response:**
- List of categories with hierarchy

---

## 12. Error Handling & Validation ✅

### 12.1 API Error Handling
- [ ] Handle validation errors (403 status)
- [ ] Display error messages from `errors` array
- [ ] Handle authentication errors (401 status)
- [ ] Handle not found errors (404 status)
- [ ] Handle server errors (500 status)
- [ ] Display user-friendly error messages

**Error Response Format:**
```json
{
  "errors": [
    {
      "code": "validation|booking|payment|...",
      "message": "Error message"
    }
  ]
}
```

### 12.2 Form Validation
- [ ] Client-side validation for all forms
- [ ] Match backend validation rules
- [ ] Display validation errors inline
- [ ] Prevent invalid submissions

### 12.3 Loading States
- [ ] Show loading indicators during API calls
- [ ] Disable buttons during processing
- [ ] Display skeleton loaders for lists

### 12.4 Success Messages
- [ ] Display success messages after operations
- [ ] Auto-dismiss success messages
- [ ] Update UI after successful operations

---

## 13. Authentication & Authorization ✅

### 13.1 Authentication
- [ ] Verify user is authenticated for all protected routes
- [ ] Handle token expiration
- [ ] Redirect to login if not authenticated
- [ ] Store and send authentication token

### 13.2 Authorization
- [ ] Verify user owns resources (bookings, reviews, etc.)
- [ ] Handle unauthorized access errors (403)
- [ ] Display appropriate error messages

---

## 14. Pagination & Filtering ✅

### 14.1 Pagination
- [ ] Implement pagination for all list endpoints
- [ ] Display page numbers
- [ ] Show total count
- [ ] Handle limit and offset parameters
- [ ] Support infinite scroll (optional)

### 14.2 Filtering
- [ ] Implement filters for all list endpoints
- [ ] Reset filters functionality
- [ ] Preserve filters in URL (optional)
- [ ] Display active filters

### 14.3 Sorting
- [ ] Implement sorting where applicable
- [ ] Default sorting (latest first)
- [ ] Sort by date, amount, rating, etc.

---

## 15. Real-time Features (Optional) ✅

### 15.1 Notifications
- [ ] Display push notifications for booking updates
- [ ] Show notification badge
- [ ] Handle notification clicks
- [ ] Mark notifications as read

### 15.2 Real-time Updates
- [ ] WebSocket integration for booking status updates
- [ ] Real-time message updates (if chat implemented)
- [ ] Live availability updates

---

## 16. UI/UX Requirements ✅

### 16.1 Responsive Design
- [ ] Mobile-first responsive design
- [ ] Tablet optimization
- [ ] Desktop optimization
- [ ] Touch-friendly interactions

### 16.2 Accessibility
- [ ] Keyboard navigation support
- [ ] Screen reader compatibility
- [ ] ARIA labels where needed
- [ ] Color contrast compliance

### 16.3 Performance
- [ ] Lazy loading for images
- [ ] Code splitting
- [ ] Optimized API calls
- [ ] Caching where appropriate

### 16.4 Internationalization
- [ ] Support for Persian (Farsi) and English
- [ ] RTL layout support for Persian
- [ ] Date/time formatting based on locale
- [ ] Currency formatting

---

## 17. Testing Checklist ✅

### 17.1 Functional Testing
- [ ] Test all booking flows
- [ ] Test payment processing
- [ ] Test cancellation and rescheduling
- [ ] Test gift card purchase and redemption
- [ ] Test loyalty points redemption
- [ ] Test package purchase
- [ ] Test review submission
- [ ] Test all filters and pagination

### 17.2 Error Testing
- [ ] Test validation errors
- [ ] Test network errors
- [ ] Test authentication errors
- [ ] Test authorization errors
- [ ] Test edge cases

### 17.3 Integration Testing
- [ ] Test API integration
- [ ] Test payment gateway integration
- [ ] Test notification integration
- [ ] Test WebSocket integration (if implemented)

---

## 18. Documentation Requirements ✅

### 18.1 Code Documentation
- [ ] Document all components
- [ ] Document API integration
- [ ] Document state management
- [ ] Document routing

### 18.2 User Documentation
- [ ] User guide for dashboard features
- [ ] Help tooltips
- [ ] FAQ section

---

## API Base URL

### Development Environment
```
http://localhost:8000/api/v1/beautybooking/
```

### Production Environment
```
https://your-domain.com/api/v1/beautybooking/
```

**Important:** 
- **Port**: Laravel backend runs on port **8000** by default in development
- **Full Base URL**: `http://localhost:8000/api/v1/beautybooking/`
- All API requests from React frontend should be sent to this base URL
- The frontend should configure the base URL in environment variables:
  - Development: `REACT_APP_API_BASE_URL=http://localhost:8000/api/v1/beautybooking`
  - Production: `REACT_APP_API_BASE_URL=https://your-domain.com/api/v1/beautybooking`

## Authentication
All protected endpoints require:
- Header: `Authorization: Bearer {token}`
- Or: Cookie-based authentication (if implemented)

**Authentication Endpoint:**
- Login: `POST http://localhost:8000/api/v1/auth/login`
- Sign Up: `POST http://localhost:8000/api/v1/auth/sign-up`

## Rate Limiting
- Booking creation: 10 requests/minute
- Booking cancellation: 5 requests/minute
- Payment: 5 requests/minute
- Gift card purchase: 5 requests/minute
- Package purchase: 5 requests/minute
- Review creation: 5 requests/minute
- Availability check: 30 requests/minute
- Service suggestions: 60 requests/minute
- List endpoints: 60 requests/minute

---

## Notes for React Team

1. **Independent Dashboard**: The Beauty Booking module must have its own completely independent dashboard, separate from other modules in the application.

2. **API Response Format**: All API responses follow this format:
   ```json
   {
     "message": "Success message",
     "data": {...},
     "total": 100,
     "per_page": 25,
     "current_page": 1,
     "last_page": 4
   }
   ```

3. **Error Format**: All errors follow this format:
   ```json
   {
     "errors": [
       {
         "code": "error_code",
         "message": "Error message"
       }
     ]
   }
   ```

4. **Pagination**: Use `limit` and `offset` parameters, but backend converts offset to page number internally.

5. **File Uploads**: Use FormData for file uploads (reviews with images).

6. **Date Formats**: 
   - Dates: `YYYY-MM-DD`
   - Times: `HH:mm` (24-hour format)
   - DateTimes: `YYYY-MM-DD HH:mm:ss`

7. **Payment Redirects**: For digital payments, the API returns a `redirect_url` that should be used to redirect the user to the payment gateway.

8. **Real-time Features**: WebSocket integration is optional but recommended for booking status updates and chat functionality.

9. **Localization**: All user-facing strings should support both Persian (Farsi) and English, with RTL support for Persian.

10. **Testing**: Test all features thoroughly, especially payment flows, booking creation, and cancellation logic.

---

## Completion Status

- [ ] **Dashboard Overview**: Not Started / In Progress / Completed
- [ ] **Booking Management**: Not Started / In Progress / Completed
- [ ] **Availability & Suggestions**: Not Started / In Progress / Completed
- [ ] **Gift Cards**: Not Started / In Progress / Completed
- [ ] **Loyalty Points**: Not Started / In Progress / Completed
- [ ] **Packages**: Not Started / In Progress / Completed
- [ ] **Reviews**: Not Started / In Progress / Completed
- [ ] **Consultations**: Not Started / In Progress / Completed
- [ ] **Retail Products**: Not Started / In Progress / Completed
- [ ] **Wallet Transactions**: Not Started / In Progress / Completed
- [ ] **Salon Search**: Not Started / In Progress / Completed
- [ ] **Error Handling**: Not Started / In Progress / Completed
- [ ] **Authentication**: Not Started / In Progress / Completed
- [ ] **Pagination & Filtering**: Not Started / In Progress / Completed
- [ ] **Real-time Features**: Not Started / In Progress / Completed
- [ ] **UI/UX**: Not Started / In Progress / Completed
- [ ] **Testing**: Not Started / In Progress / Completed
- [ ] **Documentation**: Not Started / In Progress / Completed

---

**Last Updated**: 2025-01-28
**Version**: 1.1
**Module**: BeautyBooking
**Target**: React User Dashboard

---

## Backend Server Configuration

### Laravel Development Server
The Laravel backend runs on **port 8000** by default.

**Start Laravel Server:**
```bash
php artisan serve
# Server will start at: http://localhost:8000
```

**For React Frontend:**
Configure your API base URL in `.env` file:
```env
REACT_APP_API_BASE_URL=http://localhost:8000/api/v1/beautybooking
```

**Example API Call from React:**
```javascript
// Using axios
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api/v1/beautybooking',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  }
});

// Example: Get bookings
const response = await api.get('/bookings');
```

**CORS Configuration:**
Make sure Laravel CORS is configured to allow requests from your React frontend origin (usually `http://localhost:3000` for React development server).

**Laravel CORS Config** (`config/cors.php`):
```php
'allowed_origins' => [
    'http://localhost:3000', // React dev server
    'http://localhost:3001', // Alternative React port
],
```

---

## Verification Notes

✅ **All endpoints verified against actual backend code**
✅ **All route paths match actual Laravel routes**
✅ **All controller methods exist and are implemented**
✅ **All request/response formats verified**
✅ **Rate limiting values verified**
✅ **Authentication requirements verified**

