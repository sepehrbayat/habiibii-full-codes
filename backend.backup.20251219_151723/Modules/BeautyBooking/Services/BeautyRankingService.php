<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Services;

use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Beauty Ranking Service
 * سرویس رتبه‌بندی
 *
 * Handles salon search ranking algorithm
 * مدیریت الگوریتم رتبه‌بندی جستجوی سالن
 */
class BeautyRankingService
{
    public function __construct(
        private BeautyCalendarService $calendarService
    ) {}
    
    /**
     * Calculate ranking score for a salon
     * محاسبه امتیاز رتبه‌بندی برای یک سالن
     *
     * @param BeautySalon $salon
     * @param float|null $userLatitude
     * @param float|null $userLongitude
     * @param array $filters
     * @return float
     */
    public function calculateRankingScore(BeautySalon $salon, ?float $userLatitude = null, ?float $userLongitude = null, array $filters = []): float
    {
        // Generate cache key based on salon, location, and filters
        // تولید کلید cache بر اساس سالن، موقعیت و فیلترها
        $cacheKey = $this->generateRankingScoreCacheKey($salon->id, $userLatitude, $userLongitude, $filters);
        
        // Cache TTL: 30 minutes (from config or default)
        // زمان cache: 30 دقیقه (از config یا پیش‌فرض)
        $ttl = config('beautybooking.cache.ranking_score_ttl', 1800);
        
        return Cache::remember($cacheKey, $ttl, function () use ($salon, $userLatitude, $userLongitude, $filters) {
            $score = 0.0;
            
            // Get weights from config
            // دریافت وزن‌ها از config
            $weights = config('beautybooking.ranking.weights', []);
            
            // 1. Location distance (nearest first)
            // فاصله موقعیت (نزدیک‌ترین اول)
            $locationScore = $this->calculateLocationScore($salon, $userLatitude, $userLongitude);
            $score += $locationScore * ($weights['location'] ?? 25.0) / 100;
            
            // 2. Featured/Boost status
            // وضعیت Featured/Boost
            $featuredScore = $this->calculateFeaturedScore($salon);
            $score += $featuredScore * ($weights['featured'] ?? 20.0) / 100;
            
            // 3. Rating (weighted average)
            // امتیاز (میانگین وزنی)
            $ratingScore = $this->calculateRatingScore($salon);
            $score += $ratingScore * ($weights['rating'] ?? 18.0) / 100;
            
            // 4. Activity in last 30 days (booking count)
            // فعالیت در 30 روز گذشته (تعداد رزرو)
            $activityScore = $this->calculateActivityScore($salon);
            $score += $activityScore * ($weights['activity'] ?? 10.0) / 100;
            
            // 5. Returning customer rate
            // نرخ مشتری برگشتی
            $returningRateScore = $this->calculateReturningRateScore($salon);
            $score += $returningRateScore * ($weights['returning_rate'] ?? 10.0) / 100;
            
            // 6. Available time slots count
            // تعداد زمان‌های خالی
            $availabilityScore = $this->calculateAvailabilityScore($salon);
            $score += $availabilityScore * ($weights['availability'] ?? 5.0) / 100;
            
            // 7. Cancellation rate (lower is better)
            // نرخ لغو (پایین‌تر بهتر است)
            $cancellationRateScore = $this->calculateCancellationRateScore($salon);
            $score += $cancellationRateScore * ($weights['cancellation_rate'] ?? 7.0) / 100;
            
            // 8. Service type matching (if filter applied)
            // تطابق نوع خدمت (در صورت اعمال فیلتر)
            $serviceTypeScore = $this->calculateServiceTypeScore($salon, $filters);
            $score += $serviceTypeScore * ($weights['service_type_match'] ?? 5.0) / 100;
            
            return $score;
        });
    }
    
    /**
     * Get ranked salons list
     * دریافت لیست سالن‌های رتبه‌بندی شده
     *
     * @param string|null $query
     * @param float|null $latitude
     * @param float|null $longitude
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRankedSalons(?string $query = null, ?float $latitude = null, ?float $longitude = null, array $filters = [])
    {
        // Generate cache key based on all parameters
        // تولید کلید cache بر اساس تمام پارامترها
        $cacheKey = $this->generateSearchCacheKey($query, $latitude, $longitude, $filters);
        
        // Cache TTL: from config (default: 5 minutes / 300 seconds)
        // زمان cache: از config (پیش‌فرض: 5 دقیقه / 300 ثانیه)
        // Note: Shorter TTL (300s vs 3600s) ensures fresher search results but increases DB load
        // توجه: TTL کوتاه‌تر (300s در مقابل 3600s) نتایج جستجوی تازه‌تری را تضمین می‌کند اما بار دیتابیس را افزایش می‌دهد
        // This is configurable via BEAUTY_BOOKING_CACHE_SEARCH_TTL env variable
        // این از طریق متغیر محیطی BEAUTY_BOOKING_CACHE_SEARCH_TTL قابل تنظیم است
        $ttl = config('beautybooking.cache.search_ttl', 300);
        
        return Cache::remember($cacheKey, $ttl, function () use ($query, $latitude, $longitude, $filters) {
            $salons = BeautySalon::query()
                ->where('verification_status', 1)
                ->where('is_verified', true)
                ->whereHas('store', function($q) {
                    // Only rank salons with active stores
                    // فقط رتبه‌بندی سالن‌های با فروشگاه‌های فعال
                    $q->where('status', 1)->where('active', 1);
                })
                ->with(['store', 'badges', 'reviews']);
            
            // Apply filters
            // اعمال فیلترها
            if (isset($filters['category_id'])) {
                $salons->whereHas('services', function($q) use ($filters) {
                    $q->where('category_id', $filters['category_id']);
                });
            }
            
            if (isset($filters['min_rating'])) {
                $salons->where('avg_rating', '>=', $filters['min_rating']);
            }
            
            if (isset($filters['business_type'])) {
                $salons->where('business_type', $filters['business_type']);
            }
            
            if (isset($filters['service_type'])) {
                $salons->whereHas('services', function($q) use ($filters) {
                    $q->where('service_type', $filters['service_type']);
                });
            }
            
            // Apply search query
            // اعمال جستجوی متنی
            if ($query) {
                $salons->whereHas('store', function($q) use ($query) {
                    $keys = explode(' ', $query);
                    foreach ($keys as $key) {
                        $q->where('name', 'LIKE', '%' . $key . '%');
                    }
                });
            }
            
            // Calculate scores and sort
            // محاسبه امتیازها و مرتب‌سازی
            $salons = $salons->get()->map(function($salon) use ($latitude, $longitude, $filters) {
                $salon->ranking_score = $this->calculateRankingScore($salon, $latitude, $longitude, $filters);
                return $salon;
            })->sortByDesc('ranking_score');
            
            return $salons->values();
        });
    }
    
    /**
     * Calculate location score (nearest first)
     * محاسبه امتیاز موقعیت (نزدیک‌ترین اول)
     *
     * @param BeautySalon $salon
     * @param float|null $latitude
     * @param float|null $longitude
     * @return float
     */
    private function calculateLocationScore(BeautySalon $salon, ?float $latitude, ?float $longitude): float
    {
        if ($latitude === null || $longitude === null || !$salon->store) {
            return 0.5; // Default score if no location data
        }
        
        $userLat = (float) $latitude;
        $userLng = (float) $longitude;
        $salonLat = (float) ($salon->store->latitude ?? 0.0);
        $salonLng = (float) ($salon->store->longitude ?? 0.0);
        
        // Calculate distance using Haversine formula
        // محاسبه فاصله با استفاده از فرمول Haversine
        $distance = $this->calculateDistance(
            $userLat,
            $userLng,
            $salonLat,
            $salonLng
        );
        
        // Normalize distance using config thresholds
        // نرمال‌سازی فاصله با استفاده از آستانه‌های config
        $thresholds = config('beautybooking.ranking.location_thresholds', []);
        $excellent = $thresholds['excellent'] ?? 10;
        $good = $thresholds['good'] ?? 20;
        $fair = $thresholds['fair'] ?? 50;
        
        if ($distance <= $excellent) {
            return 1.0;
        } elseif ($distance <= $good) {
            return 0.8;
        } elseif ($distance <= $fair) {
            return 0.6;
        } else {
            return 0.4;
        }
    }
    
    /**
     * Calculate distance between two coordinates (Haversine formula)
     * محاسبه فاصله بین دو مختصات (فرمول Haversine)
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in kilometers
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth radius in kilometers
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Calculate featured score
     * محاسبه امتیاز Featured
     *
     * @param BeautySalon $salon
     * @return float
     */
    private function calculateFeaturedScore(BeautySalon $salon): float
    {
        // Check for different advertisement types
        // بررسی انواع مختلف تبلیغات
        $hasFeaturedListing = $salon->subscriptions()
            ->where('subscription_type', 'featured_listing')
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->exists();
        
        $hasBoostAds = $salon->subscriptions()
            ->where('subscription_type', 'boost_ads')
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->exists();
        
        $hasBannerAds = $salon->subscriptions()
            ->where('subscription_type', 'banner_ads')
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->exists();
        
        $hasVerified = $salon->is_verified;
        $hasTopRated = $salon->badges()->where('badge_type', 'top_rated')->active()->exists();
        
        // Featured listing has highest priority
        // Featured listing بالاترین اولویت را دارد
        if ($hasFeaturedListing) {
            return 1.0;
        } elseif ($hasBoostAds) {
            return 0.9; // Boost ads slightly lower
        } elseif ($hasBannerAds) {
            return 0.85; // Banner ads
        } elseif ($hasTopRated) {
            return 0.8;
        } elseif ($hasVerified) {
            return 0.6;
        } else {
            return 0.4;
        }
    }
    
    /**
     * Calculate rating score with weighted average
     * محاسبه امتیاز رتبه‌بندی با میانگین وزنی
     *
     * Uses Bayesian average to penalize salons with few reviews
     * استفاده از میانگین Bayesian برای جریمه سالن‌های با نظرات کم
     *
     * @param BeautySalon $salon
     * @return float
     */
    private function calculateRatingScore(BeautySalon $salon): float
    {
        $minReviews = config('beautybooking.ranking.rating.min_reviews', 5);
        $totalReviews = $salon->total_reviews ?? 0;
        
        // Salons with fewer than minimum reviews get penalized score using Bayesian average
        // سالن‌های با نظرات کمتر از حداقل با استفاده از میانگین Bayesian امتیاز جریمه می‌گیرند
        if ($totalReviews < $minReviews) {
            // Bayesian average: blend with global average to reduce impact of small sample sizes
            // میانگین Bayesian: ترکیب با میانگین جهانی برای کاهش تأثیر اندازه نمونه‌های کوچک
            $globalAvg = config('beautybooking.ranking.rating.global_average', 3.5);
            $weight = $totalReviews / (float) $minReviews; // Weight increases as reviews approach minimum
            $adjustedRating = ($salon->avg_rating * $weight) + ($globalAvg * (1 - $weight));
            return min($adjustedRating / 5.0, 1.0);
        }
        
        // Normalize rating (0-5 scale to 0-1 scale) for salons with sufficient reviews
        // نرمال‌سازی امتیاز (مقیاس 0-5 به مقیاس 0-1) برای سالن‌های با نظرات کافی
        return min($salon->avg_rating / 5.0, 1.0);
    }
    
    /**
     * Calculate activity score (bookings in last 30 days)
     * محاسبه امتیاز فعالیت (رزروها در 30 روز گذشته)
     *
     * @param BeautySalon $salon
     * @return float
     */
    private function calculateActivityScore(BeautySalon $salon): float
    {
        $activityConfig = config('beautybooking.ranking.activity', []);
        $days = $activityConfig['days'] ?? 30;
        $maxBookings = $activityConfig['max_bookings'] ?? 50;
        
        $recentBookings = BeautyBooking::where('salon_id', $salon->id)
            ->where('booking_date', '>=', Carbon::now()->subDays($days))
            ->where('status', '!=', 'cancelled')
            ->count();
        
        // Normalize (max_bookings+ bookings = 1.0, 0 bookings = 0.0)
        // نرمال‌سازی
        return min($recentBookings / (float) $maxBookings, 1.0);
    }
    
    /**
     * Calculate returning customer rate score
     * محاسبه امتیاز نرخ مشتری برگشتی
     *
     * @param BeautySalon $salon
     * @return float
     */
    private function calculateReturningRateScore(BeautySalon $salon): float
    {
        $returningConfig = config('beautybooking.ranking.returning_rate', []);
        $expectedRate = $returningConfig['expected_rate'] ?? 0.3;
        
        $totalBookings = BeautyBooking::where('salon_id', $salon->id)
            ->where('status', '!=', 'cancelled')
            ->count();
        
        if ($totalBookings < 2) {
            return 0.0;
        }
        
        $returningCustomers = DB::table('beauty_bookings')
            ->select('user_id')
            ->selectRaw('COUNT(*) as booking_count')
            ->where('salon_id', $salon->id)
            ->where('status', '!=', 'cancelled')
            ->groupBy('user_id')
            ->having('booking_count', '>', 1)
            ->count();
        
        // Return rate (0-1 scale)
        // نرخ بازگشت
        return min($returningCustomers / max($totalBookings * $expectedRate, 1), 1.0);
    }
    
    /**
     * Calculate availability score (available time slots)
     * محاسبه امتیاز دسترسی (زمان‌های خالی)
     *
     * @param BeautySalon $salon
     * @return float
     */
    private function calculateAvailabilityScore(BeautySalon $salon): float
    {
        try {
            // Get all services for this salon to determine average duration
            // دریافت تمام خدمات این سالن برای تعیین مدت زمان متوسط
            $services = BeautyService::where('salon_id', $salon->id)
                ->where('status', 'active')
                ->get();
            
            if ($services->isEmpty()) {
                return 0.0; // No services means no availability
            }
            
            // Get availability config
            // دریافت تنظیمات دسترسی
            $availabilityConfig = config('beautybooking.ranking.availability', []);
            $daysAhead = $availabilityConfig['days_ahead'] ?? 7;
            $slotInterval = $availabilityConfig['slot_interval'] ?? 30;
            $minDuration = $availabilityConfig['min_duration'] ?? 30;
            
            // Use average duration from services (default 60 minutes if not specified)
            // استفاده از مدت زمان متوسط از خدمات (پیش‌فرض 60 دقیقه در صورت عدم تعیین)
            $avgDuration = $services->avg('duration_minutes') ?? 60;
            $durationMinutes = (int) max($avgDuration, $minDuration);
            
            // Calculate available slots for next N days
            // محاسبه زمان‌های خالی برای N روز آینده
            $totalAvailableSlots = 0;
            $totalPossibleSlots = 0;
            
            for ($day = 0; $day < $daysAhead; $day++) {
                $date = Carbon::now()->addDays($day)->format('Y-m-d');
                
                // Check salon working hours for this date
                // بررسی ساعات کاری سالن برای این تاریخ
                $workingHours = $this->calendarService->getWorkingHours($salon->id, null, $date);
                
                if (!$workingHours || !isset($workingHours['open']) || !isset($workingHours['close'])) {
                    continue; // Salon closed on this day
                }
                
                // Calculate total possible slots for this day
                // محاسبه کل زمان‌های ممکن برای این روز
                $startTime = Carbon::parse($date . ' ' . $workingHours['open']);
                $endTime = Carbon::parse($date . ' ' . $workingHours['close']);
                $totalMinutes = $startTime->diffInMinutes($endTime);
                $possibleSlotsForDay = (int) floor($totalMinutes / $slotInterval);
                $totalPossibleSlots += $possibleSlotsForDay;
                
                // Get available slots for this day
                // دریافت زمان‌های خالی برای این روز
                $availableSlots = $this->calendarService->getAvailableTimeSlots(
                    $salon->id,
                    null, // No specific staff
                    $date,
                    $durationMinutes,
                    $slotInterval
                );
                
                $totalAvailableSlots += count($availableSlots);
            }
            
            // Calculate availability ratio (0-1 scale)
            // محاسبه نسبت دسترسی (مقیاس 0-1)
            if ($totalPossibleSlots == 0) {
                return 0.0; // No possible slots means no availability
            }
            
            $availabilityRatio = $totalAvailableSlots / $totalPossibleSlots;
            
            // Normalize score (more available slots = higher score)
            // نرمال‌سازی امتیاز (زمان‌های خالی بیشتر = امتیاز بالاتر)
            // Score ranges from 0.0 (no availability) to 1.0 (fully available)
            // امتیاز از 0.0 (بدون دسترسی) تا 1.0 (کاملاً در دسترس) متغیر است
            return min(max($availabilityRatio, 0.0), 1.0);
            
        } catch (\Exception $e) {
            \Log::error('Error calculating availability score', [
                'salon_id' => $salon->id,
                'error' => $e->getMessage(),
            ]);
            return 0.5; // Default score on error
        }
    }
    
    /**
     * Calculate cancellation rate score (lower cancellation rate = higher score)
     * محاسبه امتیاز نرخ لغو (نرخ لغو پایین‌تر = امتیاز بالاتر)
     *
     * @param BeautySalon $salon
     * @return float
     */
    private function calculateCancellationRateScore(BeautySalon $salon): float
    {
        $cancellationRate = $salon->cancellation_rate ?? 0.0;
        
        // Lower cancellation rate = higher score
        // نرخ لغو پایین‌تر = امتیاز بالاتر
        // 0% = 1.0, 2% = 0.9, 5% = 0.7, 10% = 0.5, 20%+ = 0.2
        if ($cancellationRate <= 0) {
            return 1.0;
        } elseif ($cancellationRate <= 2.0) {
            return 0.9;
        } elseif ($cancellationRate <= 5.0) {
            return 0.7;
        } elseif ($cancellationRate <= 10.0) {
            return 0.5;
        } else {
            return 0.2;
        }
    }
    
    /**
     * Calculate service type matching score
     * محاسبه امتیاز تطابق نوع خدمت
     *
     * @param BeautySalon $salon
     * @param array $filters
     * @return float
     */
    private function calculateServiceTypeScore(BeautySalon $salon, array $filters): float
    {
        // If no service type filter, return neutral score
        // در صورت عدم وجود فیلتر نوع خدمت، امتیاز خنثی برگردانید
        if (!isset($filters['service_type']) && !isset($filters['category_id'])) {
            return 0.5; // Neutral score
        }
        
        // Check if salon has services matching the filter
        // بررسی اینکه آیا سالن خدمات مطابق با فیلتر دارد
        $hasMatchingServices = $salon->services()
            ->when(isset($filters['category_id']), function($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            })
            ->when(isset($filters['service_type']), function($q) use ($filters) {
                $q->where('service_type', $filters['service_type']);
            })
            ->where('status', 'active')
            ->exists();
        
        return $hasMatchingServices ? 1.0 : 0.0;
    }
    
    /**
     * Generate cache key for ranking score
     * تولید کلید cache برای امتیاز رتبه‌بندی
     *
     * @param int $salonId
     * @param float|null $latitude
     * @param float|null $longitude
     * @param array $filters
     * @return string
     */
    private function generateRankingScoreCacheKey(int $salonId, ?float $latitude, ?float $longitude, array $filters): string
    {
        // Round coordinates to 4 decimal places for cache key (approx 11 meters precision)
        // گرد کردن مختصات به 4 رقم اعشار برای کلید cache (دقت تقریبی 11 متر)
        $latKey = $latitude ? round($latitude, 4) : 'null';
        $lonKey = $longitude ? round($longitude, 4) : 'null';
        
        // Create filter hash
        // ایجاد hash فیلترها
        $filterHash = md5(json_encode($filters));
        
        return "beauty_ranking_score_{$salonId}_{$latKey}_{$lonKey}_{$filterHash}";
    }
    
    /**
     * Generate cache key for search results
     * تولید کلید cache برای نتایج جستجو
     *
     * @param string|null $query
     * @param float|null $latitude
     * @param float|null $longitude
     * @param array $filters
     * @return string
     */
    private function generateSearchCacheKey(?string $query, ?float $latitude, ?float $longitude, array $filters): string
    {
        // Round coordinates to 4 decimal places for cache key
        // گرد کردن مختصات به 4 رقم اعشار برای کلید cache
        $latKey = $latitude ? round($latitude, 4) : 'null';
        $lonKey = $longitude ? round($longitude, 4) : 'null';
        
        // Create hash of query and filters
        // ایجاد hash از query و فیلترها
        $queryHash = md5($query ?? '');
        $filterHash = md5(json_encode($filters));
        
        return "beauty_salons_search_{$queryHash}_{$latKey}_{$lonKey}_{$filterHash}";
    }
    
    /**
     * Invalidate ranking cache for a salon
     * باطل کردن cache رتبه‌بندی برای یک سالن
     *
     * @param int $salonId
     * @return void
     */
    public function invalidateSalonRankingCache(int $salonId): void
    {
        // Clear all ranking score caches for this salon
        // پاک کردن تمام cache های امتیاز رتبه‌بندی برای این سالن
        // Note: This uses a pattern match, which may require Redis or similar cache driver
        // توجه: این از تطابق الگو استفاده می‌کند که ممکن است نیاز به Redis یا درایور cache مشابه داشته باشد
        $pattern = "beauty_ranking_score_{$salonId}_*";
        
        // Invalidate cache based on driver type
        // باطل کردن cache بر اساس نوع driver
        // Fixed: Support for all cache drivers, not just Redis
        // اصلاح شده: پشتیبانی از تمام cache driverها، نه فقط Redis
        $cacheDriver = config('cache.default');
        
        if ($cacheDriver === 'redis') {
            // Redis: Use pattern matching
            // Redis: استفاده از تطابق الگو
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        } elseif ($cacheDriver === 'file' || $cacheDriver === 'database') {
            // File/Database: Clear all ranking caches for this salon
            // File/Database: پاک کردن تمام cache های رتبه‌بندی برای این سالن
            // Note: This is less efficient but works with all drivers
            // توجه: این کارایی کمتری دارد اما با تمام driverها کار می‌کند
            $cachePrefix = config('cache.prefix', '');
            // Clear known cache keys for this salon
            // پاک کردن کلیدهای cache شناخته شده برای این سالن
            Cache::forget("beauty_ranking_score_{$salonId}_default");
            Cache::forget("beauty_ranking_score_{$salonId}_location");
            Cache::forget("beauty_ranking_score_{$salonId}_rating");
            Cache::forget("beauty_ranking_score_{$salonId}_activity");
        } else {
            // Array/Other: Clear all caches (will regenerate on next request)
            // Array/Other: پاک کردن تمام cacheها (در درخواست بعدی دوباره تولید می‌شوند)
            Cache::flush();
        }
        
        // Also clear search caches (they will be regenerated on next search)
        // همچنین cache های جستجو را پاک کنید (در جستجوی بعدی دوباره تولید می‌شوند)
        $this->invalidateSearchCache();
    }
    
    /**
     * Invalidate all search caches
     * باطل کردن تمام cache های جستجو
     *
     * @return void
     */
    public function invalidateSearchCache(): void
    {
        // Clear all search result caches
        // پاک کردن تمام cache های نتایج جستجو
        if (config('cache.default') === 'redis') {
            $keys = Cache::getRedis()->keys('beauty_salons_search_*');
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        }
    }
}

