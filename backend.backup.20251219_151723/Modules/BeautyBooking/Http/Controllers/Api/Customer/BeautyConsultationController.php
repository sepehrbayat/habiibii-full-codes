<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Services\BeautyBookingService;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Traits\BeautyApiResponse;

/**
 * Beauty Consultation Controller (Customer API)
 * کنترلر مشاوره زیبایی (API مشتری)
 */
class BeautyConsultationController extends Controller
{
    use BeautyApiResponse;

    public function __construct(
        private BeautyService $service,
        private BeautyBookingService $bookingService,
        private BeautyCalendarService $calendarService
    ) {}

    /**
     * Get available consultations for a salon
     * دریافت مشاوره‌های موجود برای یک سالن
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @queryParam salon_id integer required Salon ID. Example: 1
     * @queryParam consultation_type string optional Filter by type (pre_consultation/post_consultation). Example: pre_consultation
     * @queryParam limit integer optional Number of items per page (default: 25). Example: 25
     * @queryParam offset integer optional Offset for pagination (default: 0). Example: 0
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Pre-Consultation",
     *       "price": 50000,
     *       "duration_minutes": 30
     *     }
     *   ],
     *   "total": 5
     * }
     */
    public function list(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'salon_id' => 'required|integer|exists:beauty_salons,id',
            'consultation_type' => 'nullable|in:pre_consultation,post_consultation',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $limit = $request->get('limit', 25);
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $query = $this->service->where('salon_id', $request->salon_id)
            ->where('status', 1)
            ->consultations()
            ->with('category');

        if ($request->filled('consultation_type')) {
            if ($request->consultation_type === 'pre_consultation') {
                $query->preConsultations();
            } else {
                $query->postConsultations();
            }
        }

        $consultations = $query->paginate($limit, ['*'], 'page', $page);

        $formatted = $consultations->getCollection()->map(function ($consultation) {
            return [
                'id' => $consultation->id,
                'name' => $consultation->name,
                'description' => $consultation->description,
                'duration_minutes' => $consultation->duration_minutes,
                'price' => $consultation->price,
                'image' => $consultation->image ? asset('storage/' . $consultation->image) : null,
                'service_type' => $consultation->service_type,
                'consultation_credit_percentage' => $consultation->consultation_credit_percentage,
                'category' => $consultation->category ? [
                    'id' => $consultation->category->id,
                    'name' => $consultation->category->name,
                ] : null,
            ];
        });

        // Format response using standardized listResponse method
        // فرمت پاسخ با استفاده از متد استاندارد listResponse
        $consultations->setCollection($formatted->values());
        return $this->listResponse($consultations);
    }

    /**
     * Book a consultation
     * رزرو مشاوره
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @bodyParam salon_id integer required Salon ID. Example: 1
     * @bodyParam consultation_id integer required Consultation service ID. Example: 1
     * @bodyParam booking_date date required Booking date (YYYY-MM-DD, must be today or future). Example: 2024-01-20
     * @bodyParam booking_time time required Booking time (H:i format). Example: 10:00
     * @bodyParam staff_id integer optional Staff member ID. Example: 1
     * @bodyParam payment_method string required Payment method (online/wallet/cash_payment). Example: wallet
     * @bodyParam main_service_id integer optional Main service ID for credit application. Example: 2
     * 
     * @response 201 {
     *   "message": "Consultation booked successfully",
     *   "data": {
     *     "id": 100001,
     *     "booking_reference": "BB-100001",
     *     "status": "pending"
     *   }
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "validation",
     *       "message": "The booking_date must be a date after or equal to today."
     *     }
     *   ]
     * }
     */
    public function book(Request $request): JsonResponse
    {
        // Convert 'online' to 'digital_payment' for backward compatibility
        // تبدیل 'online' به 'digital_payment' برای سازگاری با نسخه‌های قبلی
        if ($request->payment_method === 'online') {
            $request->merge(['payment_method' => 'digital_payment']);
        }
        
        $validator = Validator::make($request->all(), [
            'salon_id' => 'required|integer|exists:beauty_salons,id',
            'consultation_id' => 'required|integer|exists:beauty_services,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'staff_id' => 'nullable|integer|exists:beauty_staff,id',
            'payment_method' => 'required|in:digital_payment,wallet,cash_payment',
            'main_service_id' => 'nullable|integer|exists:beauty_services,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        // Verify consultation service
        // تأیید خدمت مشاوره
        $consultation = $this->service->findOrFail($request->consultation_id);
        
        if (!$consultation->isConsultation()) {
            return $this->errorResponse([
                ['code' => 'validation', 'message' => translate('messages.service_is_not_consultation')],
            ]);
        }

        if ($consultation->salon_id != $request->salon_id) {
            return $this->errorResponse([
                ['code' => 'validation', 'message' => translate('messages.consultation_not_belongs_to_salon')],
            ]);
        }

        try {
            // Payment method already converted before validation
            // روش پرداخت قبلاً قبل از اعتبارسنجی تبدیل شده است
            $paymentMethod = $request->payment_method;

            $bookingData = [
                'service_id' => $request->consultation_id,
                'booking_date' => $request->booking_date,
                'booking_time' => $request->booking_time,
                'staff_id' => $request->staff_id,
                'payment_method' => $paymentMethod,
                'notes' => $request->notes ?? null,
            ];

            // If main_service_id is provided, this consultation can be credited to main service
            // اگر main_service_id ارائه شده باشد، این مشاوره می‌تواند به خدمت اصلی اعتبار شود
            if ($request->filled('main_service_id')) {
                $mainService = $this->service->findOrFail($request->main_service_id);
                
                if ($mainService->salon_id != $request->salon_id) {
                    return $this->errorResponse([
                        ['code' => 'validation', 'message' => translate('messages.main_service_not_belongs_to_salon')],
                    ]);
                }

                $bookingData['main_service_id'] = $request->main_service_id;
                $bookingData['consultation_credit_percentage'] = $consultation->consultation_credit_percentage;
            }

            $booking = $this->bookingService->createBooking(
                $request->user()->id,
                $request->salon_id,
                $bookingData
            );

            return $this->successResponse('messages.consultation_booked_successfully', [
                'id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'status' => $booking->status,
                'booking_date' => $booking->booking_date ? $booking->booking_date->format('Y-m-d') : null,
                'booking_time' => $booking->booking_time,
                'consultation' => [
                    'id' => $consultation->id,
                    'name' => $consultation->name,
                    'price' => $consultation->price,
                ],
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Consultation booking failed', [
                'consultation_id' => $request->consultation_id ?? null,
                'salon_id' => $request->salon_id ?? null,
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse([
                ['code' => 'booking', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Get consultation availability
     * دریافت دسترسی‌پذیری مشاوره
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @bodyParam salon_id integer required Salon ID. Example: 1
     * @bodyParam consultation_id integer required Consultation service ID. Example: 1
     * @bodyParam date date required Date to check (YYYY-MM-DD, must be today or future). Example: 2024-01-20
     * @bodyParam staff_id integer optional Staff member ID. Example: 1
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "date": "2024-01-20",
     *     "available_slots": ["09:00", "10:00", "11:00", "14:00", "15:00"],
     *     "consultation_duration_minutes": 30
     *   }
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "validation",
     *       "message": "The salon_id field is required."
     *     }
     *   ]
     * }
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'salon_id' => 'required|integer|exists:beauty_salons,id',
            'consultation_id' => 'required|integer|exists:beauty_services,id',
            'date' => 'required|date|after_or_equal:today',
            'staff_id' => 'nullable|integer|exists:beauty_staff,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $consultation = $this->service->findOrFail($request->consultation_id);
        
        if (!$consultation->isConsultation()) {
            return $this->errorResponse([
                ['code' => 'validation', 'message' => translate('messages.service_is_not_consultation')],
            ]);
        }

        $availableSlots = $this->calendarService->getAvailableTimeSlots(
            $request->salon_id,
            $request->staff_id,
            $request->date,
            $consultation->duration_minutes
        );

        return $this->successResponse('messages.data_retrieved_successfully', [
            'date' => $request->date,
            'available_slots' => $availableSlots,
            'consultation_duration_minutes' => $consultation->duration_minutes,
        ]);
    }
}

