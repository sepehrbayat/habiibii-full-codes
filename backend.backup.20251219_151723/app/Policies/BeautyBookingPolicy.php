<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use App\Models\Vendor;
use Modules\BeautyBooking\Entities\BeautyBooking;

/**
 * Beauty Booking Policy
 * Policy برای رزرو زیبایی
 *
 * Handles authorization for booking-related actions
 * مدیریت مجوزها برای عملیات مربوط به رزرو
 */
class BeautyBookingPolicy
{
    /**
     * Determine if admin can view any bookings
     * تعیین اینکه آیا ادمین می‌تواند رزروها را مشاهده کند
     *
     * @param Admin $admin
     * @return bool
     */
    public function viewAny(Admin $admin): bool
    {
        return module_permission_check('beauty_booking', $admin);
    }

    /**
     * Determine if admin can view the booking
     * تعیین اینکه آیا ادمین می‌تواند رزرو را مشاهده کند
     *
     * @param Admin $admin
     * @param BeautyBooking $booking
     * @return bool
     */
    public function view(Admin $admin, BeautyBooking $booking): bool
    {
        return module_permission_check('beauty_booking', $admin);
    }

    /**
     * Determine if admin can update the booking
     * تعیین اینکه آیا ادمین می‌تواند رزرو را به‌روزرسانی کند
     *
     * @param Admin $admin
     * @param BeautyBooking $booking
     * @return bool
     */
    public function update(Admin $admin, BeautyBooking $booking): bool
    {
        return module_permission_check('beauty_booking', $admin);
    }

    /**
     * Determine if admin can cancel the booking
     * تعیین اینکه آیا ادمین می‌تواند رزرو را لغو کند
     *
     * @param Admin $admin
     * @param BeautyBooking $booking
     * @return bool
     */
    public function cancel(Admin $admin, BeautyBooking $booking): bool
    {
        return module_permission_check('beauty_booking', $admin);
    }

    /**
     * Determine if customer can view their own booking
     * تعیین اینکه آیا مشتری می‌تواند رزرو خود را مشاهده کند
     *
     * @param User $user
     * @param BeautyBooking $booking
     * @return bool
     */
    public function viewOwn(User $user, BeautyBooking $booking): bool
    {
        return $booking->user_id === $user->id;
    }

    /**
     * Determine if customer can cancel their own booking
     * تعیین اینکه آیا مشتری می‌تواند رزرو خود را لغو کند
     *
     * @param User $user
     * @param BeautyBooking $booking
     * @return bool
     */
    public function cancelOwn(User $user, BeautyBooking $booking): bool
    {
        return $booking->user_id === $user->id && $booking->canCancel();
    }

    /**
     * Determine if vendor can view bookings for their salon
     * تعیین اینکه آیا فروشنده می‌تواند رزروهای سالن خود را مشاهده کند
     *
     * @param Vendor $vendor
     * @param BeautyBooking $booking
     * @return bool
     */
    public function viewSalonBookings(Vendor $vendor, BeautyBooking $booking): bool
    {
        return $booking->salon && 
               $booking->salon->store && 
               $booking->salon->store->vendor_id === $vendor->id;
    }

    /**
     * Determine if vendor can confirm booking for their salon
     * تعیین اینکه آیا فروشنده می‌تواند رزرو سالن خود را تأیید کند
     *
     * @param Vendor $vendor
     * @param BeautyBooking $booking
     * @return bool
     */
    public function confirm(Vendor $vendor, BeautyBooking $booking): bool
    {
        return $booking->salon && 
               $booking->salon->store && 
               $booking->salon->store->vendor_id === $vendor->id &&
               $booking->status === 'pending';
    }

    /**
     * Determine if vendor can cancel booking for their salon
     * تعیین اینکه آیا فروشنده می‌تواند رزرو سالن خود را لغو کند
     *
     * @param Vendor $vendor
     * @param BeautyBooking $booking
     * @return bool
     */
    public function cancelSalonBooking(Vendor $vendor, BeautyBooking $booking): bool
    {
        return $booking->salon && 
               $booking->salon->store && 
               $booking->salon->store->vendor_id === $vendor->id &&
               in_array($booking->status, ['pending', 'confirmed']);
    }
}

