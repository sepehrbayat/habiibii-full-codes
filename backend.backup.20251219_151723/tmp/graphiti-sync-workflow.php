<?php

declare(strict_types=1);

/**
 * Graphiti Knowledge Base Sync Workflow
 * گردش کار همگام‌سازی پایگاه دانش Graphiti
 * 
 * This script updates the Graphiti knowledge base after each task
 * این اسکریپت پایگاه دانش Graphiti را پس از هر کار به‌روزرسانی می‌کند
 */

namespace GraphitiSync;

class GraphitiKnowledgeBaseUpdater
{
    private string $knowledgeBasePath;
    private string $graphitiServerUrl;
    
    public function __construct(
        string $knowledgeBasePath = __DIR__ . '/graphiti-knowledge-base.json',
        string $graphitiServerUrl = 'http://localhost:8001/mcp/'
    ) {
        $this->knowledgeBasePath = $knowledgeBasePath;
        $this->graphitiServerUrl = $graphitiServerUrl;
    }
    
    /**
     * Update knowledge base with task information
     * به‌روزرسانی پایگاه دانش با اطلاعات کار
     * 
     * @param string $taskName Task name/description
     * @param array $changes Array of changes made
     * @param array $affectedFiles Array of affected file paths
     * @return bool Success status
     */
    public function updateAfterTask(
        string $taskName,
        array $changes = [],
        array $affectedFiles = []
    ): bool {
        // Load existing knowledge base
        // بارگذاری پایگاه دانش موجود
        $knowledgeBase = $this->loadKnowledgeBase();
        
        if (!$knowledgeBase) {
            return false;
        }
        
        // Add task history
        // افزودن تاریخچه کار
        if (!isset($knowledgeBase['task_history'])) {
            $knowledgeBase['task_history'] = [];
        }
        
        $taskEntry = [
            'task_name' => $taskName,
            'timestamp' => date('Y-m-d H:i:s'),
            'timestamp_iso' => date('c'),
            'changes' => $changes,
            'affected_files' => $affectedFiles,
        ];
        
        array_unshift($knowledgeBase['task_history'], $taskEntry);
        
        // Keep only last 50 tasks
        // نگه داشتن فقط 50 کار آخر
        if (count($knowledgeBase['task_history']) > 50) {
            $knowledgeBase['task_history'] = array_slice($knowledgeBase['task_history'], 0, 50);
        }
        
        // Update last modified timestamp
        // به‌روزرسانی زمان آخرین تغییر
        $knowledgeBase['metadata']['last_modified'] = date('Y-m-d H:i:s');
        $knowledgeBase['metadata']['last_modified_timestamp'] = date('Ymd-His');
        
        // Save updated knowledge base
        // ذخیره پایگاه دانش به‌روزرسانی شده
        return $this->saveKnowledgeBase($knowledgeBase);
    }
    
    /**
     * Sync with Graphiti server
     * همگام‌سازی با سرور Graphiti
     * 
     * @return array Response from Graphiti server
     */
    public function syncWithGraphiti(): array
    {
        $knowledgeBase = $this->loadKnowledgeBase();
        
        if (!$knowledgeBase) {
            return ['success' => false, 'error' => 'Failed to load knowledge base'];
        }
        
        // Try to send to Graphiti server
        // تلاش برای ارسال به سرور Graphiti
        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'error' => 'cURL extension not available',
                'note' => 'Install php-curl extension to enable Graphiti sync'
            ];
        }
        
        $ch = curl_init($this->graphitiServerUrl . 'import');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($knowledgeBase));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'note' => 'Graphiti server may not be running or endpoint may be different'
            ];
        }
        
        return [
            'success' => $httpCode === 200,
            'http_code' => $httpCode,
            'response' => json_decode($response, true) ?? $response
        ];
    }
    
    /**
     * Load knowledge base from file
     * بارگذاری پایگاه دانش از فایل
     * 
     * @return array|null Knowledge base data or null on error
     */
    private function loadKnowledgeBase(): ?array
    {
        if (!file_exists($this->knowledgeBasePath)) {
            return null;
        }
        
        $content = file_get_contents($this->knowledgeBasePath);
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $data;
    }
    
    /**
     * Save knowledge base to file
     * ذخیره پایگاه دانش در فایل
     * 
     * @param array $knowledgeBase Knowledge base data
     * @return bool Success status
     */
    private function saveKnowledgeBase(array $knowledgeBase): bool
    {
        $json = json_encode($knowledgeBase, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        if ($json === false) {
            return false;
        }
        
        return file_put_contents($this->knowledgeBasePath, $json) !== false;
    }
    
    /**
     * Get task summary for current session
     * دریافت خلاصه کار برای جلسه فعلی
     * 
     * @param int $limit Number of recent tasks to return
     * @return array Recent tasks
     */
    public function getRecentTasks(int $limit = 10): array
    {
        $knowledgeBase = $this->loadKnowledgeBase();
        
        if (!$knowledgeBase || !isset($knowledgeBase['task_history'])) {
            return [];
        }
        
        return array_slice($knowledgeBase['task_history'], 0, $limit);
    }
}

// CLI usage example
// مثال استفاده از CLI
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $updater = new GraphitiKnowledgeBaseUpdater();
    
    if ($argc < 2) {
        echo "Usage: php graphiti-sync-workflow.php <command> [options]\n";
        echo "Commands:\n";
        echo "  update <task_name> [changes_json] [files_json] - Update knowledge base\n";
        echo "  sync - Sync with Graphiti server\n";
        echo "  recent [limit] - Show recent tasks\n";
        exit(1);
    }
    
    $command = $argv[1];
    
    switch ($command) {
        case 'update':
            $taskName = $argv[2] ?? 'Unknown Task';
            $changes = isset($argv[3]) ? json_decode($argv[3], true) : [];
            $files = isset($argv[4]) ? json_decode($argv[4], true) : [];
            
            if ($updater->updateAfterTask($taskName, $changes, $files)) {
                echo "✅ Knowledge base updated successfully\n";
            } else {
                echo "❌ Failed to update knowledge base\n";
                exit(1);
            }
            break;
            
        case 'sync':
            $result = $updater->syncWithGraphiti();
            if ($result['success']) {
                echo "✅ Synced with Graphiti successfully\n";
            } else {
                echo "❌ Failed to sync: " . ($result['error'] ?? 'Unknown error') . "\n";
                if (isset($result['note'])) {
                    echo "Note: " . $result['note'] . "\n";
                }
            }
            break;
            
        case 'recent':
            $limit = isset($argv[2]) ? (int)$argv[2] : 10;
            $tasks = $updater->getRecentTasks($limit);
            echo json_encode($tasks, JSON_PRETTY_PRINT) . "\n";
            break;
            
        default:
            echo "Unknown command: $command\n";
            exit(1);
    }
}

