# Graphiti Knowledge Base - Usage Guide
# راهنمای استفاده از پایگاه دانش Graphiti

## Files Created
## فایل‌های ایجاد شده

1. **`graphiti-knowledge-base.json`** - Complete structured JSON knowledge base
2. **`graphiti-knowledge-base.md`** - Human-readable markdown version
3. **`GRAPHITI_README.md`** - This file

## What is Graphiti?
## Graphiti چیست؟

Graphiti is a knowledge graph system for AI assistants that helps maintain context and build relationships between concepts. It's running on port 8001 at `http://localhost:8001/mcp/`.

## How to Use These Files
## نحوه استفاده از این فایل‌ها

### Option 1: Direct Import to Graphiti
### گزینه 1: وارد کردن مستقیم به Graphiti

If Graphiti has an import feature, you can import the JSON file:

```bash
# Example (adjust based on Graphiti's API)
curl -X POST http://localhost:8001/mcp/import \
  -H "Content-Type: application/json" \
  -d @tmp/graphiti-knowledge-base.json
```

### Option 2: Manual Entry
### گزینه 2: ورود دستی

Use the markdown file (`graphiti-knowledge-base.md`) as a reference to manually enter information into Graphiti's interface.

### Option 3: API Integration
### گزینه 3: یکپارچه‌سازی API

If Graphiti exposes an MCP API, you can use the structured JSON to create nodes and relationships programmatically.

## Knowledge Base Contents
## محتوای پایگاه دانش

### Project Information
### اطلاعات پروژه

- Project name, type, framework, location
- Active modules list
- Module status and configuration

### BeautyBooking Module Details
### جزئیات ماژول رزرو زیبایی

#### Entities (19)
#### موجودیت‌ها (19)

Complete information about all entities:
- Field descriptions
- Relationships (belongsTo, hasMany, belongsToMany)
- Table names
- Key attributes

#### Services (10)
#### سرویس‌ها (10)

- Service descriptions
- Key methods
- Dependencies
- Business logic patterns

#### Controllers (54)
#### کنترلرها (54)

- Controller counts by type
- Complete list of all controllers
- Purpose and responsibilities

#### Revenue Models (10)
#### مدل‌های درآمدی (10)

- All revenue streams
- Implementation details
- Business logic

### Integration Points
### نقاط یکپارچه‌سازی

- Store model integration
- User model integration
- Wallet system
- Payment gateways
- Chat system
- Notifications
- Zone scope
- Report filter

### Code Standards
### استانداردهای کد

- Strict types
- PSR-12 compliance
- Bilingual comments
- PHPDoc requirements
- Naming conventions

### Testing Information
### اطلاعات تست

- Test counts by type
- Test file names
- Coverage areas

### Observability
### قابلیت مشاهده

- OpenTelemetry configuration
- Observe Agent status
- Instrumentation details

### Key Patterns
### الگوهای کلیدی

- Service layer pattern
- Dependency injection
- Relationship safety
- API response format
- Error handling

### Knowledge Graph Relationships
### روابط گراف دانش

The JSON file includes a `knowledge_graph_relationships` section that defines:
- Project-level relationships
- Module-level relationships
- Entity relationships
- Service dependencies

## Graph Structure
## ساختار گراف

### Nodes (Entities)
### گره‌ها (موجودیت‌ها)

- **Project**: 6amMart Laravel
- **Module**: BeautyBooking
- **Entities**: 19 entities (BeautySalon, BeautyBooking, etc.)
- **Services**: 10 services
- **Controllers**: 54 controllers
- **Integration Points**: Store, User, Wallet, Payment, etc.

### Edges (Relationships)
### یال‌ها (روابط)

- **has_module**: Project → BeautyBooking
- **has_entities**: BeautyBooking → 19 entities
- **has_services**: BeautyBooking → 10 services
- **belongs_to**: Entity relationships
- **has_many**: One-to-many relationships
- **depends_on**: Service dependencies
- **integrates_with**: Integration points

## Example Queries
## نمونه پرس‌وجوها

Once imported into Graphiti, you can query:

1. **"What entities does BeautyBooking module have?"**
   - Returns: List of 19 entities with descriptions

2. **"What services does BeautyBookingService depend on?"**
   - Returns: BeautyCalendarService, BeautyCommissionService

3. **"How does BeautyBooking integrate with the existing system?"**
   - Returns: Store, User, Wallet, Payment, Chat, Notifications

4. **"What are the revenue models?"**
   - Returns: List of 10 revenue models with descriptions

5. **"What is the booking flow?"**
   - Returns: 9-step booking process

6. **"What are the code standards?"**
   - Returns: Strict types, PSR-12, bilingual comments, etc.

## Maintenance
## نگهداری

### Updating the Knowledge Base
### به‌روزرسانی پایگاه دانش

When the project changes:

1. Update `graphiti-knowledge-base.json` with new information
2. Update `graphiti-knowledge-base.md` for human readability
3. Re-import to Graphiti

### Key Areas to Update
### مناطق کلیدی برای به‌روزرسانی

- New entities or services
- Changed relationships
- New integration points
- Updated code standards
- New features or revenue models

## Graphiti Server Configuration
## پیکربندی سرور Graphiti

The Graphiti server is configured in `~/.cursor/mcp.json`:

```json
{
  "graphiti-memory": {
    "type": "http",
    "url": "http://localhost:8001/mcp/"
  }
}
```

Ensure the server is running:
```bash
# Check if Graphiti is running
curl http://localhost:8001/mcp/health
```

## Benefits of Using Graphiti
## مزایای استفاده از Graphiti

1. **Context Preservation**: Maintains project knowledge across conversations
2. **Relationship Discovery**: Understands how components connect
3. **Query Capabilities**: Can answer complex questions about the codebase
4. **Knowledge Graph**: Visual representation of project structure
5. **Semantic Search**: Find information by meaning, not just keywords

## Next Steps
## مراحل بعدی

1. **Import the JSON**: Import `graphiti-knowledge-base.json` into Graphiti
2. **Verify Import**: Query Graphiti to confirm data is loaded
3. **Test Queries**: Try example queries to verify functionality
4. **Update Regularly**: Keep the knowledge base updated as the project evolves

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

### Import Fails
### وارد کردن ناموفق است

- Verify JSON is valid: `python3 -m json.tool graphiti-knowledge-base.json`
- Check Graphiti API documentation for correct format
- Ensure server is running and accessible

### Data Not Found
### داده پیدا نشد

- Verify import was successful
- Check Graphiti query syntax
- Review knowledge base structure

---

**Created**: 2025-11-28
**Created Timestamp**: 20251128-170306
**Purpose**: Knowledge base for Graphiti MCP server
**Location**: `tmp/graphiti-knowledge-base.json` and `tmp/graphiti-knowledge-base.md`

