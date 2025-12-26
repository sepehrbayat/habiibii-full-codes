<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyPackageUsage;
use App\Models\User;

/**
 * Package Redemption Test
 * تست استفاده از پکیج
 *
 * Tests package usage tracking
 * تست ردیابی استفاده از پکیج
 */
class PackageRedemptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test package usage tracking
     * تست ردیابی استفاده از پکیج
     *
     * @return void
     */
    public function test_package_usage_tracking(): void
    {
        $user = User::factory()->create();
        $store = \App\Models\Store::factory()->create();
        $salon = \Modules\BeautyBooking\Entities\BeautySalon::factory()->create(['store_id' => $store->id]);
        $service = \Modules\BeautyBooking\Entities\BeautyService::factory()->create(['salon_id' => $salon->id]);
        
        $package = BeautyPackage::factory()->create([
            'salon_id' => $salon->id,
            'service_id' => $service->id,
            'sessions_count' => 4,
        ]);
        
        $usage = BeautyPackageUsage::factory()->create([
            'package_id' => $package->id,
            'user_id' => $user->id,
            'salon_id' => $salon->id,
            'session_number' => 1,
            'status' => 'used',
        ]);
        
        $this->assertNotNull($usage);
        $this->assertEquals(1, $usage->session_number);
        
        $remainingSessions = $package->getRemainingSessions($user->id);
        $this->assertEquals(3, $remainingSessions);
    }
}

