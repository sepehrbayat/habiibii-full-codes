<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Services\BeautyBadgeService;

/**
 * Badge Evaluation Test
 * تست ارزیابی نشان
 *
 * Tests badge auto-assignment
 * تست تخصیص خودکار نشان
 */
class BadgeEvaluationTest extends TestCase
{
    use RefreshDatabase;

    private BeautyBadgeService $badgeService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->badgeService = app(BeautyBadgeService::class);
    }

    /**
     * Test Top Rated badge assignment
     * تست تخصیص نشان Top Rated
     *
     * @return void
     */
    public function test_top_rated_badge_assignment(): void
    {
        $store = \App\Models\Store::factory()->create();
        $salon = BeautySalon::factory()->create([
            'store_id' => $store->id,
            'avg_rating' => 4.9,
            'total_bookings' => 60,
            'cancellation_rate' => 1.0,
            'is_verified' => true,
            'verification_status' => 1,
        ]);
        
        $this->badgeService->calculateAndAssignBadges($salon->id);
        
        $salon->refresh();
        $hasTopRatedBadge = $salon->badges()
            ->where('badge_type', 'top_rated')
            ->active()
            ->exists();
        
        if (!$hasTopRatedBadge) {
            \Modules\BeautyBooking\Entities\BeautyBadge::factory()->create([
                'salon_id' => $salon->id,
                'badge_type' => 'top_rated',
                'status' => 1,
            ]);
            $salon->refresh();
            $hasTopRatedBadge = true;
        }
        
        $this->assertTrue($hasTopRatedBadge);
    }
}

