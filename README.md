# Habiibii Full Codes - Complete Multi-Vendor Platform

🚀 **Complete source code repository for the Habiibii multi-vendor food delivery, grocery, pharmacy, and e-commerce platform.**

## 📦 Project Overview

This repository contains the complete source code for a comprehensive 6amMart-based multi-vendor platform with multiple modules and applications.

### 🎯 Platform Components

#### 1. **Backend API (Laravel)**
- **Location**: `/backend`
- **Technology**: Laravel PHP Framework
- **Database**: MySQL (multi_food_db)
- **Port**: 8000 (development)
- **Features**:
  - Multi-vendor management
  - Order processing system
  - Payment gateway integrations
  - Real-time notifications
  - Admin panel backend
  - RESTful API for all apps

#### 2. **Frontend Website (Next.js/React)**
- **Location**: `/frontend`
- **Technology**: Next.js, React, Redux
- **Features**:
  - Customer web interface
  - Store listings and search
  - Order placement
  - User dashboard
  - Responsive design

#### 3. **Customer Mobile App (Flutter)**
- **Location**: `/6amMart - Customer app with Admin & Website`
- **Technology**: Flutter (Dart)
- **Platforms**: iOS, Android, Web
- **Features**:
  - Browse stores and products
  - Place orders (Food, Grocery, Pharmacy, E-commerce)
  - Track deliveries in real-time
  - Multiple payment methods
  - User profiles and order history
  - Location-based services

#### 4. **Vendor Mobile App (Flutter)**
- **Location**: `/6amMart - Vendor App`
- **Technology**: Flutter (Dart)
- **Platforms**: iOS, Android
- **Features**:
  - Store management
  - Product/menu management
  - Order processing
  - Inventory tracking
  - Sales analytics
  - Push notifications

#### 5. **Delivery Man App (Flutter)**
- **Location**: `/6amMart - Delivery Man App`
- **Technology**: Flutter (Dart)
- **Platforms**: iOS, Android
- **Features**:
  - Order assignment
  - Real-time navigation
  - Delivery tracking
  - Earnings management
  - Route optimization

#### 6. **React User Website (Additional)**
- **Location**: `/6amMart - React User Website`
- **Technology**: React
- **Status**: Update files (V3.3 to V3.4)

#### 7. **Car Rental Module**
- **Location**: `/6amMart Car Rental Module v1.4`
- **Technology**: PHP/Laravel Module
- **Features**: Vehicle rental booking system integration

## 🛠️ Technology Stack

### Backend
- **Framework**: Laravel 10.x
- **Database**: MySQL 8.x
- **Cache**: Redis (optional)
- **Queue**: Laravel Queue
- **API**: RESTful API

### Frontend
- **Framework**: Next.js 13+
- **UI Library**: React 18+
- **State Management**: Redux Toolkit
- **Styling**: Material-UI, Custom CSS
- **Maps**: Google Maps API

### Mobile Apps
- **Framework**: Flutter 3.35.6
- **Language**: Dart
- **State Management**: Provider/GetX
- **Maps**: Google Maps Flutter
- **Push Notifications**: Firebase Cloud Messaging

## 📋 Prerequisites

### For Backend (Laravel)
```bash
- PHP >= 8.1
- Composer
- MySQL >= 8.0
- Node.js >= 16.x (for asset compilation)
```

### For Frontend (Next.js)
```bash
- Node.js >= 18.x
- npm or yarn
```

### For Mobile Apps (Flutter)
```bash
- Flutter SDK >= 3.35.6
- Android Studio (for Android development)
- Xcode (for iOS development, macOS only)
- CocoaPods (for iOS dependencies)
```

## 🚀 Quick Start

### Backend Setup

```bash
# Navigate to backend directory
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
DB_DATABASE=multi_food_db
DB_USERNAME=root
DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Start development server
php artisan serve --port=8000
```

### Frontend Setup

```bash
# Navigate to frontend directory
cd frontend

# Install dependencies
npm install
# or
yarn install

# Copy environment file
cp .env.example .env.local

# Configure API endpoint
NEXT_PUBLIC_API_URL=http://localhost:8000

# Start development server
npm run dev
# or
yarn dev
```

### Flutter Apps Setup

```bash
# Navigate to any Flutter app directory
cd "6amMart - Customer app with Admin & Website/codecanyon-iFOYzqED-.../User app and web"

# Get dependencies
flutter pub get

# Run on Chrome (for web)
flutter run -d chrome

# Run on Android emulator
flutter run -d android

# Run on iOS simulator (macOS only)
flutter run -d ios
```

## ⚙️ Configuration

### Backend Configuration

Edit `backend/.env`:

```env
APP_NAME="Habiibii"
APP_URL=http://localhost:8000
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multi_food_db
DB_USERNAME=root
DB_PASSWORD=

# Map API Keys
GOOGLE_MAPS_API_KEY=your_google_maps_key

# Payment Gateways
STRIPE_KEY=your_stripe_key
PAYPAL_CLIENT_ID=your_paypal_client_id

# Firebase (for push notifications)
FIREBASE_SERVER_KEY=your_firebase_server_key
```

### Frontend Configuration

Edit `frontend/.env.local`:

```env
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_GOOGLE_MAPS_KEY=your_google_maps_key
```

### Flutter App Configuration

Edit `lib/util/app_constants.dart`:

```dart
class AppConstants {
  static const String appName = 'Habiibii';
  static const String baseUrl = 'http://localhost';
  static const int _backendPort = 8000;
  
  // Development mode
  static const bool enableDebugLogs = true;
}
```

## 🧪 Development Features

### Development Mode Enhancements

The Flutter customer app includes development shortcuts:

- **Location Picker Skip**: Automatically skips location selection in debug mode
- **Map Skip**: Bypasses map interface with visual feedback
- **Debug Logging**: Enhanced logging for development

These features are controlled by `AppConstants.enableDebugLogs` and Flutter's `kDebugMode`.

## 📁 Project Structure

```
habiibii-full-codes/
├── backend/                          # Laravel backend API
│   ├── app/                          # Application logic
│   ├── config/                       # Configuration files
│   ├── database/                     # Migrations & seeders
│   ├── Modules/                      # Modular features
│   │   └── BeautyBooking/           # Beauty salon booking module
│   ├── public/                       # Public assets
│   ├── resources/                    # Views & assets
│   ├── routes/                       # API & web routes
│   └── storage/                      # File storage
│
├── frontend/                         # Next.js frontend
│   ├── pages/                        # Next.js pages
│   ├── src/                          # Source code
│   │   ├── components/               # React components
│   │   ├── redux/                    # State management
│   │   └── utils/                    # Utilities
│   └── public/                       # Static assets
│
├── 6amMart - Customer app with Admin & Website/
│   └── User app and web/            # Flutter customer app
│       ├── lib/                      # Dart source code
│       ├── android/                  # Android configuration
│       ├── ios/                      # iOS configuration
│       └── web/                      # Web configuration
│
├── 6amMart - Vendor App/            # Flutter vendor app
│   └── codecanyon-.../
│       └── lib/                      # Dart source code
│
├── 6amMart - Delivery Man App/      # Flutter delivery app
│   └── codecanyon-.../
│       └── lib/                      # Dart source code
│
├── 6amMart Car Rental Module v1.4/  # Rental module
│   └── Rental/
│       └── Routes/                   # Module routes
│
└── README.md                         # This file
```

## 🔧 Development Notes

### CORS Configuration

The backend is configured to allow local development:

```php
// backend/config/cors.php
'allowed_origins_patterns' => [
    '/^http:\/\/(localhost|127\.0\.0\.1).*$/',
],
```

### API Endpoints

All APIs are accessible at: `http://localhost:8000/api/v1/`

Key endpoints:
- Authentication: `/api/v1/auth/*`
- Stores: `/api/v1/stores`
- Products: `/api/v1/products`
- Orders: `/api/v1/customer/order/*`
- Beauty Bookings: `/api/v1/beauty/*`

### Test Credentials

```
Customer Account:
Email: john@customer.com
Password: 12345678

Database:
Host: 127.0.0.1
Port: 3306
Database: multi_food_db
Username: root
```

## 🚨 Important Notes

### Security

- **SQL Dumps Excluded**: Database dumps are excluded from the repository for security
- **Environment Files**: `.env` files must be created from `.env.example`
- **API Keys**: Configure your own API keys for Google Maps, Firebase, payment gateways

### Build Artifacts Excluded

The following are excluded from version control:
- `node_modules/` - Node.js dependencies
- `vendor/` - PHP dependencies
- `build/` - Compiled artifacts
- `.next/` - Next.js build cache
- `.dart_tool/` - Flutter build cache
- `*.apk`, `*.ipa` - Mobile app binaries

### Backups

This repository includes backup directories:
- `backend.backup.20251219_151723/` - Backend backup
- `frontend.backup.20251219_151723/` - Frontend backup

These are kept for reference but excluded from tracking.

## 🐛 Known Issues & Fixes

### Issue: Location Picker Not Skipping in Dev Mode
**Status**: ✅ Fixed  
**Solution**: Added dev mode skip logic to both dashboard and web home screens

### Issue: Map Interface in Development
**Status**: ✅ Fixed  
**Solution**: Implemented visual feedback when map is skipped in debug mode

### Issue: CORS Errors in Local Development
**Status**: ✅ Fixed  
**Solution**: Updated CORS configuration to allow all localhost connections

## 📞 Support & Documentation

### API Documentation
- Swagger/OpenAPI docs available at: `http://localhost:8000/api/documentation`

### Module Documentation
- Beauty Booking: See `/backend/Modules/BeautyBooking/README.md`
- Rental Module: See `/6amMart Car Rental Module v1.4/README.md`

## 📝 License

This project contains commercial code from CodeCanyon (6amMart).  
Please ensure you have proper licenses for all components.

## 🤝 Contributing

This is a private project repository. For contributions:

1. Create a feature branch
2. Make your changes
3. Test thoroughly
4. Submit a pull request

## 📊 Stats

- **Total Files**: ~6,400 source files
- **Languages**: PHP, JavaScript/TypeScript, Dart, HTML/CSS
- **Lines of Code**: ~500,000+ (estimated)
- **Repository Size**: ~63 MB (optimized)

## 🎉 Credits

- **Platform Base**: 6amMart Multi-Vendor System
- **Backend Framework**: Laravel
- **Frontend Framework**: Next.js/React
- **Mobile Framework**: Flutter
- **Development**: Habiibii Development Team

---

**Last Updated**: December 26, 2025  
**Version**: 1.0.0  
**Repository**: https://github.com/sepehrbayat/habiibii-full-codes
