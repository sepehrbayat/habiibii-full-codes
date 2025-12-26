<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use App\CentralLogics\Helpers;
use App\Models\Storage;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Beauty Service Category Model
 * مدل دسته‌بندی خدمات
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $image
 * @property bool $status
 * @property int $sort_order
 */
class BeautyServiceCategory extends Model
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
        return \Modules\BeautyBooking\Database\Factories\BeautyServiceCategoryFactory::new();
    }
    
    protected $casts = [
        'parent_id' => 'integer',
        'name' => 'string',
        'image' => 'string',
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];
    
    protected $appends = ['image_full_url'];
    
    /**
     * Get the parent category
     * دریافت دسته‌بندی والد
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(BeautyServiceCategory::class, 'parent_id', 'id');
    }
    
    /**
     * Get child categories
     * دریافت دسته‌بندی‌های فرزند
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(BeautyServiceCategory::class, 'parent_id', 'id');
    }
    
    /**
     * Get all services in this category
     * دریافت تمام خدمات این دسته‌بندی
     *
     * @return HasMany
     */
    public function services(): HasMany
    {
        return $this->hasMany(BeautyService::class, 'category_id', 'id');
    }
    
    /**
     * Get translations for this category
     * دریافت ترجمه‌های این دسته‌بندی
     *
     * @return MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }
    
    /**
     * Get storage for this category
     * دریافت ذخیره‌سازی این دسته‌بندی
     *
     * @return MorphMany
     */
    public function storage(): MorphMany
    {
        return $this->morphMany(Storage::class, 'data');
    }
    
    /**
     * Get image full URL attribute
     * دریافت URL کامل تصویر
     *
     * @return string
     */
    public function getImageFullUrlAttribute(): string
    {
        $imageName = $this->image ?? '';
        $storagePath = 'public';

        if ($this->relationLoaded('storage') && $this->storage->count() > 0) {
            foreach ($this->storage as $storage) {
                if (($storage['key'] ?? null) === 'image' && !empty($storage['value'])) {
                    $storagePath = $storage['value'];
                    break;
                }
            }
        }

        $resolvedImage = $imageName !== '' ? $imageName : 'placeholder.png';
        $url = Helpers::get_full_url('category', $resolvedImage, $storagePath);

        return is_string($url) && $url !== '' ? $url : url('images/placeholder.png');
    }
    
    /**
     * Get name attribute with translation support
     * دریافت نام با پشتیبانی ترجمه
     *
     * @param mixed $value
     * @return mixed
     */
    public function getNameAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'name') {
                    return $translation['value'];
                }
            }
        }
        
        return $value;
    }
    
    /**
     * Scope a query to only include active categories
     * محدود کردن کوئری به دسته‌بندی‌های فعال
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    protected static function booted()
    {
        static::addGlobalScope('storage', function ($builder) {
            $builder->with('storage');
        });
        
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }
}

