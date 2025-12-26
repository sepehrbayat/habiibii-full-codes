<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Review Store Request
 * درخواست ثبت نظر
 */
class BeautyReviewStoreRequest extends FormRequest
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
            'booking_id' => 'required|integer|exists:beauty_bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'image|mimes:jpeg,png,jpg|max:2048',
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
            'booking_id.required' => translate('messages.required_field'),
            'booking_id.exists' => translate('messages.not_found'),
            'rating.required' => translate('messages.required_field'),
            'rating.min' => translate('messages.invalid_rating'),
            'rating.max' => translate('messages.invalid_rating'),
            'attachments.*.image' => translate('messages.invalid_image'),
            'attachments.*.max' => translate('messages.image_size_exceeded'),
        ];
    }
}

