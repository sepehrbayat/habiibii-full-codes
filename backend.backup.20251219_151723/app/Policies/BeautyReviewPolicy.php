<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use Modules\BeautyBooking\Entities\BeautyReview;

/**
 * Beauty Review Policy
 * Policy برای نظر زیبایی
 *
 * Handles authorization for review-related actions
 * مدیریت مجوزها برای عملیات مربوط به نظر
 */
class BeautyReviewPolicy
{
    /**
     * Determine if admin can view any reviews
     * تعیین اینکه آیا ادمین می‌تواند نظرات را مشاهده کند
     *
     * @param Admin $admin
     * @return bool
     */
    public function viewAny(Admin $admin): bool
    {
        return module_permission_check('beauty_review', $admin);
    }

    /**
     * Determine if admin can view the review
     * تعیین اینکه آیا ادمین می‌تواند نظر را مشاهده کند
     *
     * @param Admin $admin
     * @param BeautyReview $review
     * @return bool
     */
    public function view(Admin $admin, BeautyReview $review): bool
    {
        return module_permission_check('beauty_review', $admin);
    }

    /**
     * Determine if admin can approve the review
     * تعیین اینکه آیا ادمین می‌تواند نظر را تأیید کند
     *
     * @param Admin $admin
     * @param BeautyReview $review
     * @return bool
     */
    public function approve(Admin $admin, BeautyReview $review): bool
    {
        return module_permission_check('beauty_review', $admin);
    }

    /**
     * Determine if admin can reject the review
     * تعیین اینکه آیا ادمین می‌تواند نظر را رد کند
     *
     * @param Admin $admin
     * @param BeautyReview $review
     * @return bool
     */
    public function reject(Admin $admin, BeautyReview $review): bool
    {
        return module_permission_check('beauty_review', $admin);
    }

    /**
     * Determine if admin can delete the review
     * تعیین اینکه آیا ادمین می‌تواند نظر را حذف کند
     *
     * @param Admin $admin
     * @param BeautyReview $review
     * @return bool
     */
    public function delete(Admin $admin, BeautyReview $review): bool
    {
        return module_permission_check('beauty_review', $admin);
    }

    /**
     * Determine if customer can create a review
     * تعیین اینکه آیا مشتری می‌تواند نظر ایجاد کند
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create a review
    }

    /**
     * Determine if customer can update their own review
     * تعیین اینکه آیا مشتری می‌تواند نظر خود را به‌روزرسانی کند
     *
     * @param User $user
     * @param BeautyReview $review
     * @return bool
     */
    public function update(User $user, BeautyReview $review): bool
    {
        return $review->user_id === $user->id && 
               $review->status === 'pending';
    }

    /**
     * Determine if customer can delete their own review
     * تعیین اینکه آیا مشتری می‌تواند نظر خود را حذف کند
     *
     * @param User $user
     * @param BeautyReview $review
     * @return bool
     */
    public function deleteOwn(User $user, BeautyReview $review): bool
    {
        return $review->user_id === $user->id;
    }
}

