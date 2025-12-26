# Cursor Graphiti MCP Workflow - REQUIRED FOR EVERY TASK
# گردش کار Graphiti MCP در Cursor - الزامی برای هر کار

## 🚨 MANDATORY WORKFLOW - MUST FOLLOW FOR EVERY TASK

**I (Cursor AI) MUST use Graphiti MCP tools before AND after every task I perform.**

**من (Cursor AI) باید قبل و بعد از هر کاری که انجام می‌دهم از ابزارهای Graphiti MCP استفاده کنم.**

---

## Workflow Steps (برای هر کار)

### BEFORE Starting Any Task (قبل از شروع هر کار)

1. **Query Graphiti for Context** (استعلام Graphiti برای محتوا)
   - Query existing knowledge about the task area
   - Get related entities, services, patterns
   - Understand dependencies and relationships
   - Check recent changes/tasks in the area

2. **Document Intent** (ثبت قصد)
   - Record what I'm about to do
   - Identify affected areas
   - Note expected changes

### AFTER Completing Any Task (بعد از تکمیل هر کار)

1. **Update Graphiti Knowledge Base** (به‌روزرسانی پایگاه دانش Graphiti)
   - Record task completion
   - Document changes made
   - List affected files
   - Update relationships if needed

2. **Sync with Graphiti Server** (همگام‌سازی با سرور Graphiti)
   - Send updates to Graphiti MCP server
   - Ensure knowledge graph is updated

---

## Implementation Methods

### Method 1: Using Graphiti MCP Tools (if available)

If Graphiti MCP tools are configured in Cursor:
- Use `list_mcp_resources` to check available Graphiti resources
- Query Graphiti before tasks
- Update Graphiti after tasks

### Method 2: Using PHP Helper Scripts

Since MCP tools may not always be available, use PHP scripts:

**Before Task:**
```bash
# Query recent tasks in the area
php tmp/graphiti-sync-workflow.php recent 5

# Check knowledge base for context
cat tmp/graphiti-knowledge-base.json | jq '.beauty_booking_module'
```

**After Task:**
```bash
# Update knowledge base
php tmp/cursor-graphiti-helper.php \
  "Task Description" \
  '["Change 1", "Change 2"]' \
  '["file1.php", "file2.php"]'
```

### Method 3: Direct MCP API Calls (when server is available)

If Graphiti MCP server is running at `http://localhost:8001/mcp/`:

**Before Task:**
- Query existing knowledge
- Get context about the task area
- Understand dependencies

**After Task:**
- Update knowledge graph nodes
- Create/update relationships
- Sync changes

---

## Task Types That Require Graphiti Updates

✅ **Code Changes** - Any file modifications  
✅ **Bug Fixes** - Fixing issues  
✅ **Feature Additions** - New functionality  
✅ **Refactoring** - Code improvements  
✅ **Documentation** - Document updates  
✅ **Configuration** - Config changes  
✅ **Database Changes** - Migrations, schema changes  
✅ **Analysis** - Code reviews, bug analysis  
✅ **Testing** - Test creation/modification  

**Everything requires Graphiti tracking!**

---

## Checklist for EVERY Task

### Pre-Task Checklist

- [ ] Query Graphiti for existing knowledge about the task area
- [ ] Review recent tasks in the same area
- [ ] Understand relationships and dependencies
- [ ] Document what I'm about to do

### Post-Task Checklist

- [ ] Record task completion in Graphiti
- [ ] Document all changes made
- [ ] List all affected files
- [ ] Update knowledge base JSON
- [ ] Sync with Graphiti server
- [ ] Verify update was successful

---

## Graphiti Integration Points

### Knowledge Base Location
- **JSON**: `tmp/graphiti-knowledge-base.json`
- **Markdown**: `tmp/graphiti-knowledge-base.md`
- **Server**: `http://localhost:8001/mcp/`

### Helper Scripts
- **Auto-Update**: `tmp/graphiti-auto-update.php`
- **Cursor Helper**: `tmp/cursor-graphiti-helper.php`
- **Sync Workflow**: `tmp/graphiti-sync-workflow.php`

### MCP Configuration
- **MCP Config**: `~/.cursor/mcp.json`
- **Server Name**: `graphiti-memory`
- **Server URL**: `http://localhost:8001/mcp/`

---

## Examples

### Example 1: Code Modification Task

**BEFORE:**
1. Query: "What is the BeautyBookingService structure?"
2. Review: Recent changes to booking service
3. Check: Related services and dependencies

**AFTER:**
1. Update: Task "Modified BeautyBookingService for bug fix"
2. Record: Changes to booking creation logic
3. Files: `Modules/BeautyBooking/Services/BeautyBookingService.php`
4. Sync: Send updates to Graphiti server

### Example 2: Bug Analysis Task

**BEFORE:**
1. Query: "What bugs exist in BeautyBooking module?"
2. Review: Recent bug fixes
3. Check: Known issues in knowledge base

**AFTER:**
1. Update: Task "Comprehensive bug analysis - found 10 bugs"
2. Record: All bugs found with details
3. Files: `BEAUTY_MODULE_LOGIC_BUGS.md`
4. Sync: Update Graphiti with new bug knowledge

---

## Important Notes

⚠️ **CRITICAL**: I must remember to use Graphiti tools:
- ✅ BEFORE starting ANY task
- ✅ AFTER completing ANY task
- ✅ Even for small changes
- ✅ Even for analysis/review tasks

⚠️ **If Graphiti server is not available:**
- Still update local knowledge base JSON
- Task will be tracked locally
- Can sync later when server is available

⚠️ **Always include:**
- Clear task description
- List of changes
- Affected files
- Timestamp

---

## Reminder for Cursor AI

**I MUST:**
1. Check Graphiti before every task for context
2. Update Graphiti after every task with results
3. Use the helper scripts if MCP tools aren't available
4. Document everything in the knowledge base
5. Sync with Graphiti server when possible

**I MUST NOT:**
1. Skip Graphiti updates
2. Forget to query before tasks
3. Forget to update after tasks
4. Assume Graphiti is optional

---

**Status**: ✅ ACTIVE - Must be followed for every task  
**Last Updated**: 2025-01-24  
**Version**: 1.0.0

