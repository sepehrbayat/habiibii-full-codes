<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Traits\BeautyApiResponse;

/**
 * Beauty Calendar Controller (Vendor API)
 * کنترلر تقویم (API فروشنده)
 */
class BeautyCalendarController extends Controller
{
    use BeautyApiResponse;

    public function __construct(
        private BeautyCalendarService $calendarService
    ) {}

    /**
     * Get calendar availability
     * دریافت دسترسی‌پذیری تقویم
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailability(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'staff_id' => 'nullable|integer|exists:beauty_staff,id',
            'service_id' => 'nullable|integer|exists:beauty_services,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $durationMinutes = 60; // Default duration
        if ($request->service_id) {
            $service = \Modules\BeautyBooking\Entities\BeautyService::findOrFail($request->service_id);
            $durationMinutes = $service->duration_minutes;
        }

        $availableSlots = $this->calendarService->getAvailableTimeSlots(
            $salon->id,
            $request->staff_id,
            $request->date,
            $durationMinutes
        );

        return $this->successResponse('messages.data_retrieved_successfully', [
            'date' => $request->date,
            'available_slots' => $availableSlots,
            'duration_minutes' => $durationMinutes,
        ]);
    }

    /**
     * Create calendar block
     * ایجاد بلاک تقویم
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createBlock(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|in:break,holiday,manual_block',
            'reason' => 'nullable|string|max:500',
            'staff_id' => 'nullable|integer|exists:beauty_staff,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        try {
            $block = $this->calendarService->blockTimeSlot(
                $salon->id,
                $request->staff_id,
                $request->date,
                $request->start_time,
                $request->end_time,
                $request->type,
                $request->reason
            );

            return $this->successResponse('calendar_block_created_successfully', $block, 201);
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'calendar', 'message' => translate('failed_to_create_calendar_block')],
            ]);
        }
    }

    /**
     * Delete calendar block
     * حذف بلاک تقویم
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteBlock(int $id, Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $block = BeautyCalendarBlock::where('salon_id', $salon->id)->findOrFail($id);
        
        $this->calendarService->unblockTimeSlot($block->id);

        return $this->successResponse('calendar_block_deleted_successfully');
    }
}

