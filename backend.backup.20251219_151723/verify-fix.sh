#!/bin/bash

# Verification script for Beauty Booking hooks export fix
# This script checks if all conversions were successful

REACT_APP_DIR="/var/www/6ammart-react"
HOOKS_DIR="$REACT_APP_DIR/src/api-manage/hooks/react-query/beauty/vendor"
COMPONENTS_DIR="$REACT_APP_DIR/src/components/home/module-wise-components/beauty/vendor"

echo "🔍 Beauty Booking Hooks Export Verification"
echo "============================================="
echo ""

# Check 1: Verify no default exports in hooks
echo "1️⃣  Checking for default exports in hooks..."
DEFAULT_EXPORTS=$(grep -r "export default" "$HOOKS_DIR"/*.js 2>/dev/null | wc -l || echo "0")

if [ "$DEFAULT_EXPORTS" -eq 0 ]; then
    echo "   ✅ No default exports found in hooks"
else
    echo "   ❌ Found $DEFAULT_EXPORTS default exports:"
    grep -l "export default" "$HOOKS_DIR"/*.js 2>/dev/null | while read file; do
        echo "      - $(basename "$file")"
    done
fi
echo ""

# Check 2: Verify named exports exist
echo "2️⃣  Checking for named exports in hooks..."
NAMED_EXPORTS=$(grep -r "export const" "$HOOKS_DIR"/*.js 2>/dev/null | wc -l || echo "0")
echo "   ℹ️  Found $NAMED_EXPORTS named exports"
echo ""

# Check 3: Verify component imports
echo "3️⃣  Checking component imports..."
if [ -d "$COMPONENTS_DIR" ]; then
    DEFAULT_IMPORTS=$(grep -r "import.*from.*beauty/vendor" "$COMPONENTS_DIR"/*.js 2>/dev/null | grep -v "{" | wc -l || echo "0")
    
    if [ "$DEFAULT_IMPORTS" -eq 0 ]; then
        echo "   ✅ All component imports use named imports"
    else
        echo "   ⚠️  Found $DEFAULT_IMPORTS default imports in components:"
        grep -r "import.*from.*beauty/vendor" "$COMPONENTS_DIR"/*.js 2>/dev/null | grep -v "{" | while read line; do
            echo "      $line"
        done
    fi
else
    echo "   ⚠️  Components directory not found"
fi
echo ""

# Check 4: List all hook files and their export type
echo "4️⃣  Hook files export status:"
for file in "$HOOKS_DIR"/*.js; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        if grep -q "export default" "$file" 2>/dev/null; then
            echo "   ❌ $filename - Still has default export"
        elif grep -q "export const" "$file" 2>/dev/null; then
            echo "   ✅ $filename - Uses named export"
        else
            echo "   ⚠️  $filename - No export found"
        fi
    fi
done
echo ""

# Summary
echo "============================================="
if [ "$DEFAULT_EXPORTS" -eq 0 ]; then
    echo "✅ Verification PASSED - All hooks use named exports"
    exit 0
else
    echo "❌ Verification FAILED - Some hooks still use default exports"
    exit 1
fi

