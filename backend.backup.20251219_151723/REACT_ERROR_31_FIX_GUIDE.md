# React Error #31 Fix Guide

## Quick Fix Script

Run the automated fix script:

```bash
chmod +x fix-react-error-31.sh
./fix-react-error-31.sh
```

This script will:
1. ✅ Create a safe rendering utility (`src/utils/safeRender.js`)
2. ✅ Fix `BasicInformationForm.js` - all `helperText` props
3. ✅ Fix `Menu.js` - menu names and icons
4. ✅ Fix `ProfileTabPopover.js` - menu name rendering
5. ✅ Fix `WalletBoxComponent.js` - balance rendering
6. ✅ Backup all files before modification
7. ✅ Search for other potential issues

## Manual Fix Process

If you prefer to fix manually or the script fails:

### Step 1: Create Safe Rendering Utility

Create `/var/www/6ammart-react/src/utils/safeRender.js`:

```javascript
/**
 * Safe Rendering Utilities
 * Prevents React Error #31: Objects are not valid as a React child
 */

/**
 * Safely convert any value to string for rendering
 */
export const safeString = (value) => {
  if (value === null || value === undefined) return '';
  if (typeof value === 'string') return value;
  if (typeof value === 'object') {
    return value?.message || JSON.stringify(value);
  }
  return String(value);
};

/**
 * Safely get error message for helperText
 */
export const safeHelperText = (error, touched = true) => {
  if (!touched || !error) return '';
  return safeString(error);
};

/**
 * Safely render value in JSX
 */
export const safeRender = (value) => {
  if (value === null || value === undefined) return null;
  if (typeof value === 'object') {
    return safeString(value);
  }
  return value;
};
```

### Step 2: Fix BasicInformationForm.js

**Find and replace all `helperText` patterns:**

```javascript
// ❌ BEFORE
helperText={profileFormik.touched.name && profileFormik.errors.name}

// ✅ AFTER
helperText={safeHelperText(profileFormik.errors.name, profileFormik.touched.name)}
```

**Or use inline fix:**
```javascript
helperText={
  profileFormik.touched.name && profileFormik.errors.name
    ? (typeof profileFormik.errors.name === 'string'
        ? profileFormik.errors.name
        : profileFormik.errors.name?.message || String(profileFormik.errors.name))
    : ""
}
```

**Add import at top:**
```javascript
import { safeHelperText } from '../../../utils/safeRender';
```

### Step 3: Fix Menu.js

**Fix menu name rendering:**
```javascript
// ❌ BEFORE
{menu.name}

// ✅ AFTER
{safeString(menu?.name || "")}
```

**Fix icon rendering:**
```javascript
// ❌ BEFORE
{item.icon}

// ✅ AFTER
{item?.icon && React.isValidElement(item.icon) ? item.icon : null}
```

**Add import:**
```javascript
import { safeString } from '../../../../utils/safeRender';
```

### Step 4: Fix ProfileTabPopover.js

**Fix menu name with replace:**
```javascript
// ❌ BEFORE
{menu.name.replace("-", " ")}

// ✅ AFTER
{(typeof menu?.name === 'string' ? menu.name : safeString(menu?.name || "")).replace("-", " ")}
```

**Add import:**
```javascript
import { safeString } from '../../utils/safeRender';
```

### Step 5: Fix WalletBoxComponent.js

**Fix balance rendering:**
```javascript
// ❌ BEFORE
{balance}

// ✅ AFTER
{safeString(balance)}
```

**Update getBalanceDisplay function:**
```javascript
const getBalanceDisplay = (balance) => {
  if (typeof balance === 'object') {
    return safeString(balance);
  }
  return String(balance || 0);
};
```

## After Fixes - Rebuild and Restart

```bash
# SSH to server
sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214

# Navigate to project
cd /var/www/6ammart-react

# Rebuild
npm run build

# Restart PM2
pm2 restart 6ammart-react

# Check logs
pm2 logs 6ammart-react --lines 50
```

## Testing Checklist

After applying fixes, test:

- [ ] Click Avatar in header → AccountPopover opens without error
- [ ] Navigate to Profile → Basic Information page loads
- [ ] Fill form fields → No console errors
- [ ] Submit form with errors → Error messages display correctly
- [ ] Navigate to Wallet → Balance displays correctly
- [ ] Check browser console → No "Objects are not valid" errors

## Common Patterns to Fix

### Pattern 1: Direct Object Rendering
```javascript
// ❌ WRONG
{error}
{data}
{configData?.setting}

// ✅ CORRECT
{safeString(error)}
{safeString(data)}
{safeString(configData?.setting)}
```

### Pattern 2: helperText with Objects
```javascript
// ❌ WRONG
helperText={formik.errors.field}

// ✅ CORRECT
helperText={safeHelperText(formik.errors.field, formik.touched.field)}
```

### Pattern 3: String Methods on Objects
```javascript
// ❌ WRONG
{value.replace("-", " ")}
{value.toLowerCase()}

// ✅ CORRECT
{(typeof value === 'string' ? value : safeString(value)).replace("-", " ")}
{(typeof value === 'string' ? value : safeString(value)).toLowerCase()}
```

## Troubleshooting

### If build fails:
1. Check for syntax errors in modified files
2. Verify all imports are correct
3. Check Node.js version compatibility

### If error persists:
1. Enable detailed error logging (see original instructions)
2. Check browser console for exact error location
3. Verify the problematic component using React DevTools

### If PM2 won't restart:
```bash
pm2 delete 6ammart-react
cd /var/www/6ammart-react
npm run build
pm2 start npm --name "6ammart-react" -- start
pm2 save
```

## Additional Files to Check

If error persists, search for these patterns:

```bash
# Find all helperText usage
grep -rn "helperText={" src/ --include="*.js" --include="*.jsx"

# Find direct object rendering
grep -rn "{error}" src/ --include="*.js" --include="*.jsx"
grep -rn "{res}" src/ --include="*.js" --include="*.jsx"
grep -rn "{data}" src/ --include="*.js" --include="*.jsx"

# Find string methods on potentially objects
grep -rn "\.replace(" src/ --include="*.js" --include="*.jsx"
grep -rn "\.toLowerCase(" src/ --include="*.js" --include="*.jsx"
```

Apply the same fix patterns to any files found.

