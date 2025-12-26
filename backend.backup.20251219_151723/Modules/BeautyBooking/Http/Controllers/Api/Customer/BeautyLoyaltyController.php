<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BeautyBooking\Entities\BeautyLoyaltyPoint;
use Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign;
use Modules\BeautyBooking\Traits\BeautyApiResponse;

/**
 * Beauty Loyalty Controller (Customer API)
 * کنترلر وفاداری (API مشتری)
 */
class BeautyLoyaltyController extends Controller
{
    use BeautyApiResponse;

    /**
     * Get loyalty points balance
     * دریافت موجودی امتیازهای وفاداری
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "total_points": 500,
     *     "used_points": 100,
     *     "available_points": 400
     *   }
     * }
     */
    public function getPoints(Request $request)
    {
        $user = $request->user();
        
        $totalPoints = (float) BeautyLoyaltyPoint::where('user_id', $user->id)
            ->valid()
            ->earned()
            ->sum('points');

        $usedPointsRaw = BeautyLoyaltyPoint::where('user_id', $user->id)
            ->where('type', 'redeemed')
            ->sum('points');
        $usedPoints = abs((float) $usedPointsRaw);

        $availablePoints = $totalPoints - $usedPoints;

        return $this->successResponse(
            'messages.data_retrieved_successfully',
            [
                'total_points' => $totalPoints,
                'used_points' => $usedPoints,
                'available_points' => max(0, $availablePoints),
            ]
        );
    }

    /**
     * List active loyalty campaigns
     * لیست کمپین‌های وفاداری فعال
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @queryParam salon_id integer optional Filter by salon ID. Example: 1
     * @queryParam limit integer optional Number of items per page (default: 25). Example: 25
     * @queryParam offset integer optional Offset for pagination (default: 0). Example: 0
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": [...],
     *   "total": 10,
     *   "per_page": 25,
     *   "current_page": 1,
     *   "last_page": 1
     * }
     */
    public function getCampaigns(Request $request)
    {
        $limit = $request->get('per_page', $request->get('limit', 25));
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $campaigns = BeautyLoyaltyCampaign::with(['salon.store'])
            ->active() // Use scopeActive() which properly checks is_active boolean and handles null end_date
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->salon_id);
            })
            ->paginate($limit, ['*'], 'page', $page);

        $formatted = $campaigns->getCollection()->map(function ($campaign) {
            return [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'description' => $campaign->description,
                'type' => $campaign->type,
                'rules' => $campaign->rules,
                'start_date' => $campaign->start_date ? $campaign->start_date->format('Y-m-d') : null,
                'end_date' => $campaign->end_date ? $campaign->end_date->format('Y-m-d') : null,
                'salon' => $campaign->salon ? [
                    'id' => $campaign->salon->id,
                    'name' => $campaign->salon->store?->name ?? '',
                ] : null,
                'is_active' => $campaign->isActive(),
            ];
        });

        $campaigns->setCollection($formatted->values());

        return $this->listResponse($campaigns, 'messages.data_retrieved_successfully');
    }

    /**
     * Redeem loyalty points
     * استفاده از امتیازهای وفاداری
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @bodyParam campaign_id integer required Campaign ID. Example: 1
     * @bodyParam points integer required Points to redeem (min: 1). Example: 100
     * 
     * @response 200 {
     *   "message": "Points redeemed successfully",
     *   "data": {
     *     "campaign_id": 1,
     *     "campaign_name": "Discount Campaign",
     *     "points_redeemed": 100,
     *     "remaining_points": 300,
     *     "reward": {
     *       "type": "discount_percentage",
     *       "value": 10,
     *       "description": "10% discount on next booking"
     *     }
     *   }
     * }
     * 
     * @response 400 {
     *   "errors": [
     *     {
     *       "code": "points",
     *       "message": "Insufficient loyalty points"
     *     }
     *   ]
     * }
     */
    public function redeem(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'campaign_id' => 'required|integer|exists:beauty_loyalty_campaigns,id',
            'points' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            $user = $request->user();
            $campaign = BeautyLoyaltyCampaign::findOrFail($request->campaign_id);
            
            // Check if campaign is active
            // بررسی فعال بودن کمپین
            if (!$campaign->isActive()) {
                return $this->errorResponse([
                    ['code' => 'campaign', 'message' => translate('messages.campaign_not_active')]
                ], 400);
            }
            
            // Check campaign expiry
            // بررسی انقضای کمپین
            if ($campaign->end_date && $campaign->end_date->isPast()) {
                return $this->errorResponse([
                    ['code' => 'campaign', 'message' => translate('messages.campaign_expired')]
                ], 400);
            }
            
            // Check campaign start date
            // بررسی تاریخ شروع کمپین
            if ($campaign->start_date && $campaign->start_date->isFuture()) {
                return $this->errorResponse([
                    ['code' => 'campaign', 'message' => translate('messages.campaign_not_started')]
                ], 400);
            }
            
            // Check campaign limits (max redemptions, max points per user, etc.)
            // بررسی محدودیت‌های کمپین (حداکثر استفاده، حداکثر امتیاز برای هر کاربر و غیره)
            $rules = $campaign->rules ?? [];
            
            // Check maximum points per redemption
            // بررسی حداکثر امتیاز برای هر استفاده
            if (isset($rules['max_points_per_redemption']) && $request->points > $rules['max_points_per_redemption']) {
                return $this->errorResponse([
                    ['code' => 'points', 'message' => translate('messages.points_exceed_max_per_redemption')]
                ], 400);
            }
            
            // Check minimum points per redemption
            // بررسی حداقل امتیاز برای هر استفاده
            if (isset($rules['min_points_per_redemption']) && $request->points < $rules['min_points_per_redemption']) {
                return $this->errorResponse([
                    ['code' => 'points', 'message' => translate('messages.points_below_min_per_redemption')]
                ], 400);
            }
            
            // Check maximum total redemptions for campaign
            // بررسی حداکثر تعداد کل استفاده‌ها برای کمپین
            if (isset($rules['max_total_redemptions']) && $campaign->total_redeemed >= $rules['max_total_redemptions']) {
                return $this->errorResponse([
                    ['code' => 'campaign', 'message' => translate('messages.campaign_redemption_limit_reached')]
                ], 400);
            }
            
            // Check maximum redemptions per user
            // بررسی حداکثر تعداد استفاده برای هر کاربر
            if (isset($rules['max_redemptions_per_user'])) {
                $userRedemptions = \Modules\BeautyBooking\Entities\BeautyLoyaltyPoint::where('user_id', $user->id)
                    ->where('campaign_id', $campaign->id)
                    ->where('type', 'redeemed')
                    ->count();
                
                if ($userRedemptions >= $rules['max_redemptions_per_user']) {
                    return $this->errorResponse([
                        ['code' => 'campaign', 'message' => translate('messages.user_redemption_limit_reached')]
                    ], 400);
                }
            }
            
            // Check if user has enough points
            // بررسی داشتن امتیاز کافی
            $loyaltyService = app(\Modules\BeautyBooking\Services\BeautyLoyaltyService::class);
            $availablePoints = $loyaltyService->getTotalPoints($user->id, $campaign->salon_id);
            
            if ($availablePoints < $request->points) {
                return $this->errorResponse([
                    ['code' => 'points', 'message' => translate('messages.insufficient_loyalty_points')]
                ], 400);
            }
            
            return \DB::transaction(function () use ($request, $user, $campaign, $loyaltyService) {
                // Redeem points using the service
                // استفاده از امتیازها با استفاده از سرویس
                $redeemed = $loyaltyService->redeemPoints(
                    $user->id,
                    $request->points,
                    $campaign->salon_id,
                    'Redeemed for campaign: ' . $campaign->name
                );
                
                if (!$redeemed) {
                    return $this->errorResponse([
                        ['code' => 'points', 'message' => translate('messages.failed_to_redeem_points')]
                    ], 500);
                }
                
                // Apply reward based on campaign type and rules
                // اعمال پاداش بر اساس نوع و قوانین کمپین
                $rules = $campaign->rules ?? [];
                $reward = null;
                
                if ($campaign->type === 'discount') {
                    // Discount-based redemption: points convert to discount percentage or amount
                    // استفاده مبتنی بر تخفیف: امتیازها به درصد یا مبلغ تخفیف تبدیل می‌شوند
                    $discountPercentage = $rules['discount_percentage'] ?? 0;
                    $discountAmount = $rules['discount_amount'] ?? 0;
                    
                    if ($discountPercentage > 0) {
                        $reward = [
                            'type' => 'discount_percentage',
                            'value' => $discountPercentage,
                            'description' => $discountPercentage . '% discount on next booking',
                        ];
                    } elseif ($discountAmount > 0) {
                        $reward = [
                            'type' => 'discount_amount',
                            'value' => $discountAmount,
                            'description' => $discountAmount . ' discount on next booking',
                        ];
                    }
                } elseif ($campaign->type === 'wallet_credit') {
                    // Wallet credit: points convert to wallet balance
                    // اعتبار کیف پول: امتیازها به موجودی کیف پول تبدیل می‌شوند
                    $pointsToCreditRatio = $rules['points_to_credit_ratio'] ?? 1; // e.g., 100 points = 1000 credit
                    $creditAmount = ($request->points / $pointsToCreditRatio) * ($rules['credit_per_point'] ?? 10);
                    
                    // Add credit to wallet
                    // افزودن اعتبار به کیف پول
                    $walletTransaction = \App\CentralLogics\CustomerLogic::create_wallet_transaction(
                        $user->id,
                        $creditAmount,
                        'beauty_loyalty_redemption',
                        $campaign->id
                    );
                    
                    if ($walletTransaction) {
                        $reward = [
                            'type' => 'wallet_credit',
                            'value' => $creditAmount,
                            'description' => $creditAmount . ' added to wallet',
                            'wallet_balance' => $user->fresh()->wallet_balance,
                        ];
                    }
                } elseif ($campaign->type === 'cashback') {
                    // Cashback: points convert to cashback amount added to wallet
                    // بازگشت وجه: امتیازها به مبلغ بازگشت وجه تبدیل شده و به کیف پول اضافه می‌شوند
                    $cashbackPerPoint = $rules['cashback_per_point'] ?? 10;
                    $cashbackAmount = $request->points * $cashbackPerPoint;
                    
                    // Add cashback to wallet
                    // افزودن بازگشت وجه به کیف پول
                    $walletTransaction = \App\CentralLogics\CustomerLogic::create_wallet_transaction(
                        $user->id,
                        $cashbackAmount,
                        'beauty_loyalty_cashback',
                        $campaign->id
                    );
                    
                    if ($walletTransaction) {
                        $reward = [
                            'type' => 'cashback',
                            'value' => $cashbackAmount,
                            'description' => $cashbackAmount . ' cashback added to wallet',
                            'wallet_balance' => $user->fresh()->wallet_balance,
                        ];
                    }
                } elseif ($campaign->type === 'gift_card') {
                    // Gift card: points convert to gift card
                    // کارت هدیه: امتیازها به کارت هدیه تبدیل می‌شوند
                    $giftCardAmount = $rules['gift_card_amount'] ?? ($request->points * ($rules['points_to_gift_card_ratio'] ?? 100));
                    
                    // Validate minimum gift card amount
                    // اعتبارسنجی حداقل مبلغ کارت هدیه
                    $minAmount = config('beautybooking.gift_card.min_amount', 10000);
                    if ($giftCardAmount < $minAmount) {
                        return $this->errorResponse([
                            ['code' => 'campaign', 'message' => translate('messages.gift_card_amount_below_minimum')]
                        ], 400);
                    }
                    
                    // Generate unique gift card code
                    // تولید کد منحصر به فرد کارت هدیه
                    $code = $this->generateGiftCardCode();
                    
                    // Calculate expiry date (default from config)
                    // محاسبه تاریخ انقضا (پیش‌فرض از config)
                    $expiryDays = config('beautybooking.gift_card.validity_days', 365);
                    $expiresAt = now()->addDays($expiryDays);
                    
                    // Create gift card
                    // ایجاد کارت هدیه
                    $giftCard = \Modules\BeautyBooking\Entities\BeautyGiftCard::create([
                        'code' => $code,
                        'salon_id' => $campaign->salon_id,
                        'purchased_by' => $user->id,
                        'amount' => $giftCardAmount,
                        'status' => 'active',
                        'expires_at' => $expiresAt,
                    ]);
                    
                    // Record revenue for gift card sale
                    // ثبت درآمد برای فروش کارت هدیه
                    $revenueService = app(\Modules\BeautyBooking\Services\BeautyRevenueService::class);
                    $revenueService->recordGiftCardSale($giftCard);
                    
                    $reward = [
                        'type' => 'gift_card',
                        'gift_card_id' => $giftCard->id,
                        'gift_card_code' => $giftCard->code,
                        'value' => $giftCardAmount,
                        'description' => 'Gift card worth ' . $giftCardAmount . ' created',
                        'expires_at' => $giftCard->expires_at->format('Y-m-d'),
                    ];
                } elseif ($campaign->type === 'points') {
                    // Points-based: already redeemed, just return confirmation
                    // مبتنی بر امتیاز: قبلاً استفاده شده، فقط تأیید برگردانید
                    $reward = [
                        'type' => 'points_redeemed',
                        'points' => $request->points,
                        'description' => $request->points . ' points redeemed',
                    ];
                } else {
                    // Unknown campaign type - reject explicitly
                    // نوع کمپین ناشناخته - به صراحت رد کنید
                    return $this->errorResponse([
                        ['code' => 'campaign', 'message' => translate('messages.unsupported_campaign_type')]
                    ], 400);
                }
                
                // Update campaign statistics
                // به‌روزرسانی آمار کمپین
                $campaign->increment('total_redeemed');
                
                return $this->successResponse(
                    'messages.points_redeemed_successfully',
                    [
                        'campaign_id' => $campaign->id,
                        'campaign_name' => $campaign->name,
                        'points_redeemed' => $request->points,
                        'remaining_points' => $loyaltyService->getTotalPoints($user->id, $campaign->salon_id),
                        'reward' => $reward,
                        'wallet_balance' => $user->fresh()->wallet_balance ?? null,
                    ]
                );
            });
        } catch (\Exception $e) {
            \Log::error('Loyalty points redemption failed', [
                'campaign_id' => $request->campaign_id ?? null,
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse([
                ['code' => 'loyalty', 'message' => translate('messages.failed_to_redeem_points')]
            ], 500);
        }
    }

    /**
     * Generate unique gift card code
     * تولید کد منحصر به فرد کارت هدیه
     *
     * @return string
     */
    private function generateGiftCardCode(): string
    {
        do {
            $code = 'GC' . strtoupper(\Illuminate\Support\Str::random(10));
        } while (\Modules\BeautyBooking\Entities\BeautyGiftCard::where('code', $code)->exists());
        
        return $code;
    }
}

