<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Services;

use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyStaff;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Services\BeautyCommissionService;
use Modules\BeautyBooking\Services\BeautyRevenueService;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyPackageUsage;
use Modules\BeautyBooking\Traits\BeautyPushNotification;
use Modules\BeautyBooking\Traits\OpenTelemetryInstrumentation;
use App\CentralLogics\Helpers;
use App\CentralLogics\CustomerLogic;
use App\CentralLogics\CouponLogic;
use App\Models\Coupon;
use App\Models\BusinessSetting;
use App\Models\Admin;
use App\Models\AdminWallet;
use App\Models\StoreWallet;
use App\Library\Payer;
use App\Library\Payment as PaymentInfo;
use App\Library\Receiver;
use App\Traits\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Beauty Booking Service
 * سرویس رزرو زیبایی
 *
 * Handles all booking-related business logic
 * مدیریت تمام منطق کسب‌وکار مربوط به رزرو
 */
class BeautyBookingService
{
    use Payment, BeautyPushNotification, OpenTelemetryInstrumentation;
    
    public function __construct(
        private BeautyBooking $booking,
        private BeautyCalendarService $calendarService,
        private BeautyCommissionService $commissionService,
        private BeautyRevenueService $revenueService
    ) {}
    
    /**
     * Create a new booking
     * ایجاد رزرو جدید
     *
     * @param int $userId
     * @param int $salonId
     * @param array $bookingData
     * @return BeautyBooking
     * @throws \Exception
     */
    public function createBooking(int $userId, int $salonId, array $bookingData): BeautyBooking
    {
        // Wrap all operations in a database transaction to ensure atomicity
        // قرار دادن تمام عملیات در یک transaction دیتابیس برای اطمینان از atomicity
        // DB::transaction() is at the top level to ensure proper transaction handling and rollback
        // DB::transaction() در سطح بالا است تا مدیریت صحیح transaction و rollback را تضمین کند
        // instrument() is for OpenTelemetry observability only and is called inside the transaction
        // instrument() فقط برای observability OpenTelemetry است و در داخل transaction فراخوانی می‌شود
        try {
            return DB::transaction(function () use ($userId, $salonId, $bookingData) {
                // All operations are instrumented for OpenTelemetry tracing
                // تمام عملیات برای tracing OpenTelemetry instrument شده‌اند
                return $this->instrument('beauty.booking.create', function () use ($userId, $salonId, $bookingData) {
                // Get salon and validate it's verified and active
                // دریافت سالن و اعتبارسنجی تأیید و فعال بودن آن
                $salon = BeautySalon::with('store')->findOrFail($salonId);
                
                // Check salon verification status
                // بررسی وضعیت تأیید سالن
                if ($salon->verification_status !== 1 || !$salon->is_verified) {
                    throw new \Exception(translate('messages.salon_not_verified'));
                }
                
                // Check salon store is active
                // بررسی فعال بودن فروشگاه سالن
                if (!$salon->store || $salon->store->status !== 1 || !$salon->store->active) {
                    throw new \Exception(translate('messages.salon_not_active'));
                }
                
                // Get service to check duration and validate it's active
                // دریافت خدمت برای بررسی مدت زمان و اعتبارسنجی فعال بودن آن
                $service = BeautyService::where('id', $bookingData['service_id'])
                    ->where('salon_id', $salonId)
                    ->where('status', 1) // Service must be active
                    ->firstOrFail();
                
                // Validate package if provided
                // اعتبارسنجی پکیج در صورت ارائه
                if (isset($bookingData['package_id']) && !empty($bookingData['package_id'])) {
                    $package = BeautyPackage::findOrFail($bookingData['package_id']);
                    
                    // Verify package belongs to same salon and service
                    // تأیید اینکه پکیج متعلق به همان سالن و خدمت است
                    if ($package->salon_id != $salonId || $package->service_id != $service->id) {
                        throw new \Exception(translate('messages.package_not_valid_for_service'));
                    }
                    
                    // Check if package is valid for user
                    // بررسی معتبر بودن پکیج برای کاربر
                    if (!$package->isValidForUser($userId)) {
                        throw new \Exception(translate('messages.package_not_valid_or_expired'));
                    }
                    
                    // For package bookings, set main_service_id to package's service_id if not provided
                    // برای رزروهای پکیج، تنظیم main_service_id به service_id پکیج در صورت عدم ارائه
                    // This ensures consultation credit is correctly linked to the package's main service
                    // این اطمینان می‌دهد که اعتبار مشاوره به درستی به خدمت اصلی پکیج لینک می‌شود
                    if (!isset($bookingData['main_service_id']) || empty($bookingData['main_service_id'])) {
                        $bookingData['main_service_id'] = $package->service_id;
                    }
                }
                
                // Validate staff if provided
                // اعتبارسنجی کارمند در صورت ارائه
                if (isset($bookingData['staff_id']) && !empty($bookingData['staff_id'])) {
                    $staff = BeautyStaff::where('id', $bookingData['staff_id'])
                        ->where('salon_id', $salonId)
                        ->where('status', 1) // Staff must be active
                        ->first();
                    
                    if (!$staff) {
                        throw new \Exception(translate('messages.staff_not_found_or_inactive'));
                    }
                    
                    // Check if staff can perform this service (if service-staff relationship exists)
                    // بررسی اینکه آیا کارمند می‌تواند این خدمت را انجام دهد (در صورت وجود رابطه service-staff)
                    $canPerformService = \DB::table('beauty_service_staff')
                        ->where('service_id', $service->id)
                        ->where('staff_id', $staff->id)
                        ->exists();
                    
                    // If service-staff pivot table has entries, staff must be assigned to service
                    // اگر جدول pivot service-staff دارای ورودی است، کارمند باید به خدمت اختصاص داده شده باشد
                    $hasServiceStaffAssignments = \DB::table('beauty_service_staff')
                        ->where('service_id', $service->id)
                        ->exists();
                    
                    if ($hasServiceStaffAssignments && !$canPerformService) {
                        throw new \Exception(translate('messages.staff_cannot_perform_service'));
                    }
                }
                
                // Check availability before creating booking
                // بررسی دسترسی‌پذیری قبل از ایجاد رزرو
                // Note: This is a preliminary check. We'll re-check inside transaction with lock to prevent race conditions
                // توجه: این یک بررسی اولیه است. ما دوباره داخل transaction با lock بررسی می‌کنیم تا از race condition جلوگیری کنیم
                if (!$this->calendarService->isTimeSlotAvailable(
                    $salonId,
                    $bookingData['staff_id'] ?? null,
                    $bookingData['booking_date'],
                    $bookingData['booking_time'],
                    $service->duration_minutes
                )) {
                    throw new \Exception(translate('messages.time_slot_not_available'));
                }
                
                // Re-check availability with lock to prevent race conditions (double booking)
                // بررسی مجدد دسترسی‌پذیری با lock برای جلوگیری از race condition (double booking)
                // Lock existing bookings for this time slot to prevent concurrent bookings
                // قفل کردن رزروهای موجود برای این زمان برای جلوگیری از رزروهای همزمان
                
                // Validate and parse booking date/time with explicit format
                // اعتبارسنجی و تجزیه تاریخ/زمان رزرو با فرمت صریح
                $bookingDateTime = $this->validateAndParseBookingDateTime(
                    $bookingData['booking_date'],
                    $bookingData['booking_time']
                );
                $endDateTime = $bookingDateTime->copy()->addMinutes($service->duration_minutes);
                
                // Lock and check for overlapping bookings
                // قفل و بررسی رزروهای همپوشان
                // Note: We don't filter by booking_date to detect overlaps across midnight boundaries
                // توجه: ما بر اساس booking_date فیلتر نمی‌کنیم تا همپوشانی‌های فراتر از مرز نیمه‌شب را تشخیص دهیم
                // (e.g., a booking from 11 PM Jan 15 to 1 AM Jan 16 should conflict with a booking on Jan 16)
                // (مثلاً یک رزرو از 11 شب 15 ژانویه تا 1 صبح 16 ژانویه باید با رزروی در 16 ژانویه تداخل داشته باشد)
                $overlappingBooking = BeautyBooking::where('salon_id', $salonId)
                    ->where('status', '!=', 'cancelled');
                
                // Filter by staff_id only if provided
                // فیلتر بر اساس staff_id فقط در صورت ارائه
                // If staff_id is NOT provided, check ALL bookings (both with and without staff)
                // اگر staff_id ارائه نشده است، تمام رزروها را بررسی کنید (هم با کارمند و هم بدون کارمند)
                // This prevents double-booking: an unassigned booking conflicts with ANY existing booking
                // این از double-booking جلوگیری می‌کند: یک رزرو اختصاص نیافته با هر رزرو موجودی تداخل دارد
                // because an unassigned slot can be fulfilled by any available staff member
                // چون یک زمان اختصاص نیافته می‌تواند توسط هر کارمند در دسترس انجام شود
                if (isset($bookingData['staff_id']) && $bookingData['staff_id'] !== null) {
                    // If staff_id is provided, only match bookings for that specific staff
                    // اگر staff_id ارائه شده است، فقط رزروهای آن کارمند خاص را مطابقت دهید
                    $overlappingBooking->where('staff_id', $bookingData['staff_id']);
                }
                // When staff_id is null, no filter is applied - all bookings are checked
                // زمانی که staff_id null است، هیچ فیلتری اعمال نمی‌شود - تمام رزروها بررسی می‌شوند
                
                // IMPORTANT: Assign the result of ->first() back to $overlappingBooking
                // مهم: نتیجه ->first() را به $overlappingBooking اختصاص دهید
                // Without assignment, $overlappingBooking remains a query builder (always truthy)
                // بدون اختصاص، $overlappingBooking همچنان یک query builder است (همیشه truthy)
                $overlappingBooking = $overlappingBooking
                    ->where(function($q) use ($bookingDateTime, $endDateTime) {
                        $q->where(function($q2) use ($bookingDateTime, $endDateTime) {
                            $q2->whereBetween('booking_date_time', [$bookingDateTime, $endDateTime]);
                        })
                        ->orWhere(function($q3) use ($bookingDateTime, $endDateTime) {
                            // Use COALESCE to handle NULL service duration (default to 30 minutes if service not found)
                            // استفاده از COALESCE برای مدیریت NULL duration خدمت (پیش‌فرض 30 دقیقه اگر خدمت پیدا نشد)
                            $q3->whereRaw('DATE_ADD(booking_date_time, INTERVAL COALESCE((SELECT duration_minutes FROM beauty_services WHERE id = beauty_bookings.service_id), 30) MINUTE) >= ?', [$bookingDateTime])
                               ->where('booking_date_time', '<=', $endDateTime)
                               // Also ensure service exists to prevent invalid bookings
                               // همچنین اطمینان حاصل کنید که خدمت وجود دارد تا از رزروهای نامعتبر جلوگیری شود
                               ->whereRaw('EXISTS (SELECT 1 FROM beauty_services WHERE id = beauty_bookings.service_id)');
                        });
                    })
                    ->lockForUpdate()
                    ->first();
                
                if ($overlappingBooking) {
                    throw new \Exception(translate('messages.time_slot_not_available'));
                }
                
                // Calculate amounts (include user_id for consultation credit calculation)
                // محاسبه مبالغ (شامل user_id برای محاسبه اعتبار مشاوره)
                $bookingData['user_id'] = $userId;
                $amounts = $this->calculateBookingAmounts($salonId, $bookingData);
                
                // Generate booking reference
                // تولید شماره مرجع رزرو
                $bookingReference = $this->generateBookingReference();
                
                // Handle consultation credit initialization for regular services (not consultations)
                // مدیریت مقداردهی اولیه اعتبار مشاوره برای خدمات عادی (نه مشاوره‌ها)
                // Consultation credits are applied to regular services when previous consultations have been completed
                // اعتبارهای مشاوره به خدمات عادی اعمال می‌شوند زمانی که مشاوره‌های قبلی تکمیل شده‌اند
                $consultationCreditPercentage = 0.0;
                $consultationCreditAmount = 0.0;
                
                // For consultation services, store the credit percentage that will be used later for regular services
                // برای خدمات مشاوره، درصد اعتبار را ذخیره می‌کنیم که بعداً برای خدمات عادی استفاده خواهد شد
                if ($service->isConsultation()) {
                    $consultationCreditPercentage = $bookingData['consultation_credit_percentage'] ?? $service->consultation_credit_percentage ?? 0.0;
                    // Credit amount will be calculated and applied when the main/regular service is booked
                    // مبلغ اعتبار هنگام رزرو خدمت اصلی/عادی محاسبه و اعمال می‌شود
                }
                
                // Create booking
                // ایجاد رزرو
                // Use validated booking_date_time (already parsed above)
                // استفاده از booking_date_time اعتبارسنجی شده (قبلاً تجزیه شده است)
                $booking = $this->booking->create([
                    'user_id' => $userId,
                    'salon_id' => $salonId,
                    'service_id' => $bookingData['service_id'],
                    'package_id' => $bookingData['package_id'] ?? null,
                    'main_service_id' => $bookingData['main_service_id'] ?? null,
                    'staff_id' => $bookingData['staff_id'] ?? null,
                    'zone_id' => $salon->store?->zone_id ?? $salon->zone_id ?? null, // Get from store relationship with null-safety
                    'booking_date' => $bookingData['booking_date'],
                    'booking_time' => $bookingData['booking_time'],
                    'booking_date_time' => $bookingDateTime, // Use validated and parsed datetime
                    'booking_reference' => $bookingReference,
                    'total_amount' => $amounts['total'],
                    'commission_amount' => $amounts['commission'],
                    'service_fee' => $amounts['service_fee'],
                    'consultation_credit_percentage' => $consultationCreditPercentage,
                    'consultation_credit_amount' => $amounts['consultation_credit'] ?? 0.0,
                    'additional_services' => $amounts['additional_services'] ?? null,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'payment_method' => $bookingData['payment_method'] ?? null,
                    'notes' => $bookingData['notes'] ?? null,
                ]);
                
                // Mark consultation bookings as having credit applied (only for regular service bookings, not consultations)
                // علامت‌گذاری رزروهای مشاوره به‌عنوان اعمال شده (فقط برای رزروهای خدمت عادی، نه مشاوره‌ها)
                // Note: Consultation credit is calculated in calculateBookingAmounts() only for regular services (service_type === 'service')
                // توجه: اعتبار مشاوره در calculateBookingAmounts() فقط برای خدمات عادی (service_type === 'service') محاسبه می‌شود
                // So we only need to check if credit was actually calculated and applied
                // پس فقط باید بررسی کنیم که آیا اعتبار واقعاً محاسبه و اعمال شده است
                if (!$service->isConsultation() && ($amounts['consultation_credit'] ?? 0.0) > 0) {
                    // CRITICAL FIX: Use booking IDs from calculateConsultationCredit() to ensure atomicity
                    // رفع بحرانی: استفاده از booking IDs از calculateConsultationCredit() برای اطمینان از atomicity
                    // This prevents race conditions by marking exactly the bookings that contributed to the credit
                    // این از race condition با علامت‌گذاری دقیق رزروهایی که به اعتبار کمک کرده‌اند جلوگیری می‌کند
                    $consultationBookingIds = $amounts['consultation_credit_booking_ids'] ?? [];
                    $mainServiceId = $bookingData['main_service_id'] ?? $bookingData['service_id'];
                    $this->markConsultationCreditApplied(
                        $salonId,
                        $userId,
                        $amounts['consultation_credit'],
                        $consultationBookingIds, // Pass booking IDs directly to ensure atomicity
                        $mainServiceId // Needed to link standalone consultations
                    );
                }
                
                // Update calendar to block time slot
                // به‌روزرسانی تقویم برای بلاک کردن زمان
                $this->calendarService->blockTimeSlot(
                    $salonId,
                    $bookingData['staff_id'] ?? null,
                    $bookingData['booking_date'],
                    $bookingData['booking_time'],
                    Carbon::parse($bookingData['booking_date'] . ' ' . $bookingData['booking_time'])
                        ->addMinutes($service->duration_minutes)
                        ->format('H:i:s'),
                    'manual_block',
                    'Booking #' . $bookingReference
                );
                
                // Create conversation for customer-salon chat
                // ایجاد گفتگو برای چت مشتری-سالن
                $conversation = $this->createBookingConversation($booking, $userId, $salon);
                if ($conversation) {
                    $booking->update(['conversation_id' => $conversation->id]);
                }
                
                // Update salon booking statistics
                // به‌روزرسانی آمار رزرو سالن
                $salonService = app(\Modules\BeautyBooking\Services\BeautySalonService::class);
                $salonService->updateBookingStatistics($salonId);
                
                // Send notifications to all parties
                // ارسال نوتیفیکیشن به همه طرفین
                self::sendBookingNotificationToAll($booking, 'created');
                
                return $booking;
                }, [
                    'beauty.booking.user_id' => $userId,
                    'beauty.booking.salon_id' => $salonId,
                    'beauty.booking.service_id' => $bookingData['service_id'] ?? null,
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Booking creation failed: ' . $e->getMessage(), [
                'user_id' => $userId,
                'salon_id' => $salonId,
                'booking_data' => $bookingData,
            ]);
            throw $e;
        }
    }
    
    /**
     * Check if time slot is available
     * بررسی دسترسی‌پذیری زمان
     *
     * @param int $salonId
     * @param int|null $staffId
     * @param string $date
     * @param string $time
     * @param int $durationMinutes
     * @return bool
     */
    public function checkAvailability(int $salonId, ?int $staffId, string $date, string $time, int $durationMinutes): bool
    {
        return $this->calendarService->isTimeSlotAvailable($salonId, $staffId, $date, $time, $durationMinutes);
    }
    
    /**
     * Calculate booking amounts
     * محاسبه مبالغ رزرو
     *
     * @param int $salonId
     * @param array $bookingData
     * @return array
     */
    public function calculateBookingAmounts(int $salonId, array $bookingData): array
    {
        $service = BeautyService::findOrFail($bookingData['service_id']);
        $basePrice = $service->price;
        
        // Calculate additional services (cross-sell/upsell) amount first
        // محاسبه مبلغ خدمات اضافی (فروش متقابل/افزایش فروش) ابتدا
        $additionalServicesData = $this->calculateAdditionalServices($salonId, $bookingData);
        $additionalServicesAmount = $additionalServicesData['total_amount'];
        $additionalServicesCommission = $additionalServicesData['commission'];
        
        // Calculate total base price (base + additional services) for fee calculations
        // محاسبه قیمت پایه کل (پایه + خدمات اضافی) برای محاسبات هزینه
        $totalBasePrice = $basePrice + $additionalServicesAmount;
        
        // Calculate service fee (1-3% of total base price from customer)
        // محاسبه هزینه سرویس (1-3٪ از قیمت پایه کل از مشتری)
        $serviceFeePercentage = config('beautybooking.service_fee.percentage', 2);
        $serviceFee = $totalBasePrice * ($serviceFeePercentage / 100);
        
        // Calculate tax (on total base price)
        // محاسبه مالیات (بر اساس قیمت پایه کل)
        $taxPercentage = config('beautybooking.tax.percentage', 0);
        $taxAmount = $totalBasePrice * ($taxPercentage / 100);
        
        // Calculate discount (if any) - use totalBasePrice for consistency with fees and taxes
        // محاسبه تخفیف (در صورت وجود) - استفاده از totalBasePrice برای سازگاری با هزینه‌ها و مالیات
        $discount = $this->calculateDiscount($salonId, $bookingData, $totalBasePrice);
        
        // Calculate consultation credit if this is a main/regular service booking (not a consultation)
        // محاسبه اعتبار مشاوره در صورت اینکه این رزرو خدمت اصلی/عادی باشد (نه مشاوره)
        $consultationCredit = 0.0;
        $consultationCreditBookingIds = []; // Store booking IDs that contributed to credit
        if ($service->service_type === 'service') {
            // Only apply consultation credit for regular services, not for consultations themselves
            // فقط اعمال اعتبار مشاوره برای خدمات عادی، نه برای خود مشاوره‌ها
            // Use basePrice (not totalBasePrice) because consultation credit should only apply to the main service,
            // not to additional services. The consultation was for the main service, not for additional services.
            // استفاده از basePrice (نه totalBasePrice) چون اعتبار مشاوره باید فقط به خدمت اصلی اعمال شود،
            // نه به خدمات اضافی. مشاوره برای خدمت اصلی بود، نه برای خدمات اضافی.
            $consultationCreditResult = $this->calculateConsultationCredit($salonId, $bookingData, $basePrice);
            $consultationCredit = $consultationCreditResult['credit'];
            $consultationCreditBookingIds = $consultationCreditResult['booking_ids'];
        }
        
        // Calculate commission (on base price + additional services, before consultation credit)
        // محاسبه کمیسیون (بر اساس قیمت پایه + خدمات اضافی، قبل از اعتبار مشاوره)
        $commission = $this->commissionService->calculateCommission(
            (int) $salonId,
            (int) $bookingData['service_id'],
            (float) $basePrice
        );
        $commission += $additionalServicesCommission;
        
        // Total amount (totalBasePrice already includes base + additional services, so add fees and subtract discounts/credit)
        // مبلغ کل (totalBasePrice قبلاً شامل پایه + خدمات اضافی است، پس هزینه‌ها را اضافه و تخفیف‌ها/اعتبار را کم می‌کنیم)
        // Note: serviceFee and taxAmount are calculated on totalBasePrice, so we use totalBasePrice here to avoid double-counting
        // توجه: serviceFee و taxAmount بر اساس totalBasePrice محاسبه شده‌اند، پس از totalBasePrice استفاده می‌کنیم تا از شمارش دوباره جلوگیری شود
        $total = $totalBasePrice + $serviceFee + $taxAmount - $discount - $consultationCredit;
        
        return [
            'base_price' => $basePrice,
            'additional_services_amount' => $additionalServicesAmount,
            'service_fee' => $serviceFee,
            'tax_amount' => $taxAmount,
            'discount' => $discount,
            'consultation_credit' => $consultationCredit,
            'consultation_credit_booking_ids' => $consultationCreditBookingIds, // Store booking IDs for atomic marking
            'commission' => $commission,
            'additional_services' => $additionalServicesData['services'],
            'total' => max($total, 0), // Ensure total is not negative
        ];
    }
    
    /**
     * Calculate consultation credit for main service booking
     * محاسبه اعتبار مشاوره برای رزرو خدمت اصلی
     *
     * Note: This method only calculates the credit amount. The actual marking of consultation bookings
     * as having credit applied should happen AFTER the main service booking is successfully created.
     * 
     * توجه: این متد فقط مبلغ اعتبار را محاسبه می‌کند. علامت‌گذاری واقعی رزروهای مشاوره به‌عنوان اعمال شده
     * باید پس از ایجاد موفق رزرو خدمت اصلی انجام شود.
     *
     * @param int $salonId
     * @param array $bookingData
     * @param float $basePrice Base service price (NOT including additional services). Consultation credit should only apply to the main service, not to additional services.
     * @return array Returns array with 'credit' (float) and 'booking_ids' (array)
     */
    private function calculateConsultationCredit(int $salonId, array $bookingData, float $basePrice): array
    {
        // Only apply credit if consultation credit is enabled in config
        // فقط در صورت فعال بودن اعتبار مشاوره در config اعمال شود
        $creditEnabled = config('beautybooking.consultation.credit_to_main_service', true);
        if (!$creditEnabled) {
            return ['credit' => 0.0, 'booking_ids' => []];
        }
        
        // Check if user has a completed consultation booking for this main service
        // بررسی اینکه آیا کاربر یک رزرو مشاوره تکمیل شده برای این خدمت اصلی دارد
        if (!isset($bookingData['service_id']) || !isset($bookingData['user_id'])) {
            return ['credit' => 0.0, 'booking_ids' => []];
        }
        
        // Use main_service_id if available (e.g., for package bookings), otherwise use service_id
        // استفاده از main_service_id در صورت موجود بودن (مثلاً برای رزروهای پکیج)، در غیر این صورت از service_id استفاده می‌کنیم
        // Consultation bookings are linked to main_service_id, so we need to use the correct ID
        // رزروهای مشاوره به main_service_id لینک شده‌اند، پس باید ID صحیح را استفاده کنیم
        $mainServiceId = $bookingData['main_service_id'] ?? $bookingData['service_id'];
        $userId = $bookingData['user_id'] ?? null;
        
        if (!$userId) {
            return ['credit' => 0.0, 'booking_ids' => []];
        }
        
        // Find completed consultation bookings for this user and salon with this main service
        // یافتن رزروهای مشاوره تکمیل شده برای این کاربر و سالن با این خدمت اصلی
        // IMPORTANT: Filter by service relationship to ensure we only match consultation bookings
        // (bookings where the service_id points to a consultation service, not a regular service)
        // مهم: فیلتر بر اساس رابطه service برای اطمینان از اینکه فقط رزروهای مشاوره را مطابقت می‌دهیم
        // (رزروهایی که service_id آنها به یک خدمت مشاوره اشاره می‌کند، نه یک خدمت عادی)
        // Also include consultation bookings with null main_service_id (standalone consultations not yet linked)
        // همچنین شامل رزروهای مشاوره با main_service_id null (مشاوره‌های مستقل که هنوز لینک نشده‌اند)
        // CRITICAL: Always use lockForUpdate() when in a transaction to prevent race conditions
        // بحرانی: همیشه از lockForUpdate() استفاده کنید زمانی که در یک transaction هستیم برای جلوگیری از race condition
        // The lock must persist through the entire credit calculation and marking process
        // قفل باید در طول کل فرآیند محاسبه و علامت‌گذاری اعتبار باقی بماند
        // If two concurrent booking requests both calculate credit from the same consultation booking,
        // both will see it as available and both will calculate the same credit amount, leading to
        // the same credit being applied multiple times. Locking prevents this.
        // اگر دو درخواست رزرو همزمان هر دو اعتبار را از همان رزرو مشاوره محاسبه کنند،
        // هر دو آن را به عنوان در دسترس می‌بینند و هر دو همان مبلغ اعتبار را محاسبه می‌کنند،
        // که منجر به اعمال همان اعتبار چندین بار می‌شود. قفل‌گذاری از این جلوگیری می‌کند.
        $query = $this->booking
            ->where('user_id', $userId)
            ->where('salon_id', $salonId)
            ->where(function ($query) use ($mainServiceId) {
                $query->where('main_service_id', $mainServiceId)
                      ->orWhereNull('main_service_id'); // Include standalone consultation bookings
            })
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->where('consultation_credit_amount', 0) // Not yet applied
            ->where('consultation_credit_percentage', '>', 0)
            ->whereHas('service', function ($query) {
                // Only match bookings where the service is a consultation service
                // فقط رزروهایی را مطابقت دهید که خدمت آنها یک خدمت مشاوره است
                $query->whereIn('service_type', ['pre_consultation', 'post_consultation']);
            });
        
        // CRITICAL FIX: Always use lockForUpdate() when in a transaction to ensure locks persist
        // رفع بحرانی: همیشه از lockForUpdate() استفاده کنید زمانی که در یک transaction هستیم تا قفل‌ها باقی بمانند
        // The lock must be held until markConsultationCreditApplied() completes to prevent race conditions
        // قفل باید تا تکمیل markConsultationCreditApplied() نگه داشته شود تا از race condition جلوگیری شود
        if (DB::transactionLevel() > 0) {
            $query->lockForUpdate();
        }
        
        $consultationBookings = $query->get();
        
        $totalCredit = 0.0;
        $maxCreditPercentage = config('beautybooking.consultation.max_credit_percentage', 100.0);
        $bookingIds = []; // Track which bookings contributed to the credit
        
        foreach ($consultationBookings as $consultationBooking) {
            $creditPercentage = min($consultationBooking->consultation_credit_percentage, $maxCreditPercentage);
            $creditAmount = $consultationBooking->total_amount * ($creditPercentage / 100);
            $totalCredit += $creditAmount;
            $bookingIds[] = $consultationBooking->id; // Store booking ID for later marking
        }
        
        // Credit cannot exceed base service price (NOT including additional services)
        // Consultation credit should only apply to the main service, not to additional services.
        // The consultation was for the main service, so the credit should only apply to that service.
        // اعتبار نمی‌تواند از قیمت پایه خدمت (شامل خدمات اضافی نیست) بیشتر شود
        // اعتبار مشاوره باید فقط به خدمت اصلی اعمال شود، نه به خدمات اضافی.
        // مشاوره برای خدمت اصلی بود، پس اعتبار باید فقط به آن خدمت اعمال شود.
        $finalCredit = min($totalCredit, $basePrice);
        
        return ['credit' => $finalCredit, 'booking_ids' => $bookingIds];
    }
    
    /**
     * Mark consultation bookings as having credit applied
     * علامت‌گذاری رزروهای مشاوره به‌عنوان اعمال شده
     *
     * This should be called AFTER the main service booking is successfully created
     * این باید پس از ایجاد موفق رزرو خدمت اصلی فراخوانی شود
     *
     * CRITICAL FIX: Now accepts booking IDs directly from calculateConsultationCredit()
     * to ensure atomicity. The booking IDs are the exact bookings that contributed
     * to the credit amount, preventing race conditions.
     * رفع بحرانی: اکنون booking IDs را مستقیماً از calculateConsultationCredit() می‌پذیرد
     * تا atomicity را تضمین کند. booking IDs دقیقاً رزروهایی هستند که به مبلغ اعتبار
     * کمک کرده‌اند، که از race condition جلوگیری می‌کند.
     *
     * @param int $salonId
     * @param int $userId
     * @param float $creditAmount The total credit amount that was applied
     * @param array $bookingIds The IDs of consultation bookings that contributed to the credit
     * @param int|null $mainServiceId The main service ID (needed to link standalone consultations)
     * @return void
     */
    private function markConsultationCreditApplied(int $salonId, int $userId, float $creditAmount, array $bookingIds, ?int $mainServiceId = null): void
    {
        if ($creditAmount <= 0 || empty($bookingIds)) {
            return; // No credit to mark or no bookings to mark
        }
        
        // CRITICAL FIX: Use booking IDs directly instead of recalculating
        // This ensures we mark exactly the bookings that contributed to the credit,
        // preventing race conditions where concurrent requests might calculate from different bookings
        // رفع بحرانی: استفاده مستقیم از booking IDs به جای محاسبه مجدد
        // این اطمینان می‌دهد که دقیقاً رزروهایی را علامت‌گذاری می‌کنیم که به اعتبار کمک کرده‌اند،
        // جلوگیری از race condition که در آن درخواست‌های همزمان ممکن است از رزروهای مختلف محاسبه کنند
        // We're already inside a transaction from createBooking(), so we can use lockForUpdate()
        // ما قبلاً در یک transaction از createBooking() هستیم، پس می‌توانیم از lockForUpdate() استفاده کنیم
        $consultationBookings = $this->booking
            ->whereIn('id', $bookingIds) // Use exact booking IDs from calculateConsultationCredit()
            ->where('user_id', $userId) // Additional safety check
            ->where('salon_id', $salonId) // Additional safety check
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->where('consultation_credit_amount', 0) // Not yet applied (double-check)
            ->where('consultation_credit_percentage', '>', 0)
            ->whereHas('service', function ($query) {
                // Only match bookings where the service is a consultation service
                // فقط رزروهایی را مطابقت دهید که خدمت آنها یک خدمت مشاوره است
                $query->whereIn('service_type', ['pre_consultation', 'post_consultation']);
            })
            ->orderBy('created_at', 'asc') // Apply credit to oldest consultation bookings first
            ->lockForUpdate() // Lock rows to prevent concurrent access
            ->get();
        
        $maxCreditPercentage = config('beautybooking.consultation.max_credit_percentage', 100.0);
        $remainingCredit = $creditAmount;
        
        foreach ($consultationBookings as $consultationBooking) {
            if ($remainingCredit <= 0) {
                break; // All credit has been allocated
            }
            
            try {
                $creditPercentage = min($consultationBooking->consultation_credit_percentage, $maxCreditPercentage);
                $bookingCreditAmount = $consultationBooking->total_amount * ($creditPercentage / 100);
                
                // Mark the amount that was actually applied (may be less than full credit if total exceeded)
                // علامت‌گذاری مبلغی که واقعاً اعمال شد (ممکن است کمتر از اعتبار کامل باشد اگر کل تجاوز کرد)
                $appliedAmount = min($bookingCreditAmount, $remainingCredit);
                
                // Update main_service_id if it's null (link standalone consultation booking to this main service)
                // به‌روزرسانی main_service_id در صورت null (لینک کردن رزرو مشاوره مستقل به این خدمت اصلی)
                // Use database-level conditional update to prevent race condition without releasing lock
                // استفاده از به‌روزرسانی شرطی در سطح دیتابیس برای جلوگیری از race condition بدون آزاد کردن lock
                $updateData = [
                    'consultation_credit_amount' => $appliedAmount,
                ];
                
                // Link standalone consultation booking to this main service only if still null (database-level check)
                // لینک کردن رزرو مشاوره مستقل به این خدمت اصلی فقط در صورت null بودن (بررسی در سطح دیتابیس)
                // IMPORTANT: lockForUpdate() must be used with read operations (first/find/get), not directly with update()
                // مهم: lockForUpdate() باید با عملیات خواندن (first/find/get) استفاده شود، نه مستقیماً با update()
                // First acquire the lock by fetching the record, then update it separately within the transaction
                // ابتدا lock را با واکشی رکورد به دست آورید، سپس آن را جداگانه در transaction به‌روزرسانی کنید
                // Note: Model::update() returns boolean (true/false), not number of rows affected
                // توجه: Model::update() مقدار boolean (true/false) برمی‌گرداند، نه تعداد ردیف‌های تأثیرگرفته
                $updateSucceeded = false;
                if ($consultationBooking->main_service_id === null) {
                    // Re-fetch with lockForUpdate to acquire lock, then update with whereNull condition for atomic operation
                    // واکشی مجدد با lockForUpdate برای به دست آوردن lock، سپس به‌روزرسانی با شرط whereNull برای عملیات atomic
                    $lockedBooking = $this->booking->where('id', $consultationBooking->id)
                        ->whereNull('main_service_id')
                        ->lockForUpdate()
                        ->first();
                    
                    if ($lockedBooking) {
                        // Update the locked record
                        // به‌روزرسانی رکورد قفل شده
                        $updateSucceeded = $lockedBooking->update(array_merge($updateData, ['main_service_id' => $mainServiceId]));
                    }
                } else {
                    // If main_service_id is already set, only update credit amount
                    // اگر main_service_id قبلاً تنظیم شده است، فقط مبلغ اعتبار را به‌روزرسانی کنید
                    // Re-fetch with lockForUpdate to acquire lock, then update
                    // واکشی مجدد با lockForUpdate برای به دست آوردن lock، سپس به‌روزرسانی
                    $lockedBooking = $this->booking->where('id', $consultationBooking->id)
                        ->lockForUpdate()
                        ->first();
                    
                    if ($lockedBooking) {
                        // Update the locked record
                        // به‌روزرسانی رکورد قفل شده
                        $updateSucceeded = $lockedBooking->update($updateData);
                    }
                }
                
                // Only decrement remaining credit if update actually succeeded
                // فقط در صورت موفقیت واقعی به‌روزرسانی، اعتبار باقی‌مانده را کاهش دهید
                if ($updateSucceeded) {
                    $remainingCredit -= $appliedAmount;
                } else {
                    // Update failed - check if booking was already updated by another process
                    // به‌روزرسانی ناموفق - بررسی اینکه آیا رزرو قبلاً توسط فرآیند دیگری به‌روزرسانی شده است
                    // Re-fetch to check current state (without lock since we're checking, not updating)
                    // واکشی مجدد برای بررسی وضعیت فعلی (بدون lock چون در حال بررسی هستیم، نه به‌روزرسانی)
                    $currentBooking = $this->booking->find($consultationBooking->id);
                    
                    if ($currentBooking && $currentBooking->consultation_credit_amount > 0) {
                        // Booking was already updated by another process - skip it
                        // رزرو قبلاً توسط فرآیند دیگری به‌روزرسانی شده است - رد کردن آن
                        // Don't count its credit since it was already applied elsewhere
                        // اعتبار آن را شمارش نکنید چون قبلاً در جای دیگری اعمال شده است
                        \Log::info('Consultation booking already processed by another request', [
                            'consultation_booking_id' => $consultationBooking->id,
                            'existing_credit_amount' => $currentBooking->consultation_credit_amount,
                        ]);
                        // Skip this booking - don't count its credit
                        // رد کردن این رزرو - اعتبار آن را شمارش نکنید
                        continue;
                    } else {
                        // Update failed but booking still has credit_amount = 0
                        // به‌روزرسانی ناموفق اما رزرو هنوز credit_amount = 0 دارد
                        // This is a real problem - log error and skip to prevent double application
                        // این یک مشکل واقعی است - ثبت خطا و رد کردن برای جلوگیری از اعمال دوباره
                        \Log::error('Consultation booking update failed - booking remains unmarked', [
                            'consultation_booking_id' => $consultationBooking->id,
                            'main_service_id' => $mainServiceId,
                            'applied_amount' => $appliedAmount,
                            'current_credit_amount' => $currentBooking?->consultation_credit_amount ?? 0,
                        ]);
                        // Skip this booking to prevent double application of credit
                        // رد کردن این رزرو برای جلوگیری از اعمال دوباره اعتبار
                        continue;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to mark consultation booking credit as applied', [
                    'consultation_booking_id' => $consultationBooking->id,
                    'main_service_id' => $mainServiceId,
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
                // Continue with next booking - don't fail entire process
                // ادامه با رزرو بعدی - کل فرآیند را fail نکنید
            }
        }
    }
    
    /**
     * Calculate additional services (cross-sell/upsell) amounts
     * محاسبه مبالغ خدمات اضافی (فروش متقابل/افزایش فروش)
     *
     * @param int $salonId The salon ID to validate services belong to
     * @param array $bookingData
     * @return array
     */
    private function calculateAdditionalServices(int $salonId, array $bookingData): array
    {
        if (!isset($bookingData['additional_services']) || !is_array($bookingData['additional_services']) || empty($bookingData['additional_services'])) {
            return [
                'services' => [],
                'total_amount' => 0.0,
                'commission' => 0.0,
            ];
        }
        
        $services = [];
        $totalAmount = 0.0;
        $totalCommission = 0.0;
        $commissionPercentage = config('beautybooking.cross_selling.commission_percentage', 10.0);
        
        foreach ($bookingData['additional_services'] as $additionalServiceData) {
            if (!isset($additionalServiceData['service_id'])) {
                continue;
            }
            
            $service = BeautyService::find($additionalServiceData['service_id']);
            if (!$service || !$service->status) {
                continue;
            }
            
            // Verify service belongs to same salon
            // تأیید اینکه خدمت متعلق به همان سالن است
            if ($service->salon_id != $salonId) {
                continue;
            }
            
            $servicePrice = $service->price;
            $serviceCommission = $servicePrice * ($commissionPercentage / 100);
            
            $services[] = [
                'service_id' => $service->id,
                'name' => $service->name,
                'price' => $servicePrice,
                'duration_minutes' => $service->duration_minutes,
            ];
            
            $totalAmount += $servicePrice;
            $totalCommission += $serviceCommission;
        }
        
        return [
            'services' => $services,
            'total_amount' => $totalAmount,
            'commission' => $totalCommission,
        ];
    }
    
    /**
     * Calculate discount (coupons, packages, etc.)
     * محاسبه تخفیف (کوپن، پکیج و غیره)
     *
     * Note: Gift cards are not included here because they work through wallet redemption.
     * Users redeem gift cards to their wallet, then use wallet payment for bookings.
     * This provides flexibility and allows users to use gift card balance partially.
     * 
     * توجه: کارت‌های هدیه در اینجا شامل نمی‌شوند چون از طریق redeem به wallet کار می‌کنند.
     * کاربران کارت هدیه را به wallet خود redeem می‌کنند، سپس از wallet payment برای رزرو استفاده می‌کنند.
     * این انعطاف‌پذیری را فراهم می‌کند و به کاربران اجازه می‌دهد از موجودی کارت هدیه به صورت جزئی استفاده کنند.
     *
     * @param int $salonId
     * @param array $bookingData
     * @param float $totalBasePrice Total base price including additional services (for consistency with fees and taxes)
     * @return float
     */
    private function calculateDiscount(int $salonId, array $bookingData, float $totalBasePrice): float
    {
        $totalDiscount = 0.0;
        
        // Calculate coupon discount if coupon code provided
        // محاسبه تخفیف کوپن در صورت ارائه کد کوپن
        if (isset($bookingData['coupon_code']) && !empty($bookingData['coupon_code'])) {
            $couponDiscount = $this->calculateCouponDiscount(
                $bookingData['coupon_code'],
                $salonId,
                $bookingData['user_id'] ?? null,
                $totalBasePrice
            );
            $totalDiscount += $couponDiscount;
        }
        
        // Calculate package discount if package ID provided
        // محاسبه تخفیف پکیج در صورت ارائه شناسه پکیج
        if (isset($bookingData['package_id']) && !empty($bookingData['package_id'])) {
            $packageDiscount = $this->calculatePackageDiscount(
                $bookingData['package_id'],
                $salonId,
                $totalBasePrice
            );
            $totalDiscount += $packageDiscount;
        }
        
        // Ensure discount doesn't exceed total base price (including additional services)
        // اطمینان از اینکه تخفیف از قیمت پایه کل (شامل خدمات اضافی) تجاوز نمی‌کند
        if ($totalDiscount > $totalBasePrice) {
            $totalDiscount = $totalBasePrice;
        }
        
        return max(0.0, $totalDiscount);
    }
    
    /**
     * Calculate coupon discount
     * محاسبه تخفیف کوپن
     *
     * @param string $couponCode
     * @param int $salonId
     * @param int|null $userId
     * @param float $totalBasePrice Total base price including additional services
     * @return float
     */
    private function calculateCouponDiscount(string $couponCode, int $salonId, ?int $userId, float $totalBasePrice): float
    {
        try {
            $coupon = Coupon::where('code', $couponCode)
                ->where('status', 1)
                ->first();
            
            if (!$coupon) {
                return 0.0;
            }
            
            // Get salon's store_id for coupon validation
            // دریافت store_id سالن برای اعتبارسنجی کوپن
            $salon = BeautySalon::find($salonId);
            if (!$salon || !$salon->store_id) {
                return 0.0;
            }
            
            // Validate coupon
            // اعتبارسنجی کوپن
            // Safely access config array to avoid TypeError when config returns null
            // دسترسی ایمن به آرایه config برای جلوگیری از TypeError زمانی که config مقدار null برمی‌گرداند
            $moduleData = config('module.current_module_data');
            $moduleId = (is_array($moduleData) && isset($moduleData['id'])) ? $moduleData['id'] : null;
            if ($userId) {
                $validationResult = CouponLogic::is_valide($coupon, $userId, $salon->store_id, $moduleId);
            } else {
                $validationResult = CouponLogic::is_valid_for_guest($coupon, $salon->store_id, $moduleId);
            }
            
            // 200 means valid, other codes mean invalid
            // 200 یعنی معتبر است، کدهای دیگر یعنی نامعتبر
            if ($validationResult !== 200) {
                return 0.0;
            }
            
            // Calculate discount amount on total base price (including additional services)
            // محاسبه مبلغ تخفیف بر اساس قیمت پایه کل (شامل خدمات اضافی)
            return (float) CouponLogic::get_discount($coupon, $totalBasePrice);
            
        } catch (\Exception $e) {
            \Log::error('Error calculating coupon discount', [
                'coupon_code' => $couponCode,
                'error' => $e->getMessage(),
            ]);
            return 0.0;
        }
    }
    
    /**
     * Calculate package discount
     * محاسبه تخفیف پکیج
     *
     * @param int $packageId
     * @param int $salonId
     * @param float $totalBasePrice Total base price including additional services
     * @return float
     */
    private function calculatePackageDiscount(int $packageId, int $salonId, float $totalBasePrice): float
    {
        try {
            $package = BeautyPackage::where('id', $packageId)
                ->where('salon_id', $salonId)
                ->where('status', true)
                ->first();
            
            if (!$package) {
                return 0.0;
            }
            
            // Check if package is valid for user (not expired, has remaining sessions, etc.)
            // بررسی معتبر بودن پکیج برای کاربر (منقضی نشده، جلسات باقیمانده دارد و غیره)
            // Note: userId should be passed from bookingData, but for safety we validate here too
            // توجه: userId باید از bookingData ارسال شود، اما برای اطمینان اینجا هم اعتبارسنجی می‌کنیم
            // However, since this is called from calculateDiscount which doesn't have userId,
            // we rely on the validation in createBooking() before calling calculateDiscount
            // با این حال، چون این از calculateDiscount فراخوانی می‌شود که userId ندارد،
            // به اعتبارسنجی در createBooking() قبل از فراخوانی calculateDiscount اعتماد می‌کنیم
            
            // Calculate discount based on discount_percentage on total base price (including additional services)
            // محاسبه تخفیف بر اساس discount_percentage بر روی قیمت پایه کل (شامل خدمات اضافی)
            if ($package->discount_percentage > 0 && $package->discount_percentage <= 100) {
                return $totalBasePrice * ($package->discount_percentage / 100);
            }
            
            return 0.0;
            
        } catch (\Exception $e) {
            \Log::error('Error calculating package discount', [
                'package_id' => $packageId,
                'error' => $e->getMessage(),
            ]);
            return 0.0;
        }
    }
    
    /**
     * Process payment for booking
     * پردازش پرداخت برای رزرو
     *
     * @param BeautyBooking $booking
     * @param string $paymentMethod
     * @param array $paymentData Additional payment data (gateway, callback_url, etc.)
     * @return bool|string Returns bool for wallet/cash, redirect URL for digital payment
     * @throws \Exception
     */
    public function processPayment(BeautyBooking $booking, string $paymentMethod, array $paymentData = []): bool|string
    {
        try {
            switch ($paymentMethod) {
                case 'wallet':
                    return $this->processWalletPayment($booking);
                case 'digital_payment':
                    return $this->processDigitalPayment($booking, $paymentData);
                case 'cash_payment':
                    return $this->processCashPayment($booking);
                default:
                    throw new \Exception(translate('messages.invalid_payment_method'));
            }
        } catch (\Exception $e) {
            \Log::error('Payment processing failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'payment_method' => $paymentMethod,
            ]);
            throw $e;
        }
    }
    
    /**
     * Process wallet payment
     * پردازش پرداخت از کیف پول
     *
     * @param BeautyBooking $booking
     * @return bool
     * @throws \Exception
     */
    private function processWalletPayment(BeautyBooking $booking): bool
    {
        try {
            // Check wallet balance before processing
            // بررسی موجودی کیف پول قبل از پردازش
            $user = $booking->user;
            if (!$user) {
                throw new \Exception(translate('messages.user_not_found'));
            }
            
            if ($user->wallet_balance < $booking->total_amount) {
                throw new \Exception(translate('messages.insufficient_wallet_balance'));
            }
            
            // Use existing wallet system
            // استفاده از سیستم کیف پول موجود
            // Note: 'beauty_booking' transaction type is supported for wallet debits
            // توجه: نوع تراکنش 'beauty_booking' برای کسر از کیف پول پشتیبانی می‌شود
            // This provides proper transaction categorization for beauty booking payments
            // این دسته‌بندی صحیح تراکنش را برای پرداخت‌های رزرو زیبایی فراهم می‌کند
            $walletTransactionResult = CustomerLogic::create_wallet_transaction(
                $booking->user_id,
                $booking->total_amount, // Positive amount - CustomerLogic will handle debit
                'beauty_booking', // Use 'beauty_booking' for proper transaction categorization
                $booking->id
            );
            
            // Check if wallet transaction succeeded
            // بررسی اینکه آیا تراکنش کیف پول موفق شده است
            // CustomerLogic::create_wallet_transaction() returns false on failure, transaction object or true on success
            // CustomerLogic::create_wallet_transaction() در صورت شکست false برمی‌گرداند، در صورت موفقیت شیء تراکنش یا true برمی‌گرداند
            if ($walletTransactionResult === false) {
                \Log::error('Wallet payment transaction failed', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'user_id' => $booking->user_id,
                    'amount' => $booking->total_amount,
                ]);
                throw new \Exception(translate('messages.wallet_transaction_failed'));
            }
            
            $booking->update([
                'payment_status' => 'paid',
                'payment_method' => 'wallet',
            ]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Wallet payment failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'amount' => $booking->total_amount,
            ]);
            throw $e;
        }
    }
    
    /**
     * Process digital payment
     * پردازش پرداخت دیجیتال
     *
     * @param BeautyBooking $booking
     * @param array $paymentData
     * @return string Returns redirect URL
     * @throws \Exception On failure (throws exception instead of returning false for consistency)
     */
    private function processDigitalPayment(BeautyBooking $booking, array $paymentData = []): string
    {
        try {
            $user = $booking->user;
            if (!$user) {
                throw new \Exception(translate('messages.user_not_found'));
            }
            
            $paymentGateway = $paymentData['payment_gateway'] ?? 'stripe';
            $callbackUrl = $paymentData['callback_url'] ?? null;
            $paymentPlatform = $paymentData['payment_platform'] ?? 'web';
            
            $payer = new Payer(
                (string) (($user->f_name ?? '') . ' ' . ($user->l_name ?? '')),
                (string) ($user->email ?? ''),
                (string) ($user->phone ?? ''),
                ''
            );
            
            $storeLogo = BusinessSetting::where(['key' => 'logo'])->first();
            // Fix: Use ->first() method instead of [0] array access to safely handle null storage
            // رفع: استفاده از متد ->first() به جای دسترسی آرایه [0] برای مدیریت ایمن null storage
            // When $storeLogo is null, $storeLogo?->storage returns null, and [0] would cause an error
            // زمانی که $storeLogo null است، $storeLogo?->storage null برمی‌گرداند و [0] باعث خطا می‌شود
            $storageValue = $storeLogo?->storage?->first()?->value ?? 'public';
            $additionalData = [
                'business_name' => BusinessSetting::where(['key' => 'business_name'])->first()?->value,
                'business_logo' => Helpers::get_full_url('business', $storeLogo?->value, $storageValue)
            ];
            
            $paymentInfo = new PaymentInfo(
                success_hook: 'beauty_booking_payment_success',
                failure_hook: 'beauty_booking_payment_fail',
                currency_code: Helpers::currency_code(),
                payment_method: $paymentGateway,
                payment_platform: $paymentPlatform,
                payer_id: $booking->user_id,
                receiver_id: 1,
                additional_data: $additionalData,
                payment_amount: $booking->total_amount,
                external_redirect_link: $callbackUrl,
                attribute: 'beauty_booking',
                attribute_id: $booking->id,
            );
            
            $receiverInfo = new Receiver('Admin', 'example.png');
            $redirectLink = Payment::generate_link($payer, $paymentInfo, $receiverInfo);
            
            // Update booking with payment method (but not status yet - will be updated on callback)
            $booking->update(['payment_method' => 'digital_payment']);
            
            return $redirectLink;
        } catch (\Exception $e) {
            \Log::error('Digital payment processing failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'payment_data' => $paymentData,
            ]);
            throw new \Exception(translate('messages.payment_failed'));
        }
    }
    
    /**
     * Process cash payment
     * پردازش پرداخت نقدی
     *
     * @param BeautyBooking $booking
     * @return bool
     */
    private function processCashPayment(BeautyBooking $booking): bool
    {
        // Cash payment - mark as unpaid, will be paid on arrival
        // پرداخت نقدی - به عنوان پرداخت نشده علامت بزنید، هنگام ورود پرداخت می‌شود
        $booking->update([
            'payment_status' => 'unpaid',
            'payment_method' => 'cash_payment',
        ]);
        return true;
    }
    
    /**
     * Cancel booking
     * لغو رزرو
     *
     * @param BeautyBooking $booking
     * @param string|null $reason
     * @param string $cancelledBy Who is cancelling: 'customer', 'salon', 'admin', 'none'
     * @return BeautyBooking
     */
    public function cancelBooking(BeautyBooking $booking, ?string $reason = null, string $cancelledBy = 'customer'): BeautyBooking
    {
        try {
            // Calculate cancellation fee based on who is cancelling
            // محاسبه هزینه لغو بر اساس اینکه چه کسی لغو می‌کند
            // If vendor/salon cancels, customer gets full refund (no fee)
            // اگر فروشنده/سالن لغو کند، مشتری بازگشت وجه کامل دریافت می‌کند (بدون جریمه)
            $cancellationFee = 0.0;
            if ($cancelledBy === 'customer') {
                // Customer cancellation: apply fee based on time
                // لغو مشتری: اعمال جریمه بر اساس زمان
                $cancellationFee = $this->calculateCancellationFee($booking);
            } elseif ($cancelledBy === 'admin') {
                // Admin cancellation: can choose to apply fee or not (default: no fee for admin)
                // لغو ادمین: می‌تواند انتخاب کند که جریمه اعمال شود یا نه (پیش‌فرض: بدون جریمه برای ادمین)
                $cancellationFee = 0.0;
            }
            // For salon/vendor cancellation: $cancellationFee already set to 0.0
            
            // Update booking
            // به‌روزرسانی رزرو
            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancellation_fee' => $cancellationFee,
                'cancelled_by' => $cancelledBy,
            ]);
            
            // Update salon cancellation statistics
            // به‌روزرسانی آمار لغو سالن
            $this->updateSalonCancellationStats($booking->salon_id);
            
            // Reverse previously recorded revenue if payment was made and revenue was recorded
            // برگرداندن درآمد ثبت شده قبلی در صورت پرداخت و ثبت درآمد
            if ($booking->payment_status === 'paid') {
                try {
                    $this->reverseRevenueForCancelledBooking($booking);
                } catch (\Exception $e) {
                    \Log::error('Failed to reverse revenue for cancelled booking', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't throw - continue with cancellation even if reversal fails
                    // پرتاب نکنید - ادامه لغو حتی اگر برگرداندن شکست بخورد
                }
            }
            
            // Record cancellation fee revenue if fee is charged
            // ثبت درآمد جریمه لغو در صورت اعمال جریمه
            if ($cancellationFee > 0) {
                try {
                    $this->revenueService->recordCancellationFee($booking, $cancellationFee);
                } catch (\Exception $e) {
                    \Log::error('Failed to record cancellation fee', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            // Free the time slot
            // آزاد کردن زمان
            $this->calendarService->unblockTimeSlotForBooking($booking);
            
            // Process refund if payment was made (paid or partially_paid)
            // پردازش بازگشت وجه در صورت پرداخت (paid یا partially_paid)
            if (in_array($booking->payment_status, ['paid', 'partially_paid'], true) && $cancellationFee < $booking->total_amount) {
                $refundAmount = $booking->total_amount - $cancellationFee;
                $this->processRefund($booking, $refundAmount);
            }
            
            // Handle cash payment cancellations
            // مدیریت لغوهای پرداخت نقدی
            // For unpaid cash bookings: cancellation fee is still recorded (customer owes it)
            // برای رزروهای نقدی پرداخت نشده: جریمه لغو همچنان ثبت می‌شود (مشتری بدهکار است)
            // For paid cash bookings: refund is processed above (if cancellation fee < total)
            // برای رزروهای نقدی پرداخت شده: بازگشت وجه در بالا پردازش می‌شود (اگر جریمه لغو < کل)
            // Note: If deposits/prepayments are added in the future, handle them here
            // توجه: اگر سپرده/پیش‌پرداخت در آینده اضافه شود، آن‌ها را در اینجا مدیریت کنید
            if ($booking->payment_method === 'cash_payment' && $booking->payment_status === 'unpaid') {
                // Unpaid cash bookings don't require refund processing
                // رزروهای نقدی پرداخت نشده نیاز به پردازش بازگشت وجه ندارند
                // Cancellation fee (if any) is recorded above and customer owes it
                // جریمه لغو (در صورت وجود) در بالا ثبت شده و مشتری بدهکار است
                \Log::info('Unpaid cash payment booking cancelled', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'cancellation_fee' => $cancellationFee,
                    'total_amount' => $booking->total_amount,
                    'note' => 'Customer owes cancellation fee if > 0',
                ]);
            }
            
            // Send notifications
            // ارسال نوتیفیکیشن
            self::sendBookingNotificationToAll($booking->fresh(), 'cancelled');
            
            return $booking->fresh();
        } catch (\Exception $e) {
            \Log::error('Booking cancellation failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'reason' => $reason,
            ]);
            throw $e;
        }
    }
    
    /**
     * Reschedule booking to new date/time
     * تغییر زمان رزرو به تاریخ/زمان جدید
     *
     * @param BeautyBooking $booking
     * @param array $rescheduleData
     * @return BeautyBooking
     * @throws \Exception
     */
    public function rescheduleBooking(BeautyBooking $booking, array $rescheduleData): BeautyBooking
    {
        try {
            return DB::transaction(function () use ($booking, $rescheduleData) {
                $service = BeautyService::findOrFail($booking->service_id);
                
                $newStaffId = $rescheduleData['staff_id'] ?? $booking->staff_id;
                if ($newStaffId) {
                    $staff = BeautyStaff::where('id', $newStaffId)
                        ->where('salon_id', $booking->salon_id)
                        ->where('status', 1)
                        ->first();
                    
                    if (!$staff) {
                        throw new \Exception(translate('messages.staff_not_found_or_inactive'));
                    }
                    
                    // Ensure staff can perform the service if pivot exists
                    // اطمینان از اینکه کارمند می‌تواند خدمت را انجام دهد در صورت وجود pivot
                    $hasServiceStaffAssignments = \DB::table('beauty_service_staff')
                        ->where('service_id', $service->id)
                        ->exists();
                    if ($hasServiceStaffAssignments) {
                        $canPerformService = \DB::table('beauty_service_staff')
                            ->where('service_id', $service->id)
                            ->where('staff_id', $newStaffId)
                            ->exists();
                        if (!$canPerformService) {
                            throw new \Exception(translate('messages.staff_cannot_perform_service'));
                        }
                    }
                }
                
                // Check availability before updating
                // بررسی دسترسی قبل از به‌روزرسانی
                if (!$this->calendarService->isTimeSlotAvailable(
                    $booking->salon_id,
                    $newStaffId,
                    $rescheduleData['booking_date'],
                    $rescheduleData['booking_time'],
                    $service->duration_minutes
                )) {
                    throw new \Exception(translate('messages.time_slot_not_available'));
                }
                
                $bookingDateTime = $this->validateAndParseBookingDateTime(
                    $rescheduleData['booking_date'],
                    $rescheduleData['booking_time']
                );
                
                // Free previous slot
                // آزاد کردن زمان قبلی
                $this->calendarService->unblockTimeSlotForBooking($booking);
                
                // Update booking
                // به‌روزرسانی رزرو
                $booking->update([
                    'booking_date' => $rescheduleData['booking_date'],
                    'booking_time' => $rescheduleData['booking_time'],
                    'booking_date_time' => $bookingDateTime,
                    'staff_id' => $newStaffId,
                    'notes' => $rescheduleData['notes'] ?? $booking->notes,
                ]);
                
                // Block new slot
                // بلاک کردن زمان جدید
                $this->calendarService->blockTimeSlot(
                    $booking->salon_id,
                    $newStaffId,
                    $rescheduleData['booking_date'],
                    $rescheduleData['booking_time'],
                    Carbon::parse($rescheduleData['booking_date'] . ' ' . $rescheduleData['booking_time'])
                        ->addMinutes($service->duration_minutes)
                        ->format('H:i:s'),
                    'manual_block',
                    'Rescheduled Booking #' . ($booking->booking_reference ?? $booking->id)
                );
                
                // Send notification outside of this service via controller
                // ارسال نوتیفیکیشن در کنترلر انجام می‌شود
                return $booking->fresh(['salon.store', 'service', 'staff']);
            });
        } catch (\Exception $e) {
            \Log::error('Booking reschedule failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'salon_id' => $booking->salon_id,
                'user_id' => $booking->user_id,
                'data' => $rescheduleData,
            ]);
            throw $e;
        }
    }
    
    /**
     * Update salon cancellation statistics
     * به‌روزرسانی آمار لغو سالن
     *
     * @param int $salonId
     * @return void
     */
    private function updateSalonCancellationStats(int $salonId): void
    {
        $salon = BeautySalon::findOrFail($salonId);
        
        // Count total bookings (include all statuses: pending, confirmed, completed, cancelled)
        // شمارش کل رزروها (شامل تمام وضعیت‌ها: pending، confirmed، completed، cancelled)
        // This gives a more accurate cancellation rate
        // این نرخ لغو دقیق‌تری می‌دهد
        $totalBookings = BeautyBooking::where('salon_id', $salonId)
            ->count();
        
        // Count cancellations
        // شمارش لغوها
        $totalCancellations = BeautyBooking::where('salon_id', $salonId)
            ->where('status', 'cancelled')
            ->count();
        
        // Calculate cancellation rate
        // محاسبه نرخ لغو
        // Rate = (Cancelled / Total) * 100
        // نرخ = (لغو شده / کل) * 100
        $cancellationRate = $totalBookings > 0 
            ? ($totalCancellations / $totalBookings) * 100 
            : 0.0;
        
        // Update salon
        // به‌روزرسانی سالن
        $salon->update([
            'total_cancellations' => $totalCancellations,
            'cancellation_rate' => round($cancellationRate, 2),
        ]);
        
        // Recalculate badges (cancellation rate affects Top Rated badge)
        // محاسبه مجدد نشان‌ها (نرخ لغو بر نشان Top Rated تأثیر می‌گذارد)
        $badgeService = app(\Modules\BeautyBooking\Services\BeautyBadgeService::class);
        $badgeService->calculateAndAssignBadges($salonId);
    }
    
    /**
     * Calculate cancellation fee
     * محاسبه هزینه لغو
     *
     * @param BeautyBooking $booking
     * @return float
     */
    public function calculateCancellationFee(BeautyBooking $booking): float
    {
        // Parse booking date and time correctly
        // تجزیه صحیح تاریخ و زمان رزرو
        // booking_date is cast as 'date' so it's already a Carbon date object
        // booking_date به عنوان 'date' cast شده است پس قبلاً یک شیء Carbon date است
        // Format it properly to avoid double time specification
        // آن را به درستی فرمت کنید تا از مشخص‌سازی دوباره زمان جلوگیری شود
        if ($booking->booking_date_time) {
            // Use booking_date_time if available (already combined)
            // استفاده از booking_date_time در صورت موجود بودن (قبلاً ترکیب شده)
            $bookingDateTime = Carbon::parse($booking->booking_date_time);
        } else {
            // Fallback: combine booking_date and booking_time
            // جایگزین: ترکیب booking_date و booking_time
            // Validate that both date and time are available before concatenation
            // اعتبارسنجی اینکه هم تاریخ و هم زمان موجود هستند قبل از concatenation
            if (empty($booking->booking_date)) {
                throw new \Exception(translate('messages.booking_date_time_required'));
            }
            
            if (empty($booking->booking_time)) {
                throw new \Exception(translate('messages.booking_date_time_required'));
            }
            
            $bookingDate = $booking->booking_date instanceof Carbon 
                ? $booking->booking_date->format('Y-m-d')
                : (string)$booking->booking_date;
            
            // Ensure booking_time is a string and not empty after trimming
            // اطمینان از اینکه booking_time یک رشته است و پس از trim خالی نیست
            $bookingTime = trim((string)$booking->booking_time);
            if (empty($bookingTime)) {
                throw new \Exception(translate('messages.booking_date_time_required'));
            }
            
            $bookingDateTime = Carbon::parse($bookingDate . ' ' . $bookingTime);
        }
        
        // Get cancellation fee settings from config
        // دریافت تنظیمات جریمه لغو از config
        // All values are configurable via config/beautybooking.php
        // تمام مقادیر از طریق config/beautybooking.php قابل پیکربندی هستند
        // Fallback values match config defaults for safety
        // مقادیر fallback با پیش‌فرض‌های config مطابقت دارند برای ایمنی
        $cancellationConfig = config('beautybooking.cancellation_fee', []);
        $timeThresholds = $cancellationConfig['time_thresholds'] ?? [];
        $feePercentages = $cancellationConfig['fee_percentages'] ?? [];
        
        // Time thresholds (in hours before booking)
        // آستانه‌های زمانی (به ساعت قبل از رزرو)
        // Default: 24 hours (no fee), 2 hours (partial fee threshold)
        // پیش‌فرض: 24 ساعت (بدون جریمه)، 2 ساعت (آستانه جریمه جزئی)
        $noFeeHours = $timeThresholds['no_fee_hours'] ?? config('beautybooking.cancellation_fee.time_thresholds.no_fee_hours', 24);
        $partialFeeHours = $timeThresholds['partial_fee_hours'] ?? config('beautybooking.cancellation_fee.time_thresholds.partial_fee_hours', 2);
        
        // Fee percentages (0-100%)
        // درصدهای جریمه (0-100٪)
        // Default: 0% (24+ hours), 50% (2-24 hours), 100% (< 2 hours)
        // پیش‌فرض: 0٪ (24+ ساعت)، 50٪ (2-24 ساعت)، 100٪ (< 2 ساعت)
        $noFeePercent = $feePercentages['no_fee'] ?? config('beautybooking.cancellation_fee.fee_percentages.no_fee', 0.0);
        $partialFeePercent = $feePercentages['partial'] ?? config('beautybooking.cancellation_fee.fee_percentages.partial', 50.0);
        $fullFeePercent = $feePercentages['full'] ?? config('beautybooking.cancellation_fee.fee_percentages.full', 100.0);
        
        // If booking is in the past, apply full fee
        // اگر رزرو در گذشته است، جریمه کامل اعمال شود
        // Use explicit date comparison instead of relying on diffInHours sign
        // استفاده از مقایسه صریح تاریخ به جای تکیه بر علامت diffInHours
        if ($bookingDateTime->isPast()) {
            return $booking->total_amount * ($fullFeePercent / 100);
        }
        
        // Calculate hours until booking (always positive for future bookings)
        // محاسبه ساعت تا رزرو (همیشه مثبت برای رزروهای آینده)
        $hoursUntilBooking = now()->diffInHours($bookingDateTime, true);
        
        if ($hoursUntilBooking >= $noFeeHours) {
            return $booking->total_amount * ($noFeePercent / 100);
        } elseif ($hoursUntilBooking >= $partialFeeHours) {
            return $booking->total_amount * ($partialFeePercent / 100);
        } else {
            return $booking->total_amount * ($fullFeePercent / 100);
        }
    }
    
    /**
     * Record revenue for a confirmed and paid booking (shared method to prevent duplication)
     * ثبت درآمد برای رزرو تأیید شده و پرداخت شده (متد مشترک برای جلوگیری از تکرار)
     *
     * This method ensures revenue is only recorded once, even if called from multiple places
     * این متد اطمینان می‌دهد که درآمد فقط یک بار ثبت می‌شود، حتی اگر از چندین مکان فراخوانی شود
     *
     * @param BeautyBooking $booking
     * @return void
     */
    private function recordRevenueIfNotRecorded(BeautyBooking $booking): void
    {
        // Lock the booking row first to prevent concurrent access
        // ابتدا ردیف رزرو را قفل کنید تا از دسترسی همزمان جلوگیری شود
        $booking = BeautyBooking::lockForUpdate()->find($booking->id);
        
        if (!$booking) {
            return;
        }
        
        // IMPORTANT: Don't use exists() checks with lockForUpdate() - they don't lock anything if no rows exist
        // مهم: از بررسی‌های exists() با lockForUpdate() استفاده نکنید - اگر هیچ ردیفی وجود نداشته باشد، چیزی را قفل نمی‌کنند
        // Instead, rely on unique constraint + createTransactionSafely() to handle duplicates atomically
        // در عوض، به محدودیت یکتا + createTransactionSafely() تکیه کنید تا تکرارها را به صورت اتمی مدیریت کند
        // The unique constraint on (booking_id, transaction_type) prevents duplicates at database level
        // محدودیت یکتا روی (booking_id, transaction_type) از تکرارها در سطح دیتابیس جلوگیری می‌کند
        // createTransactionSafely() catches duplicate key exceptions and returns existing transaction
        // createTransactionSafely() استثناهای کلید تکراری را می‌گیرد و تراکنش موجود را برمی‌گرداند
        
        // Record all revenue types - duplicates are handled by unique constraint + createTransactionSafely
        // ثبت تمام انواع درآمد - تکرارها توسط محدودیت یکتا + createTransactionSafely مدیریت می‌شوند
        $this->revenueService->recordCommission($booking);
        $this->revenueService->recordServiceFee($booking);
        
        // Record package sale revenue if booking uses a package
        // ثبت درآمد فروش پکیج در صورت استفاده از پکیج
        if ($booking->package_id) {
            $package = BeautyPackage::find($booking->package_id);
            if ($package) {
                // No exists() check needed - createTransactionSafely handles duplicates
                // بررسی exists() لازم نیست - createTransactionSafely تکرارها را مدیریت می‌کند
                $this->revenueService->recordPackageSale($package, $booking);
            }
        }
        
        // Record consultation fee if this is a consultation booking
        // ثبت هزینه مشاوره در صورت اینکه این رزرو مشاوره باشد
        $service = \Modules\BeautyBooking\Entities\BeautyService::find($booking->service_id);
        if ($service && $service->isConsultation()) {
            // No exists() check needed - createTransactionSafely handles duplicates
            // بررسی exists() لازم نیست - createTransactionSafely تکرارها را مدیریت می‌کند
            $this->revenueService->recordConsultationFee($booking);
        }
        
        // Record cross-selling revenue if additional services were purchased
        // ثبت درآمد فروش متقابل در صورت خرید خدمات اضافی
        if ($booking->additional_services && is_array($booking->additional_services) && count($booking->additional_services) > 0) {
            // No exists() check needed - createTransactionSafely handles duplicates
            // بررسی exists() لازم نیست - createTransactionSafely تکرارها را مدیریت می‌کند
            $this->revenueService->recordCrossSellingRevenue($booking, $booking->additional_services);
        }
        
        // Update vendor and admin wallets (similar to OrderLogic::create_transaction)
        // به‌روزرسانی کیف پول فروشنده و ادمین (مشابه OrderLogic::create_transaction)
        // This ensures disbursements include beauty booking earnings
        // این اطمینان می‌دهد که disbursement ها شامل درآمدهای رزرو زیبایی می‌شوند
        $this->updateVendorAndAdminWallets($booking);
    }
    
    /**
     * Update payment status
     * به‌روزرسانی وضعیت پرداخت
     *
     * @param BeautyBooking $booking
     * @param string $paymentStatus
     * @return BeautyBooking
     */
    public function updatePaymentStatus(BeautyBooking $booking, string $paymentStatus): BeautyBooking
    {
        try {
            $oldPaymentStatus = $booking->payment_status;
            
            // Validate payment status transition
            // اعتبارسنجی انتقال وضعیت پرداخت
            if (!$this->validatePaymentStatusTransition($oldPaymentStatus, $paymentStatus)) {
                throw new \Exception(
                    sprintf(
                        'Invalid payment status transition from "%s" to "%s" for booking #%s',
                        $oldPaymentStatus,
                        $paymentStatus,
                        $booking->booking_reference
                    )
                );
            }
            
            // If payment status changed to paid and booking is confirmed, record revenue
            // اگر وضعیت پرداخت به paid تغییر کرد و رزرو confirmed است، درآمد را ثبت کنید
            if ($paymentStatus === 'paid' && $oldPaymentStatus !== 'paid' && $booking->status === 'confirmed') {
                // Wrap payment status update AND revenue recording in a single transaction to ensure atomicity
                // قرار دادن به‌روزرسانی وضعیت پرداخت و ثبت درآمد در یک transaction برای اطمینان از atomicity
                // Fixed: Payment status update moved inside transaction to prevent inconsistency
                // اصلاح شده: به‌روزرسانی وضعیت پرداخت به داخل transaction منتقل شد تا از ناسازگاری جلوگیری شود
                // If revenue recording fails, payment status update will also be rolled back
                // اگر ثبت درآمد شکست بخورد، به‌روزرسانی وضعیت پرداخت نیز rollback می‌شود
                return DB::transaction(function () use ($booking, $paymentStatus) {
                    // Update payment status inside transaction
                    // به‌روزرسانی وضعیت پرداخت در داخل transaction
                    $booking->update(['payment_status' => $paymentStatus]);
                    
                    // Refresh booking to get updated payment_status
                    // به‌روزرسانی رزرو برای دریافت payment_status به‌روزرسانی شده
                    $booking->refresh();
                    
                    // Use shared method to record revenue (prevents duplication)
                    // استفاده از متد مشترک برای ثبت درآمد (جلوگیری از تکرار)
                    $this->recordRevenueIfNotRecorded($booking);
                    
                    // Return fresh booking instance after successful transaction
                    // برگرداندن نمونه رزرو تازه پس از transaction موفق
                    return $booking->fresh();
                });
            } else {
                // Payment status change doesn't require revenue recording - update outside transaction
                // تغییر وضعیت پرداخت نیاز به ثبت درآمد ندارد - به‌روزرسانی خارج از transaction
                $booking->update(['payment_status' => $paymentStatus]);
                return $booking->fresh();
            }
        } catch (\Exception $e) {
            \Log::error('Payment status update failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'payment_status' => $paymentStatus,
            ]);
            throw $e;
        }
    }
    
    /**
     * Update booking status
     * به‌روزرسانی وضعیت رزرو
     *
     * @param BeautyBooking $booking
     * @param string $status
     * @return BeautyBooking
     * @throws \Exception
     */
    public function updateBookingStatus(BeautyBooking $booking, string $status): BeautyBooking
    {
        try {
            $oldStatus = $booking->status;
            
            // Validate status transition (with time-based validation)
            // اعتبارسنجی انتقال وضعیت (با اعتبارسنجی مبتنی بر زمان)
            // Try to get booking_date_time, fallback to constructing from booking_date and booking_time
            // تلاش برای دریافت booking_date_time، در صورت عدم وجود از booking_date و booking_time بسازید
            $bookingDateTime = $booking->booking_date_time ?? null;
            if ($bookingDateTime === null && ($status === 'no_show' || $status === 'completed')) {
                // For time-dependent statuses, try to construct from booking_date and booking_time
                // برای وضعیت‌های وابسته به زمان، تلاش برای ساخت از booking_date و booking_time
                if ($booking->booking_date && $booking->booking_time) {
                    try {
                        $bookingDate = $booking->booking_date instanceof \Carbon\Carbon 
                            ? $booking->booking_date->format('Y-m-d')
                            : (string)$booking->booking_date;
                        $bookingTime = trim((string)$booking->booking_time);
                        if (!empty($bookingDate) && !empty($bookingTime)) {
                            $bookingDateTime = \Carbon\Carbon::parse($bookingDate . ' ' . $bookingTime);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to parse booking date/time for status transition validation', [
                            'booking_id' => $booking->id,
                            'booking_date' => $booking->booking_date,
                            'booking_time' => $booking->booking_time,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
            
            if (!$this->validateStatusTransition($oldStatus, $status, $bookingDateTime)) {
                throw new \Exception(translate('messages.invalid_status_transition', [
                    'from' => $oldStatus,
                    'to' => $status,
                ]));
            }
            
            // If confirmed and payment is paid, wrap status update AND revenue recording in transaction
            // اگر confirmed و پرداخت انجام شده است، به‌روزرسانی وضعیت و ثبت درآمد را در transaction قرار دهید
            if ($status === 'confirmed' && $booking->payment_status === 'paid') {
                // Wrap booking status update AND revenue recording in a single transaction to ensure atomicity
                // قرار دادن به‌روزرسانی وضعیت رزرو و ثبت درآمد در یک transaction برای اطمینان از atomicity
                // Fixed: Booking status update moved inside transaction to prevent inconsistency
                // اصلاح شده: به‌روزرسانی وضعیت رزرو به داخل transaction منتقل شد تا از ناسازگاری جلوگیری شود
                // If revenue recording fails, booking status update will also be rolled back
                // اگر ثبت درآمد شکست بخورد، به‌روزرسانی وضعیت رزرو نیز rollback می‌شود
                $result = DB::transaction(function () use ($booking, $status) {
                    // Update booking status inside transaction
                    // به‌روزرسانی وضعیت رزرو در داخل transaction
                    $booking->update(['status' => $status]);
                    
                    // Refresh booking to get updated status
                    // به‌روزرسانی رزرو برای دریافت وضعیت به‌روزرسانی شده
                    $booking->refresh();
                    
                    // Use shared method to record revenue (prevents duplication)
                    // استفاده از متد مشترک برای ثبت درآمد (جلوگیری از تکرار)
                    $this->recordRevenueIfNotRecorded($booking);
                    
                    // Return fresh booking instance after successful transaction
                    // برگرداندن نمونه رزرو تازه پس از transaction موفق
                    return $booking->fresh();
                });
                
                // Process side effects after transaction (for completed status)
                // پردازش عوارض جانبی پس از transaction (برای وضعیت completed)
                // Fixed: Side effects now execute for all status updates, not just non-revenue ones
                // اصلاح شده: عوارض جانبی اکنون برای تمام به‌روزرسانی‌های وضعیت اجرا می‌شوند، نه فقط موارد غیر درآمدی
                $this->processBookingStatusSideEffects($result, $status, $oldStatus);
                
                // Send notifications for status changes
                // ارسال نوتیفیکیشن برای تغییرات وضعیت
                if ($oldStatus !== $status) {
                    self::sendBookingNotificationToAll($result->fresh(), $status);
                }
                
                return $result;
            } else {
                // Status change doesn't require revenue recording - update outside transaction
                // تغییر وضعیت نیاز به ثبت درآمد ندارد - به‌روزرسانی خارج از transaction
                $booking->update(['status' => $status]);
                
                // Refresh booking to get updated status before processing side effects
                // به‌روزرسانی رزرو برای دریافت وضعیت به‌روزرسانی شده قبل از پردازش عوارض جانبی
                $booking->refresh();
                
                // Process side effects (package usage, loyalty points, time slot freeing)
                // پردازش عوارض جانبی (استفاده از پکیج، امتیاز وفاداری، آزاد کردن زمان)
                // Fixed: Side effects now execute consistently for all status updates
                // اصلاح شده: عوارض جانبی اکنون به طور یکنواخت برای تمام به‌روزرسانی‌های وضعیت اجرا می‌شوند
                $this->processBookingStatusSideEffects($booking, $status, $oldStatus);
                
                // Send notifications for status changes
                // ارسال نوتیفیکیشن برای تغییرات وضعیت
                if ($oldStatus !== $status) {
                    self::sendBookingNotificationToAll($booking->fresh(), $status);
                }
                
                // Return fresh booking instance after status update
                // برگرداندن نمونه رزرو تازه پس از به‌روزرسانی وضعیت
                return $booking->fresh();
            }
        } catch (\Exception $e) {
            \Log::error('Booking status update failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'status' => $status,
            ]);
            throw $e;
        }
    }
    
    /**
     * Track package usage when booking is completed
     * ردیابی استفاده از پکیج هنگام تکمیل رزرو
     *
     * IMPORTANT: Race Condition Prevention
     * مهم: جلوگیری از Race Condition
     * 
     * This method uses database transactions and row-level locks to prevent race conditions
     * این متد از transaction دیتابیس و قفل‌های سطح ردیف برای جلوگیری از race condition استفاده می‌کند
     * when multiple bookings complete simultaneously for the same package.
     * زمانی که چند رزرو همزمان برای همان پکیج تکمیل می‌شوند.
     * 
     * Lock Maintenance:
     * حفظ قفل:
     * - All operations happen within a single DB::transaction() block
     * - تمام عملیات در یک بلوک DB::transaction() انجام می‌شود
     * - Package row is locked with lockForUpdate() at the start
     * - ردیف پکیج با lockForUpdate() در ابتدا قفل می‌شود
     * - All subsequent queries use lockForUpdate() to maintain locks
     * - تمام کوئری‌های بعدی از lockForUpdate() استفاده می‌کنند تا قفل‌ها حفظ شوند
     * - Transaction commits only after all operations complete
     * - Transaction فقط پس از تکمیل تمام عملیات commit می‌شود
     * - This ensures no duplicate session numbers can be created
     * - این اطمینان می‌دهد که هیچ شماره جلسه تکراری ایجاد نمی‌شود
     *
     * @param BeautyBooking $booking
     * @return void
     */
    private function trackPackageUsage(BeautyBooking $booking): void
    {
        // Check if booking uses a package
        // بررسی اینکه آیا رزرو از پکیج استفاده می‌کند
        if (!$booking->package_id) {
            return;
        }
        
        // Use database transaction with row-level locks to prevent race conditions
        // استفاده از transaction دیتابیس با قفل‌های سطح ردیف برای جلوگیری از race condition
        // when multiple bookings complete simultaneously
        // زمانی که چند رزرو همزمان تکمیل می‌شوند
        DB::transaction(function () use ($booking) {
            // STEP 1: Lock the package row to prevent concurrent access
            // مرحله 1: قفل کردن ردیف پکیج برای جلوگیری از دسترسی همزمان
            // This lock is maintained throughout the entire transaction
            // این قفل در طول کل transaction حفظ می‌شود
            $package = BeautyPackage::lockForUpdate()
                ->find($booking->package_id);
            
            // Validate package exists and is active
            // اعتبارسنجی وجود و فعال بودن پکیج
            if (!$package) {
                \Log::warning('Package not found for booking', [
                    'booking_id' => $booking->id,
                    'package_id' => $booking->package_id,
                ]);
                return;
            }
            
            if (!$package->status) {
                \Log::warning('Package is inactive for booking', [
                    'booking_id' => $booking->id,
                    'package_id' => $booking->package_id,
                ]);
                return;
            }
            
            // Validate package belongs to same salon as booking
            // اعتبارسنجی اینکه پکیج متعلق به همان سالن رزرو است
            if ($package->salon_id !== $booking->salon_id) {
                \Log::warning('Package salon mismatch for booking', [
                    'booking_id' => $booking->id,
                    'booking_salon_id' => $booking->salon_id,
                    'package_id' => $package->id,
                    'package_salon_id' => $package->salon_id,
                ]);
                return;
            }
            
            // Check if user has valid package
            // بررسی اینکه آیا کاربر پکیج معتبر دارد
            if (!$package->isValidForUser($booking->user_id)) {
                \Log::warning('Package is not valid for user', [
                    'booking_id' => $booking->id,
                    'package_id' => $package->id,
                    'user_id' => $booking->user_id,
                ]);
                return;
            }
            
            // STEP 2: Check if usage already exists for this booking (with lock)
            // مرحله 2: بررسی اینکه آیا استفاده قبلاً برای این رزرو وجود دارد (با قفل)
            // Use lockForUpdate() to lock existing rows and prevent concurrent inserts
            // استفاده از lockForUpdate() برای قفل کردن ردیف‌های موجود و جلوگیری از درج همزمان
            // This check happens while holding the package lock to ensure atomicity
            // این بررسی در حالی انجام می‌شود که قفل پکیج حفظ شده است تا اتمی بودن تضمین شود
            $existingUsage = BeautyPackageUsage::lockForUpdate()
                ->where('booking_id', $booking->id)
                ->where('package_id', $package->id)
                ->first();
            
            if ($existingUsage) {
                return; // Already tracked - prevent duplicate
            }
            
            // STEP 3: Get next session number with lock to prevent duplicates
            // مرحله 3: دریافت شماره جلسه بعدی با قفل برای جلوگیری از تکرار
            // This query also uses lockForUpdate() and happens while package lock is held
            // این کوئری نیز از lockForUpdate() استفاده می‌کند و در حالی انجام می‌شود که قفل پکیج حفظ شده است
            // Lock is maintained until transaction commits
            // قفل تا commit شدن transaction حفظ می‌شود
            $lastUsage = BeautyPackageUsage::lockForUpdate()
                ->where('package_id', $package->id)
                ->where('user_id', $booking->user_id)
                ->where('status', 'used')
                ->orderByDesc('session_number')
                ->first();
            
            $nextSessionNumber = $lastUsage ? $lastUsage->session_number + 1 : 1;
            
            // STEP 4: Create usage record (still within transaction with locks held)
            // مرحله 4: ایجاد رکورد استفاده (هنوز در transaction با قفل‌های حفظ شده)
            // This creation happens while package lock is still held, preventing race conditions
            // این ایجاد در حالی انجام می‌شود که قفل پکیج هنوز حفظ شده است، از race condition جلوگیری می‌کند
            // Transaction commits here, releasing all locks atomically
            // Transaction در اینجا commit می‌شود، تمام قفل‌ها را به صورت اتمی آزاد می‌کند
            BeautyPackageUsage::create([
                'package_id' => $package->id,
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'salon_id' => $booking->salon_id,
                'session_number' => $nextSessionNumber,
                'used_at' => now(),
                'status' => 'used',
            ]);
            
            // Transaction commits here - all locks are released atomically
            // Transaction در اینجا commit می‌شود - تمام قفل‌ها به صورت اتمی آزاد می‌شوند
        });
    }
    
    /**
     * Process side effects after booking status update
     * پردازش عوارض جانبی پس از به‌روزرسانی وضعیت رزرو
     *
     * Handles package usage tracking, loyalty points, time slot freeing, and statistics updates
     * مدیریت ردیابی استفاده از پکیج، امتیاز وفاداری، آزاد کردن زمان، و به‌روزرسانی آمار
     * Only executes for "completed" status changes
     * فقط برای تغییرات وضعیت "completed" اجرا می‌شود
     *
     * @param BeautyBooking $booking
     * @param string $status
     * @param string $oldStatus
     * @return void
     */
    private function processBookingStatusSideEffects(BeautyBooking $booking, string $status, string $oldStatus): void
    {
        // Only process side effects when status changes to "completed"
        // فقط پردازش عوارض جانبی زمانی که وضعیت به "completed" تغییر می‌کند
        if ($status === 'completed' && $oldStatus !== 'completed') {
            $this->trackPackageUsage($booking);
            
            // Award loyalty points if payment is completed
            // اعطای امتیاز وفاداری در صورت تکمیل پرداخت
            if ($booking->payment_status === 'paid') {
                $loyaltyService = app(\Modules\BeautyBooking\Services\BeautyLoyaltyService::class);
                $loyaltyService->awardPointsForBooking($booking);
            }
            
            // Free the time slot since booking is completed
            // آزاد کردن زمان چون رزرو تکمیل شده است
            $this->calendarService->unblockTimeSlotForBooking($booking);
            
            // Update salon booking statistics
            // به‌روزرسانی آمار رزرو سالن
            $salonService = app(\Modules\BeautyBooking\Services\BeautySalonService::class);
            $salonService->updateBookingStatistics($booking->salon_id);
        }
    }
    
    /**
     * Validate booking status transition
     * اعتبارسنجی انتقال وضعیت رزرو
     *
     * @param string $fromStatus Current status
     * @param string $toStatus Target status
     * @param \Carbon\Carbon|null $bookingDateTime Optional booking date/time for time-based validation
     * @return bool
     */
    private function validateStatusTransition(string $fromStatus, string $toStatus, ?\Carbon\Carbon $bookingDateTime = null): bool
    {
        // If same status, allow (idempotent)
        // اگر همان وضعیت است، اجازه بده (idempotent)
        if ($fromStatus === $toStatus) {
            return true;
        }
        
        // Terminal states cannot transition to other states
        // وضعیت‌های terminal نمی‌توانند به وضعیت‌های دیگر منتقل شوند
        if ($fromStatus === 'completed' || $fromStatus === 'cancelled') {
            // Explicit check: completed and cancelled are terminal
            // بررسی صریح: completed و cancelled terminal هستند
            return false;
        }
        
        // Define allowed transitions
        // تعریف انتقال‌های مجاز
        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled', 'no_show'],
            'completed' => [], // Terminal state - cannot transition from completed
            'cancelled' => [], // Terminal state - cannot transition from cancelled
            // Note: 'no_show' is terminal by default, but can be changed to 'completed' if customer actually shows up
            // توجه: 'no_show' به طور پیش‌فرض terminal است، اما می‌تواند به 'completed' تغییر کند اگر مشتری واقعاً حاضر شود
            'no_show' => ['completed'], // Allow correction if customer actually showed up
        ];
        
        // Check if transition is allowed
        // بررسی اینکه آیا انتقال مجاز است
        if (!in_array($toStatus, $allowedTransitions[$fromStatus] ?? [], true)) {
            return false;
        }
        
        // Time-based validation for edge cases
        // اعتبارسنجی مبتنی بر زمان برای موارد لبه
        // For terminal states that require time validation, we must have booking date/time
        // برای وضعیت‌های terminal که نیاز به اعتبارسنجی زمان دارند، باید تاریخ/زمان رزرو داشته باشیم
        if ($toStatus === 'no_show' || $toStatus === 'completed') {
            if ($bookingDateTime === null) {
                // Cannot transition to time-dependent terminal states without booking date/time
                // نمی‌توان به وضعیت‌های terminal وابسته به زمان بدون تاریخ/زمان رزرو منتقل شد
                \Log::warning('Cannot transition to time-dependent status without booking date/time', [
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                ]);
                return false;
            }
            
            $now = \Carbon\Carbon::now();
            
            // Can only mark as 'no_show' if booking date/time is in the past
            // فقط می‌توان به 'no_show' علامت زد اگر تاریخ/زمان رزرو در گذشته باشد
            if ($toStatus === 'no_show' && $bookingDateTime->isFuture()) {
                \Log::warning('Cannot mark future booking as no_show', [
                    'booking_date_time' => $bookingDateTime->toDateTimeString(),
                    'current_time' => $now->toDateTimeString(),
                ]);
                return false;
            }
            
            // Can only mark as 'completed' if booking date/time is in the past or current
            // فقط می‌توان به 'completed' علامت زد اگر تاریخ/زمان رزرو در گذشته یا حال باشد
            if ($toStatus === 'completed' && $bookingDateTime->isFuture()) {
                \Log::warning('Cannot mark future booking as completed', [
                    'booking_date_time' => $bookingDateTime->toDateTimeString(),
                    'current_time' => $now->toDateTimeString(),
                ]);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate payment status transition
     * اعتبارسنجی انتقال وضعیت پرداخت
     *
     * @param string $fromStatus Current payment status
     * @param string $toStatus Desired payment status
     * @return bool
     */
    private function validatePaymentStatusTransition(string $fromStatus, string $toStatus): bool
    {
        // If same status, allow (idempotent)
        // اگر همان وضعیت است، اجازه بده (idempotent)
        if ($fromStatus === $toStatus) {
            return true;
        }
        
        // Define allowed transitions
        // تعریف انتقال‌های مجاز
        $allowedTransitions = [
            'unpaid' => ['paid', 'partially_paid'],
            'partially_paid' => ['paid', 'refunded', 'refund_pending', 'refund_failed'], // Allow refund transitions for partial payments
            'paid' => ['refunded', 'refund_pending', 'refund_failed'], // Allow direct transition to refund_failed for wallet refund failures
            'refunded' => [], // Terminal state - cannot transition from refunded
            'refund_pending' => ['refunded', 'refund_failed'],
            'refund_failed' => ['refund_pending'], // Can retry refund
        ];
        
        // Check if transition is allowed
        // بررسی اینکه آیا انتقال مجاز است
        return in_array($toStatus, $allowedTransitions[$fromStatus] ?? [], true);
    }
    
    /**
     * Validate and parse booking date/time
     * اعتبارسنجی و تجزیه تاریخ/زمان رزرو
     *
     * @param mixed $bookingDate Booking date (string or Carbon)
     * @param mixed $bookingTime Booking time (string)
     * @return Carbon
     * @throws \Exception If date/time format is invalid or date is in the past
     */
    private function validateAndParseBookingDateTime($bookingDate, $bookingTime): Carbon
    {
        // Validate that both date and time are provided
        // اعتبارسنجی اینکه هم تاریخ و هم زمان ارائه شده‌اند
        if (empty($bookingDate) || empty($bookingTime)) {
            throw new \Exception(translate('messages.booking_date_time_required'));
        }
        
        // Format date if it's a Carbon object
        // فرمت تاریخ در صورت Carbon بودن
        $dateString = $bookingDate instanceof Carbon 
            ? $bookingDate->format('Y-m-d')
            : (string)$bookingDate;
        
        // Ensure time is a string
        // اطمینان از اینکه زمان یک رشته است
        $timeString = (string)$bookingTime;
        
        // Parse the date/time string
        // تجزیه رشته تاریخ/زمان
        $parsedDateTime = null;
        $parseError = null;
        
        try {
            // Expected format: Y-m-d H:i:s or Y-m-d H:i
            // فرمت مورد انتظار: Y-m-d H:i:s یا Y-m-d H:i
            $dateTimeString = $dateString . ' ' . $timeString;
            
            // Try common formats
            // تلاش برای فرمت‌های رایج
            $formats = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d H:i:s.u'];
            
            foreach ($formats as $format) {
                try {
                    $parsedDateTime = Carbon::createFromFormat($format, $dateTimeString);
                    break;
                } catch (\Exception $e) {
                    // Try next format
                    // تلاش برای فرمت بعدی
                    $parseError = $e;
                    continue;
                }
            }
            
            // If all formats failed, try Carbon::parse() as fallback
            // اگر تمام فرمت‌ها شکست خوردند، Carbon::parse() را به عنوان fallback امتحان کنید
            if (!$parsedDateTime) {
                try {
                    $parsedDateTime = Carbon::parse($dateTimeString);
                } catch (\Exception $e) {
                    $parseError = $e;
                }
            }
        } catch (\Exception $e) {
            // Catch any unexpected errors during parsing
            // گرفتن هر خطای غیرمنتظره در طول تجزیه
            $parseError = $e;
        }
        
        // If parsing failed, throw user-friendly error
        // اگر تجزیه شکست خورد، خطای کاربرپسند پرتاب کنید
        if (!$parsedDateTime) {
            throw new \Exception(translate('messages.invalid_date_time_format') . ': ' . $dateString . ' ' . $timeString);
        }
        
        // Validate that date is not in the past
        // اعتبارسنجی اینکه تاریخ در گذشته نیست
        // This validation happens after successful parsing, so we can throw our custom exception
        // این اعتبارسنجی پس از تجزیه موفق انجام می‌شود، بنابراین می‌توانیم exception سفارشی خود را پرتاب کنیم
        if ($parsedDateTime->isPast()) {
            throw new \Exception(translate('messages.booking_date_cannot_be_in_past'));
        }
        
        return $parsedDateTime;
    }
    
    /**
     * Generate unique booking reference
     * تولید شماره مرجع منحصر به فرد رزرو
     *
     * @return string
     */
    private function generateBookingReference(): string
    {
        do {
            $reference = 'BB' . strtoupper(Str::random(8));
        } while ($this->booking->where('booking_reference', $reference)->exists());
        
        return $reference;
    }
    
    /**
     * Create conversation for booking
     * ایجاد گفتگو برای رزرو
     *
     * @param BeautyBooking $booking
     * @param int $userId
     * @param BeautySalon $salon
     * @return \App\Models\Conversation|null
     */
    private function createBookingConversation(BeautyBooking $booking, int $userId, BeautySalon $salon): ?\App\Models\Conversation
    {
        try {
            // Get vendor/store information
            // دریافت اطلاعات فروشنده/فروشگاه
            $store = $salon->store;
            if (!$store || !$store->vendor_id) {
                return null;
            }
            
            // Get or create UserInfo for customer
            // دریافت یا ایجاد UserInfo برای مشتری
            $customer = \App\Models\User::find($userId);
            if (!$customer) {
                return null;
            }
            
            $customerInfo = \App\Models\UserInfo::where('user_id', $userId)->first();
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
            
            // Get or create UserInfo for vendor
            // دریافت یا ایجاد UserInfo برای فروشنده
            $vendor = \App\Models\Vendor::find($store->vendor_id);
            if (!$vendor) {
                return null;
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
                // Create new conversation
                // ایجاد گفتگوی جدید
                $conversation = new \App\Models\Conversation();
                $conversation->sender_id = $customerInfo->id;
                $conversation->sender_type = 'customer';
                $conversation->receiver_id = $vendorInfo->id;
                $conversation->receiver_type = 'vendor';
                $conversation->unread_message_count = 0;
                $conversation->last_message_time = Carbon::now()->toDateTimeString();
                $conversation->save();
            }
            
            return $conversation;
        } catch (\Exception $e) {
            \Log::error('Failed to create booking conversation: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'user_id' => $userId,
                'salon_id' => $salon->id,
            ]);
            return null;
        }
    }
    
    /**
     * Reverse revenue transactions for a cancelled booking
     * برگرداندن تراکنش‌های درآمد برای رزرو لغو شده
     *
     * Creates reversal transactions to offset previously recorded revenue
     * ایجاد تراکنش‌های برگردانی برای جبران درآمد ثبت شده قبلی
     *
     * @param BeautyBooking $booking
     * @return void
     */
    private function reverseRevenueForCancelledBooking(BeautyBooking $booking): void
    {
        // Only reverse if revenue was actually recorded
        // فقط در صورت ثبت واقعی درآمد برگرداندن انجام شود
        // Includes cancellation_fee to handle scenarios where cancellation fee was recorded before reversal
        // شامل cancellation_fee برای مدیریت سناریوهایی که جریمه لغو قبل از برگرداندن ثبت شده است
        $hasRecordedRevenue = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
            ->whereIn('transaction_type', ['commission', 'service_fee', 'package_sale', 'consultation_fee', 'cross_selling', 'cancellation_fee'])
            ->where('status', 'completed')
            ->exists();
        
        if (!$hasRecordedRevenue) {
            return; // No revenue to reverse
        }
        
        // Wrap reversal in transaction for atomicity
        // قرار دادن برگرداندن در transaction برای atomicity
        DB::transaction(function () use ($booking) {
            // Get all revenue transactions for this booking
            // دریافت تمام تراکنش‌های درآمد برای این رزرو
            // Includes cancellation_fee to prevent double-counting when reversing all revenue
            // شامل cancellation_fee برای جلوگیری از شمارش دوباره هنگام برگرداندن تمام درآمد
            $revenueTransactions = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
                ->whereIn('transaction_type', ['commission', 'service_fee', 'package_sale', 'consultation_fee', 'cross_selling', 'cancellation_fee'])
                ->where('status', 'completed')
                ->lockForUpdate()
                ->get();
            
            foreach ($revenueTransactions as $transaction) {
                // Create reversal transaction (negative amounts)
                // ایجاد تراکنش برگردانی (مبالغ منفی)
                \Modules\BeautyBooking\Entities\BeautyTransaction::create([
                    'booking_id' => $booking->id,
                    'salon_id' => $booking->salon_id,
                    'zone_id' => $booking->zone_id,
                    'transaction_type' => $transaction->transaction_type . '_reversal',
                    'amount' => -$transaction->amount,
                    'commission' => -$transaction->commission,
                    'service_fee' => -$transaction->service_fee,
                    'status' => 'completed',
                    'notes' => 'Reversal for cancelled booking #' . $booking->booking_reference . ' - Original transaction ID: ' . $transaction->id,
                ]);
            }
            
            // Reverse wallet updates if wallets were updated
            // برگرداندن به‌روزرسانی‌های کیف پول در صورت به‌روزرسانی کیف پول‌ها
            // Check if wallets were updated by looking for wallet_updated marker
            // بررسی اینکه آیا کیف پول‌ها به‌روزرسانی شده‌اند با جستجوی نشانگر wallet_updated
            $commissionTransaction = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
                ->where('transaction_type', 'commission')
                ->first();
            
            if ($commissionTransaction && $commissionTransaction->notes && str_contains($commissionTransaction->notes, 'wallet_updated')) {
                // Wallets were updated, so reverse them
                // کیف پول‌ها به‌روزرسانی شده‌اند، بنابراین آن‌ها را برگردانید
                $this->reverseVendorAndAdminWallets($booking);
            }
        });
    }
    
    /**
     * Process refund for cancelled booking
     * پردازش بازگشت وجه برای رزرو لغو شده
     *
     * @param BeautyBooking $booking
     * @param float $refundAmount
     * @return void
     */
    private function processRefund(BeautyBooking $booking, float $refundAmount): void
    {
        try {
            // Only refund if payment was made (paid or partially_paid) and amount is positive
            // فقط در صورت پرداخت (paid یا partially_paid) و مبلغ مثبت بازگشت وجه انجام شود
            if (!in_array($booking->payment_status, ['paid', 'partially_paid'], true) || $refundAmount <= 0) {
                return;
            }
            
            // Check if wallet refund is enabled
            // بررسی فعال بودن بازگشت وجه به کیف پول
            $walletStatus = \App\Models\BusinessSetting::where('key', 'wallet_status')->first()?->value ?? 0;
            $walletAddRefund = \App\Models\BusinessSetting::where('key', 'wallet_add_refund')->first()?->value ?? 0;
            
            $refundSuccess = false;
            
            if ($walletStatus == 1 && $walletAddRefund == 1 && $booking->payment_method === 'wallet') {
                // Refund to wallet
                // بازگشت وجه به کیف پول
                $walletTransactionResult = CustomerLogic::create_wallet_transaction(
                    $booking->user_id,
                    $refundAmount,
                    'beauty_booking_refund',
                    $booking->id
                );
                
                // Check if wallet transaction succeeded
                // بررسی اینکه آیا تراکنش کیف پول موفق شده است
                // CustomerLogic::create_wallet_transaction() returns false on failure, true or transaction object on success
                // CustomerLogic::create_wallet_transaction() در صورت شکست false برمی‌گرداند، در صورت موفقیت true یا شیء تراکنش برمی‌گرداند
                $refundSuccess = ($walletTransactionResult !== false);
                
                if (!$refundSuccess) {
                    \Log::error('Wallet refund transaction failed', [
                        'booking_id' => $booking->id,
                        'booking_reference' => $booking->booking_reference,
                        'user_id' => $booking->user_id,
                        'refund_amount' => $refundAmount,
                        'payment_method' => $booking->payment_method,
                    ]);
                } else {
                    \Log::info('Wallet refund transaction succeeded', [
                        'booking_id' => $booking->id,
                        'booking_reference' => $booking->booking_reference,
                        'user_id' => $booking->user_id,
                        'refund_amount' => $refundAmount,
                    ]);
                }
            } elseif ($booking->payment_method === 'digital_payment') {
                // For digital payments, refund requires manual processing through payment gateway
                // برای پرداخت‌های دیجیتال، بازگشت وجه نیاز به پردازش دستی از طریق درگاه پرداخت دارد
                // TODO: Implement automatic payment gateway refund integration
                // TODO: پیاده‌سازی یکپارچه‌سازی خودکار بازگشت وجه درگاه پرداخت
                // This would require:
                // این نیاز دارد:
                // 1. Payment gateway API integration (Stripe, PayPal, etc.)
                // 1. یکپارچه‌سازی API درگاه پرداخت (Stripe، PayPal و غیره)
                // 2. Store payment transaction ID when booking is paid
                // 2. ذخیره شناسه تراکنش پرداخت هنگام پرداخت رزرو
                // 3. Call payment gateway refund API with transaction ID
                // 3. فراخوانی API بازگشت وجه درگاه پرداخت با شناسه تراکنش
                // 4. Handle refund success/failure and update payment status accordingly
                // 4. مدیریت موفقیت/شکست بازگشت وجه و به‌روزرسانی وضعیت پرداخت بر این اساس
                // For now, admin must process refund manually and mark as completed via markRefundCompleted()
                // در حال حاضر، ادمین باید بازگشت وجه را به صورت دستی پردازش کند و از طریق markRefundCompleted() به عنوان تکمیل شده علامت بزند
                \Log::info('Digital payment refund required - manual processing needed', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'user_id' => $booking->user_id,
                    'refund_amount' => $refundAmount,
                    'payment_method' => $booking->payment_method,
                    'action_required' => 'Admin must process refund through payment gateway and mark as completed',
                    'admin_endpoint' => 'BeautyBookingController::markRefundCompleted',
                ]);
                
                // Payment status will be set to 'refund_pending' below
                // وضعیت پرداخت در زیر به 'refund_pending' تنظیم می‌شود
            }
            
            // Update booking payment status based on refund result
            // به‌روزرسانی وضعیت پرداخت رزرو بر اساس نتیجه بازگشت وجه
            // For wallet payments: set to 'refunded' if wallet transaction succeeds, 'refund_failed' if it fails
            // برای پرداخت‌های کیف پول: در صورت موفقیت تراکنش کیف پول به 'refunded' تنظیم کنید، در صورت شکست به 'refund_failed'
            // For digital payments: always set to 'refund_pending' (requires manual admin processing)
            // برای پرداخت‌های دیجیتال: همیشه به 'refund_pending' تنظیم کنید (نیاز به پردازش دستی ادمین دارد)
            if ($booking->payment_method === 'wallet' && $walletStatus == 1 && $walletAddRefund == 1) {
                // Wallet refund status determined by transaction result
                // وضعیت بازگشت وجه کیف پول با نتیجه تراکنش تعیین می‌شود
                $paymentStatus = $refundSuccess ? 'refunded' : 'refund_failed';
            } else {
                // Digital payment or wallet refund not enabled - requires manual processing
                // پرداخت دیجیتال یا بازگشت وجه کیف پول فعال نشده - نیاز به پردازش دستی دارد
                $paymentStatus = 'refund_pending';
            }
            
            // Use updatePaymentStatus() to ensure validation and consistency
            // استفاده از updatePaymentStatus() برای اطمینان از اعتبارسنجی و سازگاری
            // This ensures state machine rules are followed and prevents invalid transitions
            // این اطمینان می‌دهد که قوانین state machine دنبال می‌شوند و از انتقال‌های نامعتبر جلوگیری می‌کند
            $this->updatePaymentStatus($booking, $paymentStatus);
        } catch (\Exception $e) {
            \Log::error('Refund processing failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'refund_amount' => $refundAmount,
            ]);
            // Don't throw - refund failure shouldn't prevent cancellation
            // پرتاب نکنید - شکست بازگشت وجه نباید از لغو جلوگیری کند
        }
    }
    
    /**
     * Update vendor and admin wallets when booking is confirmed and paid
     * به‌روزرسانی کیف پول فروشنده و ادمین زمانی که رزرو تأیید شده و پرداخت شده است
     *
     * This method integrates beauty bookings with the main system's disbursement system
     * این متد رزروهای زیبایی را با سیستم disbursement اصلی یکپارچه می‌کند
     * Similar to OrderLogic::create_transaction() for orders
     * مشابه OrderLogic::create_transaction() برای سفارش‌ها
     *
     * @param BeautyBooking $booking
     * @return void
     */
    private function updateVendorAndAdminWallets(BeautyBooking $booking): void
    {
        try {
            // IDEMPOTENCY GUARD: Use atomic database update to check and mark wallet update
            // محافظ idempotency: استفاده از به‌روزرسانی اتمی دیتابیس برای بررسی و علامت‌گذاری به‌روزرسانی کیف پول
            // CRITICAL FIX: lockForUpdate() only locks existing rows - if commission doesn't exist, no lock is acquired
            // رفع بحرانی: lockForUpdate() فقط ردیف‌های موجود را قفل می‌کند - اگر کمیسیون وجود نداشته باشد، هیچ قفلی گرفته نمی‌شود
            // Solution: Use atomic UPDATE with WHERE clause to check and mark in one operation
            // راه‌حل: استفاده از UPDATE اتمی با WHERE clause برای بررسی و علامت‌گذاری در یک عملیات
            // This ensures only one thread can mark the commission transaction, preventing duplicate wallet updates
            // این اطمینان می‌دهد که فقط یک thread می‌تواند تراکنش کمیسیون را علامت بزند، از به‌روزرسانی‌های تکراری کیف پول جلوگیری می‌کند
            
            // First, ensure commission transaction exists (it should, since recordCommission() is called before this)
            // ابتدا، اطمینان حاصل کنید که تراکنش کمیسیون وجود دارد (باید وجود داشته باشد، چون recordCommission() قبل از این فراخوانی می‌شود)
            $commissionTransaction = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
                ->where('transaction_type', 'commission')
                ->first();
            
            // If commission doesn't exist, this is an error state (shouldn't happen in normal flow)
            // اگر کمیسیون وجود ندارد، این یک حالت خطا است (نباید در جریان عادی رخ دهد)
            if (!$commissionTransaction) {
                \Log::error('Commission transaction not found when updating wallets - this should not happen', [
                    'booking_id' => $booking->id,
                ]);
                // Don't proceed without commission transaction - this ensures data integrity
                // بدون تراکنش کمیسیون ادامه ندهید - این اطمینان از یکپارچگی داده می‌دهد
                return;
            }
            
            // Use atomic UPDATE to add wallet_updated marker only if it doesn't already exist
            // استفاده از UPDATE اتمی برای افزودن نشانگر wallet_updated فقط در صورت عدم وجود
            // This is atomic at database level - only one thread can succeed in adding the marker
            // این در سطح دیتابیس اتمی است - فقط یک thread می‌تواند در افزودن نشانگر موفق شود
            // CRITICAL: We're already inside a transaction with booking locked, so this is safe
            // مهم: ما قبلاً در یک transaction با booking قفل شده هستیم، بنابراین این ایمن است
            $walletMarker = 'wallet_updated';
            
            // Check if marker already exists (with lock to prevent race conditions)
            // بررسی اینکه آیا نشانگر قبلاً وجود دارد (با قفل برای جلوگیری از race conditions)
            $existingNotes = $commissionTransaction->notes ?? '';
            if ($existingNotes && str_contains($existingNotes, $walletMarker)) {
                \Log::info('Wallet update already completed for booking, skipping', [
                    'booking_id' => $booking->id,
                    'commission_transaction_id' => $commissionTransaction->id,
                ]);
                return;
            }
            
            // Lock the commission transaction row to prevent concurrent updates
            // قفل کردن ردیف تراکنش کمیسیون برای جلوگیری از به‌روزرسانی‌های همزمان
            $commissionTransaction = \Modules\BeautyBooking\Entities\BeautyTransaction::where('id', $commissionTransaction->id)
                ->lockForUpdate()
                ->first();
            
            // BUG FIX: Check if commission transaction still exists after lock
            // رفع باگ: بررسی اینکه آیا تراکنش کمیسیون پس از قفل هنوز وجود دارد
            // If row was deleted between initial fetch and lock, first() returns null
            // اگر ردیف بین دریافت اولیه و قفل حذف شده باشد، first() null برمی‌گرداند
            if (!$commissionTransaction) {
                \Log::error('Commission transaction was deleted before wallet update could complete', [
                    'booking_id' => $booking->id,
                ]);
                // This is a critical error - cannot proceed without commission transaction
                // این یک خطای بحرانی است - بدون تراکنش کمیسیون نمی‌توان ادامه داد
                throw new \Exception('Commission transaction not found - cannot update wallets');
            }
            
            // Double-check after acquiring lock (another thread might have updated it)
            // بررسی مجدد پس از گرفتن قفل (thread دیگر ممکن است آن را به‌روزرسانی کرده باشد)
            if ($commissionTransaction->notes && str_contains($commissionTransaction->notes, $walletMarker)) {
                \Log::info('Wallet update already completed for booking (detected after lock), skipping', [
                    'booking_id' => $booking->id,
                    'commission_transaction_id' => $commissionTransaction->id,
                ]);
                return;
            }
            
            // Mark commission transaction - this is now safe because we have the lock
            // علامت‌گذاری تراکنش کمیسیون - اکنون ایمن است چون قفل را داریم
            $lockedNotes = $commissionTransaction->notes ?? '';
            $commissionTransaction->notes = $lockedNotes 
                ? $lockedNotes . ' | ' . $walletMarker 
                : $walletMarker;
            $commissionTransaction->save();
            
            // Load salon with store relationship
            // بارگذاری سالن با رابطه store
            $salon = $booking->salon()->with('store.vendor')->first();
            if (!$salon || !$salon->store || !$salon->store->vendor) {
                \Log::warning('Salon or store or vendor not found for wallet update', [
                    'booking_id' => $booking->id,
                    'salon_id' => $booking->salon_id,
                ]);
                return;
            }
            
            // Calculate store amount (what vendor should receive)
            // محاسبه مبلغ فروشگاه (آنچه فروشنده باید دریافت کند)
            // store_amount = total_amount - commission_amount - service_fee
            // مبلغ فروشگاه = مبلغ کل - کمیسیون - هزینه سرویس
            $storeAmount = $booking->total_amount - $booking->commission_amount - $booking->service_fee;
            
            // Calculate admin commission (commission + service_fee)
            // محاسبه کمیسیون ادمین (کمیسیون + هزینه سرویس)
            $adminCommission = $booking->commission_amount + $booking->service_fee;
            
            // Get or create wallets
            // دریافت یا ایجاد کیف پول‌ها
            // BUG FIX: Ensure admin exists before creating wallet - don't use hardcoded fallback
            // رفع باگ: اطمینان از وجود ادمین قبل از ایجاد کیف پول - از fallback سخت‌کد شده استفاده نکنید
            // Hardcoded fallback to admin_id = 1 could create invalid wallet records
            // fallback سخت‌کد شده به admin_id = 1 می‌تواند رکوردهای کیف پول نامعتبر ایجاد کند
            $admin = Admin::where('role_id', 1)->first();
            if (!$admin) {
                \Log::error('No admin with role_id = 1 found - cannot create admin wallet', [
                    'booking_id' => $booking->id,
                ]);
                // This is a critical error - cannot proceed without valid admin
                // این یک خطای بحرانی است - بدون ادمین معتبر نمی‌توان ادامه داد
                throw new \Exception('No admin found - cannot update admin wallet');
            }
            
            $adminWallet = AdminWallet::firstOrNew(
                ['admin_id' => $admin->id]
            );
            
            $vendorWallet = StoreWallet::firstOrNew(
                ['vendor_id' => $salon->store->vendor->id]
            );
            
            // Update admin wallet - commission and service fee
            // به‌روزرسانی کیف پول ادمین - کمیسیون و هزینه سرویس
            $adminWallet->total_commission_earning = ($adminWallet->total_commission_earning ?? 0) + $adminCommission;
            
            // Update vendor wallet - store amount (earnings)
            // به‌روزرسانی کیف پول فروشنده - مبلغ فروشگاه (درآمد)
            $vendorWallet->total_earning = ($vendorWallet->total_earning ?? 0) + $storeAmount;
            
            // Handle payment method - determine who received the payment
            // مدیریت روش پرداخت - تعیین اینکه چه کسی پرداخت را دریافت کرده است
            $paymentMethod = $booking->payment_method ?? 'digital_payment';
            $paymentAmount = $booking->total_amount;
            
            // Determine received_by based on payment method
            // تعیین received_by بر اساس روش پرداخت
            // For beauty bookings:
            // - cash_payment: Customer pays at salon → vendor collects cash
            // - digital_payment: Customer pays online → admin receives digital
            // - wallet: Customer pays from wallet → admin receives (wallet already deducted)
            // برای رزروهای زیبایی:
            // - cash_payment: مشتری در سالن پرداخت می‌کند → فروشنده وجه نقد جمع می‌کند
            // - digital_payment: مشتری آنلاین پرداخت می‌کند → ادمین دریافت دیجیتال می‌کند
            // - wallet: مشتری از کیف پول پرداخت می‌کند → ادمین دریافت می‌کند (کیف پول قبلاً کسر شده است)
            if ($paymentMethod === 'cash_payment') {
                // Vendor collects cash at salon
                // فروشنده وجه نقد را در سالن جمع می‌کند
                $vendorWallet->collected_cash = ($vendorWallet->collected_cash ?? 0) + $paymentAmount;
            } elseif ($paymentMethod === 'digital_payment' || $paymentMethod === 'wallet') {
                // Admin receives digital payment or wallet payment
                // ادمین پرداخت دیجیتال یا پرداخت کیف پول را دریافت می‌کند
                $adminWallet->digital_received = ($adminWallet->digital_received ?? 0) + $paymentAmount;
            } else {
                // Default: admin receives manually (fallback)
                // پیش‌فرض: ادمین به صورت دستی دریافت می‌کند (fallback)
                $adminWallet->manual_received = ($adminWallet->manual_received ?? 0) + $paymentAmount;
            }
            
            // Save wallets (already inside outer transaction - no need for nested transaction)
            // ذخیره کیف پول‌ها (قبلاً در transaction بیرونی هستیم - نیازی به transaction تو در تو نیست)
            // CRITICAL: Wallet update is part of atomic operation - if it fails, entire transaction must rollback
            // مهم: به‌روزرسانی کیف پول بخشی از عملیات اتمی است - اگر شکست بخورد، کل transaction باید rollback شود
            // This ensures disbursements include beauty booking earnings
            // این اطمینان می‌دهد که disbursement ها شامل درآمدهای رزرو زیبایی می‌شوند
            // NOTE: Commission transaction is already marked with wallet_updated marker atomically above
            // توجه: تراکنش کمیسیون قبلاً با نشانگر wallet_updated به صورت اتمی در بالا علامت‌گذاری شده است
            $adminWallet->save();
            $vendorWallet->save();
            
            \Log::info('Vendor and admin wallets updated for beauty booking', [
                'booking_id' => $booking->id,
                'salon_id' => $booking->salon_id,
                'store_amount' => $storeAmount,
                'admin_commission' => $adminCommission,
                'payment_method' => $paymentMethod,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to update vendor and admin wallets for beauty booking', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // CRITICAL FIX: Re-throw exception to ensure outer transaction rollback
            // رفع بحرانی: پرتاب مجدد استثنا برای اطمینان از rollback transaction بیرونی
            // Wallet update is critical for disbursements - if it fails, entire operation must rollback
            // به‌روزرسانی کیف پول برای disbursement ها حیاتی است - اگر شکست بخورد، کل عملیات باید rollback شود
            // This ensures atomicity: payment status, revenue recording, and wallet updates all succeed or all fail
            // این اطمینان می‌دهد از atomicity: وضعیت پرداخت، ثبت درآمد، و به‌روزرسانی کیف پول همه موفق می‌شوند یا همه شکست می‌خورند
            throw $e;
        }
    }
    
    /**
     * Reverse vendor and admin wallet updates when booking is cancelled
     * برگرداندن به‌روزرسانی‌های کیف پول فروشنده و ادمین زمانی که رزرو لغو می‌شود
     *
     * This method reverses the wallet updates made by updateVendorAndAdminWallets()
     * این متد به‌روزرسانی‌های کیف پول انجام شده توسط updateVendorAndAdminWallets() را برمی‌گرداند
     * It subtracts the amounts that were previously added
     * مبالغی که قبلاً اضافه شده بودند را کم می‌کند
     *
     * @param BeautyBooking $booking
     * @return void
     */
    private function reverseVendorAndAdminWallets(BeautyBooking $booking): void
    {
        try {
            // IDEMPOTENCY GUARD: Check if wallet reversal has already been done
            // محافظ idempotency: بررسی اینکه آیا برگرداندن کیف پول قبلاً انجام شده است
            $commissionTransaction = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
                ->where('transaction_type', 'commission')
                ->first();
            
            // If commission doesn't exist, wallets were never updated, so nothing to reverse
            // اگر کمیسیون وجود ندارد، کیف پول‌ها هرگز به‌روزرسانی نشده‌اند، بنابراین چیزی برای برگرداندن نیست
            if (!$commissionTransaction) {
                \Log::info('No commission transaction found - wallets were never updated, skipping reversal', [
                    'booking_id' => $booking->id,
                ]);
                return;
            }
            
            $reversalMarker = 'wallet_reversed';
            
            // Check if reversal already done
            // بررسی اینکه آیا برگرداندن قبلاً انجام شده است
            $existingNotes = $commissionTransaction->notes ?? '';
            if ($existingNotes && str_contains($existingNotes, $reversalMarker)) {
                \Log::info('Wallet reversal already completed for booking, skipping', [
                    'booking_id' => $booking->id,
                    'commission_transaction_id' => $commissionTransaction->id,
                ]);
                return;
            }
            
            // Lock the commission transaction row to prevent concurrent updates
            // قفل کردن ردیف تراکنش کمیسیون برای جلوگیری از به‌روزرسانی‌های همزمان
            $commissionTransaction = \Modules\BeautyBooking\Entities\BeautyTransaction::where('id', $commissionTransaction->id)
                ->lockForUpdate()
                ->first();
            
            // Check if commission transaction still exists after lock
            // بررسی اینکه آیا تراکنش کمیسیون پس از قفل هنوز وجود دارد
            if (!$commissionTransaction) {
                \Log::error('Commission transaction was deleted before wallet reversal could complete', [
                    'booking_id' => $booking->id,
                ]);
                throw new \Exception('Commission transaction not found - cannot reverse wallets');
            }
            
            // Double-check after acquiring lock
            // بررسی مجدد پس از گرفتن قفل
            if ($commissionTransaction->notes && str_contains($commissionTransaction->notes, $reversalMarker)) {
                \Log::info('Wallet reversal already completed for booking (detected after lock), skipping', [
                    'booking_id' => $booking->id,
                    'commission_transaction_id' => $commissionTransaction->id,
                ]);
                return;
            }
            
            // Mark commission transaction - this is now safe because we have the lock
            // علامت‌گذاری تراکنش کمیسیون - اکنون ایمن است چون قفل را داریم
            $lockedNotes = $commissionTransaction->notes ?? '';
            $commissionTransaction->notes = $lockedNotes 
                ? $lockedNotes . ' | ' . $reversalMarker 
                : $reversalMarker;
            $commissionTransaction->save();
            
            // Load salon with store relationship
            // بارگذاری سالن با رابطه store
            $salon = $booking->salon()->with('store.vendor')->first();
            if (!$salon || !$salon->store || !$salon->store->vendor) {
                \Log::warning('Salon or store or vendor not found for wallet reversal', [
                    'booking_id' => $booking->id,
                    'salon_id' => $booking->salon_id,
                ]);
                return;
            }
            
            // Calculate reversed amounts (negative of what was added)
            // محاسبه مبالغ برگردانده شده (منفی آنچه اضافه شده بود)
            // store_amount = total_amount - commission_amount - service_fee
            // مبلغ فروشگاه = مبلغ کل - کمیسیون - هزینه سرویس
            $storeAmount = $booking->total_amount - $booking->commission_amount - $booking->service_fee;
            
            // Calculate admin commission (commission + service_fee)
            // محاسبه کمیسیون ادمین (کمیسیون + هزینه سرویس)
            $adminCommission = $booking->commission_amount + $booking->service_fee;
            
            // Get or create wallets
            // دریافت یا ایجاد کیف پول‌ها
            $admin = Admin::where('role_id', 1)->first();
            if (!$admin) {
                \Log::error('No admin with role_id = 1 found - cannot reverse admin wallet', [
                    'booking_id' => $booking->id,
                ]);
                throw new \Exception('No admin found - cannot reverse admin wallet');
            }
            
            $adminWallet = AdminWallet::firstOrNew(
                ['admin_id' => $admin->id]
            );
            
            $vendorWallet = StoreWallet::firstOrNew(
                ['vendor_id' => $salon->store->vendor->id]
            );
            
            // Reverse admin wallet - subtract commission and service fee
            // برگرداندن کیف پول ادمین - کم کردن کمیسیون و هزینه سرویس
            // Validate that we have enough balance to reverse (data integrity check)
            // اعتبارسنجی اینکه موجودی کافی برای برگرداندن داریم (بررسی یکپارچگی داده)
            $currentAdminCommission = $adminWallet->total_commission_earning ?? 0;
            if ($currentAdminCommission < $adminCommission) {
                \Log::error('Insufficient admin commission balance for reversal', [
                    'booking_id' => $booking->id,
                    'current_balance' => $currentAdminCommission,
                    'reversal_amount' => $adminCommission,
                    'shortfall' => $adminCommission - $currentAdminCommission,
                ]);
                throw new \Exception(
                    sprintf(
                        'Cannot reverse admin commission: insufficient balance. Current: %s, Required: %s, Shortfall: %s',
                        $currentAdminCommission,
                        $adminCommission,
                        $adminCommission - $currentAdminCommission
                    )
                );
            }
            $adminWallet->total_commission_earning = $currentAdminCommission - $adminCommission;
            
            // Reverse vendor wallet - subtract store amount (earnings)
            // برگرداندن کیف پول فروشنده - کم کردن مبلغ فروشگاه (درآمد)
            // Validate that we have enough balance to reverse (data integrity check)
            // اعتبارسنجی اینکه موجودی کافی برای برگرداندن داریم (بررسی یکپارچگی داده)
            $currentVendorEarning = $vendorWallet->total_earning ?? 0;
            if ($currentVendorEarning < $storeAmount) {
                \Log::error('Insufficient vendor earning balance for reversal', [
                    'booking_id' => $booking->id,
                    'vendor_id' => $salon->store->vendor->id,
                    'current_balance' => $currentVendorEarning,
                    'reversal_amount' => $storeAmount,
                    'shortfall' => $storeAmount - $currentVendorEarning,
                ]);
                throw new \Exception(
                    sprintf(
                        'Cannot reverse vendor earning: insufficient balance. Current: %s, Required: %s, Shortfall: %s',
                        $currentVendorEarning,
                        $storeAmount,
                        $storeAmount - $currentVendorEarning
                    )
                );
            }
            $vendorWallet->total_earning = $currentVendorEarning - $storeAmount;
            
            // Reverse payment method specific fields
            // برگرداندن فیلدهای خاص روش پرداخت
            $paymentMethod = $booking->payment_method ?? 'digital_payment';
            $paymentAmount = $booking->total_amount;
            
            if ($paymentMethod === 'cash_payment') {
                // Reverse vendor collected cash
                // برگرداندن وجه نقد جمع‌آوری شده فروشنده
                // Validate that we have enough collected cash to reverse
                // اعتبارسنجی اینکه وجه نقد جمع‌آوری شده کافی برای برگرداندن داریم
                $currentCollectedCash = $vendorWallet->collected_cash ?? 0;
                if ($currentCollectedCash < $paymentAmount) {
                    \Log::error('Insufficient vendor collected cash for reversal', [
                        'booking_id' => $booking->id,
                        'vendor_id' => $salon->store->vendor->id,
                        'current_balance' => $currentCollectedCash,
                        'reversal_amount' => $paymentAmount,
                        'shortfall' => $paymentAmount - $currentCollectedCash,
                    ]);
                    throw new \Exception(
                        sprintf(
                            'Cannot reverse vendor collected cash: insufficient balance. Current: %s, Required: %s, Shortfall: %s',
                            $currentCollectedCash,
                            $paymentAmount,
                            $paymentAmount - $currentCollectedCash
                        )
                    );
                }
                $vendorWallet->collected_cash = $currentCollectedCash - $paymentAmount;
            } elseif ($paymentMethod === 'digital_payment' || $paymentMethod === 'wallet') {
                // Reverse admin digital received
                // برگرداندن دریافت دیجیتال ادمین
                // Validate that we have enough digital received to reverse
                // اعتبارسنجی اینکه دریافت دیجیتال کافی برای برگرداندن داریم
                $currentDigitalReceived = $adminWallet->digital_received ?? 0;
                if ($currentDigitalReceived < $paymentAmount) {
                    \Log::error('Insufficient admin digital received for reversal', [
                        'booking_id' => $booking->id,
                        'current_balance' => $currentDigitalReceived,
                        'reversal_amount' => $paymentAmount,
                        'shortfall' => $paymentAmount - $currentDigitalReceived,
                    ]);
                    throw new \Exception(
                        sprintf(
                            'Cannot reverse admin digital received: insufficient balance. Current: %s, Required: %s, Shortfall: %s',
                            $currentDigitalReceived,
                            $paymentAmount,
                            $paymentAmount - $currentDigitalReceived
                        )
                    );
                }
                $adminWallet->digital_received = $currentDigitalReceived - $paymentAmount;
            } else {
                // Reverse admin manual received
                // برگرداندن دریافت دستی ادمین
                // Validate that we have enough manual received to reverse
                // اعتبارسنجی اینکه دریافت دستی کافی برای برگرداندن داریم
                $currentManualReceived = $adminWallet->manual_received ?? 0;
                if ($currentManualReceived < $paymentAmount) {
                    \Log::error('Insufficient admin manual received for reversal', [
                        'booking_id' => $booking->id,
                        'current_balance' => $currentManualReceived,
                        'reversal_amount' => $paymentAmount,
                        'shortfall' => $paymentAmount - $currentManualReceived,
                    ]);
                    throw new \Exception(
                        sprintf(
                            'Cannot reverse admin manual received: insufficient balance. Current: %s, Required: %s, Shortfall: %s',
                            $currentManualReceived,
                            $paymentAmount,
                            $paymentAmount - $currentManualReceived
                        )
                    );
                }
                $adminWallet->manual_received = $currentManualReceived - $paymentAmount;
            }
            
            // Save wallets
            // ذخیره کیف پول‌ها
            $adminWallet->save();
            $vendorWallet->save();
            
            \Log::info('Vendor and admin wallets reversed for cancelled beauty booking', [
                'booking_id' => $booking->id,
                'salon_id' => $booking->salon_id,
                'store_amount' => $storeAmount,
                'admin_commission' => $adminCommission,
                'payment_method' => $paymentMethod,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to reverse vendor and admin wallets for cancelled beauty booking', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Re-throw exception to ensure outer transaction rollback
            // پرتاب مجدد استثنا برای اطمینان از rollback transaction بیرونی
            throw $e;
        }
    }
}

