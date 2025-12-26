<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Beauty Commission Setting Model
 * مدل تنظیمات کمیسیون
 *
 * @property int $id
 * @property int|null $service_category_id
 * @property string|null $salon_level
 * @property float $commission_percentage
 * @property float $min_commission
 * @property float|null $max_commission
 * @property bool $status
 */
class BeautyCommissionSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'service_category_id' => 'integer',
        'salon_level' => 'string',
        'commission_percentage' => 'float',
        'min_commission' => 'float',
        'max_commission' => 'float',
        'status' => 'boolean',
    ];
    
    /**
     * Get the service category for this commission setting (nullable for global)
     * دریافت دسته‌بندی خدمت این تنظیمات کمیسیون (اختیاری برای تنظیمات جهانی)
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BeautyServiceCategory::class, 'service_category_id', 'id');
    }
}

