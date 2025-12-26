<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Loyalty Campaign Store Request
 * درخواست ایجاد کمپین وفاداری
 */
class BeautyLoyaltyCampaignStoreRequest extends FormRequest
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
            'salon_id' => 'nullable|integer|exists:beauty_salons,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|string|in:points,discount,cashback,gift_card,points_per_booking,points_per_amount,referral_bonus',
            'rules' => 'nullable|json',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'nullable|boolean',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'commission_type' => 'nullable|string|in:percentage,fixed',
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
            'salon_id.exists' => translate('messages.not_found'),
            'name.required' => translate('messages.required_field'),
            'name.max' => translate('messages.max_length_exceeded'),
            'description.max' => translate('messages.max_length_exceeded'),
            'type.required' => translate('messages.required_field'),
            'type.in' => translate('messages.invalid_campaign_type'),
            'rules.json' => translate('messages.invalid_json'),
            'start_date.required' => translate('messages.required_field'),
            'start_date.date' => translate('messages.invalid_date'),
            'start_date.after_or_equal' => translate('messages.date_must_be_future'),
            'end_date.date' => translate('messages.invalid_date'),
            'end_date.after' => translate('messages.end_date_after_start'),
            'commission_percentage.numeric' => translate('messages.invalid_amount'),
            'commission_type.in' => translate('messages.invalid_commission_type'),
        ];
    }
}

