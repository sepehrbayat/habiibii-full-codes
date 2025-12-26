<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Console\Commands;

use Illuminate\Console\Command;
use Modules\BeautyBooking\Entities\BeautySubscription;
use Modules\BeautyBooking\Services\BeautyBadgeService;
use Carbon\Carbon;

/**
 * Update Expired Subscriptions Command
 * دستور به‌روزرسانی اشتراک‌های منقضی شده
 *
 * This command checks for expired subscriptions and updates their status,
 * and recalculates badges for affected salons
 * این دستور اشتراک‌های منقضی شده را بررسی می‌کند و وضعیت آن‌ها را به‌روزرسانی می‌کند،
 * و نشان‌های سالن‌های تأثیرگذار را محاسبه مجدد می‌کند
 */
class UpdateExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beautybooking:update-expired-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update expired subscriptions and recalculate badges';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Checking for expired subscriptions...');
        
        // Find subscriptions that have expired (end_date < today and status is active)
        // یافتن اشتراک‌هایی که منقضی شده‌اند (end_date < امروز و وضعیت active است)
        $expiredSubscriptions = BeautySubscription::where('status', 'active')
            ->where('end_date', '<', Carbon::now()->toDateString())
            ->get();
        
        $updatedCount = 0;
        $badgeService = app(BeautyBadgeService::class);
        
        foreach ($expiredSubscriptions as $subscription) {
            // Update subscription status to expired
            // به‌روزرسانی وضعیت اشتراک به expired
            $subscription->update(['status' => 'expired']);
            
            // Recalculate badges for the salon
            // محاسبه مجدد نشان‌ها برای سالن
            $badgeService->calculateAndAssignBadges($subscription->salon_id);
            
            $updatedCount++;
        }
        
        $this->info("Updated {$updatedCount} expired subscriptions and recalculated badges.");
        
        return Command::SUCCESS;
    }
}

