<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Http\Requests\BeautyBookingStoreRequest;
use Modules\BeautyBooking\Services\BeautyBookingService;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Services\BeautyCrossSellingService;
use Modules\BeautyBooking\Traits\BeautyPushNotification;
use Modules\BeautyBooking\Traits\BeautyApiResponse;

/**
 * Beauty Booking Controller (Customer API)
 * کنترلر رزرو (API مشتری)
 */
class BeautyBookingController extends Controller
{
    use BeautyPushNotification, BeautyApiResponse;

    public function __construct(
        private BeautyBooking $booking,
        private BeautyBookingService $bookingService,
        private BeautyCalendarService $calendarService,
        private BeautyCrossSellingService $crossSellingService
    ) {}

    /**
     * Get service suggestions for cross-selling/upsell
     * دریافت پیشنهادات خدمت برای فروش متقابل/افزایش فروش
     *
     * @param Request $request
     * @param int $id Service ID
     * @return JsonResponse
     */
    public function getServiceSuggestions(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'salon_id' => 'nullable|integer|exists:beauty_salons,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            $service = BeautyService::findOrFail($id);
            $salonIdInput = $request->salon_id ?? $service->salon_id;
            $salonId = is_numeric($salonIdInput) ? (int) $salonIdInput : null;
            $userId = $request->user() ? $request->user()->id : null;

            $suggestions = $this->crossSellingService->getSuggestedServices(
                $id,
                $userId,
                $salonId
            );

            return $this->simpleListResponse(
                $suggestions,
                'messages.data_retrieved_successfully',
                ['total' => $suggestions->count()]
            );
        } catch (\Exception $e) {
            \Log::error('Service suggestions failed', [
                'service_id' => $id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse([
                ['code' => 'service', 'message' => translate('messages.service_suggestions_failed')],
            ], 500);
        }
    }

    /**
     * Check availability for time slots
     * بررسی دسترسی‌پذیری برای زمان‌ها
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @bodyParam salon_id integer required Salon ID. Example: 1
     * @bodyParam service_id integer required Service ID. Example: 1
     * @bodyParam date date required Booking date (must be today or future). Example: 2024-01-20
     * @bodyParam staff_id integer optional Staff member ID. Example: 1
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "date": "2024-01-20",
     *     "available_slots": ["09:00", "10:00", "11:00", "14:00", "15:00"],
     *     "service_duration_minutes": 60
     *   }
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "validation",
     *       "message": "The salon_id field is required."
     *     },
     *     {
     *       "code": "validation",
     *       "message": "The date must be a date after or equal to today."
     *     }
     *   ]
     * }
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'salon_id' => 'required|integer|exists:beauty_salons,id',
            'service_id' => 'required|integer|exists:beauty_services,id',
            'date' => 'required|date|after_or_equal:today',
            'staff_id' => 'nullable|integer|exists:beauty_staff,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $service = BeautyService::findOrFail($request->service_id);
        
        $availableSlots = $this->calendarService->getAvailableTimeSlots(
            $request->salon_id,
            $request->staff_id,
            $request->date,
            $service->duration_minutes
        );

        return $this->successResponse('messages.data_retrieved_successfully', [
            'date' => $request->date,
            'available_slots' => $availableSlots,
            'service_duration_minutes' => $service->duration_minutes,
        ]);
    }

    /**
     * Store new booking
     * ذخیره رزرو جدید
     *
     * @param BeautyBookingStoreRequest $request
     * @return JsonResponse
     * 
     * @bodyParam salon_id integer required Salon ID. Example: 1
     * @bodyParam service_id integer required Service ID. Example: 1
     * @bodyParam staff_id integer optional Staff member ID. Example: 1
     * @bodyParam booking_date date required Booking date (must be future date). Example: 2024-01-20
     * @bodyParam booking_time time required Booking time in H:i format. Example: 10:00
     * @bodyParam payment_method string required Payment method (wallet/digital_payment/cash_payment). Example: cash_payment
     * 
     * @response 201 {
     *   "message": "Booking created successfully",
     *   "data": {
     *     "id": 100001,
     *     "booking_reference": "BB-100001",
     *     "status": "pending",
     *     "total_amount": 100000,
     *     "salon": {...},
     *     "service": {...},
     *     "staff": {...}
     *   }
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "validation",
     *       "message": "The salon_id field is required."
     *     },
     *     {
     *       "code": "booking",
     *       "message": "Time slot is not available"
     *     }
     *   ]
     * }
     * 
     * @response 302 {
     *   "message": "redirect_to_payment",
     *   "data": {
     *     "payment_link": "https://payment-gateway.com/..."
     *   }
     * }
     */
    public function store(BeautyBookingStoreRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            // Ensure backward compatibility for payment_method 'online'
            // اطمینان از سازگاری با payment_method مقدار 'online'
            if ($request->payment_method === 'online') {
                $request->merge(['payment_method' => 'digital_payment']);
            }

            $booking = $this->bookingService->createBooking(
                $request->user()->id,
                $request->salon_id,
                $request->all()
            );

            // Process payment
            // پردازش پرداخت
            $paymentResult = $this->bookingService->processPayment(
                $booking, 
                $request->payment_method,
                [
                    'payment_gateway' => $request->payment_gateway ?? 'stripe',
                    'callback_url' => $request->callback_url ?? null,
                    'payment_platform' => $request->payment_platform ?? 'web',
                ]
            );

            // If digital payment, return redirect URL
            if ($request->payment_method === 'digital_payment' && $paymentResult) {
                DB::commit();
                return $this->successResponse('redirect_to_payment', [
                    'redirect_url' => $paymentResult,
                    'booking' => $this->formatBookingForApi($booking),
                ]);
            }

            DB::commit();

            // Send notification
            // ارسال نوتیفیکیشن
            self::sendBookingNotificationToAll($booking, 'booking_created');

            return $this->successResponse('booking_created_successfully', $this->formatBookingForApi($booking), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Check if error is about slot availability
            // بررسی اینکه آیا خطا مربوط به دسترسی‌پذیری زمان است
            $errorMessage = $e->getMessage();
            $isAvailabilityError = stripos($errorMessage, 'not available') !== false 
                || stripos($errorMessage, 'time_slot') !== false
                || stripos($errorMessage, 'availability') !== false;
            
            \Log::error('Booking creation failed', [
                'user_id' => $request->user()->id ?? null,
                'salon_id' => $request->salon_id ?? null,
                'service_id' => $request->service_id ?? null,
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString(),
            ]);
            
            if ($isAvailabilityError) {
                return $this->errorResponse([
                    [
                        'code' => 'slot_unavailable',
                        'message' => translate('messages.time_slot_not_available'),
                    ],
                ], 422);
            }
            
            return $this->errorResponse([
                ['code' => 'booking', 'message' => $errorMessage],
            ], 500);
        }
    }

    /**
     * Index - List user's bookings
     * لیست رزروهای کاربر
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->get('per_page', $request->get('limit', 25));
        $offset = $request->get('offset', 0);
        $serviceId = $request->get('service_id', $request->get('service_type'));
        $dateRange = $this->parseDateRange($request->get('date_range'));
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        // Fixed: paginate() expects page number (1, 2, 3...), not offset
        // اصلاح شده: paginate() شماره صفحه را انتظار دارد (1, 2, 3...)، نه offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $bookings = $this->booking->where('user_id', $request->user()->id)
            ->with(['salon.store', 'service', 'staff'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                if ($request->type === 'upcoming') {
                    $query->upcoming();
                } elseif ($request->type === 'past') {
                    $query->past();
                } elseif ($request->type === 'cancelled') {
                    $query->where('status', 'cancelled');
                }
            })
            ->when($serviceId, function ($query) use ($serviceId) {
                $query->where('service_id', $serviceId);
            })
            ->when($request->filled('staff_id'), function ($query) use ($request) {
                $query->where('staff_id', $request->staff_id);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('booking_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('booking_date', '<=', $request->date_to);
            })
            ->when($dateRange, function ($query) use ($dateRange) {
                [$fromDate, $toDate] = $dateRange;
                $query->whereDate('booking_date', '>=', $fromDate)
                    ->whereDate('booking_date', '<=', $toDate);
            })
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return $this->listResponse($bookings);
    }

    /**
     * Show booking details
     * نمایش جزئیات رزرو
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $booking = $this->booking->where('user_id', $request->user()->id)
            ->with(['salon.store', 'service', 'staff', 'review', 'conversation'])
            ->findOrFail($id);

        // Authorization check: Ensure booking belongs to current user
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به کاربر فعلی است
        $this->authorizeBookingOwnership($booking, $request->user()->id);

        return $this->successResponse('messages.data_retrieved_successfully', $this->formatBookingForApi($booking, true));
    }
    
    /**
     * Get conversation for a booking
     * دریافت گفتگو برای یک رزرو
     *
     * @param Request $request
     * @param int $id Booking ID
     * @return JsonResponse
     */
    public function getConversation(Request $request, int $id): JsonResponse
    {
        $booking = $this->booking->where('user_id', $request->user()->id)
            ->findOrFail($id);

        // Authorization check
        // بررسی مجوز
        $this->authorizeBookingOwnership($booking, $request->user()->id);

        if (!$booking->conversation_id) {
            return $this->errorResponse([
                ['code' => 'conversation', 'message' => translate('messages.conversation_not_found')],
            ], 404);
        }

        $conversation = \App\Models\Conversation::with(['messages' => function($q) {
            $q->orderBy('created_at', 'asc'); // Order messages ascending
        }, 'sender', 'receiver'])
            ->findOrFail($booking->conversation_id);

        return $this->successResponse('messages.data_retrieved_successfully', [
            'conversation_id' => $conversation->id,
            'messages' => $conversation->messages->map(function ($message) {
                $fileData = null;
                if ($message->file) {
                    if (is_string($message->file)) {
                        $fileData = json_decode($message->file, true);
                    } else {
                        $fileData = $message->file;
                    }
                }
                
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'message' => $message->message,
                    'file' => $fileData,
                    'file_url' => $message->file_full_url ?? null,
                    'sender_type' => 'customer', // Customer is always sender in this context
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * Send message in booking conversation
     * ارسال پیام در گفتگوی رزرو
     *
     * @param Request $request
     * @param int $id Booking ID
     * @return JsonResponse
     * 
     * @bodyParam message string required Message text. Example: "Hello, I have a question"
     * @bodyParam file file optional File attachment (multipart/form-data). Example: image.jpg
     * 
     * @response 200 {
     *   "message": "Message sent successfully",
     *   "data": {
     *     "id": 1,
     *     "message": "Hello, I have a question",
     *     "file_url": null,
     *     "sender_type": "customer",
     *     "created_at": "2024-01-20 10:00:00"
     *   }
     * }
     */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $booking = $this->booking->where('user_id', $request->user()->id)
            ->with(['salon.store.vendor'])
            ->findOrFail($id);

        // Authorization check
        // بررسی مجوز
        $this->authorizeBookingOwnership($booking, $request->user()->id);

        try {
            // Get or create UserInfo for customer (always needed)
            // دریافت یا ایجاد UserInfo برای مشتری (همیشه مورد نیاز است)
            $customer = $request->user();
            $customerInfo = \App\Models\UserInfo::where('user_id', $customer->id)->first();
            if (!$customerInfo) {
                $customerInfo = new \App\Models\UserInfo();
                $customerInfo->user_id = $customer->id;
                $customerInfo->f_name = $customer->f_name;
                $customerInfo->l_name = $customer->l_name;
                $customerInfo->phone = $customer->phone;
                $customerInfo->email = $customer->email;
                $customerInfo->image = $customer->image;
                $customerInfo->save();
            }

            // Get or create conversation
            // دریافت یا ایجاد گفتگو
            $conversation = null;
            if ($booking->conversation_id) {
                $conversation = \App\Models\Conversation::find($booking->conversation_id);
            }

            // If no conversation exists, create one
            // اگر گفتگو وجود ندارد، ایجاد کنید
            if (!$conversation) {
                $salon = $booking->salon;
                $store = $salon->store;
                
                if (!$store || !$store->vendor_id) {
                    return $this->errorResponse([
                        ['code' => 'conversation', 'message' => translate('messages.vendor_not_found')],
                    ], 404);
                }

                // Get or create UserInfo for vendor
                // دریافت یا ایجاد UserInfo برای فروشنده
                $vendor = \App\Models\Vendor::find($store->vendor_id);
                if (!$vendor) {
                    return $this->errorResponse([
                        ['code' => 'conversation', 'message' => translate('messages.vendor_not_found')],
                    ], 404);
                }

                $vendorInfo = \App\Models\UserInfo::where('vendor_id', $vendor->id)->first();
                if (!$vendorInfo) {
                    $vendorInfo = new \App\Models\UserInfo();
                    $vendorInfo->vendor_id = $vendor->id;
                    $vendorInfo->f_name = $vendor->f_name;
                    $vendorInfo->l_name = $vendor->l_name;
                    $vendorInfo->phone = $vendor->phone;
                    $vendorInfo->email = $vendor->email;
                    $vendorInfo->image = $vendor->image;
                    $vendorInfo->save();
                }

                // Check if conversation already exists
                // بررسی وجود گفتگو
                $conversation = \App\Models\Conversation::whereConversation($customerInfo->id, $vendorInfo->id)->first();

                if (!$conversation) {
                    $conversation = new \App\Models\Conversation();
                    $conversation->sender_id = $customerInfo->id;
                    $conversation->sender_type = 'customer';
                    $conversation->receiver_id = $vendorInfo->id;
                    $conversation->receiver_type = 'vendor';
                    $conversation->unread_message_count = 0;
                    $conversation->last_message_time = now();
                    $conversation->save();
                }

                // Update booking with conversation_id
                // به‌روزرسانی رزرو با conversation_id
                $booking->update(['conversation_id' => $conversation->id]);
            }

            // Handle file upload if provided
            // مدیریت آپلود فایل در صورت ارائه
            $fileData = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension() ?: 'png';
                $uploadedPath = \App\CentralLogics\Helpers::upload('conversation/', $extension, $file);
                
                if ($uploadedPath) {
                    $fileData = [
                        [
                            'img' => $uploadedPath,
                            'storage' => 'public',
                        ],
                    ];
                }
            }

            // Create message
            // ایجاد پیام
            $message = new \App\Models\Message();
            $message->conversation_id = $conversation->id;
            // Use UserInfo id to match Message::sender() relationship
            // استفاده از شناسه UserInfo برای انطباق با رابطه sender()
            $message->sender_id = $customerInfo->id;
            $message->message = $request->message;
            
            if ($fileData) {
                $message->file = json_encode($fileData, JSON_UNESCAPED_SLASHES);
            }

            $message->save();

            // Update conversation
            // به‌روزرسانی گفتگو
            $conversation->unread_message_count = ($conversation->unread_message_count ?? 0) + 1;
            $conversation->last_message_id = $message->id;
            $conversation->last_message_time = now();
            $conversation->save();

            // Format file URL
            // فرمت URL فایل
            $fileUrl = null;
            if ($fileData && isset($fileData[0]['img'])) {
                $fileUrl = asset('storage/' . $fileData[0]['img']);
            }

            return $this->successResponse('messages.message_sent_successfully', [
                'id' => $message->id,
                'message' => $message->message,
                'file_url' => $fileUrl,
                'sender_type' => 'customer',
                'created_at' => $message->created_at->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Send message failed', [
                'booking_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse([
                ['code' => 'message', 'message' => translate('messages.failed_to_send_message')],
            ], 500);
        }
    }

    /**
     * Cancel booking
     * لغو رزرو
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $booking = $this->booking->where('user_id', $request->user()->id)->findOrFail($id);

        // Authorization check: Ensure booking belongs to current user
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به کاربر فعلی است
        $this->authorizeBookingOwnership($booking, $request->user()->id);

        // Enforce 24h cancellation rule server-side
        // اعمال قانون لغو 24 ساعته در سمت سرور
        if (!$booking->canCancel()) {
            try {
                $bookingDateTime = $booking->getParsedBookingDateTime();
                $hoursUntilBooking = now()->diffInHours($bookingDateTime, true);
                $cancellationFee = $booking->calculateCancellationFee();
                $cancellationDeadline = $bookingDateTime->copy()->subHours(24);
                
                return $this->errorResponse([
                    [
                        'code' => 'cancellation_window_passed',
                        'message' => translate('cannot_cancel_booking_within_24_hours'),
                        'cancellation_fee' => $cancellationFee,
                        'cancellation_deadline' => $cancellationDeadline->format('Y-m-d H:i:s'),
                        'hours_until_booking' => $hoursUntilBooking,
                    ],
                ], 422);
            } catch (\Exception $e) {
            return $this->errorResponse([
                    ['code' => 'cancellation_window_passed', 'message' => translate('cannot_cancel_booking_within_24_hours')],
                ], 422);
            }
        }

        try {
            $booking = $this->bookingService->cancelBooking($booking, $request->cancellation_reason);

            // Send notification
            // ارسال نوتیفیکیشن
            self::sendBookingNotificationToAll($booking, 'booking_cancelled');

            return $this->successResponse('booking_cancelled_successfully', $this->formatBookingForApi($booking));
        } catch (\Exception $e) {
            \Log::error('Booking cancellation failed', [
                'booking_id' => $id,
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
     * Process payment
     * پردازش پرداخت
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function payment(Request $request): JsonResponse
    {
        if ($request->payment_method === 'online') {
            $request->merge(['payment_method' => 'digital_payment']);
        }
        
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|integer|exists:beauty_bookings,id',
            'payment_method' => 'required|in:wallet,digital_payment,cash_payment',
            'payment_gateway' => 'nullable|string|in:stripe,paypal,razorpay',
            'callback_url' => 'nullable|url',
            'payment_platform' => 'nullable|string|in:web,mobile',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $booking = $this->booking->where('user_id', $request->user()->id)->findOrFail($request->booking_id);
        
        // Authorization check: Ensure booking belongs to current user
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به کاربر فعلی است
        $this->authorizeBookingOwnership($booking, $request->user()->id);

        try {
            $paymentResult = $this->bookingService->processPayment(
                $booking,
                $request->payment_method,
                [
                    'payment_gateway' => $request->payment_gateway ?? 'stripe',
                    'callback_url' => $request->callback_url ?? null,
                    'payment_platform' => $request->payment_platform ?? 'web',
                ]
            );

            // If digital payment, return redirect URL
            if ($request->payment_method === 'digital_payment' && $paymentResult) {
                return $this->successResponse('redirect_to_payment', [
                    'redirect_url' => $paymentResult,
                    'booking' => $this->formatBookingForApi($booking),
                ]);
            }

            return $this->successResponse('payment_processed_successfully', $this->formatBookingForApi($booking));
        } catch (\Exception $e) {
            \Log::error('Payment processing failed', [
                'booking_id' => $request->booking_id ?? null,
                'user_id' => $request->user()->id ?? null,
                'payment_method' => $request->payment_method ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse([
                ['code' => 'payment', 'message' => $e->getMessage()],
            ], 500);
        }
    }
    
    /**
     * Reschedule booking
     * تغییر زمان رزرو
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function reschedule(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'staff_id' => 'nullable|integer|exists:beauty_staff,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            $booking = $this->booking->where('user_id', $request->user()->id)
                ->with(['salon', 'service'])
                ->findOrFail($id);

            $this->authorizeBookingOwnership($booking, $request->user()->id);

            // Enforce 24h reschedule rule server-side
            // اعمال قانون تغییر زمان 24 ساعته در سمت سرور
            if (method_exists($booking, 'canReschedule') && !$booking->canReschedule()) {
                try {
                    $bookingDateTime = $booking->getParsedBookingDateTime();
                    $hoursUntilBooking = now()->diffInHours($bookingDateTime, true);
                    
                    return $this->errorResponse([
                        [
                            'code' => 'cancellation_window_passed',
                            'message' => translate('messages.cannot_reschedule_booking'),
                            'hours_until_booking' => $hoursUntilBooking,
                        ],
                    ], 422);
                } catch (\Exception $e) {
                    return $this->errorResponse([
                        ['code' => 'cancellation_window_passed', 'message' => translate('messages.cannot_reschedule_booking')],
                    ], 422);
                }
            }

            // Validate availability for new time slot
            // اعتبارسنجی دسترسی‌پذیری برای زمان جدید
            $service = $booking->service;
            if (!$service) {
                return $this->errorResponse([
                    ['code' => 'service', 'message' => translate('messages.service_not_found')],
                ], 404);
            }

            $availableSlots = $this->calendarService->getAvailableTimeSlots(
                $booking->salon_id,
                $request->staff_id ?? $booking->staff_id,
                $request->booking_date,
                $service->duration_minutes
            );

            $requestedTime = $request->booking_time;
            if (!in_array($requestedTime, $availableSlots)) {
                return $this->errorResponse([
                    [
                        'code' => 'slot_unavailable',
                        'message' => translate('messages.time_slot_not_available'),
                        'requested_time' => $requestedTime,
                        'available_slots' => $availableSlots,
                    ],
                ], 422);
            }

            $booking = $this->bookingService->rescheduleBooking($booking, [
                'booking_date' => $request->booking_date,
                'booking_time' => $request->booking_time,
                'staff_id' => $request->staff_id,
                'notes' => $request->notes,
            ]);

            self::sendBookingNotificationToAll($booking, 'booking_rescheduled');

            return $this->successResponse('booking_rescheduled_successfully', $this->formatBookingForApi($booking, true));
        } catch (\Exception $e) {
            \Log::error('Booking reschedule failed', [
                'booking_id' => $id,
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
     * Format booking data for API response
     * فرمت داده رزرو برای پاسخ API
     *
     * @param BeautyBooking $booking
     * @param bool $includeDetails
     * @return array
     */
    private function formatBookingForApi(BeautyBooking $booking, bool $includeDetails = false): array
    {
        // Safely format booking_date with null check
        // فرمت ایمن booking_date با بررسی null
        $bookingDate = null;
        if ($booking->booking_date) {
            $bookingDate = $booking->booking_date instanceof \Carbon\Carbon 
                ? $booking->booking_date->format('Y-m-d')
                : (is_string($booking->booking_date) ? $booking->booking_date : null);
        }
        
        // Safely format booking_time with null check
        // فرمت ایمن booking_time با بررسی null
        $bookingTime = null;
        if ($booking->booking_time) {
            $bookingTime = is_string($booking->booking_time) 
                ? $booking->booking_time 
                : (is_object($booking->booking_time) && method_exists($booking->booking_time, 'format')
                    ? $booking->booking_time->format('H:i:s')
                    : null);
        }
        
        // Safely format booking_date_time with null check
        // فرمت ایمن booking_date_time با بررسی null
        $bookingDateTime = null;
        if ($booking->booking_date_time) {
            if ($booking->booking_date_time instanceof \Carbon\Carbon) {
                $bookingDateTime = $booking->booking_date_time->format('Y-m-d H:i:s');
            } elseif (is_string($booking->booking_date_time)) {
                $bookingDateTime = $booking->booking_date_time;
            }
        }
        
        $bookingDateTimeCarbon = $booking->booking_date_time instanceof \Carbon\Carbon
            ? $booking->booking_date_time
            : ($booking->booking_date_time ? Carbon::parse($booking->booking_date_time) : null);
        
        $data = [
            'id' => $booking->id,
            'booking_reference' => $booking->booking_reference ?? '',
            'booking_date' => $bookingDate,
            'booking_time' => $bookingTime ?? '',
            'booking_date_time' => $bookingDateTime,
            'status' => $booking->status ?? 'pending',
            'payment_status' => $booking->payment_status ?? 'unpaid',
            'total_amount' => $booking->total_amount ?? 0.0,
            'payment_method' => $booking->payment_method ?? null,
            'salon_name' => $booking->salon?->store?->name ?? '',
            'service_name' => $booking->service?->name ?? '',
            'salon' => [
                'id' => $booking->salon?->id ?? null,
                'name' => $booking->salon?->store?->name ?? '',
                'address' => $booking->salon?->store?->address ?? null,
                'phone' => $booking->salon?->store?->phone ?? null,
                'image' => $booking->salon?->store?->image ? asset('storage/' . $booking->salon->store->image) : null,
            ],
            'can_cancel' => method_exists($booking, 'canCancel') ? $booking->canCancel() : false,
            'can_reschedule' => method_exists($booking, 'canReschedule') ? $booking->canReschedule() : false,
            'cancellation_deadline' => $bookingDateTimeCarbon ? $bookingDateTimeCarbon->copy()->subHours(24)->format('Y-m-d H:i:s') : null,
            'cancellation_fee' => method_exists($booking, 'calculateCancellationFee') ? $booking->calculateCancellationFee() : ($booking->cancellation_fee ?? 0.0),
            'created_at' => $booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $booking->updated_at ? $booking->updated_at->format('Y-m-d H:i:s') : null,
        ];

        if ($includeDetails) {
            // Use null-safe operator to prevent errors if service relationship is missing
            // استفاده از null-safe operator برای جلوگیری از خطا در صورت نبود رابطه service
            $data['service'] = $booking->service ? [
                'id' => $booking->service->id ?? null,
                'name' => $booking->service->name ?? '',
                'duration_minutes' => $booking->service->duration_minutes ?? null,
                'price' => $booking->service->price ?? null,
            ] : null;
            $data['staff'] = $booking->staff ? [
                'id' => $booking->staff->id,
                'name' => $booking->staff->name,
            ] : null;
            $data['notes'] = $booking->notes;
            $data['cancellation_reason'] = $booking->cancellation_reason;
            $data['cancellation_fee'] = $booking->cancellation_fee;

            $review = $booking->relationLoaded('review') ? $booking->review : null;
            $data['review'] = $review ? [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'status' => $review->status,
                'attachments' => $review->attachments ? array_map(fn ($path) => asset('storage/' . $path), $review->attachments) : [],
                'created_at' => $review->created_at ? $review->created_at->format('Y-m-d H:i:s') : null,
            ] : null;

            $conversation = $booking->relationLoaded('conversation') ? $booking->conversation : null;
            $data['conversation'] = $conversation ? [
                'id' => $conversation->id,
            ] : null;
        }

        return $data;
    }

    /**
     * Parse comma-separated date range safely
     * تجزیه بازه تاریخ جداشده با کاما به‌صورت ایمن
     *
     * @param string|null $dateRange Comma-separated date range string
     * @return array|null [from, to] dates or null when invalid
     */
    private function parseDateRange(?string $dateRange): ?array
    {
        if ($dateRange === null) {
            return null;
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $dateRange)), fn ($part) => $part !== ''));

        if (count($parts) !== 2) {
            return null;
        }

        try {
            $from = Carbon::parse($parts[0])->toDateString();
            $to = Carbon::parse($parts[1])->toDateString();
        } catch (\Exception) {
            return null;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    /**
     * Authorize booking ownership
     * مجوز مالکیت رزرو
     *
     * @param BeautyBooking $booking
     * @param int $userId
     * @return void
     */
    private function authorizeBookingOwnership(BeautyBooking $booking, int $userId): void
    {
        if ($booking->user_id !== $userId) {
            abort(403, translate('messages.unauthorized_access'));
        }
    }
}

