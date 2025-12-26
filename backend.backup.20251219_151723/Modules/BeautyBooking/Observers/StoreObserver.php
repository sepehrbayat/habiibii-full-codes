<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Observers;

use App\Models\Store;
use Modules\BeautyBooking\Services\BeautyBadgeService;

/**
 * Store Observer
 * Observer برای Store
 *
 * Handles badge cache invalidation when store status changes
 * مدیریت باطل کردن cache نشان هنگام تغییر وضعیت فروشگاه
 */
class StoreObserver
{
    /**
     * Handle the Store "saved" event.
     * مدیریت رویداد "saved" Store
     *
     * @param Store $store
     * @return void
     */
    public function saved(Store $store): void
    {
        // Only proceed if BeautyBooking module is active
        // فقط در صورت فعال بودن ماژول BeautyBooking ادامه دهید
        if (!addon_published_status('BeautyBooking')) {
            return;
        }
        
        // IMPORTANT: isDirty() doesn't work in saved/updated events because Laravel clears dirty state before firing
        // مهم: isDirty() در رویدادهای saved/updated کار نمی‌کند چون Laravel وضعیت dirty را قبل از fire کردن پاک می‌کند
        // Use wasChanged() instead, which checks if attributes were changed in the last save operation
        // به جای آن از wasChanged() استفاده کنید، که بررسی می‌کند آیا ویژگی‌ها در آخرین عملیات ذخیره تغییر کرده‌اند
        if ($store->wasChanged(['status', 'active'])) {
            // Invalidate badge cache for all salons belonging to this store
            // باطل کردن cache نشان برای تمام سالن‌های متعلق به این فروشگاه
            BeautyBadgeService::invalidateBadgeCacheForStore($store->id);
        }
    }
    
    /**
     * Handle the Store "updated" event.
     * مدیریت رویداد "updated" Store
     *
     * NOTE: This method is intentionally empty because Laravel fires the "saved" event
     * after "updated", so saved() will handle both create and update events automatically.
     * 
     * توجه: این متد به عمد خالی است چون Laravel رویداد "saved" را بعد از "updated" fire می‌کند،
     * بنابراین saved() به طور خودکار هر دو رویداد create و update را مدیریت می‌کند.
     *
     * @param Store $store
     * @return void
     */
    public function updated(Store $store): void
    {
        // Do nothing - saved() event will handle this automatically
        // هیچ کاری انجام ندهید - رویداد saved() به طور خودکار این را مدیریت می‌کند
    }
}

