# Graphiti Workflow - Quick Reference
# مرجع سریع گردش کار Graphiti

## 🚀 Quick Start
## شروع سریع

### Wrap Any Task
### Wrap کردن هر کار

```bash
./tmp/graphiti-task-wrapper.sh "Task Name" <your-command>
```

**Examples:**
```bash
# Migration
./tmp/graphiti-task-wrapper.sh "Run Migrations" php artisan migrate

# Test
./tmp/graphiti-task-wrapper.sh "Run Tests" php artisan test

# Custom Script
./tmp/graphiti-task-wrapper.sh "Update Config" php scripts/update.php
```

## 📋 Common Commands
## دستورات رایج

### Update Knowledge Base Manually
### به‌روزرسانی دستی پایگاه دانش

```bash
php tmp/graphiti-sync-workflow.php update "Task Name" \
  '["Change 1", "Change 2"]' \
  '["file1.php", "file2.php"]'
```

### Sync with Graphiti
### همگام‌سازی با Graphiti

```bash
php tmp/graphiti-sync-workflow.php sync
```

### View Recent Tasks
### مشاهده کارهای اخیر

```bash
php tmp/graphiti-sync-workflow.php recent 10
```

## 🔄 Workflow Pattern
## الگوی گردش کار

```
1. Execute Task (with wrapper)
   ↓
2. Task Completes
   ↓
3. Auto-Update Knowledge Base
   ↓
4. Auto-Sync with Graphiti
   ↓
5. Done! ✅
```

## 📝 Task History Location
## محل تاریخچه کارها

All tasks are stored in: `tmp/graphiti-knowledge-base.json` → `task_history`

## ⚙️ Configuration
## پیکربندی

- **Knowledge Base**: `tmp/graphiti-knowledge-base.json`
- **Graphiti Server**: `http://localhost:8001/mcp/`
- **PHP Script**: `tmp/graphiti-sync-workflow.php`
- **Bash Wrapper**: `tmp/graphiti-task-wrapper.sh`

## 🐛 Troubleshooting
## عیب‌یابی

### Server Not Responding?
```bash
curl http://localhost:8001/mcp/health
```

### JSON Invalid?
```bash
python3 -m json.tool tmp/graphiti-knowledge-base.json
```

### Wrapper Not Working?
```bash
chmod +x tmp/graphiti-task-wrapper.sh
```

## 📚 Full Documentation
## مستندات کامل

See `tmp/GRAPHITI_WORKFLOW_GUIDE.md` for complete documentation.

---

**Last Updated**: 2025-11-28

