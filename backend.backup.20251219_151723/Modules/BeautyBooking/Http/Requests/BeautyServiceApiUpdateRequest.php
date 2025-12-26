<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Service API Update Request
 * درخواست به‌روزرسانی خدمت زیبایی (API)
 */
class BeautyServiceApiUpdateRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|integer|exists:beauty_service_categories,id',
            'description' => 'nullable|string',
            'duration_minutes' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'nullable|boolean',
            'staff_ids' => 'nullable|array',
            'staff_ids.*' => 'integer|exists:beauty_staff,id',
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
            'name.max' => translate('messages.max_length_exceeded'),
            'category_id.exists' => translate('messages.not_found'),
            'duration_minutes.integer' => translate('messages.invalid_duration'),
            'duration_minutes.min' => translate('messages.invalid_duration'),
            'price.numeric' => translate('messages.invalid_price'),
            'price.min' => translate('messages.invalid_price'),
            'image.image' => translate('messages.invalid_image'),
            'image.mimes' => translate('messages.invalid_image_format'),
            'image.max' => translate('messages.image_size_exceeded'),
            'status.boolean' => translate('messages.invalid_status'),
            'staff_ids.array' => translate('messages.invalid_json'),
            'staff_ids.*.exists' => translate('messages.not_found'),
        ];
    }
}

