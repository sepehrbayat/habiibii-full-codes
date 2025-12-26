# 🔴 اشکالات منطقی در تحلیل Promotion Management

## ❌ اشکالات پیدا شده

### 1. ❌ Coupon - `salon_wise` پشتیبانی نمی‌شود

**مشکل:**
- در تحلیل نوشتم که `salon_wise` برای ماژول زیبایی پشتیبانی می‌شود
- اما در `CouponLogic::is_valide()` هیچ منطق خاصی برای `salon_wise` وجود ندارد!
- فقط `store_wise` و `zone_wise` پشتیبانی می‌شوند

**کد واقعی:**
```php
// backend/app/CentralLogics/coupon.php
if($coupon->coupon_type == 'store_wise' && !in_array($store_id, json_decode($coupon->data, true)))
{  
    return 404;   
}
else if($coupon->coupon_type == 'zone_wise')
{
    // منطق zone_wise
}
// ❌ هیچ else if برای salon_wise وجود ندارد!
```

**راه حل:**
- باید منطق `salon_wise` به `CouponLogic::is_valide()` اضافه شود
- یا در ماژول زیبایی، `salon_wise` به `store_wise` تبدیل شود (چون salon ها store_id دارند)

---

### 2. ❌ Flash Sale - Scope Running فقط تاریخ را چک می‌کند

**مشکل:**
- در تحلیل نوشتم که Flash Sale هم تاریخ و هم زمان را چک می‌کند
- اما در کد واقعی فقط تاریخ را چک می‌کند!

**کد واقعی:**
```php
// backend/app/Models/FlashSale.php
public function scopeRunning($query)
{
    return $query->where('start_date','<=',date('Y-m-d H:i:s'))
                 ->where('end_date','>=',date('Y-m-d H:i:s'));
    // ❌ فقط تاریخ را چک می‌کند، start_time و end_time را چک نمی‌کند!
}
```

**مقایسه با Campaign:**
```php
// backend/app/Models/Campaign.php
public function scopeRunning($query)
{
    return $query->where(function($q){
            $q->whereDate('end_date', '>=', date('Y-m-d'))->orWhereNull('end_date');
        })->where(function($q){
            $q->whereDate('start_date', '<=', date('Y-m-d'))->orWhereNull('start_date');
        })->where(function($q){
            $q->whereTime('start_time', '<=', date('H:i:s'))->orWhereNull('start_time');
        })->where(function($q){
            $q->whereTime('end_time', '>=', date('H:i:s'))->orWhereNull('end_time');
        });
    // ✅ هم تاریخ و هم زمان را چک می‌کند
}
```

**راه حل:**
- باید scope Running در FlashSale به‌روزرسانی شود تا start_time و end_time را هم چک کند
- یا در مستندات تصحیح شود که Flash Sale فقط تاریخ را چک می‌کند

---

### 3. ⚠️ Flash Sale - Discount Percentages توضیح نادرست

**مشکل:**
- در تحلیل نوشتم که `vendor_discount_percentage` و `admin_discount_percentage` برای تقسیم تخفیف بین Admin و Vendor است
- این درست است اما توضیح کامل نیست

**کد واقعی:**
```php
// backend/app/CentralLogics/helpers.php
return [
    'discount_type'=>'flash_sale',
    'discount_amount'=> $price_discount,  // کل تخفیف
    'admin_discount_amount'=> ($price_discount*$running_flash_sale->flashSale->admin_discount_percentage)/100,
    'vendor_discount_amount'=> ($price_discount*$running_flash_sale->flashSale->vendor_discount_percentage)/100,
];
```

**توضیح صحیح:**
- `discount_amount`: کل مبلغ تخفیف که به مشتری داده می‌شود
- `admin_discount_percentage`: درصدی از `discount_amount` که Admin باید پرداخت کند
- `vendor_discount_percentage`: درصدی از `discount_amount` که Vendor باید پرداخت کند
- این برای تقسیم هزینه تخفیف بین Admin و Vendor است، نه برای محاسبه تخفیف

---

### 4. ❌ Coupon - Limit توضیح نادرست

**مشکل:**
- در تحلیل نوشتم که `limit` برای تعداد استفاده مجاز است
- اما در کد واقعی، `limit` برای تعداد استفاده توسط یک کاربر خاص است!

**کد واقعی:**
```php
// backend/app/CentralLogics/coupon.php
if ($coupon['limit'] == null) {
    return 200;
} else {
    $total = Order::where(['user_id' => $user_id, 'coupon_code' => $coupon['code']])->count();
    // ❌ فقط سفارش‌های این کاربر را می‌شمارد
    if ($total < $coupon['limit']) {
        return 200;
    }else{
        return 406;//Limite orer
    }
}
```

**توضیح صحیح:**
- `limit`: تعداد دفعاتی که یک کاربر خاص می‌تواند از این کوپن استفاده کند
- نه تعداد کل استفاده توسط همه کاربران!

---

### 5. ⚠️ Flash Sale - Available Stock توضیح ناقص

**مشکل:**
- در تحلیل نوشتم که با هر خرید، `sold` افزایش و `available_stock` کاهش می‌یابد
- اما در کد واقعی، این منطق خودکار نیست!

**کد واقعی:**
```php
// backend/app/Http/Controllers/Admin/FlashSaleController.php
$flash_sale->stock = $request->stock;
$flash_sale->available_stock = $request->stock;  // فقط در زمان ایجاد تنظیم می‌شود
$flash_sale->sold = 0;  // پیش‌فرض 0
```

**نکته:**
- باید بررسی شود که آیا در زمان ثبت سفارش، `sold` و `available_stock` به‌روزرسانی می‌شوند یا نه
- اگر نه، باید این منطق اضافه شود

---

### 6. ⚠️ Other Banner - منطق Parcel

**مشکل:**
- در تحلیل نوشتم که همه ماژول‌ها از `firstOrNew` استفاده می‌کنند
- اما برای Parcel از `new ModuleWiseBanner()` استفاده می‌شود!

**کد واقعی:**
```php
// backend/app/Http/Controllers/Admin/OtherBannerController.php
if($module_type == 'parcel'){
    $banner = new ModuleWiseBanner();  // ❌ همیشه جدید می‌سازد
    $banner->module_id = $module_id;
    $banner->key = $request->key;
    $banner->type = 'promotional_banner';
    $banner->value = Helpers::upload('promotional_banner/', 'png', $request->file('image'));
    $banner->save();
} else {
    $banner = ModuleWiseBanner::firstOrNew([  // ✅ برای بقیه firstOrNew
        'module_id' => $module_id,
        'key' => $request->key,
        'type' => 'promotional_banner',
    ]);
}
```

**نکته:**
- این یک تفاوت منطقی است که باید در مستندات ذکر شود

---

### 7. ⚠️ Coupon - First Order Logic

**مشکل:**
- در تحلیل نوشتم که `first_order` فقط برای اولین سفارش است
- اما در کد واقعی، بررسی می‌کند که تعداد سفارش‌های کاربر کمتر از `limit` باشد!

**کد واقعی:**
```php
// backend/app/CentralLogics/coupon.php
else if($coupon->coupon_type == 'first_order')
{
    $total = Order::where(['user_id' => $user_id])->count();
    if ($total < $coupon['limit']) {  // ❌ نه فقط اولین سفارش!
        return 200;
    }else{
        return 406;//Limite orer
    }
}
```

**توضیح صحیح:**
- `first_order` با `limit` ترکیب می‌شود
- اگر `limit = 1` باشد، فقط برای اولین سفارش است
- اگر `limit > 1` باشد، برای چند سفارش اول است

---

## ✅ تصحیحات انجام شده

1. **✅ Coupon Logic**: منطق `salon_wise` به `CouponLogic::is_valide()` و `is_valid_for_guest()` اضافه شد
   - salon_ids به store_ids تبدیل می‌شود
   - از `addon_published_status('BeautyBooking')` برای بررسی وجود ماژول استفاده می‌شود
2. **✅ Flash Sale Running Scope**: مستندات تصحیح شد
   - Flash Sale از `datetime` استفاده می‌کند (نه `date` + `time` جداگانه)
   - scope Running درست است و تاریخ و زمان کلی را چک می‌کند
3. **✅ مستندات**: توضیحات `limit`, `first_order`, `discount_percentages`, `available_stock`, `Other Banner` تصحیح شد
4. **✅ Flash Sale Stock**: تایید شد که `ProductLogic::update_flash_stock()` وجود دارد و درست کار می‌کند

---

## 📝 خلاصه اشکالات

| # | بخش | مشکل | شدت |
|---|-----|------|-----|
| 1 | Coupon | `salon_wise` پشتیبانی نمی‌شود | 🔴 Critical |
| 2 | Flash Sale | Scope Running فقط تاریخ را چک می‌کند | 🟡 Medium |
| 3 | Flash Sale | توضیح discount percentages ناقص | 🟢 Low |
| 4 | Coupon | توضیح limit نادرست | 🟡 Medium |
| 5 | Flash Sale | منطق available_stock ناقص | 🟡 Medium |
| 6 | Other Banner | تفاوت منطق Parcel | 🟢 Low |
| 7 | Coupon | توضیح first_order نادرست | 🟡 Medium |

