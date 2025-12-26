# Toastr addslashes() Null Deprecation Warning Fix

## Issue Summary

PHP 8.4+ deprecation warning was occurring:

```
addslashes(): Passing null to parameter #1 ($string) of type string is deprecated
```

**Error Location:** `vendor/brian2694/laravel-toastr/src/Toastr.php` on line 63

**Root Cause:** The Toastr library was calling `addslashes()` on potentially null values (`$message['title']`), which triggers deprecation warnings in PHP 8.4+ where `addslashes()` expects a string parameter.

## Root Cause

In the Toastr library's `message()` method, the code was:
```php
$title = addslashes($message['title']) ?: null;
```

When `$message['title']` is `null`, calling `addslashes(null)` causes a deprecation warning because PHP 8.4+ requires string parameters.

Similarly, `$message['message']` could also potentially be null in edge cases.

## Solution

### Fixed Toastr Library

Updated the `vendor/brian2694/laravel-toastr/src/Toastr.php` file to safely handle null values:

**Before:**
```php
$title = addslashes($message['title']) ?: null;

$script .= 'toastr.' . $message['type'] .
    '(\'' . addslashes($message['message']) .
    "','$title" .
    '\');';
```

**After:**
```php
// Safely handle null values to prevent PHP 8.4+ deprecation warnings
// مدیریت امن مقادیر null برای جلوگیری از هشدارهای deprecation در PHP 8.4+
$title = ($message['title'] !== null && $message['title'] !== '') 
    ? addslashes((string)$message['title']) 
    : '';

$messageText = ($message['message'] !== null) 
    ? addslashes((string)$message['message']) 
    : '';

$script .= 'toastr.' . $message['type'] .
    '(\'' . $messageText .
    "','" . $title .
    '\');';
```

### Changes Made

1. **Null Check Before addslashes()**: Added explicit null checks before calling `addslashes()` to prevent deprecation warnings
2. **Type Casting**: Cast values to string before passing to `addslashes()` to ensure type safety
3. **Empty String Fallback**: Use empty string instead of null when values are missing
4. **Message Field Protection**: Also added null check for `$message['message']` field

## Benefits

- ✅ No more PHP 8.4+ deprecation warnings
- ✅ Type-safe handling of null values
- ✅ Backward compatible (still works with existing code)
- ✅ Prevents potential issues if translate() returns null
- ✅ Cleaner JavaScript output (empty string instead of null)

## Technical Details

### Why This Happens

PHP 8.4+ has stricter type checking. Functions like `addslashes()` now explicitly require string parameters and will throw deprecation warnings if null is passed.

### The Fix

1. Check if value is not null before calling `addslashes()`
2. Cast to string explicitly using `(string)` for type safety
3. Use empty string as fallback instead of null
4. Apply same protection to both `title` and `message` fields

## Files Modified

1. **vendor/brian2694/laravel-toastr/src/Toastr.php**
   - Line 63-71: Added null checks and type casting
   - Added bilingual comments (Persian + English)

## Testing

- ✅ Syntax check passed
- ✅ No linter errors
- ✅ Backward compatible with existing Toastr usage
- ✅ Handles all edge cases (null title, null message, empty strings)

## Notes

- This is a vendor file modification. If the package is updated via composer, this change will be lost.
- Consider creating a patch file or forking the package for long-term maintenance.
- The fix is safe and doesn't change the behavior, only makes it compatible with PHP 8.4+.

## Date

2025-11-30

## Status

✅ **RESOLVED** - Deprecation warnings eliminated, Toastr library now PHP 8.4+ compatible

