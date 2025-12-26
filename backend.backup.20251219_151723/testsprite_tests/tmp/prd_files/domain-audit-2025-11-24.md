# Beauty Booking Domain Audit — 2025-11-24

## Scope Reviewed
- Entities under `Modules/BeautyBooking/Entities`
- Database migrations (`Modules/BeautyBooking/Database/Migrations`, 32 files)
- Service layer in `Modules/BeautyBooking/Services`
- Module configuration in `Modules/BeautyBooking/Config/config.php`
- Representative relationship definitions inside core models (`BeautySalon`, `BeautyService`, `BeautyLoyaltyPoint`, `BeautyRetailOrder`, `BeautyTransaction`)

## Findings At A Glance
| Area | Status | Notes |
| --- | --- | --- |
| Entities | ✅ Coverage present for salons, staff, services, packages, loyalty, ads/subscriptions, retail, badges, calendar, reviews, transactions. | `BeautyServiceRelation`, `BeautyPackageUsage`, and retail entities exist, ensuring cross-sell + retail flows are modelled. Need to add policy bindings (Phase 1.3) and ensure observers registered (currently `BeautyBookingObserver`, `BeautyReviewObserver` only). |
| Migrations | ✅ 32 migrations mapped to every domain area: base tables + incremental expansions (consultation fields, advertisements, loyalty, package usage, retail, monthly reports, indexes). | Auto-increment bump for bookings not yet in migrations (missing `ALTER TABLE ... AUTO_INCREMENT = 100000`). Some follow-up migrations (e.g., `add_loyalty_campaign_to_transaction_types`) touch enums but require verification after DB refresh. |
| Services | ✅ Core services exist for booking, calendar, commission, ranking, badges, loyalty, cross-selling, retail, revenue. | Logic is comprehensive (e.g., `BeautyBookingService::createBooking` handles packages, consults, conversation creation). Need integration tests + façade wiring for `BeautyCrossSellingService` and `BeautyRetailService` (currently self-contained but not invoked by controllers). |
| Config | ✅ Config file already exposes toggles for commission, service fee, tax, ranking weights, badges, cancellation fee, consultation, cross-selling, retail, subscription/ads, packages, gift cards, booking, review, notification, payment, loyalty. | Missing explicit sections for reporting cache TTLs and feature flags for admin/vendor menu gating (can reuse `config('beautybooking.*.enabled')`). Consider splitting into sub-configs once Admin/Vendor forms need runtime editing. |
| Relationships & Constraints | ✅ Key relationships confirmed (`BeautySalon` ↔ `Store/Zone/Staff/Services/Bookings/Badges/Subs`, `BeautyService` ↔ categories/staff/bookings/reviews/packages, `BeautyLoyaltyPoint` linking to campaigns/bookings, `BeautyRetailOrder` referencing salons/users, `BeautyTransaction` referencing bookings/salons). | Need to ensure pivot table `beauty_service_staff` has unique constraint (present via migration `2025_11_22_103319_create_beauty_service_staff_table.php`). Recommend adding explicit foreign key constraints to late-add columns (e.g., `booking_id` on `beauty_transactions`) to guarantee integrity. |

## Detailed Observations
1. **Entity Coverage**
   - Retail (`BeautyRetailProduct`, `BeautyRetailOrder`) and loyalty (`BeautyLoyaltyCampaign`, `BeautyLoyaltyPoint`) already implemented with casts + scopes.
   - `BeautyMonthlyReport` exists for reporting pipeline but no cron wiring (only console commands shell exist; need scheduling).
   - `BeautyServiceRelation` provides cross-selling graph yet no repository/service hooking is visible from controllers—flag for later integration.

2. **Migrations**
   - Base tables cover every major feature (salons, staff, categories, services, bookings, calendar blocks, badges, reviews, subscriptions, packages, gift cards, commission settings, transactions).
   - Add-on migrations extend functionality: consultation columns (`2025_11_22_1400xx`), package usage, retail, loyalty, monthly reports, indexes, chat conversation linking.
   - Missing pieces detected:
     - Booking auto-increment bump (per rules) absent.
     - Some tables (e.g., `beauty_retail_orders`, `beauty_loyalty_points`) rely on JSON columns without partial indexes—evaluate query plans later.

3. **Service Layer**
   - `BeautyBookingService` handles lifecycle (availability, package validation, conversation creation, stats update, notifications).
   - `BeautyCalendarService` (not fully reviewed) should be exercised via tests to ensure working-hours/holiday logic matches spec.
   - `BeautyCommissionService` + `BeautyRevenueService` cover 10 revenue models; however, controllers must call `BeautyRevenueService` for non-booking revenue (ads/subscriptions/retail) to ensure unified ledger.
   - Shared helpers (price calc, availability, badge evaluation) already live inside services/traits but require surfacing via façade or trait usage.

4. **Configuration**
   - Config is exhaustive and adheres to bilingual documentation.
   - Need to add environment-driven toggles for future customer web gating (e.g., `beautybooking.customer.search.map_enabled`) if design demands.
   - Ensure admin UI reads these keys before exposing forms (Phase 2.3 & 2.4).

5. **Next Actions Derived from Audit**
   - Implement missing seed data for permissions, default badges, module activation toggles, and menu entries (Phase 1.2).
   - Wire Laravel policies for `BeautySalon`, `BeautyBooking`, `BeautyReview` and register in `AuthServiceProvider` (Phase 1.3).
   - Finish shared helper exposure + tests for calendar/commission/ranking services (Phase 1.4).
   - Add migration to set booking auto-increment + ensure FK constraints on late-add columns.
   - Introduce config-driven feature toggles for admin/vendor menu gating before finalizing sidebars.

This audit satisfies Phase 1.1 and feeds concrete TODOs for subsequent phases.

