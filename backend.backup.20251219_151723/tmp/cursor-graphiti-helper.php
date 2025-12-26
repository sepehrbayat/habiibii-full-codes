<?php

declare(strict_types=1);

/**
 * Cursor Graphiti Helper
 * کمک‌کننده Graphiti برای Cursor
 * 
 * This file is automatically called by Cursor after each task
 * این فایل به طور خودکار توسط Cursor پس از هر کار فراخوانی می‌شود
 */

require_once __DIR__ . '/graphiti-auto-update.php';

/**
 * Update Graphiti after completing a task
 * به‌روزرسانی Graphiti پس از تکمیل یک کار
 * 
 * This function should be called by Cursor automatically after each task
 * این تابع باید به طور خودکار توسط Cursor پس از هر کار فراخوانی شود
 */
function cursorUpdateGraphiti(
    string $taskDescription,
    array $changes = [],
    array $affectedFiles = []
): void {
    $result = autoUpdateGraphiti($taskDescription, $changes, $affectedFiles);
    
    // Output for Cursor to see
    // خروجی برای مشاهده Cursor
    foreach ($result['messages'] as $message) {
        echo $message . "\n";
    }
}

// Example usage (this will be called automatically by Cursor)
// مثال استفاده (این به طور خودکار توسط Cursor فراخوانی می‌شود)
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $taskName = $argv[1];
    $changes = isset($argv[2]) ? json_decode($argv[2], true) : [];
    $files = isset($argv[3]) ? json_decode($argv[3], true) : [];
    
    cursorUpdateGraphiti($taskName, $changes, $files);
}

