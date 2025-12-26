<?php

namespace App\Traits;

trait AddonHelper
{
    public function get_addons(): array
    {
        $dir = 'Modules';
        $directories = self::getDirectories($dir);
        $addons = [];
        foreach ($directories as $directory) {
            if($directory == 'Gateways'){
                $sub_dirs = self::getDirectories('Modules/' . $directory);
                if (in_array('Addon', $sub_dirs)) {
                    $addons[] = 'Modules/' . $directory;
                }
            }
        }

        $array = [];
        foreach ($addons as $item) {
            // Use base_path() to ensure correct path regardless of current working directory
            // استفاده از base_path() برای اطمینان از مسیر صحیح بدون توجه به دایرکتوری فعلی
            $info_path = base_path($item . '/Addon/info.php');
            if (file_exists($info_path)) {
                $full_data = include($info_path);
            $array[] = [
                'addon_name' => $full_data['name'],
                'software_id' => $full_data['software_id'],
                'is_published' => $full_data['is_published'],
            ];
            }
        }

        return $array;
    }

    public function get_addon_admin_routes(): array
    {
        try {
            $dir = 'Modules';
            $directories = self::getDirectories($dir);
            $addons = [];
            foreach ($directories as $directory) {
                if($directory == 'Gateways'){
                    $sub_dirs = self::getDirectories('Modules/' . $directory);
                    if (in_array('Addon', $sub_dirs)) {
                        $addons[] = 'Modules/' . $directory;
                    }
                }
            }

            $full_data = [];
            foreach ($addons as $item) {
                // Use base_path() to ensure correct path regardless of current working directory
                // استفاده از base_path() برای اطمینان از مسیر صحیح بدون توجه به دایرکتوری فعلی
                $info_path = base_path($item . '/Addon/info.php');
                $routes_path = base_path($item . '/Addon/admin_routes.php');
                if (file_exists($info_path)) {
                    $info = include($info_path);
                    if ($info['is_published'] && file_exists($routes_path)){
                        $full_data[] = include($routes_path);
                    }
                }
            }

            return is_array($full_data) ? $full_data : [];
        } catch (\Exception $e) {
            // Always return an array, even if there's an error
            // همیشه یک آرایه برگردان، حتی در صورت خطا
            return [];
        }
    }

    public function get_payment_publish_status(): array
    {
        $dir = 'Modules'; // Update the directory path to Modules/Gateways
        $directories = self::getDirectories($dir);
        // dd($directories);
        $addons = [];
        foreach ($directories as $directory) {
            $sub_dirs = self::getDirectories($dir . '/' . $directory); // Use $dir instead of 'Modules/'
            if($directory == 'Gateways'){
                if (in_array('Addon', $sub_dirs)) {
                    $addons[] = $dir . '/' . $directory; // Use $dir instead of 'Modules/'
                }
            }
        }

        $array = [];
        foreach ($addons as $item) {
            // Use base_path() to ensure correct path regardless of current working directory
            // استفاده از base_path() برای اطمینان از مسیر صحیح بدون توجه به دایرکتوری فعلی
            $info_path = base_path($item . '/Addon/info.php');
            if (file_exists($info_path)) {
                $full_data = include($info_path);
            $array[] = [
                'is_published' => $full_data['is_published'],
            ];
            }
        }

        return $array;
    }


    function getDirectories(string $path): array
    {
        $directories = [];
        $path= base_path($path);
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item == '..' || $item == '.')
                continue;
            if (is_dir($path . '/' . $item))
                $directories[] = $item;
        }
        return $directories;
    }
}
