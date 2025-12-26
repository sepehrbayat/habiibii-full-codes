<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Subscription Store Request
 * درخواست ایجاد اشتراک
 */
class BeautySubscriptionStoreRequest extends FormRequest
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
            'salon_id' => 'required|integer|exists:beauty_salons,id',
            'subscription_type' => 'required|string|in:featured_listing,boost_ads,banner_ads,dashboard_subscription',
            'duration_days' => 'required|integer|min:1',
            'payment_method' => 'required|string|in:wallet,digital_payment,cash_payment',
            'ad_position' => 'nullable|string|in:homepage,category_page,search_results|required_if:subscription_type,banner_ads',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
            'salon_id.required' => translate('messages.required_field'),
            'salon_id.exists' => translate('messages.not_found'),
            'subscription_type.required' => translate('messages.required_field'),
            'subscription_type.in' => translate('messages.invalid_subscription_type'),
            'duration_days.required' => translate('messages.required_field'),
            'duration_days.integer' => translate('messages.invalid_duration'),
            'duration_days.min' => translate('messages.min_duration_days'),
            'ad_position.required_if' => translate('messages.ad_position_required_for_banner_ads'),
            'ad_position.in' => translate('messages.invalid_ad_position'),
            'banner_image.image' => translate('messages.invalid_image'),
            'banner_image.mimes' => translate('messages.invalid_image_format'),
            'banner_image.max' => translate('messages.image_size_exceeded'),
            'payment_method.required' => translate('messages.required_field'),
            'payment_method.in' => translate('messages.invalid_payment_method'),
        ];
    }
}

