# Beauty Booking Module - Admin Help Documentation
# مستندات راهنمای ادمین ماژول رزرو زیبایی

## Table of Contents
## فهرست مطالب

1. [Salon Management](#salon-management)
2. [Commission Configuration](#commission-configuration)
3. [Subscription Management](#subscription-management)
4. [Report Generation](#report-generation)
5. [Review Moderation](#review-moderation)

---

## Salon Management
## مدیریت سالن

### Approving Salons
### تأیید سالن‌ها

1. Navigate to **Beauty Booking > Salons**
2. Find the salon with status "Pending"
3. Click **View** to see salon details
4. Review:
   - Business license and documents
   - Working hours
   - Service categories
   - Staff information
5. Click **Approve** to approve or **Reject** to reject
6. If rejecting, provide rejection notes explaining the reason

### Rejecting Salons
### رد سالن‌ها

1. Navigate to **Beauty Booking > Salons**
2. Find the salon you want to reject
3. Click **View** then **Reject**
4. Enter rejection notes (required)
5. Click **Confirm Rejection**

**Note:** Rejected salons can be resubmitted by vendors after addressing the issues.

### Featured Salons
### سالن‌های ویژه

To feature a salon:

1. Navigate to **Beauty Booking > Salons**
2. Find the salon
3. Click **View**
4. Toggle **Featured** status
5. Featured salons appear at the top of search results

**Note:** Featured status can also be purchased by vendors through subscriptions.

---

## Commission Configuration
## پیکربندی کمیسیون

### Setting Up Commission Rules
### تنظیم قوانین کمیسیون

1. Navigate to **Beauty Booking > Commission Settings**
2. Click **Add New Commission Setting**
3. Configure:
   - **Commission Type**: Variable, Fixed, or Tiered
   - **Service Category**: Select category (or "All Categories")
   - **Salon Level**: Select level (or "All Levels")
   - **Commission Percentage**: Enter percentage (5-20%)
   - **Minimum Commission**: Minimum amount (optional)
   - **Maximum Commission**: Maximum amount (optional)
4. Click **Save**

### Commission Types
### انواع کمیسیون

- **Variable Commission**: Percentage-based commission (default)
- **Fixed Commission**: Fixed amount per booking
- **Tiered Commission**: Different rates based on booking volume

### Priority Order
### ترتیب اولویت

Commission settings are applied in this order:
1. Category + Salon Level specific
2. Category specific
3. Salon Level specific
4. Default commission

---

## Subscription Management
## مدیریت اشتراک‌ها

### Viewing Active Subscriptions
### مشاهده اشتراک‌های فعال

1. Navigate to **Beauty Booking > Subscriptions**
2. View list of active subscriptions:
   - Featured Listing subscriptions
   - Boost Ads subscriptions
   - Banner Ads subscriptions
   - Dashboard subscriptions

### Managing Banner Ads
### مدیریت تبلیغات بنر

1. Navigate to **Beauty Booking > Subscriptions > Banner Ads**
2. View active banner ads by position:
   - Homepage banners
   - Category page banners
   - Search results banners
3. Click **View** to see banner details
4. Click **Deactivate** to remove banner (if needed)

### Subscription Expiry
### انقضای اشتراک

- Subscriptions automatically expire on end_date
- Vendors receive notifications 7 days before expiry
- Expired subscriptions are automatically deactivated
- Badges (Featured) are automatically removed when subscription expires

---

## Report Generation
## تولید گزارش

### Financial Reports
### گزارش‌های مالی

1. Navigate to **Beauty Booking > Reports > Financial**
2. Select date range
3. Choose filters:
   - Salon
   - Service category
   - Booking status
4. Click **Generate Report**
5. View:
   - Total revenue
   - Commission breakdown
   - Service fees
   - Net payout

### Monthly Summary
### خلاصه ماهانه

1. Navigate to **Beauty Booking > Reports > Monthly Summary**
2. Select month and year
3. Click **Generate**
4. View:
   - Total bookings
   - Revenue by model (all 10 revenue streams)
   - Top performing salons
   - Commission collected

### Package Usage Report
### گزارش استفاده از پکیج

1. Navigate to **Beauty Booking > Reports > Package Usage**
2. View statistics:
   - Packages sold
   - Sessions redeemed
   - Expired packages
   - Revenue from packages

### Loyalty Statistics
### آمار وفاداری

1. Navigate to **Beauty Booking > Reports > Loyalty Stats**
2. View:
   - Points issued
   - Points redeemed
   - Active campaigns
   - Campaign performance

### Top Rated Salons (Monthly)
### سالن‌های برتر ماهانه

1. Navigate to **Beauty Booking > Reports > Top Rated Monthly**
2. View list of top-rated salons for the selected month
3. Criteria:
   - Rating > 4.5
   - Minimum 50 bookings
   - Active status

### Trending Clinics
### کلینیک‌های ترند

1. Navigate to **Beauty Booking > Reports > Trending Clinics**
2. View clinics with:
   - High booking growth
   - Positive rating trends
   - Increased activity

### Revenue Breakdown
### تفکیک درآمد

1. Navigate to **Beauty Booking > Reports > Revenue Breakdown**
2. View revenue by model:
   - Variable Commission
   - Subscription Revenue
   - Advertising Revenue
   - Service Fees
   - Package Sales
   - Cancellation Fees
   - Consultation Fees
   - Cross-selling Revenue
   - Retail Sales
   - Gift Card Sales
   - Loyalty Revenue

---

## Review Moderation
## مدیریت نظرات

### Approving Reviews
### تأیید نظرات

1. Navigate to **Beauty Booking > Reviews**
2. Find reviews with status "Pending"
3. Click **View** to see review details
4. Review:
   - Rating
   - Comment
   - Attachments (if any)
   - Booking details
5. Click **Approve** or **Reject**

### Rejecting Reviews
### رد نظرات

1. Navigate to **Beauty Booking > Reviews**
2. Find the review
3. Click **Reject**
4. Enter rejection reason (optional)
5. Click **Confirm**

**Note:** Rejected reviews are not displayed publicly but are stored for records.

### Review Guidelines
### دستورالعمل‌های بررسی

Reviews should be rejected if they:
- Contain inappropriate language
- Are spam or fake
- Violate terms of service
- Are unrelated to the service

---

## Badge Management
## مدیریت نشان‌ها

### Badge Types
### انواع نشان

- **Top Rated**: Automatically awarded (rating > 4.5, 50+ bookings)
- **Featured**: Awarded when subscription is active
- **Verified**: Manually awarded by admin

### Manual Badge Assignment
### تخصیص دستی نشان

1. Navigate to **Beauty Booking > Salons**
2. Find the salon
3. Click **View**
4. Go to **Badges** tab
5. Click **Assign Badge**
6. Select badge type
7. Set expiry date (optional)
8. Click **Save**

### Badge Criteria
### معیارهای نشان

**Top Rated:**
- Average rating > 4.5
- Minimum 50 bookings
- Active status

**Featured:**
- Active featured listing subscription

**Verified:**
- Admin approval
- Valid business license
- Complete profile

---

## Booking Management
## مدیریت رزرو

### Viewing Bookings
### مشاهده رزروها

1. Navigate to **Beauty Booking > Bookings**
2. Use filters:
   - Status (pending, confirmed, completed, cancelled)
   - Date range
   - Salon
   - Customer
3. Click **View** to see booking details

### Processing Refunds
### پردازش بازپرداخت

1. Navigate to **Beauty Booking > Bookings**
2. Find the booking
3. Click **View** then **Refund**
4. Enter:
   - Refund amount
   - Refund reason
5. Click **Process Refund**

**Note:** Refunds are processed immediately and cannot be undone.

### Force Cancellation
### لغو اجباری

1. Navigate to **Beauty Booking > Bookings**
2. Find the booking
3. Click **View** then **Force Cancel**
4. Enter cancellation reason
5. Click **Confirm**

**Note:** Force cancellation is only used in exceptional circumstances.

---

## Tips & Best Practices
## نکات و بهترین روش‌ها

### Regular Tasks
### وظایف منظم

- **Daily**: Review pending salon approvals
- **Daily**: Moderate pending reviews
- **Weekly**: Review financial reports
- **Monthly**: Generate monthly summary reports
- **Monthly**: Review top-rated salons list

### Commission Optimization
### بهینه‌سازی کمیسیون

- Set competitive commission rates
- Use tiered commissions for high-volume salons
- Review commission settings quarterly
- Monitor commission trends in reports

### Quality Control
### کنترل کیفیت

- Verify salon documents before approval
- Review suspicious reviews
- Monitor cancellation rates
- Check for duplicate salons

---

## Support
## پشتیبانی

For additional help or questions:
- Contact system administrator
- Review module documentation
- Check error logs in system logs

