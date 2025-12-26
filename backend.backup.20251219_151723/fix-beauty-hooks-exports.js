#!/usr/bin/env node

/**
 * Fix Beauty Booking Module Hook Exports
 * Converts all default exports to named exports in React Query hooks
 * 
 * Usage: node fix-beauty-hooks-exports.js
 */

const fs = require('fs');
const path = require('path');

const HOOKS_DIR = '/var/www/6ammart-react/src/api-manage/hooks/react-query/beauty/vendor';
const BACKUP_DIR = '/tmp/beauty-vendor-hooks-backup';

// List of hooks that need to be converted
const HOOKS_TO_FIX = [
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
  'usePurchaseSubscription.js',
];

/**
 * Convert export default function to export const
 * Converts: export default function useHookName(params) { ... }
 * To: export const useHookName = (params) => { ... }
 */
function convertDefaultExportToNamedExport(content) {
  // Pattern 1: export default function useHookName(params, enabled = true) { ... }
  const pattern1 = /export\s+default\s+function\s+(\w+)\s*\(([^)]*)\)\s*\{/g;
  
  let converted = content.replace(pattern1, (match, hookName, params) => {
    return `export const ${hookName} = (${params}) => {`;
  });
  
  // Pattern 2: export default function useHookName() { ... }
  const pattern2 = /export\s+default\s+function\s+(\w+)\s*\(\s*\)\s*\{/g;
  
  converted = converted.replace(pattern2, (match, hookName) => {
    return `export const ${hookName} = () => {`;
  });
  
  return converted;
}

/**
 * Process a single hook file
 */
function processHookFile(filePath) {
  try {
    console.log(`Processing: ${path.basename(filePath)}`);
    
    // Read file content
    let content = fs.readFileSync(filePath, 'utf8');
    
    // Check if file has default export
    if (!content.includes('export default')) {
      console.log(`  ⏭️  Skipping: Already uses named export or no default export found`);
      return { processed: false, skipped: true };
    }
    
    // Convert default export to named export
    const converted = convertDefaultExportToNamedExport(content);
    
    // Check if conversion actually happened
    if (converted === content) {
      console.log(`  ⚠️  Warning: No changes detected`);
      return { processed: false, skipped: true };
    }
    
    // Write converted content back
    fs.writeFileSync(filePath, converted, 'utf8');
    console.log(`  ✅ Converted: Default export → Named export`);
    
    return { processed: true, skipped: false };
  } catch (error) {
    console.error(`  ❌ Error processing ${filePath}:`, error.message);
    return { processed: false, error: error.message };
  }
}

/**
 * Main function
 */
function main() {
  console.log('🚀 Starting Beauty Booking Hooks Export Conversion\n');
  
  // Check if hooks directory exists
  if (!fs.existsSync(HOOKS_DIR)) {
    console.error(`❌ Error: Hooks directory not found: ${HOOKS_DIR}`);
    process.exit(1);
  }
  
  // Create backup
  console.log('📦 Creating backup...');
  if (fs.existsSync(BACKUP_DIR)) {
    console.log(`  ⚠️  Backup directory already exists: ${BACKUP_DIR}`);
    console.log(`  ℹ️  Skipping backup (remove it manually if you want a fresh backup)`);
  } else {
    try {
      fs.mkdirSync(BACKUP_DIR, { recursive: true });
      fs.cpSync(HOOKS_DIR, BACKUP_DIR, { recursive: true });
      console.log(`  ✅ Backup created: ${BACKUP_DIR}`);
    } catch (error) {
      console.error(`  ❌ Failed to create backup:`, error.message);
      console.log(`  ⚠️  Continuing without backup...`);
    }
  }
  
  console.log('\n🔄 Processing hook files...\n');
  
  let stats = {
    processed: 0,
    skipped: 0,
    errors: 0,
  };
  
  // Process each hook file
  HOOKS_TO_FIX.forEach((hookFile) => {
    const filePath = path.join(HOOKS_DIR, hookFile);
    
    if (!fs.existsSync(filePath)) {
      console.log(`⚠️  File not found: ${hookFile}`);
      stats.skipped++;
      return;
    }
    
    const result = processHookFile(filePath);
    
    if (result.processed) {
      stats.processed++;
    } else if (result.skipped) {
      stats.skipped++;
    } else if (result.error) {
      stats.errors++;
    }
  });
  
  // Summary
  console.log('\n' + '='.repeat(50));
  console.log('📊 Summary:');
  console.log(`  ✅ Processed: ${stats.processed}`);
  console.log(`  ⏭️  Skipped: ${stats.skipped}`);
  console.log(`  ❌ Errors: ${stats.errors}`);
  console.log('='.repeat(50));
  
  // Verification
  console.log('\n🔍 Verifying changes...\n');
  
  const remainingDefaults = [];
  HOOKS_TO_FIX.forEach((hookFile) => {
    const filePath = path.join(HOOKS_DIR, hookFile);
    if (fs.existsSync(filePath)) {
      const content = fs.readFileSync(filePath, 'utf8');
      if (content.includes('export default')) {
        remainingDefaults.push(hookFile);
      }
    }
  });
  
  if (remainingDefaults.length > 0) {
    console.log('⚠️  Warning: Some files still contain default exports:');
    remainingDefaults.forEach(file => console.log(`  - ${file}`));
  } else {
    console.log('✅ All hooks successfully converted to named exports!');
  }
  
  console.log('\n✨ Conversion complete!');
  console.log('\n📝 Next steps:');
  console.log('  1. Run: cd /var/www/6ammart-react && npm run build');
  console.log('  2. Check for any remaining import errors');
  console.log('  3. If build succeeds: pm2 restart 6ammart-react');
}

// Run main function
main();

