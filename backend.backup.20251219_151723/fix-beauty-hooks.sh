#!/bin/bash

# Fix Beauty Booking Module Import Errors
# This script converts all default exports to named exports in React Query hooks

set -e  # Exit on error

REACT_APP_DIR="/var/www/6ammart-react"
HOOKS_DIR="$REACT_APP_DIR/src/api-manage/hooks/react-query/beauty/vendor"
BACKUP_DIR="/tmp/beauty-vendor-hooks-backup-$(date +%Y%m%d-%H%M%S)"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "🚀 Beauty Booking Hooks Export Fix Script"
echo "=========================================="
echo ""

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then 
   echo "⚠️  Warning: Not running as root. You may need sudo for some operations."
fi

# Step 1: Navigate to React app directory
echo "📂 Step 1: Checking React app directory..."
if [ ! -d "$REACT_APP_DIR" ]; then
    echo "❌ Error: React app directory not found: $REACT_APP_DIR"
    exit 1
fi
echo "✅ React app directory found"
echo ""

# Step 2: Check hooks directory
echo "📂 Step 2: Checking hooks directory..."
if [ ! -d "$HOOKS_DIR" ]; then
    echo "❌ Error: Hooks directory not found: $HOOKS_DIR"
    exit 1
fi
echo "✅ Hooks directory found"
echo ""

# Step 3: Create backup
echo "📦 Step 3: Creating backup..."
mkdir -p "$(dirname "$BACKUP_DIR")"
if [ -d "$HOOKS_DIR" ]; then
    cp -r "$HOOKS_DIR" "$BACKUP_DIR"
    echo "✅ Backup created: $BACKUP_DIR"
else
    echo "❌ Error: Cannot create backup"
    exit 1
fi
echo ""

# Step 4: Check if Node.js script exists
echo "🔍 Step 4: Checking for conversion script..."
if [ -f "$SCRIPT_DIR/fix-beauty-hooks-exports.js" ]; then
    CONVERSION_SCRIPT="$SCRIPT_DIR/fix-beauty-hooks-exports.js"
elif [ -f "./fix-beauty-hooks-exports.js" ]; then
    CONVERSION_SCRIPT="./fix-beauty-hooks-exports.js"
else
    echo "❌ Error: fix-beauty-hooks-exports.js not found"
    echo "   Please ensure the script is in the same directory or provide the path"
    exit 1
fi
echo "✅ Conversion script found: $CONVERSION_SCRIPT"
echo ""

# Step 5: Run conversion script
echo "🔄 Step 5: Running conversion script..."
cd "$REACT_APP_DIR"
node "$CONVERSION_SCRIPT"
CONVERSION_EXIT_CODE=$?

if [ $CONVERSION_EXIT_CODE -ne 0 ]; then
    echo ""
    echo "❌ Error: Conversion script failed with exit code $CONVERSION_EXIT_CODE"
    echo "🔄 Restoring from backup..."
    rm -rf "$HOOKS_DIR"
    cp -r "$BACKUP_DIR" "$HOOKS_DIR"
    echo "✅ Backup restored"
    exit 1
fi
echo ""

# Step 6: Verify no default exports remain
echo "🔍 Step 6: Verifying conversion..."
REMAINING_DEFAULTS=$(grep -r "export default" "$HOOKS_DIR"/*.js 2>/dev/null | wc -l || echo "0")

if [ "$REMAINING_DEFAULTS" -gt 0 ]; then
    echo "⚠️  Warning: Found $REMAINING_DEFAULTS files with 'export default'"
    echo "   Files with remaining default exports:"
    grep -l "export default" "$HOOKS_DIR"/*.js 2>/dev/null | xargs -I {} basename {} || true
else
    echo "✅ No default exports found - all hooks converted!"
fi
echo ""

# Step 7: Check component imports (optional verification)
echo "🔍 Step 7: Checking component imports..."
COMPONENTS_DIR="$REACT_APP_DIR/src/components/home/module-wise-components/beauty/vendor"
if [ -d "$COMPONENTS_DIR" ]; then
    DEFAULT_IMPORTS=$(grep -r "import.*from.*beauty/vendor" "$COMPONENTS_DIR"/*.js 2>/dev/null | grep -v "{" | wc -l || echo "0")
    
    if [ "$DEFAULT_IMPORTS" -gt 0 ]; then
        echo "⚠️  Warning: Found $DEFAULT_IMPORTS component files with default imports"
        echo "   These should be converted to named imports"
    else
        echo "✅ All component imports appear to use named imports"
    fi
else
    echo "⚠️  Components directory not found: $COMPONENTS_DIR"
fi
echo ""

# Step 8: Summary
echo "=========================================="
echo "✨ Conversion Complete!"
echo ""
echo "📝 Next Steps:"
echo "  1. Test build: cd $REACT_APP_DIR && npm run build"
echo "  2. Check for errors: npm run build 2>&1 | grep -i error"
echo "  3. If build succeeds: pm2 restart 6ammart-react"
echo ""
echo "💾 Backup location: $BACKUP_DIR"
echo "   (You can remove this after verifying the build works)"
echo "=========================================="

