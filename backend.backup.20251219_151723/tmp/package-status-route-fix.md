# Package Status Route Fix - BeautyBooking Admin

## Issue Summary

The BeautyBooking admin package list page was throwing an error:

```
Route [admin.beautybooking.package.status] not defined.
```

**Error Location:** Line 128 in `Modules/BeautyBooking/Resources/views/admin/package/index.blade.php`

**Root Cause:** The view was trying to use a status toggle route that didn't exist in the routes file.

## Root Cause

The package index view contains a status toggle checkbox that uses:
```blade
data-url="{{route('admin.beautybooking.package.status',[$package->id])}}"
```

However, the route `admin.beautybooking.package.status` was not defined in the routes file, and the controller didn't have a `status()` method to handle the toggle.

## Solution

### 1. Added Status Method to Controller

Added `status()` method to `BeautyPackageController` that:
- Finds the package by ID
- Toggles the status (active/inactive)
- Shows success message
- Returns back to previous page

### 2. Added Route

Added route in `Modules/BeautyBooking/Routes/web/admin/admin.php`:
```php
Route::get('status/{id}', [BeautyPackageController::class, 'status'])->name('status');
```

Full route name: `admin.beautybooking.package.status`

## Implementation

### Controller Method

```php
/**
 * Toggle package status
 * تغییر وضعیت پکیج
 *
 * @param int $id
 * @return RedirectResponse
 */
public function status(int $id): RedirectResponse
{
    $package = $this->package->findOrFail($id);
    $package->update(['status' => !$package->status]);
    
    Toastr::success(translate('messages.status_updated_successfully'));
    return back();
}
```

### Route

```php
Route::group(['prefix' => 'package', 'as' => 'package.'], function () {
    Route::get('list', [BeautyPackageController::class, 'list'])->name('list');
    Route::get('view/{id}', [BeautyPackageController::class, 'view'])->name('view');
    Route::get('status/{id}', [BeautyPackageController::class, 'status'])->name('status'); // Added
    Route::get('export', [BeautyPackageController::class, 'export'])->name('export');
});
```

## Files Modified

1. **Modules/BeautyBooking/Http/Controllers/Web/Admin/BeautyPackageController.php**
   - Added `status()` method to toggle package status

2. **Modules/BeautyBooking/Routes/web/admin/admin.php**
   - Added `status/{id}` GET route for package status toggle

## Pattern Consistency

This follows the same pattern used in other BeautyBooking admin controllers:
- `BeautyServiceController::status()`
- `BeautyStaffController::status()`
- `BeautyRetailController::status()`
- `BeautyLoyaltyController::status()`
- `BeautyCommissionController::status()`

## Benefits

- ✅ Status toggle now works correctly
- ✅ No more route not found errors
- ✅ Consistent with other admin controllers
- ✅ Simple and maintainable code

## Date

2025-11-30

## Status

✅ **RESOLVED** - Package status toggle route added and working

