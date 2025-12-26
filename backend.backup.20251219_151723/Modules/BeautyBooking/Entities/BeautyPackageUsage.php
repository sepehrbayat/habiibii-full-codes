<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Beauty Package Usage Model
 * مدل استفاده از پکیج
 *
 * @property int $id
 * @property int $package_id
 * @property int|null $booking_id
 * @property int $user_id
 * @property int $salon_id
 * @property int $session_number
 * @property \Illuminate\Support\Carbon $used_at
 * @property string $status
 */
class BeautyPackageUsage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'package_id' => 'integer',
        'booking_id' => 'integer',
        'user_id' => 'integer',
        'salon_id' => 'integer',
        'session_number' => 'integer',
        'used_at' => 'datetime',
        'status' => 'string',
    ];
    
    /**
     * Get the package
     * دریافت پکیج
     *
     * @return BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(BeautyPackage::class, 'package_id', 'id');
    }
    
    /**
     * Get the booking
     * دریافت رزرو
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(BeautyBooking::class, 'booking_id', 'id');
    }
    
    /**
     * Get the user
     * دریافت کاربر
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    /**
     * Get the salon
     * دریافت سالن
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Scope a query to only include used sessions
     * محدود کردن کوئری به جلسات استفاده شده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }
    
    /**
     * Scope a query to only include pending sessions
     * محدود کردن کوئری به جلسات در انتظار
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}

