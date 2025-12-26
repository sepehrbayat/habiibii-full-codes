<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Retail Product Store Request
 * درخواست ایجاد محصول خرده‌فروشی
 */
class BeautyRetailProductStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'category' => 'nullable|string|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
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
            'name.required' => translate('messages.required_field'),
            'name.max' => translate('messages.max_length_exceeded'),
            'description.max' => translate('messages.max_length_exceeded'),
            'price.required' => translate('messages.required_field'),
            'price.numeric' => translate('messages.invalid_amount'),
            'image.image' => translate('messages.invalid_image'),
            'image.mimes' => translate('messages.invalid_image_format'),
            'image.max' => translate('messages.image_size_exceeded'),
            'category.max' => translate('messages.max_length_exceeded'),
            'stock_quantity.required' => translate('messages.required_field'),
            'stock_quantity.integer' => translate('messages.invalid_quantity'),
            'stock_quantity.min' => translate('messages.min_quantity'),
        ];
    }
}

