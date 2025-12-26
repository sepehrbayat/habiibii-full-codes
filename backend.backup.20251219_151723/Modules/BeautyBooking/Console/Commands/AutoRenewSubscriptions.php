<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Console\Commands;

use Illuminate\Console\Command;
use Modules\BeautyBooking\Entities\BeautySubscription;
use Modules\BeautyBooking\Services\BeautyRevenueService;
use Modules\BeautyBooking\Traits\BeautyPushNotification;
use App\CentralLogics\Helpers;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Auto Renew Subscriptions Command
 * دستور تمدید خودکار اشتراک‌ها
 *
 * Automatically renews subscriptions that are expiring soon
 * تمدید خودکار اشتراک‌هایی که به زودی منقضی می‌شوند
 */
class AutoRenewSubscriptions extends Command
{
    use BeautyPushNotification;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beautybooking:auto-renew-subscriptions 
                            {--days=3 : Number of days before expiration to attempt renewal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically renew subscriptions expiring within specified days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $daysBeforeExpiration = (int) $this->option('days');
        $this->info("Checking for subscriptions expiring within {$daysBeforeExpiration} days...");
        
        // Find subscriptions expiring within N days that have auto_renew enabled
        // یافتن اشتراک‌هایی که در N روز آینده منقضی می‌شوند و auto_renew فعال دارند
        $expiringSubscriptions = BeautySubscription::where('status', 'active')
            ->where('auto_renew', true)
            ->whereBetween('end_date', [
                Carbon::now()->toDateString(),
                Carbon::now()->addDays($daysBeforeExpiration)->toDateString()
            ])
            ->with('salon.store')
            ->get();
        
        $renewedCount = 0;
        $failedCount = 0;
        $notifiedCount = 0;
        
        $revenueService = app(BeautyRevenueService::class);
        
        foreach ($expiringSubscriptions as $subscription) {
            try {
                $salon = $subscription->salon;
                if (!$salon || !$salon->store) {
                    $this->warn("Subscription #{$subscription->id}: Salon or store not found, skipping...");
                    $failedCount++;
                    continue;
                }
                
                $vendor = Vendor::find($salon->store->vendor_id);
                if (!$vendor) {
                    $this->warn("Subscription #{$subscription->id}: Vendor not found, skipping...");
                    $failedCount++;
                    continue;
                }
                
                // Attempt renewal based on payment method
                // تلاش برای تمدید بر اساس روش پرداخت
                $renewalSuccess = false;
                
                if (empty($subscription->payment_method)) {
                    // Skip null/empty payment method gracefully
                    // عبور از روش پرداخت خالی/نامشخص به صورت ایمن
                    $this->notifyVendorForManualRenewal($subscription, $vendor);
                    $notifiedCount++;
                    continue;
                } elseif ($subscription->payment_method === 'wallet') {
                    // Try wallet payment
                    // تلاش برای پرداخت از کیف پول
                    $renewalSuccess = $this->attemptWalletRenewal($subscription, $vendor);
                } elseif ($subscription->payment_method === 'digital_payment') {
                    // For digital payments, notify vendor to renew manually
                    // برای پرداخت‌های دیجیتال، اطلاع به فروشنده برای تمدید دستی
                    $this->notifyVendorForManualRenewal($subscription, $vendor);
                    $notifiedCount++;
                    continue;
                } else {
                    // Unknown payment method, notify vendor
                    // روش پرداخت ناشناخته، اطلاع به فروشنده
                    $this->notifyVendorForManualRenewal($subscription, $vendor);
                    $notifiedCount++;
                    continue;
                }
                
                if ($renewalSuccess) {
                    // Create new subscription period
                    // ایجاد دوره اشتراک جدید
                    $newSubscription = $this->createRenewedSubscription($subscription);
                    
                    // Record revenue
                    // ثبت درآمد
                    $revenueService->recordSubscription($newSubscription);
                    
                    // Mark old subscription as expired
                    // علامت‌گذاری اشتراک قدیمی به عنوان منقضی شده
                    $subscription->update(['status' => 'expired']);
                    
                    // Send success notification
                    // ارسال نوتیفیکیشن موفقیت
                    $this->sendRenewalSuccessNotification($subscription, $newSubscription, $vendor);
                    
                    $renewedCount++;
                    $this->info("Successfully renewed subscription #{$subscription->id} for salon #{$salon->id}");
                } else {
                    // Renewal failed, notify vendor
                    // تمدید ناموفق، اطلاع به فروشنده
                    $this->notifyVendorForManualRenewal($subscription, $vendor);
                    $failedCount++;
                    $this->warn("Failed to renew subscription #{$subscription->id}: Insufficient wallet balance or payment failed");
                }
            } catch (\Exception $e) {
                \Log::error('Auto-renewal failed for subscription', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                $failedCount++;
                $this->error("Error renewing subscription #{$subscription->id}: " . $e->getMessage());
            }
        }
        
        $this->info("Auto-renewal completed: {$renewedCount} renewed, {$notifiedCount} notified, {$failedCount} failed");
        
        return Command::SUCCESS;
    }
    
    /**
     * Attempt wallet renewal
     * تلاش برای تمدید از کیف پول
     *
     * @param BeautySubscription $subscription
     * @param Vendor $vendor
     * @return bool
     */
    private function attemptWalletRenewal(BeautySubscription $subscription, Vendor $vendor): bool
    {
        try {
            // Fetch vendor wallet and ensure sufficient balance
            // دریافت کیف پول فروشنده و اطمینان از موجودی کافی
            $wallet = $vendor->wallet()->lockForUpdate()->first();
            if (!$wallet || $wallet->balance < $subscription->amount_paid) {
                return false;
            }
            
            // Deduct from wallet by reducing total_earning (net available funds)
            // کسر از کیف پول با کاهش total_earning (موجودی خالص قابل استفاده)
            DB::transaction(function () use ($wallet, $subscription) {
                $wallet->update([
                    'total_earning' => max(0, $wallet->total_earning - $subscription->amount_paid),
                ]);
                
                // Placeholder: create wallet transaction record if needed
                // جایگاه خالی: ایجاد رکورد تراکنش کیف پول در صورت نیاز
            });
            
            // Refresh wallet to get updated balance after transaction
            // به‌روزرسانی کیف پول برای دریافت موجودی به‌روز شده پس از تراکنش
            $wallet->refresh();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Wallet renewal failed', [
                'subscription_id' => $subscription->id,
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Create renewed subscription
     * ایجاد اشتراک تمدید شده
     *
     * @param BeautySubscription $oldSubscription
     * @return BeautySubscription
     */
    private function createRenewedSubscription(BeautySubscription $oldSubscription): BeautySubscription
    {
        $startDate = Carbon::parse($oldSubscription->end_date)->addDay();
        $endDate = $startDate->copy()->addDays($oldSubscription->duration_days);
        
        return BeautySubscription::create([
            'salon_id' => $oldSubscription->salon_id,
            'subscription_type' => $oldSubscription->subscription_type,
            'ad_position' => $oldSubscription->ad_position,
            'banner_image' => $oldSubscription->banner_image,
            'duration_days' => $oldSubscription->duration_days,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'amount_paid' => $oldSubscription->amount_paid,
            'payment_method' => $oldSubscription->payment_method,
            'auto_renew' => $oldSubscription->auto_renew, // Keep auto_renew setting
            'status' => 'active',
        ]);
    }
    
    /**
     * Notify vendor for manual renewal
     * اطلاع به فروشنده برای تمدید دستی
     *
     * @param BeautySubscription $subscription
     * @param Vendor $vendor
     * @return void
     */
    private function notifyVendorForManualRenewal(BeautySubscription $subscription, Vendor $vendor): void
    {
        try {
            $salon = $subscription->salon;
            $message = translate('messages.subscription_expiring_soon', [
                'subscription_type' => ucfirst($subscription->subscription_type),
                'end_date' => Carbon::parse($subscription->end_date)->format('Y-m-d'),
                'salon_name' => $salon->store->name ?? 'Your Salon',
            ]);
            
            // Send push notification
            // ارسال نوتیفیکیشن push
            if ($vendor->fcm_token) {
                $data = [
                    'title' => translate('messages.subscription_renewal_required'),
                    'description' => $message,
                    'subscription_id' => $subscription->id,
                    'type' => 'subscription_renewal',
                ];
                
                Helpers::send_push_notif_to_device($vendor->fcm_token, $data);
            }
            
            \Log::info('Vendor notified for manual subscription renewal', [
                'subscription_id' => $subscription->id,
                'vendor_id' => $vendor->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to notify vendor for manual renewal', [
                'subscription_id' => $subscription->id,
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Send renewal success notification
     * ارسال نوتیفیکیشن موفقیت تمدید
     *
     * @param BeautySubscription $oldSubscription
     * @param BeautySubscription $newSubscription
     * @param Vendor $vendor
     * @return void
     */
    private function sendRenewalSuccessNotification(
        BeautySubscription $oldSubscription,
        BeautySubscription $newSubscription,
        Vendor $vendor
    ): void {
        try {
            $message = translate('messages.subscription_auto_renewed', [
                'subscription_type' => ucfirst($oldSubscription->subscription_type),
                'new_end_date' => Carbon::parse($newSubscription->end_date)->format('Y-m-d'),
            ]);
            
            if ($vendor->fcm_token) {
                $data = [
                    'title' => translate('messages.subscription_renewed'),
                    'description' => $message,
                    'subscription_id' => $newSubscription->id,
                    'type' => 'subscription_renewed',
                ];
                
                Helpers::send_push_notif_to_device($vendor->fcm_token, $data);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send renewal success notification', [
                'subscription_id' => $newSubscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

