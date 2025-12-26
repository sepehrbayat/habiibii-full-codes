# 🔄 Complete Prompt for Syncing Local Files Structure with Server

## Task: Synchronize Local Project Structure with Server Structure

You are tasked with synchronizing the local project structure to match exactly with the server structure. This includes:
1. **File Structure**: Directory hierarchy and file organization
2. **File Content**: All files should match server versions
3. **File Paths**: Import paths and references should be identical
4. **File Locations**: Files should be in the same relative locations

---

## Server Information

**Server Details:**
- **Host**: `193.162.129.214`
- **User**: `root`
- **Password**: `H161t5dzCG`
- **Server React Path**: `/var/www/6ammart-react/`
- **Server Laravel Path**: `/var/www/6ammart-laravel/`

**Local Project Paths:**
- **Local Laravel Path**: `/home/sepehr/Projects/6ammart-laravel/`
- **Local React Path**: `/home/sepehr/Projects/6ammart-react/`

---

## Step-by-Step Instructions

### Phase 1: Analyze Server Structure

1. **Get Complete Server Directory Structure**
   ```bash
   sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 \
     "find /var/www/6ammart-react/src -type d | sort" > /tmp/server_react_dirs.txt
   
   sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 \
     "find /var/www/6ammart-react/src -type f -name '*.js' -o -name '*.jsx' | sort" > /tmp/server_react_files.txt
   ```

2. **Get Server File Tree**
   ```bash
   sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 \
     "cd /var/www/6ammart-react && find src -type f | sort" > /tmp/server_file_tree.txt
   ```

3. **Get Server Package.json and Config Files**
   ```bash
   sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 \
     "cat /var/www/6ammart-react/package.json" > /tmp/server_package.json
   
   sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 \
     "cat /var/www/6ammart-react/next.config.js 2>/dev/null || echo 'No next.config.js'" > /tmp/server_next_config.js
   ```

### Phase 2: Analyze Local Structure

1. **Get Local Directory Structure**
   ```bash
   find /home/sepehr/Projects/6ammart-laravel -type d -path "*/node_modules" -prune -o -type d -print | sort > /tmp/local_dirs.txt
   
   # React source path: /home/sepehr/Projects/6ammart-react
   if [ -d "/home/sepehr/Projects/6ammart-react" ]; then
     find /home/sepehr/Projects/6ammart-react/src -type d | sort > /tmp/local_react_dirs.txt
     find /home/sepehr/Projects/6ammart-react/src -type f -name '*.js' -o -name '*.jsx' | sort > /tmp/local_react_files.txt
   else
     echo "⚠️  Local React directory not found: /home/sepehr/Projects/6ammart-react"
   fi
   ```

2. **Compare Structures**
   ```bash
   diff /tmp/server_react_dirs.txt /tmp/local_react_dirs.txt > /tmp/dir_differences.txt
   diff /tmp/server_react_files.txt /tmp/local_react_files.txt > /tmp/file_differences.txt
   ```

### Phase 3: Create Missing Directories Locally

1. **Create Directory Structure Script**
   ```bash
   # Read server directories and create locally
   # Local React path: /home/sepehr/Projects/6ammart-react
   while IFS= read -r dir; do
     local_dir="/home/sepehr/Projects/6ammart-react${dir#/var/www/6ammart-react}"
     if [ ! -d "$local_dir" ]; then
       mkdir -p "$local_dir"
       echo "Created: $local_dir"
     fi
   done < /tmp/server_react_dirs.txt
   ```

### Phase 4: Sync Files from Server to Local

1. **Sync All React Source Files**
   ```bash
   # Create sync script
   cat > /tmp/sync_react_files.sh << 'EOF'
   #!/bin/bash
   SERVER_HOST="193.162.129.214"
   SERVER_USER="root"
   SERVER_PASS="H161t5dzCG"
   SERVER_PATH="/var/www/6ammart-react"
   LOCAL_PATH="/home/sepehr/Projects/6ammart-react"
   
   # Create local directory if it doesn't exist
   mkdir -p "$LOCAL_PATH"
   
   # Sync entire src directory
   sshpass -p "$SERVER_PASS" rsync -avz --delete \
     -e "ssh -o StrictHostKeyChecking=no" \
     "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/src/" \
     "$LOCAL_PATH/src/"
   
   # Sync package.json and config files
   sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
     "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/package.json" \
     "$LOCAL_PATH/package.json"
   
   sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
     "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/next.config.js" \
     "$LOCAL_PATH/next.config.js" 2>/dev/null || echo "next.config.js not found"
   
   sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
     "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/.env.local" \
     "$LOCAL_PATH/.env.local" 2>/dev/null || echo ".env.local not found"
   
   echo "✅ React files synced successfully"
   EOF
   
   chmod +x /tmp/sync_react_files.sh
   /tmp/sync_react_files.sh
   ```

2. **Sync Laravel Files (if needed)**
   ```bash
   # Sync specific Laravel files that were modified
   sshpass -p 'H161t5dzCG' scp -o StrictHostKeyChecking=no \
     root@193.162.129.214:/var/www/6ammart-laravel/config/cors.php \
     /home/sepehr/Projects/6ammart-laravel/config/cors.php
   
   sshpass -p 'H161t5dzCG' scp -o StrictHostKeyChecking=no \
     root@193.162.129.214:/var/www/6ammart-laravel/app/Http/Controllers/Api/V1/ConversationController.php \
     /home/sepehr/Projects/6ammart-laravel/app/Http/Controllers/Api/V1/ConversationController.php
   
   sshpass -p 'H161t5dzCG' scp -o StrictHostKeyChecking=no \
     root@193.162.129.214:/var/www/6ammart-laravel/app/CentralLogics/helpers.php \
     /home/sepehr/Projects/6ammart-laravel/app/CentralLogics/helpers.php
   
   sshpass -p 'H161t5dzCG' scp -o StrictHostKeyChecking=no \
     root@193.162.129.214:/var/www/6ammart-laravel/app/Http/Controllers/Api/V1/ConfigController.php \
     /home/sepehr/Projects/6ammart-laravel/app/Http/Controllers/Api/V1/ConfigController.php
   ```

### Phase 5: Fix Import Paths and References

1. **Analyze Import Paths in Server Files**
   ```bash
   # Extract all import statements from server files
   sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 \
     "grep -r '^import\|^export.*from' /var/www/6ammart-react/src --include='*.js' --include='*.jsx' | \
     sed 's|/var/www/6ammart-react/||g' | sort | uniq" > /tmp/server_imports.txt
   ```

2. **Analyze Import Paths in Local Files**
   ```bash
   grep -r '^import\|^export.*from' /home/sepehr/Projects/6ammart-react/src \
     --include='*.js' --include='*.jsx' | \
     sed 's|/home/sepehr/Projects/6ammart-react/||g' | sort | uniq > /tmp/local_imports.txt
   ```

3. **Create Path Mapping Script**
   ```bash
   # Common path patterns that might differ
   # Server: /var/www/6ammart-react/src/utils/safeRender.js
   # Local should be: /home/sepehr/Projects/6ammart-react/src/utils/safeRender.js
   # Import should be: import { safeString } from '../../../utils/safeRender';
   
   # Verify all relative imports are correct
   # Local React path: /home/sepehr/Projects/6ammart-react
   cat > /tmp/verify_imports.sh << 'EOF'
   #!/bin/bash
   LOCAL_REACT="/home/sepehr/Projects/6ammart-react"
   
   find "$LOCAL_REACT/src" -name "*.js" -o -name "*.jsx" | while read file; do
     # Check for absolute paths in imports
     if grep -q "from '/var/www" "$file" || grep -q "from '/home/sepehr" "$file"; then
       echo "⚠️  Absolute path found in: $file"
     fi
     
     # Check for incorrect relative paths
     if grep -q "from '\.\./\.\./\.\./\.\./\.\./" "$file"; then
       echo "⚠️  Deep relative path in: $file"
     fi
   done
   EOF
   
   chmod +x /tmp/verify_imports.sh
   /tmp/verify_imports.sh
   ```

### Phase 6: Verify File Structure Match

1. **Compare File Checksums**
   ```bash
   # Get server file checksums
   sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 \
     "find /var/www/6ammart-react/src -type f -name '*.js' -o -name '*.jsx' | \
     xargs md5sum | sort" > /tmp/server_checksums.txt
   
   # Get local file checksums
   find /home/sepehr/Projects/6ammart-react/src -type f -name '*.js' -o -name '*.jsx' | \
     xargs md5sum | sort > /tmp/local_checksums.txt
   
   # Compare
   diff /tmp/server_checksums.txt /tmp/local_checksums.txt > /tmp/checksum_differences.txt
   
   if [ -s /tmp/checksum_differences.txt ]; then
     echo "⚠️  Files differ:"
     cat /tmp/checksum_differences.txt
   else
     echo "✅ All files match!"
   fi
   ```

2. **Verify Directory Structure**
   ```bash
   # Compare directory structures
   diff /tmp/server_react_dirs.txt /tmp/local_react_dirs.txt
   
   if [ $? -eq 0 ]; then
     echo "✅ Directory structures match!"
   else
     echo "⚠️  Directory structures differ - see differences above"
   fi
   ```

### Phase 7: Update Local Project Configuration

1. **Update Package.json Dependencies**
   ```bash
   # Compare package.json
   diff /tmp/server_package.json /home/sepehr/Projects/6ammart-react/package.json
   
   # If different, update local package.json
   cp /tmp/server_package.json /home/sepehr/Projects/6ammart-react/package.json
   ```

2. **Update Environment Files**
   ```bash
   # Get server .env.local (if exists)
   sshpass -p 'H161t5dzCG' scp -o StrictHostKeyChecking=no \
     root@193.162.129.214:/var/www/6ammart-react/.env.local \
     /home/sepehr/Projects/6ammart-react/.env.local.example 2>/dev/null
   
   # Note: Don't overwrite local .env.local, just create example
   ```

### Phase 8: Create Backup Before Changes

1. **Backup Current Local Structure**
   ```bash
   BACKUP_DIR="/home/sepehr/Projects/6ammart-laravel/backups/$(date +%Y%m%d_%H%M%S)"
   mkdir -p "$BACKUP_DIR"
   
   # Backup React files if they exist
   if [ -d "/home/sepehr/Projects/6ammart-react" ]; then
     cp -r /home/sepehr/Projects/6ammart-react "$BACKUP_DIR/react_backup"
     echo "✅ React files backed up to: $BACKUP_DIR/react_backup"
   fi
   
   # Backup Laravel modified files
   mkdir -p "$BACKUP_DIR/laravel_backup"
   cp /home/sepehr/Projects/6ammart-laravel/config/cors.php "$BACKUP_DIR/laravel_backup/" 2>/dev/null
   cp /home/sepehr/Projects/6ammart-laravel/app/Http/Controllers/Api/V1/ConversationController.php "$BACKUP_DIR/laravel_backup/" 2>/dev/null
   cp /home/sepehr/Projects/6ammart-laravel/app/CentralLogics/helpers.php "$BACKUP_DIR/laravel_backup/" 2>/dev/null
   cp /home/sepehr/Projects/6ammart-laravel/app/Http/Controllers/Api/V1/ConfigController.php "$BACKUP_DIR/laravel_backup/" 2>/dev/null
   
   echo "✅ Backup created at: $BACKUP_DIR"
   ```

### Phase 9: Final Verification

1. **Create Verification Report**
   ```bash
   cat > /tmp/sync_verification_report.txt << EOF
   # File Structure Sync Verification Report
   Generated: $(date)
   
   ## Directory Structure
   Server directories: $(wc -l < /tmp/server_react_dirs.txt)
   Local directories: $(wc -l < /tmp/local_react_dirs.txt)
   
   ## File Count
   Server files: $(wc -l < /tmp/server_react_files.txt)
   Local files: $(wc -l < /tmp/local_react_files.txt)
   
   ## File Differences
   $(if [ -s /tmp/checksum_differences.txt ]; then echo "⚠️  Files differ - see checksum_differences.txt"; else echo "✅ All files match"; fi)
   
   ## Import Path Verification
   $(/tmp/verify_imports.sh 2>&1 | head -20)
   
   EOF
   
   cat /tmp/sync_verification_report.txt
   ```

---

## Complete Automation Script

Create a single script that does everything:

```bash
#!/bin/bash
# Complete Local-Server Sync Script

set -e  # Exit on error

SERVER_HOST="193.162.129.214"
SERVER_USER="root"
SERVER_PASS="H161t5dzCG"
SERVER_REACT="/var/www/6ammart-react"
SERVER_LARAVEL="/var/www/6ammart-laravel"
LOCAL_LARAVEL="/home/sepehr/Projects/6ammart-laravel"
LOCAL_REACT="/home/sepehr/Projects/6ammart-react"  # Local React project path

echo "🔄 Starting Local-Server Sync Process..."

# Step 1: Create backup
echo "📦 Creating backup..."
BACKUP_DIR="$LOCAL_LARAVEL/backups/$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
if [ -d "$LOCAL_REACT" ]; then
  cp -r "$LOCAL_REACT" "$BACKUP_DIR/react_backup"
fi
echo "✅ Backup created at: $BACKUP_DIR"

# Step 2: Sync React files
echo "🔄 Syncing React files..."
mkdir -p "$LOCAL_REACT"
sshpass -p "$SERVER_PASS" rsync -avz --delete \
  -e "ssh -o StrictHostKeyChecking=no" \
  "$SERVER_USER@$SERVER_HOST:$SERVER_REACT/src/" \
  "$LOCAL_REACT/src/"

# Step 3: Sync config files
echo "🔄 Syncing config files..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST:$SERVER_REACT/package.json" \
  "$LOCAL_REACT/package.json"

# Step 4: Sync Laravel modified files
echo "🔄 Syncing Laravel files..."
sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST:$SERVER_LARAVEL/config/cors.php" \
  "$LOCAL_LARAVEL/config/cors.php"

sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST:$SERVER_LARAVEL/app/Http/Controllers/Api/V1/ConversationController.php" \
  "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConversationController.php"

sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST:$SERVER_LARAVEL/app/CentralLogics/helpers.php" \
  "$LOCAL_LARAVEL/app/CentralLogics/helpers.php"

sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST:$SERVER_LARAVEL/app/Http/Controllers/Api/V1/ConfigController.php" \
  "$LOCAL_LARAVEL/app/Http/Controllers/Api/V1/ConfigController.php"

# Step 5: Verify
echo "✅ Verification..."
SERVER_FILE_COUNT=$(sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no \
  "$SERVER_USER@$SERVER_HOST" "find $SERVER_REACT/src -type f | wc -l")
LOCAL_FILE_COUNT=$(find "$LOCAL_REACT/src" -type f 2>/dev/null | wc -l)

echo "Server files: $SERVER_FILE_COUNT"
echo "Local files: $LOCAL_FILE_COUNT"

if [ "$SERVER_FILE_COUNT" -eq "$LOCAL_FILE_COUNT" ]; then
  echo "✅ File counts match!"
else
  echo "⚠️  File counts differ"
fi

echo "🎉 Sync complete!"
```

---

## Important Notes

1. **Always Backup First**: Never sync without creating a backup
2. **Verify Paths**: Ensure all import paths use relative paths, not absolute
3. **Environment Files**: Don't overwrite local `.env` files - use `.env.example`
4. **Node Modules**: Don't sync `node_modules` - run `npm install` locally
5. **Git**: Consider committing changes after sync for version control

---

## Expected Results

After completing this sync:
- ✅ Local directory structure matches server exactly
- ✅ All files have identical content
- ✅ Import paths are correct and relative
- ✅ File locations are identical
- ✅ Configuration files are synced
- ✅ Backup is created before changes

---

## Troubleshooting

If files don't match:
1. Check SSH connection: `sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "echo 'Connected'"`
2. Verify paths exist on server
3. Check file permissions
4. Review backup and restore if needed

---

**Last Updated**: 2025-01-16
**Status**: Ready for execution

