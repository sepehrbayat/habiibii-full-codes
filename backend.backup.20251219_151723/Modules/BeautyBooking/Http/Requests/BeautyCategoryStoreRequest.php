<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Category Store Request
 * درخواست ایجاد دسته‌بندی
 */
class BeautyCategoryStoreRequest extends FormRequest
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
            'parent_id' => 'nullable|integer|exists:beauty_service_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
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
            'name.max' => translate('messages.max_length_exceeded'),
            'parent_id.exists' => translate('messages.not_found'),
            'image.image' => translate('messages.invalid_image'),
            'image.mimes' => translate('messages.invalid_image_format'),
            'image.max' => translate('messages.image_size_exceeded'),
            'sort_order.integer' => translate('messages.invalid_sort_order'),
        ];
    }
}

