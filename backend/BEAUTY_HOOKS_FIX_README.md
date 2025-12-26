# Beauty Booking Module - Hook Export Fix Guide

This guide provides scripts and instructions to fix import/export mismatches in the Beauty Booking module's React Query hooks.

## Problem

- Components are using **named imports**: `import { useHookName } from ...`
- Some hooks are using **default exports**: `export default function useHookName() {}`
- This causes build errors: "Attempted import error: 'useHookName' is not exported"

## Solution

Convert all hooks from `export default function` to `export const` (named exports).

## Files Provided

1. **`fix-beauty-hooks-exports.js`** - Node.js script (recommended)
   - Intelligent conversion with proper pattern matching
   - Creates backup automatically
   - Provides detailed progress and summary

2. **`fix-beauty-hooks.sh`** - Main shell script
   - Orchestrates the conversion process
   - Runs the Node.js script
   - Handles backup and verification

3. **`fix-hooks-sed.sh`** - Alternative sed-based script
   - Simpler, direct approach
   - Uses sed for bulk replacement
   - Faster but less intelligent

4. **`verify-fix.sh`** - Verification script
   - Checks if conversion was successful
   - Reports any remaining issues
   - Lists all hook files and their export status

## Quick Start

### Option 1: Using the Main Script (Recommended)

```bash
# 1. Connect to server
ssh root@193.162.129.214
# Enter password: H161t5dzCG

# 2. Upload scripts to server (from your local machine)
scp fix-beauty-hooks-exports.js fix-beauty-hooks.sh verify-fix.sh root@193.162.129.214:/tmp/

# 3. On server: Make scripts executable
chmod +x /tmp/fix-beauty-hooks.sh /tmp/verify-fix.sh

# 4. Run the fix script
/tmp/fix-beauty-hooks.sh
```

### Option 2: Manual Step-by-Step

```bash
# 1. Connect to server
ssh root@193.162.129.214
# Enter password: H161t5dzCG

# 2. Navigate to React app
cd /var/www/6ammart-react

# 3. Create backup
cp -r src/api-manage/hooks/react-query/beauty/vendor /tmp/beauty-vendor-hooks-backup

# 4. Upload and run Node.js script
# (Upload fix-beauty-hooks-exports.js first)
node fix-beauty-hooks-exports.js

# 5. Verify
./verify-fix.sh

# 6. Test build
npm run build 2>&1 | grep -i error

# 7. Restart application
pm2 restart 6ammart-react
```

## Detailed Instructions

### Step 1: Connect to Server

```bash
ssh root@193.162.129.214
# Password: H161t5dzCG
```

### Step 2: Upload Scripts

From your local machine:

```bash
scp fix-beauty-hooks-exports.js root@193.162.129.214:/tmp/
scp fix-beauty-hooks.sh root@193.162.129.214:/tmp/
scp verify-fix.sh root@193.162.129.214:/tmp/
```

### Step 3: Make Scripts Executable

On the server:

```bash
chmod +x /tmp/fix-beauty-hooks.sh
chmod +x /tmp/verify-fix.sh
```

### Step 4: Run the Fix

```bash
/tmp/fix-beauty-hooks.sh
```

This will:
- ✅ Create a backup
- ✅ Convert all default exports to named exports
- ✅ Verify the conversion
- ✅ Show summary

### Step 5: Verify the Fix

```bash
/tmp/verify-fix.sh
```

Expected output:
- ✅ No default exports found in hooks
- ✅ All hooks use named exports
- ✅ All component imports use named imports

### Step 6: Test Build

```bash
cd /var/www/6ammart-react
npm run build 2>&1 | grep -E "error|Error|ERROR|Attempted import error"
```

If no errors appear, the fix was successful!

### Step 7: Restart Application

```bash
pm2 restart 6ammart-react
# or
pm2 restart all
```

## Hooks That Will Be Converted

The following 24 hooks will be converted:

1. `useGetBadgeStatus.js`
2. `useGetCalendarAvailability.js`
3. `useGetCampaignStats.js`
4. `useGetPackageUsageStats.js`
5. `useGetPayoutSummary.js`
6. `useGetPointsHistory.js`
7. `useGetRedemptionHistory.js`
8. `useGetServiceDetails.js`
9. `useGetStaffDetails.js`
10. `useGetSubscriptionHistory.js`
11. `useGetSubscriptionPlans.js`
12. `useGetTransactionHistory.js`
13. `useGetVendorBookingDetails.js`
14. `useGetVendorBookings.js`
15. `useGetVendorGiftCards.js`
16. `useGetVendorLoyaltyCampaigns.js`
17. `useGetVendorPackages.js`
18. `useGetVendorProfile.js`
19. `useGetVendorRetailOrders.js`
20. `useGetVendorRetailProducts.js`
21. `useGetVendorServices.js`
22. `useGetVendorStaff.js`
23. `useManageHolidays.js`
24. `usePurchaseSubscription.js`

## Conversion Pattern

**Before:**
```javascript
export default function useHookName(params, enabled = true) {
  return useQuery(
    ["query-key", params],
    () => fetchFunction(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
}
```

**After:**
```javascript
export const useHookName = (params, enabled = true) => {
  return useQuery(
    ["query-key", params],
    () => fetchFunction(params),
    {
      enabled: enabled,
      onError: onSingleErrorResponse,
    }
  );
};
```

## Troubleshooting

### Issue: Script Permission Denied

```bash
chmod +x fix-beauty-hooks.sh verify-fix.sh
```

### Issue: Node.js Not Found

```bash
# Check Node.js version
node --version

# If not installed, install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### Issue: Files Still Have Default Exports

Run the verification script to see which files:
```bash
./verify-fix.sh
```

Then manually check and fix those files.

### Issue: Build Still Fails

1. Check for other import errors:
   ```bash
   cd /var/www/6ammart-react
   npm run build 2>&1 | grep -i "error"
   ```

2. Check component imports:
   ```bash
   grep -r "import.*from.*beauty/vendor" src/components/home/module-wise-components/beauty/vendor/*.js | grep -v "{"
   ```

3. Restore from backup if needed:
   ```bash
   cp -r /tmp/beauty-vendor-hooks-backup-* /var/www/6ammart-react/src/api-manage/hooks/react-query/beauty/vendor
   ```

## Rollback

If something goes wrong, restore from backup:

```bash
# Find the backup directory
ls -la /tmp/beauty-vendor-hooks-backup-*

# Restore (replace TIMESTAMP with actual timestamp)
cp -r /tmp/beauty-vendor-hooks-backup-TIMESTAMP/* /var/www/6ammart-react/src/api-manage/hooks/react-query/beauty/vendor/
```

## Expected Results

After successful conversion:

- ✅ All 24 hooks use `export const` (named exports)
- ✅ All components use `import { hookName } from ...` (named imports)
- ✅ Build completes without import errors
- ✅ Application runs without runtime import errors

## Verification Commands

```bash
# 1. Check all hooks use named exports
grep -r "export default" /var/www/6ammart-react/src/api-manage/hooks/react-query/beauty/vendor/
# Should return nothing

# 2. Check all components use named imports
grep -r "import.*from.*beauty/vendor" /var/www/6ammart-react/src/components/home/module-wise-components/beauty/vendor/ | grep -v "{"
# Should return nothing

# 3. Build and check for errors
cd /var/www/6ammart-react && npm run build 2>&1 | grep -i "error"
# Should return nothing
```

## Support

If you encounter issues:

1. Check the backup was created successfully
2. Run the verification script to identify problems
3. Check file permissions (should be `ubuntu24:ubuntu24`)
4. Verify Node.js is installed and working
5. Check PM2 process status: `pm2 list`

---

**Last Updated**: 2025-01-XX  
**Scripts Version**: 1.0

