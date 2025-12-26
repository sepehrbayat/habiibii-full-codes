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
 * Beauty Loyalty Campaign Model
 * مدل کمپین وفاداری
 *
 * @property int $id
 * @property int|null $salon_id
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property array|null $rules
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property bool $is_active
 * @property float $commission_percentage
 * @property string $commission_type
 * @property int $total_participants
 * @property int $total_redeemed
 * @property float $total_revenue
 */
class BeautyLoyaltyCampaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    protected $casts = [
        'salon_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'type' => 'string',
        'rules' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'commission_percentage' => 'float',
        'commission_type' => 'string',
        'total_participants' => 'integer',
        'total_redeemed' => 'integer',
        'total_revenue' => 'float',
    ];
    
    /**
     * Get the salon for this campaign (nullable - can be platform-wide)
     * دریافت سالن این کمپین (اختیاری - می‌تواند در سطح پلتفرم باشد)
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Get all loyalty points for this campaign
     * دریافت تمام امتیازهای وفاداری این کمپین
     *
     * @return HasMany
     */
    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(BeautyLoyaltyPoint::class, 'campaign_id', 'id');
    }
    
    /**
     * Scope a query to only include active campaigns
     * محدود کردن کوئری به کمپین‌های فعال
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }
    
    /**
     * Check if campaign is currently active
     * بررسی فعال بودن کمپین در حال حاضر
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        
        if ($this->start_date->isFuture()) {
            return false;
        }
        
        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }
        
        return true;
    }
}

