<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Services;

use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautySubscription;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign;
use Modules\BeautyBooking\Entities\BeautyTransaction;
use Modules\BeautyBooking\Services\BeautyRankingService;
use Illuminate\Database\QueryException;

/**
 * Beauty Revenue Service
 * سرویس درآمد
 *
 * Manages all 10 revenue models
 * مدیریت تمام 10 مدل درآمدی
 */
class BeautyRevenueService
{
    public function __construct(
        private ?BeautyRankingService $rankingService = null
    ) {
        // Lazy load ranking service to avoid circular dependency
        // بارگذاری تنبل سرویس رتبه‌بندی برای جلوگیری از وابستگی دایره‌ای
        if (!$this->rankingService) {
            $this->rankingService = app(BeautyRankingService::class);
        }
    }
    /**
     * Record commission revenue
     * ثبت درآمد کمیسیون
     *
     * @param BeautyBooking $booking
     * @return BeautyTransaction
     */
    public function recordCommission(BeautyBooking $booking): BeautyTransaction
    {
        return $this->createTransactionSafely([
            'booking_id' => $booking->id,
            'salon_id' => $booking->salon_id,
            'zone_id' => $booking->zone_id,
            'transaction_type' => 'commission',
            'amount' => $booking->total_amount,
            'commission' => $booking->commission_amount,
            'service_fee' => 0,
            'status' => 'completed',
            'notes' => 'Commission from booking #' . $booking->booking_reference,
        ]);
    }
    
    /**
     * Record subscription revenue
     * ثبت درآمد اشتراک
     *
     * @param BeautySubscription $subscription
     * @return BeautyTransaction
     */
    public function recordSubscription(BeautySubscription $subscription): BeautyTransaction
    {
        // Get zone_id from salon's store relationship
        // دریافت zone_id از رابطه store سالن
        $salon = $subscription->salon()->with('store')->first();
        $zoneId = null;
        if ($salon) {
            $zoneId = $salon->store?->zone_id ?? $salon->zone_id ?? null;
        }
        
        $transaction = $this->createTransactionSafely([
            'booking_id' => null,
            'salon_id' => $subscription->salon_id,
            'zone_id' => $zoneId,
            'transaction_type' => 'subscription',
            'amount' => $subscription->amount_paid,
            'commission' => 0,
            'service_fee' => 0,
            'status' => 'completed',
            'reference_number' => 'subscription_' . $subscription->id, // Store subscription ID for duplicate checking
            'notes' => ucfirst($subscription->subscription_type) . ' subscription - ' . $subscription->duration_days . ' days',
        ]);
        
        // Invalidate ranking cache for the salon (subscription affects featured score)
        // باطل کردن cache رتبه‌بندی برای سالن (اشتراک بر امتیاز featured تأثیر می‌گذارد)
        try {
            $this->rankingService->invalidateSalonRankingCache($subscription->salon_id);
        } catch (\Exception $e) {
            // Log but don't fail transaction recording
            // ثبت اما عدم شکست ثبت تراکنش
            \Log::warning('Failed to invalidate ranking cache after subscription revenue recording', [
                'salon_id' => $subscription->salon_id,
                'error' => $e->getMessage(),
            ]);
        }
        
        return $transaction;
    }
    
    /**
     * Record advertisement revenue
     * ثبت درآمد تبلیغات
     *
     * @param string $adType
     * @param int $salonId
     * @param float $amount
     * @param int $duration
     * @return BeautyTransaction
     */
    public function recordAdvertisement(string $adType, int $salonId, float $amount, int $duration): BeautyTransaction
    {
        $salon = \Modules\BeautyBooking\Entities\BeautySalon::findOrFail($salonId);
        
        return $this->createTransactionSafely([
            'booking_id' => null,
            'salon_id' => $salonId,
            'zone_id' => $salon->zone_id ?? null,
            'transaction_type' => 'advertisement',
            'amount' => $amount,
            'commission' => 0,
            'service_fee' => 0,
            'status' => 'completed',
            'notes' => ucfirst($adType) . ' advertisement - ' . $duration . ' days',
        ]);
    }
    
    /**
     * Record service fee revenue
     * ثبت درآمد هزینه سرویس
     *
     * @param BeautyBooking $booking
     * @return BeautyTransaction
     */
    public function recordServiceFee(BeautyBooking $booking): BeautyTransaction
    {
        return $this->createTransactionSafely([
            'booking_id' => $booking->id,
            'salon_id' => $booking->salon_id,
            'zone_id' => $booking->zone_id,
            'transaction_type' => 'service_fee',
            'amount' => $booking->total_amount,
            'commission' => 0,
            'service_fee' => $booking->service_fee,
            'status' => 'completed',
            'notes' => 'Service fee from booking #' . $booking->booking_reference,
        ]);
    }
    
    /**
     * Record package sale revenue
     * ثبت درآمد فروش پکیج
     *
     * Note: Commission should be calculated from total package price (not per session)
     * According to requirements: "پلتفرم از کل مبلغ پکیج، کمیسیون خود را برداشت میکند"
     * توجه: کمیسیون باید از کل مبلغ پکیج محاسبه شود (نه برای هر جلسه)
     * طبق الزامات: "پلتفرم از کل مبلغ پکیج، کمیسیون خود را برداشت میکند"
     *
     * @param BeautyPackage $package
     * @param BeautyBooking $booking
     * @return BeautyTransaction
     */
    public function recordPackageSale(BeautyPackage $package, BeautyBooking $booking): BeautyTransaction
    {
        // Calculate commission from total package price
        // محاسبه کمیسیون از کل مبلغ پکیج
        $commissionService = app(\Modules\BeautyBooking\Services\BeautyCommissionService::class);
        $commissionAmount = $commissionService->calculateCommission(
            $package->salon_id,
            $package->service_id,
            $package->total_price
        );
        
        return $this->createTransactionSafely([
            'booking_id' => $booking->id,
            'salon_id' => $package->salon_id,
            'zone_id' => $booking->zone_id,
            'transaction_type' => 'package_sale',
            'amount' => $package->total_price,
            'commission' => round($commissionAmount, 2),
            'service_fee' => 0,
            'status' => 'completed',
            'notes' => 'Package sale: ' . $package->name . ' for booking #' . $booking->booking_reference,
        ]);
    }
    
    /**
     * Record cancellation fee revenue
     * ثبت درآمد جریمه لغو
     *
     * @param BeautyBooking $booking
     * @param float $feeAmount
     * @return BeautyTransaction
     */
    public function recordCancellationFee(BeautyBooking $booking, float $feeAmount): BeautyTransaction
    {
        return $this->createTransactionSafely([
            'booking_id' => $booking->id,
            'salon_id' => $booking->salon_id,
            'zone_id' => $booking->zone_id,
            'transaction_type' => 'cancellation_fee',
            'amount' => $feeAmount,
            'commission' => 0,
            'service_fee' => 0,
            'status' => 'completed',
            'notes' => 'Cancellation fee for booking #' . $booking->booking_reference,
        ]);
    }
    
    /**
     * Record consultation fee revenue
     * ثبت درآمد هزینه مشاوره
     *
     * Note: Consultation bookings also generate commission revenue
     * توجه: رزروهای مشاوره نیز درآمد کمیسیون تولید می‌کنند
     *
     * @param BeautyBooking $booking
     * @return BeautyTransaction
     */
    public function recordConsultationFee(BeautyBooking $booking): BeautyTransaction
    {
        // Calculate commission for consultation booking
        // محاسبه کمیسیون برای رزرو مشاوره
        $commissionPercentage = config('beautybooking.consultation.commission_percentage', 10.0);
        $commissionAmount = $booking->total_amount * ($commissionPercentage / 100);
        
        return $this->createTransactionSafely([
            'booking_id' => $booking->id,
            'salon_id' => $booking->salon_id,
            'zone_id' => $booking->zone_id,
            'transaction_type' => 'consultation_fee',
            'amount' => $booking->total_amount,
            'commission' => round($commissionAmount, 2),
            'service_fee' => 0,
            'status' => 'completed',
            'notes' => 'Consultation fee from booking #' . $booking->booking_reference,
        ]);
    }
    
    /**
     * Record cross-selling revenue
     * ثبت درآمد فروش متقابل
     *
     * Note: Cross-selling services also generate commission revenue
     * توجه: خدمات فروش متقابل نیز درآمد کمیسیون تولید می‌کنند
     *
     * @param BeautyBooking $booking
     * @param array $additionalServices Array of additional services with 'price' key
     * @return BeautyTransaction
     */
    public function recordCrossSellingRevenue(BeautyBooking $booking, array $additionalServices): BeautyTransaction
    {
        $totalAmount = array_sum(array_column($additionalServices, 'price'));
        
        // Calculate commission for cross-selling services
        // محاسبه کمیسیون برای خدمات فروش متقابل
        $commissionPercentage = config('beautybooking.cross_selling.commission_percentage', 10.0);
        $commissionAmount = $totalAmount * ($commissionPercentage / 100);
        
        return $this->createTransactionSafely([
            'booking_id' => $booking->id,
            'salon_id' => $booking->salon_id,
            'zone_id' => $booking->zone_id,
            'transaction_type' => 'cross_selling',
            'amount' => $totalAmount,
            'commission' => round($commissionAmount, 2),
            'service_fee' => 0,
            'status' => 'completed',
            'notes' => 'Cross-selling revenue for booking #' . $booking->booking_reference,
        ]);
    }
    
    /**
     * Record retail sale revenue
     * ثبت درآمد فروش خرده‌فروشی
     *
     * @param int $salonId
     * @param float $amount
     * @return BeautyTransaction
     */
    public function recordRetailSale(int $salonId, float $amount): BeautyTransaction
    {
        $salon = \Modules\BeautyBooking\Entities\BeautySalon::with('store')->findOrFail($salonId);
        
        // Get zone_id from salon's store relationship with null-safety
        // دریافت zone_id از رابطه store سالن با null-safety
        $zoneId = $salon->store?->zone_id ?? $salon->zone_id ?? null;
        
        return $this->createTransactionSafely([
            'booking_id' => null,
            'salon_id' => $salonId,
            'zone_id' => $zoneId,
            'transaction_type' => 'retail_sale',
            'amount' => $amount,
            'commission' => 0,
            'service_fee' => 0,
            'status' => 'completed',
            'notes' => 'Retail product sale',
        ]);
    }
    
    /**
     * Record gift card sale revenue
     * ثبت درآمد فروش کارت هدیه
     *
     * @param BeautyGiftCard $giftCard
     * @return BeautyTransaction
     */
    public function recordGiftCardSale(BeautyGiftCard $giftCard): BeautyTransaction
    {
        // Get zone_id from salon's store relationship if salon exists
        // دریافت zone_id از رابطه store سالن در صورت وجود سالن
        $zoneId = null;
        if ($giftCard->salon_id) {
            $salon = $giftCard->salon()->with('store')->first();
            if ($salon) {
                $zoneId = $salon->store?->zone_id ?? $salon->zone_id ?? null;
            }
        }
        
        return $this->createTransactionSafely([
            'booking_id' => null,
            'salon_id' => $giftCard->salon_id ?? 0, // Can be general gift card
            'zone_id' => $zoneId,
            'transaction_type' => 'gift_card_sale',
            'amount' => $giftCard->amount,
            'commission' => 0,
            'service_fee' => 0,
            'status' => 'completed',
            'notes' => 'Gift card sale: ' . $giftCard->code,
        ]);
    }
    
    /**
     * Record loyalty campaign revenue
     * ثبت درآمد کمپین وفاداری
     *
     * @param BeautyLoyaltyCampaign $campaign
     * @param float $revenue
     * @return BeautyTransaction
     */
    public function recordLoyaltyCampaignRevenue(BeautyLoyaltyCampaign $campaign, float $revenue): BeautyTransaction
    {
        // Get zone_id from salon's store relationship if salon exists
        // دریافت zone_id از رابطه store سالن در صورت وجود سالن
        $zoneId = null;
        if ($campaign->salon_id) {
            $salon = $campaign->salon()->with('store')->first();
            if ($salon) {
                $zoneId = $salon->store?->zone_id ?? $salon->zone_id ?? null;
            }
        }
        
        // Calculate commission
        // محاسبه کمیسیون
        $loyaltyService = app(\Modules\BeautyBooking\Services\BeautyLoyaltyService::class);
        $commission = $loyaltyService->calculateCommission($campaign, $revenue);
        
        return $this->createTransactionSafely([
            'booking_id' => null,
            'salon_id' => $campaign->salon_id ?? 0, // Can be platform-wide
            'zone_id' => $zoneId,
            'transaction_type' => 'loyalty_campaign',
            'amount' => $revenue,
            'commission' => $commission,
            'service_fee' => 0,
            'status' => 'completed',
            'notes' => 'Loyalty campaign revenue: ' . $campaign->name,
        ]);
    }
    
    /**
     * Create transaction safely with duplicate key handling
     * ایجاد تراکنش با مدیریت کلید تکراری
     *
     * Handles duplicate key exceptions gracefully by returning existing transaction
     * مدیریت استثناهای کلید تکراری با بازگرداندن تراکنش موجود
     *
     * @param array $data
     * @return BeautyTransaction
     * @throws \Exception
     */
    private function createTransactionSafely(array $data): BeautyTransaction
    {
        try {
            return BeautyTransaction::create($data);
        } catch (QueryException $e) {
            // Check if this is a duplicate key error (MySQL error code 1062)
            // بررسی اینکه آیا این خطای کلید تکراری است (کد خطای MySQL 1062)
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                // Find existing transaction
                // یافتن تراکنش موجود
                $query = BeautyTransaction::where('transaction_type', $data['transaction_type']);
                
                // Use booking_id for booking-related transactions
                // استفاده از booking_id برای تراکنش‌های مربوط به رزرو
                if (isset($data['booking_id']) && $data['booking_id'] !== null) {
                    $query->where('booking_id', $data['booking_id']);
                }
                
                // Use reference_number for non-booking transactions
                // استفاده از reference_number برای تراکنش‌های غیر رزرو
                if (isset($data['reference_number']) && $data['reference_number'] !== null) {
                    $query->where('reference_number', $data['reference_number']);
                }
                
                $existing = $query->first();
                
                if ($existing) {
                    \Log::info('Duplicate transaction prevented, returning existing', [
                        'transaction_type' => $data['transaction_type'],
                        'booking_id' => $data['booking_id'] ?? null,
                        'reference_number' => $data['reference_number'] ?? null,
                        'existing_id' => $existing->id,
                    ]);
                    return $existing;
                }
            }
            
            // Re-throw if not a duplicate key error
            // پرتاب مجدد در صورت عدم خطای کلید تکراری
            throw $e;
        }
    }
    
    /**
     * Get revenue breakdown by transaction type
     * دریافت تفکیک درآمد بر اساس نوع تراکنش
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    public function getRevenueBreakdown(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        $transactionTypes = [
            'commission',
            'subscription',
            'advertisement',
            'service_fee',
            'package_sale',
            'cancellation_fee',
            'consultation_fee',
            'cross_selling',
            'retail_sale',
            'gift_card_sale',
            'loyalty_campaign',
        ];
        
        $breakdown = [];
        
        foreach ($transactionTypes as $type) {
            $amount = BeautyTransaction::where('transaction_type', $type)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('amount');
            
            // Ensure amount is numeric before rounding (PHP 8.4+ requires int|float, not string)
            // اطمینان از عددی بودن amount قبل از گرد کردن (PHP 8.4+ نیاز به int|float دارد، نه string)
            $amount = (float)($amount ?? 0);
            
            $breakdown[$type] = round($amount, 2);
        }
        
        return $breakdown;
    }
}

