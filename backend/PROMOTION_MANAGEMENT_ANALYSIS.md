# تحلیل کامل منطق و فانکشنالیتی بخش‌های Promotion Management

## 📋 فهرست بخش‌ها

1. [Other Banner (Promotional Banner)](#1-other-banner-promotional-banner)
2. [Banner](#2-banner)
3. [Campaign](#3-campaign)
4. [Coupon](#4-coupon)
5. [Notification](#5-notification)
6. [Flash Sale](#6-flash-sale)
7. [Advertisement](#7-advertisement)

---

## 1. Other Banner (Promotional Banner)

### 📦 محل ذخیره‌سازی
- **جدول**: `module_wise_banners`
- **Model**: `ModuleWiseBanner`
- **Controller**: `OtherBannerController`

### 🔧 منطق کار
1. **ذخیره‌سازی**:
   - داده‌ها در جدول `module_wise_banners` ذخیره می‌شوند
   - هر banner با `module_id`, `key`, `type`, `value` مشخص می‌شود
   - برای ماژول زیبایی: `type = 'promotional_banner'`, `key = 'best_reviewed_section_banner'`

2. **ساختار داده**:
   ```php
   // برای همه ماژول‌ها به جز Parcel
   ModuleWiseBanner::firstOrNew([
       'module_id' => $module_id,
       'key' => $request->key,  // مثلاً 'best_reviewed_section_banner'
       'type' => 'promotional_banner',
   ]);
   $banner->value = Helpers::upload('promotional_banner/', 'png', $request->file('image'));
   
   // برای ماژول Parcel از new ModuleWiseBanner() استفاده می‌شود
   ```

3. **ویژگی‌ها**:
   - پشتیبانی از چند زبانه (Translation)
   - ذخیره‌سازی در Storage (S3/Public)
   - فعال/غیرفعال کردن (status)
   - URL کامل خودکار (`value_full_url`)

4. **استفاده در Frontend**:
   - API: `/api/v1/other-banner/get-banners`
   - برای نمایش در صفحات React/Web استفاده می‌شود

---

## 2. Banner

### 📦 محل ذخیره‌سازی
- **جدول**: `banners`
- **Model**: `Banner`
- **Controller**: `BannerController` (Admin)

### 🔧 منطق کار
1. **ذخیره‌سازی**:
   - هر banner یک رکورد مستقل در جدول `banners` است
   - شامل: `title`, `type`, `image`, `status`, `zone_id`, `module_id`, `data` (store_id), `featured`

2. **انواع Banner**:
   - **Store Banner**: `data` = store_id (لینک به یک فروشگاه)
   - **Zone Banner**: `zone_id` مشخص (نمایش در یک منطقه)
   - **Featured Banner**: `featured = 1` (نمایش در صفحه اصلی)

3. **منطق نمایش**:
   - بر اساس `zone_id` و `module_id` فیلتر می‌شوند
   - Banner های featured اولویت دارند
   - Banner های فعال (`status = 1`) نمایش داده می‌شوند

4. **ارتباط با Campaign**:
   - Banner ها می‌توانند به Campaign متصل شوند (`join_campaign`)

---

## 3. Campaign

### 📦 محل ذخیره‌سازی
- **جدول**: `campaigns` (Basic Campaign)
- **جدول**: `item_campaigns` (Item Campaign)
- **Model**: `Campaign`, `ItemCampaign`
- **Controller**: `CampaignController`

### 🔧 منطق کار

#### Basic Campaign:
1. **هدف**: ایجاد کمپین برای فروشگاه‌ها
2. **ساختار**:
   - `title`, `description`, `image`
   - `start_date`, `end_date`, `start_time`, `end_time`
   - `module_id`, `status`
   - ارتباط با Store ها از طریق `campaign_store` (pivot table)

3. **منطق**:
   - Admin کمپین ایجاد می‌کند
   - Store ها می‌توانند به کمپین بپیوندند
   - Store ها باید تایید شوند (`campaign_status` در pivot)
   - کمپین در بازه زمانی مشخص فعال است

#### Item Campaign:
1. **هدف**: ایجاد کمپین برای محصولات خاص
2. **ساختار**:
   - مشابه Item اما با قیمت و تخفیف خاص
   - `price`, `discount`, `discount_type`
   - `start_date`, `end_date`, `start_time`, `end_time`
   - `stock`, `maximum_cart_quantity`

3. **منطق**:
   - Admin محصولات را با قیمت/تخفیف خاص در کمپین قرار می‌دهد
   - در زمان کمپین، قیمت کمپین اعمال می‌شود
   - محدودیت موجودی و تعداد خرید

4. **Scope Running**:
   ```php
   ->whereDate('end_date', '>=', date('Y-m-d'))
   ->whereDate('start_date', '<=', date('Y-m-d'))
   ->whereTime('start_time', '<=', date('H:i:s'))
   ->whereTime('end_time', '>=', date('H:i:s'))
   ```

---

## 4. Coupon

### 📦 محل ذخیره‌سازی
- **جدول**: `coupons`
- **Model**: `Coupon`
- **Controller**: `BeautyCouponController` (برای ماژول زیبایی)

### 🔧 منطق کار
1. **ساختار**:
   - `code`: کد کوپن (unique)
   - `title`, `discount`, `discount_type` (percent/amount)
   - `start_date`, `expire_date`
   - `min_purchase`, `max_discount`
   - `limit`: تعداد دفعاتی که یک کاربر خاص می‌تواند از این کوپن استفاده کند (نه تعداد کل استفاده)
   - `coupon_type`: default, first_order, free_delivery, zone_wise, salon_wise
   - `data`: JSON (zone_ids یا salon_ids)
   - `customer_id`: JSON (مشتری‌های مجاز)
   - `module_id`, `created_by`

2. **انواع Coupon**:
   - **Default**: برای همه قابل استفاده
   - **First Order**: برای چند سفارش اول (با `limit` مشخص می‌شود - اگر `limit = 1` باشد، فقط اولین سفارش)
   - **Free Delivery**: رایگان کردن هزینه ارسال
   - **Zone Wise**: فقط برای منطقه‌های خاص
   - **Salon Wise**: فقط برای سالن‌های خاص (در ماژول زیبایی - salon_ids به store_ids تبدیل می‌شود برای اعتبارسنجی)

3. **اعتبارسنجی** (در `CouponLogic::is_valide()`):
   - بررسی تاریخ (start_date, expire_date)
   - بررسی تعداد استفاده توسط کاربر (`total_uses < limit` - فقط سفارش‌های این کاربر شمرده می‌شود)
   - بررسی `min_purchase`
   - بررسی `max_discount`
   - بررسی `coupon_type` و `data`
   - برای `salon_wise`: salon_ids به store_ids تبدیل می‌شود (با استفاده از `BeautySalon::whereIn('id', $salon_ids)->pluck('store_id')`) و سپس اعتبارسنجی انجام می‌شود

4. **محاسبه تخفیف**:
   ```php
   if ($discount_type == 'percent') {
       $discount_amount = ($total / 100) * $discount;
       $discount_amount = min($discount_amount, $max_discount);
   } else {
       $discount_amount = $discount;
   }
   ```

---

## 5. Notification

### 📦 محل ذخیره‌سازی
- **جدول**: `notifications`
- **Model**: `Notification`
- **Controller**: `BeautyNotificationController` (برای ماژول زیبایی)

### 🔧 منطق کار
1. **ساختار**:
   - `title`, `description`, `image`
   - `status`, `tergat` (target), `zone_id`
   - پشتیبانی از چند زبانه

2. **ارسال**:
   - Push Notification به کاربران
   - فیلتر بر اساس `zone_id`
   - فیلتر بر اساس `tergat` (all, customer, vendor, etc.)

3. **ذخیره در User Notifications**:
   - هر notification برای کاربران در `user_notifications` ذخیره می‌شود
   - شامل `user_id`, `data` (JSON), `read_at`

4. **منطق ارسال**:
   - استفاده از `NotificationTrait`
   - ارسال به Firebase/OneSignal
   - ذخیره در دیتابیس برای تاریخچه

---

## 6. Flash Sale

### 📦 محل ذخیره‌سازی
- **جدول**: `flash_sales` (کمپین اصلی)
- **جدول**: `flash_sale_items` (محصولات در کمپین)
- **Model**: `FlashSale`, `FlashSaleItem`
- **Controller**: `FlashSaleController`

### 🔧 منطق کار
1. **ساختار Flash Sale**:
   - `title`, `image`
   - `start_date`, `end_date`
   - `is_publish`: انتشار/عدم انتشار
   - `vendor_discount_percentage`: درصدی از `discount_amount` که Vendor باید پرداخت کند (برای تقسیم هزینه تخفیف)
   - `admin_discount_percentage`: درصدی از `discount_amount` که Admin باید پرداخت کند (برای تقسیم هزینه تخفیف)
   - `module_id`

2. **ساختار Flash Sale Item**:
   - `flash_sale_id`, `item_id`
   - `stock`: موجودی اولیه
   - `sold`: تعداد فروخته شده
   - `available_stock`: موجودی باقیمانده
   - `discount_type`: percent, amount, current_active_discount
   - `discount`, `discount_amount`
   - `price`: قیمت نهایی (قیمت اصلی - تخفیف)

3. **منطق تخفیف**:
   - **percent**: `discount_amount = (item->price / 100) * discount`
   - **amount**: `discount_amount = discount`
   - **current_active_discount**: استفاده از تخفیف فعلی محصول
   - `price = item->price - discount_amount`

4. **منطق موجودی**:
   - `available_stock = stock - sold`
   - با هر خرید، `ProductLogic::update_flash_stock()` فراخوانی می‌شود که `sold` را افزایش و `available_stock` را کاهش می‌دهد
   - وقتی `available_stock = 0`، محصول از Flash Sale حذف می‌شود

5. **Scope Running**:
   ```php
   ->where('start_date', '<=', date('Y-m-d H:i:s'))
   ->where('end_date', '>=', date('Y-m-d H:i:s'))
   ->where('is_publish', 1)
   ```
   **نکته**: Flash Sale از `datetime` استفاده می‌کند (نه `date` + `time` جداگانه مثل Campaign). پس scope Running فقط تاریخ و زمان کلی را چک می‌کند، نه زمان روزانه (مثلاً هر روز از 10 صبح تا 6 عصر).

6. **استفاده در محاسبه قیمت**:
   - در `Helpers::product_discount_calculate()` اولویت با Flash Sale است
   - اگر محصول در Flash Sale فعال باشد، تخفیف Flash Sale اعمال می‌شود
   - `admin_discount_percentage` و `vendor_discount_percentage`: برای تقسیم هزینه تخفیف بین Admin و Vendor (نه برای محاسبه تخفیف)

---

## 7. Advertisement

### 📦 محل ذخیره‌سازی
- **جدول**: `advertisements`
- **Model**: `Advertisement`
- **Controller**: `AdvertisementController` (Admin/Vendor)

### 🔧 منطق کار
1. **ساختار**:
   - `store_id`: فروشگاه درخواست‌دهنده
   - `add_type`: store_promotion, banner, etc.
   - `title`, `description`, `cover_image`
   - `start_date`, `end_date`
   - `status`: pending, approved, denied, paused, expired
   - `priority`: اولویت نمایش (NULL = بدون اولویت)
   - `is_rating_active`, `is_review_active`
   - `is_paid`: پرداخت شده یا خیر
   - `created_by_id`, `created_by_type`: چه کسی ایجاد کرده (Vendor/VendorEmployee)

2. **فرآیند**:
   - **Vendor** درخواست تبلیغ می‌دهد (`status = pending`)
   - **Admin** درخواست را بررسی می‌کند
   - Admin می‌تواند: approve, deny, pause
   - Admin می‌تواند `priority` تنظیم کند

3. **منطق نمایش**:
   - فقط Advertisement های `approved` و `valid` نمایش داده می‌شوند
   - فیلتر بر اساس `zone_id` (از طریق store)
   - مرتب‌سازی بر اساس `priority` (NULL ها آخر)
   - Scope `valid`: `start_date <= now() AND end_date >= now() AND status = 'approved'`

4. **انواع Advertisement**:
   - **Store Promotion**: تبلیغ فروشگاه با ریتینگ و ریویو
   - **Banner**: بنر تبلیغاتی ساده

5. **API**:
   - `/api/v1/advertisement/get-adds`: دریافت Advertisement های معتبر
   - Cache برای 20 دقیقه
   - شامل اطلاعات store و reviews

---

## 🔗 ارتباطات بین بخش‌ها

1. **Banner ↔ Campaign**: Banner ها می‌توانند به Campaign متصل شوند
2. **Flash Sale ↔ Item**: Flash Sale روی محصولات اعمال می‌شود
3. **Coupon ↔ Order**: Coupon در زمان checkout اعمال می‌شود
4. **Advertisement ↔ Store**: Advertisement برای Store ها است
5. **Notification ↔ Zone**: Notification بر اساس Zone ارسال می‌شود

---

## 📊 خلاصه جدول‌ها

| بخش | جدول | کلید اصلی |
|-----|------|-----------|
| Other Banner | `module_wise_banners` | id (module_id + key + type) |
| Banner | `banners` | id |
| Campaign | `campaigns` | id |
| Item Campaign | `item_campaigns` | id |
| Coupon | `coupons` | id (code unique) |
| Notification | `notifications` | id |
| Flash Sale | `flash_sales` | id |
| Flash Sale Item | `flash_sale_items` | id (flash_sale_id + item_id) |
| Advertisement | `advertisements` | id |

---

## ✅ بررسی فانکشنالیتی در ماژول زیبایی

### Other Banner ✅
- View ایجاد شد: `beauty-index.blade.php`
- Controller: `OtherBannerController` (core)
- ذخیره‌سازی: `module_wise_banners` با `module_id` ماژول زیبایی

### Banner ✅
- Controller: `BannerController` (core)
- فیلتر بر اساس `module_id`
- کار می‌کند

### Campaign ✅
- Controller: `CampaignController` (core)
- فیلتر بر اساس `module_id`
- Basic Campaign و Item Campaign کار می‌کنند

### Coupon ✅
- Controller: `BeautyCouponController` (ماژول زیبایی)
- فیلتر بر اساس `module_id`
- پشتیبانی از `salon_wise` (مخصوص ماژول زیبایی)
- کار می‌کند

### Notification ✅
- Controller: `BeautyNotificationController` (ماژول زیبایی)
- جدول `notifications` ایجاد شد
- فیلتر بر اساس `module_id`
- کار می‌کند

### Flash Sale ✅
- Controller: `BeautyFlashSaleController` (ماژول زیبایی)
- فیلتر بر اساس `module_id`
- کار می‌کند

### Advertisement ✅
- Controller: `AdvertisementController` (core)
- فیلتر بر اساس `module_id`
- کار می‌کند

---

## 🎯 نتیجه‌گیری

همه بخش‌های Promotion Management در ماژول زیبایی:
- ✅ به درستی با `module_id` فیلتر می‌شوند
- ✅ منطق مشابه با ماژول‌های دیگر دارند
- ✅ فانکشنالیتی کامل دارند
- ✅ آماده استفاده هستند

