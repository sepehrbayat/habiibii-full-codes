<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Beauty Service Model
 * مدل خدمات
 *
 * @property int $id
 * @property int $salon_id
 * @property int $category_id
 * @property string $name
 * @property string|null $description
 * @property int $duration_minutes
 * @property float $price
 * @property string|null $image
 * @property bool $status
 * @property array|null $staff_ids
 * @property string $service_type
 * @property float $consultation_credit_percentage
 */
class BeautyService extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    /**
     * Create a new factory instance for the model.
     * ایجاد instance جدید factory برای مدل
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\BeautyBooking\Database\Factories\BeautyServiceFactory::new();
    }
    
    protected $casts = [
        'salon_id' => 'integer',
        'category_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'duration_minutes' => 'integer',
        'price' => 'float',
        'image' => 'string',
        'status' => 'boolean',
        'staff_ids' => 'array',
        'service_type' => 'string',
        'consultation_credit_percentage' => 'float',
    ];
    
    /**
     * Get the salon that owns this service
     * دریافت سالنی که این خدمت متعلق به آن است
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Get the category for this service
     * دریافت دسته‌بندی این خدمت
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BeautyServiceCategory::class, 'category_id', 'id');
    }
    
    /**
     * Get staff members who can perform this service
     * دریافت کارمندان که می‌توانند این خدمت را انجام دهند
     *
     * @return BelongsToMany
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(
            BeautyStaff::class,
            'beauty_service_staff',
            'service_id',
            'staff_id'
        )->withTimestamps();
    }
    
    /**
     * Get all bookings for this service
     * دریافت تمام رزروهای این خدمت
     *
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(BeautyBooking::class, 'service_id', 'id');
    }
    
    /**
     * Get all reviews for this service
     * دریافت تمام نظرات این خدمت
     *
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(BeautyReview::class, 'service_id', 'id');
    }
    
    /**
     * Get all packages for this service
     * دریافت تمام پکیج‌های این خدمت
     *
     * @return HasMany
     */
    public function packages(): HasMany
    {
        return $this->hasMany(BeautyPackage::class, 'service_id', 'id');
    }

    /**
     * Get explicit service relations (cross-sell / complementary / upsell)
     * دریافت روابط صریح خدمت (فروش متقابل / مکمل / افزایش فروش)
     *
     * @return HasMany
     */
    public function relations(): HasMany
    {
        return $this->hasMany(BeautyServiceRelation::class, 'service_id', 'id');
    }

    /**
     * Get active service relations only
     * دریافت روابط فعال خدمت
     *
     * @return HasMany
     */
    public function activeRelations(): HasMany
    {
        return $this->relations()->where('status', 1);
    }
    
    /**
     * Scope a query to only include active services
     * محدود کردن کوئری به خدمات فعال
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    /**
     * Scope a query to only include regular services (not consultations)
     * محدود کردن کوئری به خدمات عادی (نه مشاوره)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRegularServices($query)
    {
        return $query->where('service_type', 'service');
    }
    
    /**
     * Scope a query to only include consultation services
     * محدود کردن کوئری به خدمات مشاوره
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConsultations($query)
    {
        return $query->whereIn('service_type', ['pre_consultation', 'post_consultation']);
    }
    
    /**
     * Scope a query to only include pre-consultation services
     * محدود کردن کوئری به خدمات مشاوره پیش از خدمت
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePreConsultations($query)
    {
        return $query->where('service_type', 'pre_consultation');
    }
    
    /**
     * Scope a query to only include post-consultation services
     * محدود کردن کوئری به خدمات مشاوره پس از خدمت
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePostConsultations($query)
    {
        return $query->where('service_type', 'post_consultation');
    }
    
    /**
     * Check if this is a consultation service
     * بررسی اینکه آیا این یک خدمت مشاوره است
     *
     * @return bool
     */
    public function isConsultation(): bool
    {
        return in_array($this->service_type, ['pre_consultation', 'post_consultation']);
    }
    
    /**
     * Check if this is a pre-consultation service
     * بررسی اینکه آیا این یک خدمت مشاوره پیش از خدمت است
     *
     * @return bool
     */
    public function isPreConsultation(): bool
    {
        return $this->service_type === 'pre_consultation';
    }
    
    /**
     * Check if this is a post-consultation service
     * بررسی اینکه آیا این یک خدمت مشاوره پس از خدمت است
     *
     * @return bool
     */
    public function isPostConsultation(): bool
    {
        return $this->service_type === 'post_consultation';
    }
}

