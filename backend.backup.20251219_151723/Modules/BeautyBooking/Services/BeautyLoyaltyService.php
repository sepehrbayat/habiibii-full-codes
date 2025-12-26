<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Services;

use Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign;
use Modules\BeautyBooking\Entities\BeautyLoyaltyPoint;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautySalon;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Beauty Loyalty Service
 * سرویس وفاداری
 *
 * Handles loyalty campaigns and points management
 * مدیریت کمپین‌های وفاداری و امتیازها
 */
class BeautyLoyaltyService
{
    /**
     * Award loyalty points for a purchase (package, gift card, etc.)
     * اعطای امتیاز وفاداری برای یک خرید (پکیج، کارت هدیه و غیره)
     *
     * @param int $userId
     * @param int $salonId
     * @param float $amount
     * @param string $purchaseType Type of purchase: 'package_purchase', 'gift_card_purchase', etc.
     * @return void
     */
    public function awardPointsForPurchase(int $userId, int $salonId, float $amount, string $purchaseType = 'package_purchase'): void
    {
        // Get active campaigns for this salon (or platform-wide)
        // دریافت کمپین‌های فعال برای این سالن (یا در سطح پلتفرم)
        $campaigns = BeautyLoyaltyCampaign::active()
            ->where(function($q) use ($salonId) {
                $q->where('salon_id', $salonId)
                  ->orWhereNull('salon_id'); // Platform-wide campaigns
            })
            ->get();
        
        foreach ($campaigns as $campaign) {
            // Check if purchase qualifies for this campaign
            // بررسی اینکه آیا خرید واجد شرایط این کمپین است
            if ($this->purchaseQualifies($amount, $campaign)) {
                $points = $this->calculatePointsForPurchase($amount, $campaign);
                
                if ($points > 0) {
                    $this->awardPoints($userId, $salonId, $campaign->id, null, $points, $campaign);
                }
            }
        }
    }
    
    /**
     * Check if purchase qualifies for campaign
     * بررسی اینکه آیا خرید واجد شرایط کمپین است
     *
     * @param float $amount
     * @param BeautyLoyaltyCampaign $campaign
     * @return bool
     */
    private function purchaseQualifies(float $amount, BeautyLoyaltyCampaign $campaign): bool
    {
        $rules = $campaign->rules ?? [];
        
        // Check minimum purchase amount if set
        // بررسی حداقل مبلغ خرید در صورت تنظیم
        if (isset($rules['min_purchase']) && $amount < $rules['min_purchase']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Calculate points for a purchase
     * محاسبه امتیاز برای یک خرید
     *
     * @param float $amount
     * @param BeautyLoyaltyCampaign $campaign
     * @return int
     */
    private function calculatePointsForPurchase(float $amount, BeautyLoyaltyCampaign $campaign): int
    {
        $rules = $campaign->rules ?? [];
        
        if ($campaign->type === 'points') {
            // Points-based: X points per currency unit
            // مبتنی بر امتیاز: X امتیاز به ازای هر واحد پول
            $pointsPerCurrency = $rules['points_per_currency'] ?? 1;
            return (int) floor($amount * $pointsPerCurrency);
        }
        
        return 0;
    }
    
    /**
     * Award loyalty points for a booking
     * اعطای امتیاز وفاداری برای یک رزرو
     *
     * @param BeautyBooking $booking
     * @return void
     */
    public function awardPointsForBooking(BeautyBooking $booking): void
    {
        // Wrap in transaction to ensure atomicity and prevent duplicate awards
        // قرار دادن در transaction برای اطمینان از atomicity و جلوگیری از اعطای تکراری
        DB::transaction(function () use ($booking) {
            // Get active campaigns for this salon (or platform-wide)
            // دریافت کمپین‌های فعال برای این سالن (یا در سطح پلتفرم)
            $campaigns = BeautyLoyaltyCampaign::active()
                ->where(function($q) use ($booking) {
                    $q->where('salon_id', $booking->salon_id)
                      ->orWhereNull('salon_id'); // Platform-wide campaigns
                })
                ->get();
            
            foreach ($campaigns as $campaign) {
                // Check if booking qualifies for this campaign
                // بررسی اینکه آیا رزرو واجد شرایط این کمپین است
                if ($this->bookingQualifies($booking, $campaign)) {
                    // Check if points were already awarded for this booking and campaign
                    // بررسی اینکه آیا امتیازها قبلاً برای این رزرو و کمپین اعطا شده است
                    // Fixed: Prevent duplicate point awards for same booking and campaign
                    // اصلاح شده: جلوگیری از اعطای تکراری امتیاز برای همان رزرو و کمپین
                    $existingPoints = BeautyLoyaltyPoint::where('booking_id', $booking->id)
                        ->where('campaign_id', $campaign->id)
                        ->where('type', 'earned')
                        ->lockForUpdate()
                        ->exists();
                    
                    if ($existingPoints) {
                        // Points already awarded for this booking and campaign - skip
                        // امتیازها قبلاً برای این رزرو و کمپین اعطا شده است - رد شدن
                        continue;
                    }
                    
                    $points = $this->calculatePoints($booking, $campaign);
                    
                    if ($points > 0) {
                        $this->awardPoints($booking->user_id, $booking->salon_id, $campaign->id, $booking->id, $points, $campaign);
                    }
                }
            }
        });
    }
    
    /**
     * Check if booking qualifies for campaign
     * بررسی اینکه آیا رزرو واجد شرایط کمپین است
     *
     * @param BeautyBooking $booking
     * @param BeautyLoyaltyCampaign $campaign
     * @return bool
     */
    private function bookingQualifies(BeautyBooking $booking, BeautyLoyaltyCampaign $campaign): bool
    {
        // Only award points for completed and paid bookings
        // فقط برای رزروهای تکمیل شده و پرداخت شده امتیاز اعطا کنید
        if ($booking->status !== 'completed' || $booking->payment_status !== 'paid') {
            return false;
        }
        
        $rules = $campaign->rules ?? [];
        
        // Check minimum purchase amount if set
        // بررسی حداقل مبلغ خرید در صورت تنظیم
        if (isset($rules['min_purchase']) && $booking->total_amount < $rules['min_purchase']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Calculate points for a booking
     * محاسبه امتیاز برای یک رزرو
     *
     * @param BeautyBooking $booking
     * @param BeautyLoyaltyCampaign $campaign
     * @return int
     */
    private function calculatePoints(BeautyBooking $booking, BeautyLoyaltyCampaign $campaign): int
    {
        $rules = $campaign->rules ?? [];
        
        if ($campaign->type === 'points') {
            // Points-based: X points per currency unit
            // مبتنی بر امتیاز: X امتیاز به ازای هر واحد پول
            $pointsPerCurrency = $rules['points_per_currency'] ?? 1;
            return (int) floor($booking->total_amount * $pointsPerCurrency);
        }
        
        return 0;
    }
    
    /**
     * Award points to user
     * اعطای امتیاز به کاربر
     *
     * @param int $userId
     * @param int|null $salonId
     * @param int|null $campaignId
     * @param int|null $bookingId
     * @param int $points
     * @param BeautyLoyaltyCampaign $campaign
     * @return BeautyLoyaltyPoint
     */
    public function awardPoints(int $userId, ?int $salonId, ?int $campaignId, ?int $bookingId, int $points, ?BeautyLoyaltyCampaign $campaign = null): BeautyLoyaltyPoint
    {
        // Calculate expiry date (default: 1 year from now, or from campaign rules)
        // محاسبه تاریخ انقضا (پیش‌فرض: 1 سال از الان، یا از قوانین کمپین)
        $expiresAt = null;
        if ($campaign) {
            $rules = $campaign->rules ?? [];
            if (isset($rules['points_expiry_days'])) {
                $expiresAt = Carbon::now()->addDays($rules['points_expiry_days']);
            }
        }
        
        // If no expiry from campaign, default to 1 year
        // اگر انقضا از کمپین نباشد، پیش‌فرض 1 سال
        if (!$expiresAt) {
            $expiresAt = Carbon::now()->addYear();
        }
        
        $point = BeautyLoyaltyPoint::create([
            'user_id' => $userId,
            'salon_id' => $salonId,
            'campaign_id' => $campaignId,
            'booking_id' => $bookingId,
            'points' => $points,
            'type' => 'earned',
            'description' => 'Points earned from booking',
            'expires_at' => $expiresAt,
        ]);
        
        // Update campaign statistics
        // به‌روزرسانی آمار کمپین
        if ($campaign) {
            $campaign->increment('total_participants');
            $campaign->increment('total_revenue', $bookingId ? BeautyBooking::find($bookingId)->total_amount ?? 0 : 0);
        }
        
        return $point;
    }
    
    /**
     * Get total available points for a user
     * دریافت کل امتیازهای موجود برای یک کاربر
     *
     * @param int $userId
     * @param int|null $salonId
     * @return int
     */
    public function getTotalPoints(int $userId, ?int $salonId = null): int
    {
        $query = BeautyLoyaltyPoint::where('user_id', $userId)
            ->valid()
            ->earned();
        
        if ($salonId) {
            $query->where('salon_id', $salonId);
        }
        
        return (int) $query->sum('points');
    }
    
    /**
     * Redeem points
     * استفاده از امتیازها
     *
     * @param int $userId
     * @param int $points
     * @param int|null $salonId
     * @param string $description
     * @return bool
     */
    public function redeemPoints(int $userId, int $points, ?int $salonId = null, string $description = 'Points redeemed'): bool
    {
        $availablePoints = $this->getTotalPoints($userId, $salonId);
        
        if ($availablePoints < $points) {
            return false; // Insufficient points
        }
        
        // Get points to redeem (FIFO - First In First Out)
        // دریافت امتیازها برای استفاده (FIFO - اولین ورود، اولین خروج)
        $pointsToRedeem = $points;
        $pointsQuery = BeautyLoyaltyPoint::where('user_id', $userId)
            ->valid()
            ->earned()
            ->orderBy('created_at', 'asc');
        
        if ($salonId) {
            $pointsQuery->where('salon_id', $salonId);
        }
        
        $availablePointsList = $pointsQuery->get();
        
        foreach ($availablePointsList as $point) {
            if ($pointsToRedeem <= 0) {
                break;
            }
            
            $pointsToDeduct = min($point->points, $pointsToRedeem);
            
            // Create redemption record
            // ایجاد رکورد استفاده
            BeautyLoyaltyPoint::create([
                'user_id' => $userId,
                'salon_id' => $point->salon_id,
                'campaign_id' => $point->campaign_id,
                'booking_id' => null,
                'points' => -$pointsToDeduct,
                'type' => 'redeemed',
                'description' => $description,
                'expires_at' => null,
            ]);
            
            // Update original point
            // به‌روزرسانی امتیاز اصلی
            $point->decrement('points', $pointsToDeduct);
            
            $pointsToRedeem -= $pointsToDeduct;
        }
        
        return true;
    }
    
    /**
     * Record commission for loyalty campaign
     * ثبت کمیسیون برای کمپین وفاداری
     *
     * @param BeautyLoyaltyCampaign $campaign
     * @param float $revenue
     * @return float
     */
    public function calculateCommission(BeautyLoyaltyCampaign $campaign, float $revenue): float
    {
        if ($campaign->commission_type === 'percentage') {
            return $revenue * ($campaign->commission_percentage / 100);
        } else {
            // Fixed commission from rules
            // کمیسیون ثابت از قوانین
            $rules = $campaign->rules ?? [];
            return $rules['fixed_commission'] ?? 0.0;
        }
    }
}

