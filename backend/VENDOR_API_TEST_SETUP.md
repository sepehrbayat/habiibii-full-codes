# Vendor API Test Setup Guide

## Current Status

✅ **Test Files Created:**
- `Modules/BeautyBooking/Tests/Feature/Api/Vendor/VendorApiTest.php` - 44 comprehensive test methods
- `Modules/BeautyBooking/Tests/Feature/Api/Vendor/VendorApiAuthenticationTest.php` - Authentication tests

✅ **Configuration Updates:**
- Fixed `SOFTWARE_INFO` constant redefinition issue
- Created base `users` table migration
- Created `VendorFactory` and `StoreFactory`
- Updated `phpunit.xml` for MySQL testing

## Database Setup Required

Use the existing project database (no separate test DB needed):

- Ensure `phpunit.xml` (or `.env.testing`) points to the same DB you use for development.
- If you need to override, uncomment the DB server entries in `phpunit.xml` and set real values (no placeholders).
- The `RefreshDatabase` trait will migrate and roll back automatically for each test run.

If you prefer a dedicated DB, you can still create one and point `phpunit.xml` to it, but it is optional.

## Base Table Migrations

The following base table migrations have been created for testing:

1. ✅ **vendors table** - `2014_10_11_000000_create_vendors_table.php`
2. ✅ **zones table** - `2014_10_11_000001_create_zones_table.php`
3. ✅ **modules table** - `2014_10_11_000002_create_modules_table.php`
4. ✅ **stores table** - `2014_10_11_000003_create_stores_table.php`
5. ✅ **users table** - `2014_10_12_000000_create_users_table.php`

These migrations are safe (check for table existence before creating) and will work with both fresh and existing databases.

## Running the Tests

Once the database is configured:

```bash
# Run all vendor API tests
vendor/bin/phpunit --filter=VendorApiTest

# Run authentication tests
vendor/bin/phpunit --filter=VendorApiAuthenticationTest

# Run specific test
vendor/bin/phpunit --filter=test_vendor_can_list_all_bookings
```

## Factories Created

All required factories have been created and registered:

- ✅ `VendorFactory` - Registered in `app/Models/Vendor.php`
- ✅ `StoreFactory` - Registered in `app/Models/Store.php`
- ✅ `BeautySalonFactory` - Registered in `Modules/BeautyBooking/Entities/BeautySalon.php`
- ✅ `BeautyServiceCategoryFactory` - Registered in `Modules/BeautyBooking/Entities/BeautyServiceCategory.php`
- ✅ `BeautyServiceFactory` - Registered in `Modules/BeautyBooking/Entities/BeautyService.php`
- ✅ `BeautyStaffFactory` - Registered in `Modules/BeautyBooking/Entities/BeautyStaff.php`
- ✅ `BeautyBookingFactory` - Registered in `Modules/BeautyBooking/Entities/BeautyBooking.php`

All factories are located in:
- Base factories: `database/factories/`
- Module factories: `Modules/BeautyBooking/Database/Factories/`

## Test Coverage

The test suite covers all 44 vendor API endpoints:

- ✅ Booking Management (6 endpoints)
- ✅ Staff Management (6 endpoints)
- ✅ Service Management (6 endpoints)
- ✅ Calendar Management (3 endpoints)
- ✅ Salon Management (6 endpoints)
- ✅ Retail Management (3 endpoints)
- ✅ Subscription Management (3 endpoints)
- ✅ Finance & Reports (2 endpoints)
- ✅ Badge Status (1 endpoint)
- ✅ Package Management (2 endpoints)
- ✅ Gift Card Management (2 endpoints)
- ✅ Loyalty Campaign Management (3 endpoints)

## Troubleshooting

### Error: "Table doesn't exist"
- Ensure all base table migrations have been run
- Check that the test database has all required tables

### Error: "Factory not found"
- Ensure `VendorFactory` and `StoreFactory` exist in `database/factories/`
- Run `composer dump-autoload` if factories were just created

### Error: "PDO driver not found"
- Install required PDO drivers (pdo_mysql, pdo_sqlite)
- Or configure tests to use available database driver

## Next Steps

1. Set up test database (Option 1 or 2 above)
2. Ensure all base table migrations exist and have been run
3. Run the test suite to verify all endpoints work correctly
4. Integrate tests into CI/CD pipeline

