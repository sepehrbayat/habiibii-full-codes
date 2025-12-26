# Quick Fix Prompt for Cursor AI

## Issue
Error Code -109 when accessing React Next.js app at `http://188.245.192.118:3000/`. The app fails to fetch analytics config from Laravel API endpoint `/api/v1/config/get-analytic-scripts`.

## Error
```
Error fetching analytics config: SyntaxError: Unexpected token < in JSON at position 0
```

## Problem
The endpoint returns HTML instead of JSON when called from Next.js server-side rendering (`pages/_document.js` in `getInitialProps`), but returns JSON when tested with curl.

## What to Do
1. Check why `/api/v1/config/get-analytic-scripts` returns HTML from SSR context but JSON from curl
2. Verify route is accessible without auth in `routes/api/v1/api.php`
3. Check `app/Http/Controllers/Api/V1/ConfigController.php` - `getAnalyticScripts()` method
4. Review Laravel logs for exceptions
5. Ensure endpoint always returns `Content-Type: application/json`
6. Fix the issue so React app loads without Error -109

## Files
- `pages/_document.js` (line ~191) - fetch call
- `app/Http/Controllers/Api/V1/ConfigController.php` - endpoint implementation
- `routes/api/v1/api.php` - route definition
- `storage/logs/laravel.log` - check for errors

Fix the endpoint to consistently return JSON and handle errors gracefully.
