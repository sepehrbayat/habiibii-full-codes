# Fix Applied for Wish-List API 403 Error

## Problem Identified

The React app was sending the header as `zoneid` (lowercase) but the Laravel backend expects `zoneId` (with capital I). Additionally, the backend expects `zoneId` to be a JSON array string like `"[1]"` or `"[1,2,3]"`.

## Solution Applied

### File Modified: `/var/www/6ammart-react/src/api-manage/MainApi.js`

**Changes Made:**

1. **Header Name Fix**: Changed from `zoneid` to `zoneId` (capital I)
2. **Format Fix**: Added logic to convert zoneId to JSON array string format

### Code Changes:

**Before:**
```javascript
if (zoneid) config.headers.zoneid = zoneid;
```

**After:**
```javascript
// Fix: Use zoneId (capital I) and format as JSON array string for backend
if (zoneid) {
  // If zoneid is already a JSON array string, use it as is
  // Otherwise, convert it to JSON array format
  let zoneIdValue = zoneid;
  try {
    // Try to parse it, if it's not valid JSON, wrap it in array
    JSON.parse(zoneid);
    zoneIdValue = zoneid;
  } catch (e) {
    // If it's a single number or comma-separated, convert to JSON array
    if (zoneid.includes(",")) {
      const zones = zoneid.split(",").map(z => parseInt(z.trim())).filter(z => !isNaN(z));
      zoneIdValue = JSON.stringify(zones);
    } else {
      const zoneNum = parseInt(zoneid);
      if (!isNaN(zoneNum)) {
        zoneIdValue = JSON.stringify([zoneNum]);
      }
    }
  }
  config.headers.zoneId = zoneIdValue; // Fixed: zoneId with capital I
}
```

## What This Fix Does

1. **Header Name**: Now sends `zoneId` instead of `zoneid` (matches backend expectation)
2. **Format Conversion**: Automatically converts zoneId to JSON array format:
   - `"1"` → `"[1]"`
   - `"1,2,3"` → `"[1,2,3]"`
   - `"[1]"` → `"[1]"` (already correct format)
3. **Backward Compatible**: Handles both string and JSON array formats from localStorage

## Testing

After this fix, the wish-list API should work correctly:

```javascript
// In React app, localStorage should have:
localStorage.setItem("zoneid", "1"); // or "[1]" or "1,2,3"

// The API call will now send:
// Header: zoneId: "[1]"
// Header: Authorization: Bearer TOKEN
```

## React App Restart

The React app has been restarted via PM2 to apply the changes.

## Verification

To verify the fix is working:

1. Open browser console
2. Check Network tab
3. Look for `/api/v1/customer/wish-list` request
4. Verify headers:
   - `zoneId: "[1]"` (or your zone ID)
   - `Authorization: Bearer YOUR_TOKEN`

## Backup

Original file backed up to: `/var/www/6ammart-react/src/api-manage/MainApi.js.bak`

