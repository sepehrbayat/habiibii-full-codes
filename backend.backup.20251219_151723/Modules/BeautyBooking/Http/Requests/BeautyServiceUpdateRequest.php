<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Service Update Request
 * درخواست به‌روزرسانی خدمت
 */
class BeautyServiceUpdateRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:beauty_service_categories,id',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'boolean',
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
            'name.required' => translate('messages.required_field'),
            'category_id.required' => translate('messages.required_field'),
            'category_id.exists' => translate('messages.not_found'),
            'duration_minutes.required' => translate('messages.required_field'),
            'duration_minutes.min' => translate('messages.invalid_duration'),
            'price.required' => translate('messages.required_field'),
            'price.min' => translate('messages.invalid_price'),
            'image.image' => translate('messages.invalid_image'),
            'image.max' => translate('messages.image_size_exceeded'),
        ];
    }
}

