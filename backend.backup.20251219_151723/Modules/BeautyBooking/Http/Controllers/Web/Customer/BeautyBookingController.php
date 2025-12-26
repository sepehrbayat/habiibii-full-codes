<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyStaff;
use Modules\BeautyBooking\Services\BeautyBookingService;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Services\BeautyCrossSellingService;
use App\Traits\Payment;
use App\CentralLogics\Helpers;

/**
 * Beauty Booking Controller (Customer Web)
 * کنترلر رزرو (وب مشتری)
 */
class BeautyBookingController extends Controller
{
    use Payment;

    public function __construct(
        private BeautyBookingService $bookingService,
        private BeautyCalendarService $calendarService,
        private BeautyCrossSellingService $crossSellingService
    ) {}

    /**
     * Start booking wizard
     * شروع ویزارد رزرو
     *
     * @param int $salonId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(int $salonId)
    {
        // Validate salon_id
        // اعتبارسنجی salon_id
        if ($salonId <= 0) {
            Toastr::error(translate('messages.invalid_salon_id'));
            return redirect()->route('beauty-booking.search');
        }
        
        // Find salon with validation
        // پیدا کردن سالن با اعتبارسنجی
        $salon = BeautySalon::with(['services.category', 'staff', 'store'])
            ->where('id', $salonId)
            ->active()
            ->verified()
            ->first();
        
        if (!$salon) {
            Toastr::error(translate('messages.salon_not_found'));
            return redirect()->route('beauty-booking.search');
        }
        
        // Check if salon has active services
        // بررسی اینکه آیا سالن خدمات فعال دارد
        $services = $salon->services()->where('status', 1)->get();
        
        if ($services->isEmpty()) {
            Toastr::error(translate('messages.salon_no_services'));
            return redirect()->route('beauty-booking.salon.show', $salonId);
        }
        
        return view('beautybooking::customer.booking.create', compact('salon', 'services'));
    }

    /**
     * Save booking wizard step data
     * ذخیره داده‌های مرحله ویزارد رزرو
     *
     * @param int $step
     * @param Request $request
     * @return RedirectResponse
     */
    public function saveStep(int $step, Request $request): RedirectResponse
    {
        $sessionData = $request->session()->get('booking_wizard', []);
        
        switch ($step) {
            case 1: // Service selection
                $validator = \Validator::make($request->all(), [
                    'salon_id' => 'required|integer|exists:beauty_salons,id',
                    'service_id' => 'required|integer|exists:beauty_services,id',
                ]);
                
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }
                
                $sessionData['salon_id'] = $request->salon_id;
                $sessionData['service_id'] = $request->service_id;
                break;
                
            case 2: // Staff selection
                if (!isset($sessionData['service_id'])) {
                    return redirect()->route('beauty-booking.booking.step', ['step' => 1, 'salon_id' => $sessionData['salon_id'] ?? $request->salon_id]);
                }
                $sessionData['staff_id'] = $request->staff_id ?? null;
                break;
                
            case 3: // Date/Time selection
                if (!isset($sessionData['service_id'])) {
                    return redirect()->route('beauty-booking.booking.step', ['step' => 1]);
                }
                $sessionData['booking_date'] = $request->booking_date;
                $sessionData['booking_time'] = $request->booking_time;
                break;
                
            case 4: // Payment method
                if (!isset($sessionData['booking_date']) || !isset($sessionData['booking_time'])) {
                    return redirect()->route('beauty-booking.booking.step', ['step' => 3]);
                }
                
                // Validate payment method
                // اعتبارسنجی روش پرداخت
                $validator = \Validator::make($request->all(), [
                    'payment_method' => 'required|in:digital_payment,wallet,cash_payment',
                ]);
                
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }
                
                $sessionData['payment_method'] = $request->payment_method;
                break;
        }
        
        $request->session()->put('booking_wizard', $sessionData);
        
        // After step 4 (payment method), redirect to step 5 (review) if step is less than 5
        // بعد از مرحله 4 (روش پرداخت)، هدایت به مرحله 5 (بررسی) در صورت کمتر از 5 بودن مرحله
        // Otherwise, the wizard is complete and booking should be stored via store() method
        // در غیر این صورت، ویزارد تکمیل شده و رزرو باید از طریق متد store() ذخیره شود
        if ($step < 4) {
            $nextStep = $step + 1;
            return redirect()->route('beauty-booking.booking.step', ['step' => $nextStep]);
        } elseif ($step === 4) {
            // After step 4, go to review step (step 5)
            // بعد از مرحله 4، به مرحله بررسی (مرحله 5) برو
            return redirect()->route('beauty-booking.booking.step', ['step' => 5]);
        } else {
            // If somehow we get here after step 4, redirect back to review
            // اگر به هر دلیلی بعد از مرحله 4 به اینجا رسیدیم، به بررسی برگرد
            return redirect()->route('beauty-booking.booking.step', ['step' => 5]);
        }
    }

    /**
     * Booking wizard step
     * مرحله ویزارد رزرو
     *
     * @param int $step
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function step(int $step, Request $request)
    {
        $sessionData = $request->session()->get('booking_wizard', []);
        
        // For AJAX requests, return error response instead of redirecting
        // برای درخواست‌های AJAX، برگرداندن پاسخ خطا به جای هدایت
        $isAjax = $request->ajax() || $request->wantsJson();

        switch ($step) {
            case 1: // Service selection
                $salonId = $sessionData['salon_id'] ?? $request->salon_id;
                if (!$salonId) {
                    if ($isAjax) {
                        return response()->json(['error' => translate('messages.salon_not_selected')], 400);
                    }
                    return redirect()->route('beauty-booking.search');
                }
                $salon = BeautySalon::findOrFail($salonId);
                $services = $salon->services()->where('status', 1)->with('category')->get();
                
                // Initialize session with salon_id if not set
                if (!isset($sessionData['salon_id'])) {
                    $sessionData['salon_id'] = $salonId;
                    $request->session()->put('booking_wizard', $sessionData);
                }
                
                return view('beautybooking::customer.booking.step1-service', compact('salon', 'services'));
            
            case 2: // Staff selection
                // Get service_id from session, request, or form data
                // دریافت service_id از session، request، یا داده‌های فرم
                $serviceId = $sessionData['service_id'] ?? $request->input('service_id') ?? $request->get('service_id');
                
                if (!$serviceId) {
                    if ($isAjax) {
                        return response()->json(['error' => translate('messages.service_not_selected')], 400);
                    }
                    return redirect()->route('beauty-booking.booking.step', ['step' => 1]);
                }
                
                // Save to session if not already there
                // ذخیره در session اگر هنوز وجود ندارد
                if (!isset($sessionData['service_id'])) {
                    $sessionData['service_id'] = $serviceId;
                    $request->session()->put('booking_wizard', $sessionData);
                }
                
                $service = BeautyService::findOrFail($serviceId);
                $staff = $service->salon->staff()->where('status', 1)->get();
                return view('beautybooking::customer.booking.step2-staff', compact('service', 'staff'));
            
            case 3: // Date/Time selection
                // Get service_id from session or request
                // دریافت service_id از session یا request
                $serviceId = $sessionData['service_id'] ?? $request->input('service_id');
                
                if (!$serviceId) {
                    if ($isAjax) {
                        return response()->json(['error' => translate('messages.service_not_selected')], 400);
                    }
                    return redirect()->route('beauty-booking.booking.step', ['step' => 1]);
                }
                
                // Save to session if not already there
                // ذخیره در session اگر هنوز وجود ندارد
                if (!isset($sessionData['service_id'])) {
                    $sessionData['service_id'] = $serviceId;
                    $request->session()->put('booking_wizard', $sessionData);
                }
                
                $service = BeautyService::findOrFail($serviceId);
                $staffId = $sessionData['staff_id'] ?? $request->input('staff_id');
                $selectedDate = $request->input('date', now()->format('Y-m-d'));
                
                // Get available time slots (requires duration from service)
                // دریافت زمان‌های در دسترس (نیاز به مدت زمان از سرویس)
                $availableSlots = $this->calendarService->getAvailableTimeSlots(
                    $service->salon_id,
                    $staffId,
                    $selectedDate,
                    $service->duration_minutes ?? 60 // Use service duration or default to 60 minutes
                );
                
                return view('beautybooking::customer.booking.step3-time', compact('service', 'availableSlots', 'staffId'));
            
            case 4: // Payment method
                // Get booking_date and booking_time from session or request
                // دریافت booking_date و booking_time از session یا request
                $bookingDate = $sessionData['booking_date'] ?? $request->input('date');
                $bookingTime = $sessionData['booking_time'] ?? $request->input('time');
                
                if (!$bookingDate || !$bookingTime) {
                    if ($isAjax) {
                        return response()->json(['error' => translate('messages.date_time_not_selected')], 400);
                    }
                    return redirect()->route('beauty-booking.booking.step', ['step' => 3]);
                }
                
                // Get service to get salon_id and ensure service_id is set
                // دریافت سرویس برای دریافت salon_id و اطمینان از تنظیم service_id
                if (!isset($sessionData['service_id'])) {
                    if ($isAjax) {
                        return response()->json(['error' => translate('messages.service_not_selected')], 400);
                    }
                    return redirect()->route('beauty-booking.booking.step', ['step' => 1]);
                }
                
                $service = BeautyService::findOrFail($sessionData['service_id']);
                
                // Ensure salon_id is in session
                // اطمینان از وجود salon_id در session
                if (!isset($sessionData['salon_id'])) {
                    $sessionData['salon_id'] = $service->salon_id;
                }
                
                // Save to session if not already there
                // ذخیره در session اگر هنوز وجود ندارد
                if (!isset($sessionData['booking_date'])) {
                    $sessionData['booking_date'] = $bookingDate;
                }
                if (!isset($sessionData['booking_time'])) {
                    $sessionData['booking_time'] = $bookingTime;
                }
                $request->session()->put('booking_wizard', $sessionData);
                
                // Calculate amounts
                // محاسبه مبالغ
                $amounts = $this->bookingService->calculateBookingAmounts(
                    $sessionData['salon_id'],
                    $sessionData
                );
                
                // Get cross-selling suggestions (with error handling)
                // دریافت پیشنهادات فروش متقابل (با مدیریت خطا)
                $suggestions = collect([]);
                try {
                    $userId = $request->user() ? $request->user()->id : null;
                    $salonId = $sessionData['salon_id'] ?? $service->salon_id;
                    $serviceId = (int) ($sessionData['service_id'] ?? $service->id);
                    
                    $suggestionsData = $this->crossSellingService->getSuggestedServices(
                        $serviceId,
                        $userId,
                        $salonId
                    );
                    
                    // Convert array results to objects for view compatibility
                    // تبدیل نتایج آرایه به اشیاء برای سازگاری با view
                    $suggestions = $suggestionsData->map(function($item) {
                        return (object) $item;
                    });
                } catch (\Exception $e) {
                    // Log error but don't break the payment step
                    // ثبت خطا اما شکستن مرحله پرداخت
                    \Log::error('Error loading cross-selling suggestions: ' . $e->getMessage());
                    // Continue with empty suggestions
                    // ادامه با پیشنهادات خالی
                    $suggestions = collect([]);
                }
                
                // Pass service and booking details to view for summary display
                // ارسال جزئیات سرویس و رزرو به view برای نمایش خلاصه
                return view('beautybooking::customer.booking.step4-payment', compact(
                    'amounts',
                    'suggestions',
                    'service',
                    'bookingDate',
                    'bookingTime'
                ));
            
            case 5: // Review & Confirm
                if (!isset($sessionData['payment_method'])) {
                    return redirect()->route('beauty-booking.booking.step', ['step' => 4]);
                }
                return view('beautybooking::customer.booking.step5-review', compact('sessionData'));
            
            default:
                return redirect()->route('beauty-booking.booking.create', ['salon_id' => $sessionData['salon_id']]);
        }
    }

    /**
     * Store booking
     * ذخیره رزرو
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $sessionData = $request->session()->get('booking_wizard', []);

        $validator = \Validator::make(array_merge($sessionData, $request->all()), [
            'salon_id' => 'required|integer|exists:beauty_salons,id',
            'service_id' => 'required|integer|exists:beauty_services,id',
            'booking_date' => 'required|date|after:today',
            'booking_time' => 'required|date_format:H:i',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $booking = $this->bookingService->createBooking(
                $user->id,
                $sessionData['salon_id'],
                array_merge($sessionData, $request->all())
            );

            // Clear session
            $request->session()->forget('booking_wizard');

            // Process payment
            if ($request->payment_method !== 'cash_payment') {
                // Handle digital payment or wallet
                // This would redirect to payment gateway
                return redirect()->route('beauty-booking.booking.confirmation', $booking->id);
            }

            Toastr::success(translate('messages.booking_created_successfully'));
            return redirect()->route('beauty-booking.booking.confirmation', $booking->id);
        } catch (\Exception $e) {
            \Log::error('Booking creation failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_create_booking'));
            return back()->withInput();
        }
    }

    /**
     * Booking confirmation
     * تأیید رزرو
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function confirmation(int $id)
    {
        $booking = \Modules\BeautyBooking\Entities\BeautyBooking::with([
            'salon.store',
            'service',
            'staff',
            'user'
        ])->findOrFail($id);

        return view('beautybooking::customer.booking.confirmation', compact('booking'));
    }
}



