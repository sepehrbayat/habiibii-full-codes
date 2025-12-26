<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Traits;

use App\CentralLogics\Helpers;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

/**
 * API response helper trait
 * تریت کمکی برای پاسخ‌های API
 */
trait BeautyApiResponse
{
    /**
     * Build a standard success response
     * ساخت پاسخ موفق استاندارد
     *
     * @param string $message Translation key or plain text message
     * @param mixed $data Payload to return
     * @param int $status HTTP status code
     * @return JsonResponse
     */
    protected function successResponse(string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => translate($message),
            'data' => $data,
        ], $status);
    }

    /**
     * Build a paginated list response
     * ساخت پاسخ لیست صفحه‌بندی‌شده
     *
     * @param LengthAwarePaginator $paginator Data paginator
     * @param string $message Response message translation key
     * @return JsonResponse
     */
    protected function listResponse(LengthAwarePaginator $paginator, string $message = 'messages.data_retrieved_successfully'): JsonResponse
    {
        return response()->json([
            'message' => translate($message),
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ], 200);
    }

    /**
     * Build a simple list response for non-paginated collections
     * ساخت پاسخ لیست ساده برای مجموعه‌های بدون صفحه‌بندی
     *
     * @param iterable $items Collection items
     * @param string $message Response message translation key
     * @param array $meta Additional metadata to merge
     * @return JsonResponse
     */
    protected function simpleListResponse(iterable $items, string $message = 'messages.data_retrieved_successfully', array $meta = []): JsonResponse
    {
        return response()->json(array_merge([
            'message' => translate($message),
            'data' => $items,
        ], $meta), 200);
    }

    /**
     * Build an error response
     * ساخت پاسخ خطا
     *
     * @param array $errors Error array
     * @param int $status HTTP status code
     * @return JsonResponse
     */
    protected function errorResponse(array $errors, int $status = 403): JsonResponse
    {
        return response()->json([
            'errors' => $errors,
        ], $status);
    }

    /**
     * Build a validation error response using Helpers::error_processor
     * ساخت پاسخ خطای اعتبارسنجی با استفاده از Helpers::error_processor
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator Validator instance
     * @return JsonResponse
     */
    protected function validationErrorResponse($validator): JsonResponse
    {
        return $this->errorResponse(Helpers::error_processor($validator), 403);
    }
}

