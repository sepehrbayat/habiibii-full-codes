# Profile Split Error - FIXED ✅

## Problem

```
TypeError: Cannot read properties of undefined (reading 'split')
at profile-aac1ac3dee747e19.js:1:9209
```

## Root Cause

در `ProfileTab.js` از `page.split("?")` استفاده می‌شود، اما `page` ممکن است `undefined` باشد.

## Fix Applied

فایل `/var/www/6ammart-react/src/components/user-information/ProfileTab.js` اصلاح شد:

**Before:**
```javascript
page={page.split("?")[0]}
item?.name === page.split("?")[0] ? "600" : "400"
```

**After:**
```javascript
page={page?.split("?")[0]}
item?.name === page?.split("?")[0] ? "600" : "400"
```

## Status

✅ **FIXED** - React app باید restart شود تا تغییرات اعمال شود.

## Next Steps

1. React app restart شد
2. Browser را refresh کنید (Ctrl+F5)
3. خطا باید دیگر نمایش داده نشود

## Note

اگر خطا هنوز نمایش داده می‌شود:
- Browser cache را clear کنید
- Hard refresh کنید (Ctrl+Shift+R)
- یا در Incognito mode تست کنید

