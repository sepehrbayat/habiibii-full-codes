#!/bin/bash

# Fix React Error #31: Objects are not valid as a React child
# This script fixes all identified files on the remote server

set -e

# Server configuration
SERVER="root@193.162.129.214"
PASSWORD="H161t5dzCG"
SSH_CMD="sshpass -p '$PASSWORD' ssh -o StrictHostKeyChecking=no $SERVER"
SCP_CMD="sshpass -p '$PASSWORD' scp -o StrictHostKeyChecking=no"
REACT_DIR="/var/www/6ammart-react"

echo "🔧 Starting React Error #31 Fix Process..."
echo "=========================================="

# Function to backup file
backup_file() {
    local file_path=$1
    echo "📦 Backing up: $file_path"
    $SSH_CMD "cp $file_path ${file_path}.backup.$(date +%Y%m%d_%H%M%S)" || true
}

# Function to check if file exists
check_file() {
    local file_path=$1
    if $SSH_CMD "test -f $file_path"; then
        echo "✅ Found: $file_path"
        return 0
    else
        echo "❌ Not found: $file_path"
        return 1
    fi
}

# Function to create safe helperText utility
create_helper_utility() {
    echo "📝 Creating safe rendering utility..."
    
    $SSH_CMD "cat > $REACT_DIR/src/utils/safeRender.js << 'EOF'
/**
 * Safe Rendering Utilities
 * Prevents React Error #31: Objects are not valid as a React child
 */

/**
 * Safely convert any value to string for rendering
 * @param {any} value - Value to convert
 * @returns {string} Safe string representation
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
 * @param {any} error - Error value (string, object, or null)
 * @param {boolean} touched - Whether field was touched
 * @returns {string} Safe error message or empty string
 */
export const safeHelperText = (error, touched = true) => {
  if (!touched || !error) return '';
  return safeString(error);
};

/**
 * Safely render value in JSX
 * @param {any} value - Value to render
 * @returns {string|number|null} Safe value for rendering
 */
export const safeRender = (value) => {
  if (value === null || value === undefined) return null;
  if (typeof value === 'object') {
    return safeString(value);
  }
  return value;
};
EOF"
    echo "✅ Utility created"
}

# Fix 1: BasicInformationForm.js
fix_basic_information_form() {
    local file="$REACT_DIR/src/components/profile/basic-information/BasicInformationForm.js"
    
    if ! check_file "$file"; then
        echo "⚠️  Skipping BasicInformationForm.js (file not found)"
        return
    fi
    
    echo ""
    echo "🔨 Fixing BasicInformationForm.js..."
    backup_file "$file"
    
    # Download file
    $SCP_CMD "$SERVER:$file" ./temp_basic_form.js
    
    # Apply fixes using Node.js script for complex replacements
    node << 'NODE_SCRIPT'
const fs = require('fs');

let content = fs.readFileSync('./temp_basic_form.js', 'utf8');

// Fix helperText pattern - replace all instances
// Pattern: helperText={formik.touched.field && formik.errors.field}
const helperTextPattern = /helperText=\{([^}]+\.touched\.(\w+))\s*&&\s*([^}]+\.errors\.\2)\}/g;

content = content.replace(helperTextPattern, (match, touched, fieldName, errors) => {
  return `helperText={
    ${touched} && ${errors}
      ? (typeof ${errors} === 'string'
          ? ${errors}
          : ${errors}?.message || String(${errors}))
      : ""
  }`;
});

// Also fix simpler patterns like helperText={errors.field}
const simpleHelperTextPattern = /helperText=\{([^}]+\.errors\.\w+)\}/g;
content = content.replace(simpleHelperTextPattern, (match, errorPath) => {
  return `helperText={
    ${errorPath}
      ? (typeof ${errorPath} === 'string'
          ? ${errorPath}
          : ${errorPath}?.message || String(${errorPath}))
      : ""
  }`;
});

// Add import for safeHelperText at top if not exists
if (!content.includes('safeHelperText')) {
  const importMatch = content.match(/^import\s+.*from\s+['"]/m);
  if (importMatch) {
    const lastImportIndex = content.lastIndexOf('import');
    const lastImportEnd = content.indexOf('\n', lastImportIndex);
    content = content.slice(0, lastImportEnd + 1) + 
              "import { safeHelperText } from '../../../utils/safeRender';\n" +
              content.slice(lastImportEnd + 1);
  }
}

fs.writeFileSync('./temp_basic_form.js', content);
console.log('✅ BasicInformationForm.js fixed');
NODE_SCRIPT
    
    # Upload fixed file
    $SCP_CMD ./temp_basic_form.js "$SERVER:$file"
    rm -f ./temp_basic_form.js
    echo "✅ BasicInformationForm.js fixed and uploaded"
}

# Fix 2: Menu.js (AccountPopover)
fix_menu_component() {
    local file="$REACT_DIR/src/components/header/second-navbar/account-popover/Menu.js"
    
    if ! check_file "$file"; then
        echo "⚠️  Skipping Menu.js (file not found)"
        return
    fi
    
    echo ""
    echo "🔨 Fixing Menu.js..."
    backup_file "$file"
    
    # Download file
    $SCP_CMD "$SERVER:$file" ./temp_menu.js
    
    # Apply fixes
    node << 'NODE_SCRIPT'
const fs = require('fs');

let content = fs.readFileSync('./temp_menu.js', 'utf8');

// Add safe rendering utility import
if (!content.includes('safeRender')) {
  const importMatch = content.match(/^import\s+.*from\s+['"]/m);
  if (importMatch) {
    const lastImportIndex = content.lastIndexOf('import');
    const lastImportEnd = content.indexOf('\n', lastImportIndex);
    content = content.slice(0, lastImportEnd + 1) + 
              "import { safeString, safeRender } from '../../../../utils/safeRender';\n" +
              content.slice(lastImportEnd + 1);
  }
}

// Fix menu.name rendering - find patterns like {menu.name} or {item.name}
content = content.replace(/\{menu\.name\}/g, '{safeString(menu?.name || "")}');
content = content.replace(/\{item\.name\}/g, '{safeString(item?.name || "")}');

// Fix t() calls - ensure they receive strings
content = content.replace(/t\(([^)]+)\)/g, (match, arg) => {
  // If arg is already a string literal, keep it
  if (arg.match(/^['"]/)) return match;
  // Otherwise wrap with safeString
  return `t(safeString(${arg}))`;
});

// Fix icon rendering
content = content.replace(/\{([^}]+)\.icon\}/g, (match, obj) => {
  return `{${obj}?.icon && React.isValidElement(${obj}.icon) ? ${obj}.icon : null}`;
});

fs.writeFileSync('./temp_menu.js', content);
console.log('✅ Menu.js fixed');
NODE_SCRIPT
    
    # Upload fixed file
    $SCP_CMD ./temp_menu.js "$SERVER:$file"
    rm -f ./temp_menu.js
    echo "✅ Menu.js fixed and uploaded"
}

# Fix 3: ProfileTabPopover.js
fix_profile_tab_popover() {
    local file="$REACT_DIR/src/components/profile/ProfileTabPopover.js"
    
    if ! check_file "$file"; then
        echo "⚠️  Skipping ProfileTabPopover.js (file not found)"
        return
    fi
    
    echo ""
    echo "🔨 Fixing ProfileTabPopover.js..."
    backup_file "$file"
    
    # Download file
    $SCP_CMD "$SERVER:$file" ./temp_profile_tab.js
    
    # Apply fixes
    node << 'NODE_SCRIPT'
const fs = require('fs');

let content = fs.readFileSync('./temp_profile_tab.js', 'utf8');

// Add safe rendering utility import
if (!content.includes('safeString')) {
  const importMatch = content.match(/^import\s+.*from\s+['"]/m);
  if (importMatch) {
    const lastImportIndex = content.lastIndexOf('import');
    const lastImportEnd = content.indexOf('\n', lastImportIndex);
    content = content.slice(0, lastImportEnd + 1) + 
              "import { safeString } from '../../utils/safeRender';\n" +
              content.slice(lastImportEnd + 1);
  }
}

// Fix menu.name.replace() pattern
// Find patterns like: menu.name.replace("-", " ") or menu?.name.replace(...)
content = content.replace(/(menu\??\.name)\s*\.replace\(/g, (match, menuName) => {
  return `(typeof ${menuName} === 'string' ? ${menuName} : safeString(${menuName} || "")).replace(`;
});

// Also fix direct menu.name usage
content = content.replace(/\{menu\.name\}/g, '{safeString(menu?.name || "")}');
content = content.replace(/\{menu\?\.name\}/g, '{safeString(menu?.name || "")}');

fs.writeFileSync('./temp_profile_tab.js', content);
console.log('✅ ProfileTabPopover.js fixed');
NODE_SCRIPT
    
    # Upload fixed file
    $SCP_CMD ./temp_profile_tab.js "$SERVER:$file"
    rm -f ./temp_profile_tab.js
    echo "✅ ProfileTabPopover.js fixed and uploaded"
}

# Fix 4: WalletBoxComponent.js
fix_wallet_component() {
    local file="$REACT_DIR/src/components/wallet/WalletBoxComponent.js"
    
    if ! check_file "$file"; then
        echo "⚠️  Skipping WalletBoxComponent.js (file not found)"
        return
    fi
    
    echo ""
    echo "🔨 Fixing WalletBoxComponent.js..."
    backup_file "$file"
    
    # Download file
    $SCP_CMD "$SERVER:$file" ./temp_wallet.js
    
    # Apply fixes
    node << 'NODE_SCRIPT'
const fs = require('fs');

let content = fs.readFileSync('./temp_wallet.js', 'utf8');

// Add safe rendering utility import
if (!content.includes('safeString')) {
  const importMatch = content.match(/^import\s+.*from\s+['"]/m);
  if (importMatch) {
    const lastImportIndex = content.lastIndexOf('import');
    const lastImportEnd = content.indexOf('\n', lastImportIndex);
    content = content.slice(0, lastImportEnd + 1) + 
              "import { safeString } from '../../utils/safeRender';\n" +
              content.slice(lastImportEnd + 1);
  }
}

// Fix balance rendering - find getBalanceDisplay or direct balance usage
// Ensure getBalanceDisplay handles objects
if (content.includes('getBalanceDisplay')) {
  content = content.replace(/getBalanceDisplay\s*=\s*\([^)]*\)\s*=>\s*\{[^}]*\}/s, (match) => {
    if (match.includes('typeof') && match.includes('object')) {
      return match; // Already fixed
    }
    // Add object handling
    return match.replace(/(return\s+)([^;]+);/s, (retMatch, ret, value) => {
      return `${ret}typeof ${value} === 'object' ? safeString(${value}) : ${value};`;
    });
  });
}

// Fix direct balance rendering
content = content.replace(/\{balance\}/g, '{safeString(balance)}');
content = content.replace(/\{([^}]+)\.balance\}/g, '{safeString($1.balance)}');

fs.writeFileSync('./temp_wallet.js', content);
console.log('✅ WalletBoxComponent.js fixed');
NODE_SCRIPT
    
    # Upload fixed file
    $SCP_CMD ./temp_wallet.js "$SERVER:$file"
    rm -f ./temp_wallet.js
    echo "✅ WalletBoxComponent.js fixed and uploaded"
}

# Search for other problematic patterns
search_other_issues() {
    echo ""
    echo "🔍 Searching for other potential issues..."
    
    $SSH_CMD "cd $REACT_DIR && grep -rn 'helperText={' src/ --include='*.js' --include='*.jsx' | head -20" || true
    $SSH_CMD "cd $REACT_DIR && grep -rn '{error}' src/ --include='*.js' --include='*.jsx' | head -20" || true
    $SSH_CMD "cd $REACT_DIR && grep -rn '{res}' src/ --include='*.js' --include='*.jsx' | head -20" || true
}

# Main execution
main() {
    echo ""
    echo "Step 1: Creating safe rendering utility..."
    create_helper_utility
    
    echo ""
    echo "Step 2: Fixing identified files..."
    fix_basic_information_form
    fix_menu_component
    fix_profile_tab_popover
    fix_wallet_component
    
    echo ""
    echo "Step 3: Searching for other issues..."
    search_other_issues
    
    echo ""
    echo "=========================================="
    echo "✅ Fix process completed!"
    echo ""
    echo "Next steps:"
    echo "1. Rebuild: cd $REACT_DIR && npm run build"
    echo "2. Restart: pm2 restart 6ammart-react"
    echo "3. Check logs: pm2 logs 6ammart-react"
    echo ""
    echo "To rebuild and restart now, run:"
    echo "  $SSH_CMD \"cd $REACT_DIR && npm run build && pm2 restart 6ammart-react\""
}

# Run main
main

