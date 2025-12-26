# User Dashboard & Beauty Module Integration Plan

## Objective

Create a comprehensive plan to integrate Beauty Booking module features into the main user/customer dashboard. The plan should identify what beauty-related features, statistics, widgets, and navigation items should be displayed in the main customer dashboard, considering both admin and vendor features to ensure customers have access to all relevant beauty booking functionality.

## Context

### Current State

1. **Main User Dashboard**: Located at `app/Http/Controllers/Admin/DashboardController.php` (user_dashboard method) - handles general customer statistics and overview
2. **Beauty Module Customer Dashboard**: Located at `Modules/BeautyBooking/Http/Controllers/Web/Customer/BeautyDashboardController.php` - handles beauty-specific features
3. **Beauty Module Routes**: 
   - Web: `Modules/BeautyBooking/Routes/web/customer/routes.php`
   - API: `Modules/BeautyBooking/Routes/api/v1/customer/api.php`

### Beauty Module Features Overview

#### Admin Features (Reference for what's available):
- Salon management (approval, verification, status)
- Category management
- Staff management
- Service management
- Booking management (view, calendar, invoices, refunds)
- Review moderation (approve/reject)
- Package management
- Gift card management
- Retail product management
- Loyalty campaign management
- Subscription management
- Commission settings
- Financial reports (monthly summary, package usage, loyalty stats, top-rated, trending, revenue breakdown)
- Settings (home page setup, email formats)

#### Vendor Features (Reference for what vendors can do):
- Dashboard (booking statistics, revenue overview)
- Salon registration/profile management
- Staff management
- Service management
- Calendar management (view bookings, create blocks)
- Booking management (confirm, complete, cancel, mark paid, invoices)
- Subscription purchase
- Package creation/management
- Gift card management
- Retail product management
- Loyalty campaign management
- Finance/payouts
- Badge status
- Reports
- Review replies
- Settings

#### Existing Customer Features (Already implemented):
- Dashboard (upcoming bookings overview)
- Booking wizard (create booking)
- My bookings (list, detail view)
- Wallet transactions
- Gift cards (purchased)
- Loyalty points (balance, history)
- Consultations (pre/post consultation bookings)
- Reviews (user's reviews)
- Retail orders

## Requirements

### 1. Dashboard Widgets & Statistics

Identify what beauty-related widgets and statistics should appear on the main user dashboard:

- **Quick Stats Cards**: What metrics should be displayed?
  - Upcoming bookings count
  - Total bookings (all-time, this month, this year)
  - Total spent on beauty services
  - Active packages count
  - Gift card balance
  - Loyalty points balance
  - Pending reviews count
  - Active consultations count
  - Retail orders count

- **Charts/Graphs**: What visualizations are needed?
  - Booking trends over time
  - Spending trends
  - Most used services
  - Favorite salons
  - Loyalty points earned over time

- **Recent Activity Feed**: What activities should be shown?
  - Recent bookings
  - Recent reviews submitted
  - Recent gift card purchases/redemptions
  - Recent package purchases
  - Recent retail orders
  - Recent loyalty point transactions

### 2. Navigation & Menu Integration

Plan how beauty features should be accessible from the main dashboard:

- **Main Menu Items**: What beauty-related menu items should appear?
  - Beauty Bookings (with submenu: My Bookings, Create Booking, Booking History)
  - Beauty Services (with submenu: Search Salons, My Favorites, Top Rated)
  - Packages & Subscriptions
  - Gift Cards
  - Loyalty Program
  - Consultations
  - Reviews
  - Retail Shop
  - Beauty Wallet

- **Quick Action Buttons**: What quick actions should be available?
  - "Book Appointment" button
  - "Browse Salons" button
  - "Redeem Gift Card" button
  - "Check Loyalty Points" button
  - "View Packages" button

- **Notification Badges**: What notification counts should be shown?
  - Upcoming bookings (count)
  - Pending reviews to submit (count)
  - Available gift cards (count)
  - Active packages (count)
  - Unread consultation messages (count)

### 3. Integration Points

Identify where and how to integrate beauty features:

- **Controller Integration**:
  - Which controller methods need to be added/modified in the main dashboard controller?
  - How to check if BeautyBooking module is active before showing features?
  - How to handle module activation/deactivation gracefully?

- **View Integration**:
  - Which Blade views need to be created/modified?
  - How to include beauty widgets in the main dashboard view?
  - How to handle partial views for beauty features?
  - Where should beauty-specific views be located?

- **Route Integration**:
  - How to add beauty routes to the main customer routes?
  - Should beauty routes be prefixed or nested?
  - How to handle route conflicts?

- **Database Queries**:
  - What queries are needed to fetch beauty statistics?
  - How to optimize queries for dashboard performance?
  - What eager loading is needed?

### 4. Feature-Specific Integration

For each major feature, plan the integration:

#### Bookings
- Dashboard widget showing upcoming bookings
- Quick access to booking history
- Booking status indicators
- Cancellation options
- Rescheduling options
- Invoice download links

#### Packages
- Active packages display
- Package usage progress
- Available packages to purchase
- Package expiration warnings

#### Gift Cards
- Gift card balance display
- Active gift cards list
- Gift card purchase/redemption options
- Gift card expiration warnings

#### Loyalty Program
- Points balance display
- Points history
- Available rewards/campaigns
- Points expiration warnings
- Redemption options

#### Consultations
- Active consultations list
- Consultation history
- Consultation booking options
- Consultation messages/chat

#### Reviews
- Pending reviews to submit
- Review history
- Review editing options
- Review statistics (total reviews, average rating given)

#### Retail Orders
- Recent retail orders
- Order status tracking
- Reorder options
- Order history

#### Wallet Integration
- Beauty-specific wallet transactions
- Wallet balance for beauty bookings
- Payment history for beauty services

### 5. Related Files to Consider

Identify all files that need to be created, modified, or referenced:

**Controllers:**
- `app/Http/Controllers/Admin/DashboardController.php` (main dashboard)
- `Modules/BeautyBooking/Http/Controllers/Web/Customer/BeautyDashboardController.php` (beauty dashboard)
- Any new controllers needed for integration

**Views:**
- Main dashboard view location
- Beauty module customer views location
- Partial views for widgets
- Layout files

**Routes:**
- Main customer routes file
- Beauty module customer routes file
- API routes (if needed for AJAX widgets)

**Models:**
- `Modules/BeautyBooking/Entities/BeautyBooking.php`
- `Modules/BeautyBooking/Entities/BeautyPackage.php`
- `Modules/BeautyBooking/Entities/BeautyGiftCard.php`
- `Modules/BeautyBooking/Entities/BeautyLoyaltyPoint.php`
- `Modules/BeautyBooking/Entities/BeautyReview.php`
- `Modules/BeautyBooking/Entities/BeautyRetailOrder.php`
- `App\Models\User.php` (check for relationships)

**Services:**
- `Modules/BeautyBooking/Services/BeautyBookingService.php`
- `Modules/BeautyBooking/Services/BeautyLoyaltyService.php`
- Any new services needed for dashboard statistics

**Helpers:**
- `App/CentralLogics/Helpers.php` (check for existing helper functions)
- Module status checking functions

**Config:**
- `Modules/BeautyBooking/Config/config.php`
- Main app config files

**JavaScript/CSS:**
- Dashboard JavaScript files
- Beauty module assets
- Chart libraries (if needed)

### 6. Technical Considerations

- **Module Activation Check**: How to check if BeautyBooking module is active before showing features?
- **Performance**: How to optimize dashboard loading with beauty statistics?
- **Caching**: What data should be cached? (e.g., statistics, counts)
- **AJAX Loading**: Which widgets should load via AJAX for better performance?
- **Error Handling**: How to handle errors gracefully when module is inactive?
- **Backward Compatibility**: Ensure main dashboard still works when beauty module is disabled
- **Database Queries**: Optimize queries to prevent N+1 problems
- **Authorization**: Ensure users can only see their own data

### 7. User Experience Considerations

- **Progressive Disclosure**: Should all features be visible immediately or hidden until needed?
- **Empty States**: What to show when user has no bookings/packages/gift cards?
- **Onboarding**: Should there be a tutorial or guide for first-time beauty module users?
- **Mobile Responsiveness**: Ensure all widgets work on mobile devices
- **Loading States**: Show loading indicators for AJAX-loaded content
- **Error Messages**: User-friendly error messages when features are unavailable

### 8. Implementation Phases

Plan the implementation in phases:

**Phase 1: Core Integration**
- Basic dashboard widgets (upcoming bookings, quick stats)
- Navigation menu items
- Module activation checks

**Phase 2: Statistics & Analytics**
- Charts and graphs
- Detailed statistics
- Activity feeds

**Phase 3: Feature Access**
- Quick action buttons
- Direct links to beauty features
- Notification badges

**Phase 4: Advanced Features**
- AJAX loading
- Caching
- Performance optimization

## Deliverables

The plan should include:

1. **Detailed Feature List**: All beauty features that should be accessible from main dashboard
2. **Widget Specifications**: Exact widgets to create with their data requirements
3. **File Structure**: Complete list of files to create/modify
4. **Database Query Plans**: Optimized queries for each widget/statistic
5. **Route Structure**: Complete route mapping
6. **View Structure**: Blade view organization
7. **Controller Methods**: New methods needed in controllers
8. **Integration Points**: Exact locations where code should be added
9. **Testing Checklist**: What to test after implementation
10. **Implementation Steps**: Step-by-step implementation guide

## Constraints

- Must work when BeautyBooking module is disabled (graceful degradation)
- Must follow existing code patterns and conventions
- Must use existing helper functions where possible
- Must maintain backward compatibility
- Must follow PSR-12 coding standards
- Must include bilingual comments (Persian + English)
- Must use existing translation system
- Must follow existing authentication/authorization patterns

## Success Criteria

- Main dashboard shows beauty-related statistics when module is active
- All beauty features are accessible from main dashboard
- Dashboard loads quickly (< 2 seconds)
- No errors when module is disabled
- Mobile-responsive design
- All features work correctly
- Code follows project standards

## Additional Notes

- Consider existing dashboard patterns in the codebase
- Reference `Modules/Rental/` module if similar integration exists
- Check for existing widget/statistic patterns
- Consider using Laravel's view composers for shared data
- Consider using service classes for complex statistics calculations
- Ensure proper eager loading to prevent N+1 queries
- Consider using database indexes for performance

---

**Use this prompt to create a comprehensive, actionable plan that can be directly implemented by a developer.**

