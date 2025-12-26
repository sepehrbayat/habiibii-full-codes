<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\BeautyBooking\Entities\BeautySalon;
use App\Models\Admin;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\Hash;

/**
 * Admin Salon Approval Browser Test
 * تست مرورگر تأیید سالن توسط ادمین
 *
 * Tests admin salon approval flow
 * تست جریان تأیید سالن توسط ادمین
 */
class AdminSalonApprovalTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Setup browser test environment
     * راه‌اندازی محیط تست مرورگر
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test salon approval flow
     * تست جریان تأیید سالن
     *
     * @return void
     */
    public function test_salon_approval_flow(): void
    {
        $admin = Admin::query()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        
        $salon = BeautySalon::factory()->create([
            'verification_status' => 0,
            'is_verified' => false,
        ]);
        
        $this->browse(function ($browser) use ($admin, $salon) {
            $browser->loginAs($admin, 'admin')
                ->visit('/admin/beautybooking/salon/view/' . $salon->id)
                ->assertSee('Pending Verification')
                ->click('@approve-button')
                ->waitForText('Salon approved successfully')
                ->assertSee('Approved');
        });
    }
}

