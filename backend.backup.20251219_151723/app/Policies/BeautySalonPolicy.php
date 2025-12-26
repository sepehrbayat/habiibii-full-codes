<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;
use App\Models\Vendor;
use Modules\BeautyBooking\Entities\BeautySalon;

/**
 * Beauty Salon Policy
 * Policy برای سالن زیبایی
 *
 * Handles authorization for salon-related actions
 * مدیریت مجوزها برای عملیات مربوط به سالن
 */
class BeautySalonPolicy
{
    /**
     * Determine if admin can view any salons
     * تعیین اینکه آیا ادمین می‌تواند سالن‌ها را مشاهده کند
     *
     * @param Admin $admin
     * @return bool
     */
    public function viewAny(Admin $admin): bool
    {
        return module_permission_check('beauty_salon', $admin);
    }

    /**
     * Determine if admin can view the salon
     * تعیین اینکه آیا ادمین می‌تواند سالن را مشاهده کند
     *
     * @param Admin $admin
     * @param BeautySalon $salon
     * @return bool
     */
    public function view(Admin $admin, BeautySalon $salon): bool
    {
        return module_permission_check('beauty_salon', $admin);
    }

    /**
     * Determine if admin can create salons
     * تعیین اینکه آیا ادمین می‌تواند سالن ایجاد کند
     *
     * @param Admin $admin
     * @return bool
     */
    public function create(Admin $admin): bool
    {
        return module_permission_check('beauty_salon', $admin);
    }

    /**
     * Determine if admin can update the salon
     * تعیین اینکه آیا ادمین می‌تواند سالن را به‌روزرسانی کند
     *
     * @param Admin $admin
     * @param BeautySalon $salon
     * @return bool
     */
    public function update(Admin $admin, BeautySalon $salon): bool
    {
        return module_permission_check('beauty_salon', $admin);
    }

    /**
     * Determine if admin can delete the salon
     * تعیین اینکه آیا ادمین می‌تواند سالن را حذف کند
     *
     * @param Admin $admin
     * @param BeautySalon $salon
     * @return bool
     */
    public function delete(Admin $admin, BeautySalon $salon): bool
    {
        return module_permission_check('beauty_salon', $admin);
    }

    /**
     * Determine if admin can approve the salon
     * تعیین اینکه آیا ادمین می‌تواند سالن را تأیید کند
     *
     * @param Admin $admin
     * @param BeautySalon $salon
     * @return bool
     */
    public function approve(Admin $admin, BeautySalon $salon): bool
    {
        return module_permission_check('beauty_salon', $admin);
    }

    /**
     * Determine if vendor can view their own salon
     * تعیین اینکه آیا فروشنده می‌تواند سالن خود را مشاهده کند
     *
     * @param Vendor $vendor
     * @param BeautySalon $salon
     * @return bool
     */
    public function viewOwn(Vendor $vendor, BeautySalon $salon): bool
    {
        return $salon->store && $salon->store->vendor_id === $vendor->id;
    }

    /**
     * Determine if vendor can update their own salon
     * تعیین اینکه آیا فروشنده می‌تواند سالن خود را به‌روزرسانی کند
     *
     * @param Vendor $vendor
     * @param BeautySalon $salon
     * @return bool
     */
    public function updateOwn(Vendor $vendor, BeautySalon $salon): bool
    {
        return $salon->store && $salon->store->vendor_id === $vendor->id;
    }
}

