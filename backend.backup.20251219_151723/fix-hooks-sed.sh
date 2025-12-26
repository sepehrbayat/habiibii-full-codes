#!/bin/bash

# Alternative: Simple sed-based conversion script
# This is a more direct approach using sed for bulk replacement

set -e

HOOKS_DIR="/var/www/6ammart-react/src/api-manage/hooks/react-query/beauty/vendor"
BACKUP_DIR="/tmp/beauty-vendor-hooks-backup-$(date +%Y%m%d-%H%M%S)"

echo "🚀 Converting default exports to named exports using sed..."
echo ""

# Create backup
echo "📦 Creating backup..."
cp -r "$HOOKS_DIR" "$BACKUP_DIR"
echo "✅ Backup created: $BACKUP_DIR"
echo ""

# Convert export default function to export const
echo "🔄 Converting hooks..."

# Pattern 1: export default function useHookName(params) {
# Convert to: export const useHookName = (params) => {
find "$HOOKS_DIR" -name "*.js" -type f | while read file; do
    # Convert: export default function hookName(params) { 
    # To: export const hookName = (params) => {
    sed -i 's/export\s\+default\s\+function\s\+\([a-zA-Z_][a-zA-Z0-9_]*\)\s*(\([^)]*\))\s*{/export const \1 = (\2) => {/g' "$file"
    
    # Convert: export default function hookName() {
    # To: export const hookName = () => {
    sed -i 's/export\s\+default\s\+function\s\+\([a-zA-Z_][a-zA-Z0-9_]*\)\s*()\s*{/export const \1 = () => {/g' "$file"
done

echo "✅ Conversion complete!"
echo ""
echo "🔍 Verifying..."
REMAINING=$(grep -r "export default" "$HOOKS_DIR"/*.js 2>/dev/null | wc -l || echo "0")
if [ "$REMAINING" -eq 0 ]; then
    echo "✅ All hooks converted successfully!"
else
    echo "⚠️  Warning: $REMAINING files still contain 'export default'"
fi

