# Cursor Auto-Graphiti Integration
# یکپارچه‌سازی خودکار Graphiti در Cursor

## How Cursor Uses Graphiti Automatically
## نحوه استفاده خودکار Cursor از Graphiti

**🚨 MANDATORY: I (Cursor) MUST use Graphiti MCP tools BEFORE AND AFTER EVERY task I perform.**

**🚨 الزامی: من (Cursor) باید قبل و بعد از هر کاری که انجام می‌دهم از ابزارهای Graphiti MCP استفاده کنم.**

## Required Workflow (برای هر کار)

### BEFORE Starting Any Task:
1. ✅ Query Graphiti for existing knowledge about the task area
2. ✅ Check recent tasks in the same area
3. ✅ Understand relationships and dependencies
4. ✅ Document what I'm about to do

### AFTER Completing Any Task:
1. ✅ Update Graphiti knowledge base with task completion
2. ✅ Document all changes made
3. ✅ List all affected files
4. ✅ Sync with Graphiti server

## Automatic Workflow
## گردش کار خودکار

After completing any task, I will:

1. **Call the update function**:
   ```bash
   php tmp/cursor-graphiti-helper.php "Task Description" '["Change 1", "Change 2"]' '["file1.php", "file2.php"]'
   ```

2. **The system automatically**:
   - Updates `tmp/graphiti-knowledge-base.json` with task info
   - Adds entry to `task_history`
   - Attempts to sync with Graphiti server at `http://localhost:8001/mcp/`
   - Reports success/failure

3. **No action required from you** - it's completely automatic!

## What Gets Tracked
## چه چیزهایی ردیابی می‌شوند

For each task, I track:
- **Task Name**: Clear description of what was done
- **Timestamp**: When the task was completed
- **Changes**: List of changes made
- **Affected Files**: Files that were modified

## Example: After This Task
## مثال: پس از این کار

I just completed: "Created Auto-Update System for Graphiti"

The system automatically:
- ✅ Updated knowledge base
- ⚠️ Attempted Graphiti sync (server may not be running, but that's OK)
- ✅ Task history now includes this task

## For Future Tasks
## برای کارهای آینده

**You don't need to do anything!** I will automatically:

1. Complete your requested task
2. Update Graphiti knowledge base
3. Attempt to sync with Graphiti server
4. Report the results

## Files Created
## فایل‌های ایجاد شده

- `tmp/graphiti-auto-update.php` - Auto-update function
- `tmp/cursor-graphiti-helper.php` - Helper for Cursor to use
- `tmp/graphiti-sync-workflow.php` - Core sync class
- `tmp/CURSOR_AUTO_GRAPHITI.md` - This file

## Verification
## تأیید

You can verify I'm using Graphiti by:

```bash
# View recent tasks I've tracked
php tmp/graphiti-sync-workflow.php recent 10

# Check knowledge base
cat tmp/graphiti-knowledge-base.json | jq '.task_history[0:3]'
```

## Notes
## یادداشت‌ها

- If Graphiti server is not running, the knowledge base is still updated locally
- All tasks are tracked in `task_history` array
- Knowledge base is always kept up-to-date
- Graphiti sync is attempted but not required for local tracking

---

**Status**: ✅ Active - Cursor will automatically update Graphiti after each task
**Last Updated**: 2025-11-28

