# Profile Split Error Fix

## Problem

```
TypeError: Cannot read properties of undefined (reading 'split')
at profile-aac1ac3dee747e19.js:1:9209
```

## Root Cause

در `ProfileTab.js` از `page.split("?")` استفاده می‌شود، اما `page` ممکن است `undefined` باشد.

## Location

`/var/www/6ammart-react/src/components/user-information/ProfileTab.js`

## Solution

باید null check اضافه شود:

```javascript
// Before (خطا می‌دهد):
page={page.split("?")[0]}

// After (درست):
page={page?.split("?")[0] || ""}
```

یا:

```javascript
// Before:
item?.name === page.split("?")[0] ? "600" : "400"

// After:
item?.name === (page?.split("?")[0] || "") ? "600" : "400"
```

## Temporary Workaround

این خطا **غیر بحرانی** است و فقط در console نمایش داده می‌شود. برنامه باید کار کند.

اگر می‌خواهید این خطا را fix کنید:

1. فایل `/var/www/6ammart-react/src/components/user-information/ProfileTab.js` را باز کنید
2. همه جاهایی که `page.split` استفاده شده، به `page?.split` تغییر دهید
3. React app را rebuild کنید: `npm run build` یا `npm run dev`

## Status

⚠️ **Non-Critical Error** - برنامه باید کار کند، فقط console error است.

