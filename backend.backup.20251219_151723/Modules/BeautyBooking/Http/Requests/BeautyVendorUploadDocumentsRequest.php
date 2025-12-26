<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Vendor Upload Documents Request
 * درخواست آپلود مدارک فروشنده زیبایی
 */
class BeautyVendorUploadDocumentsRequest extends FormRequest
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
            'documents' => 'required|array|min:1|max:10',
            'documents.*' => 'required|file|mimes:pdf,jpeg,png,jpg|max:5120', // 5MB max
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
            'documents.required' => translate('messages.required_field'),
            'documents.array' => translate('messages.invalid_json'),
            'documents.min' => translate('messages.min_quantity'),
            'documents.max' => translate('messages.max_length_exceeded'),
            'documents.*.required' => translate('messages.required_field'),
            'documents.*.file' => translate('messages.invalid_file'),
            'documents.*.mimes' => translate('messages.invalid_image_format'),
            'documents.*.max' => translate('messages.image_size_exceeded'),
        ];
    }
}

