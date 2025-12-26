<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Services\BeautyRankingService;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use Illuminate\Support\Facades\Validator;

/**
 * Beauty Salon Controller (Customer API)
 * کنترلر سالن زیبایی (API مشتری)
 */
class BeautySalonController extends Controller
{
    use BeautyApiResponse;

    public function __construct(
        private BeautySalon $salon,
        private BeautyRankingService $rankingService
    ) {}

    /**
     * Search salons with ranking
     * جستجوی سالن‌ها با رتبه‌بندی
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @queryParam search string Search query for salon name. Example: "hair salon"
     * @queryParam latitude float User latitude for location-based ranking. Example: 35.6892
     * @queryParam longitude float User longitude for location-based ranking. Example: 51.3890
     * @queryParam category_id integer Filter by service category ID. Example: 1
     * @queryParam business_type string Filter by business type (salon/clinic). Example: salon
     * @queryParam min_rating float Minimum rating filter (0-5). Example: 4.0
     * @queryParam radius float Search radius in kilometers (1-100). Example: 10
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Salon Name",
     *       "business_type": "salon",
     *       "avg_rating": 4.5,
     *       "total_reviews": 100,
     *       "total_bookings": 500,
     *       "is_verified": true,
     *       "is_featured": false,
     *       "badges": ["top_rated"],
     *       "latitude": 35.6892,
     *       "longitude": 51.3890
     *     }
     *   ],
     *   "total": 10
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "validation",
     *       "message": "The radius must be between 1 and 100."
     *     }
     *   ]
     * }
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'category_id' => 'nullable|integer|exists:beauty_service_categories,id',
            'business_type' => 'nullable|in:salon,clinic',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'radius' => 'nullable|numeric|min:1|max:100', // in kilometers
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $filters = [
            'category_id' => $request->category_id,
            'business_type' => $request->business_type,
            'min_rating' => $request->min_rating,
        ];

        // Generate cache key based on search parameters
        // تولید کلید cache بر اساس پارامترهای جستجو
        $cacheKey = 'beauty_search_' . md5(json_encode([
            'search' => $request->search,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'filters' => $filters,
        ]));
        
        // Cache search results for 5 minutes
        // Cache کردن نتایج جستجو برای 5 دقیقه
        $ttl = config('beautybooking.cache.search_ttl', 300);
        
        $formattedSalons = Cache::remember($cacheKey, $ttl, function () use ($request, $filters) {
            $salons = $this->rankingService->getRankedSalons(
                $request->search,
                $request->latitude,
                $request->longitude,
                array_filter($filters)
            );

            // Eager load relationships to prevent N+1 queries
            // بارگذاری eager روابط برای جلوگیری از N+1 queries
            $salons->load(['store', 'badges']);

            return $salons->map(function ($salon) use ($request) {
                return $this->formatSalonForApi(
                    $salon,
                    false,
                    $request->latitude,
                    $request->longitude
                );
            })->values();
        });

        return $this->simpleListResponse(
            $formattedSalons,
            'messages.data_retrieved_successfully',
            ['total' => $formattedSalons->count()]
        );
    }

    /**
     * Show salon details
     * نمایش جزئیات سالن
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $salon = $this->salon->with([
            'store',
            'services' => function($q) {
                $q->where('status', 1);
            },
            'staff' => function($q) {
                $q->where('status', 1);
            },
            'badges' => function($q) {
                $q->active();
            },
            'reviews' => function($q) {
                $q->approved()->latest()->limit(10);
            },
        ])
        ->active()
        ->findOrFail($id);

        return $this->successResponse(
            'messages.data_retrieved_successfully',
            $this->formatSalonForApi($salon, true)
        );
    }

    /**
     * Get popular salons
     * دریافت سالن‌های محبوب
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('per_page', $request->get('limit', 25));
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        // Cache TTL: 1 hour (from config or default)
        // زمان cache: 1 ساعت (از config یا پیش‌فرض)
        $ttl = config('beautybooking.cache.popular_salons_ttl', 3600);
        $cacheKey = 'beauty_salons_popular_all';
        
        // Get all popular salons (cached)
        // دریافت تمام سالن‌های محبوب (cache شده)
        $allSalons = Cache::remember($cacheKey, $ttl, function () {
            return $this->salon->active()
                ->with(['store', 'badges'])
                ->where('total_bookings', '>', 10)
                ->orderByDesc('total_bookings')
                ->get();
        });

        // Apply pagination manually
        // اعمال صفحه‌بندی به صورت دستی
        $total = $allSalons->count();
        $paginatedSalons = $allSalons->slice($offset, $limit)->values();

        $formattedSalons = $paginatedSalons->map(function ($salon) {
            return $this->formatSalonForApi($salon);
        })->values();

        $perPage = $limit;
        $currentPage = $page;
        $lastPage = (int)ceil($total / $perPage);

        return response()->json([
            'message' => translate('messages.data_retrieved_successfully'),
            'data' => $formattedSalons,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
        ], 200);
    }

    /**
     * Get top rated salons
     * دریافت سالن‌های دارای رتبه بالا
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function topRated(Request $request): JsonResponse
    {
        $limit = $request->get('per_page', $request->get('limit', 25));
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        // Cache TTL: 1 hour (from config or default)
        // زمان cache: 1 ساعت (از config یا پیش‌فرض)
        $ttl = config('beautybooking.cache.top_rated_salons_ttl', 3600);
        $cacheKey = 'beauty_salons_top_rated_all';
        
        // Get all top-rated salons (cached)
        // دریافت تمام سالن‌های دارای رتبه بالا (cache شده)
        $allSalons = Cache::remember($cacheKey, $ttl, function () {
            return $this->salon->topRated()
                ->with(['store', 'badges'])
                ->get();
        });

        // Apply pagination manually
        // اعمال صفحه‌بندی به صورت دستی
        $total = $allSalons->count();
        $paginatedSalons = $allSalons->slice($offset, $limit)->values();

        $formattedSalons = $paginatedSalons->map(function ($salon) {
            return $this->formatSalonForApi($salon);
        })->values();

        $perPage = $limit;
        $currentPage = $page;
        $lastPage = (int)ceil($total / $perPage);

        return response()->json([
            'message' => translate('messages.data_retrieved_successfully'),
            'data' => $formattedSalons,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
        ], 200);
    }

    /**
     * Get monthly top rated salons
     * دریافت سالن‌های دارای رتبه بالا ماهانه
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function monthlyTopRated(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'year' => 'nullable|integer|min:2020|max:' . (date('Y') + 1),
            'month' => 'nullable|integer|min:1|max:12',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $limit = $request->get('per_page', $request->get('limit', 25));
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $year = $request->get('year', \Carbon\Carbon::now()->subMonth()->year);
        $month = $request->get('month', \Carbon\Carbon::now()->subMonth()->month);

        // Try to get monthly report from database
        // تلاش برای دریافت گزارش ماهانه از دیتابیس
        $monthlyReport = \Modules\BeautyBooking\Entities\BeautyMonthlyReport::where('report_type', 'top_rated_salons')
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($monthlyReport && !empty($monthlyReport->salon_ids)) {
            // Get salons from monthly report with pagination
            // دریافت سالن‌ها از گزارش ماهانه با صفحه‌بندی
            $salonIds = $monthlyReport->salon_ids;
            $total = count($salonIds);
            
            // Apply pagination to salon IDs
            // اعمال صفحه‌بندی به شناسه‌های سالن
            $paginatedIds = array_slice($salonIds, $offset, $limit);
            
            $salons = $this->salon->whereIn('id', $paginatedIds)
                ->with(['store', 'badges'])
                ->get()
                ->sortBy(function($salon) use ($paginatedIds) {
                    return array_search($salon->id, $paginatedIds);
                })
                ->values();
        } else {
            // Fallback to real-time calculation with pagination
            // بازگشت به محاسبه در زمان واقعی با صفحه‌بندی
            $salons = $this->salon->topRated()
                ->with(['store', 'badges'])
                ->orderByDesc('avg_rating')
                ->paginate($limit, ['*'], 'page', $page);
            
            $total = $salons->total();
            $salons = $salons->items();
        }
        
        // Eager load relationships to prevent N+1 queries
        // بارگذاری eager روابط برای جلوگیری از N+1 queries
        if (is_array($salons) || $salons instanceof \Illuminate\Support\Collection) {
            $salons = collect($salons);
        $salons->load(['store', 'badges']);
        }

        $formattedSalons = collect($salons)->map(function ($salon) {
            return $this->formatSalonForApi($salon);
        })->values();

        // Return paginated response
        // برگرداندن پاسخ صفحه‌بندی شده
        $perPage = $limit;
        $currentPage = $page;
        $lastPage = (int)ceil(($total ?? $formattedSalons->count()) / $perPage);

        return response()->json([
            'message' => translate('messages.data_retrieved_successfully'),
            'data' => $formattedSalons,
            'total' => $total ?? $formattedSalons->count(),
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
                'year' => $year,
                'month' => $month,
        ], 200);
    }

    /**
     * Get trending clinics
     * دریافت کلینیک‌های ترند
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function trendingClinics(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'year' => 'nullable|integer|min:2020|max:' . (date('Y') + 1),
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $year = $request->get('year', \Carbon\Carbon::now()->subMonth()->year);
        $month = $request->get('month', \Carbon\Carbon::now()->subMonth()->month);

        // Try to get monthly report from database
        // تلاش برای دریافت گزارش ماهانه از دیتابیس
        $monthlyReport = \Modules\BeautyBooking\Entities\BeautyMonthlyReport::where('report_type', 'trending_clinics')
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($monthlyReport && !empty($monthlyReport->salon_ids)) {
            // Get clinics from monthly report
            // دریافت کلینیک‌ها از گزارش ماهانه
            $salons = $this->salon->whereIn('id', $monthlyReport->salon_ids)
                ->where('business_type', 'clinic')
                ->with(['store', 'badges'])
                ->get()
                ->sortBy(function($salon) use ($monthlyReport) {
                    return array_search($salon->id, $monthlyReport->salon_ids);
                })
                ->values();
        } else {
            // Fallback to real-time calculation
            // بازگشت به محاسبه در زمان واقعی
            $dateFrom = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
            $dateTo = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

            $salons = $this->salon->where('business_type', 'clinic')
                ->whereHas('bookings', function($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('booking_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
                      ->where('status', '!=', 'cancelled');
                })
                ->withCount(['bookings' => function($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('booking_date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
                      ->where('status', '!=', 'cancelled');
                }])
                ->with(['store', 'badges'])
                ->orderByDesc('bookings_count')
                ->limit(50)
                ->get();
        }
        
        // Eager load relationships to prevent N+1 queries
        // بارگذاری eager روابط برای جلوگیری از N+1 queries
        $salons->load(['store', 'badges']);

        $formattedSalons = $salons->map(function ($salon) {
            return $this->formatSalonForApi($salon);
        })->values();

        return $this->simpleListResponse(
            $formattedSalons,
            'messages.data_retrieved_successfully',
            [
                'year' => $year,
                'month' => $month,
                'total' => $formattedSalons->count(),
            ]
        );
    }

    /**
     * Format salon data for API response
     * فرمت داده سالن برای پاسخ API
     *
     * @param BeautySalon $salon
     * @param bool $includeDetails
     * @param float|null $userLatitude
     * @param float|null $userLongitude
     * @return array
     */
    private function formatSalonForApi(BeautySalon $salon, bool $includeDetails = false, ?float $userLatitude = null, ?float $userLongitude = null): array
    {
        $distance = null;
        if ($userLatitude !== null && $userLongitude !== null && $salon->store?->latitude !== null && $salon->store?->longitude !== null) {
            $distance = $this->calculateDistance(
                $userLatitude,
                $userLongitude,
                (float)$salon->store->latitude,
                (float)$salon->store->longitude
            );
        }
        
        $isOpen = $this->isSalonOpen($salon);
        
        $data = [
            'id' => $salon->id,
            'name' => $salon->store->name ?? '',
            'business_type' => $salon->business_type,
            'avg_rating' => $salon->avg_rating,
            'total_reviews' => $salon->total_reviews,
            'total_bookings' => $salon->total_bookings,
            'is_verified' => $salon->is_verified,
            'is_featured' => $salon->is_featured,
            'badges' => $salon->badges_list ?? [],
            'latitude' => $salon->store->latitude ?? null,
            'longitude' => $salon->store->longitude ?? null,
            'address' => $salon->store->address ?? null,
            'image' => $salon->store->image ? asset('storage/' . $salon->store->image) : null,
            'phone' => $salon->store->phone ?? null,
            'email' => $salon->store->email ?? null,
            'opening_time' => $salon->store->opening_time ?? null,
            'closing_time' => $salon->store->closing_time ?? null,
            'is_open' => $isOpen,
            'distance' => $distance,
            'store' => [
                'id' => $salon->store->id ?? null,
                'name' => $salon->store->name ?? '',
                'address' => $salon->store->address ?? null,
                'image' => $salon->store->image ? asset('storage/' . $salon->store->image) : null,
                'latitude' => $salon->store->latitude ?? null,
                'longitude' => $salon->store->longitude ?? null,
                'phone' => $salon->store->phone ?? null,
                'email' => $salon->store->email ?? null,
                'opening_time' => $salon->store->opening_time ?? null,
                'closing_time' => $salon->store->closing_time ?? null,
            ],
        ];

        if ($includeDetails) {
            // Eager load relationships to prevent N+1 queries
            // بارگذاری eager روابط برای جلوگیری از N+1 queries
            $salon->loadMissing(['services', 'staff', 'reviews.user']);
            
            $data['services'] = $salon->services->map(function($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'duration_minutes' => $service->duration_minutes,
                    'price' => $service->price,
                    'image' => $service->image ? asset('storage/' . $service->image) : null,
                ];
            });

            $data['staff'] = $salon->staff->map(function($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'avatar' => $staff->avatar ? asset('storage/' . $staff->avatar) : null,
                    'specializations' => $staff->specializations ?? [],
                ];
            });

            $data['working_hours'] = $salon->working_hours;
            $data['reviews'] = $salon->reviews->map(function($review) {
                return [
                    'id' => $review->id,
                    'user_name' => ($review->user->f_name ?? '') . ' ' . ($review->user->l_name ?? ''),
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                ];
            });
        }

        return $data;
    }

    /**
     * Determine if salon is currently open based on working hours
     * تعیین باز بودن سالن بر اساس ساعات کاری
     *
     * @param BeautySalon $salon
     * @return bool
     */
    private function isSalonOpen(BeautySalon $salon): bool
    {
        $workingHours = $salon->working_hours ?? [];
        $now = now();
        $dayKey = strtolower($now->format('l'));
        
        if (!isset($workingHours[$dayKey]['open'], $workingHours[$dayKey]['close'])) {
            return false;
        }
        
        $currentTime = $now->format('H:i');
        return $currentTime >= $workingHours[$dayKey]['open']
            && $currentTime <= $workingHours[$dayKey]['close'];
    }
    
    /**
     * Calculate distance in kilometers using Haversine formula
     * محاسبه فاصله بر اساس فرمول هاورساین
     *
     * @param float $userLat
     * @param float $userLng
     * @param float $salonLat
     * @param float $salonLng
     * @return float
     */
    private function calculateDistance(float $userLat, float $userLng, float $salonLat, float $salonLng): float
    {
        $earthRadius = 6371; // km
        
        $latFrom = deg2rad($userLat);
        $lonFrom = deg2rad($userLng);
        $latTo = deg2rad($salonLat);
        $lonTo = deg2rad($salonLng);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return round($earthRadius * $angle, 2);
    }
}

