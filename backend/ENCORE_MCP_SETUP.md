# Encore MCP Server Setup Guide
# راهنمای راه‌اندازی Encore MCP Server

## Overview
## بررسی کلی

Encore is a backend framework that provides an MCP (Model Context Protocol) server for Cursor. This allows Cursor's Composer Agent to interact with Encore applications, query databases, call APIs, view traces, and more.

Encore یک فریمورک بک‌اند است که یک سرور MCP (Model Context Protocol) برای Cursor ارائه می‌دهد. این امکان به Composer Agent در Cursor اجازه می‌دهد تا با برنامه‌های Encore تعامل داشته باشد، پایگاه داده را جستجو کند، APIها را فراخوانی کند، traceها را مشاهده کند و غیره.

## Prerequisites
## پیش‌نیازها

- Cursor IDE installed
- Encore CLI installed
- An Encore app (or create a new one)

## Installation Steps
## مراحل نصب

### 1. Install Encore CLI
### 1. نصب Encore CLI

For Linux:

```bash
curl -L https://encore.dev/install.sh | bash
```

Or download manually from [encore.dev](https://encore.dev).

After installation, verify:

```bash
encore version
```

### 2. Create an Encore App
### 2. ایجاد یک برنامه Encore

Create a new Encore app:

```bash
# Create a new Encore app with example
encore app create --example=ts/hello-world --llm-rules=cursor

# Or create a basic app
encore app create my-app
```

### 3. Get Your App ID
### 3. دریافت App ID

When you create an Encore app, you'll get an app ID. You can also find it by running:

```bash
cd your-encore-app
encore mcp start
```

This will show you:

```
MCP Service is running!
MCP SSE URL:        http://localhost:9900/sse?app=your-app-id
MCP stdio Command:  encore mcp run --app=your-app-id
```

### 4. Update MCP Configuration
### 4. به‌روزرسانی تنظیمات MCP

Edit `~/.cursor/mcp.json` and add the Encore MCP server configuration:

```json
{
  "mcpServers": {
    "encore-mcp": {
      "command": "encore",
      "args": ["mcp", "run", "--app=your-actual-app-id"]
    }
  }
}
```

**Important**: Replace `your-actual-app-id` with your actual Encore app ID.

### 5. Restart Cursor
### 5. راه‌اندازی مجدد Cursor

Restart Cursor to load the new MCP server configuration.

## How to Use Encore MCP in Cursor
## نحوه استفاده از Encore MCP در Cursor

### Using MCP Tools in Composer
### استفاده از ابزارهای MCP در Composer

1. **Open Composer (Agent mode)** in Cursor
   **باز کردن Composer (حالت Agent)** در Cursor

2. The Composer Agent automatically uses Encore MCP tools when relevant
   Composer Agent به طور خودکار از ابزارهای Encore MCP استفاده می‌کند

3. You can explicitly request actions like:
   می‌توانید به صراحت درخواست کنید:

   - "Add an endpoint that publishes to a pub/sub topic, call it and verify that the publish is in the traces"
   - "Show me all the services in my Encore app"
   - "Query the database and show me the results"
   - "Get the traces for the last API call"

## Available Encore MCP Tools
## ابزارهای موجود Encore MCP

### Database Tools
### ابزارهای پایگاه داده

- **get_databases** - Get all database schemas
- **query_database** - Execute SQL queries

### API Tools
### ابزارهای API

- **call_endpoint** - Make HTTP requests to your APIs
- **get_services** - Get all services and endpoints
- **get_middleware** - Get middleware information
- **get_auth_handlers** - Get authentication handlers

### Trace Tools
### ابزارهای Trace

- **get_traces** - Get request traces
- **get_trace_spans** - Get detailed trace information

### Source Code Tools
### ابزارهای کد منبع

- **get_metadata** - Get complete app metadata
- **get_src_files** - Get source file contents

### PubSub Tools
### ابزارهای PubSub

- **get_pubsub** - Get PubSub topics and subscriptions

### Storage Tools
### ابزارهای Storage

- **get_storage_buckets** - Get storage bucket information
- **get_objects** - List objects in storage

### Additional Tools
### ابزارهای اضافی

- Cache tools
- Metrics tools
- Cron tools
- Secrets tools
- Documentation tools

## Tool Execution Process
## فرآیند اجرای ابزار

1. When you ask for something in Composer, Cursor will show a message requesting approval
   وقتی در Composer درخواست می‌کنید، Cursor پیامی برای تأیید نشان می‌دهد

2. Review the tool call arguments (expandable)
   بررسی آرگومان‌های فراخوانی ابزار (قابل گسترش)

3. Click "Approve" to execute
   کلیک روی "Approve" برای اجرا

4. The tool's response will be displayed in the chat
   پاسخ ابزار در چت نمایش داده می‌شود

## Important Notes
## نکات مهم

- ⚠️ MCP tools are only available in **Composer (Agent mode)**, not in regular chat
  ابزارهای MCP فقط در **Composer (حالت Agent)** در دسترس هستند، نه در چت عادی

- ⚠️ You need to be in an Encore app directory or have Encore properly configured
  باید در دایرکتوری برنامه Encore باشید یا Encore را به درستی پیکربندی کرده باشید

- ⚠️ The MCP server needs your Encore app to be running or accessible
  سرور MCP نیاز دارد که برنامه Encore شما در حال اجرا یا قابل دسترسی باشد

## Quick Start Examples
## نمونه‌های شروع سریع

Once set up, try asking in Composer:

### Example 1: List Services
```text
"Show me all the services and endpoints in my Encore app"
```

### Example 2: Create API Endpoint
```text
"Add a new REST API endpoint that returns a list of users from the database"
```

### Example 3: Query Database
```text
"Query the database and show me all users"
```

### Example 4: View Traces
```text
"Get the traces for the last API call"
```

## Troubleshooting
## عیب‌یابی

### Encore Not Found
### Encore پیدا نشد

If `encore version` fails:

```bash
# Add Encore to PATH
export PATH="$HOME/.encore/bin:$PATH"

# Or reinstall
curl -L https://encore.dev/install.sh | bash
```

### MCP Server Not Starting
### سرور MCP راه‌اندازی نمی‌شود

1. Verify Encore app ID is correct
2. Check that Encore app is accessible
3. Ensure Encore CLI is in PATH
4. Check Cursor logs for errors

### Tools Not Available
### ابزارها در دسترس نیستند

- Make sure you're in **Composer (Agent mode)**, not regular chat
- Verify MCP configuration in `~/.cursor/mcp.json`
- Restart Cursor after configuration changes

## Integration with This Project
## یکپارچه‌سازی با این پروژه

**Note**: This is a Laravel project. Encore MCP is a separate tool that can be used alongside this project for backend development with Encore framework.

**توجه**: این یک پروژه Laravel است. Encore MCP یک ابزار جداگانه است که می‌تواند در کنار این پروژه برای توسعه بک‌اند با فریمورک Encore استفاده شود.

If you want to use Encore for backend services while keeping this Laravel project:

1. Create Encore app in a separate directory
2. Configure MCP as described above
3. Use Encore MCP tools in Cursor for Encore app development
4. This Laravel project remains independent

## References
## مراجع

- [Encore Documentation](https://encore.dev/docs)
- [Encore MCP Guide](https://encore.dev/docs/develop/mcp)
- [Cursor MCP Documentation](https://docs.cursor.com/mcp)

## Status
## وضعیت

- [x] Encore CLI installed (v1.52.1) ✅
- [x] Encore app created ✅
- [x] App ID obtained (kumza) ✅
- [x] MCP configuration updated ✅
- [ ] Cursor restarted (manual step required)
- [ ] Tools tested in Composer

## Current Setup Status
## وضعیت راه‌اندازی فعلی

✅ **Encore CLI**: Installed and working (v1.52.1)
- Location: `/home/sepehr/.encore/bin/encore`
- Added to PATH in `~/.bashrc`

✅ **Encore App**: Created successfully
- App Name: `encore-test-app`
- App ID: `kumza`
- Location: `/home/sepehr/Projects/6ammart-laravel/encore-test-app`
- Template: `ts/hello-world` with LLM rules for Cursor

✅ **MCP Configuration**: Updated and ready
- File: `~/.cursor/mcp.json`
- Entry: `encore-mcp` configured with App ID `kumza`
- Command: `encore mcp run --app=kumza`

⏳ **Final Step Required**:
1. **Restart Cursor** to load the new MCP configuration
2. Open **Composer (Agent mode)** in Cursor
3. Test with: "Show me all the services in my Encore app"

