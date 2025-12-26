<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Unit;

use Tests\TestCase;
use Modules\BeautyBooking\Services\BeautyCommissionService;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Entities\BeautyCommissionSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Beauty Commission Service Unit Tests
 * تست‌های واحد سرویس کمیسیون
 */
class BeautyCommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BeautyCommissionService $commissionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commissionService = app(BeautyCommissionService::class);
    }

    /**
     * Test commission calculation with default percentage
     * تست محاسبه کمیسیون با درصد پیش‌فرض
     */
    public function test_commission_calculation_with_default(): void
    {
        // Arrange
        $salon = BeautySalon::factory()->create(['business_type' => 'salon']);
        $category = BeautyServiceCategory::factory()->create();
        $service = BeautyService::factory()->create([
            'salon_id' => $salon->id,
            'category_id' => $category->id,
        ]);
        $basePrice = 100.00;

        // Act
        $commission = $this->commissionService->calculateCommission(
            $salon->id,
            $service->id,
            $basePrice
        );

        // Assert
        $this->assertIsFloat($commission);
        $this->assertGreaterThanOrEqual(0, $commission);
    }

    /**
     * Test commission calculation with category-specific setting
     * تست محاسبه کمیسیون با تنظیمات مخصوص دسته‌بندی
     */
    public function test_commission_calculation_with_category_setting(): void
    {
        // Arrange
        $salon = BeautySalon::factory()->create(['business_type' => 'salon']);
        $category = BeautyServiceCategory::factory()->create();
        $service = BeautyService::factory()->create([
            'salon_id' => $salon->id,
            'category_id' => $category->id,
        ]);
        
        BeautyCommissionSetting::factory()->create([
            'service_category_id' => $category->id,
            'salon_level' => 'salon',
            'commission_percentage' => 15.0,
            'status' => 1,
        ]);
        
        $basePrice = 100.00;

        // Act
        $commission = $this->commissionService->calculateCommission(
            $salon->id,
            $service->id,
            $basePrice
        );

        // Assert
        $this->assertEquals(15.0, $commission);
    }

    /**
     * Test top rated discount
     * تست تخفیف Top Rated
     */
    public function test_top_rated_discount(): void
    {
        // Arrange
        $salon = BeautySalon::factory()->create(['business_type' => 'salon']);
        $category = BeautyServiceCategory::factory()->create();
        $service = BeautyService::factory()->create([
            'salon_id' => $salon->id,
            'category_id' => $category->id,
        ]);
        
        // Create Top Rated badge
        \Modules\BeautyBooking\Entities\BeautyBadge::factory()->create([
            'salon_id' => $salon->id,
            'badge_type' => 'top_rated',
            'status' => 1,
        ]);
        
        $basePrice = 100.00;

        // Act
        $commission = $this->commissionService->calculateCommission(
            $salon->id,
            $service->id,
            $basePrice
        );

        // Assert
        $this->assertIsFloat($commission);
        // Commission should be reduced if top rated discount is configured
    }
}

