# Local Development Setup Guide

## ✅ Completed Steps

1. **Environment Configuration**
   - Created `.env` file from `.env.example`
   - Generated application key

2. **Dependencies Installed**
   - ✅ PHP dependencies (Composer packages)
   - ✅ Node.js dependencies (npm packages)
   - ✅ PHP XML/DOM extension installed

3. **Laravel Setup**
   - ✅ Application key generated
   - ✅ Storage symlink created
   - ✅ Configuration cache cleared

## ⚠️ Required: Database Setup

The application requires a MySQL database. Current configuration in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multi_food_db
DB_USERNAME=root
DB_PASSWORD=
```

### To Complete Database Setup:

1. **Start MySQL Service** (if not running):
   ```bash
   sudo systemctl start mysql
   # or
   sudo service mysql start
   ```

2. **Create Database**:
   ```bash
   mysql -u root -p
   CREATE DATABASE multi_food_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   EXIT;
   ```

3. **Update .env** with your database credentials if different from defaults

4. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

5. **Seed Database** (optional):
   ```bash
   php artisan db:seed
   ```

## 🚀 Starting the Application

### Development Server
The server should be starting on: **http://127.0.0.1:8000**

To start manually:
```bash
php artisan serve
```

Or with custom host/port:
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Frontend Assets (if needed)
If you need to compile frontend assets:
```bash
npm run dev
# or for production
npm run prod
```

## 📝 Next Steps

1. **Configure Database**: Set up MySQL and update `.env` if needed
2. **Run Migrations**: `php artisan migrate`
3. **Access Application**: Open http://127.0.0.1:8000 in your browser
4. **Module Setup**: If using BeautyBooking module, ensure it's enabled in `modules_statuses.json`

## 🔧 Troubleshooting

### Database Connection Issues
- Verify MySQL is running: `sudo systemctl status mysql`
- Check database credentials in `.env`
- Ensure database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### Missing PHP Extensions
If you encounter missing extension errors:
```bash
sudo apt-get install php8.4-xml php8.4-dom php8.4-mbstring php8.4-curl php8.4-zip php8.4-gd
```

### Permission Issues
Ensure storage and cache directories are writable:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 📦 Module Status

Check module status:
```bash
cat modules_statuses.json
```

Enable BeautyBooking module if needed by setting status to 1 in `modules_statuses.json`.

