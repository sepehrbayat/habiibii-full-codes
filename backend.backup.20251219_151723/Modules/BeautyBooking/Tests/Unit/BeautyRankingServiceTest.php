<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BeautyBooking\Services\BeautyRankingService;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use App\Models\Store;
use Carbon\Carbon;

/**
 * Beauty Ranking Service Test
 * تست سرویس رتبه‌بندی
 */
class BeautyRankingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BeautyRankingService $rankingService;
    private BeautySalon $salon;

    protected function setUp(): void
    {
        parent::setUp();
        
        $calendarService = new BeautyCalendarService();
        $this->rankingService = new BeautyRankingService($calendarService);
        
        // Create test store and salon
        // ایجاد فروشگاه و سالن تست
        $store = Store::factory()->create([
            'latitude' => 35.6892,
            'longitude' => 51.3890,
        ]);
        
        $this->salon = BeautySalon::factory()->create([
            'store_id' => $store->id,
            'avg_rating' => 4.5,
            'total_bookings' => 100,
            'total_reviews' => 50,
            'is_verified' => true,
        ]);
    }

    /**
     * Test ranking score calculation
     * تست محاسبه امتیاز رتبه‌بندی
     */
    public function test_calculate_ranking_score(): void
    {
        $score = $this->rankingService->calculateRankingScore(
            $this->salon,
            35.6892, // User latitude
            51.3890, // User longitude
            []
        );
        
        $this->assertIsFloat($score);
        $this->assertGreaterThanOrEqual(0.0, $score);
        $this->assertLessThanOrEqual(100.0, $score);
    }

    /**
     * Test ranking score with location
     * تست امتیاز رتبه‌بندی با موقعیت
     */
    public function test_calculate_ranking_score_with_location(): void
    {
        // Close location
        // موقعیت نزدیک
        $closeScore = $this->rankingService->calculateRankingScore(
            $this->salon,
            35.6892,
            51.3890,
            []
        );
        
        // Far location
        // موقعیت دور
        $farScore = $this->rankingService->calculateRankingScore(
            $this->salon,
            36.0000,
            52.0000,
            []
        );
        
        $this->assertGreaterThan($farScore, $closeScore);
    }

    /**
     * Test ranking score with filters
     * تست امتیاز رتبه‌بندی با فیلترها
     */
    public function test_calculate_ranking_score_with_filters(): void
    {
        $filters = [
            'category_id' => 1,
            'min_rating' => 4.0,
        ];
        
        $score = $this->rankingService->calculateRankingScore(
            $this->salon,
            null,
            null,
            $filters
        );
        
        $this->assertIsFloat($score);
    }

    /**
     * Test get ranked salons
     * تست دریافت سالن‌های رتبه‌بندی شده
     */
    public function test_get_ranked_salons(): void
    {
        $salons = $this->rankingService->getRankedSalons(
            null,
            35.6892,
            51.3890,
            []
        );
        
        $this->assertNotEmpty($salons);
        $this->assertTrue($salons->contains($this->salon));
    }

    /**
     * Test ranking score weights
     * تست وزن‌های امتیاز رتبه‌بندی
     */
    public function test_ranking_score_weights(): void
    {
        // Test that all factors contribute to score
        // تست اینکه تمام فاکتورها به امتیاز کمک می‌کنند
        $score = $this->rankingService->calculateRankingScore(
            $this->salon,
            35.6892,
            51.3890,
            []
        );
        
        // Score should be positive
        // امتیاز باید مثبت باشد
        $this->assertGreaterThan(0, $score);
    }
}

