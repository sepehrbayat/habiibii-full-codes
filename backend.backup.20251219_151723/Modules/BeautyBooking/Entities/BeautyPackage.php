<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * Beauty Package Model
 * مدل پکیج
 *
 * @property int $id
 * @property int $salon_id
 * @property int $service_id
 * @property string $name
 * @property int $sessions_count
     * @property float $total_price
 * @property float $discount_percentage
 * @property int $validity_days
 * @property bool $status
     * @property int $total_sessions
     * @property int $used_sessions
 */
class BeautyPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    protected $casts = [
        'salon_id' => 'integer',
        'service_id' => 'integer',
        'name' => 'string',
        'sessions_count' => 'integer',
        'total_price' => 'float',
        'discount_percentage' => 'float',
        'validity_days' => 'integer',
        'status' => 'boolean',
        'total_sessions' => 'integer',
        'used_sessions' => 'integer',
    ];
    
    /**
     * Get the salon that owns this package
     * دریافت سالنی که این پکیج متعلق به آن است
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Get the service for this package
     * دریافت خدمت این پکیج
     *
     * @return BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(BeautyService::class, 'service_id', 'id');
    }
    
    /**
     * Get all usages for this package
     * دریافت تمام استفاده‌ها از این پکیج
     *
     * @return HasMany
     */
    public function usages(): HasMany
    {
        return $this->hasMany(BeautyPackageUsage::class, 'package_id', 'id');
    }
    
    /**
     * Get remaining sessions for a user
     * دریافت جلسات باقیمانده برای یک کاربر
     *
     * @param int $userId
     * @return int
     */
    public function getRemainingSessions(int $userId): int
    {
        $usedSessions = $this->usages()
            ->where('user_id', $userId)
            ->where('status', 'used')
            ->count();
        
        return max($this->sessions_count - $usedSessions, 0);
    }
    
    /**
     * Check if package is valid for a user
     * بررسی معتبر بودن پکیج برای یک کاربر
     *
     * @param int $userId
     * @return bool
     */
    public function isValidForUser(int $userId): bool
    {
        // Check if package is active
        // بررسی فعال بودن پکیج
        if (!$this->status) {
            return false;
        }
        
        // Check if user has remaining sessions
        // بررسی داشتن جلسات باقیمانده توسط کاربر
        if ($this->getRemainingSessions($userId) <= 0) {
            return false;
        }
        
        // Check if package is still within validity period
        // بررسی اینکه آیا پکیج هنوز در دوره اعتبار است
        // Get the earliest valid purchase record (pending or used, not cancelled)
        // دریافت اولین رکورد خرید معتبر (pending یا used، نه cancelled)
        $earliestValidUsage = $this->usages()
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'used']) // Only valid purchase records
            ->orderBy('created_at', 'asc') // Use created_at to get purchase date
            ->first();
        
        if ($earliestValidUsage) {
            // If package has been used, calculate expiry from first usage date
            // اگر پکیج استفاده شده است، محاسبه انقضا از تاریخ اولین استفاده
            $firstUsedUsage = $this->usages()
                ->where('user_id', $userId)
                ->where('status', 'used')
                ->whereNotNull('used_at')
                ->orderBy('used_at', 'asc')
                ->first();
            
            if ($firstUsedUsage) {
                // Validity starts from first usage date
                // اعتبار از تاریخ اولین استفاده شروع می‌شود
                $expiryDate = Carbon::parse($firstUsedUsage->used_at)->addDays($this->validity_days);
            } else {
                // Package purchased but never used - validity starts from purchase date
                // پکیج خریداری شده اما هرگز استفاده نشده - اعتبار از تاریخ خرید شروع می‌شود
                // Use earliestValidUsage which is guaranteed to be a valid purchase (pending or used)
                // استفاده از earliestValidUsage که تضمین می‌شود یک خرید معتبر است (pending یا used)
                $expiryDate = Carbon::parse($earliestValidUsage->created_at)->addDays($this->validity_days);
            }
            
            if (now()->greaterThan($expiryDate)) {
                return false;
            }
        }
        
        return true;
    }
}

