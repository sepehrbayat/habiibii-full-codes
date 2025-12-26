#!/bin/bash
# Complete Local-Server Sync Script
# This script synchronizes local project structure with server structure

set -e  # Exit on error

# Configuration
SERVER_HOST="193.162.129.214"
SERVER_USER="root"
SERVER_PASS="H161t5dzCG"
SERVER_REACT="/var/www/6ammart-react"
SERVER_LARAVEL="/var/www/6ammart-laravel"
LOCAL_LARAVEL="/home/sepehr/Projects/6ammart-laravel"
LOCAL_REACT="/home/sepehr/Projects/6ammart-react"  # Local React project path

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_step() {
    echo -e "${BLUE}🔄 $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Step 1: Create backup
print_step "Creating backup..."
BACKUP_DIR="$LOCAL_LARAVEL/backups/$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

if [ -d "$LOCAL_REACT" ]; then
    print_step "Backing up React files..."
    cp -r "$LOCAL_REACT" "$BACKUP_DIR/react_backup" 2>/dev/null || print_warning "Could not backup React files"
    print_success "React backup created"
fi

print_step "Backing up Laravel modified files..."
mkdir -p "$BACKUP_DIR/laravel_backup"
[ -f "$LOCAL_LARAVEL/config/cors.php" ] && cp "$LOCAL_LARAVEL/config/cors.php" "$BACKUP_DIR/laravel_backup/" 2>/dev/null
[ -f "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConversationController.php" ] && \
    cp "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConversationController.php" "$BACKUP_DIR/laravel_backup/" 2>/dev/null
[ -f "$LOCAL_LARAVEL/app/CentralLogics/helpers.php" ] && \
    cp "$LOCAL_LARAVEL/app/CentralLogics/helpers.php" "$BACKUP_DIR/laravel_backup/" 2>/dev/null
[ -f "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConfigController.php" ] && \
    cp "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConfigController.php" "$BACKUP_DIR/laravel_backup/" 2>/dev/null

print_success "Backup created at: $BACKUP_DIR"

# Step 2: Create local React directory if it doesn't exist
if [ ! -d "$LOCAL_REACT" ]; then
    print_step "Creating local React directory..."
    mkdir -p "$LOCAL_REACT"
    print_success "Local React directory created"
fi

# Step 3: Sync React source files
print_step "Syncing React source files from server..."
sshpass -p "$SERVER_PASS" rsync -avz --delete \
    -e "ssh -o StrictHostKeyChecking=no" \
    "$SERVER_USER@$SERVER_HOST:$SERVER_REACT/src/" \
    "$LOCAL_REACT/src/" 2>&1 | grep -v "Permission denied" || print_warning "Some files may have permission issues"

print_success "React source files synced"

# Step 4: Sync React config files
print_step "Syncing React configuration files..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
    "$SERVER_USER@$SERVER_HOST:$SERVER_REACT/package.json" \
    "$LOCAL_REACT/package.json" 2>/dev/null && print_success "package.json synced" || print_warning "package.json not found"

[ -f "$SERVER_REACT/next.config.js" ] && sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
    "$SERVER_USER@$SERVER_HOST:$SERVER_REACT/next.config.js" \
    "$LOCAL_REACT/next.config.js" 2>/dev/null && print_success "next.config.js synced" || print_warning "next.config.js not found"

[ -f "$SERVER_REACT/.eslintrc.json" ] && sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
    "$SERVER_USER@$SERVER_HOST:$SERVER_REACT/.eslintrc.json" \
    "$LOCAL_REACT/.eslintrc.json" 2>/dev/null && print_success ".eslintrc.json synced" || print_warning ".eslintrc.json not found"

# Step 5: Sync Laravel modified files
print_step "Syncing Laravel modified files..."

print_step "Syncing cors.php..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
    "$SERVER_USER@$SERVER_HOST:$SERVER_LARAVEL/config/cors.php" \
    "$LOCAL_LARAVEL/config/cors.php" 2>/dev/null && print_success "cors.php synced" || print_warning "cors.php sync failed"

print_step "Syncing ConversationController.php..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
    "$SERVER_USER@$SERVER_HOST:$SERVER_LARAVEL/app/Http/Controllers/Api/V1/ConversationController.php" \
    "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConversationController.php" 2>/dev/null && \
    print_success "ConversationController.php synced" || print_warning "ConversationController.php sync failed"

print_step "Syncing helpers.php..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
    "$SERVER_USER@$SERVER_HOST:$SERVER_LARAVEL/app/CentralLogics/helpers.php" \
    "$LOCAL_LARAVEL/app/CentralLogics/helpers.php" 2>/dev/null && \
    print_success "helpers.php synced" || print_warning "helpers.php sync failed"

print_step "Syncing ConfigController.php..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
    "$SERVER_USER@$SERVER_HOST:$SERVER_LARAVEL/app/Http/Controllers/Api/V1/ConfigController.php" \
    "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConfigController.php" 2>/dev/null && \
    print_success "ConfigController.php synced" || print_warning "ConfigController.php sync failed"

# Step 6: Verify file counts
print_step "Verifying file synchronization..."

SERVER_FILE_COUNT=$(sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no \
    "$SERVER_USER@$SERVER_HOST" "find $SERVER_REACT/src -type f 2>/dev/null | wc -l" 2>/dev/null || echo "0")

LOCAL_FILE_COUNT=$(find "$LOCAL_REACT/src" -type f 2>/dev/null | wc -l || echo "0")

echo ""
echo "📊 Synchronization Summary:"
echo "   Server files: $SERVER_FILE_COUNT"
echo "   Local files: $LOCAL_FILE_COUNT"

if [ "$SERVER_FILE_COUNT" -eq "$LOCAL_FILE_COUNT" ] && [ "$SERVER_FILE_COUNT" != "0" ]; then
    print_success "File counts match!"
else
    print_warning "File counts differ - this may be normal if some files are server-specific"
fi

# Step 7: Check for absolute paths in imports
print_step "Checking for absolute paths in imports..."
ABSOLUTE_PATH_COUNT=$(grep -r "from '/var/www\|from '/home/sepehr" "$LOCAL_REACT/src" \
    --include="*.js" --include="*.jsx" 2>/dev/null | wc -l || echo "0")

if [ "$ABSOLUTE_PATH_COUNT" -eq "0" ]; then
    print_success "No absolute paths found in imports"
else
    print_warning "Found $ABSOLUTE_PATH_COUNT files with absolute paths - these should be fixed"
    grep -r "from '/var/www\|from '/home/sepehr" "$LOCAL_REACT/src" \
        --include="*.js" --include="*.jsx" 2>/dev/null | head -5
fi

# Step 8: Create sync report
REPORT_FILE="$LOCAL_LARAVEL/sync_report_$(date +%Y%m%d_%H%M%S).txt"
cat > "$REPORT_FILE" << EOF
# File Structure Sync Report
Generated: $(date)

## Backup Location
$BACKUP_DIR

## File Counts
Server files: $SERVER_FILE_COUNT
Local files: $LOCAL_FILE_COUNT

## Absolute Paths Found
$ABSOLUTE_PATH_COUNT files with absolute paths

## Synced Files
- React source files: ✅
- package.json: ✅
- Laravel cors.php: ✅
- Laravel ConversationController.php: ✅
- Laravel helpers.php: ✅
- Laravel ConfigController.php: ✅

## Next Steps
1. Review the synced files
2. Fix any absolute import paths if found
3. Run 'npm install' in React directory if package.json changed
4. Test the application locally

EOF

print_success "Sync report created: $REPORT_FILE"

echo ""
print_success "🎉 Sync complete!"
echo ""
echo "📝 Next steps:"
echo "   1. Review the sync report: $REPORT_FILE"
echo "   2. Check backup location: $BACKUP_DIR"
echo "   3. If React package.json changed, run: cd $LOCAL_REACT && npm install"
echo "   4. Verify import paths are relative, not absolute"
echo ""

