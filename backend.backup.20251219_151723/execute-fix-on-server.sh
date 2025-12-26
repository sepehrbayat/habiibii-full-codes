#!/usr/bin/env expect

# Expect script to automate SSH connection and fix execution
# Usage: ./execute-fix-on-server.sh

set timeout 300
set server "193.162.129.214"
set user "root"
set password "H161t5dzCG"
set react_dir "/var/www/6ammart-react"
set hooks_dir "$react_dir/src/api-manage/hooks/react-query/beauty/vendor"

# Spawn SSH connection
spawn ssh -o StrictHostKeyChecking=no $user@$server

expect {
    "password:" {
        send "$password\r"
    }
    "yes/no" {
        send "yes\r"
        expect "password:"
        send "$password\r"
    }
    timeout {
        puts "Connection timeout"
        exit 1
    }
}

expect {
    "# " {
        puts "Connected successfully"
    }
    "$ " {
        puts "Connected successfully"
    }
    timeout {
        puts "Login timeout"
        exit 1
    }
}

# Step 1: Create backup
send "echo '📦 Creating backup...'\r"
expect "# "
send "BACKUP_DIR=\"/tmp/beauty-vendor-hooks-backup-\$(date +%Y%m%d-%H%M%S)\"\r"
expect "# "
send "cp -r $hooks_dir \$BACKUP_DIR && echo \"✅ Backup created: \$BACKUP_DIR\" || echo \"❌ Backup failed\"\r"
expect "# "

# Step 2: Convert hooks using inline Node.js script
send "echo '🔄 Converting hooks...'\r"
expect "# "

# Create temporary conversion script
send "cat > /tmp/convert-hooks.js << 'EOFNODE'
const fs = require('fs');
const path = require('path');

const hooksDir = '$hooks_dir';
const hooks = [
  'useGetBadgeStatus.js', 'useGetCalendarAvailability.js', 'useGetCampaignStats.js',
  'useGetPackageUsageStats.js', 'useGetPayoutSummary.js', 'useGetPointsHistory.js',
  'useGetRedemptionHistory.js', 'useGetServiceDetails.js', 'useGetStaffDetails.js',
  'useGetSubscriptionHistory.js', 'useGetSubscriptionPlans.js', 'useGetTransactionHistory.js',
  'useGetVendorBookingDetails.js', 'useGetVendorBookings.js', 'useGetVendorGiftCards.js',
  'useGetVendorLoyaltyCampaigns.js', 'useGetVendorPackages.js', 'useGetVendorProfile.js',
  'useGetVendorRetailOrders.js', 'useGetVendorRetailProducts.js', 'useGetVendorServices.js',
  'useGetVendorStaff.js', 'useManageHolidays.js', 'usePurchaseSubscription.js'
];

let processed = 0;
let skipped = 0;

hooks.forEach(hookFile => {
  const filePath = path.join(hooksDir, hookFile);
  if (!fs.existsSync(filePath)) {
    console.log(\`⚠️  File not found: \${hookFile}\`);
    skipped++;
    return;
  }
  
  let content = fs.readFileSync(filePath, 'utf8');
  
  if (!content.includes('export default')) {
    skipped++;
    return;
  }
  
  // Convert: export default function hookName(params) { -> export const hookName = (params) => {
  content = content.replace(/export\\s+default\\s+function\\s+(\\w+)\\s*\\(([^)]*)\\)\\s*\\{/g, 'export const $1 = ($2) => {');
  
  // Convert: export default function hookName() { -> export const hookName = () => {
  content = content.replace(/export\\s+default\\s+function\\s+(\\w+)\\s*\\(\\s*\\)\\s*\\{/g, 'export const $1 = () => {');
  
  fs.writeFileSync(filePath, content, 'utf8');
  console.log(\`✅ Converted: \${hookFile}\`);
  processed++;
});

console.log(\`\\n📊 Summary: Processed \${processed}, Skipped \${skipped}\`);
EOFNODE
\r"
expect "# "

# Run the conversion script
send "cd $react_dir && node /tmp/convert-hooks.js\r"
expect "# "

# Step 3: Verify conversion
send "echo '🔍 Verifying conversion...'\r"
expect "# "
send "REMAINING=\$(grep -r 'export default' $hooks_dir/*.js 2>/dev/null | wc -l)\r"
expect "# "
send "if [ \"\$REMAINING\" -eq 0 ]; then echo '✅ All hooks converted successfully!'; else echo \"⚠️  Warning: \$REMAINING files still have default exports\"; fi\r"
expect "# "

# Step 4: Test build (optional, can be commented out if takes too long)
send "echo '🧪 Testing build...'\r"
expect "# "
send "cd $react_dir && timeout 300 npm run build 2>&1 | grep -E 'error|Error|ERROR|Attempted import error' | head -20 || echo '✅ No import errors found (or build in progress)'\r"
expect "# "

# Step 5: Summary
send "echo '✨ Fix complete! Next: pm2 restart 6ammart-react'\r"
expect "# "

send "exit\r"
expect eof


