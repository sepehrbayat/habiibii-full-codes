<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use App\Models\Store;
use App\Models\Zone;
use App\Scopes\ZoneScope;
use App\Traits\ReportFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Beauty Salon Model
 * مدل سالن زیبایی
 *
 * @property int $id
 * @property int $store_id
 * @property int|null $zone_id
 * @property string $business_type
 * @property string|null $license_number
 * @property \Illuminate\Support\Carbon|null $license_expiry
 * @property array|null $documents
 * @property int $verification_status
 * @property string|null $verification_notes
 * @property bool $is_verified
 * @property bool $is_featured
 * @property array|null $working_hours
 * @property array|null $holidays
 * @property float $avg_rating
 * @property int $total_bookings
 * @property int $total_reviews
 * @property int $total_cancellations
 * @property float $cancellation_rate
 */
class BeautySalon extends Model
{
    use HasFactory, SoftDeletes, ReportFilter;

    protected $guarded = ['id'];
    
    /**
     * Create a new factory instance for the model.
     * ایجاد instance جدید factory برای مدل
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\BeautyBooking\Database\Factories\BeautySalonFactory::new();
    }
    
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
        'store_id' => 'integer',
        'zone_id' => 'integer',
        'business_type' => 'string',
        'license_number' => 'string',
        'license_expiry' => 'date',
        'documents' => 'array',
        'verification_status' => 'integer',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'working_hours' => 'array',
        'holidays' => 'array',
        'avg_rating' => 'float',
        'total_bookings' => 'integer',
        'total_reviews' => 'integer',
        'total_cancellations' => 'integer',
        'cancellation_rate' => 'float',
    ];
    
    protected $appends = ['badges_list', 'verification_status_text'];
    
    /**
     * Get the store that owns this salon
     * دریافت فروشگاهی که این سالن متعلق به آن است
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
    
    /**
     * Get the zone for this salon
     * دریافت منطقه این سالن
     *
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id', 'id');
    }
    
    /**
     * Get all staff members for this salon
     * دریافت تمام کارمندان این سالن
     *
     * @return HasMany
     */
    public function staff(): HasMany
    {
        return $this->hasMany(BeautyStaff::class, 'salon_id', 'id');
    }
    
    /**
     * Get all services for this salon
     * دریافت تمام خدمات این سالن
     *
     * @return HasMany
     */
    public function services(): HasMany
    {
        return $this->hasMany(BeautyService::class, 'salon_id', 'id');
    }
    
    /**
     * Get all bookings for this salon
     * دریافت تمام رزروهای این سالن
     *
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(BeautyBooking::class, 'salon_id', 'id');
    }
    
    /**
     * Get all reviews for this salon
     * دریافت تمام نظرات این سالن
     *
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(BeautyReview::class, 'salon_id', 'id');
    }
    
    /**
     * Get all badges for this salon
     * دریافت تمام نشان‌های این سالن
     *
     * @return HasMany
     */
    public function badges(): HasMany
    {
        return $this->hasMany(BeautyBadge::class, 'salon_id', 'id');
    }
    
    /**
     * Get all subscriptions for this salon
     * دریافت تمام اشتراک‌های این سالن
     *
     * @return HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(BeautySubscription::class, 'salon_id', 'id');
    }
    
    /**
     * Get all loyalty campaigns for this salon
     * دریافت تمام کمپین‌های وفاداری این سالن
     *
     * @return HasMany
     */
    public function loyaltyCampaigns(): HasMany
    {
        return $this->hasMany(\Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign::class, 'salon_id', 'id');
    }
    
    /**
     * Get active subscription for this salon
     * دریافت اشتراک فعال این سالن
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function activeSubscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BeautySubscription::class, 'salon_id', 'id')
            ->where('status', 'active')
            ->where('end_date', '>', now());
    }
    
    /**
     * Scope a query to only include verified salons
     * محدود کردن کوئری به سالن‌های تأیید شده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true)
            ->where('verification_status', 1);
    }
    
    /**
     * Scope a query to only include featured salons
     * محدود کردن کوئری به سالن‌های ویژه
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->whereHas('subscriptions', function($q) {
                $q->where('status', 'active')
                  ->where('end_date', '>=', now()->toDateString());
            });
    }
    
    /**
     * Scope a query to only include top rated salons
     * محدود کردن کوئری به سالن‌های دارای رتبه بالا
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTopRated($query)
    {
        $minRating = config('beautybooking.badge.top_rated.min_rating', 4.8);
        $minBookings = config('beautybooking.badge.top_rated.min_bookings', 50);
        
        // Requirement: "میانگین امتیاز بالاتر از ۴.۸" means > 4.8 (strictly greater than)
        // الزامات: "میانگین امتیاز بالاتر از ۴.۸" یعنی > 4.8 (به طور دقیق بیشتر از)
        // Note: Badge requirement is optional - salons can be top-rated even without badge
        // توجه: نیاز به badge اختیاری است - سالن‌ها می‌توانند top-rated باشند حتی بدون badge
        // This allows queries to work even if badges haven't been generated yet
        // این اجازه می‌دهد کوئری‌ها کار کنند حتی اگر badgeها هنوز تولید نشده باشند
        // Simply check rating and bookings criteria - badges are optional visual indicators
        // به سادگی معیارهای رتبه و رزروها را بررسی کنید - badgeها نشانگرهای بصری اختیاری هستند
        return $query->where('avg_rating', '>', $minRating)
            ->where('total_bookings', '>=', $minBookings);
    }
    
    /**
     * Scope a query to only include active salons
     * محدود کردن کوئری به سالن‌های فعال
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('verification_status', 1)
            ->where('is_verified', true)
            ->whereHas('store', function($q) {
                $q->where('status', 1)->where('active', 1);
            });
    }
    
    /**
     * Get badges list attribute
     * دریافت لیست نشان‌ها
     *
     * @return array
     */
    public function getBadgesListAttribute(): array
    {
        return $this->badges()->active()->pluck('badge_type')->toArray();
    }
    
    /**
     * Get verification status text attribute
     * دریافت متن وضعیت تأیید
     *
     * @return string
     */
    public function getVerificationStatusTextAttribute(): string
    {
        return match($this->verification_status) {
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Rejected',
            default => 'Unknown'
        };
    }
}

