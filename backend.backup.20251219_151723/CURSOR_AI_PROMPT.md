# Cursor AI Prompt: Fix Beauty Booking Module Import Errors

## Context & Current Situation

You are working on a Laravel + Next.js application (6amMart) with a Beauty Booking module. The React frontend is deployed on a remote server and needs import errors fixed.

## What Has Been Done So Far

1. **Initial Fix Attempt**: A script was created to change all default imports to named imports in Beauty Vendor components:
   - Changed `import useCreateCalendarBlock from ...` to `import { useCreateCalendarBlock } from ...`
   - Applied to all hooks in `/var/www/6ammart-react/src/components/home/module-wise-components/beauty/vendor/`

2. **Problem Identified**: The script partially worked, but there's a mismatch:
   - Some hooks use `export default function` (e.g., `useGetCalendarAvailability`, `useGetVendorBookings`)
   - Some hooks use `export const` (e.g., `useCreateCalendarBlock`, `useCreateService`)
   - Components are now trying to use named imports for ALL hooks, but some hooks actually have default exports

## Current Server Errors

Based on the build output, the following import errors exist:

```
Attempted import error: 'useGetCalendarAvailability' is not exported from '.../useGetCalendarAvailability'
Attempted import error: 'useGetCampaignStats' is not exported from '.../useGetCampaignStats'
Attempted import error: 'useGetVendorBookings' is not exported from '.../useGetVendorBookings'
Attempted import error: 'useGetVendorGiftCards' is not exported from '.../useGetVendorGiftCards'
Attempted import error: 'useGetVendorProfile' is not exported from '.../useGetVendorProfile'
Attempted import error: 'useManageHolidays' is not exported from '.../useManageHolidays'
Attempted import error: 'useGetVendorLoyaltyCampaigns' is not exported from '.../useGetVendorLoyaltyCampaigns'
Attempted import error: 'useGetVendorPackages' is not exported from '.../useGetVendorPackages'
Attempted import error: 'useGetPackageUsageStats' is not exported from '.../useGetPackageUsageStats'
Attempted import error: 'useGetPayoutSummary' is not exported from '.../useGetPayoutSummary'
Attempted import error: 'useGetPointsHistory' is not exported from '.../useGetPointsHistory'
Attempted import error: 'useGetRedemptionHistory' is not exported from '.../useGetRedemptionHistory'
Attempted import error: 'useGetVendorRetailOrders' is not exported from '.../useGetVendorRetailOrders'
Attempted import error: 'useGetVendorRetailProducts' is not exported from '.../useGetVendorRetailProducts'
Attempted import error: 'useGetServiceDetails' is not exported from '.../useGetServiceDetails'
Attempted import error: 'useGetVendorStaff' is not exported from '.../useGetVendorStaff'
```

## Server Information

### Connection Details
- **Host**: `193.162.129.214`
- **User**: `root`
- **Password**: `H161t5dzCG` (you will need to enter this first of each command 
- **Connection Method**: SSH via terminal

### Server Paths
- **React App Root**: `/var/www/6ammart-react`
- **Beauty Vendor Hooks**: `/var/www/6ammart-react/src/api-manage/hooks/react-query/beauty/vendor/`
- **Beauty Vendor Components**: `/var/www/6ammart-react/src/components/home/module-wise-components/beauty/vendor/`

### Build & Process Management
- **Build Command**: `cd /var/www/6ammart-react && npm run build`
- **Process Manager**: PM2 (likely running the Next.js app)
- **Restart Command**: `pm2 restart 6ammart-react` or similar (check with `pm2 list`)

## Task: Fix All Import Errors

### Step 1: Connect to Server
```bash
ssh root@193.162.129.214
# Enter password: H161t5dzCG
```

### Step 2: Identify Export Types
1. Find all hooks that use `export default`:
   ```bash
   cd /var/www/6ammart-react
   grep -l "export default" src/api-manage/hooks/react-query/beauty/vendor/*.js
   ```

2. Find all hooks that use `export const`:
   ```bash
   grep -l "export const" src/api-manage/hooks/react-query/beauty/vendor/*.js
   ```

### Step 3: Standardize All Hooks to Named Exports
**Decision**: Convert ALL hooks to use `export const` (named export) for consistency.

For each hook file that uses `export default function`, change:
```javascript
export default function useHookName(params, enabled = true) {
  // ...
}
```

To:
```javascript
export const useHookName = (params, enabled = true) => {
  // ...
}
```

Or if it's already a const function:
```javascript
export const useHookName = () => {
  // ...
}
```

### Step 4: Verify Component Imports
All components should already be using named imports (from the previous script), but verify:
```bash
# Check if any components still use default imports
grep -r "import.*from.*beauty/vendor" src/components/home/module-wise-components/beauty/vendor/*.js | grep -v "{"
```

If any default imports are found, they should be changed to named imports.

### Step 5: Test Build
```bash
cd /var/www/6ammart-react
npm run build 2>&1 | grep -E "error|Error|ERROR|Attempted import error"
```

### Step 6: Restart Application
After successful build:
```bash
pm2 restart all
# or
pm2 restart 6ammart-react
```

## Files That Need Changes

**Total**: 24 hook files need to be converted from `export default` to `export const`.

Complete list of hooks with default exports:
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

**Pattern to Convert**:

From:
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

To:
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

## Important Notes

1. **Password Entry**: You must manually enter the password `H161t5dzCG` when connecting via SSH. The system will prompt you.

2. **File Permissions**: Files are owned by `ubuntu24:ubuntu24`. You may need to use `sudo` or change ownership if needed.

3. **Backup**: Before making changes, consider backing up the hooks directory:
   ```bash
   cp -r /var/www/6ammart-react/src/api-manage/hooks/react-query/beauty/vendor /tmp/beauty-vendor-hooks-backup
   ```

4. **Consistency**: The goal is to have ALL hooks use named exports (`export const`) and ALL components use named imports (`import { hookName } from ...`).

5. **Testing**: After fixing, run a full build and check for any remaining errors. The build should complete without import errors.

## Expected Outcome

After completing these steps:
- ✅ All hooks use `export const` (named exports)
- ✅ All components use `import { hookName } from ...` (named imports)
- ✅ Build completes without import errors
- ✅ Application runs without runtime import errors

## Additional Context

- The application uses React Query for data fetching
- Hooks are organized in `/api-manage/hooks/react-query/beauty/vendor/`
- Components are in `/components/home/module-wise-components/beauty/vendor/`
- The build process uses Next.js
- The application is managed with PM2

## Verification Commands

After making changes, verify with:

```bash
# 1. Check all hooks use named exports
grep -r "export default" /var/www/6ammart-react/src/api-manage/hooks/react-query/beauty/vendor/

# Should return nothing (or only comments)

# 2. Check all components use named imports
grep -r "import.*from.*beauty/vendor" /var/www/6ammart-react/src/components/home/module-wise-components/beauty/vendor/ | grep -v "{"

# Should return nothing

# 3. Build and check for errors
cd /var/www/6ammart-react && npm run build 2>&1 | grep -i "error"
```

---

**Start by connecting to the server and identifying which hooks need to be converted.**
