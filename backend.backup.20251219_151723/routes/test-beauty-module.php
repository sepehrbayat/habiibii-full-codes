<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/test-beauty-module', function () {
    $admin = \App\Models\Admin::first();
    if (!$admin) {
        return response()->json(['error' => 'No admin found'], 404);
    }
    
    Auth::guard('admin')->login($admin);
    
    // Get modules exactly as header does
    $adminUser = Auth::guard('admin')->user();
    $modules = \App\Models\Module::when($adminUser && $adminUser->zone_id, function($query) use ($adminUser){
        $query->where(function($q) use ($adminUser){
            $q->where('all_zone_service', 1)
              ->orWhereHas('zones', function($zoneQuery) use ($adminUser){
                  $zoneQuery->where('zone_id', $adminUser->zone_id);
              });
        });
    })->Active()->get();
    
    // Render header
    try {
        $html = view('layouts.admin.partials._header')->render();
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'View render failed',
            'message' => $e->getMessage()
        ], 500);
    }
    
    // Check for beauty module
    $hasBeauty = strpos($html, 'data-module-type="beauty"') !== false;
    $beautyCount = substr_count($html, 'data-module-type="beauty"');
    
    // Debug addon_published_status
    $addonStatus = addon_published_status('BeautyBooking');
    $infoPath = base_path("Modules/BeautyBooking/Addon/info.php");
    $infoExists = file_exists($infoPath);
    $infoData = null;
    if ($infoExists) {
        try {
            $infoData = include $infoPath;
        } catch (\Exception $e) {
            $infoData = ['error' => $e->getMessage()];
        }
    }
    
    return response()->json([
        'modules_in_query' => $modules->count(),
        'module_types' => $modules->pluck('module_type')->toArray(),
        'module_ids' => $modules->pluck('id')->toArray(),
        'html_length' => strlen($html),
        'has_beauty_in_html' => $hasBeauty,
        'beauty_count_in_html' => $beautyCount,
        'addon_status' => $addonStatus,
        'addon_status_debug' => [
            'function_result' => $addonStatus,
            'info_path' => $infoPath,
            'info_exists' => $infoExists,
            'info_data' => $infoData,
            'base_path' => base_path(),
            'current_dir' => getcwd(),
        ],
        'html_snippet' => $hasBeauty 
            ? substr($html, strpos($html, 'data-module-type="beauty"') - 100, 300)
            : 'Beauty module not found in HTML'
    ]);
})->middleware('web');

