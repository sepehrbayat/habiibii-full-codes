<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Booking Store Request
 * درخواست ایجاد رزرو
 */
class BeautyBookingStoreRequest extends FormRequest
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
     * Prepare the data for validation.
     * آماده‌سازی داده‌ها برای اعتبارسنجی
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Convert 'online' to 'digital_payment' for backward compatibility
        // تبدیل 'online' به 'digital_payment' برای سازگاری با نسخه‌های قبلی
        if ($this->has('payment_method') && $this->payment_method === 'online') {
            $this->merge(['payment_method' => 'digital_payment']);
        }
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
            'service_id' => 'required|integer|exists:beauty_services,id',
            'staff_id' => 'nullable|integer|exists:beauty_staff,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'payment_method' => 'required|in:wallet,digital_payment,cash_payment',
            'payment_gateway' => 'nullable|string|in:stripe,paypal,razorpay',
            'callback_url' => 'nullable|url',
            'payment_platform' => 'nullable|string|in:web,mobile',
            'notes' => 'nullable|string|max:500',
            'additional_services' => 'nullable|array',
            'additional_services.*.service_id' => 'required_with:additional_services|integer|exists:beauty_services,id',
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
            'service_id.required' => translate('messages.required_field'),
            'service_id.exists' => translate('messages.not_found'),
            'booking_date.required' => translate('messages.required_field'),
            'booking_date.after_or_equal' => translate('messages.date_must_be_future'),
            'booking_time.required' => translate('messages.required_field'),
            'booking_time.date_format' => translate('messages.invalid_time'),
            'payment_method.required' => translate('messages.required_field'),
            'payment_method.in' => translate('messages.invalid_payment_method'),
            'payment_gateway.in' => translate('messages.invalid_payment_gateway'),
            'callback_url.url' => translate('messages.invalid_url'),
            'payment_platform.in' => translate('messages.invalid_payment_platform'),
        ];
    }
}

