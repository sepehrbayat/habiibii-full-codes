# ⚛️ راهنمای استقرار React Frontend

## 📋 پیش‌نیازها

- Node.js >= 16.x
- NPM یا Yarn
- دسترسی به سرور Laravel

---

## 🔧 تنظیمات Laravel برای React

### 1. تنظیم CORS

ویرایش `config/cors.php`:

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',  // Development
        'http://188.245.192.118:3000',  // Production
        'https://your-react-domain.com',  // Production Domain
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
```

### 2. تنظیم .env در Laravel

```env
APP_URL=http://188.245.192.118
FRONTEND_URL=http://188.245.192.118:3000
```

### 3. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

---

## ⚛️ تنظیمات React Project

### 1. ایجاد فایل .env

در ریشه پروژه React:

```env
REACT_APP_API_URL=http://188.245.192.118/api/v1
REACT_APP_API_BASE_URL=http://188.245.192.118
REACT_APP_WS_URL=ws://188.245.192.118:6001
REACT_APP_ENV=production
```

### 2. تنظیم Axios

```javascript
// src/config/api.js
import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_API_BASE_URL || 'http://188.245.192.118';
const API_URL = `${API_BASE_URL}/api/v1`;

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
});

// Interceptor برای اضافه کردن Token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor برای مدیریت خطاها
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Logout user
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

### 3. استفاده در Components

```javascript
// src/services/authService.js
import api from '../config/api';

export const login = async (email, password) => {
  const response = await api.post('/auth/login', {
    email,
    password,
  });
  return response.data;
};

export const getProfile = async () => {
  const response = await api.get('/auth/profile');
  return response.data;
};
```

---

## 🚀 استقرار React App

### روش 1: استفاده از PM2 (توصیه می‌شود)

#### 1. Build پروژه

```bash
cd /path/to/react-app
npm install
npm run build
```

#### 2. نصب serve

```bash
npm install -g serve
```

#### 3. ایجاد فایل ecosystem.config.js

```javascript
module.exports = {
  apps: [{
    name: '6ammart-react',
    script: 'serve',
    args: '-s build -l 3000',
    env: {
      NODE_ENV: 'production',
      PORT: 3000
    },
    error_file: './logs/err.log',
    out_file: './logs/out.log',
    log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
    merge_logs: true,
    autorestart: true,
    watch: false,
    max_memory_restart: '1G',
  }]
};
```

#### 4. اجرا با PM2

```bash
# در سرور
cd /var/www/react-app
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

#### 5. بررسی وضعیت

```bash
pm2 status
pm2 logs 6ammart-react
```

### روش 2: استفاده از Apache/Nginx

#### 1. Build پروژه

```bash
cd /path/to/react-app
npm run build
```

#### 2. آپلود Build به سرور

```bash
# در سیستم محلی
tar -czf react-build.tar.gz build/

# آپلود
scp react-build.tar.gz root@188.245.192.118:/var/www/

# در سرور
cd /var/www
tar -xzf react-build.tar.gz
mv build react-app
```

#### 3. تنظیم Apache Virtual Host

```apache
<VirtualHost *:3000>
    ServerName 188.245.192.118
    DocumentRoot /var/www/react-app

    <Directory /var/www/react-app>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        # برای React Router
        RewriteEngine On
        RewriteBase /
        RewriteRule ^index\.html$ - [L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.html [L]
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/react_error.log
    CustomLog ${APACHE_LOG_DIR}/react_access.log combined
</VirtualHost>
```

#### 4. فعال‌سازی و Restart

```bash
a2ensite react-app.conf
systemctl reload apache2
```

---

## 🔒 تنظیمات امنیتی

### 1. تنظیم Environment Variables

```bash
# در سرور
nano /var/www/react-app/.env.production
```

```env
REACT_APP_API_URL=http://188.245.192.118/api/v1
REACT_APP_API_BASE_URL=http://188.245.192.118
```

### 2. استفاده از HTTPS

```bash
# نصب Certbot
apt install certbot python3-certbot-apache

# دریافت گواهینامه
certbot --apache -d yourdomain.com
```

### 3. تنظیم CORS در Production

```php
// config/cors.php
'allowed_origins' => [
    'https://your-react-domain.com',
],
```

---

## ✅ تست اتصال

### 1. تست API از React

```javascript
// در React App
import api from './config/api';

const testConnection = async () => {
  try {
    const response = await api.get('/configurations');
    console.log('✅ اتصال موفق:', response.data);
  } catch (error) {
    console.error('❌ خطا در اتصال:', error);
  }
};
```

### 2. تست از Terminal

```bash
# تست API
curl http://188.245.192.118/api/v1/configurations

# تست React App
curl http://188.245.192.118:3000
```

---

## 🐛 عیب‌یابی

### مشکل: CORS Error

**راه‌حل:**
1. بررسی `config/cors.php` در Laravel
2. بررسی `allowed_origins`
3. Clear cache: `php artisan config:clear`

### مشکل: 401 Unauthorized

**راه‌حل:**
1. بررسی Token در localStorage
2. بررسی Authorization header
3. بررسی middleware در Laravel

### مشکل: React Router 404

**راه‌حل:**
1. تنظیم `.htaccess` یا Nginx config
2. اطمینان از Rewrite Rules

---

## 📝 دستورات مفید

### PM2 Commands

```bash
# مشاهده وضعیت
pm2 status

# مشاهده Logs
pm2 logs 6ammart-react

# Restart
pm2 restart 6ammart-react

# Stop
pm2 stop 6ammart-react

# Delete
pm2 delete 6ammart-react
```

### Build و Deploy

```bash
# Build
npm run build

# Test Build
serve -s build -l 3000

# Deploy
pm2 restart 6ammart-react
```

---

**آخرین به‌روزرسانی**: 2024-12-XX

