<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\CentralLogics\CustomerLogic;

/**
 * Beauty Gift Card Controller (Customer API)
 * کنترلر کارت هدیه (API مشتری)
 */
class BeautyGiftCardController extends Controller
{
    use BeautyApiResponse;

    public function __construct(
        private BeautyGiftCard $giftCard
    ) {}

    /**
     * Redeem gift card
     * استفاده از کارت هدیه
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @bodyParam code string required Gift card code. Example: GCABC123XYZ
     * 
     * @response 200 {
     *   "message": "Gift card redeemed successfully",
     *   "data": {
     *     "amount": 100000,
     *     "salon_id": 1,
     *     "wallet_balance": 150000
     *   }
     * }
     * 
     * @response 404 {
     *   "errors": [
     *     {
     *       "code": "gift_card",
     *       "message": "Gift card not found"
     *     }
     *   ]
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "gift_card",
     *       "message": "Gift card invalid or expired"
     *     }
     *   ]
     * }
     */
    public function redeem(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $giftCard = $this->giftCard->where('code', $request->code)->first();

        if (!$giftCard) {
            return $this->errorResponse([
                ['code' => 'gift_card', 'message' => translate('gift_card_not_found')],
            ], 404);
        }

        if (!$giftCard->isValid()) {
            return $this->errorResponse([
                ['code' => 'gift_card', 'message' => translate('gift_card_invalid_or_expired')],
            ]);
        }

        if ($giftCard->status !== 'active') {
            return $this->errorResponse([
                ['code' => 'gift_card', 'message' => translate('gift_card_already_redeemed')],
            ]);
        }

        try {
            DB::beginTransaction();
            
            // Add gift card amount to user wallet
            // افزودن مبلغ کارت هدیه به کیف پول کاربر
            $userId = $request->user()->id;
            $walletTransaction = CustomerLogic::create_wallet_transaction(
                $userId,
                $giftCard->amount,
                'add_fund', // Use 'add_fund' type for gift card redemption
                'beauty_gift_card_' . $giftCard->id
            );
            
                if (!$walletTransaction) {
                    DB::rollBack();
                    return $this->errorResponse([
                        ['code' => 'gift_card', 'message' => translate('wallet_transaction_failed')],
                    ]);
                }
            
            // Update gift card status
            // به‌روزرسانی وضعیت کارت هدیه
            $giftCard->update([
                'status' => 'redeemed',
                'redeemed_by' => $userId,
                'redeemed_at' => now(),
            ]);
            
            DB::commit();

            return $this->successResponse('gift_card_redeemed_successfully', [
                'amount' => $giftCard->amount,
                'salon_id' => $giftCard->salon_id,
                'wallet_balance' => $request->user()->fresh()->wallet_balance,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gift card redemption failed', [
                'gift_card_id' => $giftCard->id,
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse([
                ['code' => 'gift_card', 'message' => translate('failed_to_redeem_gift_card')],
            ]);
        }
    }

    /**
     * Purchase gift card
     * خرید کارت هدیه
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @bodyParam salon_id integer optional Salon ID (null for general gift card). Example: 1
     * @bodyParam amount numeric required Gift card amount (min: 10000). Example: 100000
     * @bodyParam payment_method string required Payment method (wallet/digital_payment/cash_payment). Example: wallet
     * 
     * @response 201 {
     *   "message": "Gift card purchased successfully",
     *   "data": {
     *     "gift_card": {
     *       "id": 1,
     *       "code": "GCABC123XYZ",
     *       "amount": 100000,
     *       "expires_at": "2025-12-01",
     *       "status": "active"
     *     }
     *   }
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "validation",
     *       "message": "The amount must be at least 10000."
     *     }
     *   ]
     * }
     */
    public function purchase(Request $request): JsonResponse
    {
        // Convert 'online' to 'digital_payment' for backward compatibility
        // تبدیل 'online' به 'digital_payment' برای سازگاری با نسخه‌های قبلی
        if ($request->payment_method === 'online') {
            $request->merge(['payment_method' => 'digital_payment']);
        }
        
        $validator = Validator::make($request->all(), [
            'salon_id' => 'nullable|integer|exists:beauty_salons,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:wallet,digital_payment,cash_payment',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            DB::beginTransaction();
            
            $userId = $request->user()->id;
            $amount = $request->amount;
            
            // Generate unique gift card code
            // تولید کد منحصر به فرد کارت هدیه
            $code = $this->generateGiftCardCode();
            
            // Calculate expiry date (default 1 year from now)
            // محاسبه تاریخ انقضا (پیش‌فرض 1 سال از الان)
            $expiryDays = config('beautybooking.gift_card.validity_days', 365);
            $expiresAt = now()->addDays($expiryDays);
            
            // Create gift card
            // ایجاد کارت هدیه
            $giftCard = $this->giftCard->create([
                'code' => $code,
                'salon_id' => $request->salon_id,
                'purchased_by' => $userId,
                'amount' => $amount,
                'status' => 'active',
                'expires_at' => $expiresAt,
            ]);
            
            // Process payment
            // پردازش پرداخت
            $paymentService = app(\Modules\BeautyBooking\Services\BeautyBookingService::class);
            // For gift cards, we'll use a simple payment processing
            // برای کارت‌های هدیه، از پردازش پرداخت ساده استفاده می‌کنیم
            if ($request->payment_method === 'wallet') {
                // Deduct from wallet
                // کسر از کیف پول
                $walletTransaction = CustomerLogic::create_wallet_transaction(
                    $userId,
                    -$amount,
                    'beauty_gift_card_purchase',
                    $giftCard->id
                );
                
                if (!$walletTransaction) {
                    DB::rollBack();
                    return $this->errorResponse([
                        ['code' => 'payment', 'message' => translate('insufficient_wallet_balance')],
                    ]);
                }
            } elseif ($request->payment_method === 'digital_payment') {
                // Digital payment would be handled through payment gateway
                // پرداخت دیجیتال از طریق درگاه پرداخت انجام می‌شود
                // For now, mark as pending and handle via webhook
                // فعلاً به عنوان pending علامت بزنید و از طریق webhook مدیریت کنید
                $giftCard->update(['status' => 'pending_payment']);
            }
            
            // Record revenue if payment is successful
            // ثبت درآمد در صورت موفقیت پرداخت
            if ($request->payment_method === 'wallet' || $request->payment_method === 'cash_payment') {
                $revenueService = app(\Modules\BeautyBooking\Services\BeautyRevenueService::class);
                $revenueService->recordGiftCardSale($giftCard);
            }
            
            DB::commit();
            
            return $this->successResponse('gift_card_purchased_successfully', [
                'gift_card' => [
                    'id' => $giftCard->id,
                    'code' => $giftCard->code,
                    'amount' => $giftCard->amount,
                    'expires_at' => $giftCard->expires_at->format('Y-m-d'),
                    'status' => $giftCard->status,
                    'salon_id' => $giftCard->salon_id,
                    'salon_name' => $giftCard->salon?->store?->name ?? null,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gift card purchase failed', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse([
                ['code' => 'gift_card', 'message' => translate('failed_to_purchase_gift_card')],
            ]);
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
        } while ($this->giftCard->where('code', $code)->exists());
        
        return $code;
    }

    /**
     * Index - List user's gift cards
     * لیست کارت‌های هدیه کاربر
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @queryParam limit integer optional Number of items per page (default: 25). Example: 25
     * @queryParam offset integer optional Offset for pagination (default: 0). Example: 0
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": [...],
     *   "total": 5,
     *   "per_page": 25,
     *   "current_page": 1,
     *   "last_page": 1
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->get('per_page', $request->get('limit', 25));
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        // Fixed: paginate() expects page number (1, 2, 3...), not offset
        // اصلاح شده: paginate() شماره صفحه را انتظار دارد (1, 2, 3...)، نه offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $giftCards = $this->giftCard->where('purchased_by', $request->user()->id)
            ->with(['salon.store'])
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $formatted = $giftCards->getCollection()->map(function ($giftCard) {
            return [
                'id' => $giftCard->id,
                'code' => $giftCard->code,
                'amount' => $giftCard->amount,
                'expires_at' => $giftCard->expires_at->format('Y-m-d'),
                'status' => $giftCard->status,
                'salon' => $giftCard->salon ? [
                    'id' => $giftCard->salon->id,
                    'name' => $giftCard->salon->store?->name ?? '',
                ] : null,
            ];
        });

        $giftCards->setCollection($formatted->values());

        return $this->listResponse($giftCards);
    }
}

