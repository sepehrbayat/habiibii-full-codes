# 6amMart React - Multi-Module E-Commerce Platform

A comprehensive Next.js-based e-commerce platform supporting multiple business modules including Food Delivery, Grocery, Pharmacy, Beauty Services, and more.

## 🚀 Features

### Core Modules
- **Food Delivery**: Restaurant ordering and delivery
- **Grocery**: Online grocery shopping
- **Pharmacy**: Medicine and health products
- **Beauty Services**: Salon bookings, packages, consultations, gift cards, and loyalty programs
- **Rental**: Vehicle and equipment rental
- **Parcel Delivery**: Package delivery services

### Beauty Module Features
- **Salon Management**: Browse and filter salons by ratings, services, and location
- **Booking System**: Schedule appointments with real-time availability
- **Packages**: Purchase beauty service packages
- **Consultations**: Book consultations with beauty experts
- **Gift Cards**: Purchase and manage gift cards
- **Loyalty Points**: Earn and redeem loyalty points
- **Wallet Integration**: Seamless payment and transaction management
- **Vendor Dashboard**: Complete vendor management system

## 📋 Prerequisites

- Node.js 16.x or higher
- npm or yarn
- Git

## 🛠️ Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd 6ammart-react
```

2. Install dependencies:
```bash
npm install
# or
yarn install
```

3. Set up environment variables:
```bash
cp .env.example .env.local
```

Configure the following variables in `.env.local`:
- `NEXT_PUBLIC_BASE_URL`: API base URL
- `NEXT_PUBLIC_GOOGLE_MAP_KEY`: Google Maps API key
- Firebase configuration (if using Firebase authentication)

4. Run the development server:
```bash
npm run dev
# or
yarn dev
```

5. Open [http://localhost:3000](http://localhost:3000) in your browser.

## 📁 Project Structure

```
6ammart-react/
├── pages/                 # Next.js pages (routes)
│   ├── beauty/           # Beauty module pages
│   ├── home/             # Home page
│   ├── profile/           # User profile pages
│   └── ...
├── src/
│   ├── api-manage/        # API management and hooks
│   ├── components/        # React components
│   │   ├── home/module-wise-components/beauty/  # Beauty module components
│   │   ├── layout/        # Layout components
│   │   ├── navigation/    # Navigation components
│   │   └── ...
│   ├── redux/             # Redux store and slices
│   ├── helper-functions/  # Utility functions
│   ├── theme/             # Theme configuration
│   └── ...
├── public/                 # Static assets
└── ...
```

## 🎨 Key Components

### Beauty Module Components
- `BeautyDashboard`: Main dashboard for beauty services
- `BookingForm`: Appointment booking form
- `BookingList`: List of user bookings
- `PackageList`: Available beauty packages
- `GiftCardList`: Gift card management
- `LoyaltyPoints`: Loyalty points display and management
- `SalonFilters`: Salon filtering and search

### Navigation Components
- `VendorPageHeader`: Reusable header for vendor pages with back button
- `MobileTopMenu`: Mobile hamburger menu with module switching
- `SideDrawer`: Customer profile side drawer with module switching

### Module Selection
- `ModuleSelection`: Modal for selecting/switching between modules
- Supports zone-based module filtering
- Automatic zone ID initialization from selected module

## 🔄 Module Switching

The platform supports seamless switching between modules:

1. **From Header Menu**: Click the hamburger menu (☰) → "Switch Module"
2. **From Profile Menu**: Open profile side drawer → "Switch Module"
3. **From Vendor Dashboard**: Click hamburger menu → "Switch Module"

### Zone Management
- Zone IDs are automatically initialized from the selected module
- Zone information is stored in localStorage
- Zone-based module filtering ensures only relevant modules are shown

## 💳 Wallet & Transactions

### Transaction History
- **Desktop View**: `src/components/transaction-history/index.js`
- **Mobile View**: `src/components/wallet/TransactionHistoryMobile.js`

Features:
- Debit/Credit indicators with proper signs (+/-)
- Balance after transaction display
- Color-coded transaction types
- Formatted dates with tooltips
- Infinite scroll with loading indicators
- Enhanced empty states with CTAs

## 🎯 Recent Updates

### Module Switching Improvements
- Added "Switch Module" button to customer and vendor hamburger menus
- Improved zone ID initialization from module data
- Better error handling for missing zone IDs
- Enhanced ModuleSelection modal with context-aware messages

### Transaction History Enhancements
- Fixed debit/credit amount display with proper signs
- Added balance after transaction chips
- Improved loading states and empty state messaging
- Better date formatting with full timestamp tooltips

### Navigation Improvements
- Created reusable VendorPageHeader component
- Improved breadcrumb navigation in vendor dashboard
- Better back button handling

## 🧪 Development

### Running Tests
```bash
npm run test
```

### Building for Production
```bash
npm run build
npm start
```

### Code Style
The project uses ESLint for code quality. Run:
```bash
npm run lint
```

## 📱 Responsive Design

The platform is fully responsive with:
- Mobile-first approach
- Breakpoints: xs, sm, md, lg, xl
- Touch-optimized components
- Adaptive layouts for all screen sizes

## 🌐 Internationalization

The platform supports multiple languages using `react-i18next`:
- Language files in `src/language/`
- Dynamic language switching
- RTL support for Arabic and similar languages

## 🔐 Authentication

- Email/Phone login
- OTP verification
- Social login (Google, Facebook)
- Guest checkout support
- Firebase authentication integration

## 🛒 Shopping Features

- Shopping cart management
- Wishlist functionality
- Order tracking
- Multiple payment methods
- Address management
- Delivery tracking

## 📦 Deployment

### Vercel (Recommended)
1. Push code to GitHub
2. Import project in Vercel
3. Configure environment variables
4. Deploy

### Other Platforms
The project can be deployed to any platform supporting Next.js:
- Netlify
- AWS Amplify
- Docker containers

## 🤝 Contributing

1. Create a feature branch from `with-beauty-module`
2. Make your changes
3. Test thoroughly
4. Submit a pull request

## 📝 Important Notes

### phpmyadmin Symlink
If you encounter build errors related to `phpmyadmin`, it's a symlink that should be ignored. It's already added to `.gitignore` and excluded from Next.js build process.

### Zone ID Requirements
- Zone IDs are required for module-specific operations
- Zone IDs are automatically initialized from selected module
- If zone ID is missing, the system will attempt to set it from module data

### Module Selection Flow
1. User selects a module
2. Module data is stored in localStorage and Redux
3. Zone IDs are extracted and stored
4. User is redirected to module-specific route
5. Module context is maintained across navigation

## 🐛 Troubleshooting

### Common Issues

**"Zone id required" Error**
- Ensure you've selected a module
- Check localStorage for `zoneid` and `module`
- Try switching modules to reinitialize zone IDs

**Build Errors**
- Clear `.next` directory: `rm -rf .next`
- Reinstall dependencies: `rm -rf node_modules && npm install`
- Check for symlink issues (phpmyadmin)

**Module Not Switching**
- Clear browser cache and localStorage
- Check Redux store for `selectedModule`
- Verify module data in API response

## 📚 Documentation

Additional documentation files:
- `BEAUTY_MODULE_ANALYSIS.md`: Beauty module analysis
- `BEAUTY_MODULE_FIXES.md`: Beauty module fixes
- `REACT_BEAUTY_MODULE_ALIGNMENT_CHANGES.md`: Alignment changes

## 📄 License

[Add your license information here]

## 👥 Team

[Add team information here]

## 🔗 Links

- [API Documentation](link-to-api-docs)
- [Design System](link-to-design-system)
- [Project Wiki](link-to-wiki)

---

Built with ❤️ using Next.js, React, and Material-UI
