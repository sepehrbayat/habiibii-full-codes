<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use App\Models\Zone;
use App\Traits\ReportFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Beauty Transaction Model
 * مدل تراکنش مالی
 *
 * @property int $id
 * @property int|null $booking_id
 * @property int $salon_id
 * @property int|null $zone_id
 * @property string $transaction_type
 * @property float $amount
 * @property float $commission
 * @property float $service_fee
 * @property string|null $reference_number
 * @property string $status
 * @property string|null $notes
 */
class BeautyTransaction extends Model
{
    use HasFactory, ReportFilter;

    protected $guarded = ['id'];
    
    protected $casts = [
        'booking_id' => 'integer',
        'salon_id' => 'integer',
        'zone_id' => 'integer',
        'transaction_type' => 'string',
        'amount' => 'float',
        'commission' => 'float',
        'service_fee' => 'float',
        'reference_number' => 'string',
        'status' => 'string',
        'notes' => 'string',
    ];
    
    /**
     * Get the booking for this transaction (nullable)
     * دریافت رزرو این تراکنش (اختیاری)
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(BeautyBooking::class, 'booking_id', 'id');
    }
    
    /**
     * Get the salon for this transaction
     * دریافت سالن این تراکنش
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Get the zone for this transaction
     * دریافت منطقه این تراکنش
     *
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id', 'id');
    }
}

