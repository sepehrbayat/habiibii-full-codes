<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Repositories\ModuleRepository;
use App\Models\Module;
use Illuminate\Http\Request;

$repo = new ModuleRepository(new Module());
$request = new Request();
$modules = $repo->getListWhere(null, [], ['stores'], 20);

echo "=== Testing Module View Rendering ===\n\n";
echo "Total modules from repository: " . $modules->count() . "\n\n";

foreach ($modules as $key => $module) {
    echo "Module #" . ($key + 1) . ":\n";
    echo "  - ID: " . $module->id . "\n";
    echo "  - Type: " . $module->module_type . "\n";
    echo "  - Name: " . $module->module_name . "\n";
    
    // Simulate view logic
    $shouldShow = true;
    if ($module->module_type == 'rental' && addon_published_status('Rental') != 1) {
        $shouldShow = false;
    }
    if ($module->module_type == 'beauty') {
        $beautyAddonStatus = addon_published_status('BeautyBooking');
        $shouldShow = $beautyAddonStatus == 1;
        echo "  - BeautyBooking addon status: " . $beautyAddonStatus . "\n";
    }
    
    echo "  - Should show in view: " . ($shouldShow ? "YES ✓" : "NO ✗") . "\n";
    echo "\n";
}

echo "=== Checking if Beauty module row would be in HTML ===\n";
$beautyModule = $modules->firstWhere('module_type', 'beauty');
if ($beautyModule) {
    $beautyAddonStatus = addon_published_status('BeautyBooking');
    $shouldShow = $beautyAddonStatus == 1;
    echo "Beauty module found: YES\n";
    echo "BeautyBooking addon status: " . $beautyAddonStatus . "\n";
    echo "Should render in HTML: " . ($shouldShow ? "YES ✓" : "NO ✗") . "\n";
    if ($shouldShow) {
        echo "\n✓ Beauty module row SHOULD be visible in the HTML!\n";
        echo "If it's not visible, check:\n";
        echo "  1. Browser DevTools → Elements → Search for 'beauty' or module ID 2\n";
        echo "  2. Check if there's a URL filter: ?module_type=grocery (should be 'all' or empty)\n";
        echo "  3. Check browser console for JavaScript errors\n";
    }
} else {
    echo "Beauty module NOT found in results!\n";
}

