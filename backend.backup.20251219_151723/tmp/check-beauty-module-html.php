<?php
/**
 * Script to check if Beauty module HTML is in the rendered header
 * اسکریپت برای بررسی اینکه آیا HTML ماژول Beauty در header رندر شده است
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate authenticated admin
$admin = \App\Models\Admin::first();
if ($admin) {
    auth('admin')->login($admin);
}

try {
    // Get the rendered header HTML
    $html = view('layouts.admin.partials._header')->render();
    
    // Check if beauty module is in HTML
    $hasBeauty = strpos($html, 'data-module-type="beauty"') !== false || 
                 strpos($html, 'Beauty Booking') !== false ||
                 strpos($html, 'module_type="beauty"') !== false;
    
    echo "=== Beauty Module HTML Check ===\n\n";
    echo "HTML length: " . strlen($html) . " bytes\n";
    echo "Beauty module in HTML: " . ($hasBeauty ? "YES ✓" : "NO ✗") . "\n\n";
    
    if ($hasBeauty) {
        echo "✓ Beauty module IS in the rendered HTML!\n";
        echo "If it's not visible in browser, check:\n";
        echo "  1. Browser cache (hard refresh: Ctrl+Shift+R)\n";
        echo "  2. JavaScript errors in console\n";
        echo "  3. CSS hiding the element\n";
    } else {
        echo "✗ Beauty module is NOT in the HTML!\n";
        echo "This means the view logic is filtering it out.\n";
        
        // Debug the modules
        $modules = \App\Models\Module::where('status', 1)->get();
        echo "\nModules in database:\n";
        foreach($modules as $m) {
            $shouldShow = true;
            if ($m->module_type == 'rental') {
                $shouldShow = addon_published_status('Rental') == 1;
            } elseif ($m->module_type == 'beauty') {
                $shouldShow = addon_published_status('BeautyBooking') == 1;
            }
            echo "  - {$m->module_type} (ID: {$m->id}): shouldShow=" . ($shouldShow ? 'YES' : 'NO') . "\n";
        }
    }
    
    // Extract module items from HTML
    preg_match_all('/data-module-type="([^"]+)"/', $html, $matches);
    if (!empty($matches[1])) {
        echo "\nModule types found in HTML:\n";
        foreach($matches[1] as $type) {
            echo "  - $type\n";
        }
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

