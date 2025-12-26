<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautySubscription;
use Modules\BeautyBooking\Services\BeautyRevenueService;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\Traits\Payment;
use App\Library\Payer;
use App\Library\Payment as PaymentInfo;
use App\Library\Receiver;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use Carbon\Carbon;

/**
 * Beauty Subscription Controller (Vendor API)
 * کنترلر اشتراک (API فروشنده)
 */
class BeautySubscriptionController extends Controller
{
    use Payment, BeautyApiResponse;

    public function __construct(
        private BeautySubscription $subscription,
        private BeautyRevenueService $revenueService
    ) {}

    /**
     * Get available subscription plans
     * دریافت پلان‌های اشتراک موجود
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPlans(Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        // Subscription plans configuration
        // پیکربندی پلان‌های اشتراک
        $plans = [
            'featured_listing' => [
                '7' => ['duration_days' => 7, 'price' => config('beautybooking.subscription.featured.7_days', 50000)],
                '30' => ['duration_days' => 30, 'price' => config('beautybooking.subscription.featured.30_days', 150000)],
            ],
            'boost_ads' => [
                '7' => ['duration_days' => 7, 'price' => config('beautybooking.subscription.boost.7_days', 75000)],
                '30' => ['duration_days' => 30, 'price' => config('beautybooking.subscription.boost.30_days', 200000)],
            ],
            'banner_ads' => [
                'homepage' => ['duration_days' => 30, 'price' => config('beautybooking.subscription.banner.homepage', 100000)],
                'category' => ['duration_days' => 30, 'price' => config('beautybooking.subscription.banner.category', 75000)],
                'search_results' => ['duration_days' => 30, 'price' => config('beautybooking.subscription.banner.search_results', 60000)],
            ],
            'dashboard_subscription' => [
                'monthly' => ['duration_days' => 30, 'price' => config('beautybooking.subscription.dashboard.monthly', 50000)],
                'yearly' => ['duration_days' => 365, 'price' => config('beautybooking.subscription.dashboard.yearly', 500000)],
            ],
        ];

        $activeSubscriptions = $this->subscription->where('salon_id', $salon->id)
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->get();

        return $this->successResponse('messages.data_retrieved_successfully', [
            'plans' => $plans,
            'active_subscriptions' => $activeSubscriptions,
        ]);
    }

    /**
     * Purchase subscription
     * خرید اشتراک
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function purchase(\Modules\BeautyBooking\Http\Requests\BeautySubscriptionPurchaseRequest $request): JsonResponse
    {

        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        try {
            DB::beginTransaction();

            // Get plan price
            // دریافت قیمت پلان
            $planPrice = $this->getSubscriptionPrice($request->subscription_type, $request->duration_days, $request->ad_position);

            if ($planPrice <= 0) {
                DB::rollBack();
                return $this->errorResponse([
                    ['code' => 'subscription', 'message' => translate('messages.invalid_subscription_plan')]
                ]);
            }

            // Handle banner image upload
            // مدیریت آپلود تصویر بنر
            $bannerImagePath = null;
            if ($request->subscription_type === 'banner_ads' && $request->banner_image) {
                // If base64, decode and save
                // اگر base64 است، decode و ذخیره
                if (preg_match('/^data:image\/(\w+);base64,/', $request->banner_image, $matches)) {
                    $imageData = base64_decode(substr($request->banner_image, strpos($request->banner_image, ',') + 1));
                    $extension = $matches[1] ?? 'png';
                    $bannerImagePath = Helpers::upload('beauty/banners/', $extension, $imageData, true);
                } else {
                    // Assume it's a URL or path
                    // فرض کنید URL یا مسیر است
                    $bannerImagePath = $request->banner_image;
                }
            }

            // Process payment
            // پردازش پرداخت
            $paymentProcessed = false;
            $redirectUrl = null;

            switch ($request->payment_method) {
                case 'cash_payment':
                    // Create subscription for cash payment
                    // ایجاد اشتراک برای پرداخت نقدی
                    $startDate = now();
                    $endDate = $startDate->copy()->addDays($request->duration_days);

                    $subscription = $this->subscription->create([
                        'salon_id' => $salon->id,
                        'subscription_type' => $request->subscription_type,
                        'ad_position' => $request->ad_position ?? null,
                        'banner_image' => $bannerImagePath,
                        'duration_days' => $request->duration_days,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'amount_paid' => $planPrice,
                        'payment_method' => 'cash_payment',
                        'status' => 'active',
                    ]);

                    // Record revenue
                    // ثبت درآمد
                    $this->revenueService->recordSubscription($subscription);

                    // Update badge if needed
                    // به‌روزرسانی نشان در صورت نیاز
                    if ($request->subscription_type === 'featured_listing') {
                        $salon->update(['is_featured' => true]);
                        $badgeService = app(\Modules\BeautyBooking\Services\BeautyBadgeService::class);
                        $badgeService->assignBadgeIfNotExists($salon->id, 'featured', $endDate);
                    }

                    // Recalculate badges
                    // محاسبه مجدد نشان‌ها
                    $badgeService = app(\Modules\BeautyBooking\Services\BeautyBadgeService::class);
                    $badgeService->calculateAndAssignBadges($salon->id);

                    DB::commit();

                    return $this->successResponse('messages.subscription_purchased_successfully', $subscription->load(['salon']), 201);

                case 'wallet':
                    $store = $salon->store;
                    if (!$store) {
                        DB::rollBack();
                        return $this->errorResponse([
                            ['code' => 'wallet', 'message' => translate('messages.store_not_found')]
                        ]);
                    }

                    $wallet = \App\Models\StoreWallet::firstOrNew(['vendor_id' => $store->vendor_id]);
                    $balance = \App\Models\BusinessSetting::where('key', 'wallet_status')->first()?->value == 1
                        ? $wallet->balance ?? 0
                        : 0;

                    if ($balance < $planPrice) {
                        DB::rollBack();
                        return $this->errorResponse([
                            ['code' => 'wallet', 'message' => translate('messages.insufficient_wallet_balance')]
                        ]);
                    }

                    $wallet->total_withdrawn = ($wallet->total_withdrawn ?? 0) + $planPrice;
                    $wallet->save();

                    // Create subscription for wallet payment
                    // ایجاد اشتراک برای پرداخت کیف پول
                    $startDate = now();
                    $endDate = $startDate->copy()->addDays($request->duration_days);

                    $subscription = $this->subscription->create([
                        'salon_id' => $salon->id,
                        'subscription_type' => $request->subscription_type,
                        'ad_position' => $request->ad_position ?? null,
                        'banner_image' => $bannerImagePath,
                        'duration_days' => $request->duration_days,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'amount_paid' => $planPrice,
                        'payment_method' => 'wallet',
                        'status' => 'active',
                    ]);

                    // Record revenue
                    // ثبت درآمد
                    $this->revenueService->recordSubscription($subscription);

                    // Update badge if needed
                    // به‌روزرسانی نشان در صورت نیاز
                    if ($request->subscription_type === 'featured_listing') {
                        $salon->update(['is_featured' => true]);
                        $badgeService = app(\Modules\BeautyBooking\Services\BeautyBadgeService::class);
                        $badgeService->assignBadgeIfNotExists($salon->id, 'featured', $endDate);
                    }

                    // Recalculate badges
                    // محاسبه مجدد نشان‌ها
                    $badgeService = app(\Modules\BeautyBooking\Services\BeautyBadgeService::class);
                    $badgeService->calculateAndAssignBadges($salon->id);

                    DB::commit();

                    return $this->successResponse('messages.subscription_purchased_successfully', $subscription->load(['salon']), 201);

                case 'digital_payment':
                    // Create subscription with pending status
                    // ایجاد اشتراک با وضعیت pending
                    $startDate = now();
                    $endDate = $startDate->copy()->addDays($request->duration_days);

                    $subscription = $this->subscription->create([
                        'salon_id' => $salon->id,
                        'subscription_type' => $request->subscription_type,
                        'ad_position' => $request->ad_position ?? null,
                        'banner_image' => $bannerImagePath,
                        'duration_days' => $request->duration_days,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'amount_paid' => 0,
                        'payment_method' => 'digital_payment',
                        'status' => 'pending',
                    ]);

                    // Prepare payment
                    // آماده‌سازی پرداخت
                    $store = $salon->store;
                    $vendorObj = $store?->vendor ?? null;

                    if (!$store || !$vendorObj) {
                        DB::rollBack();
                        return $this->errorResponse([
                            ['code' => 'payment', 'message' => translate('messages.vendor_not_found')]
                        ]);
                    }

                    $payer = new Payer(
                        $store->name ?? ($vendorObj->f_name . ' ' . $vendorObj->l_name) ?? '',
                        $store->email ?? $vendorObj->email ?? '',
                        $store->phone ?? $vendorObj->phone ?? '',
                        ''
                    );

                    $storeLogo = BusinessSetting::where(['key' => 'logo'])->first();
                    // Fix: Use ->first() method instead of [0] array access to safely handle null storage
                    // رفع: استفاده از متد ->first() به جای دسترسی آرایه [0] برای مدیریت ایمن null storage
                    $storageValue = $storeLogo?->storage?->first()?->value ?? 'public';
                    $additionalData = [
                        'business_name' => BusinessSetting::where(['key' => 'business_name'])->first()?->value,
                        'business_logo' => Helpers::get_full_url('business', $storeLogo?->value, $storageValue)
                    ];

                    $paymentInfo = new PaymentInfo(
                        success_hook: 'beauty_subscription_payment_success',
                        failure_hook: 'beauty_subscription_payment_fail',
                        currency_code: Helpers::currency_code(),
                        payment_method: $request->payment_gateway ?? 'stripe',
                        payment_platform: $request->payment_platform ?? 'web',
                        payer_id: $vendorObj->id,
                        receiver_id: 1,
                        additional_data: $additionalData,
                        payment_amount: $planPrice,
                        external_redirect_link: $request->callback_url ?? route('beautybooking.vendor.subscription.index'),
                        attribute: 'beauty_subscription',
                        attribute_id: $subscription->id,
                    );

                    $receiverInfo = new Receiver('Admin', 'example.png');
                    $redirectUrl = Payment::generate_link($payer, $paymentInfo, $receiverInfo);

                    DB::commit();

                    return $this->successResponse('redirect_to_payment_gateway', [
                        'subscription_id' => $subscription->id,
                        'redirect_url' => $redirectUrl,
                    ]);
            }

            // This code should never be reached as all payment methods return early
            // این کد هرگز نباید اجرا شود چون تمام روش‌های پرداخت زودتر return می‌کنند
            DB::rollBack();
            return $this->errorResponse([
                ['code' => 'payment', 'message' => translate('messages.payment_failed')]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Subscription purchase failed: ' . $e->getMessage(), [
                'vendor_id' => $vendor->id,
                'error' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse([
                ['code' => 'subscription', 'message' => translate('messages.failed_to_purchase_subscription')]
            ], 500);
        }
    }

    /**
     * Get subscription history
     * دریافت تاریخچه اشتراک‌ها
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $subscriptions = $this->subscription->where('salon_id', $salon->id)
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->listResponse($subscriptions, 'messages.data_retrieved_successfully');
    }

    /**
     * Get subscription price based on type
     * دریافت قیمت اشتراک بر اساس نوع
     *
     * @param string $subscriptionType
     * @param int $durationDays
     * @param string|null $adPosition
     * @return float
     */
    private function getSubscriptionPrice(string $subscriptionType, int $durationDays, ?string $adPosition = null): float
    {
        switch ($subscriptionType) {
            case 'featured_listing':
            case 'boost_ads':
                $configKey = "beautybooking.subscription." . ($subscriptionType === 'featured_listing' ? 'featured' : 'boost') . ".{$durationDays}_days";
                return (float) config($configKey, 0);

            case 'banner_ads':
                if (!$adPosition) {
                    return 0.0;
                }
                $positionKey = match($adPosition) {
                    'homepage' => 'homepage',
                    'category_page' => 'category',
                    'search_results' => 'search_results',
                    default => null,
                };
                if (!$positionKey) {
                    return 0.0;
                }
                return (float) config("beautybooking.subscription.banner.{$positionKey}", 0);

            case 'dashboard_subscription':
                if ($durationDays == 30) {
                    return (float) config('beautybooking.subscription.dashboard.monthly', 0);
                } elseif ($durationDays == 365) {
                    return (float) config('beautybooking.subscription.dashboard.yearly', 0);
                } else {
                    return 0.0;
                }

            default:
                return 0.0;
        }
    }
}

