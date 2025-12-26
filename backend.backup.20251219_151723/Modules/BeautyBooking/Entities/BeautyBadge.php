<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * Beauty Badge Model
 * مدل نشان (Badge)
 *
 * @property int $id
 * @property int $salon_id
 * @property string $badge_type
 * @property \Illuminate\Support\Carbon $earned_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 */
class BeautyBadge extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'salon_id' => 'integer',
        'badge_type' => 'string',
        'earned_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
    
    /**
     * Get the salon that owns this badge
     * دریافت سالنی که این نشان متعلق به آن است
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Scope a query to only include active badges
     * محدود کردن کوئری به نشان‌های فعال
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
    
    /**
     * Scope a query to only include expired badges
     * محدود کردن کوئری به نشان‌های منقضی شده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }
    
    /**
     * Scope a query to filter by badge type
     * محدود کردن کوئری بر اساس نوع نشان
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('badge_type', $type);
    }
    
    /**
     * Check if badge is active
     * بررسی فعال بودن نشان
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->expires_at === null) {
            return true; // Permanent badge
        }
        
        return $this->expires_at->isFuture();
    }
}

