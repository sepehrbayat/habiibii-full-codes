# Blade Foreach Syntax Error Fix - BeautyBooking Salon Index

## Issue Summary

The BeautyBooking admin salon index page (`Modules/BeautyBooking/Resources/views/admin/salon/index.blade.php`) was throwing a Blade compilation error:

```
syntax error, unexpected token "endforeach", expecting end of file
```

**Error Location:** Line 273 in the view file

## Root Cause

The error was caused by **multi-line `@php()` shorthand directives with closures** that were confusing the Blade compiler. The shorthand `@php()` syntax is designed for single-line PHP code, but the view had multi-line closures that spanned multiple lines:

```blade
@php($active_salons = \Modules\BeautyBooking\Entities\BeautySalon::whereHas('store', function($query){
    return $query->where('status', 1);
})->where('verification_status', 1)->count())
```

This caused Blade to fail during compilation, leading to the "unexpected endforeach" error.

## Solution

Moved all statistics calculations from the view to the controller, following Laravel best practices:

### 1. Added Statistics Calculations to Controller

**File:** `Modules/BeautyBooking/Http/Controllers/Web/Admin/BeautySalonController.php`

Added calculations for:
- `$totalSalons` - Total number of salons
- `$activeSalons` - Active salons (verified with active store)
- `$inactiveSalons` - Inactive salons
- `$newlyJoinedSalons` - Salons created in last 30 days

### 2. Removed @php Blocks from View

**File:** `Modules/BeautyBooking/Resources/views/admin/salon/index.blade.php`

- Removed all multi-line `@php()` directives
- Removed `@php ... @endphp` blocks that contained complex queries
- Replaced with simple variable references: `{{$totalSalons ?? 0}}`

## Changes Made

### Controller Changes

```php
// Calculate statistics for display
$totalSalons = BeautySalon::count();
$activeSalons = BeautySalon::whereHas('store', function($query) {
    return $query->where('status', 1);
})->where('verification_status', 1)->count();
$inactiveSalons = BeautySalon::whereHas('store', function($query) {
    return $query->where('status', 1);
})->where('verification_status', '!=', 1)->count();
$newlyJoinedSalons = BeautySalon::where('created_at', '>=', now()->subDays(30)->toDateTimeString())->count();

// Pass to view
return view('beautybooking::admin.salon.index', compact(
    'salons',
    'totalSalons',
    'activeSalons',
    'inactiveSalons',
    'newlyJoinedSalons',
    // ... other variables
));
```

### View Changes

**Before:**
```blade
@php
    $active_salons = \Modules\BeautyBooking\Entities\BeautySalon::whereHas('store', function($query) {
        return $query->where('status', 1);
    })->where('verification_status', 1)->count();
    $active_salons = isset($active_salons) ? $active_salons : 0;
@endphp
<h4 class="title">{{$active_salons}}</h4>
```

**After:**
```blade
<h4 class="title">{{$activeSalons ?? 0}}</h4>
```

## Benefits

1. **Cleaner View Code** - Views are now simpler and easier to read
2. **Better Separation of Concerns** - Business logic is in the controller, not the view
3. **Easier Testing** - Logic can be tested independently in the controller
4. **Better Performance** - Calculations happen once in controller, not on every view render
5. **No More Blade Compilation Errors** - Removed problematic multi-line @php() directives

## Files Modified

1. `Modules/BeautyBooking/Http/Controllers/Web/Admin/BeautySalonController.php`
   - Added statistics calculations
   - Added variables to compact() call

2. `Modules/BeautyBooking/Resources/views/admin/salon/index.blade.php`
   - Removed all @php blocks with complex queries
   - Replaced with simple variable references

## Verification

- ✅ View compiles successfully without syntax errors
- ✅ All control structures (@foreach, @endforeach, @if, @endif) are properly balanced
- ✅ Statistics are calculated correctly in controller
- ✅ View displays statistics correctly

## Best Practices Applied

1. **Separation of Concerns** - Business logic in controller, presentation in view
2. **DRY Principle** - Calculations done once, not repeated in view
3. **Laravel Conventions** - Follow Laravel best practices for view/controller separation
4. **Error Prevention** - Avoid complex PHP code in Blade templates

## Date

2025-11-30

## Status

✅ **RESOLVED** - Blade compilation error fixed, view renders successfully

