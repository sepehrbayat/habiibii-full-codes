# Beauty Booking Module - Login Credentials & URLs
# اطلاعات ورود به سیستم ماژول رزرو زیبایی

**Server URL:** `http://localhost:8000`

---

## 🔐 Admin Panel (پنل ادمین)

### Login URL
```
http://localhost:8000/login/admin
```

### Credentials

**✅ Both accounts are now ready to use:**

**Option 1:**
- **Email:** `admin@example.com`
- **Password:** `12345678`
- **Status:** ✅ Password verified and working

**Option 2:**
- **Email:** `admin@admin.com`
- **Password:** `12345678`
- **Status:** ✅ Account created and ready

**Note:** If login still fails, run these commands:
```bash
# Reset password for admin@example.com
php artisan tinker --execute="\$admin = \App\Models\Admin::where('email', 'admin@example.com')->first(); if(\$admin) { \$admin->password = bcrypt('12345678'); \$admin->save(); echo 'Password reset'; }"

# Or create admin@admin.com
php artisan tinker --execute="\$admin = \App\Models\Admin::firstOrNew(['email' => 'admin@admin.com']); \$admin->f_name = 'Master'; \$admin->l_name = 'Admin'; \$admin->phone = '1234567890'; \$admin->password = bcrypt('12345678'); \$admin->image = 'def.png'; \$admin->role_id = 1; \$admin->save(); echo 'Admin created';"
```

### After Login
- **Dashboard:** `http://localhost:8000/admin/beautybooking`
- **Help Pages:** `http://localhost:8000/admin/beautybooking/help`
- **Salon Management:** `http://localhost:8000/admin/beautybooking/salon/list`
- **Bookings:** `http://localhost:8000/admin/beautybooking/booking/list`
- **Reviews:** `http://localhost:8000/admin/beautybooking/review/list`
- **Reports:** `http://localhost:8000/admin/beautybooking/reports/*`

---

## 🏪 Vendor Panel (پنل فروشنده)

### Login URL
```
http://localhost:8000/login/vendor
```

### Credentials
- **Email:** `test.restaurant@gmail.com`
- **Password:** `12345678`

### After Login
- **Dashboard:** `http://localhost:8000/vendor-panel/dashboard`
- **Beauty Booking Dashboard:** `http://localhost:8000/vendor-panel/beautybooking/dashboard`
- **Calendar:** `http://localhost:8000/vendor-panel/beautybooking/calendar`
- **Bookings:** `http://localhost:8000/vendor-panel/beautybooking/booking/list`
- **Services:** `http://localhost:8000/vendor-panel/beautybooking/service/list`
- **Staff:** `http://localhost:8000/vendor-panel/beautybooking/staff/list`

---

## 📱 Customer API (API مشتری)

### Login Endpoint
```
POST http://localhost:8000/api/v1/auth/login
```

### Request Body
```json
{
    "email": "customer@example.com",
    "password": "password123"
}
```

### Response
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

### Using the Token
Include the token in subsequent API requests:
```
Authorization: Bearer {token}
```

### Customer API Endpoints (After Login)
- **Search Salons:** `GET /api/v1/beautybooking/salons/search`
- **Create Booking:** `POST /api/v1/beautybooking/bookings`
- **My Bookings:** `GET /api/v1/beautybooking/bookings`
- **Check Availability:** `POST /api/v1/beautybooking/availability/check`
- **Create Review:** `POST /api/v1/beautybooking/reviews`

**Note:** You may need to create a test customer account first via:
```
POST /api/v1/auth/sign-up
```

---

## 🚚 Vendor API (API فروشنده)

### Login Endpoint
```
POST http://localhost:8000/api/v1/auth/seller/login
```

### Request Headers
```
Content-Type: application/json
X-APP-KEY: {vendor_app_key}
```

### Request Body
```json
{
    "email": "test.restaurant@gmail.com",
    "password": "12345678"
}
```

### Response
```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "vendor": {
        "id": 1,
        "f_name": "Vendor",
        "l_name": "Name",
        "email": "test.restaurant@gmail.com"
    }
}
```

### Vendor API Endpoints (After Login)
- **Bookings List:** `GET /api/v1/beautybooking/bookings/list/{all}`
- **Confirm Booking:** `PUT /api/v1/beautybooking/bookings/confirm`
- **Staff Management:** `GET /api/v1/beautybooking/staff/list`
- **Service Management:** `GET /api/v1/beautybooking/service/list`
- **Calendar Availability:** `GET /api/v1/beautybooking/calendar/availability`

---

## 📝 Quick Test Checklist

### Admin Panel Testing
- [ ] Login at `/login/admin`
- [ ] Access Beauty Booking dashboard
- [ ] Navigate to Help section from sidebar
- [ ] Test all 5 help guide pages
- [ ] Verify route names work correctly
- [ ] Check page titles render properly

### Vendor Panel Testing
- [ ] Login at `/login/vendor`
- [ ] Access Beauty Booking vendor dashboard
- [ ] Test calendar view
- [ ] Manage services and staff
- [ ] View bookings

### API Testing
- [ ] Test customer login endpoint
- [ ] Test vendor login endpoint
- [ ] Test booking creation
- [ ] Test salon search
- [ ] Test availability checking

---

## 🔧 Troubleshooting

### If login fails:
1. **Check database:** Ensure seeders have been run
   ```bash
   php artisan db:seed
   ```

2. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

3. **Check module status:**
   - Verify `modules_statuses.json` has `"BeautyBooking": true`
   - Check module is published: `php artisan module:publish BeautyBooking`

### If routes don't work:
1. **Clear route cache:**
   ```bash
   php artisan route:clear
   php artisan route:cache
   ```

2. **Verify routes are loaded:**
   ```bash
   php artisan route:list | grep beautybooking
   ```

---

## 📌 Important Notes

- **Default credentials** are for development/testing only
- **Change passwords** in production environment
- **API tokens** expire after 24 hours of inactivity
- **Customer accounts** may need to be created via sign-up endpoint
- **Vendor accounts** must be associated with a Store in the database

---

**Last Updated:** 2025-11-29  
**Server Status:** ✅ Running on port 8000

