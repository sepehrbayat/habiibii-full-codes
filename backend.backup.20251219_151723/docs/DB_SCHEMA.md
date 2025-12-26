# Database Schema Reference (6amMart)

> Snapshot of current schema after recent migrations (Beauty + core). Generate `schema.sql` via `mysqldump` for authoritative structure if needed.

## Quick Dump (authoritative)
- Full structure:  
  `mysqldump -uroot -prootpassword --no-data multi_food_db > schema.sql`
- Core tables:  
  `mysqldump -uroot -prootpassword --no-data multi_food_db modules zones stores store_modules store_schedule items categories banners discounts coupons campaigns campaign_store orders users customer_addresses reviews > schema-core.sql`
- Generated dump (current):  
  `/home/sepehr/Projects/6ammart-laravel/docs/schema.sql`

## Key Tables (columns & FKs)

### modules
- `id` PK, `module_name`, `module_type`, `status`, `stores_count`, `all_zone_service`, `theme_id`, timestamps

### zones
- `id` PK, `name`, `display_name`, `coordinates` (text/json), `status`, `cash_on_delivery`, `digital_payment`, `offline_payment`, `increased_delivery_fee`, `increased_delivery_fee_status`, timestamps

### stores
- `id` PK, `vendor_id`, `zone_id` FK zones, `module_id` FK modules, `name`, `phone`, `email`, `address`, `latitude`, `longitude`, `schedule_order`, `status`, `active`, `delivery`, `take_away`, `item_section`, `tax`, `store_business_model`, `slug`, `featured`, `per_km_shipping_charge`, `maximum_shipping_charge`, `prescription_order`, `cutlery`, `meta_title/description/image`, `announcement`, `announcement_message`, `comment`, `tin*`, `package_id`, timestamps

### store_modules (pivot)
- `id`, `store_id` FK stores, `module_id` FK modules, `status`, unique(store_id, module_id), timestamps

### store_schedule
- `id`, `store_id` FK stores cascade, `day`, `opening_time`, `closing_time`, `status`, timestamps

### items
- `id` PK, `store_id` FK stores, `module_id` FK modules, `category_id`, `category_ids` (json), `name`, `slug`, `price` (decimal), `discount` (decimal), `status` (tinyint), `is_approved`, `is_halal`, `recommended`, `organic`, `maximum_cart_quantity`, `order_count` (int), `images` (json), `unit_id`, timestamps

### categories
- `id` PK, `parent_id`, `name`, `image`, `priority` (int), timestamps

### banners
- `id` PK, `title`, `image`, `data` (text/json), `type`, `zone_id` FK zones, `module_id` FK modules, `store_id` FK stores, `item_id` FK items, `status`, `created_by`, `start_date/end_date`, `start_time/end_time`, timestamps

### discounts
- `id` PK, `store_id` FK stores, `module_id` FK modules, `discount_type`, `discount` (decimal), `start_date/end_date`, `start_time/end_time`, `status`, timestamps

### coupons
- `id` PK, `code` unique, `module_id` FK modules, `status`, `start_date`, `expire_date`, timestamps

### campaigns
- `id` PK, `title`, `module_id` FK modules, `status`, `start_date/end_date`, `start_time/end_time`, timestamps

### campaign_store (pivot)
- `id`, `campaign_id` FK campaigns cascade, `store_id` FK stores cascade, unique(campaign_id, store_id), timestamps

### orders
- `id` PK, `user_id` FK users, `store_id` FK stores, `order_status` (default pending), `order_type` (default delivery), `is_guest`, timestamps + سایر فیلدهای مالی/تکمیلی (tax, discount, ... موجود در DB)

### users
- `id` PK, `f_name`, `l_name`, `email` unique, `phone`, `password`, `status`, `wallet_balance`, `loyalty_point`, `ref_code` unique, `module_ids`, `login_medium`, `cm_firebase_token`, `zone_id` FK zones, `is_email_verified`, `is_from_pos`, timestamps

### customer_addresses
- `id` PK, `user_id` FK users cascade, `contact_person_name/number`, `address_type`, `address`, `floor`, `road`, `house`, `longitude`, `latitude`, `zone_id` FK zones set null, timestamps

### reviews
- `id` PK, `user_id`, `store_id`, `item_id`, `rating`, `comment`, `review_id`, `reply`, `replied_at`, timestamps

### store_subscriptions (موجود از قبل)
- شامل `store_id`, `status`, `max_order`, ... (برای featured/commission)

### flash_sales / flash_sale_items (در صورت فعال)
- موجود در dump کامل

### campaign/discount/flash روابط
- طبق FKs بالا؛ جهت تطبیق فرانت از `schema-core.sql` استفاده کنید.

## یادداشت برای فرانت
- لیست/کارت آیتم: items.{id,name,slug,price,discount,status,store_id,module_id,images,order_count}, store.{status,zone_id,module_id,store_business_model}
- فیلتر زون/ماژول: zones و module_zone (در dump کامل).
- بنر/کمپین/تخفیف/کوپن: tables banners, campaigns+campaign_store, discounts, coupons.
- سفارش: orders.{user_id,store_id,order_status,order_type,is_guest,…}
- کاربر: users.{login_medium,cm_firebase_token,zone_id,status}

> برای دقت ۱۰۰٪، فایل dump (`schema.sql`) را به‌عنوان مرجع نهایی در نظر بگیرید. این خلاصه برای نگاه سریع فرانت است.

---

## Full Table Catalog (purpose & hints)
هر جدول یک خط کوتاه برای کاربرد/وابستگی؛ برای ستون‌ها به `schema.sql` رجوع کنید.

- add_ons: افزودنی‌های آیتم
- addon_categories: دسته‌بندی افزودنی‌ها
- addon_settings: تنظیمات افزودنی/یکپارچه‌سازی
- admin_features: سطح دسترسی و ویژگی‌های پنل ادمین
- admin_promotional_banners: بنرهای تبلیغی مخصوص ادمین
- admin_roles: نقش‌های ادمین
- admin_special_criterias: معیارهای ویژه در پنل ادمین
- admin_testimonials: نظرات/تستیمونیال‌های پنل ادمین
- admin_wallets: کیف‌پول ادمین
- admins: اکانت‌های ادمین
- advertisements: تبلیغات عمومی
- allergies / allergy_item / allergy_item_campaign: آلرژی و ارتباط با آیتم/کمپین
- automated_messages: پیام‌های خودکار
- banners: بنرهای فرانت (زون/ماژول/استور/آیتم) — تازه ایجاد شده
- beauty_* (badges, bookings, calendar_blocks, commission_settings, gift_cards, loyalty_campaigns, loyalty_points, monthly_reports, package_usages, packages, retail_orders, retail_products, reviews, salons, service_categories, service_relations, service_staff, services, staff, subscriptions, transactions): جداول ماژول BeautyBooking
- brands: برند محصولات
- business_settings: تنظیمات سراسری (currency/default_location/…)
- cache, cache_locks: کش لاراول
- campaign_store: پیوت کمپین به استور — تازه ایجاد شده
- campaigns: کمپین‌ها — تازه ایجاد شده
- carts: سبد خرید
- cash_back* (histories): کش‌بک
- categories: دسته‌بندی‌ها — image/priority اضافه شد
- common_conditions: شرایط عمومی
- contacts: تماس‌ها
- conversations, messages: چت داخلی
- coupons: کوپن‌ها — تازه ایجاد شده
- currencies: ارزها — تازه ایجاد شده
- customer_addresses: آدرس مشتری — تازه ایجاد شده (ساختار کامل)
- d_m_vehicles: وسایل نقلیه پیک
- data_settings: تنظیمات داده
- disbursement* (details/withdrawal_methods/…): تسویه
- discounts: تخفیف‌ها — تازه ایجاد شده
- ecommerce_item_details / pharmacy_item_details: جزئیات تجارت/داروخانه
- email_templates: قالب ایمیل
- expenses: هزینه‌ها
- external_configurations: کانفیگ خارجی
- flash_sales, flash_sale_items: فلش‌سیل
- flutter_special_criterias: معیارهای ویژه Flutter
- generic_names, nutritions, item_generic_names, item_nutrition, item_tag, tags: مشخصات/برچسب مواد
- guests: مهمان‌ها
- item_campaigns (+ *_generic_names, *_nutrition): کمپین آیتم
- items: آیتم‌ها — store_id/module_id/category_ids/price/discount/status/order_count … تازه تکمیل شد
- jobs: جاب‌ها/صف
- loyalty_point_transactions: تراکنش امتیاز وفاداری
- migrations: مایگریشن‌ها
- module_wise_banners / module_wise_why_chooses: محتوا بر اساس ماژول
- module_zone: پیوت ماژول-زون
- modules: ماژول‌ها (غذا/دارو/بیوتی/…)
- newsletters: خبرنامه
- notification_messages / notification_settings: اعلان‌ها
- oauth_*: جداول پاسپورت OAuth
- offline_payment_methods / offline_payments: پرداخت آفلاین
- order_* (orders, order_details, order_payments, order_references, order_taxes, order_transactions): سفارش‌ها — order_status/order_type/store_id/user_id تازه اضافه شدند
- parcel_delivery_instructions: راهنمای تحویل
- partial_payments: پرداخت جزئی
- password_resets / phone_verifications: احراز هویت
- priority_lists: اولویت‌بندی
- react_testimonials: تستیمونیال
- recent_searches: جستجوهای اخیر
- refund_reasons / refunds: ریفاند
- rental_* (carts, cart_user_data, email_templates, wishlishes): ماژول Rental
- reviews: نظرات آیتم/استور
- social_media: شبکه‌های اجتماعی
- soft_credentials: کرنشیال نرم
- storages: مدیریت فایل/استوریج
- store_configs / store_notification_settings / store_wallets / store_subscriptions: پیکربندی/اعلان/کیف پول/اشتراک استور
- subscription_* (billing_and_refund_histories, packages, transactions): اشتراک‌ها
- surge_price* (dates/prices): افزایش قیمت
- system_tax_setups, tax_additional_setups, taxables, taxes: مالیات
- temp_products: محصولات موقت
- translations: ترجمه‌ها
- trip_* (details/transactions/vehicle_details): سفر/حمل
- trips: سفرها
- user_infos: اطلاعات کاربر
- users: کاربران — zone_id/login_medium/cm_firebase_token اضافه شد
- vehicle_* (brands/categories/drivers/identities/reviews/vehicles): ناوگان
- vendor_employees / vendors: فروشندگان و کارمندان
- wallet_* (bonuses/payments/transactions): کیف پول
- websockets_statistics_entries: آمار وب‌سوکت
- wishlists: لیست علاقه‌مندی
- withdrawal_methods: روش‌های برداشت
- zones: زون‌ها

---

## نحوه استفاده (نمای کلی فرانت)
- فهرست و نمایش آیتم: جدول `items` با ارتباط به `stores`، فیلتر روی `status/is_approved/module_id/zone_id`؛ قیمت/تخفیف/تصاویر/دسته‌ها.
- فیلتر زون/ماژول: `zones`, `module_zone`, `stores.module_id`, `items.module_id`, `banners.module_id`.
- بنر/پروموشن: `banners`, `campaigns` + `campaign_store`, `discounts`, `coupons`, `flash_sales` + `flash_sale_items`.
- سفارش: `orders` (user_id, store_id, order_status, order_type), جزئیات در `order_details`, پرداخت‌ها در `order_payments`, مالیات در `order_taxes`, تراکنش‌ها در `order_transactions`.
- کاربر و آدرس: `users` (login_medium, cm_firebase_token, zone_id), `customer_addresses` (geo/zone).
- چت: `conversations`, `messages`.
- وفاداری/کیف‌پول: `loyalty_point_transactions`, `wallet_transactions`, `wallet_payments`, `wallet_bonuses`.
- استور و زمان‌بندی: `stores`, `store_schedule`, `store_modules`, `store_subscriptions`, `store_wallets`.
- کمپین‌ها و تبلیغات: `campaigns`, `campaign_store`, `module_wise_banners`, `admin_promotional_banners`.
- ماژول بیوتی: همه‌ی `beauty_*` جداول (سالن/خدمات/کارکنان/رزرو/تراکنش/…).

> برای جزئیات ستون‌ها و انواع داده، از dump (`schema.sql`) یا `DESCRIBE <table>` استفاده کنید. این فایل یک نقشهٔ کامل جدول‌ها + کاربردشان است تا فرانت بتواند مپ کند.
