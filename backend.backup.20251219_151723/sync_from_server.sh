#!/bin/bash
# Complete Local-Server Sync Script
# This script synchronizes local project structure with server structure

set -e  # Exit on error

SERVER_HOST="193.162.129.214"
SERVER_USER="root"
SERVER_PASS="H161t5dzCG"
SERVER_REACT="/var/www/6ammart-react"
SERVER_LARAVEL="/var/www/6ammart-laravel"
LOCAL_LARAVEL="/home/sepehr/Projects/6ammart-laravel"
LOCAL_REACT="/home/sepehr/Projects/6ammart-react"

echo "🔄 Starting Local-Server Sync Process..."
echo ""

# Check if sshpass is installed
if ! command -v sshpass &> /dev/null; then
    echo "❌ sshpass is not installed. Installing..."
    sudo apt-get update && sudo apt-get install -y sshpass
fi

# Check if rsync is installed
if ! command -v rsync &> /dev/null; then
    echo "❌ rsync is not installed. Installing..."
    sudo apt-get update && sudo apt-get install -y rsync
fi

# Test SSH connection
echo "🔌 Testing SSH connection..."
if ! sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no -o ConnectTimeout=5 "$SERVER_USER@$SERVER_HOST" "echo 'Connected'" &>/dev/null; then
    echo "❌ Cannot connect to server. Please check:"
    echo "   - Server is accessible"
    echo "   - Credentials are correct"
    echo "   - Network connection"
    exit 1
fi
echo "✅ SSH connection successful"
echo ""

# Step 1: Create backup
echo "📦 Creating backup..."
BACKUP_DIR="$LOCAL_LARAVEL/backups/$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

if [ -d "$LOCAL_REACT" ]; then
    echo "   Backing up React project..."
    cp -r "$LOCAL_REACT" "$BACKUP_DIR/react_backup" 2>/dev/null || echo "   ⚠️  Could not backup React project"
fi

# Backup Laravel modified files
echo "   Backing up Laravel files..."
mkdir -p "$BACKUP_DIR/laravel_backup"
[ -f "$LOCAL_LARAVEL/config/cors.php" ] && cp "$LOCAL_LARAVEL/config/cors.php" "$BACKUP_DIR/laravel_backup/" 2>/dev/null || true
[ -f "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConversationController.php" ] && cp "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConversationController.php" "$BACKUP_DIR/laravel_backup/" 2>/dev/null || true
[ -f "$LOCAL_LARAVEL/app/CentralLogics/helpers.php" ] && cp "$LOCAL_LARAVEL/app/CentralLogics/helpers.php" "$BACKUP_DIR/laravel_backup/" 2>/dev/null || true
[ -f "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConfigController.php" ] && cp "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConfigController.php" "$BACKUP_DIR/laravel_backup/" 2>/dev/null || true

echo "✅ Backup created at: $BACKUP_DIR"
echo ""

# Step 2: Sync React files
echo "🔄 Syncing React files..."
mkdir -p "$LOCAL_REACT"

echo "   Syncing src directory..."
sshpass -p "$SERVER_PASS" rsync -avz --delete \
  -e "ssh -o StrictHostKeyChecking=no" \
  "$SERVER_USER@$SERVER_HOST:$SERVER_REACT/src/" \
  "$LOCAL_REACT/src/" || {
    echo "❌ Failed to sync React src directory"
    exit 1
}

# Step 3: Sync config files
echo "   Syncing config files..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST:$SERVER_REACT/package.json" \
  "$LOCAL_REACT/package.json" 2>/dev/null || echo "   ⚠️  package.json not found or failed to sync"

sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST:$SERVER_REACT/next.config.js" \
  "$LOCAL_REACT/next.config.js" 2>/dev/null || echo "   ⚠️  next.config.js not found (optional)"

sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST:$SERVER_REACT/.env.local" \
  "$LOCAL_REACT/.env.local.example" 2>/dev/null || echo "   ⚠️  .env.local not found (creating .env.local.example)"

echo "✅ React files synced successfully"
echo ""

# Step 4: Sync Laravel modified files
echo "🔄 Syncing Laravel files..."
mkdir -p "$LOCAL_LARAVEL/config"
mkdir -p "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1"
mkdir -p "$LOCAL_LARAVEL/app/CentralLogics"

echo "   Syncing cors.php..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST:$SERVER_LARAVEL/config/cors.php" \
  "$LOCAL_LARAVEL/config/cors.php" 2>/dev/null || echo "   ⚠️  cors.php not found or failed to sync"

echo "   Syncing ConversationController.php..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST:$SERVER_LARAVEL/app/Http/Controllers/Api/V1/ConversationController.php" \
  "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConversationController.php" 2>/dev/null || echo "   ⚠️  ConversationController.php not found or failed to sync"

echo "   Syncing helpers.php..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST:$SERVER_LARAVEL/app/CentralLogics/helpers.php" \
  "$LOCAL_LARAVEL/app/CentralLogics/helpers.php" 2>/dev/null || echo "   ⚠️  helpers.php not found or failed to sync"

echo "   Syncing ConfigController.php..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST:$SERVER_LARAVEL/app/Http/Controllers/Api/V1/ConfigController.php" \
  "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConfigController.php" 2>/dev/null || echo "   ⚠️  ConfigController.php not found or failed to sync"

echo "✅ Laravel files synced"
echo ""

# Step 5: Verify
echo "✅ Verification..."
SERVER_FILE_COUNT=$(sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST" "find $SERVER_REACT/src -type f 2>/dev/null | wc -l" || echo "0")
LOCAL_FILE_COUNT=$(find "$LOCAL_REACT/src" -type f 2>/dev/null | wc -l || echo "0")

echo "   Server React files: $SERVER_FILE_COUNT"
echo "   Local React files: $LOCAL_FILE_COUNT"

if [ "$SERVER_FILE_COUNT" -eq "$LOCAL_FILE_COUNT" ] && [ "$SERVER_FILE_COUNT" -gt 0 ]; then
    echo "✅ File counts match!"
else
    echo "⚠️  File counts differ (this may be normal if some files were skipped)"
fi

echo ""
echo "🎉 Sync complete!"
echo ""
echo "📝 Next steps:"
echo "   1. Review synced files"
echo "   2. Run 'npm install' in React project if package.json changed"
echo "   3. Check import paths in React files"
echo "   4. Test the application"
echo ""
echo "💾 Backup location: $BACKUP_DIR"

