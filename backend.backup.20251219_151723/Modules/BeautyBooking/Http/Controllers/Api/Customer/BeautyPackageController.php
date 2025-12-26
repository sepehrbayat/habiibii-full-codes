<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyPackageUsage;
use Modules\BeautyBooking\Traits\BeautyApiResponse;

/**
 * Beauty Package Controller (Customer API)
 * کنترلر پکیج (API مشتری)
 */
class BeautyPackageController extends Controller
{
    use BeautyApiResponse;

    /**
     * List available packages
     * لیست پکیج‌های موجود
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @queryParam salon_id integer optional Filter by salon ID. Example: 1
     * @queryParam service_id integer optional Filter by service ID. Example: 1
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
    public function index(Request $request)
    {
        $limit = $request->get('per_page', $request->get('limit', 25));
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $packages = BeautyPackage::with(['salon.store', 'service'])
            ->where('status', 1)
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->salon_id);
            })
            ->when($request->filled('service_id'), function ($query) use ($request) {
                $query->where('service_id', $request->service_id);
            })
            ->paginate($limit, ['*'], 'page', $page);

        $formatted = $packages->getCollection()->map(function ($package) {
            return $this->formatPackage($package);
        });

        $packages->setCollection($formatted->values());

        return $this->listResponse($packages, 'messages.data_retrieved_successfully');
    }

    /**
     * Get package details
     * دریافت جزئیات پکیج
     *
     * @param int $id Package ID
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Hair Care Package",
     *     "sessions_count": 5,
     *     "total_price": 500000,
     *     "salon": {...},
     *     "service": {...}
     *   }
     * }
     * 
     * @response 404 {
     *   "errors": [
     *     {
     *       "code": "package",
     *       "message": "Package not found"
     *     }
     *   ]
     * }
     */
    public function show(int $id)
    {
        $package = BeautyPackage::with(['salon.store', 'service'])
            ->where('status', 1)
            ->findOrFail($id);

        return $this->successResponse(
            'messages.data_retrieved_successfully',
            $this->formatPackage($package, true)
        );
    }

    /**
     * Purchase package
     * خرید پکیج
     *
     * @param Request $request
     * @param int $id Package ID
     * @return \Illuminate\Http\JsonResponse
     * 
     * @bodyParam payment_method string required Payment method (wallet/digital_payment/cash_payment). Example: wallet
     * 
     * @response 200 {
     *   "message": "Package purchased successfully",
     *   "data": {
     *     "package_id": 1,
     *     "package_name": "Hair Care Package",
     *     "sessions_count": 5,
     *     "total_price": 500000,
     *     "payment_status": "paid"
     *   }
     * }
     * 
     * @response 400 {
     *   "errors": [
     *     {
     *       "code": "package",
     *       "message": "Package already purchased"
     *     }
     *   ]
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "validation",
     *       "message": "The payment_method field is required."
     *     }
     *   ]
     * }
     */
    public function purchase(Request $request, int $id)
    {
        // Convert 'online' to 'digital_payment' for backward compatibility
        // تبدیل 'online' به 'digital_payment' برای سازگاری با نسخه‌های قبلی
        if ($request->payment_method === 'online') {
            $request->merge(['payment_method' => 'digital_payment']);
        }
        
        $validator = \Validator::make($request->all(), [
            'payment_method' => 'required|string|in:wallet,digital_payment,cash_payment',
            'payment_gateway' => 'nullable|string|in:stripe,paypal,razorpay',
            'callback_url' => 'nullable|url',
            'payment_platform' => 'nullable|string|in:web,mobile',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            $package = BeautyPackage::with(['salon.store', 'service', 'salon'])
                ->where('status', 1)
                ->findOrFail($id);
            
            $user = $request->user();
            
            // Check if user already has an active (non-expired, with remaining sessions) package of this type
            // بررسی اینکه آیا کاربر قبلاً یک پکیج فعال (منقضی نشده، با جلسات باقیمانده) از این نوع دارد
            // Use the package's isValidForUser method to properly check validity
            // استفاده از متد isValidForUser پکیج برای بررسی صحیح اعتبار
            if ($package->isValidForUser($user->id)) {
                // Check if there are any usage records (meaning package was purchased)
                // بررسی وجود رکوردهای استفاده (یعنی پکیج خریداری شده است)
                $hasUsageRecords = \Modules\BeautyBooking\Entities\BeautyPackageUsage::where('package_id', $package->id)
                ->where('user_id', $user->id)
                ->exists();
            
                if ($hasUsageRecords) {
                return $this->errorResponse([
                    ['code' => 'package', 'message' => translate('messages.package_already_purchased')]
                ], 400);
                }
            }
            
            return \DB::transaction(function () use ($request, $package, $user) {
                // Process payment
                // پردازش پرداخت
                if ($request->payment_method === 'wallet') {
                    // Check wallet balance
                    // بررسی موجودی کیف پول
                    if ($user->wallet_balance < $package->total_price) {
                        return $this->errorResponse([
                            ['code' => 'payment', 'message' => translate('messages.insufficient_wallet_balance')]
                        ], 400);
                    }
                    
                    // Deduct from wallet
                    // کسر از کیف پول
                    $walletTransaction = \App\CentralLogics\CustomerLogic::create_wallet_transaction(
                        $user->id,
                        -$package->total_price,
                        'beauty_package_purchase',
                        $package->id
                    );
                    
                    if (!$walletTransaction) {
                        return $this->errorResponse([
                            ['code' => 'payment', 'message' => translate('messages.wallet_transaction_failed')]
                        ], 500);
                    }
                } elseif ($request->payment_method === 'digital_payment') {
                    // Digital payment will be handled via payment gateway
                    // پرداخت دیجیتال از طریق درگاه پرداخت انجام می‌شود
                    // Create pending transaction record so webhook can find and update it
                    // ایجاد رکورد تراکنش pending تا webhook بتواند آن را پیدا کرده و به‌روزرسانی کند
                    $salon = $package->salon;
                    $zoneId = $salon->store?->zone_id ?? $salon->zone_id ?? null;
                    $referenceNumber = 'package_' . $package->id . '_user_' . $user->id . '_' . time();
                    
                    // Create pending transaction record
                    // ایجاد رکورد تراکنش pending
                    \Modules\BeautyBooking\Entities\BeautyTransaction::create([
                        'booking_id' => null, // No booking, package purchase
                        'salon_id' => $package->salon_id,
                        'zone_id' => $zoneId,
                        'transaction_type' => 'package_sale',
                        'amount' => $package->total_price,
                        'commission' => 0, // Will be calculated when payment is confirmed
                        'service_fee' => 0,
                        'status' => 'pending',
                        'reference_number' => $referenceNumber,
                        'notes' => 'Package purchase (pending payment): ' . $package->name . ' by user #' . $user->id,
                    ]);
                    
                    // Payment will be confirmed via webhook (beauty_booking_payment_success)
                    // پرداخت از طریق webhook تأیید می‌شود (beauty_booking_payment_success)
                    // Webhook should update this transaction using reference_number
                    // Webhook باید این تراکنش را با استفاده از reference_number به‌روزرسانی کند
                }
                
                // Create package usage records for all sessions (status: pending)
                // ایجاد رکوردهای استفاده از پکیج برای تمام جلسات (وضعیت: pending)
                // Note: used_at must be null for pending sessions to ensure correct expiry calculation
                // توجه: used_at باید برای جلسات pending null باشد تا محاسبه انقضا به درستی انجام شود
                $usageRecords = [];
                for ($sessionNumber = 1; $sessionNumber <= $package->sessions_count; $sessionNumber++) {
                    $usageRecords[] = \Modules\BeautyBooking\Entities\BeautyPackageUsage::create([
                        'package_id' => $package->id,
                        'user_id' => $user->id,
                        'salon_id' => $package->salon_id,
                        'session_number' => $sessionNumber,
                        'status' => 'pending',
                        'used_at' => null, // Must be null for pending - will be set when session is actually used
                    ]);
                }
                
                // Record revenue if payment is successful (wallet or cash)
                // ثبت درآمد در صورت موفقیت پرداخت (کیف پول یا نقدی)
                if ($request->payment_method === 'wallet' || $request->payment_method === 'cash_payment') {
                    // Calculate commission from total package price
                    // محاسبه کمیسیون از کل مبلغ پکیج
                    $commissionService = app(\Modules\BeautyBooking\Services\BeautyCommissionService::class);
                    $commissionAmount = $commissionService->calculateCommission(
                        $package->salon_id,
                        $package->service_id,
                        $package->total_price
                    );
                    
                    // Get zone_id from salon
                    // دریافت zone_id از سالن
                    $salon = $package->salon;
                    $zoneId = $salon->store?->zone_id ?? $salon->zone_id ?? null;
                    
                    // Record package sale transaction
                    // ثبت تراکنش فروش پکیج
                    \Modules\BeautyBooking\Entities\BeautyTransaction::create([
                        'booking_id' => null, // No booking yet, package is purchased upfront
                        'salon_id' => $package->salon_id,
                        'zone_id' => $zoneId,
                        'transaction_type' => 'package_sale',
                        'amount' => $package->total_price,
                        'commission' => round($commissionAmount, 2),
                        'service_fee' => 0,
                        'status' => 'completed',
                        'reference_number' => 'package_' . $package->id . '_user_' . $user->id,
                        'notes' => 'Package purchase: ' . $package->name . ' by user #' . $user->id,
                    ]);
                    
                    // Award loyalty points for package purchase
                    // اعطای امتیاز وفاداری برای خرید پکیج
                    try {
                        $loyaltyService = app(\Modules\BeautyBooking\Services\BeautyLoyaltyService::class);
                        // Award points based on package purchase amount
                        // اعطای امتیاز بر اساس مبلغ خرید پکیج
                        $loyaltyService->awardPointsForPurchase($user->id, $package->salon_id, $package->total_price, 'package_purchase');
                    } catch (\Exception $e) {
                        // Log but don't fail package purchase if loyalty points fail
                        // ثبت اما عدم شکست خرید پکیج در صورت شکست امتیازهای وفاداری
                        \Log::warning('Failed to award loyalty points for package purchase', [
                            'package_id' => $package->id,
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                return $this->successResponse(
                    'messages.package_purchased_successfully',
                    [
                        'package_id' => $package->id,
                        'package_name' => $package->name,
                        'sessions_count' => $package->sessions_count,
                        'total_price' => $package->total_price,
                        'validity_days' => $package->validity_days,
                        'remaining_sessions' => $package->sessions_count,
                        'payment_method' => $request->payment_method,
                        'payment_status' => $request->payment_method === 'digital_payment' ? 'pending' : 'paid',
                        'usage_records' => collect($usageRecords)->map(function ($usage) {
                            return [
                                'session_number' => $usage->session_number,
                                'status' => $usage->status,
                            ];
                        }),
                        'salon' => [
                            'id' => $package->salon?->id ?? null,
                            'name' => $package->salon?->store?->name ?? '',
                        ],
                        'service' => [
                            'id' => $package->service?->id ?? null,
                            'name' => $package->service?->name ?? '',
                        ],
                    ]
                );
            });
        } catch (\Exception $e) {
            \Log::error('Package purchase failed', [
                'package_id' => $id,
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse([
                ['code' => 'package', 'message' => translate('messages.failed_to_purchase_package')]
            ], 500);
        }
    }
    
    /**
     * Get package status (remaining sessions)
     * دریافت وضعیت پکیج (جلسات باقیمانده)
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getPackageStatus(Request $request, int $id): JsonResponse
    {
        try {
            $package = BeautyPackage::findOrFail($id);
            $userId = $request->user()->id;
            
            $remainingSessions = $package->getRemainingSessions($userId);
            $isValid = $package->isValidForUser($userId);
            
            $usages = BeautyPackageUsage::where('package_id', $id)
                ->where('user_id', $userId)
                ->orderBy('session_number', 'asc')
                ->get();
            
            return $this->successResponse('messages.data_retrieved_successfully', [
                'package_id' => $package->id,
                'package_name' => $package->name,
                'total_sessions' => $package->sessions_count,
                'remaining_sessions' => $remainingSessions,
                'used_sessions' => $package->sessions_count - $remainingSessions,
                'is_valid' => $isValid,
                'validity_days' => $package->validity_days,
                'usages' => $usages->map(function ($usage) {
                    return [
                        'session_number' => $usage->session_number,
                        'used_at' => $usage->used_at ? $usage->used_at->format('Y-m-d H:i:s') : null,
                        'status' => $usage->status,
                        'booking_id' => $usage->booking_id,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            \Log::error('Package status retrieval failed', [
                'package_id' => $id,
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse([
                ['code' => 'package', 'message' => translate('messages.failed_to_retrieve_package_status')],
            ], 500);
        }
    }
    
    /**
     * Get usage history for a package
     * دریافت تاریخچه استفاده از پکیج
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getUsageHistory(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $package = BeautyPackage::with(['salon.store', 'service'])->findOrFail($id);
        
        $usages = BeautyPackageUsage::where('package_id', $id)
            ->where('user_id', $userId)
            ->with('booking')
            ->orderBy('session_number', 'asc')
            ->get();
        
        return $this->successResponse('messages.data_retrieved_successfully', [
            'package' => $this->formatPackage($package, true),
            'usages' => $usages->map(function ($usage) {
                return [
                    'session_number' => $usage->session_number,
                    'status' => $usage->status,
                    'used_at' => $usage->used_at ? $usage->used_at->format('Y-m-d H:i:s') : null,
                    'booking' => $usage->booking ? [
                        'id' => $usage->booking?->id,
                        'booking_reference' => $usage->booking?->booking_reference,
                        'booking_date' => $usage->booking?->booking_date ? $usage->booking->booking_date->format('Y-m-d') : null,
                    ] : null,
                ];
            }),
        ]);
    }
    
    /**
     * Format package for API responses
     * فرمت پکیج برای پاسخ‌های API
     *
     * @param BeautyPackage $package
     * @param bool $includeRelations
     * @return array
     */
    private function formatPackage(BeautyPackage $package, bool $includeRelations = false): array
    {
        $data = [
            'id' => $package->id,
            'name' => $package->name,
            'description' => $package->description,
            'sessions_count' => $package->sessions_count,
            'total_price' => $package->total_price,
            'validity_days' => $package->validity_days,
            'image' => $package->image ? asset('storage/' . $package->image) : null,
            'salon' => [
                'id' => $package->salon?->id ?? null,
                'name' => $package->salon?->store?->name ?? '',
            ],
            'service' => [
                'id' => $package->service?->id ?? null,
                'name' => $package->service?->name ?? '',
                'duration_minutes' => $package->service?->duration_minutes ?? null,
                'price' => $package->service?->price ?? null,
            ],
        ];
        
        if ($includeRelations) {
            $data['salon']['address'] = $package->salon?->store?->address ?? null;
        }
        
        return $data;
    }
}

