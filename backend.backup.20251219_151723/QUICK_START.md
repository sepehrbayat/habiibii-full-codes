# Quick Start - Fix Beauty Booking Hooks

## 🚀 Fastest Way to Fix

```bash
# 1. Upload scripts to server
scp fix-beauty-hooks-exports.js fix-beauty-hooks.sh verify-fix.sh root@193.162.129.214:/tmp/

# 2. SSH to server
ssh root@193.162.129.214
# Password: H161t5dzCG

# 3. Run fix
cd /tmp
chmod +x fix-beauty-hooks.sh verify-fix.sh
./fix-beauty-hooks.sh

# 4. Verify
./verify-fix.sh

# 5. Test build
cd /var/www/6ammart-react
npm run build

# 6. Restart
pm2 restart 6ammart-react
```

## 📋 What Gets Fixed

- ✅ 24 hooks converted from `export default function` → `export const`
- ✅ All components already use named imports (from previous fix)
- ✅ Build errors resolved

## 🔧 Files Created

1. **`fix-beauty-hooks-exports.js`** - Main conversion script (Node.js)
2. **`fix-beauty-hooks.sh`** - Orchestration script
3. **`verify-fix.sh`** - Verification script
4. **`fix-hooks-sed.sh`** - Alternative sed-based script
5. **`BEAUTY_HOOKS_FIX_README.md`** - Full documentation

## ⚠️ Important

- Backup is created automatically at `/tmp/beauty-vendor-hooks-backup-*`
- Scripts handle file permissions automatically
- All changes are reversible via backup

