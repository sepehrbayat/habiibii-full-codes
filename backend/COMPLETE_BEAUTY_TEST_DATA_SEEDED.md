# Complete Beauty Booking Test Data - Seeded

## Summary

تمام داده‌های تست برای ماژول زیبایی ایجاد شده است.

## Created Data

### Module
- **Module ID**: 5
- **Module Name**: `beauty_booking`
- **Module Type**: `beauty`
- **Status**: Active (1)
- **Zones**: 1 zone attached

### Zone
- **Zone ID**: 3
- **Zone Name**: `Tehran Zone`
- **Status**: Active

### User (Test Customer)
- **User ID**: 3
- **Email**: `test@6ammart.com`
- **Password**: `123456`
- **Status**: Active

### Vendor
- **Vendor ID**: 3
- **Email**: `beauty@salon.com`
- **Password**: `123456`

### Store & Salon
- **Store ID**: 3
- **Store Name**: `Beauty Salon Test`
- **Salon ID**: 3
- **Business Type**: `salon`
- **Verification Status**: Approved (1)
- **Is Verified**: Yes
- **Is Featured**: Yes

### Service Categories
Created 6 categories:
1. Hair Services (ID: 7)
2. Facial Treatments (ID: 8)
3. Nail Services (ID: 9)
4. Massage (ID: 10)
5. Haircut (ID: 11)
6. Hair Color (ID: 12)

### Services
Created 3 services:
1. Haircut (ID: 1) - 50.00, 30 minutes
2. Hair Color (ID: 2) - 120.00, 120 minutes
3. Hair Styling (ID: 3) - 80.00, 60 minutes

## API Endpoints for Testing

### 1. Get Modules (for module selection)
```bash
curl -X GET http://193.162.129.214/api/v1/module \
  -H "zoneId: [3]"
```

### 2. Login
```bash
curl -X POST http://193.162.129.214/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "login_type": "manual",
    "email_or_phone": "test@6ammart.com",
    "password": "123456",
    "field_type": "email"
  }'
```

### 3. Get Wish List
```bash
curl -X GET http://193.162.129.214/api/v1/customer/wish-list \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "zoneId: [3]"
```

## Frontend Configuration

برای اینکه ماژول در frontend نمایش داده شود:

1. **Zone ID**: باید در localStorage ذخیره شود:
   ```javascript
   localStorage.setItem("zoneid", "3");
   // یا
   localStorage.setItem("zoneid", "[3]");
   ```

2. **Module**: باید در localStorage ذخیره شود:
   ```javascript
   localStorage.setItem("module", JSON.stringify({
     id: 5,
     module_name: "beauty_booking",
     module_type: "beauty"
   }));
   ```

3. **Token**: بعد از login در localStorage ذخیره می‌شود

## Testing Checklist

- [x] Module created and active
- [x] Zone created and attached to module
- [x] User created (test@6ammart.com)
- [x] Vendor created (beauty@salon.com)
- [x] Store and Salon created
- [x] Service Categories created
- [x] Services created
- [ ] Staff created (needs seeder)
- [ ] Bookings created (needs seeder)
- [ ] Reviews created (needs seeder)
- [ ] Packages created (needs seeder)
- [ ] Gift Cards created (needs seeder)

## Next Steps

برای ایجاد داده‌های کامل‌تر (Staff, Bookings, Reviews, etc.):

```bash
php artisan db:seed --class="Modules\BeautyBooking\Database\Seeders\BeautyBookingTestDataSeeder" --force
```

یا از اسکریپت `seed-beauty-module-test-data.php` استفاده کنید که به صورت خودکار همه داده‌ها را ایجاد می‌کند.

