<?php

declare(strict_types=1);

/**
 * Graphiti Auto-Update Function
 * تابع به‌روزرسانی خودکار Graphiti
 * 
 * This function is called automatically by Cursor after each task
 * این تابع به طور خودکار توسط Cursor پس از هر کار فراخوانی می‌شود
 */

require_once __DIR__ . '/graphiti-sync-workflow.php';

use GraphitiSync\GraphitiKnowledgeBaseUpdater;

/**
 * Auto-update Graphiti knowledge base after a task
 * به‌روزرسانی خودکار پایگاه دانش Graphiti پس از یک کار
 * 
 * @param string $taskName Task name/description
 * @param array $changes Array of changes made
 * @param array $affectedFiles Array of affected file paths
 * @return array Result with success status and messages
 */
function autoUpdateGraphiti(
    string $taskName,
    array $changes = [],
    array $affectedFiles = []
): array {
    $result = [
        'success' => false,
        'messages' => [],
        'sync_status' => 'not_attempted'
    ];
    
    try {
        $updater = new GraphitiKnowledgeBaseUpdater();
        
        // Update knowledge base
        // به‌روزرسانی پایگاه دانش
        if ($updater->updateAfterTask($taskName, $changes, $affectedFiles)) {
            $result['success'] = true;
            $result['messages'][] = "✅ Knowledge base updated successfully";
            
            // Attempt to sync with Graphiti server
            // تلاش برای همگام‌سازی با سرور Graphiti
            $syncResult = $updater->syncWithGraphiti();
            $result['sync_status'] = $syncResult['success'] ? 'success' : 'failed';
            
            if ($syncResult['success']) {
                $result['messages'][] = "✅ Synced with Graphiti server successfully";
            } else {
                $result['messages'][] = "⚠️ Graphiti sync failed: " . ($syncResult['error'] ?? 'Unknown error');
                if (isset($syncResult['note'])) {
                    $result['messages'][] = "   Note: " . $syncResult['note'];
                }
            }
        } else {
            $result['messages'][] = "❌ Failed to update knowledge base";
        }
    } catch (\Exception $e) {
        $result['messages'][] = "❌ Error: " . $e->getMessage();
    }
    
    return $result;
}

// CLI usage for testing
// استفاده از CLI برای تست
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $taskName = $argv[1] ?? 'Test Task';
    $changes = isset($argv[2]) ? json_decode($argv[2], true) : ['Test change'];
    $files = isset($argv[3]) ? json_decode($argv[3], true) : [];
    
    $result = autoUpdateGraphiti($taskName, $changes, $files);
    
    foreach ($result['messages'] as $message) {
        echo $message . "\n";
    }
    
    exit($result['success'] ? 0 : 1);
}

