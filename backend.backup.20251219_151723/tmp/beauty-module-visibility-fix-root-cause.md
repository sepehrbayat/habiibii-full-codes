# Beauty Module Visibility Issue - Root Cause & Fix

**Date:** 2025-01-28  
**Issue:** Beauty module not visible in admin dashboard module switcher dropdown  
**Status:** ✅ FIXED

## Root Cause

The `addon_published_status()` function in `app/helpers.php` was using a **relative path** to load module info files:

```php
$full_data = include("Modules/{$module_name}/Addon/info.php");
```

### The Problem

1. **In CLI context (tinker, artisan commands):**
   - Current working directory: `/home/sepehr/Projects/6ammart-laravel/`
   - Relative path works: `Modules/BeautyBooking/Addon/info.php` ✅

2. **In Web request context:**
   - Current working directory: `/home/sepehr/Projects/6ammart-laravel/public/`
   - Relative path fails: `public/Modules/BeautyBooking/Addon/info.php` ❌ (doesn't exist)
   - Function returns `0` instead of `1`
   - Beauty module gets filtered out by `shouldShow` logic

### Impact

- `addon_published_status('BeautyBooking')` returned `0` in web requests
- `shouldShow` logic: `$shouldShow = addon_published_status('BeautyBooking') == 1;` evaluated to `false`
- Beauty module was filtered out before rendering
- Only grocery module appeared in the dropdown

## Solution

Changed `addon_published_status()` to use `base_path()` for absolute path resolution:

```php
function addon_published_status($module_name)
{
    $is_published = 0;
    try {
        // Use base_path() to ensure correct path regardless of current working directory
        // استفاده از base_path() برای اطمینان از مسیر صحیح بدون توجه به دایرکتوری فعلی
        $info_path = base_path("Modules/{$module_name}/Addon/info.php");
        if (file_exists($info_path)) {
            $full_data = include($info_path);
            $is_published = $full_data['is_published'] == 1 ? 1 : 0;
        }
        return $is_published;
    } catch (\Exception $exception) {
        return 0;
    }
}
```

## Files Modified

1. **`app/helpers.php`** (line 243-257)
   - Changed from relative path to `base_path()` absolute path
   - Added `file_exists()` check before including

## Verification

After fix:
- ✅ `addon_published_status('BeautyBooking')` returns `1` in web requests
- ✅ Beauty module appears in HTML
- ✅ Both modules (grocery + beauty) visible in dropdown

## Test Route

Created test route: `http://localhost:8000/test-beauty-module`
- Verifies module query results
- Checks if Beauty module is in rendered HTML
- Shows `addon_published_status()` result

## Lessons Learned

1. **Always use absolute paths in helper functions** that may be called from different contexts (CLI vs Web)
2. **Use `base_path()`** for paths relative to project root
3. **Test functions in both CLI and Web contexts** - they may behave differently
4. **Current working directory differs** between:
   - CLI: Project root
   - Web: `public/` directory

## Related Issues

- Similar issue could affect other functions using relative paths
- Check other helper functions for similar patterns
- Consider using `base_path()`, `app_path()`, `resource_path()`, etc. consistently

## Diagnostic Tools Created

1. **Server-side test:** `tmp/advanced-beauty-module-diagnostic.php`
   - Comprehensive PHP diagnostic script
   - Tests database, queries, view rendering, routes

2. **Browser test:** `tmp/browser-console-test.js`
   - JavaScript diagnostic for browser console
   - Checks DOM, HTML source, CSS visibility

3. **Test route:** `routes/test-beauty-module.php`
   - Web endpoint to verify server rendering
   - Returns JSON with diagnostic information

