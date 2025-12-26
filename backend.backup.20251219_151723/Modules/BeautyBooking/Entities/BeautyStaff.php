<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * Beauty Staff Model
 * مدل کارمند سالن
 *
 * @property int $id
 * @property int $salon_id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $avatar
 * @property bool $status
 * @property array|null $specializations
 * @property array|null $working_hours
 * @property array|null $breaks
 * @property array|null $days_off
 */
class BeautyStaff extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    /**
     * Create a new factory instance for the model.
     * ایجاد instance جدید factory برای مدل
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\BeautyBooking\Database\Factories\BeautyStaffFactory::new();
    }
    
    protected $casts = [
        'salon_id' => 'integer',
        'name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'avatar' => 'string',
        'status' => 'boolean',
        'specializations' => 'array',
        'working_hours' => 'array',
        'breaks' => 'array',
        'days_off' => 'array',
    ];
    
    /**
     * Get the salon that owns this staff member
     * دریافت سالنی که این کارمند متعلق به آن است
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Get all bookings for this staff member
     * دریافت تمام رزروهای این کارمند
     *
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(BeautyBooking::class, 'staff_id', 'id');
    }
    
    /**
     * Get all services this staff member can perform
     * دریافت تمام خدماتی که این کارمند می‌تواند انجام دهد
     *
     * @return BelongsToMany
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            BeautyService::class,
            'beauty_service_staff',
            'staff_id',
            'service_id'
        )->withTimestamps();
    }
    
    /**
     * Get all calendar blocks for this staff member
     * دریافت تمام بلاک‌های تقویم این کارمند
     *
     * @return HasMany
     */
    public function calendarBlocks(): HasMany
    {
        return $this->hasMany(BeautyCalendarBlock::class, 'staff_id', 'id');
    }
    
    /**
     * Get all reviews for this staff member
     * دریافت تمام نظرات این کارمند
     *
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(BeautyReview::class, 'staff_id', 'id');
    }
    
    /**
     * Check if staff is available at a specific date and time
     * بررسی دسترسی کارمند در تاریخ و زمان مشخص
     *
     * @param string $date Date in Y-m-d format
     * @param string $time Time in H:i format
     * @return bool
     */
    public function isAvailable(string $date, string $time): bool
    {
        // Check if staff is active
        if (!$this->status) {
            return false;
        }
        
        // Check if date is in days_off
        $daysOff = $this->days_off ?? [];
        if (in_array($date, $daysOff)) {
            return false;
        }
        
        // Check working hours
        $workingHours = $this->working_hours ?? [];
        $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));
        
        if (!isset($workingHours[$dayOfWeek])) {
            return false;
        }
        
        $dayHours = $workingHours[$dayOfWeek];
        if (!isset($dayHours['open']) || !isset($dayHours['close'])) {
            return false;
        }
        
        // Check if time is within working hours
        if ($time < $dayHours['open'] || $time > $dayHours['close']) {
            return false;
        }
        
        // Check breaks
        $breaks = $this->breaks ?? [];
        foreach ($breaks as $break) {
            if (isset($break['date']) && $break['date'] === $date) {
                if (isset($break['start_time']) && isset($break['end_time'])) {
                    if ($time >= $break['start_time'] && $time <= $break['end_time']) {
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
    
    /**
     * Get working hours for a specific date
     * دریافت ساعات کاری برای تاریخ مشخص
     *
     * @param string $date Date in Y-m-d format
     * @return array|null
     */
    public function getWorkingHours(string $date): ?array
    {
        $workingHours = $this->working_hours ?? [];
        $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));
        
        return $workingHours[$dayOfWeek] ?? null;
    }
}

