<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Traits;

use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Beauty Booking Logic Trait
 * Trait منطق رزرو زیبایی
 *
 * Handles booking calculations and logic
 * مدیریت محاسبات و منطق رزرو
 */
trait BeautyBookingLogic
{
    /**
     * Calculate price for a service
     * محاسبه قیمت برای یک خدمت
     *
     * @param BeautyService $service
     * @param array $discounts
     * @return float
     */
    public static function calculatePrice(BeautyService $service, array $discounts = []): float
    {
        $price = $service->price;
        
        // Apply discounts
        // اعمال تخفیف‌ها
        foreach ($discounts as $discount) {
            if (isset($discount['type']) && $discount['type'] === 'percentage') {
                $price -= $price * ($discount['value'] / 100);
            } elseif (isset($discount['type']) && $discount['type'] === 'fixed') {
                $price -= $discount['value'];
            }
        }
        
        return max($price, 0); // Ensure price is not negative
    }
    
    /**
     * Calculate service fee
     * محاسبه هزینه سرویس
     *
     * @param float $basePrice
     * @param float $percentage
     * @return float
     */
    public static function calculateServiceFee(float $basePrice, float $percentage): float
    {
        return round($basePrice * ($percentage / 100), 2);
    }
    
    /**
     * Calculate commission
     * محاسبه کمیسیون
     *
     * @param BeautyBooking $booking
     * @param float $percentage
     * @return float
     */
    public static function calculateCommission(BeautyBooking $booking, float $percentage): float
    {
        return round($booking->total_amount * ($percentage / 100), 2);
    }
    
    /**
     * Calculate cancellation fee based on hours until booking
     * محاسبه هزینه لغو بر اساس ساعت تا زمان رزرو
     *
     * @param BeautyBooking $booking
     * @param int $hoursUntil
     * @return float
     */
    public static function calculateCancellationFee(BeautyBooking $booking, int $hoursUntil): float
    {
        if ($hoursUntil >= 24) {
            return 0.0; // No fee if cancelled 24+ hours before
        } elseif ($hoursUntil >= 2) {
            return round($booking->total_amount * 0.5, 2); // 50% fee
        } else {
            return round($booking->total_amount, 2); // 100% fee (full amount)
        }
    }
    
    /**
     * Format booking reference number
     * فرمت شماره مرجع رزرو
     *
     * @param int $bookingId
     * @return string
     */
    public static function formatBookingReference(int $bookingId): string
    {
        // Booking IDs start from 100000, so format as #100001, #100002, etc.
        // شناسه‌های رزرو از 100000 شروع می‌شوند، بنابراین به صورت #100001، #100002 و غیره فرمت می‌شوند
        return '#' . $bookingId;
    }
    
    /**
     * Generate unique booking reference code
     * تولید کد مرجع منحصر به فرد رزرو
     *
     * @return string
     */
    public static function generateBookingReferenceCode(): string
    {
        do {
            $reference = 'BB' . strtoupper(Str::random(8));
        } while (BeautyBooking::where('booking_reference', $reference)->exists());
        
        return $reference;
    }
}

