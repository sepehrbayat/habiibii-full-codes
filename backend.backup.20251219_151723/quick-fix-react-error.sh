#!/bin/bash

# Quick Fix React Error #31 - One Command Execution
# This script runs the fix and rebuilds automatically

cd "$(dirname "$0")"

echo "🚀 Quick Fix React Error #31"
echo "=============================="
echo ""

# Run the main fix script
./fix-react-error-31.sh

# Ask if user wants to rebuild now
echo ""
read -p "Do you want to rebuild and restart the application now? (y/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo ""
    echo "🔨 Rebuilding and restarting..."
    sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "cd /var/www/6ammart-react && npm run build && pm2 restart 6ammart-react"
    echo ""
    echo "✅ Rebuild and restart completed!"
    echo ""
    echo "Checking PM2 status..."
    sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "pm2 status"
    echo ""
    echo "Viewing recent logs..."
    sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 "pm2 logs 6ammart-react --lines 20 --nostream"
else
    echo ""
    echo "⚠️  Skipping rebuild. Run manually when ready:"
    echo "   sshpass -p 'H161t5dzCG' ssh -o StrictHostKeyChecking=no root@193.162.129.214 \"cd /var/www/6ammart-react && npm run build && pm2 restart 6ammart-react\""
fi

