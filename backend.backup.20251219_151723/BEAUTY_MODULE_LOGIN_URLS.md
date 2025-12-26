# Beauty Booking Module - Login URLs

## Web Login URLs

The system uses custom login URLs stored in the database. Here are the default login URLs for different roles:

### Admin Panel
- **URL**: `http://localhost:8000/login/admin`
- **Role**: Admin (Super Admin with role_id = 1)
- **Credentials**: 
  - Email: `admin@example.com` or `admin@admin.com`
  - Password: `12345678`

### Admin Employee
- **URL**: `http://localhost:8000/login/admin-employee`
- **Role**: Admin Employee (role_id != 1)

### Vendor Panel
- **URL**: `http://localhost:8000/login/vendor`
- **Role**: Vendor/Store Owner

### Vendor Employee
- **URL**: `http://localhost:8000/login/vendor-employee`
- **Role**: Store Employee

## API Login Endpoints

### Customer API
- **URL**: `POST http://localhost:8000/api/v1/auth/login`
- **Body**: 
  ```json
  {
    "email": "customer@example.com",
    "password": "password"
  }
  ```

### Vendor API
- **URL**: `POST http://localhost:8000/api/v1/auth/seller/login`
- **Body**: 
  ```json
  {
    "email": "vendor@example.com",
    "password": "password"
  }
  ```

### Delivery Man API
- **URL**: `POST http://localhost:8000/api/v1/auth/rider/login`
- **Body**: 
  ```json
  {
    "email": "deliveryman@example.com",
    "password": "password"
  }
  ```

## How Login URLs Work

1. The login URLs are stored in the `data_settings` table with keys:
   - `admin_login_url` (default: `admin`)
   - `admin_employee_login_url` (default: `admin-employee`)
   - `store_login_url` (default: `vendor`)
   - `store_employee_login_url` (default: `vendor-employee`)

2. The route is: `Route::get('login/{tab}', 'LoginController@login')`

3. The controller checks if the `{tab}` parameter matches one of the stored URLs in the database.

4. If the URL doesn't match, it returns a 404 error.

## Troubleshooting 404 Error

If you're getting a 404 error on `/login/admin`:

1. **Check database values**:
   ```php
   php artisan tinker
   \App\Models\DataSetting::whereIn('key', ['admin_login_url', 'store_login_url'])->get(['key', 'value']);
   ```

2. **Verify the URL matches exactly** - The URL must match the value stored in the database.

3. **Check route registration**:
   ```bash
   php artisan route:list | grep login
   ```

4. **Clear route cache**:
   ```bash
   php artisan route:clear
   php artisan cache:clear
   ```

## Changing Login URLs

You can change login URLs from the admin panel:
- Navigate to: **Business Settings > Login Setup > Panel Login Page URL**
- Or directly update in database:
  ```php
  \App\Models\DataSetting::updateOrInsert(
      ['key' => 'admin_login_url', 'type' => 'login_admin'],
      ['value' => 'your-custom-url']
  );
  ```

## Notes

- Login URLs are customizable and can be changed from the admin panel
- The URLs must be unique across all login types
- URLs can only contain alphanumeric characters, hyphens, and underscores
- After changing URLs, clear the cache: `php artisan route:clear && php artisan cache:clear`

