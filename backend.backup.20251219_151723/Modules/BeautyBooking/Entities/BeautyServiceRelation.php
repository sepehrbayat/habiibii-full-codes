<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Beauty Service Relation Model
 * مدل رابطه خدمات
 *
 * @property int $id
 * @property int $service_id
 * @property int $related_service_id
 * @property string $relation_type
 * @property int $priority
 * @property bool $status
 */
class BeautyServiceRelation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'service_id' => 'integer',
        'related_service_id' => 'integer',
        'relation_type' => 'string',
        'priority' => 'integer',
        'status' => 'boolean',
    ];
    
    /**
     * Get the main service
     * دریافت خدمت اصلی
     *
     * @return BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(BeautyService::class, 'service_id', 'id');
    }
    
    /**
     * Get the related service
     * دریافت خدمت مرتبط
     *
     * @return BelongsTo
     */
    public function relatedService(): BelongsTo
    {
        return $this->belongsTo(BeautyService::class, 'related_service_id', 'id');
    }
    
    /**
     * Scope a query to only include active relations
     * محدود کردن کوئری به روابط فعال
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    /**
     * Scope a query to only include complementary relations
     * محدود کردن کوئری به روابط مکمل
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeComplementary($query)
    {
        return $query->where('relation_type', 'complementary');
    }
    
    /**
     * Scope a query to only include upsell relations
     * محدود کردن کوئری به روابط افزایش فروش
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpsell($query)
    {
        return $query->where('relation_type', 'upsell');
    }
}

