<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * Beauty Gift Card Model
 * مدل کارت هدیه
 *
 * @property int $id
 * @property string $code
 * @property int|null $salon_id
 * @property int $purchased_by
 * @property int|null $redeemed_by
 * @property float $amount
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $redeemed_at
 */
class BeautyGiftCard extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'code' => 'string',
        'salon_id' => 'integer',
        'purchased_by' => 'integer',
        'redeemed_by' => 'integer',
        'amount' => 'float',
        'status' => 'string',
        'expires_at' => 'date',
        'redeemed_at' => 'datetime',
    ];
    
    /**
     * Get the salon for this gift card (nullable - can be general)
     * دریافت سالن این کارت هدیه (اختیاری - می‌تواند عمومی باشد)
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Get the user who purchased this gift card
     * دریافت کاربری که این کارت هدیه را خریده است
     *
     * @return BelongsTo
     */
    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by', 'id');
    }
    
    /**
     * Get the user who redeemed this gift card
     * دریافت کاربری که این کارت هدیه را استفاده کرده است
     *
     * @return BelongsTo
     */
    public function redeemer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by', 'id');
    }
    
    /**
     * Check if gift card is valid for use
     * بررسی معتبر بودن کارت هدیه برای استفاده
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        
        if ($this->expires_at && Carbon::parse($this->expires_at)->isPast()) {
            return false;
        }
        
        return true;
    }
}

