# Graphiti Workflow Guide
# راهنمای گردش کار Graphiti

## Overview
## نمای کلی

This workflow automatically updates the Graphiti knowledge base after each task and syncs with the Graphiti server.
این گردش کار به طور خودکار پایگاه دانش Graphiti را پس از هر کار به‌روزرسانی می‌کند و با سرور Graphiti همگام‌سازی می‌کند.

## Files Created
## فایل‌های ایجاد شده

1. **`graphiti-sync-workflow.php`** - PHP class for knowledge base updates
2. **`graphiti-task-wrapper.sh`** - Bash wrapper script for automatic updates
3. **`GRAPHITI_WORKFLOW_GUIDE.md`** - This guide

## How It Works
## نحوه کار

### Automatic Workflow
### گردش کار خودکار

The workflow tracks:
گردش کار ردیابی می‌کند:

- Task name and description
- Timestamp of completion
- Changes made
- Affected files
- Automatic sync with Graphiti server

### Task History
### تاریخچه کارها

All tasks are stored in `task_history` array in the knowledge base JSON:
تمام کارها در آرایه `task_history` در JSON پایگاه دانش ذخیره می‌شوند:

```json
{
  "task_history": [
    {
      "task_name": "Task Description",
      "timestamp": "2025-11-28 17:15:00",
      "changes": ["Change 1", "Change 2"],
      "affected_files": ["file1.php", "file2.php"]
    }
  ]
}
```

## Usage
## استفاده

### Method 1: Using the Bash Wrapper
### روش 1: استفاده از Bash Wrapper

Wrap any command with the wrapper script:

```bash
./tmp/graphiti-task-wrapper.sh "Task Name" <your-command>
```

**Example:**
```bash
# Run a PHP artisan command
./tmp/graphiti-task-wrapper.sh "Run Migrations" php artisan migrate

# Run a test suite
./tmp/graphiti-task-wrapper.sh "Run Tests" php artisan test --filter BeautyBooking

# Run a custom script
./tmp/graphiti-task-wrapper.sh "Update Config" php scripts/update-config.php
```

### Method 2: Using PHP Script Directly
### روش 2: استفاده مستقیم از اسکریپت PHP

Update knowledge base manually:

```bash
# Update after a task
php tmp/graphiti-sync-workflow.php update "Task Name" '["Change 1", "Change 2"]' '["file1.php", "file2.php"]'

# Sync with Graphiti server
php tmp/graphiti-sync-workflow.php sync

# View recent tasks
php tmp/graphiti-sync-workflow.php recent 10
```

### Method 3: Programmatic Usage
### روش 3: استفاده برنامه‌نویسی

Use the PHP class in your code:

```php
<?php
require_once 'tmp/graphiti-sync-workflow.php';

use GraphitiSync\GraphitiKnowledgeBaseUpdater;

$updater = new GraphitiKnowledgeBaseUpdater();

// Update after a task
$updater->updateAfterTask(
    'My Task Name',
    ['Change 1', 'Change 2'],
    ['file1.php', 'file2.php']
);

// Sync with Graphiti
$result = $updater->syncWithGraphiti();
if ($result['success']) {
    echo "Synced successfully!";
}
```

## Integration with Cursor
## یکپارچه‌سازی با Cursor

### For Each Task:
### برای هر کار:

1. **Before Task**: Document what you're about to do
2. **Execute Task**: Run your command/script
3. **After Task**: The wrapper automatically:
   - Updates knowledge base with task info
   - Tracks changes and affected files
   - Attempts to sync with Graphiti server

### Manual Update in Cursor
### به‌روزرسانی دستی در Cursor

If you need to manually update after a task:

```bash
# After completing a task, run:
php tmp/graphiti-sync-workflow.php update "Task Description" \
  '["Change description 1", "Change description 2"]' \
  '["path/to/file1.php", "path/to/file2.php"]'

# Then sync:
php tmp/graphiti-sync-workflow.php sync
```

## Workflow Steps
## مراحل گردش کار

### Step 1: Execute Task
### مرحله 1: اجرای کار

```bash
# Your normal command
php artisan migrate
```

### Step 2: Update Knowledge Base (Automatic)
### مرحله 2: به‌روزرسانی پایگاه دانش (خودکار)

The wrapper automatically:
- Captures task name
- Detects affected files (via git status)
- Updates knowledge base JSON
- Adds entry to task_history

### Step 3: Sync with Graphiti (Automatic)
### مرحله 3: همگام‌سازی با Graphiti (خودکار)

The wrapper attempts to:
- Connect to Graphiti server at `http://localhost:8001/mcp/`
- Send updated knowledge base
- Report success/failure

## Configuration
## پیکربندی

### Graphiti Server URL
### URL سرور Graphiti

Default: `http://localhost:8001/mcp/`

To change, edit `graphiti-sync-workflow.php`:

```php
$updater = new GraphitiKnowledgeBaseUpdater(
    __DIR__ . '/graphiti-knowledge-base.json',
    'http://your-graphiti-server:port/mcp/'  // Custom URL
);
```

### Knowledge Base Path
### مسیر پایگاه دانش

Default: `tmp/graphiti-knowledge-base.json`

To change, edit the constructor in `graphiti-sync-workflow.php`.

## Task History Management
## مدیریت تاریخچه کارها

### View Recent Tasks
### مشاهده کارهای اخیر

```bash
# View last 10 tasks
php tmp/graphiti-sync-workflow.php recent 10

# View last 20 tasks
php tmp/graphiti-sync-workflow.php recent 20
```

### Task History Structure
### ساختار تاریخچه کارها

```json
{
  "task_name": "Human-readable task description",
  "timestamp": "2025-11-28 17:15:00",
  "timestamp_iso": "2025-11-28T17:15:00+00:00",
  "changes": [
    "Description of change 1",
    "Description of change 2"
  ],
  "affected_files": [
    "path/to/file1.php",
    "path/to/file2.php"
  ]
}
```

### Automatic Cleanup
### پاکسازی خودکار

The workflow automatically keeps only the last 50 tasks to prevent the knowledge base from growing too large.

## Troubleshooting
## عیب‌یابی

### Graphiti Server Not Responding
### سرور Graphiti پاسخ نمی‌دهد

```bash
# Check if server is running
curl http://localhost:8001/mcp/health

# Check port
netstat -tlnp | grep 8001
```

**Note**: The workflow will continue even if Graphiti server is not available. Tasks are still tracked in the knowledge base JSON file.

### Knowledge Base Update Fails
### به‌روزرسانی پایگاه دانش ناموفق است

- Check file permissions: `chmod 644 tmp/graphiti-knowledge-base.json`
- Verify JSON is valid: `python3 -m json.tool tmp/graphiti-knowledge-base.json`
- Check disk space

### Wrapper Script Not Working
### اسکریپت wrapper کار نمی‌کند

```bash
# Check permissions
chmod +x tmp/graphiti-task-wrapper.sh

# Test with a simple command
./tmp/graphiti-task-wrapper.sh "Test Task" echo "Hello World"
```

## Best Practices
## بهترین روش‌ها

1. **Use Descriptive Task Names**: Clear, concise descriptions
2. **Document Changes**: List all significant changes made
3. **Include Affected Files**: Helps track what was modified
4. **Regular Syncs**: Sync with Graphiti regularly to keep it updated
5. **Review Task History**: Periodically review to understand project evolution

## Example Workflow
## مثال گردش کار

```bash
# 1. Start a new feature
./tmp/graphiti-task-wrapper.sh "Add New Booking Status" \
  php artisan make:migration add_status_to_beauty_bookings

# 2. Run migrations
./tmp/graphiti-task-wrapper.sh "Run Migrations" \
  php artisan migrate

# 3. Create a service
./tmp/graphiti-task-wrapper.sh "Create BookingStatusService" \
  php artisan make:service BeautyBookingStatusService

# 4. Run tests
./tmp/graphiti-task-wrapper.sh "Run Booking Tests" \
  php artisan test --filter BeautyBooking

# 5. View recent tasks
php tmp/graphiti-sync-workflow.php recent 5
```

## Integration with CI/CD
## یکپارچه‌سازی با CI/CD

You can integrate this workflow into your CI/CD pipeline:

```yaml
# Example GitHub Actions
- name: Update Graphiti Knowledge Base
  run: |
    php tmp/graphiti-sync-workflow.php update \
      "CI/CD Pipeline Run" \
      '["Automated build and test"]' \
      '[]'
    php tmp/graphiti-sync-workflow.php sync
```

## Next Steps
## مراحل بعدی

1. **Test the Workflow**: Try running a simple task with the wrapper
2. **Verify Graphiti Connection**: Ensure Graphiti server is accessible
3. **Customize as Needed**: Adjust paths, URLs, or behavior
4. **Integrate into Workflow**: Use wrapper for all significant tasks

---

**Created**: 2025-11-28
**Last Updated**: 2025-11-28 17:15:00
**Version**: 1.0.0

