<?php
/**
 * Advanced Diagnostic Test for Beauty Module Visibility Issue
 * تست پیشرفته تشخیصی برای مشکل نمایش ماژول Beauty
 * 
 * This script performs comprehensive testing to find why Beauty module
 * is in server HTML but not visible in browser
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  Advanced Beauty Module Visibility Diagnostic Test           ║\n";
echo "║  تست پیشرفته تشخیصی برای مشکل نمایش ماژول Beauty            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$results = [
    'passed' => [],
    'failed' => [],
    'warnings' => []
];

// Test 1: Check if Beauty module exists in database
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 1: Database Module Check\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$beautyModule = \App\Models\Module::where('module_type', 'beauty')->first();
if ($beautyModule) {
    echo "✓ Beauty module exists in database\n";
    echo "  - ID: {$beautyModule->id}\n";
    echo "  - Type: {$beautyModule->module_type}\n";
    echo "  - Name: {$beautyModule->module_name}\n";
    echo "  - Status: " . ($beautyModule->status ? 'Active (1)' : 'Inactive (0)') . "\n";
    echo "  - all_zone_service: {$beautyModule->all_zone_service}\n";
    $results['passed'][] = 'Beauty module exists in database';
} else {
    echo "✗ Beauty module NOT found in database!\n";
    $results['failed'][] = 'Beauty module not in database';
    exit(1);
}

// Test 2: Check addon published status
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 2: Addon Published Status Check\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$addonStatus = addon_published_status('BeautyBooking');
echo "addon_published_status('BeautyBooking'): {$addonStatus}\n";
echo "Type: " . gettype($addonStatus) . "\n";
echo "Should show (== 1): " . (($addonStatus == 1) ? 'YES ✓' : 'NO ✗') . "\n";
echo "Should show (=== 1): " . (($addonStatus === 1) ? 'YES ✓' : 'NO ✗') . "\n";

if ($addonStatus == 1) {
    $results['passed'][] = 'BeautyBooking addon is published';
} else {
    $results['failed'][] = 'BeautyBooking addon is not published';
}

// Check info.php file
$infoPath = 'Modules/BeautyBooking/Addon/info.php';
if (file_exists($infoPath)) {
    $info = include $infoPath;
    echo "\ninfo.php contents:\n";
    echo "  - is_published: " . ($info['is_published'] ?? 'NOT SET') . "\n";
    echo "  - name: " . ($info['name'] ?? 'NOT SET') . "\n";
} else {
    echo "\n✗ info.php file not found at: {$infoPath}\n";
    $results['failed'][] = 'info.php file missing';
}

// Test 3: Check admin user and zone
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 3: Admin User & Zone Check\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$admin = \App\Models\Admin::first();
if ($admin) {
    echo "✓ Admin user found\n";
    echo "  - ID: {$admin->id}\n";
    echo "  - zone_id: " . ($admin->zone_id ?? 'NULL') . "\n";
    echo "  - role_id: {$admin->role_id}\n";
    
    // Simulate login
    auth('admin')->login($admin);
    echo "  - Logged in as admin\n";
    
    $results['passed'][] = 'Admin user exists and logged in';
} else {
    echo "✗ No admin user found!\n";
    $results['failed'][] = 'No admin user';
    exit(1);
}

// Test 4: Test the exact query from header
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 4: Module Query Test (Exact Header Logic)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$adminUser = auth('admin')->user();
$modules = \App\Models\Module::when($adminUser && $adminUser->zone_id, function($query) use ($adminUser){
    $query->where(function($q) use ($adminUser){
        $q->where('all_zone_service', 1)
          ->orWhereHas('zones', function($zoneQuery) use ($adminUser){
              $zoneQuery->where('zone_id', $adminUser->zone_id);
          });
    });
})->Active()->get();

echo "Query executed with zone_id: " . ($adminUser->zone_id ?? 'NULL') . "\n";
echo "Modules returned: {$modules->count()}\n\n";

if ($modules->count() == 0) {
    echo "✗ NO MODULES RETURNED!\n";
    $results['failed'][] = 'Query returns 0 modules';
} else {
    foreach ($modules as $key => $module) {
        echo "Module [" . ($key + 1) . "]:\n";
        echo "  - ID: {$module->id}\n";
        echo "  - Type: {$module->module_type}\n";
        echo "  - Name: {$module->module_name}\n";
        echo "  - Status: {$module->status}\n";
        echo "  - all_zone_service: {$module->all_zone_service}\n";
        echo "  - Zones: ";
        $zoneIds = $module->zones->pluck('id')->toArray();
        echo empty($zoneIds) ? 'NONE' : implode(', ', $zoneIds);
        echo "\n";
        
        // Test shouldShow logic
        $shouldShow = true;
        $beautyAddonStatus = null;
        if ($module->module_type == 'rental') {
            $shouldShow = addon_published_status('Rental') == 1;
        } elseif ($module->module_type == 'beauty') {
            $beautyAddonStatus = addon_published_status('BeautyBooking');
            $shouldShow = $beautyAddonStatus == 1;
        }
        
        echo "  - shouldShow: " . ($shouldShow ? 'YES ✓' : 'NO ✗') . "\n";
        if ($module->module_type == 'beauty') {
            echo "  - BeautyBooking addon status: {$beautyAddonStatus}\n";
        }
        
        if ($module->module_type == 'beauty' && !$shouldShow) {
            $results['failed'][] = "Beauty module shouldShow = false (addon status: {$beautyAddonStatus})";
        } elseif ($module->module_type == 'beauty' && $shouldShow) {
            $results['passed'][] = 'Beauty module shouldShow = true';
        }
    }
}

// Test 5: Test route generation
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 5: Route Generation Test\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$beautyModule = $modules->firstWhere('module_type', 'beauty');
if ($beautyModule && addon_published_status('BeautyBooking') == 1) {
    try {
        $route1 = route('admin.beautybooking.dashboard');
        echo "✓ Route 'admin.beautybooking.dashboard' exists: {$route1}\n";
        $results['passed'][] = 'Route admin.beautybooking.dashboard exists';
    } catch (\Exception $e) {
        echo "✗ Route 'admin.beautybooking.dashboard' does NOT exist\n";
        echo "  Error: " . $e->getMessage() . "\n";
        $results['failed'][] = 'Route admin.beautybooking.dashboard missing';
        
        try {
            $route2 = route('beautybooking.dashboard');
            echo "✓ Route 'beautybooking.dashboard' exists: {$route2}\n";
            $results['passed'][] = 'Route beautybooking.dashboard exists';
        } catch (\Exception $e2) {
            echo "✗ Route 'beautybooking.dashboard' also does NOT exist\n";
            echo "  Error: " . $e2->getMessage() . "\n";
            $results['failed'][] = 'Both beauty routes missing';
        }
    }
}

// Test 6: Render the actual view
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 6: View Rendering Test\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
try {
    $html = view('layouts.admin.partials._header')->render();
    $htmlLength = strlen($html);
    echo "✓ View rendered successfully\n";
    echo "  - HTML length: {$htmlLength} bytes\n";
    
    // Check for Beauty module in HTML
    $hasBeautyDataAttribute = strpos($html, 'data-module-type="beauty"') !== false;
    $hasBeautyModuleName = strpos($html, 'Beauty Booking') !== false || strpos($html, 'BeautyBooking') !== false;
    $hasBeautyModuleId = strpos($html, 'data-module-id="2"') !== false;
    $hasDebugComment = strpos($html, 'DEBUG: Modules count:') !== false;
    
    echo "\nHTML Content Analysis:\n";
    echo "  - Contains 'data-module-type=\"beauty\"': " . ($hasBeautyDataAttribute ? 'YES ✓' : 'NO ✗') . "\n";
    echo "  - Contains 'Beauty Booking' text: " . ($hasBeautyModuleName ? 'YES ✓' : 'NO ✗') . "\n";
    echo "  - Contains 'data-module-id=\"2\"': " . ($hasBeautyModuleId ? 'YES ✓' : 'NO ✗') . "\n";
    echo "  - Contains debug comment: " . ($hasDebugComment ? 'YES ✓' : 'NO ✗') . "\n";
    
    if ($hasDebugComment) {
        // Extract debug comment
        preg_match('/<!-- DEBUG: Modules count: ([^,]+), Types: ([^>]+) -->/', $html, $matches);
        if (!empty($matches)) {
            echo "\n  Debug comment found:\n";
            echo "    - Modules count: {$matches[1]}\n";
            echo "    - Types: {$matches[2]}\n";
        }
    }
    
    // Count module items in HTML
    preg_match_all('/data-module-id="(\d+)"/', $html, $moduleIdMatches);
    if (!empty($moduleIdMatches[1])) {
        echo "\n  Module IDs found in HTML:\n";
        foreach ($moduleIdMatches[1] as $moduleId) {
            echo "    - Module ID: {$moduleId}\n";
        }
    } else {
        echo "\n  ✗ NO module IDs found in HTML!\n";
        $results['failed'][] = 'No module IDs in rendered HTML';
    }
    
    // Check for module type attributes
    preg_match_all('/data-module-type="([^"]+)"/', $html, $moduleTypeMatches);
    if (!empty($moduleTypeMatches[1])) {
        echo "\n  Module types found in HTML:\n";
        foreach ($moduleTypeMatches[1] as $moduleType) {
            echo "    - Type: {$moduleType}\n";
        }
        
        if (!in_array('beauty', $moduleTypeMatches[1])) {
            echo "\n  ✗ 'beauty' type NOT found in HTML!\n";
            $results['failed'][] = 'Beauty module type not in HTML';
        } else {
            $results['passed'][] = 'Beauty module type found in HTML';
        }
    }
    
    // Extract the modules section HTML
    preg_match('/<div class="__nav-module-items">(.*?)<\/div>\s*<\/div>\s*<\/div>/s', $html, $modulesSectionMatches);
    if (!empty($modulesSectionMatches[1])) {
        $modulesSectionHtml = $modulesSectionMatches[1];
        echo "\n  Modules section HTML length: " . strlen($modulesSectionHtml) . " bytes\n";
        
        // Count <a> tags with set-module class
        preg_match_all('/<a[^>]*class="[^"]*set-module[^"]*"[^>]*>/', $modulesSectionHtml, $moduleLinkMatches);
        $moduleLinkCount = count($moduleLinkMatches[0]);
        echo "  Module links (<a> with set-module class): {$moduleLinkCount}\n";
        
        if ($moduleLinkCount < 2) {
            echo "  ⚠️ WARNING: Expected 2 module links, found {$moduleLinkCount}\n";
            $results['warnings'][] = "Only {$moduleLinkCount} module link(s) found in HTML";
        }
        
        // Check if beauty module link exists
        $hasBeautyLink = strpos($modulesSectionHtml, 'data-module-type="beauty"') !== false;
        echo "  Beauty module link exists: " . ($hasBeautyLink ? 'YES ✓' : 'NO ✗') . "\n";
        
        if (!$hasBeautyLink) {
            echo "\n  ✗ BEAUTY MODULE LINK NOT FOUND IN MODULES SECTION!\n";
            echo "  This is the problem - the module is filtered out before rendering.\n";
            $results['failed'][] = 'Beauty module link missing from modules section';
        }
    }
    
    $results['passed'][] = 'View rendered successfully';
    
} catch (\Exception $e) {
    echo "✗ View rendering FAILED!\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    $results['failed'][] = 'View rendering exception: ' . $e->getMessage();
}

// Test 7: Check for exceptions in shouldShow logic
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 7: Exception Testing in View Logic\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$beautyModule = $modules->firstWhere('module_type', 'beauty');
if ($beautyModule) {
    echo "Testing shouldShow logic for Beauty module:\n";
    
    try {
        $shouldShow = true;
        $beautyAddonStatus = addon_published_status('BeautyBooking');
        $shouldShow = $beautyAddonStatus == 1;
        
        echo "  - addon_published_status('BeautyBooking'): {$beautyAddonStatus}\n";
        echo "  - shouldShow result: " . ($shouldShow ? 'true' : 'false') . "\n";
        
        if (!$shouldShow) {
            echo "  ✗ shouldShow is FALSE - this will hide the module!\n";
            $results['failed'][] = 'shouldShow logic returns false for Beauty module';
        } else {
            echo "  ✓ shouldShow is TRUE - module should be visible\n";
            $results['passed'][] = 'shouldShow logic returns true';
        }
        
        // Test route generation
        try {
            $dashboardRoute = route('admin.beautybooking.dashboard');
            echo "  - Route generation: SUCCESS ({$dashboardRoute})\n";
            $results['passed'][] = 'Route generation successful';
        } catch (\Exception $e) {
            echo "  - Route generation: FAILED - " . $e->getMessage() . "\n";
            echo "    This might cause the module to be filtered out!\n";
            $results['warnings'][] = 'Route generation failed: ' . $e->getMessage();
        }
        
    } catch (\Exception $e) {
        echo "  ✗ Exception in shouldShow logic: " . $e->getMessage() . "\n";
        $results['failed'][] = 'Exception in shouldShow logic: ' . $e->getMessage();
    }
}

// Test 8: Check view cache
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 8: View Cache Check\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$viewCachePath = storage_path('framework/views');
$compiledViews = glob($viewCachePath . '/*.php');
echo "Compiled views count: " . count($compiledViews) . "\n";

// Find header view
$headerViewHash = md5('layouts.admin.partials._header');
$headerViewFile = null;
foreach ($compiledViews as $viewFile) {
    if (strpos(basename($viewFile), $headerViewHash) !== false) {
        $headerViewFile = $viewFile;
        break;
    }
}

if ($headerViewFile) {
    echo "Header view compiled file: " . basename($headerViewFile) . "\n";
    $fileTime = filemtime($headerViewFile);
    $fileAge = time() - $fileTime;
    echo "File age: " . round($fileAge / 60, 2) . " minutes\n";
    
    if ($fileAge > 300) { // 5 minutes
        echo "⚠️ WARNING: Compiled view is older than 5 minutes\n";
        $results['warnings'][] = 'Compiled view might be stale';
    }
} else {
    echo "Header view compiled file: NOT FOUND (will be created on next request)\n";
}

// Test 9: Check for JavaScript that might filter modules
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST 9: JavaScript Filter Check\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$jsFiles = [
    'public/assets/admin/js/view-pages/common.js',
    'public/assets/admin/js/theme.min.js',
    'public/assets/admin/js/vendor.min.js'
];

foreach ($jsFiles as $jsFile) {
    if (file_exists($jsFile)) {
        $content = file_get_contents($jsFile);
        $hasModuleFilter = preg_match('/__nav-module|set-module|module.*filter|hide.*module/i', $content);
        if ($hasModuleFilter) {
            echo "⚠️ Found potential module filtering in: {$jsFile}\n";
            $results['warnings'][] = "Potential module filtering in {$jsFile}";
        }
    }
}

// Final Summary
echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║  TEST SUMMARY                                                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "✓ PASSED: " . count($results['passed']) . " tests\n";
foreach ($results['passed'] as $test) {
    echo "  - {$test}\n";
}

if (!empty($results['warnings'])) {
    echo "\n⚠️ WARNINGS: " . count($results['warnings']) . " issues\n";
    foreach ($results['warnings'] as $warning) {
        echo "  - {$warning}\n";
    }
}

if (!empty($results['failed'])) {
    echo "\n✗ FAILED: " . count($results['failed']) . " tests\n";
    foreach ($results['failed'] as $test) {
        echo "  - {$test}\n";
    }
    echo "\n❌ ISSUE FOUND: " . $results['failed'][0] . "\n";
    exit(1);
} else {
    echo "\n✓ ALL TESTS PASSED - Beauty module should be visible!\n";
    echo "\nIf it's still not visible in browser:\n";
    echo "  1. Hard refresh browser (Ctrl+Shift+R)\n";
    echo "  2. Clear browser cache completely\n";
    echo "  3. Check Network tab - verify HTML response contains beauty module\n";
    echo "  4. Check if JavaScript is removing it after page load\n";
    exit(0);
}

