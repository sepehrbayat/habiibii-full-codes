<?php

declare(strict_types=1);

/**
 * Graphiti Initialization Check
 * بررسی مقداردهی اولیه Graphiti
 * 
 * This script MUST be run before every Cursor conversation
 * این اسکریپت باید قبل از هر گفتگوی Cursor اجرا شود
 */

$knowledgeBasePath = __DIR__ . '/graphiti-knowledge-base.json';

if (!file_exists($knowledgeBasePath)) {
    echo "⚠️ WARNING: Graphiti knowledge base not found at: {$knowledgeBasePath}\n";
    exit(1);
}

$knowledgeBase = json_decode(file_get_contents($knowledgeBasePath), true);

if (!$knowledgeBase) {
    echo "⚠️ ERROR: Failed to parse Graphiti knowledge base JSON\n";
    exit(1);
}

echo "✅ Graphiti Knowledge Base Loaded\n";
echo "📊 Project: " . ($knowledgeBase['project']['name'] ?? 'Unknown') . "\n";
echo "📅 Last Modified: " . ($knowledgeBase['metadata']['last_modified'] ?? 'Unknown') . "\n";

$taskHistory = $knowledgeBase['task_history'] ?? [];
$recentTasks = array_slice($taskHistory, 0, 5);

echo "\n📋 Recent Tasks (Last 5):\n";
foreach ($recentTasks as $index => $task) {
    echo "  " . ($index + 1) . ". " . ($task['task_name'] ?? 'Unknown') . " - " . ($task['timestamp'] ?? '') . "\n";
}

echo "\n✅ Graphiti initialization check complete. You may proceed.\n";

