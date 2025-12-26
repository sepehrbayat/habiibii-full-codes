<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Beauty Review Model
 * مدل نظر
 *
 * @property int $id
 * @property int $booking_id
 * @property int $user_id
 * @property int $salon_id
 * @property int $service_id
 * @property int|null $staff_id
 * @property int $rating
 * @property string|null $comment
 * @property array|null $attachments
 * @property string $status
 * @property string|null $admin_notes
 */
class BeautyReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    protected $casts = [
        'booking_id' => 'integer',
        'user_id' => 'integer',
        'salon_id' => 'integer',
        'service_id' => 'integer',
        'staff_id' => 'integer',
        'rating' => 'integer',
        'comment' => 'string',
        'attachments' => 'array',
        'status' => 'string',
        'admin_notes' => 'string',
    ];
    
    /**
     * Get the booking for this review
     * دریافت رزرو این نظر
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(BeautyBooking::class, 'booking_id', 'id');
    }
    
    /**
     * Get the user who wrote this review
     * دریافت کاربری که این نظر را نوشته است
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    /**
     * Get the salon for this review
     * دریافت سالن این نظر
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Get the service for this review
     * دریافت خدمت این نظر
     *
     * @return BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(BeautyService::class, 'service_id', 'id');
    }
    
    /**
     * Get the staff member for this review
     * دریافت کارمند این نظر
     *
     * @return BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(BeautyStaff::class, 'staff_id', 'id');
    }
    
    /**
     * Scope a query to only include approved reviews
     * محدود کردن کوئری به نظرات تأیید شده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    /**
     * Scope a query to only include pending reviews
     * محدود کردن کوئری به نظرات در انتظار
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
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
}

