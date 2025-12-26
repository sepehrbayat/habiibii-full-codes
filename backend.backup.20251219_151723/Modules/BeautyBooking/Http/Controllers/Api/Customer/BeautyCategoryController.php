<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Traits\BeautyApiResponse;

/**
 * Beauty Category Controller (Customer API)
 * کنترلر دسته‌بندی (API مشتری)
 */
class BeautyCategoryController extends Controller
{
    use BeautyApiResponse;

    public function __construct(
        private BeautyServiceCategory $category
    ) {}

    /**
     * List all categories
     * لیست تمام دسته‌بندی‌ها
     *
     * @return JsonResponse
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Hair Services",
     *       "image": "http://example.com/storage/categories/hair.jpg",
     *       "children": [
     *         {
     *           "id": 2,
     *           "name": "Haircut",
     *           "image": "http://example.com/storage/categories/haircut.jpg"
     *         }
     *       ]
     *     }
     *   ]
     * }
     */
    public function list(): JsonResponse
    {
        // Cache TTL: 24 hours (from config or default)
        // زمان cache: 24 ساعت (از config یا پیش‌فرض)
        $ttl = config('beautybooking.cache.categories_ttl', 86400);
        $cacheKey = 'beauty_categories_list';
        
        $categories = Cache::remember($cacheKey, $ttl, function () {
            return $this->category->where('status', 1)
                ->whereNull('parent_id') // Main categories only
                ->with('children')
                ->orderBy('sort_order')
                ->get();
        });

        $formatted = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $category->image_full_url ?? null,
                'children' => $category->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'image' => $child->image_full_url ?? null,
                    ];
                }),
            ];
        })->values();

        return $this->simpleListResponse($formatted);
    }
}

