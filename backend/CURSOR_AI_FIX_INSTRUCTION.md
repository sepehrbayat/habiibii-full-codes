# Fix React Error #31: Objects are not valid as a React child

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
```

## Problem
When clicking Avatar in header → AccountPopover opens → React crashes with error #31 because an object is being rendered as a React child.

## Root Cause
React is trying to render objects directly in JSX. This happens in:
1. **Formik helperText** - Error objects from validation are rendered directly
2. **Menu/Profile components** - Objects from Redux state or props are rendered
3. **Balance/Data values** - Objects passed as props are rendered without conversion

## Files to Fix (Priority Order)

### 1. `/var/www/6ammart-react/src/components/profile/basic-information/BasicInformationForm.js`
**Problem:** `helperText` props may receive Formik error objects instead of strings.

**Fix ALL helperText instances:**
```javascript
// ❌ WRONG
helperText={profileFormik.touched.name && profileFormik.errors.name}

// ✅ CORRECT
helperText={
  profileFormik.touched.name && profileFormik.errors.name
    ? (typeof profileFormik.errors.name === 'string'
        ? profileFormik.errors.name
        : profileFormik.errors.name?.message || String(profileFormik.errors.name))
    : ""
}
```

**Apply to these fields:**
- `name` (around line 359)
- `email` (around line 386)  
- `password` (around line 509)
- `confirm_password` (around line 544)
- Any other fields with helperText

### 2. `/var/www/6ammart-react/src/components/header/second-navbar/account-popover/Menu.js`
**Problem:** Menu items or config data might be objects rendered directly.

**Check and fix:**
```javascript
// Ensure menu.name is always string
const menuName = typeof menu?.name === 'string' 
  ? menu.name 
  : String(menu?.name || "");

// Ensure icon is valid React element
{item?.icon && React.isValidElement(item.icon) 
  ? item.icon 
  : null}
```

**Verify:**
- All values passed to `t()` are strings
- No objects rendered in JSX
- `configData` and `modules` values are converted to primitives before rendering

### 3. `/var/www/6ammart-react/src/components/profile/ProfileTabPopover.js`
**Problem:** Menu name might be object when using `.replace()`.

**Fix:**
```javascript
// Ensure menu.name is string before replace
const menuName = menu?.name || "";
const displayName = typeof menuName === 'string' 
  ? menuName.replace("-", " ") 
  : String(menuName);
```

### 4. `/var/www/6ammart-react/src/components/wallet/WalletBoxComponent.js`
**Problem:** Balance might be object.

**Verify:**
- `getBalanceDisplay()` function exists and handles objects
- All balance renders use `String()` conversion

## General Fix Pattern

**For any value that might be an object:**
```javascript
// Pattern 1: Safe string conversion
const safeValue = typeof value === 'object' && value !== null
  ? (value?.message || JSON.stringify(value))
  : String(value || "");

// Pattern 2: Conditional rendering
{typeof value !== 'object' ? value : null}

// Pattern 3: For helperText specifically
helperText={error 
  ? (typeof error === 'string' 
      ? error 
      : error?.message || String(error))
  : ""}
```

## Action Items

1. **Search entire codebase** for patterns like:
   - `helperText={...errors...}`
   - `{data?.something}` where data might be object
   - `{error}` or `{res}` directly in JSX
   - `{configData?....}` without type checking

2. **Add type guards** before rendering any value:
   ```javascript
   const renderValue = (value) => {
     if (value === null || value === undefined) return "";
     if (typeof value === 'object') {
       return value?.message || JSON.stringify(value);
     }
     return String(value);
   };
   ```

3. **Test after each fix:**
   - Click Avatar in header
   - Open AccountPopover
   - Navigate to profile pages
   - Check browser console

## Critical: Enable Detailed Error Logging

Add to ErrorBoundary or _app.js:
```javascript
componentDidCatch(error, errorInfo) {
  console.error('React Error #31 Details:', {
    error: error.toString(),
    message: error.message,
    stack: error.stack,
    componentStack: errorInfo.componentStack
  });
}
```

This will help identify exactly which component/value is causing the error.

