#!/bin/bash
# Script to be executed on remote server

set -e

REACT_DIR="/var/www/6ammart-react"
HOOKS_DIR="$REACT_DIR/src/api-manage/hooks/react-query/beauty/vendor"
BACKUP_DIR="/tmp/beauty-vendor-hooks-backup-$(date +%Y%m%d-%H%M%S)"

echo "🚀 Starting Beauty Booking Hooks Export Fix"
echo "============================================="
echo ""

# Step 1: Create backup
echo "📦 Step 1: Creating backup..."
if [ -d "$HOOKS_DIR" ]; then
    cp -r "$HOOKS_DIR" "$BACKUP_DIR"
    echo "✅ Backup created: $BACKUP_DIR"
else
    echo "❌ Error: Hooks directory not found: $HOOKS_DIR"
    exit 1
fi
echo ""

# Step 2: Convert hooks
echo "🔄 Step 2: Converting hooks from default to named exports..."
cd "$REACT_DIR"

# Create inline Node.js conversion script
node << 'EOFNODE'
const fs = require('fs');
const path = require('path');

const hooksDir = '/var/www/6ammart-react/src/api-manage/hooks/react-query/beauty/vendor';
const hooks = [
  'useGetBadgeStatus.js',
  'useGetCalendarAvailability.js',
  'useGetCampaignStats.js',
  'useGetPackageUsageStats.js',
  'useGetPayoutSummary.js',
  'useGetPointsHistory.js',
  'useGetRedemptionHistory.js',
  'useGetServiceDetails.js',
  'useGetStaffDetails.js',
  'useGetSubscriptionHistory.js',
  'useGetSubscriptionPlans.js',
  'useGetTransactionHistory.js',
  'useGetVendorBookingDetails.js',
  'useGetVendorBookings.js',
  'useGetVendorGiftCards.js',
  'useGetVendorLoyaltyCampaigns.js',
  'useGetVendorPackages.js',
  'useGetVendorProfile.js',
  'useGetVendorRetailOrders.js',
  'useGetVendorRetailProducts.js',
  'useGetVendorServices.js',
  'useGetVendorStaff.js',
  'useManageHolidays.js',
  'usePurchaseSubscription.js'
];

let processed = 0;
let skipped = 0;
let errors = 0;

hooks.forEach(hookFile => {
  const filePath = path.join(hooksDir, hookFile);
  
  if (!fs.existsSync(filePath)) {
    console.log(`⚠️  File not found: ${hookFile}`);
    skipped++;
    return;
  }
  
  try {
    let content = fs.readFileSync(filePath, 'utf8');
    
    if (!content.includes('export default')) {
      console.log(`⏭️  Skipping ${hookFile}: No default export found`);
      skipped++;
      return;
    }
    
    // Convert: export default function hookName(params) { -> export const hookName = (params) => {
    const originalContent = content;
    content = content.replace(/export\s+default\s+function\s+(\w+)\s*\(([^)]*)\)\s*\{/g, 'export const $1 = ($2) => {');
    
    // Convert: export default function hookName() { -> export const hookName = () => {
    content = content.replace(/export\s+default\s+function\s+(\w+)\s*\(\s*\)\s*\{/g, 'export const $1 = () => {');
    
    if (content === originalContent) {
      console.log(`⚠️  No changes made to ${hookFile}`);
      skipped++;
      return;
    }
    
    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`✅ Converted: ${hookFile}`);
    processed++;
  } catch (error) {
    console.error(`❌ Error processing ${hookFile}: ${error.message}`);
    errors++;
  }
});

console.log('');
console.log('=============================================');
console.log('📊 Conversion Summary:');
console.log(`  ✅ Processed: ${processed}`);
console.log(`  ⏭️  Skipped: ${skipped}`);
console.log(`  ❌ Errors: ${errors}`);
console.log('=============================================');
EOFNODE

echo ""

# Step 3: Verify conversion
echo "🔍 Step 3: Verifying conversion..."
REMAINING=$(grep -r "export default" "$HOOKS_DIR"/*.js 2>/dev/null | wc -l || echo "0")

if [ "$REMAINING" -eq "0" ]; then
    echo "✅ All hooks successfully converted to named exports!"
else
    echo "⚠️  Warning: Found $REMAINING files with 'export default'"
    echo "   Files with remaining default exports:"
    grep -l "export default" "$HOOKS_DIR"/*.js 2>/dev/null | xargs -I {} basename {} || true
fi
echo ""

# Step 4: Check component imports
echo "🔍 Step 4: Checking component imports..."
COMPONENTS_DIR="$REACT_DIR/src/components/home/module-wise-components/beauty/vendor"
if [ -d "$COMPONENTS_DIR" ]; then
    DEFAULT_IMPORTS=$(grep -r "import.*from.*beauty/vendor" "$COMPONENTS_DIR"/*.js 2>/dev/null | grep -v "{" | wc -l || echo "0")
    if [ "$DEFAULT_IMPORTS" -eq "0" ]; then
        echo "✅ All component imports use named imports"
    else
        echo "⚠️  Found $DEFAULT_IMPORTS default imports in components"
    fi
else
    echo "⚠️  Components directory not found"
fi
echo ""

# Step 5: Test build
echo "🧪 Step 5: Testing build (this may take a few minutes)..."
cd "$REACT_DIR"
BUILD_OUTPUT=$(timeout 600 npm run build 2>&1 || echo "BUILD_TIMEOUT_OR_ERROR")
IMPORT_ERRORS=$(echo "$BUILD_OUTPUT" | grep -E "Attempted import error|error.*export" || echo "")

if [ -z "$IMPORT_ERRORS" ]; then
    echo "✅ No import errors found in build output!"
else
    echo "⚠️  Found import errors:"
    echo "$IMPORT_ERRORS" | head -20
fi
echo ""

# Final summary
echo "============================================="
echo "✨ Fix Complete!"
echo ""
echo "📝 Summary:"
echo "  💾 Backup: $BACKUP_DIR"
echo "  🔄 Hooks converted: See above"
echo "  🧪 Build test: See above"
echo ""
echo "📝 Next Steps:"
echo "  1. Review the build output above"
echo "  2. If build succeeded: pm2 restart 6ammart-react"
echo "  3. Monitor application logs: pm2 logs 6ammart-react"
echo "============================================="


