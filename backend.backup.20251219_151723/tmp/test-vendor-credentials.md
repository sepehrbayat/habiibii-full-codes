# Test Vendor Credentials - Multi-Module Access

## 🔐 Login Credentials

**Email:** `test.multimodule@example.com`  
**Password:** `password123`  
**Vendor ID:** `3`

## 📦 Stores Created

This vendor has **3 stores** with different module access configurations:

### Store 1: Grocery Store with Beauty Access
- **Store ID:** `8`
- **Name:** Grocery Store with Beauty Access
- **Primary Module:** Grocery
- **Module Access:**
  - ✅ Grocery (Primary - cannot be disabled)
  - ✅ Beauty (Additional access - enabled)
- **Expected Behavior:**
  - Should see **module switcher** in header (2 accessible modules)
  - Should see both **Grocery** and **Beauty** sections in sidebar
  - Can switch between Grocery and Beauty dashboards
  - Default dashboard: Grocery (primary module)

### Store 2: Beauty Salon with Grocery Access
- **Store ID:** `9`
- **Name:** Beauty Salon with Grocery Access
- **Primary Module:** Beauty
- **Module Access:**
  - ✅ Beauty (Primary - cannot be disabled)
  - ✅ Grocery (Additional access - enabled)
- **Expected Behavior:**
  - Should see **module switcher** in header (2 accessible modules)
  - Should see both **Beauty** and **Grocery** sections in sidebar
  - Can switch between Beauty and Grocery dashboards
  - Default dashboard: Beauty (primary module)

### Store 3: Grocery Store Only
- **Store ID:** `10`
- **Name:** Grocery Store Only
- **Primary Module:** Grocery
- **Module Access:**
  - ✅ Grocery (Primary - cannot be disabled)
  - ❌ Beauty (Access disabled - for testing)
- **Expected Behavior:**
  - Should **NOT** see module switcher (only 1 accessible module)
  - Should see only **Grocery** sections in sidebar
  - Should **NOT** see Beauty sections
  - Default dashboard: Grocery

## 🧪 Testing Instructions

### Step 1: Login
1. Navigate to vendor login page
2. Use credentials:
   - Email: `test.multimodule@example.com`
   - Password: `password123`

### Step 2: Store Selection
**Note:** The system uses the **first store** (`stores[0]`) by default, which is **Store 1** (Grocery Store with Beauty Access).

If you need to test different stores, you can:
- **Option A:** Temporarily reorder stores in database
- **Option B:** Use vendor employees (each employee is assigned to a specific store)
- **Option C:** Modify the store order in the database

### Step 3: Test Store 1 (Grocery + Beauty)
After login, you should be on Store 1 by default:

1. **Check Header:**
   - ✅ Module switcher icon should be visible
   - Click it to see dropdown with "Grocery" and "Beauty" options

2. **Check Sidebar:**
   - ✅ Should see "Grocery Dashboard" link
   - ✅ Should see "Beauty Dashboard" link
   - ✅ Should see Grocery sections (POS, Orders, Items, etc.)
   - ✅ Should see Beauty sections when on beauty dashboard

3. **Test Module Switching:**
   - Click module switcher → Select "Beauty"
   - Should redirect to Beauty Booking dashboard
   - Sidebar should show beauty-specific sections
   - Click module switcher → Select "Grocery"
   - Should redirect to Grocery dashboard
   - Sidebar should show grocery-specific sections

### Step 4: Test Store 2 (Beauty + Grocery)
To test Store 2, you need to switch to it. Options:

**Option A: Direct Database Query**
```sql
-- Temporarily make Store 2 the first store
UPDATE stores SET id = 999 WHERE id = 9;
UPDATE stores SET id = 9 WHERE id = 8;
UPDATE stores SET id = 8 WHERE id = 999;
```

**Option B: Create Vendor Employee for Store 2**
```php
// Run in tinker
$employee = \App\Models\VendorEmployee::create([
    'f_name' => 'Test',
    'l_name' => 'Employee Store2',
    'email' => 'employee.store2@example.com',
    'password' => bcrypt('password123'),
    'vendor_id' => 3,
    'store_id' => 9,
    'status' => 1,
]);
```

### Step 5: Test Store 3 (Grocery Only)
To test Store 3:

**Option A: Direct Database Query**
```sql
-- Make Store 3 the first store
UPDATE stores SET id = 999 WHERE id = 10;
UPDATE stores SET id = 10 WHERE id = 8;
UPDATE stores SET id = 8 WHERE id = 999;
```

**Option B: Create Vendor Employee for Store 3**
```php
// Run in tinker
$employee = \App\Models\VendorEmployee::create([
    'f_name' => 'Test',
    'l_name' => 'Employee Store3',
    'email' => 'employee.store3@example.com',
    'password' => bcrypt('password123'),
    'vendor_id' => 3,
    'store_id' => 10,
    'status' => 1,
]);
```

## 🔍 Verification Queries

### Check Store Module Access
```sql
-- Check all stores for this vendor
SELECT 
    s.id,
    s.name,
    s.module_id as primary_module_id,
    m.module_type as primary_module_type,
    GROUP_CONCAT(DISTINCT sm2.module_id) as accessible_module_ids
FROM stores s
JOIN modules m ON s.module_id = m.id
LEFT JOIN store_modules sm ON s.id = sm.store_id AND sm.status = 1
LEFT JOIN store_modules sm2 ON s.id = sm2.store_id AND sm2.status = 1
WHERE s.vendor_id = 3
GROUP BY s.id, s.name, s.module_id, m.module_type;
```

### Check Store Modules Table
```sql
SELECT 
    sm.*,
    s.name as store_name,
    m.module_type,
    m.module_name
FROM store_modules sm
JOIN stores s ON sm.store_id = s.id
JOIN modules m ON sm.module_id = m.id
WHERE s.vendor_id = 3
ORDER BY sm.store_id, sm.module_id;
```

## 📋 Testing Checklist

### Store 1 (Grocery + Beauty) - Default
- [ ] Module switcher appears in header
- [ ] Can see both Grocery and Beauty in switcher dropdown
- [ ] Sidebar shows Grocery sections
- [ ] Can switch to Beauty dashboard
- [ ] Sidebar shows Beauty sections when on Beauty dashboard
- [ ] Can switch back to Grocery dashboard

### Store 2 (Beauty + Grocery)
- [ ] Module switcher appears in header
- [ ] Can see both Beauty and Grocery in switcher dropdown
- [ ] Sidebar shows Beauty sections (primary)
- [ ] Can switch to Grocery dashboard
- [ ] Sidebar shows Grocery sections when on Grocery dashboard
- [ ] Can switch back to Beauty dashboard

### Store 3 (Grocery Only)
- [ ] Module switcher does NOT appear (only 1 module)
- [ ] Sidebar shows only Grocery sections
- [ ] No Beauty sections visible
- [ ] Cannot access Beauty routes (should return 404)

## 🎯 Admin Testing

### Test Module Access Management
1. Login as admin
2. Navigate to: **Admin → Vendors → View Store → Settings tab**
3. Find **"Module Access Control"** section
4. For each store, test:
   - Enabling additional module access
   - Disabling additional module access
   - Verify primary module cannot be disabled
   - Verify changes persist after page reload

## 📝 Notes

- The system uses `stores[0]` (first store) by default for vendor login
- To test different stores, use vendor employees or temporarily reorder stores
- Module access is stored in `store_modules` pivot table
- Primary module access is always enabled (cannot be disabled)
- Module switcher only appears when vendor has access to 2+ modules

## 🔧 Quick Store Switch (For Testing)

If you need to quickly switch which store is used by default, run:

```bash
php artisan tinker
```

Then:
```php
// Get the vendor
$vendor = \App\Models\Vendor::find(3);

// Get stores
$store1 = \App\Models\Store::find(8); // Grocery + Beauty
$store2 = \App\Models\Store::find(9); // Beauty + Grocery  
$store3 = \App\Models\Store::find(10); // Grocery only

// Temporarily swap store IDs to make a different store first
// (This is just for testing - restore after testing)
```

---

**Created:** 2025-11-30  
**Vendor ID:** 3  
**Total Stores:** 3

