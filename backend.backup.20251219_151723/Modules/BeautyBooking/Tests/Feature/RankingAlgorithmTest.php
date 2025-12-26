<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Services\BeautyRankingService;

/**
 * Ranking Algorithm Test
 * تست الگوریتم رتبه‌بندی
 *
 * Tests search ranking algorithm
 * تست الگوریتم رتبه‌بندی جستجو
 */
class RankingAlgorithmTest extends TestCase
{
    use RefreshDatabase;

    private BeautyRankingService $rankingService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->rankingService = app(BeautyRankingService::class);
    }

    /**
     * Test ranking score calculation
     * تست محاسبه امتیاز رتبه‌بندی
     *
     * @return void
     */
    public function test_ranking_score_calculation(): void
    {
        $store = \App\Models\Store::factory()->create();
        $salon = BeautySalon::factory()->create([
            'store_id' => $store->id,
            'avg_rating' => 4.8,
            'total_bookings' => 100,
        ]);
        
        $score = $this->rankingService->calculateRankingScore(
            $salon,
            35.6892, // Tehran latitude
            51.3890, // Tehran longitude
            []
        );
        
        $this->assertIsFloat($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }
}

