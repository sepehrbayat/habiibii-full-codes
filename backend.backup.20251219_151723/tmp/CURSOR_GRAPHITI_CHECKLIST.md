# Cursor Graphiti Checklist - Use This Before & After EVERY Task
# چک‌لیست Graphiti در Cursor - از این قبل و بعد از هر کار استفاده کنید

## ✅ PRE-TASK CHECKLIST (قبل از شروع کار)

Before starting ANY task, I must:

1. **Query Graphiti for Context** 
   ```bash
   # Check recent tasks in the area
   php tmp/graphiti-sync-workflow.php recent 5
   
   # Check knowledge base for related information
   cat tmp/graphiti-knowledge-base.json | jq '.task_history[0:3]'
   ```

2. **Review Existing Knowledge**
   - What do I know about this area?
   - What are the dependencies?
   - What are recent changes?

3. **Document Intent**
   - What am I about to do?
   - What files might be affected?
   - What is the expected outcome?

---

## ✅ POST-TASK CHECKLIST (بعد از تکمیل کار)

After completing ANY task, I must:

1. **Update Graphiti Knowledge Base**
   ```bash
   php tmp/cursor-graphiti-helper.php \
     "Task Description" \
     '["Change 1", "Change 2"]' \
     '["file1.php", "file2.php"]'
   ```

2. **Record Task Completion**
   - Task name and description
   - All changes made
   - All affected files
   - Timestamp

3. **Sync with Graphiti Server**
   - Automatically attempted by helper script
   - Updates knowledge graph
   - Records relationships

---

## 🔄 Quick Reference

**Before Task Command:**
```bash
php tmp/graphiti-sync-workflow.php recent 5
```

**After Task Command:**
```bash
php tmp/cursor-graphiti-helper.php "Task Name" '["change1","change2"]' '["file1.php","file2.php"]'
```

---

## 📝 Remember

- ✅ ALWAYS check Graphiti before tasks
- ✅ ALWAYS update Graphiti after tasks  
- ✅ NO exceptions - every task must be tracked
- ✅ Even small changes need to be recorded

