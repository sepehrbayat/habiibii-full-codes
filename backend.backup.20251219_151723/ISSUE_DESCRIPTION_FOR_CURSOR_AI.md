# React Error #31: Objects are not valid as a React child

## Server Access Information

### SSH Connection Details
- **Server IP:** `193.162.129.214`
- **Username:** `root`
- **Password:** `H161t5dzCG`
- **SSH Command:** `sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214`

### Next.js Application Path
- **Application Directory:** `/var/www/6ammart-react`
- **Build Command:** `cd /var/www/6ammart-react && npm run build`
- **PM2 Process Name:** `6ammart-react`
- **Restart Command:** `cd /var/www/6ammart-react && pm2 restart 6ammart-react`

### How to Access and Edit Files

#### Method 1: Direct SSH Access
```bash
# Connect to server
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214

# Navigate to project
cd /var/www/6ammart-react

# Edit files using nano or vim
nano src/components/profile/basic-information/BasicInformationForm.js
```

#### Method 2: Copy File, Edit Locally, Upload Back
```bash
# Download file to local machine
sshpass -p 'H161t5dzCG' scp -o StrictHostKeyChecking=no root@193.162.129.214:/var/www/6ammart-react/src/components/profile/basic-information/BasicInformationForm.js ./BasicInformationForm.js

# Edit file locally, then upload back
sshpass -p 'H161t5dzCG' scp -o StrictHostKeyChecking=no ./BasicInformationForm.js root@193.162.129.214:/var/www/6ammart-react/src/components/profile/basic-information/BasicInformationForm.js
```

#### Method 3: Direct Edit via SSH (One-liner)
```bash
# Edit file directly using sed or cat
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "cat > /var/www/6ammart-react/src/components/profile/basic-information/BasicInformationForm.js << 'EOF'
[file content here]
EOF"
```

### After Making Changes - Rebuild and Restart

```bash
# 1. Rebuild Next.js application
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "cd /var/www/6ammart-react && npm run build"

# 2. Restart PM2 process
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "cd /var/www/6ammart-react && pm2 restart 6ammart-react"

# 3. Check logs
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "pm2 logs 6ammart-react --lines 20 --nostream"
```

### Useful Commands

```bash
# View file content
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "cat /var/www/6ammart-react/src/components/profile/basic-information/BasicInformationForm.js"

# Search for patterns
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "grep -n 'helperText' /var/www/6ammart-react/src/components/profile/basic-information/BasicInformationForm.js"

# Backup file before editing
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "cp /var/www/6ammart-react/src/components/profile/basic-information/BasicInformationForm.js /var/www/6ammart-react/src/components/profile/basic-information/BasicInformationForm.js.backup"

# Check PM2 status
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "pm2 status"

# View build errors
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "cd /var/www/6ammart-react && npm run build 2>&1 | tail -30"

# Find all helperText usages
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "grep -rn 'helperText.*profileFormik' /var/www/6ammart-react/src/components"

# Check for object rendering patterns
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "grep -rn '{.*error.*}\|{.*Error.*}\|{.*data.*}' /var/www/6ammart-react/src/components/profile /var/www/6ammart-react/src/components/header/second-navbar/account-popover 2>/dev/null | grep -E '\.(js|jsx)$' | grep -v '//' | grep -v 'console'"
```

### File Locations (Full Paths)

All file paths mentioned in this document are relative to `/var/www/6ammart-react/`:

- `src/components/profile/basic-information/BasicInformationForm.js`
- `src/components/header/second-navbar/account-popover/Menu.js`
- `src/components/profile/ProfileTabPopover.js`
- `src/components/wallet/WalletBoxComponent.js`
- `src/components/header/second-navbar/SecondNavbar.js`
- `src/components/header/second-navbar/account-popover/index.js`

## Problem Description

When clicking on the Avatar element in the header (which opens the AccountPopover), the application crashes with React error #31: "Objects are not valid as a React child". This error occurs because React is trying to render an object directly in JSX, which is not allowed.

## Error Location

The error occurs in the profile-related components, specifically when:
1. Clicking on the Avatar in `SecondNavbar.js` (header)
2. Opening the `AccountPopover` component
3. Rendering profile-related components that may contain objects

## Root Causes

### 1. helperText in BasicInformationForm.js
The `helperText` prop in TextField components may receive error objects from Formik validation instead of strings. When Formik errors are objects (which can happen with complex validation), React tries to render them directly, causing error #31.

**Current problematic code:**
```javascript
helperText={profileFormik.touched.name && profileFormik.errors.name}
```

**Issue:** If `profileFormik.errors.name` is an object (e.g., `{message: "error", code: "validation"}`), React will try to render it as a child, causing the error.

### 2. Potential Object Rendering in Menu Components
The Menu component in `account-popover/Menu.js` may be rendering objects from `menuData` or `configData` directly in JSX.

### 3. Balance/Object Values in WalletBoxComponent
The `balance` prop in `WalletBoxComponent.js` may be an object instead of a primitive value, and we're trying to render it directly.

## Files to Check and Fix

### Priority 1: BasicInformationForm.js
**Location:** `/var/www/6ammart-react/src/components/profile/basic-information/BasicInformationForm.js`

**Check for:**
- All `helperText` props that use Formik errors
- Ensure all helperText values are converted to strings

**Fix pattern:**
```javascript
// ❌ WRONG - may render object
helperText={profileFormik.touched.field && profileFormik.errors.field}

// ✅ CORRECT - always string
helperText={profileFormik.touched.field && profileFormik.errors.field ? String(profileFormik.errors.field) : ""}
```

**Fields to check:**
- `name` (line ~359)
- `email` (line ~386)
- `phone` (if exists)
- `password` (line ~509)
- `confirm_password` (line ~544)

### Priority 2: AccountPopover/Menu.js
**Location:** `/var/www/6ammart-react/src/components/header/second-navbar/account-popover/Menu.js`

**Check for:**
- Any direct rendering of `item`, `menu`, `configData`, or `modules` objects
- Ensure all values passed to `t()` translation function are strings
- Check if `item?.icon` is a valid React element or might be an object

**Potential issues:**
```javascript
// Check if these might be objects:
{item?.icon}  // Should be React element, not object
{configData?.something}  // Should be primitive, not object
{modules?.find(...)}  // Should be converted to string if rendered
```

### Priority 3: ProfileTabPopover.js
**Location:** `/var/www/6ammart-react/src/components/profile/ProfileTabPopover.js`

**Check for:**
- `menu?.name` - ensure it's a string before using `.replace()`
- Any direct rendering of menu objects

**Already fixed but verify:**
- `getToken` import is present
- `userToken` is used instead of calling `getToken()` in JSX

### Priority 4: WalletBoxComponent.js
**Location:** `/var/www/6ammart-react/src/components/wallet/WalletBoxComponent.js`

**Check for:**
- `balance` prop - ensure it's converted to string/number before rendering
- Any object properties that might be rendered directly

**Already fixed but verify:**
- `getBalanceDisplay()` function properly handles objects
- All balance displays use `String()` conversion

## Debugging Steps

1. **Enable non-minified React errors:**
   - Set `NODE_ENV=development` in Next.js build
   - Or add error boundary with detailed error logging

2. **Check browser console:**
   - Look for the exact component stack trace
   - Identify which component is trying to render the object

3. **Add defensive checks:**
   ```javascript
   // Before rendering any value, check its type
   const safeValue = typeof value === 'object' ? JSON.stringify(value) : String(value);
   ```

4. **Use React DevTools:**
   - Inspect the component tree when error occurs
   - Check props and state values for objects

## Solution Pattern

For all places where values might be objects:

```javascript
// Pattern 1: For helperText
helperText={error ? (typeof error === 'string' ? error : error?.message || String(error)) : ""}

// Pattern 2: For rendering values
{typeof value === 'object' ? JSON.stringify(value) : String(value)}

// Pattern 3: For conditional rendering
{value && typeof value !== 'object' ? value : null}
```

## Specific Fixes Needed

### Fix 1: BasicInformationForm.js - helperText
```javascript
// Find all instances of:
helperText={profileFormik.touched.X && profileFormik.errors.X}

// Replace with:
helperText={profileFormik.touched.X && profileFormik.errors.X 
  ? (typeof profileFormik.errors.X === 'string' 
      ? profileFormik.errors.X 
      : profileFormik.errors.X?.message || String(profileFormik.errors.X))
  : ""}
```

### Fix 2: Menu.js - Check all rendered values
```javascript
// Ensure menu.name is always string
const menuName = typeof menu?.name === 'string' ? menu.name : String(menu?.name || "");

// Ensure icon is valid React element
{item?.icon && React.isValidElement(item.icon) ? item.icon : null}
```

### Fix 3: Add Error Boundary with detailed logging
```javascript
// In _app.js or ErrorBoundary component
componentDidCatch(error, errorInfo) {
  console.error('Error details:', {
    error: error.toString(),
    errorMessage: error.message,
    errorStack: error.stack,
    componentStack: errorInfo.componentStack,
    // Log the actual value that caused the error
  });
}
```

## Testing

After fixes:
1. Click on Avatar in header
2. Open AccountPopover
3. Navigate to profile pages
4. Check browser console for any remaining errors
5. Verify all helperText values display correctly
6. Verify all menu items render correctly

## Additional Notes

- Firebase messaging errors are non-critical (permission-related) and can be ignored
- The main issue is React trying to render objects as children
- All values passed to JSX must be primitives (string, number, boolean) or valid React elements
- Formik errors can sometimes be objects, especially with custom validators

