# Beauty Module – Laravel/React Alignment (Backend View)

## TL;DR
- Customer-facing APIs already match React calls: booking, packages (incl. usage-history/status), gift-cards, loyalty, consultation, retail, reviews, availability, reschedule, conversation.
- Vendor API surface is complete (bookings, staff, services, calendar, profile/onboarding, retail, subscription, finance, badge, packages, gift-cards, loyalty). **Key contracts the frontend must honor are documented below.**
- No mandatory backend code changes, but optional compat shims are suggested where React currently diverges (subscription purchase payload, holiday removal semantics, working_hours shape).

## What Was Checked
- Routes: `Modules/BeautyBooking/Routes/api/v1/customer/api.php`, `.../vendor/api.php`
- Controllers (spot-checked): Customer `BeautyBookingController`, `BeautyPackageController`, `BeautyRetailController`, `BeautyReviewController`; Vendor `BeautyBookingController`, `BeautyStaffController`, `BeautyServiceController`, `BeautyVendorController`
- Response helpers: `Modules/BeautyBooking/Traits/BeautyApiResponse.php`
- React API clients: `src/api-manage/another-formated-api/beautyApi.js`, `beautyVendorApi.js`
- Auth headers: React `MainApi` sets `authorization` from `vendor_token` for vendor routes

## Alignment Status by Area
- Booking (customer): create/list/show/reschedule/cancel/conversation + availability → endpoints and fields match React.
- Packages: list/show/purchase/status/usage-history → implemented and used; field shapes align.
- Gift cards: purchase/redeem/list → implemented; payment method conversion handled backend & frontend.
- Loyalty: points, campaigns, redeem → implemented and used.
- Consultation: list/book/check-availability → implemented; payment method conversion handled.
- Retail: products list, create order, orders list/detail → implemented; React calls exist.
- Reviews: submit/list/salon-reviews with attachment URLs → implemented.
- Vendor surface: all 33 endpoints present with pagination, validation, and BeautyApiResponse format.

## Backend Contracts to Preserve (React must align)
- Subscription purchase (`POST /api/v1/beautybooking/vendor/subscription/purchase`)
  - Required: `subscription_type` ∈ `featured_listing|boost_ads|banner_ads|dashboard_subscription`, `duration_days` (int), `payment_method` ∈ `digital_payment|wallet|cash_payment`
  - Optional: `ad_position` (for `banner_ads`), `banner_image` (base64 or URL), `payment_platform`, `payment_gateway`
  - Current React sends `{plan_type, payment_method}` only → must be updated client-side or backend may add an alias layer if needed.
- Subscription plans (`GET /api/v1/beautybooking/vendor/subscription/plans`)
  - Returns nested map: `plans.featured_listing.{7|30}.price`, `boost_ads.{7|30}.price`, `banner_ads.{homepage|category|search_results}.price`, `dashboard_subscription.{monthly|yearly}.price` plus `active_subscriptions`.
  - React should normalize this map; backend stays as-is.
- Salon registration (`POST /vendor/salon/register`)
  - Requires `working_hours` array of `{day, open, close}`; React currently sends empty array. No backend change required; React must collect and send.
- Holidays management (`POST /vendor/salon/holidays/manage`)
  - `action` ∈ `add|remove|replace`; `holidays` is the list to add/remove/replace. For `remove`, backend expects the dates to remove (not the remaining set). React currently sends remaining dates; must adjust on frontend or backend could add alias logic if desired.
- Working hours update/profile update
  - Expects array of `{day, open, close}`; stored as JSON map keyed by day. React should transform profile response to array when editing and send array back.

## Optional Compatibility Shims (if we decide to harden backend for current React)
- Accept `plan_type=monthly|annual` by mapping to `subscription_type=dashboard_subscription` with `duration_days=30|365` when `subscription_type` missing.
- Accept `plan_type=featured|boost|banner` plus `duration_days` and map to `subscription_type` accordingly.
- Accept holiday payloads that provide the **remaining** list by treating `remove` without explicit removals as `replace` (last resort).

## Notes for Frontend Team (FYI)
- Vendor APIs require `vendor.api` middleware with vendor bearer token; React must issue/store `vendor_token` via existing platform vendor auth (core `/api/v1/auth/seller/login` flow).
- Pagination: all list endpoints accept `limit`/`offset`; React normalizer already supports this.
- Payment methods: backend accepts `digital_payment|wallet|cash_payment`; React already converts `online` → `digital_payment`.
- Honor the contracts listed above; otherwise backend validation will fail (especially subscription purchase, holidays remove, working_hours).

## Quick Validation Checklist (backend)
- Customer routes respond with BeautyApiResponse format (message + data) ✅
- Vendor routes protected by `vendor.api` middleware ✅
- Offset/limit translated to page for all paginated queries ✅
- Attachment and image URLs returned as absolute (asset/storage) ✅
- Booking/package/retail/gift/loyalty/consultation endpoints return required fields ✅

## Next Steps
- No Laravel work needed for alignment. Concentrate on React vendor auth/login flow and wiring vendor pages to the existing vendor APIs.

