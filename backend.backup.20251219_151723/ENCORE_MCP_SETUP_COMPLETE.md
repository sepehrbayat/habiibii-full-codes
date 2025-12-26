# Encore MCP Setup - Complete ✅
# راه‌اندازی Encore MCP - کامل ✅

## Setup Summary
## خلاصه راه‌اندازی

All steps have been completed successfully! Encore MCP is now configured and ready to use.

تمام مراحل با موفقیت انجام شد! Encore MCP اکنون پیکربندی شده و آماده استفاده است.

## Completed Steps
## مراحل انجام شده

### 1. ✅ Encore CLI Installation
### 1. ✅ نصب Encore CLI

- **Version**: v1.52.1
- **Location**: `/home/sepehr/.encore/bin/encore`
- **PATH**: Added to `~/.bashrc`
- **Status**: Installed and verified

### 2. ✅ Encore App Creation
### 2. ✅ ایجاد برنامه Encore

- **App Name**: `encore-test-app`
- **App ID**: `kumza`
- **Location**: `/home/sepehr/Projects/6ammart-laravel/encore-test-app`
- **Template**: `ts/hello-world` with LLM rules for Cursor
- **Status**: Created successfully

### 3. ✅ MCP Configuration Update
### 3. ✅ به‌روزرسانی تنظیمات MCP

- **Config File**: `~/.cursor/mcp.json`
- **Server Name**: `encore-mcp`
- **Command**: `encore mcp run --app=kumza`
- **Status**: Updated with actual App ID

## Configuration Details
## جزئیات پیکربندی

### MCP Server Configuration
### پیکربندی سرور MCP

```json
{
  "encore-mcp": {
    "command": "encore",
    "args": ["mcp", "run", "--app=kumza"]
  }
}
```

### Encore App Details
### جزئیات برنامه Encore

- **App ID**: `kumza`
- **MCP SSE URL**: `http://localhost:9900/sse?app=kumza`
- **MCP Command**: `encore mcp run --app=kumza`

## Next Steps (Manual)
## مراحل بعدی (دستی)

### 1. Restart Cursor
### 1. راه‌اندازی مجدد Cursor

**Important**: You must restart Cursor for the MCP configuration to take effect.

**مهم**: باید Cursor را راه‌اندازی مجدد کنید تا تنظیمات MCP اعمال شود.

1. Close Cursor completely
2. Reopen Cursor
3. The Encore MCP server will be available in Composer (Agent mode)

### 2. Test in Composer
### 2. تست در Composer

After restarting Cursor:

1. Open **Composer** (Agent mode) in Cursor
2. Try these prompts:

   ```
   "Show me all the services in my Encore app"
   ```

   ```
   "Add image uploads to my hello world app"
   ```

   ```
   "Add a SQL database for storing user profiles"
   ```

   ```
   "Get the traces for the last API call"
   ```

## Available Encore MCP Tools
## ابزارهای موجود Encore MCP

Once Cursor is restarted, these tools will be available in Composer:

### Database Tools
- `get_databases` - Get all database schemas
- `query_database` - Execute SQL queries

### API Tools
- `call_endpoint` - Make HTTP requests to your APIs
- `get_services` - Get all services and endpoints
- `get_middleware` - Get middleware information
- `get_auth_handlers` - Get authentication handlers

### Trace Tools
- `get_traces` - Get request traces
- `get_trace_spans` - Get detailed trace information

### Source Code Tools
- `get_metadata` - Get complete app metadata
- `get_src_files` - Get source file contents

### PubSub Tools
- `get_pubsub` - Get PubSub topics and subscriptions

### Storage Tools
- `get_storage_buckets` - Get storage bucket information
- `get_objects` - List objects in storage

And more: Cache, Metrics, Cron, Secrets, and Documentation tools.

## Running Your Encore App
## اجرای برنامه Encore

To run your Encore app locally:

```bash
cd encore-test-app
encore run
```

This will:
- Start the Encore development server
- Show API endpoints
- Enable hot-reloading
- Display logs and traces

## Verification
## تأیید

To verify everything is working:

1. **Check Encore CLI**:
   ```bash
   encore version
   # Should show: encore version v1.52.1
   ```

2. **Check App ID**:
   ```bash
   cd encore-test-app
   encore mcp start
   # Should show: MCP stdio Command: encore mcp run --app=kumza
   ```

3. **Check MCP Config**:
   ```bash
   cat ~/.cursor/mcp.json | grep -A 3 "encore-mcp"
   # Should show the configuration with app=kumza
   ```

4. **Test in Cursor**:
   - Restart Cursor
   - Open Composer (Agent mode)
   - Ask: "Show me all the services in my Encore app"

## Troubleshooting
## عیب‌یابی

### MCP Tools Not Available

- ✅ Make sure you're in **Composer (Agent mode)**, not regular chat
- ✅ Verify Cursor was restarted after configuration update
- ✅ Check that Encore CLI is in PATH: `which encore`
- ✅ Verify app exists: `cd encore-test-app && encore mcp start`

### Encore Command Not Found

If `encore` command is not found:

```bash
export ENCORE_INSTALL="/home/sepehr/.encore"
export PATH="$ENCORE_INSTALL/bin:$PATH"
```

Or add to `~/.bashrc` (already done):
```bash
export ENCORE_INSTALL="/home/sepehr/.encore"
export PATH="$ENCORE_INSTALL/bin:$PATH"
```

## Files Created/Modified
## فایل‌های ایجاد/تغییر یافته

1. ✅ `~/.cursor/mcp.json` - Updated with App ID
2. ✅ `~/.bashrc` - Added Encore to PATH
3. ✅ `encore-test-app/` - New Encore app directory
4. ✅ `ENCORE_MCP_SETUP.md` - Setup documentation
5. ✅ `ENCORE_MCP_SETUP_COMPLETE.md` - This summary

## Integration Notes
## نکات یکپارچه‌سازی

**Important**: This is a Laravel project. The Encore app is separate and can be used alongside this Laravel project for backend development.

**مهم**: این یک پروژه Laravel است. برنامه Encore جداگانه است و می‌تواند در کنار این پروژه Laravel برای توسعه بک‌اند استفاده شود.

- Encore app: `/home/sepehr/Projects/6ammart-laravel/encore-test-app`
- Laravel project: `/home/sepehr/Projects/6ammart-laravel`
- Both can coexist and be developed independently

## Status: ✅ COMPLETE
## وضعیت: ✅ کامل

All automated setup steps are complete! 

تمام مراحل راه‌اندازی خودکار کامل شد!

**Final Step**: Restart Cursor to activate the MCP server.

**مرحله نهایی**: Cursor را راه‌اندازی مجدد کنید تا سرور MCP فعال شود.

---

**Setup Date**: November 28, 2024
**Encore Version**: v1.52.1
**App ID**: kumza
**Status**: Ready to use after Cursor restart

