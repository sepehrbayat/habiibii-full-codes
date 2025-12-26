# Vendor API Endpoints Reference

Complete reference table for all Beauty Booking vendor API endpoints.

## Base URL
```
/api/v1/beautybooking/vendor/
```

## Authentication
All endpoints require `vendor.api` middleware. Include vendor authentication token in request headers.

---

## Booking Management

| Endpoint | Method | Controller | Description | Status |
|----------|--------|------------|-------------|--------|
| `/bookings/list/{all}` | GET | BeautyBookingController::list | List vendor bookings (all or filtered by status) | ✅ Ready |
| `/bookings/details` | GET | BeautyBookingController::details | Get booking details | ✅ Ready |
| `/bookings/confirm` | PUT | BeautyBookingController::confirm | Confirm a booking | ✅ Ready |
| `/bookings/complete` | PUT | BeautyBookingController::complete | Mark booking as completed | ✅ Ready |
| `/bookings/mark-paid` | PUT | BeautyBookingController::markPaid | Mark cash payment as paid | ✅ Ready |
| `/bookings/cancel` | PUT | BeautyBookingController::cancel | Cancel a booking | ✅ Ready |

**Total: 6 endpoints**

---

## Staff Management

| Endpoint | Method | Controller | Description | Status |
|----------|--------|------------|-------------|--------|
| `/staff/list` | GET | BeautyStaffController::list | List all staff members | ✅ Ready |
| `/staff/create` | POST | BeautyStaffController::store | Create new staff member | ✅ Ready |
| `/staff/update/{id}` | POST | BeautyStaffController::update | Update staff member | ✅ Ready |
| `/staff/details/{id}` | GET | BeautyStaffController::details | Get staff details | ✅ Ready |
| `/staff/delete/{id}` | DELETE | BeautyStaffController::destroy | Delete staff member | ✅ Ready |
| `/staff/status/{id}` | GET | BeautyStaffController::status | Toggle staff status | ✅ Ready |

**Total: 6 endpoints**

---

## Service Management

| Endpoint | Method | Controller | Description | Status |
|----------|--------|------------|-------------|--------|
| `/service/list` | GET | BeautyServiceController::list | List all services | ✅ Ready |
| `/service/create` | POST | BeautyServiceController::store | Create new service | ✅ Ready |
| `/service/update/{id}` | POST | BeautyServiceController::update | Update service | ✅ Ready |
| `/service/details/{id}` | GET | BeautyServiceController::details | Get service details | ✅ Ready |
| `/service/delete/{id}` | DELETE | BeautyServiceController::destroy | Delete service | ✅ Ready |
| `/service/status/{id}` | GET | BeautyServiceController::status | Toggle service status | ✅ Ready |

**Total: 6 endpoints**

---

## Calendar Management

| Endpoint | Method | Controller | Description | Status |
|----------|--------|------------|-------------|--------|
| `/calendar/availability` | GET | BeautyCalendarController::getAvailability | Get calendar availability | ✅ Ready |
| `/calendar/blocks/create` | POST | BeautyCalendarController::createBlock | Create calendar block | ✅ Ready |
| `/calendar/blocks/delete/{id}` | DELETE | BeautyCalendarController::deleteBlock | Delete calendar block | ✅ Ready |

**Total: 3 endpoints**

---

## Salon Registration & Profile

| Endpoint | Method | Controller | Description | Status |
|----------|--------|------------|-------------|--------|
| `/salon/register` | POST | BeautyVendorController::register | Register new salon | ✅ Ready |
| `/salon/documents/upload` | POST | BeautyVendorController::uploadDocuments | Upload salon documents | ✅ Ready |
| `/salon/working-hours/update` | POST | BeautyVendorController::updateWorkingHours | Update working hours | ✅ Ready |
| `/salon/holidays/manage` | POST | BeautyVendorController::manageHolidays | Manage holidays | ✅ Ready |
| `/profile` | GET | BeautyVendorController::profile | Get vendor profile | ✅ Ready |
| `/profile/update` | POST | BeautyVendorController::profileUpdate | Update vendor profile | ✅ Ready |

**Total: 6 endpoints**

---

## Retail Management

| Endpoint | Method | Controller | Description | Status |
|----------|--------|------------|-------------|--------|
| `/retail/products` | GET | BeautyRetailController::listProducts | List retail products | ✅ Ready |
| `/retail/products` | POST | BeautyRetailController::storeProduct | Create retail product | ✅ Ready |
| `/retail/orders` | GET | BeautyRetailController::listOrders | List retail orders | ✅ Ready |

**Total: 3 endpoints**

---

## Subscription Management

| Endpoint | Method | Controller | Description | Status |
|----------|--------|------------|-------------|--------|
| `/subscription/plans` | GET | BeautySubscriptionController::getPlans | Get subscription plans | ✅ Ready |
| `/subscription/purchase` | POST | BeautySubscriptionController::purchase | Purchase subscription | ✅ Ready |
| `/subscription/history` | GET | BeautySubscriptionController::history | Get subscription history | ✅ Ready |

**Total: 3 endpoints**

---

## Finance & Reports

| Endpoint | Method | Controller | Description | Status |
|----------|--------|------------|-------------|--------|
| `/finance/payout-summary` | GET | BeautyFinanceController::payoutSummary | Get payout summary | ✅ Ready |
| `/finance/transactions` | GET | BeautyFinanceController::transactionHistory | Get transaction history | ✅ Ready |

**Total: 2 endpoints**

---

## Badge Management

| Endpoint | Method | Controller | Description | Status |
|----------|--------|------------|-------------|--------|
| `/badge/status` | GET | BeautyBadgeController::status | Get badge status | ✅ Ready |

**Total: 1 endpoint**

---

## Package Management

| Endpoint | Method | Controller | Description | Status |
|----------|--------|------------|-------------|--------|
| `/packages/list` | GET | BeautyPackageController::list | List packages | ✅ Ready |
| `/packages/usage-stats` | GET | BeautyPackageController::usageStats | Get package usage statistics | ✅ Ready |

**Total: 2 endpoints**

---

## Gift Card Management

| Endpoint | Method | Controller | Description | Status |
|----------|--------|------------|-------------|--------|
| `/gift-cards/list` | GET | BeautyGiftCardController::list | List gift cards | ✅ Ready |
| `/gift-cards/redemption-history` | GET | BeautyGiftCardController::redemptionHistory | Get redemption history | ✅ Ready |

**Total: 2 endpoints**

---

## Loyalty Campaign Management

| Endpoint | Method | Controller | Description | Status |
|----------|--------|------------|-------------|--------|
| `/loyalty/campaigns` | GET | BeautyLoyaltyController::listCampaigns | List loyalty campaigns | ✅ Ready |
| `/loyalty/points-history` | GET | BeautyLoyaltyController::pointsHistory | Get points history | ✅ Ready |
| `/loyalty/campaign/{id}/stats` | GET | BeautyLoyaltyController::campaignStats | Get campaign statistics | ✅ Ready |

**Total: 3 endpoints**

---

## Summary

- **Total Endpoints:** 43
- **Controllers:** 12
- **Status:** ✅ All endpoints ready and implemented
- **Authentication:** All endpoints require `vendor.api` middleware
- **Response Format:** All endpoints use `BeautyApiResponse` trait
- **Pagination:** All list endpoints support `offset` and `limit` parameters

---

## Notes

1. All endpoints are fully implemented and tested
2. All endpoints use standardized response format via `BeautyApiResponse` trait
3. All endpoints require vendor authentication via `vendor.api` middleware
4. Pagination uses offset/limit pattern (same as customer APIs)
5. Rate limiting is configured per endpoint group
6. All responses follow standard format: `{message, data}` or `{errors}`

---

**Last Updated:** 2025-12-03  
**Verification Status:** ✅ All 43 endpoints verified and ready

