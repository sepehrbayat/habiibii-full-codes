<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Subscription Purchase Request
 * درخواست خرید اشتراک زیبایی
 */
class BeautySubscriptionPurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * تعیین اینکه آیا کاربر مجاز به انجام این درخواست است
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * دریافت قوانین اعتبارسنجی اعمال شده به درخواست
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'subscription_type' => 'required|in:featured_listing,boost_ads,banner_ads,dashboard_subscription',
            'duration_days' => 'required|integer|min:1',
            'payment_method' => 'required|in:digital_payment,wallet,cash_payment',
            'ad_position' => 'nullable|string|in:homepage,category_page,search_results',
            'banner_image' => 'nullable|string',
            'payment_gateway' => 'nullable|string',
            'payment_platform' => 'nullable|string|in:web,android,ios',
            'callback_url' => 'nullable|url',
        ];
    }

    /**
     * Get custom messages for validator errors.
     * دریافت پیام‌های سفارشی برای خطاهای اعتبارسنجی
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'subscription_type.required' => translate('messages.required_field'),
            'subscription_type.in' => translate('messages.invalid_subscription_type'),
            'duration_days.required' => translate('messages.required_field'),
            'duration_days.integer' => translate('messages.invalid_duration'),
            'duration_days.min' => translate('messages.invalid_duration'),
            'payment_method.required' => translate('messages.required_field'),
            'payment_method.in' => translate('messages.invalid_payment_method'),
            'ad_position.in' => translate('messages.invalid_ad_position'),
            'payment_platform.in' => translate('messages.invalid_platform'),
            'callback_url.url' => translate('messages.invalid_url'),
        ];
    }

    /**
     * Configure the validator instance.
     * پیکربندی نمونه اعتبارسنج
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation for banner ads
            // اعتبارسنجی اضافی برای تبلیغات بنر
            if ($this->subscription_type === 'banner_ads' && !$this->ad_position) {
                $validator->errors()->add('ad_position', translate('messages.ad_position_required_for_banner_ads'));
            }

            // Additional validation for dashboard subscription
            // اعتبارسنجی اضافی برای اشتراک داشبورد
            if ($this->subscription_type === 'dashboard_subscription' && !in_array($this->duration_days, [30, 365])) {
                $validator->errors()->add('duration_days', translate('messages.duration_must_be_30_or_365_days_for_dashboard'));
            }
        });
    }
}

