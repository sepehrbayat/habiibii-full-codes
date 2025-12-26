# Beauty Module – React Alignment (Frontend View)

## TL;DR
- Customer flows are wired and match Laravel: booking/availability/reschedule, packages (status + usage-history), gift cards, loyalty, consultation, retail, reviews → no changes required.
- Vendor auth flow now exists (`/beauty/vendor/login`) and uses `BeautyVendorApi.loginVendor`, `VendorAuthContext`, and `MainApi` bearer handling.
- Remaining gaps are contract mismatches with Laravel: subscription plans/purchase payload, salon registration working_hours capture, holiday removal semantics, and working_hours shape normalization. Fixes below.

## Current State Snapshot
- API clients: `beautyApi.js` (customer) and `beautyVendorApi.js` (vendor) cover all Laravel endpoints.
- Hooks/components/pages: vendor dashboard/bookings/staff/services/calendar/profile/register/retail/subscription/finance/badge/packages/gift-cards/loyalty exist and are guarded by `VendorAuthGuard`; login page is present.
- Headers: `MainApi` injects `vendor_token` for `/beautybooking/vendor/` routes.

## Gaps & Required Changes (React)
1) Subscription plans & purchase payload (blocking for featured/boost/banner/dashboard)
- Backend `GET /vendor/subscription/plans` returns a **nested map** (`plans.featured_listing.{7|30}.price`, `boost_ads.{7|30}`, `banner_ads.{homepage|category|search_results}`, `dashboard_subscription.{monthly|yearly}`) plus `active_subscriptions`. `SubscriptionPlans` currently expects an array. Normalize to UI-friendly cards (derive `name`, `duration_days`, `price`, `subscription_type`, `ad_position?`).
- Purchase (`POST /vendor/subscription/purchase`) must send `subscription_type`, `duration_days`, `payment_method`, optional `ad_position`, `banner_image`. Current call sends `{plan_type, payment_method}` only. Map UI selections to backend contract:
  - dashboard monthly/annual → `subscription_type=dashboard_subscription`, `duration_days=30|365`
  - featured 7/30 → `subscription_type=featured_listing`, `duration_days=7|30`
  - boost 7/30 → `subscription_type=boost_ads`, `duration_days=7|30`
  - banner → require `ad_position` and `duration_days=30` (per config), optional `banner_image`

2) Salon registration working_hours (validation mismatch)
- Backend requires `working_hours` array of `{day, open, close}` on `/vendor/salon/register`. `SalonRegistrationForm` currently submits an empty array. Collect day/open/close before calling `registerSalon` (reuse WorkingHoursForm data or enforce in-step form). Ensure `working_hours` is passed in payload.

3) Holidays remove semantics
- Backend `action=remove` expects the dates **to remove** (not the remaining set). `HolidaysManager` currently sends the remaining list, so deletions are ignored. Fix payload to send the removed date(s) (or switch to `action=replace` with the new full list).

4) Working hours edit shape
- Profile responses return working_hours as a map keyed by day; `WorkingHoursForm` sets that object directly and iterates a hardcoded array, leading to empty fields. Normalize profile `working_hours` to an array of `{day, open, close}` (fallback defaults) before rendering and when submitting `updateWorkingHours`.

5) Nice-to-have UX
- Show errors via `onSingleErrorResponse` for subscription and holidays mutations.
- Add logout entry in vendor layout if not present in the current menu.

## Validation Checklist (Frontend)
- Subscription plans render derived cards from nested map; purchase payload matches Laravel contract (`subscription_type`, `duration_days`, `payment_method`, optional `ad_position/banner_image`).
- Salon registration sends `working_hours` array; form validates required fields.
- Holidays remove actually deletes: payload matches backend contract.
- Working hours edit pre-fills from profile by normalizing map → array; submit sends array.
- Vendor login persists `vendor_token`; guard passes when authenticated; logout clears token.

## File Pointers (to change/create)
- Add page: `pages/beauty/vendor/login/index.js` (form + call to `/api/v1/auth/seller/login`, store token via `VendorAuthContext`).
- Wire context if needed: `src/contexts/vendor-auth-context.js` (already present; confirm login/logout usage).
- Menu/action: `src/components/layout/VendorLayout.js` or header component to include logout entry.

## Notes
- Do not change API paths: Laravel vendor routes already exposed under `/api/v1/beautybooking/vendor/...`.
- Payment methods: keep sending `digital_payment|wallet|cash_payment`; conversion from `online` not needed on vendor side (already handled where present).

