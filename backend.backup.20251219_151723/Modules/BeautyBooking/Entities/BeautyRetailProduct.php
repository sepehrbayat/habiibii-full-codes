<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Beauty Retail Product Model
 * مدل محصول خرده‌فروشی
 *
 * @property int $id
 * @property int $salon_id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property string|null $image
 * @property string|null $category
 * @property int $stock_quantity
 * @property int $min_stock_level
 * @property bool $status
 */
class BeautyRetailProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    protected $casts = [
        'salon_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'price' => 'float',
        'image' => 'string',
        'category' => 'string',
        'stock_quantity' => 'integer',
        'min_stock_level' => 'integer',
        'status' => 'boolean',
    ];
    
    /**
     * Get the salon that owns this product
     * دریافت سالنی که این محصول متعلق به آن است
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Get all orders containing this product
     * دریافت تمام سفارشات حاوی این محصول
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        // Use whereRaw with JSON_CONTAINS for efficient JSON array search
        // استفاده از whereRaw با JSON_CONTAINS برای جستجوی کارآمد آرایه JSON
        // Parameter is properly bound to prevent SQL injection
        // پارامتر به درستی bind شده است تا از SQL injection جلوگیری شود
        // JSON_CONTAINS with JSON_OBJECT is MySQL-optimized and more efficient than Laravel's whereJsonContains for this use case
        // JSON_CONTAINS با JSON_OBJECT بهینه‌سازی MySQL است و برای این مورد استفاده کارآمدتر از whereJsonContains لاراول است
        return $this->hasMany(BeautyRetailOrder::class, 'salon_id', 'salon_id')
            ->whereRaw('JSON_CONTAINS(products, JSON_OBJECT("product_id", ?))', [$this->id]);
    }
    
    /**
     * Scope a query to only include active products
     * محدود کردن کوئری به محصولات فعال
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    /**
     * Scope a query to only include in-stock products
     * محدود کردن کوئری به محصولات موجود
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }
    
    /**
     * Check if product is in stock
     * بررسی موجود بودن محصول
     *
     * @param int $quantity
     * @return bool
     */
    public function isInStock(int $quantity = 1): bool
    {
        return $this->stock_quantity >= $quantity;
    }
    
    /**
     * Check if product is low on stock
     * بررسی کم بودن موجودی محصول
     *
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }
}

