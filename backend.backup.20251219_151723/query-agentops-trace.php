<?php

/**
 * Query AgentOps Trace Helper
 * کمک‌کننده کوئری Trace در AgentOps
 * 
 * This script helps query AgentOps for specific trace information
 * این اسکریپت به کوئری AgentOps برای اطلاعات trace خاص کمک می‌کند
 * 
 * Usage:
 *   php query-agentops-trace.php <trace_id>
 *   php query-agentops-trace.php <span_id> --span
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "========================================\n";
echo "AgentOps Trace Query Helper\n";
echo "کمک‌کننده کوئری Trace در AgentOps\n";
echo "========================================\n\n";

// Get trace ID or span ID from command line
$id = $argv[1] ?? null;
$isSpan = isset($argv[2]) && $argv[2] === '--span';

if (!$id) {
    echo "Usage:\n";
    echo "  php query-agentops-trace.php <trace_id>\n";
    echo "  php query-agentops-trace.php <span_id> --span\n";
    echo "\n";
    echo "To get trace IDs:\n";
    echo "  1. Check AgentOps dashboard\n";
    echo "  2. Check OpenTelemetry logs\n";
    echo "  3. Check Laravel application logs\n";
    exit(1);
}

echo "Querying AgentOps for " . ($isSpan ? "span" : "trace") . " ID: {$id}\n";
echo "کوئری AgentOps برای شناسه " . ($isSpan ? "span" : "trace") . ": {$id}\n";
echo "\n";

// Note: This script demonstrates the concept
// In practice, you would use the AgentOps MCP tools or API
// توجه: این اسکریپت مفهوم را نشان می‌دهد
// در عمل، باید از ابزارهای AgentOps MCP یا API استفاده کنید

echo "To query AgentOps, use one of these methods:\n";
echo "برای کوئری AgentOps، از یکی از این روش‌ها استفاده کنید:\n";
echo "\n";

if ($isSpan) {
    echo "1. Use AgentOps MCP tool:\n";
    echo "   mcp_agentops-mcp_get_span(span_id: '{$id}')\n";
    echo "\n";
    echo "2. Use AgentOps API directly:\n";
    echo "   curl -X GET 'https://api.agentops.ai/v1/spans/{$id}' \\\n";
    echo "     -H 'Authorization: Bearer YOUR_API_KEY'\n";
} else {
    echo "1. Use AgentOps MCP tool:\n";
    echo "   mcp_agentops-mcp_get_trace(trace_id: '{$id}')\n";
    echo "\n";
    echo "2. Use AgentOps API directly:\n";
    echo "   curl -X GET 'https://api.agentops.ai/v1/traces/{$id}' \\\n";
    echo "     -H 'Authorization: Bearer YOUR_API_KEY'\n";
}

echo "\n";
echo "3. Check AgentOps Dashboard:\n";
echo "   - Navigate to Traces section\n";
echo "   - Search for trace/span ID: {$id}\n";
echo "   - View detailed information\n";

echo "\n";
echo "========================================\n";
echo "Note: This is a helper script\n";
echo "توجه: این یک اسکریپت کمک‌کننده است\n";
echo "========================================\n";
echo "\n";
echo "For actual trace queries, use:\n";
echo "برای کوئری‌های واقعی trace، استفاده کنید:\n";
echo "1. AgentOps MCP tools (via Cursor AI)\n";
echo "2. AgentOps Dashboard (web interface)\n";
echo "3. AgentOps API (direct HTTP calls)\n";

