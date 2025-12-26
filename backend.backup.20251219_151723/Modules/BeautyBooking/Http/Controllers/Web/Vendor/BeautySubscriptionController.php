<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautySubscription;
use Modules\BeautyBooking\Http\Requests\BeautySubscriptionStoreRequest;
use Modules\BeautyBooking\Services\BeautyRevenueService;
use App\Traits\Payment;
use App\Library\Payer;
use App\Library\Payment as PaymentInfo;
use App\Library\Receiver;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use Carbon\Carbon;

/**
 * Beauty Subscription Controller (Vendor)
 * کنترلر اشتراک (فروشنده)
 */
class BeautySubscriptionController extends Controller
{
    use Payment;

    public function __construct(
        private BeautySubscription $subscription,
        private BeautyRevenueService $revenueService
    ) {}

    /**
     * Index - Show available subscription plans
     * نمایش پلان‌های اشتراک موجود
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

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

        return view('beautybooking::vendor.subscription.index', compact('salon', 'plans', 'activeSubscriptions'));
    }

    /**
     * Show subscription plan details
     * نمایش جزئیات پلان اشتراک
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function planDetails(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $subscription = $this->subscription->where('salon_id', $salon->id)
            ->with(['salon'])
            ->findOrFail($id);

        return view('beautybooking::vendor.subscription.plan-details', compact('subscription', 'salon'));
    }

    /**
     * Purchase subscription
     * خرید اشتراک
     *
     * @param BeautySubscriptionStoreRequest $request
     * @param string $planId
     * @return RedirectResponse
     */
    public function purchase(BeautySubscriptionStoreRequest $request, string $planId): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        try {
            DB::beginTransaction();

            // Get plan price based on subscription type
            // دریافت قیمت پلان بر اساس نوع اشتراک
            $planPrice = $this->getSubscriptionPrice($request->subscription_type, $request->duration_days, $request->ad_position);

            if ($planPrice <= 0) {
                DB::rollBack();
                Toastr::error(translate('messages.invalid_subscription_plan'));
                return back();
            }

            // Process payment based on payment method
            // پردازش پرداخت بر اساس روش پرداخت
            $paymentResult = $this->processPayment($request, $salon, $planPrice);
            
            if (!$paymentResult['success']) {
                DB::rollBack();
                if (isset($paymentResult['message'])) {
                    Toastr::error($paymentResult['message']);
                }
                if (isset($paymentResult['redirect'])) {
                    return $paymentResult['redirect'];
                }
                return back();
            }

            // If digital payment, subscription was already created with pending status
            // اگر پرداخت دیجیتال باشد، اشتراک قبلاً با وضعیت pending ایجاد شده است
            if ($request->payment_method === 'digital_payment' && isset($paymentResult['subscription'])) {
                DB::commit();
                // Ensure redirect exists before returning
                // اطمینان از وجود redirect قبل از بازگشت
                if (isset($paymentResult['redirect'])) {
                    return $paymentResult['redirect'];
                }
                // Fallback: redirect to subscription index if redirect is missing
                // جایگزین: هدایت به صفحه اشتراک در صورت نبود redirect
                Toastr::warning(translate('messages.payment_redirect_failed'));
                return redirect()->route('beautybooking.vendor.subscription.index');
            }

            // Create subscription for cash/wallet payments
            // ایجاد اشتراک برای پرداخت‌های نقدی/کیف پول
            $subscription = $this->createSubscription($request, $salon, $planPrice);

            // Record revenue
            // ثبت درآمد
            $this->revenueService->recordSubscription($subscription);

            // Update salon featured status
            // به‌روزرسانی وضعیت Featured سالن
            if ($request->subscription_type === 'featured') {
                $salon->update(['is_featured' => true]);
            }

            DB::commit();
            Toastr::success(translate('messages.subscription_purchased_successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Subscription purchase failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_purchase_subscription'));
        }

        return back();
    }
    
    /**
     * Process payment for subscription
     * پردازش پرداخت برای اشتراک
     *
     * @param BeautySubscriptionStoreRequest $request
     * @param BeautySalon $salon
     * @param float $planPrice
     * @return array
     */
    private function processPayment(BeautySubscriptionStoreRequest $request, BeautySalon $salon, float $planPrice): array
    {
            switch ($request->payment_method) {
                case 'cash_payment':
                return ['success' => true];
                    
                case 'digital_payment':
                return $this->processDigitalPayment($request, $salon, $planPrice);
                
            case 'wallet':
                return $this->processWalletPayment($request, $salon, $planPrice);
                
            default:
                return [
                    'success' => false,
                    'message' => translate('messages.invalid_payment_method')
                ];
        }
    }
    
    /**
     * Process digital payment
     * پردازش پرداخت دیجیتال
     *
     * @param BeautySubscriptionStoreRequest $request
     * @param BeautySalon $salon
     * @param float $planPrice
     * @return array
     */
    private function processDigitalPayment(BeautySubscriptionStoreRequest $request, BeautySalon $salon, float $planPrice): array
    {
        // Validate vendor/store information BEFORE creating subscription
        // اعتبارسنجی اطلاعات فروشنده/فروشگاه قبل از ایجاد اشتراک
        // This prevents orphaned subscription records if validation fails
        // این از ایجاد رکوردهای اشتراک یتیم در صورت شکست اعتبارسنجی جلوگیری می‌کند
        $store = $salon->store;
        $vendor = $store?->vendor ?? null;

        if (!$store || !$vendor) {
            return [
                'success' => false,
                'message' => translate('messages.vendor_not_found')
            ];
        }
        
        // Create subscription with pending status AFTER validation
        // ایجاد اشتراک با وضعیت pending بعد از اعتبارسنجی
                    $startDate = now();
                    $endDate = $startDate->copy()->addDays($request->duration_days);
        $bannerImagePath = $this->handleBannerImageUpload($request);
                    
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

                    // Prepare payment information
                    // آماده‌سازی اطلاعات پرداخت
                    $payer = new Payer(
                        $store->name ?? ($vendor?->f_name . ' ' . $vendor?->l_name) ?? '',
                        $store->email ?? $vendor?->email ?? '',
                        $store->phone ?? $vendor?->phone ?? '',
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
                        payer_id: $vendor->id,
            receiver_id: 1,
                        additional_data: $additionalData,
                        payment_amount: $planPrice,
                        external_redirect_link: route('beautybooking.vendor.subscription.index'),
                        attribute: 'beauty_subscription',
                        attribute_id: $subscription->id,
                    );

                    $receiverInfo = new Receiver('Admin', 'example.png');
                    $redirectLink = Payment::generate_link($payer, $paymentInfo, $receiverInfo);

        return [
            'success' => true,
            'subscription' => $subscription,
            'redirect' => redirect($redirectLink)
        ];
    }
    
    /**
     * Process wallet payment
     * پردازش پرداخت از کیف پول
     *
     * @param BeautySubscriptionStoreRequest $request
     * @param BeautySalon $salon
     * @param float $planPrice
     * @return array
     */
    private function processWalletPayment(BeautySubscriptionStoreRequest $request, BeautySalon $salon, float $planPrice): array
    {
                    $store = $salon->store;
                    if (!$store) {
            return [
                'success' => false,
                'message' => translate('messages.store_not_found')
            ];
                    }
                    
                    // Get or create vendor wallet
                    // دریافت یا ایجاد کیف پول فروشنده
                    $wallet = \App\Models\StoreWallet::firstOrNew(['vendor_id' => $store->vendor_id]);
                    $balance = \App\Models\BusinessSetting::where('key', 'wallet_status')->first()?->value == 1 
                        ? $wallet->balance ?? 0 
                        : 0;
                    
                    if ($balance < $planPrice) {
            return [
                'success' => false,
                'message' => translate('messages.insufficient_wallet_balance')
            ];
                    }
                    
                    // Deduct from wallet
                    // کسر از کیف پول
                    $wallet->total_withdrawn = ($wallet->total_withdrawn ?? 0) + $planPrice;
                    $wallet->save();
                    
        return ['success' => true];
    }
    
    /**
     * Create subscription
     * ایجاد اشتراک
     *
     * @param BeautySubscriptionStoreRequest $request
     * @param BeautySalon $salon
     * @param float $planPrice
     * @return BeautySubscription
     */
    private function createSubscription(BeautySubscriptionStoreRequest $request, BeautySalon $salon, float $planPrice): BeautySubscription
    {
            $startDate = now();
            $endDate = $startDate->copy()->addDays($request->duration_days);
        $bannerImagePath = $this->handleBannerImageUpload($request);

        return $this->subscription->create([
                'salon_id' => $salon->id,
                'subscription_type' => $request->subscription_type,
                'ad_position' => $request->ad_position ?? null,
                'banner_image' => $bannerImagePath,
                'duration_days' => $request->duration_days,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'amount_paid' => $planPrice,
            'payment_method' => $request->payment_method,
                'status' => 'active',
            ]);
    }
    
    /**
     * Handle banner image upload
     * مدیریت آپلود تصویر بنر
     *
     * @param BeautySubscriptionStoreRequest $request
     * @return string|null
     */
    private function handleBannerImageUpload(BeautySubscriptionStoreRequest $request): ?string
    {
        if ($request->subscription_type === 'banner_ads' && $request->hasFile('banner_image')) {
            return Helpers::upload('beauty/banners/', 'png', $request->file('banner_image'));
        }
        return null;
    }

    /**
     * Subscription history
     * تاریخچه اشتراک‌ها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function history(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $subscriptions = $this->subscription->where('salon_id', $salon->id)
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::vendor.subscription.history', compact('subscriptions', 'salon'));
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
                // Use duration-based keys: 7_days, 30_days
                // استفاده از کلیدهای مبتنی بر مدت: 7_days، 30_days
                $configKey = "beautybooking.subscription." . ($subscriptionType === 'featured_listing' ? 'featured' : 'boost') . ".{$durationDays}_days";
                return (float) config($configKey, 0);
                
            case 'banner_ads':
                // Use ad_position-based keys: homepage, category, search_results
                // استفاده از کلیدهای مبتنی بر موقعیت: homepage، category، search_results
                if (!$adPosition) {
                    return 0.0;
                }
                // Map ad_position to config key
                // نگاشت ad_position به کلید config
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
                // Use duration-based keys: monthly (30 days), yearly (365 days)
                // استفاده از کلیدهای مبتنی بر مدت: monthly (30 روز)، yearly (365 روز)
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
    
    /**
     * Get vendor's salon with authorization check
     * دریافت سالن فروشنده با بررسی مجوز
     *
     * @param object $vendor
     * @return BeautySalon
     */
    private function getVendorSalon(object $vendor): BeautySalon
    {
        $salon = BeautySalon::where('store_id', $vendor->store_id)->first();
        
        if (!$salon) {
            abort(403, translate('messages.salon_not_found'));
        }
        
        // Authorization check: Ensure salon belongs to vendor
        // بررسی مجوز: اطمینان از اینکه سالن متعلق به فروشنده است
        if ($salon->store->vendor_id !== $vendor->id) {
            abort(403, translate('messages.unauthorized_access'));
        }
        
        return $salon;
    }
}

