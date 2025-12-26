<?php

namespace Modules\Gateways\Http\Controllers;


use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Traits\Processor;
use Modules\Gateways\Entities\PaymentRequest;

class PaymobController extends Controller
{
    use Processor;

    private mixed $config_values;

    private PaymentRequest $payment;
    private User $user;
    private string $base_url;

    private array $supportedCountries = [
        'egypt' => 'https://accept.paymob.com',
        'PAK' => 'https://pakistan.paymob.com',
        'KSA' => 'https://ksa.paymob.com',
        'oman' => 'https://oman.paymob.com',
        'UAE' => 'https://uae.paymob.com',
    ];
    private string $defaultBaseUrl = 'https://accept.paymob.com';

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('paymob_accept', 'payment_config');
        $this->config_values = [];
        
        if (!is_null($config)) {
            $jsonString = null;
            if ($config->mode == 'live') {
                $jsonString = $config->live_values;
            } elseif ($config->mode == 'test') {
                $jsonString = $config->test_values;
            }
            
            // Validate and decode JSON with proper error handling
            // اعتبارسنجی و رمزگشایی JSON با مدیریت خطای مناسب
            if ($jsonString !== null) {
                $decoded = json_decode($jsonString, true);
                
                // Check if JSON decoding was successful
                // بررسی اینکه آیا رمزگشایی JSON موفق بوده است
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $this->config_values = $decoded;
                } else {
                    // Log JSON decode error for debugging (without exposing sensitive payment config data)
                    // ثبت خطای رمزگشایی JSON برای دیباگ (بدون افشای داده‌های حساس تنظیمات پرداخت)
                    Log::warning('PaymobController: Invalid JSON in payment config', [
                        'mode' => $config->mode,
                        'json_error' => json_last_error_msg(),
                        'json_length' => $jsonString !== null ? strlen($jsonString) : 0,
                        // Do not log json_string content as it may contain sensitive payment credentials
                        // لاگ کردن محتوای json_string به دلیل احتمال وجود اطلاعات حساس پرداخت
                    ]);
                    $this->config_values = [];
                }
            }
        }
        
        $this->payment = $payment;
        $this->user = $user;
        $country = $this->config_values['supported_country'] ?? null;
        if ($country && array_key_exists($country, $this->supportedCountries)) {
            $this->base_url = $this->supportedCountries[$country];
        } else {
            $this->base_url = $this->defaultBaseUrl;
        }
    }

    protected function cURL($url, $json)
    {
        $ch = curl_init($url);

        $headers = array();
        $headers[] = 'Content-Type: application/json';

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $output = curl_exec($ch);

        curl_close($ch);
        return json_decode($output);
    }

    public function credit(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $payment_data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($payment_data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        session()->put('payment_id', $payment_data->id);

        if ($payment_data['additional_data'] != null) {
            $business = json_decode($payment_data['additional_data']);
            $business_name = $business->business_name ?? "my_business";
        } else {
            $business_name = "my_business";
        }

        $payer = json_decode($payment_data['payer_information']);
        $url = $this->base_url . '/v1/intention/';
        $config = $this->config_values;
        $token = $config['secret_key']; //secret key

        // Data for the request
        $integration_id = (int)$config['integration_id'];
        $data = [
            'amount' => round($payment_data->payment_amount * 1000),
            'currency' => $payment_data->currency_code,
            'payment_methods' => [$integration_id], //integration id will be integer
            'items' => [
                [
                    'name' => 'payable amount',
                    'amount' => round($payment_data->payment_amount * 1000),
                    'description' => 'payable amount',
                    'quantity' => 1,
                ]
            ],
            'billing_data' => [
                "apartment" => "N/A",
                "email" => !empty($payer->email) ? $payer->email : 'test@gmail.com',
                "floor" => "N/A",
                "first_name" => !empty($payer->name) ? $payer->name : "rashed",
                "street" => "N/A",
                "building" => "N/A",
                "phone_number" => !empty($payer->phone) ? $payer->phone : "0182780000000",
                "shipping_method" => "PKG",
                "postal_code" => "N/A",
                "city" => "N/A",
                "country" => "N/A",
                "last_name" => !empty($payer->name) ? $payer->name : "rashed",
                "state" => "N/A",
            ],
            'special_reference' => time(),
            'customer' => [
                'first_name' => !empty($payer->name) ? $payer->name : "rashed",
                'last_name' => !empty($payer->name) ? $payer->name : "rashed",
                'email' => !empty($payer->email) ? $payer->email : 'test@gmail.com',
                'extras' => [
                    're' => '22',
                ],
            ],
            'extras' => [
                'ee' => 22,
            ],
            "redirection_url" => route('paymob.callback'),
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Token ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $result = json_decode($response, true);
        if (!isset($result['client_secret'])) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $secret_key = $result['client_secret'];
        curl_close($ch);
        $publicKey = $config['public_key'];
        $urlRedirect = $this->base_url . "/unifiedcheckout/?publicKey=$publicKey&clientSecret=$secret_key";
        return redirect()->to($urlRedirect);

    }

    public function createOrder($token, $payment_data, $business_name)
    {
        $items[] = [
            'name' => $business_name,
            'amount_cents' => round($payment_data->payment_amount * 1000),
            'description' => 'payment ID :' . $payment_data->id,
            'quantity' => 1
        ];

        $data = [
            "auth_token" => $token,
            "delivery_needed" => "false",
            "amount_cents" => round($payment_data->payment_amount * 1000),
            "currency" => $payment_data->currency_code,
            "items" => $items,

        ];

        return $this->cURL(
            $this->base_url . '/api/ecommerce/orders',
            $data
        );
    }

    public function getPaymentToken($order, $token, $payment_data, $payer)
    {
        $value = $payment_data->payment_amount;
        $billingData = [
            "apartment" => "N/A",
            "email" => !empty($payer->email) ? $payer->email : 'test@gmail.com',
            "floor" => "N/A",
            "first_name" => !empty($payer->name) ? $payer->name : "rashed",
            "street" => "N/A",
            "building" => "N/A",
            "phone_number" => !empty($payer->phone) ? $payer->phone : "0182780000000",
            "shipping_method" => "PKG",
            "postal_code" => "N/A",
            "city" => "N/A",
            "country" => "N/A",
            "last_name" => !empty($payer->name) ? $payer->name : "rashed",
            "state" => "N/A",
        ];

        $data = [
            "auth_token" => $token,
            "amount_cents" => round($value * 1000),
            "expiration" => 3600,
            "order_id" => $order->id,
            "billing_data" => $billingData,
            "currency" => $payment_data->currency_code,
            "integration_id" => is_array($this->config_values) ? $this->config_values['integration_id'] : $this->config_values->integration_id
        ];

        $response = $this->cURL(
            $this->base_url . '/api/acceptance/payment_keys',
            $data
        );

        return $response->token;
    }

    public function callback(Request $request)
    {
        $data = $request->all();
        ksort($data);
        // Extract HMAC from request data with null safety
        // استخراج HMAC از داده‌های درخواست با ایمنی null
        $hmac = $data['hmac'] ?? null;
        
        $array = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success',
        ];
        $connectedString = '';
        foreach ($data as $key => $element) {
            if (in_array($key, $array)) {
                $connectedString .= $element;
            }
        }
        // Extract HMAC secret from config with proper validation
        // استخراج کلید HMAC از تنظیمات با اعتبارسنجی مناسب
        $secret = null;
        if (is_object($this->config_values)) {
            $secret = $this->config_values->hmac ?? null;
        } elseif (is_array($this->config_values)) {
            $secret = $this->config_values['hmac'] ?? null;
        } else {
            // Explicitly set secret to null when config_values is invalid type
            // تنظیم صریح secret به null زمانی که config_values نوع نامعتبر دارد
            $secret = null;
            Log::error("PaymobController: config_values is neither an object nor an array!", [
                'config_values_type' => gettype($this->config_values),
                'payment_id' => session('payment_id')
            ]);
        }
        
        // Validate secret before HMAC calculation to prevent authentication bypass
        // اعتبارسنجی secret قبل از محاسبه HMAC برای جلوگیری از دور زدن احراز هویت
        if (empty($secret) || !is_string($secret)) {
            Log::error("PaymobController: HMAC secret is missing or invalid!", [
                'payment_id' => session('payment_id'),
                'config_values_type' => gettype($this->config_values)
            ]);
            // Return failure response when secret is invalid
            // برگرداندن پاسخ خطا زمانی که secret نامعتبر است
            $payment_data = $this->payment::where(['id' => session('payment_id')])->first();
            
            // Check if payment_data exists before calling payment_response
            // بررسی وجود payment_data قبل از فراخوانی payment_response
            if ($payment_data === null) {
                Log::error("PaymobController: Payment data not found for payment_id", [
                    'payment_id' => session('payment_id')
                ]);
                // Return error response when payment data is not found
                // برگرداندن پاسخ خطا زمانی که داده پرداخت یافت نشد
                return redirect()->route('payment-fail', ['token' => base64_encode('payment_method=paymob_accept&&attribute_id=&&transaction_reference=')]);
            }
            
            // $payment_data is guaranteed to be non-null here (early return above handles null case)
            // $payment_data در اینجا قطعاً null نیست (return زودرس بالا مورد null را مدیریت می‌کند)
            if (function_exists($payment_data->failure_hook)) {
                call_user_func($payment_data->failure_hook, $payment_data);
            }
            return $this->payment_response($payment_data, 'fail');
        }
        
        // Validate HMAC is present before verification
        // اعتبارسنجی وجود HMAC قبل از تأیید
        if (empty($hmac)) {
            Log::error("PaymobController: HMAC is missing from callback data!", [
                'payment_id' => session('payment_id'),
                'data_keys' => array_keys($data)
            ]);
            $payment_data = $this->payment::where(['id' => session('payment_id')])->first();
            if ($payment_data && function_exists($payment_data->failure_hook)) {
                call_user_func($payment_data->failure_hook, $payment_data);
            }
            if ($payment_data === null) {
                return redirect()->route('payment-fail', ['token' => base64_encode('payment_method=paymob_accept&&attribute_id=&&transaction_reference=')]);
            }
            return $this->payment_response($payment_data, 'fail');
        }
        
        $hased = hash_hmac('sha512', $connectedString, $secret);
        // Use strict comparison (===) for cryptographic verification to prevent type juggling attacks
        // استفاده از مقایسه دقیق (===) برای تأیید رمزنگاری برای جلوگیری از حملات type juggling
        if ($hased === $hmac && $data['success'] === "true") {

            $this->payment::where(['id' => session('payment_id')])->update([
                'payment_method' => 'paymob_accept',
                'is_paid' => 1,
                'transaction_id' => session('payment_id'),
            ]);

            $payment_data = $this->payment::where(['id' => session('payment_id')])->first();

            // Check if payment_data exists before calling payment_response
            // بررسی وجود payment_data قبل از فراخوانی payment_response
            if ($payment_data === null) {
                Log::error("PaymobController: Payment data not found for payment_id in success case", [
                    'payment_id' => session('payment_id')
                ]);
                // Return error response when payment data is not found
                // برگرداندن پاسخ خطا زمانی که داده پرداخت یافت نشد
                return redirect()->route('payment-fail', ['token' => base64_encode('payment_method=paymob_accept&&attribute_id=&&transaction_reference=')]);
            }

            // At this point, $payment_data is guaranteed to be non-null due to early return above
            // در این نقطه، $payment_data به دلیل return زودهنگام بالا، قطعاً non-null است
            if (function_exists($payment_data->success_hook)) {
                call_user_func($payment_data->success_hook, $payment_data);
            }
            return $this->payment_response($payment_data,'success');
        }
        $payment_data = $this->payment::where(['id' => session('payment_id')])->first();
        
        // Check if payment_data exists before calling payment_response
        // بررسی وجود payment_data قبل از فراخوانی payment_response
        if ($payment_data === null) {
            Log::error("PaymobController: Payment data not found for payment_id in final fail case", [
                'payment_id' => session('payment_id')
            ]);
            // Return error response when payment data is not found
            // برگرداندن پاسخ خطا زمانی که داده پرداخت یافت نشد
            return redirect()->route('payment-fail', ['token' => base64_encode('payment_method=paymob_accept&&attribute_id=&&transaction_reference=')]);
        }
        
        // At this point, $payment_data is guaranteed to be non-null due to early return above
        // در این نقطه، $payment_data به دلیل return زودهنگام بالا، قطعاً non-null است
        if (function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data,'fail');
    }
}
