<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * Beauty Loyalty Point Model
 * مدل امتیاز وفاداری
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $salon_id
 * @property int|null $campaign_id
 * @property int|null $booking_id
 * @property int $points
 * @property string $type
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $expires_at
 */
class BeautyLoyaltyPoint extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'user_id' => 'integer',
        'salon_id' => 'integer',
        'campaign_id' => 'integer',
        'booking_id' => 'integer',
        'points' => 'integer',
        'type' => 'string',
        'description' => 'string',
        'expires_at' => 'date',
    ];
    
    /**
     * Get the user who owns these points
     * دریافت کاربری که این امتیازها متعلق به اوست
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    /**
     * Get the salon for these points (nullable)
     * دریافت سالن این امتیازها (اختیاری)
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Get the campaign for these points (nullable)
     * دریافت کمپین این امتیازها (اختیاری)
     *
     * @return BelongsTo
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(BeautyLoyaltyCampaign::class, 'campaign_id', 'id');
    }
    
    /**
     * Get the booking for these points (nullable)
     * دریافت رزرو این امتیازها (اختیاری)
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(BeautyBooking::class, 'booking_id', 'id');
    }
    
    /**
     * Scope a query to only include valid (non-expired) points
     * محدود کردن کوئری به امتیازهای معتبر (منقضی نشده)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
    
    /**
     * Scope a query to only include earned points
     * محدود کردن کوئری به امتیازهای کسب شده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEarned($query)
    {
        return $query->where('type', 'earned')
            ->where('points', '>', 0);
    }
    
    /**
     * Check if points are expired
     * بررسی منقضی شدن امتیازها
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false; // Never expires
        }
        
        return $this->expires_at->isPast();
    }
}

