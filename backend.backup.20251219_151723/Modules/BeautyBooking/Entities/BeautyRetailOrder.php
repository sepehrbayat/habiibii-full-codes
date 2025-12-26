<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use App\Models\User;
use App\Models\Zone;
use App\Scopes\ZoneScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Beauty Retail Order Model
 * مدل سفارش خرده‌فروشی
 *
 * @property int $id
 * @property int $user_id
 * @property int $salon_id
 * @property int|null $zone_id
 * @property string $order_reference
 * @property array $products
 * @property float $subtotal
 * @property float $tax_amount
 * @property float $shipping_fee
 * @property float $discount
 * @property float $total_amount
 * @property float $commission_amount
 * @property string $status
 * @property string $payment_status
 * @property string|null $payment_method
 * @property string|null $shipping_address
 * @property string|null $shipping_phone
 */
class BeautyRetailOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    /**
     * Boot the model
     * بوت مدل
     *
     * @return void
     */
    protected static function booted(): void
    {
        // Add ZoneScope for admin zone filtering
        // افزودن ZoneScope برای فیلتر منطقه ادمین
        static::addGlobalScope(new ZoneScope);
    }
    
    protected $casts = [
        'user_id' => 'integer',
        'salon_id' => 'integer',
        'zone_id' => 'integer',
        'order_reference' => 'string',
        'products' => 'array',
        'subtotal' => 'float',
        'tax_amount' => 'float',
        'shipping_fee' => 'float',
        'discount' => 'float',
        'total_amount' => 'float',
        'commission_amount' => 'float',
        'status' => 'string',
        'payment_status' => 'string',
        'payment_method' => 'string',
        'shipping_address' => 'string',
        'shipping_phone' => 'string',
        'notes' => 'string',
    ];
    
    /**
     * Get the user who placed the order
     * دریافت کاربری که سفارش را ثبت کرده است
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    /**
     * Get the salon
     * دریافت سالن
     *
     * @return BelongsTo
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(BeautySalon::class, 'salon_id', 'id');
    }
    
    /**
     * Get the zone
     * دریافت منطقه
     *
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id', 'id');
    }
    
    /**
     * Scope a query to filter by status
     * محدود کردن کوئری بر اساس وضعیت
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope a query to only include pending orders
     * محدود کردن کوئری به سفارشات در انتظار
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}

