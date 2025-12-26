<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use App\Scopes\StoreScope;
use App\Scopes\ZoneScope;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Modules\Rental\Entities\Trips;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'interest',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_phone_verified' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'order_count' => 'integer',
        'wallet_balance' => 'float',
        'loyalty_point' => 'integer',
        'ref_by' => 'integer',
    ];
    protected $appends = ['image_full_url'];
    public function getImageFullUrlAttribute(){
        $value = $this->image;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'image') {
                    return Helpers::get_full_url('profile',$value,$storage['value']);
                }
            }
        }

        return Helpers::get_full_url('profile',$value,'public');
    }

    public function getFullNameAttribute(): string
    {
        return $this->f_name . ' ' . $this->l_name;
    }

    public function scopeOfStatus($query, $status): void
    {
        $query->where('status', '=', $status);
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->where('is_guest', 0);
    }
    public function trips()
    {
        return $this->hasMany(Trips::class)->where('is_guest', 0);
    }

    /**
     * Get all beauty bookings for this user
     * دریافت تمام رزروهای زیبایی این کاربر
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function beautyBookings()
    {
        // Check if BeautyBooking module is published before creating relationship
        // بررسی اینکه آیا ماژول BeautyBooking منتشر شده قبل از ایجاد رابطه
        // This prevents ClassNotFoundException when module is disabled
        // این از ClassNotFoundException زمانی که ماژول غیرفعال است جلوگیری می‌کند
        
        $bookingClass = 'Modules\BeautyBooking\Entities\BeautyBooking';
        
        // Check module status first (this doesn't require the class to exist)
        // بررسی وضعیت ماژول ابتدا (این به وجود کلاس نیاز ندارد)
        if (!addon_published_status('BeautyBooking')) {
            // Module not published - return relationship using string class name with whereRaw
            // ماژول منتشر نشده - برگرداندن رابطه با استفاده از نام کلاس به صورت رشته همراه با whereRaw
            // The whereRaw('1 = 0') ensures no queries are executed, so class resolution is never triggered
            // whereRaw('1 = 0') اطمینان می‌دهد که هیچ کوئری اجرا نمی‌شود، بنابراین resolve کلاس هرگز trigger نمی‌شود
            // This is semantically correct (uses the correct class name) and functionally safe
            // این از نظر معنایی صحیح است (از نام کلاس صحیح استفاده می‌کند) و از نظر عملکردی ایمن است
            return $this->hasMany($bookingClass, 'user_id', 'id')->whereRaw('1 = 0');
        }
        
        // Check if class exists before trying to use it (allow autoloading since module is published)
        // بررسی وجود کلاس قبل از استفاده از آن (اجازه autoloading چون ماژول منتشر شده است)
        // Use class_exists with autoload=true to allow Laravel's autoloader to load the class if needed
        // استفاده از class_exists با autoload=true برای اجازه دادن به Laravel autoloader برای لود کردن کلاس در صورت نیاز
        if (!class_exists($bookingClass, true)) {
            // Class doesn't exist even after autoloading - return empty relationship as fallback
            // کلاس وجود ندارد حتی پس از autoloading - برگرداندن رابطه خالی به عنوان fallback
            return $this->hasMany($bookingClass, 'user_id', 'id')->whereRaw('1 = 0');
        }
        
        // Module is published and class exists - create normal relationship
        // ماژول منتشر شده و کلاس وجود دارد - ایجاد رابطه عادی
        return $this->hasMany($bookingClass, 'user_id', 'id');
    }

    public function addresses(){
        return $this->hasMany(CustomerAddress::class);
    }

    public function userinfo()
    {
        return $this->hasOne(UserInfo::class,'user_id', 'id');
    }

    public function scopeZone($query, $zone_id=null){
        $query->when(is_numeric($zone_id), function ($q) use ($zone_id) {
            return $q->where('zone_id', $zone_id);
        });
    }

    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }

    protected static function booted()
    {
        static::addGlobalScope('storage', function ($builder) {
            $builder->with('storage');
        });
    }
    protected static function boot()
    {
        parent::boot();
        static::saved(function ($model) {
            if($model->isDirty('image')){
                $value = Helpers::getDisk();

                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'image',
                ], [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

    }
}
