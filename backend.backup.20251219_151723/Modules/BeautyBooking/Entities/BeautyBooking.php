<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use App\Models\User;
use App\Models\Zone;
use App\Scopes\ZoneScope;
use App\Traits\ReportFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * Beauty Booking Model
 * مدل رزرو
 *
 * @property int $id
 * @property int $user_id
 * @property int $salon_id
 * @property int $service_id
 * @property int|null $package_id
 * @property int|null $main_service_id
 * @property int|null $staff_id
 * @property int|null $zone_id
 * @property int|null $conversation_id
 * @property string $booking_date
 * @property string $booking_time
 * @property string|null $booking_date_time
 * @property string $booking_reference
 * @property string $status
 * @property string $payment_status
 * @property string|null $payment_method
 * @property float $total_amount
 * @property float $commission_amount
 * @property float $service_fee
 * @property float $cancellation_fee
 * @property float $consultation_credit_percentage
 * @property float $consultation_credit_amount
 * @property array|null $additional_services
 * @property string|null $notes
 * @property string|null $cancellation_reason
 * @property string $cancelled_by
 */
class BeautyBooking extends Model
{
    use HasFactory, SoftDeletes, ReportFilter;

    protected $guarded = ['id'];
    
    /**
     * Create a new factory instance for the model.
     * ایجاد instance جدید factory برای مدل
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\BeautyBooking\Database\Factories\BeautyBookingFactory::new();
    }
    
    /**
     * Boot the model
     * بوت مدل
     *
     * @return void
     */
    protected static function booted(): void
    {
        // Add ZoneScope for admin zone filtering
        // افزودن ZoneScope برای فیلتر منطقه ادمین
        static::addGlobalScope(new ZoneScope);
    }
    
    protected $casts = [
        'user_id' => 'integer',
        'salon_id' => 'integer',
        'service_id' => 'integer',
        'package_id' => 'integer',
        'main_service_id' => 'integer',
        'staff_id' => 'integer',
        'zone_id' => 'integer',
        'conversation_id' => 'integer',
        'booking_date' => 'date',
        'booking_time' => 'string',
        'booking_date_time' => 'datetime',
        'booking_reference' => 'string',
        'status' => 'string',
        'payment_status' => 'string',
        'payment_method' => 'string',
        'total_amount' => 'float',
        'commission_amount' => 'float',
        'service_fee' => 'float',
        'cancellation_fee' => 'float',
        'consultation_credit_percentage' => 'float',
        'consultation_credit_amount' => 'float',
        'additional_services' => 'array',
        'notes' => 'string',
        'cancellation_reason' => 'string',
        'cancelled_by' => 'string',
    ];
    
    /**
     * Get the user who made the booking
     * دریافت کاربری که رزرو را انجام داده است
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    /**
     * Get the salon for this booking
     * دریافت سالن این رزرو
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Get the service for this booking
     * دریافت خدمت این رزرو
     *
     * @return BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(BeautyService::class, 'service_id', 'id');
    }
    
    /**
     * Get the package if this booking uses a package
     * دریافت پکیج در صورت استفاده از پکیج در این رزرو
     *
     * @return BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(BeautyPackage::class, 'package_id', 'id');
    }
    
    /**
     * Get the main service if this is a consultation booking
     * دریافت خدمت اصلی در صورت اینکه این رزرو مشاوره باشد
     *
     * @return BelongsTo
     */
    public function mainService(): BelongsTo
    {
        return $this->belongsTo(BeautyService::class, 'main_service_id', 'id');
    }
    
    /**
     * Get the staff member for this booking
     * دریافت کارمند این رزرو
     *
     * @return BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(BeautyStaff::class, 'staff_id', 'id');
    }
    
    /**
     * Get the zone for this booking
     * دریافت منطقه این رزرو
     *
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id', 'id');
    }
    
    /**
     * Get the conversation for this booking
     * دریافت گفتگو این رزرو
     *
     * @return BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Conversation::class, 'conversation_id', 'id');
    }
    
    /**
     * Get all loyalty points for this booking
     * دریافت تمام امتیازهای وفاداری این رزرو
     *
     * @return HasMany
     */
    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(\Modules\BeautyBooking\Entities\BeautyLoyaltyPoint::class, 'booking_id', 'id');
    }
    
    /**
     * Get the review for this booking
     * دریافت نظر این رزرو
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BeautyReview::class, 'booking_id', 'id');
    }
    
    /**
     * Get the transaction for this booking
     * دریافت تراکنش این رزرو
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BeautyTransaction::class, 'booking_id', 'id');
    }
    
    /**
     * Get parsed booking date/time as Carbon instance
     * دریافت تاریخ/زمان رزرو تجزیه شده به عنوان نمونه Carbon
     *
     * This helper method centralizes date/time parsing logic to avoid duplication
     * این متد کمکی منطق تجزیه تاریخ/زمان را متمرکز می‌کند تا از تکرار جلوگیری شود
     *
     * @return Carbon
     * @throws \Exception If booking_date or booking_time is missing or invalid
     */
    protected function getParsedBookingDateTime(): Carbon
    {
        // Use booking_date_time if available (already combined)
        // استفاده از booking_date_time در صورت موجود بودن (قبلاً ترکیب شده)
        if ($this->booking_date_time) {
            return Carbon::parse($this->booking_date_time);
        }
        
        // Validate that both booking_date and booking_time are available
        // اعتبارسنجی اینکه هم booking_date و هم booking_time موجود هستند
        if (empty($this->booking_date)) {
            throw new \Exception(translate('messages.booking_date_time_required'));
        }
        
        if (empty($this->booking_time)) {
            throw new \Exception(translate('messages.booking_date_time_required'));
        }
        
        // Fallback: combine booking_date and booking_time
        // جایگزین: ترکیب booking_date و booking_time
        // booking_date is cast as 'date' so it's already a Carbon date object
        // booking_date به عنوان 'date' cast شده است پس قبلاً یک شیء Carbon date است
        $bookingDate = $this->booking_date instanceof Carbon 
            ? $this->booking_date->format('Y-m-d')
            : (string)$this->booking_date;
        
        // Ensure booking_time is a string before concatenation
        // اطمینان از اینکه booking_time یک رشته است قبل از concatenation
        $bookingTime = (string)$this->booking_time;
        
        // Validate that booking_time is not empty after string conversion
        // اعتبارسنجی اینکه booking_time پس از تبدیل به رشته خالی نیست
        if (empty(trim($bookingTime))) {
            throw new \Exception(translate('messages.booking_date_time_required'));
        }
        
        return Carbon::parse($bookingDate . ' ' . $bookingTime);
    }
    
    /**
     * Check if booking can be cancelled
     * بررسی امکان لغو رزرو
     *
     * @return bool
     * @throws \Exception If booking date/time is invalid or missing
     */
    public function canCancel(): bool
    {
        if ($this->status === 'cancelled' || $this->status === 'completed') {
            return false;
        }
        
        try {
            $bookingDateTime = $this->getParsedBookingDateTime();
            
            // Check if booking is in the past - cannot cancel past bookings
            // بررسی اینکه آیا رزرو در گذشته است - نمی‌توان رزروهای گذشته را لغو کرد
            if ($bookingDateTime->isPast()) {
                return false;
            }
            
            // Calculate hours until booking (use true to get signed values for consistency)
            // محاسبه ساعت تا رزرو (استفاده از true برای دریافت مقادیر علامت‌دار برای سازگاری)
            $hoursUntilBooking = now()->diffInHours($bookingDateTime, true);
            
            // Can cancel if more than 24 hours before booking
            return $hoursUntilBooking >= 24;
        } catch (\Exception $e) {
            // If date/time is invalid, cannot determine cancellation eligibility
            // اگر تاریخ/زمان نامعتبر است، نمی‌توان واجد شرایط لغو را تعیین کرد
            // Return false to be safe (prevent cancellation when data is invalid)
            // برگرداندن false برای ایمنی (جلوگیری از لغو زمانی که داده نامعتبر است)
            return false;
        }
    }
    
    /**
     * Check if booking can be rescheduled
     * بررسی امکان تغییر زمان رزرو
     *
     * @return bool
     * @throws \Exception If booking date/time is invalid or missing
     */
    public function canReschedule(): bool
    {
        if (in_array($this->status, ['cancelled', 'completed'], true)) {
            return false;
        }
        
        try {
            $bookingDateTime = $this->getParsedBookingDateTime();
            
            if ($bookingDateTime->isPast()) {
                return false;
            }
            
            // Allow reschedule if at least 2 hours remain
            // اجازه تغییر زمان اگر حداقل ۲ ساعت تا رزرو باقی مانده باشد
            $hoursUntil = now()->diffInHours($bookingDateTime, true);
            return $hoursUntil >= 2;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Calculate cancellation fee
     * محاسبه هزینه لغو
     *
     * @return float
     * @throws \Exception If booking date/time is invalid or missing
     */
    public function calculateCancellationFee(): float
    {
        // If booking is already cancelled or completed, no cancellation fee applies
        // اگر رزرو قبلاً لغو شده یا تکمیل شده است، هیچ هزینه لغو اعمال نمی‌شود
        if ($this->status === 'cancelled' || $this->status === 'completed') {
            return 0.0;
        }
        
        try {
            $bookingDateTime = $this->getParsedBookingDateTime();
            
            // Get cancellation fee settings from config (consistent with service method)
            // دریافت تنظیمات جریمه لغو از config (سازگار با متد سرویس)
            // Fixed: Now uses config values instead of hardcoded thresholds
            // اصلاح شده: اکنون از مقادیر config به جای آستانه‌های hardcoded استفاده می‌کند
            $cancellationConfig = config('beautybooking.cancellation_fee', []);
            $timeThresholds = $cancellationConfig['time_thresholds'] ?? [];
            $feePercentages = $cancellationConfig['fee_percentages'] ?? [];
            
            // Time thresholds (in hours before booking)
            // آستانه‌های زمانی (به ساعت قبل از رزرو)
            $noFeeHours = $timeThresholds['no_fee_hours'] ?? config('beautybooking.cancellation_fee.time_thresholds.no_fee_hours', 24);
            $partialFeeHours = $timeThresholds['partial_fee_hours'] ?? config('beautybooking.cancellation_fee.time_thresholds.partial_fee_hours', 2);
            
            // Fee percentages (0-100%)
            // درصدهای جریمه (0-100٪)
            $noFeePercent = $feePercentages['no_fee'] ?? config('beautybooking.cancellation_fee.fee_percentages.no_fee', 0.0);
            $partialFeePercent = $feePercentages['partial'] ?? config('beautybooking.cancellation_fee.fee_percentages.partial', 50.0);
            $fullFeePercent = $feePercentages['full'] ?? config('beautybooking.cancellation_fee.fee_percentages.full', 100.0);
            
            // Check if booking is in the past first
            // بررسی اینکه آیا رزرو در گذشته است ابتدا
            if ($bookingDateTime->isPast()) {
                // Booking is in the past - full fee applies
                // رزرو در گذشته است - جریمه کامل اعمال می‌شود
                return $this->total_amount * ($fullFeePercent / 100);
            }
            
            // Calculate hours until booking (use true to get signed values)
            // محاسبه ساعت تا رزرو (استفاده از true برای دریافت مقادیر علامت‌دار)
            // Note: We calculate hours here directly to avoid race conditions
            // توجه: ما ساعت را مستقیماً اینجا محاسبه می‌کنیم تا از race condition جلوگیری کنیم
            // between canCancel() and calculateCancellationFee() calls
            // بین فراخوانی‌های canCancel() و calculateCancellationFee()
            $hoursUntilBooking = now()->diffInHours($bookingDateTime, true);
            
            // Apply config-based fee calculation
            // اعمال محاسبه جریمه مبتنی بر config
            if ($hoursUntilBooking >= $noFeeHours) {
                // 24+ hours: No fee (configurable)
                // 24+ ساعت: بدون جریمه (قابل پیکربندی)
                return $this->total_amount * ($noFeePercent / 100);
            } elseif ($hoursUntilBooking >= $partialFeeHours) {
                // 2-24 hours: Partial fee (configurable)
                // 2-24 ساعت: جریمه جزئی (قابل پیکربندی)
                return $this->total_amount * ($partialFeePercent / 100);
            } else {
                // < 2 hours: Full fee (configurable)
                // < 2 ساعت: جریمه کامل (قابل پیکربندی)
                return $this->total_amount * ($fullFeePercent / 100);
            }
        } catch (\Exception $e) {
            // If date/time is invalid, cannot calculate fee accurately
            // اگر تاریخ/زمان نامعتبر است، نمی‌توان هزینه را به درستی محاسبه کرد
            // Return full amount to be safe (prevent free cancellation when data is invalid)
            // برگرداندن مبلغ کامل برای ایمنی (جلوگیری از لغو رایگان زمانی که داده نامعتبر است)
            return $this->total_amount;
        }
    }
    
    /**
     * Update booking status
     * به‌روزرسانی وضعیت رزرو
     *
     * @param string $status
     * @return bool
     */
    public function updateStatus(string $status): bool
    {
        return $this->update(['status' => $status]);
    }
    
    /**
     * Scope a query to only include upcoming bookings
     * محدود کردن کوئری به رزروهای آینده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'completed');
    }
    
    /**
     * Scope a query to only include past bookings
     * محدود کردن کوئری به رزروهای گذشته
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePast($query)
    {
        return $query->where(function($q) {
            $q->where('booking_date', '<', now()->toDateString())
              ->orWhere(function($q2) {
                  $q2->where('booking_date', '=', now()->toDateString())
                     ->where('booking_time', '<', now()->format('H:i:s'));
              });
        });
    }
    
    /**
     * Scope a query to filter by status
     * محدود کردن کوئری بر اساس وضعیت
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope a query to filter by salon
     * محدود کردن کوئری بر اساس سالن
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $salonId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySalon($query, int $salonId)
    {
        return $query->where('salon_id', $salonId);
    }
    
    /**
     * Scope a query to only include pending bookings
     * محدود کردن کوئری به رزروهای در انتظار
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope a query to only include confirmed bookings
     * محدود کردن کوئری به رزروهای تأیید شده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }
    
    /**
     * Scope a query to only include cancelled bookings
     * محدود کردن کوئری به رزروهای لغو شده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
    
    /**
     * Scope a query to only include completed bookings
     * محدود کردن کوئری به رزروهای تکمیل شده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    /**
     * Check if booking is upcoming
     * بررسی آینده بودن رزرو
     *
     * @return bool
     * @throws \Exception If booking date/time is invalid or missing
     */
    public function isUpcoming(): bool
    {
        try {
            $bookingDateTime = $this->getParsedBookingDateTime();
            return $bookingDateTime->isFuture() && 
                   !in_array($this->status, ['cancelled', 'completed']);
        } catch (\Exception $e) {
            // If date/time is invalid, cannot determine if upcoming
            // اگر تاریخ/زمان نامعتبر است، نمی‌توان تعیین کرد که آیا آینده است
            // Return false to be safe (treat invalid bookings as not upcoming)
            // برگرداندن false برای ایمنی (در نظر گرفتن رزروهای نامعتبر به عنوان غیر آینده)
            return false;
        }
    }
    
    /**
     * Check if booking is past
     * بررسی گذشته بودن رزرو
     *
     * @return bool
     * @throws \Exception If booking date/time is invalid or missing
     */
    public function isPast(): bool
    {
        try {
            $bookingDateTime = $this->getParsedBookingDateTime();
            return $bookingDateTime->isPast();
        } catch (\Exception $e) {
            // If date/time is invalid, cannot determine if past
            // اگر تاریخ/زمان نامعتبر است، نمی‌توان تعیین کرد که آیا گذشته است
            // Return false to be safe (treat invalid bookings as not past)
            // برگرداندن false برای ایمنی (در نظر گرفتن رزروهای نامعتبر به عنوان غیر گذشته)
            return false;
        }
    }
    
    /**
     * Check if booking is cancellable
     * بررسی قابل لغو بودن رزرو
     *
     * @return bool
     */
    public function isCancellable(): bool
    {
        return $this->canCancel();
    }
}

