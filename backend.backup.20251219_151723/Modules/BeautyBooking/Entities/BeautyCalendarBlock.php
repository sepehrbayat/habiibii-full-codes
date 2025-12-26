<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Beauty Calendar Block Model
 * مدل بلاک تقویم
 *
 * @property int $id
 * @property int $salon_id
 * @property int|null $staff_id
 * @property string $date
 * @property string $start_time
 * @property string $end_time
 * @property string $type
 * @property string|null $reason
 */
class BeautyCalendarBlock extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'salon_id' => 'integer',
        'staff_id' => 'integer',
        'date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'type' => 'string',
        'reason' => 'string',
    ];
    
    /**
     * Get the salon for this block
     * دریافت سالن این بلاک
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Get the staff member for this block
     * دریافت کارمند این بلاک
     *
     * @return BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(BeautyStaff::class, 'staff_id', 'id');
    }
}

