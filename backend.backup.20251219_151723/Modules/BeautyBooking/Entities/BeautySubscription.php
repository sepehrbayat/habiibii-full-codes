<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * Beauty Subscription Model
 * مدل اشتراک
 *
 * @property int $id
 * @property int $salon_id
 * @property string $subscription_type
 * @property string|null $ad_position
 * @property string|null $banner_image
 * @property int $duration_days
 * @property string $start_date
 * @property string $end_date
 * @property float $amount_paid
 * @property string|null $payment_method
 * @property string $status
 */
class BeautySubscription extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'salon_id' => 'integer',
        'subscription_type' => 'string',
        'ad_position' => 'string',
        'banner_image' => 'string',
        'duration_days' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'amount_paid' => 'float',
        'payment_method' => 'string',
        'auto_renew' => 'boolean',
        'status' => 'string',
    ];
    
    /**
     * Get the salon that owns this subscription
     * دریافت سالنی که این اشتراک متعلق به آن است
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Scope a query to only include active subscriptions
     * محدود کردن کوئری به اشتراک‌های فعال
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString());
    }
    
    /**
     * Scope a query to only include expired subscriptions
     * محدود کردن کوئری به اشتراک‌های منقضی شده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where(function($q) {
            $q->where('status', 'expired')
              ->orWhere('end_date', '<', now()->toDateString());
        });
    }
    
    /**
     * Scope a query to filter by subscription type
     * محدود کردن کوئری بر اساس نوع اشتراک
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('subscription_type', $type);
    }
    
    /**
     * Check if subscription is active
     * بررسی فعال بودن اشتراک
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               Carbon::parse($this->end_date)->isFuture();
    }
}

