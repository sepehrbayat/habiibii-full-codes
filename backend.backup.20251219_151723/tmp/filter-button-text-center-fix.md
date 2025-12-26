# Filter Button Text Centering Fix

## Issue Summary

The "Filter" text in filter buttons across report sections was not perfectly centered within the button, even though the button width was adequate.

## Solution Applied

Added `text-center` class to all filter buttons to ensure the text is perfectly centered:

### Files Modified

1. **Revenue Breakdown** (`revenue-breakdown.blade.php`):
   - Added `text-center` class to filter button
   - Button classes: `btn btn--primary btn-block text-center`

2. **Monthly Summary** (`monthly-summary.blade.php`):
   - Added `text-center` class to filter button
   - Button classes: `btn btn--primary btn-block text-center`

3. **Top-Rated Salons** (`top-rated.blade.php`):
   - Added `text-center` class to filter button
   - Button classes: `btn btn--primary btn-block text-center`

## Result

All filter buttons now have perfectly centered "Filter" text within the button, ensuring consistent and professional appearance across all report sections.

