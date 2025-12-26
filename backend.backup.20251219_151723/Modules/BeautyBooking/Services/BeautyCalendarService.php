<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Services;

use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyStaff;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;
use Illuminate\Database\QueryException;
use PDOException;
use Carbon\Carbon;

/**
 * Beauty Calendar Service
 * سرویس تقویم زیبایی
 *
 * Handles all calendar and availability-related logic
 * مدیریت تمام منطق تقویم و دسترسی‌پذیری
 */
class BeautyCalendarService
{
    /**
     * Check if time slot is available
     * بررسی دسترسی‌پذیری زمان
     *
     * @param int $salonId
     * @param int|null $staffId
     * @param string $date
     * @param string $time
     * @param int $durationMinutes
     * @return bool
     */
    public function isTimeSlotAvailable(int $salonId, ?int $staffId, string $date, string $time, int $durationMinutes): bool
    {
        try {
            // Check if within working hours
            // بررسی ساعات کاری
            if (!$this->isWithinWorkingHours($salonId, $staffId, $date, $time)) {
                return false;
            }
            
            // Check if staff is available (days_off and breaks)
            // بررسی دسترسی کارمند (days_off و breaks)
            if ($staffId && !$this->isStaffAvailable($staffId, $date, $time, $durationMinutes)) {
                return false;
            }
            
            // Check if holiday
            // بررسی تعطیلات
            if ($this->isHoliday($salonId, $date)) {
                return false;
            }
            
            // Check calendar blocks
            // بررسی بلاک‌های تقویم
            if ($this->hasCalendarBlock($salonId, $staffId, $date, $time, $durationMinutes)) {
                return false;
            }
            
            // Check existing bookings
            // بررسی رزروهای موجود
            if ($this->hasOverlappingBooking($salonId, $staffId, $date, $time, $durationMinutes)) {
                return false;
            }
            
            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            // Critical error: Database connection or query failure
            // خطای بحرانی: خطای اتصال دیتابیس یا کوئری
            \Log::critical('Database error in time slot availability check: ' . $e->getMessage(), [
                'salon_id' => $salonId,
                'staff_id' => $staffId,
                'date' => $date,
                'time' => $time,
                'exception_type' => 'QueryException',
                'severity' => 'critical',
            ]);
            // Re-throw critical database errors - they should not be silently ignored
            // پرتاب مجدد خطاهای بحرانی دیتابیس - نباید به صورت خاموش نادیده گرفته شوند
            throw $e;
        } catch (\PDOException $e) {
            // Critical error: Database connection failure
            // خطای بحرانی: خطای اتصال دیتابیس
            \Log::critical('PDO error in time slot availability check: ' . $e->getMessage(), [
                'salon_id' => $salonId,
                'staff_id' => $staffId,
                'date' => $date,
                'time' => $time,
                'exception_type' => 'PDOException',
                'severity' => 'critical',
            ]);
            // Re-throw critical database connection errors
            // پرتاب مجدد خطاهای بحرانی اتصال دیتابیس
            throw $e;
        } catch (\Exception $e) {
            // Expected business logic errors (invalid input, validation failures, etc.)
            // خطاهای منطق کسب‌وکار مورد انتظار (ورودی نامعتبر، خطاهای اعتبارسنجی و غیره)
            \Log::warning('Time slot availability check failed (expected error): ' . $e->getMessage(), [
                'salon_id' => $salonId,
                'staff_id' => $staffId,
                'date' => $date,
                'time' => $time,
                'exception_type' => get_class($e),
                'severity' => 'warning',
            ]);
            // Return false for expected business logic errors
            // برگرداندن false برای خطاهای منطق کسب‌وکار مورد انتظار
            return false;
        }
    }
    
    /**
     * Get available time slots for a date
     * دریافت زمان‌های خالی برای یک تاریخ
     *
     * @param int $salonId
     * @param int|null $staffId
     * @param string $date
     * @param int $durationMinutes
     * @param int $intervalMinutes Default 30 minutes
     * @return array
     */
    public function getAvailableTimeSlots(int $salonId, ?int $staffId, string $date, int $durationMinutes, int $intervalMinutes = 30): array
    {
        $availableSlots = [];
        
        // Get working hours for this date
        // دریافت ساعات کاری برای این تاریخ
        $workingHours = $this->getWorkingHours($salonId, $staffId, $date);
        
        if (!$workingHours) {
            return [];
        }
        
        $startTime = Carbon::parse($date . ' ' . $workingHours['open']);
        $endTime = Carbon::parse($date . ' ' . $workingHours['close']);
        $currentTime = $startTime->copy();
        
        while ($currentTime->copy()->addMinutes($durationMinutes)->lte($endTime)) {
            $timeSlot = $currentTime->format('H:i');
            
            if ($this->isTimeSlotAvailable($salonId, $staffId, $date, $timeSlot, $durationMinutes)) {
                $availableSlots[] = $timeSlot;
            }
            
            $currentTime->addMinutes($intervalMinutes);
        }
        
        return $availableSlots;
    }
    
    /**
     * Block a time slot
     * بلاک کردن یک زمان
     *
     * @param int $salonId
     * @param int|null $staffId
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param string $type
     * @param string|null $reason
     * @return BeautyCalendarBlock
     */
    public function blockTimeSlot(int $salonId, ?int $staffId, string $date, string $startTime, string $endTime, string $type = 'manual_block', ?string $reason = null): BeautyCalendarBlock
    {
        try {
            return BeautyCalendarBlock::create([
                'salon_id' => $salonId,
                'staff_id' => $staffId,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'type' => $type,
                'reason' => $reason,
            ]);
        } catch (\Exception $e) {
            \Log::error('Calendar block creation failed: ' . $e->getMessage(), [
                'salon_id' => $salonId,
                'staff_id' => $staffId,
                'date' => $date,
            ]);
            throw $e;
        }
    }
    
    /**
     * Unblock a time slot
     * آزاد کردن یک زمان
     *
     * @param int $blockId
     * @return bool
     */
    public function unblockTimeSlot(int $blockId): bool
    {
        try {
            $block = BeautyCalendarBlock::findOrFail($blockId);
            return $block->delete();
        } catch (\Exception $e) {
            \Log::error('Calendar block deletion failed: ' . $e->getMessage(), [
                'block_id' => $blockId,
            ]);
            throw $e;
        }
    }
    
    /**
     * Unblock time slot for booking
     * آزاد کردن زمان برای رزرو
     *
     * @param BeautyBooking $booking
     * @return void
     */
    public function unblockTimeSlotForBooking(BeautyBooking $booking): void
    {
        // Find and remove all blocks created for this booking
        // پیدا کردن و حذف تمام بلاک‌های ایجاد شده برای این رزرو
        // Only delete blocks that actually reference this booking in the reason field
        // فقط بلاک‌هایی را حذف کنید که واقعاً به این رزرو در فیلد reason اشاره می‌کنند
        // This prevents deleting unrelated manual blocks (e.g., "lunch break") at the same time slot
        // این از حذف بلاک‌های دستی نامرتبط (مثلاً "ناهار") در همان زمان جلوگیری می‌کند
        BeautyCalendarBlock::where('salon_id', $booking->salon_id)
            ->where(function($q) use ($booking) {
                // Match blocks for the same staff (if staff was assigned)
                // مطابقت بلاک‌ها برای همان کارمند (اگر کارمند اختصاص داده شده باشد)
                if ($booking->staff_id) {
                    $q->where('staff_id', $booking->staff_id);
                } else {
                    // If no staff assigned, match blocks with null staff_id (salon-level blocks)
                    // اگر کارمند اختصاص داده نشده باشد، بلاک‌های با staff_id null را مطابقت دهید (بلاک‌های سطح سالن)
                    $q->whereNull('staff_id');
                }
            })
            ->where('date', $booking->booking_date)
            ->where('start_time', $booking->booking_time)
            ->where(function($q) use ($booking) {
                // Only delete blocks that reference this booking in the reason field (case-insensitive)
                // فقط بلاک‌هایی را حذف کنید که به این رزرو در فیلد reason اشاره می‌کنند (بدون حساسیت به حروف)
                // This ensures we only delete blocks created for this specific booking
                // این اطمینان می‌دهد که فقط بلاک‌های ایجاد شده برای این رزرو خاص را حذف می‌کنیم
                $bookingReference = $booking->booking_reference;
                $q->where('reason', 'LIKE', '%Booking #' . $bookingReference . '%')
                  ->orWhere('reason', 'LIKE', '%booking #' . $bookingReference . '%')
                  ->orWhere('reason', 'LIKE', '%BOOKING #' . $bookingReference . '%');
            })
            ->delete();
    }
    
    /**
     * Check if time is within working hours
     * بررسی ساعات کاری
     *
     * @param int $salonId
     * @param int|null $staffId
     * @param string $date
     * @param string $time
     * @return bool
     */
    public function isWithinWorkingHours(int $salonId, ?int $staffId, string $date, string $time): bool
    {
        $workingHours = $this->getWorkingHours($salonId, $staffId, $date);
        
        if (!$workingHours || !isset($workingHours['open']) || !isset($workingHours['close'])) {
            return false;
        }
        
        return $time >= $workingHours['open'] && $time <= $workingHours['close'];
    }
    
    /**
     * Get working hours for a date
     * دریافت ساعات کاری برای یک تاریخ
     *
     * @param int $salonId
     * @param int|null $staffId
     * @param string $date
     * @return array|null
     */
    public function getWorkingHours(int $salonId, ?int $staffId, string $date): ?array
    {
        // If staff is specified, use staff working hours, otherwise use salon hours
        // اگر کارمند مشخص شده باشد، از ساعات کاری کارمند استفاده کنید، در غیر این صورت از ساعات سالن استفاده کنید
        if ($staffId) {
            $staff = BeautyStaff::findOrFail($staffId);
            $workingHours = $staff->working_hours ?? [];
        } else {
            $salon = BeautySalon::findOrFail($salonId);
            $workingHours = $salon->working_hours ?? [];
        }
        
        $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));
        
        return $workingHours[$dayOfWeek] ?? null;
    }
    
    /**
     * Check if date is a holiday
     * بررسی تعطیلات
     *
     * @param int $salonId
     * @param string $date
     * @return bool
     */
    public function isHoliday(int $salonId, string $date): bool
    {
        $salon = BeautySalon::findOrFail($salonId);
        $holidays = $salon->holidays ?? [];
        
        return in_array($date, $holidays);
    }
    
    /**
     * Check if staff is available (days_off and breaks)
     * بررسی دسترسی کارمند (days_off و breaks)
     *
     * @param int $staffId
     * @param string $date
     * @param string $time Start time of booking
     * @param int $durationMinutes Duration of booking in minutes
     * @return bool
     */
    private function isStaffAvailable(int $staffId, string $date, string $time, int $durationMinutes): bool
    {
        try {
            $staff = BeautyStaff::findOrFail($staffId);
            
            // Check if staff is active
            // بررسی فعال بودن کارمند
            if (!$staff->status) {
                return false;
            }
            
            // Check if date is in days_off
            // بررسی اینکه آیا تاریخ در days_off است
            $daysOff = $staff->days_off ?? [];
            if (in_array($date, $daysOff)) {
                return false;
            }
            
            // Calculate booking end time
            // محاسبه زمان پایان رزرو
            $bookingStart = \Carbon\Carbon::parse($date . ' ' . $time);
            $bookingEnd = $bookingStart->copy()->addMinutes($durationMinutes);
            
            // Check breaks - ensure entire booking period doesn't overlap with any break
            // بررسی breaks - اطمینان از عدم همپوشانی کل دوره رزرو با هیچ break
            $breaks = $staff->breaks ?? [];
            foreach ($breaks as $break) {
                if (isset($break['date']) && $break['date'] === $date) {
                    if (isset($break['start_time']) && isset($break['end_time'])) {
                        $breakStart = \Carbon\Carbon::parse($date . ' ' . $break['start_time']);
                        $breakEnd = \Carbon\Carbon::parse($date . ' ' . $break['end_time']);
                        
                        // Check if booking period overlaps with break period
                        // بررسی همپوشانی دوره رزرو با دوره break
                        // Overlap occurs if: booking starts before break ends AND booking ends after break starts
                        // همپوشانی زمانی رخ می‌دهد که: رزرو قبل از پایان break شروع شود AND رزرو بعد از شروع break پایان یابد
                        if ($bookingStart->lt($breakEnd) && $bookingEnd->gt($breakStart)) {
                            return false;
                        }
                    }
                }
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Staff availability check failed: ' . $e->getMessage(), [
                'staff_id' => $staffId,
                'date' => $date,
                'time' => $time,
                'duration_minutes' => $durationMinutes,
            ]);
            return false;
        }
    }
    
    /**
     * Check if there's a calendar block
     * بررسی وجود بلاک تقویم
     *
     * @param int $salonId
     * @param int|null $staffId
     * @param string $date
     * @param string $time
     * @param int $durationMinutes
     * @return bool
     */
    private function hasCalendarBlock(int $salonId, ?int $staffId, string $date, string $time, int $durationMinutes): bool
    {
        $endTime = Carbon::parse($date . ' ' . $time)->addMinutes($durationMinutes)->format('H:i:s');
        
        $query = BeautyCalendarBlock::where('salon_id', $salonId)
            ->where('date', $date)
            ->where(function($q) use ($staffId) {
                $q->whereNull('staff_id')
                  ->orWhere('staff_id', $staffId);
            })
            ->where(function($q) use ($time, $endTime) {
                // Check if booking time overlaps with any block
                // بررسی همپوشانی زمان رزرو با بلاک‌ها
                $q->where(function($q2) use ($time, $endTime) {
                    $q2->whereBetween('start_time', [$time, $endTime])
                       ->orWhereBetween('end_time', [$time, $endTime])
                       ->orWhere(function($q3) use ($time, $endTime) {
                           $q3->where('start_time', '<=', $time)
                              ->where('end_time', '>=', $endTime);
                       });
                });
            });
        
        return $query->exists();
    }
    
    /**
     * Check if there's an overlapping booking
     * بررسی وجود رزرو همپوشان
     *
     * @param int $salonId
     * @param int|null $staffId
     * @param string $date
     * @param string $time
     * @param int $durationMinutes
     * @return bool
     */
    private function hasOverlappingBooking(int $salonId, ?int $staffId, string $date, string $time, int $durationMinutes): bool
    {
        // Validate duration is positive
        // اعتبارسنجی اینکه مدت زمان مثبت است
        if ($durationMinutes <= 0) {
            \Log::warning('Invalid service duration in overlap check', [
                'salon_id' => $salonId,
                'duration_minutes' => $durationMinutes,
            ]);
            return true; // Treat invalid duration as overlap to prevent booking
        }
        
        // Parse date and time correctly
        // تجزیه صحیح تاریخ و زمان
        // $date might be a Carbon object if passed from model
        // $date ممکن است یک شیء Carbon باشد اگر از مدل ارسال شده باشد
        $dateString = $date instanceof Carbon ? $date->format('Y-m-d') : (string)$date;
        $bookingDateTime = Carbon::parse($dateString . ' ' . $time);
        $endDateTime = $bookingDateTime->copy()->addMinutes($durationMinutes);
        
        // Note: We don't filter by booking_date to detect overlaps across midnight boundaries
        // توجه: ما بر اساس booking_date فیلتر نمی‌کنیم تا همپوشانی‌های فراتر از مرز نیمه‌شب را تشخیص دهیم
        // (e.g., a booking from 11 PM Jan 15 to 1 AM Jan 16 should conflict with a booking on Jan 16)
        // (مثلاً یک رزرو از 11 شب 15 ژانویه تا 1 صبح 16 ژانویه باید با رزروی در 16 ژانویه تداخل داشته باشد)
        // The booking_date_time overlap checks below handle cross-day scenarios correctly
        // بررسی‌های همپوشانی booking_date_time زیر سناریوهای چند روزه را به درستی مدیریت می‌کنند
        $query = BeautyBooking::where('salon_id', $salonId)
            ->where('status', '!=', 'cancelled');
        
        // If staff_id is null, check if any staff member is available
        // اگر staff_id null است، بررسی کنید که آیا هر کارمندی در دسترس است
        // This allows concurrent bookings when multiple staff are available
        // این اجازه می‌دهد رزروهای همزمان زمانی که چندین کارمند در دسترس هستند
        if ($staffId === null) {
            // First, check for salon-level bookings without staff assignment
            // ابتدا، بررسی رزروهای سطح سالن بدون تخصیص کارمند
            // These bookings conflict with any new unassigned booking at the same time
            // این رزروها با هر رزرو جدید بدون تخصیص در همان زمان تداخل دارند
            $salonLevelOverlapQuery = BeautyBooking::where('salon_id', $salonId)
                ->whereNull('staff_id')
                ->where('status', '!=', 'cancelled')
                ->where(function($q) use ($bookingDateTime, $endDateTime) {
                    $q->where(function($q2) use ($bookingDateTime, $endDateTime) {
                        $q2->whereBetween('booking_date_time', [$bookingDateTime, $endDateTime]);
                    })
                    ->orWhere(function($q3) use ($bookingDateTime, $endDateTime) {
                        $q3->whereRaw('DATE_ADD(booking_date_time, INTERVAL COALESCE((SELECT duration_minutes FROM beauty_services WHERE id = beauty_bookings.service_id), 30) MINUTE) >= ?', [$bookingDateTime])
                           ->where('booking_date_time', '<=', $endDateTime);
                    });
                });
            
            // If there's an existing unassigned booking at this time, overlap exists
            // اگر رزرو بدون تخصیص موجودی در این زمان وجود دارد، همپوشانی وجود دارد
            if ($salonLevelOverlapQuery->exists()) {
                return true;
            }
            
            // Get all active staff for the salon
            // دریافت تمام کارمندان فعال برای سالن
            $activeStaff = BeautyStaff::where('salon_id', $salonId)
                ->where('status', true)
                ->get();
            
            // If no active staff, overlap exists (cannot book)
            // اگر هیچ کارمند فعالی وجود ندارد، همپوشانی وجود دارد (نمی‌توان رزرو کرد)
            if ($activeStaff->isEmpty()) {
                return true;
            }
            
            // Check if at least one staff member is available for this time slot
            // بررسی اینکه آیا حداقل یک کارمند برای این زمان در دسترس است
            $dateString = $date instanceof Carbon ? $date->format('Y-m-d') : (string)$date;
            foreach ($activeStaff as $staff) {
                // Check if staff is available (not on days_off, not in breaks, within working hours)
                // بررسی اینکه آیا کارمند در دسترس است (نه در days_off، نه در breaks، در ساعات کاری)
                if (!$this->isStaffAvailable($staff->id, $dateString, $time, $durationMinutes)) {
                    continue; // This staff is not available, check next
                }
                
                // Check if this staff has any overlapping bookings
                // بررسی اینکه آیا این کارمند رزروهای همپوشانی دارد
                $staffOverlapQuery = BeautyBooking::where('salon_id', $salonId)
                    ->where('staff_id', $staff->id)
                    ->where('status', '!=', 'cancelled')
                    ->where(function($q) use ($bookingDateTime, $endDateTime) {
                        $q->where(function($q2) use ($bookingDateTime, $endDateTime) {
                            $q2->whereBetween('booking_date_time', [$bookingDateTime, $endDateTime]);
                        })
                        ->orWhere(function($q3) use ($bookingDateTime, $endDateTime) {
                            $q3->whereRaw('DATE_ADD(booking_date_time, INTERVAL COALESCE((SELECT duration_minutes FROM beauty_services WHERE id = beauty_bookings.service_id), 30) MINUTE) >= ?', [$bookingDateTime])
                               ->where('booking_date_time', '<=', $endDateTime);
                        });
                    });
                
                // If this staff has no overlapping bookings, they are available
                // اگر این کارمند رزروهای همپوشانی ندارد، در دسترس است
                if (!$staffOverlapQuery->exists()) {
                    // At least one staff is available - no overlap
                    // حداقل یک کارمند در دسترس است - همپوشانی وجود ندارد
                    return false;
                }
            }
            
            // No staff members are available - overlap exists
            // هیچ کارمندی در دسترس نیست - همپوشانی وجود دارد
            return true;
        }
        
        // If staff_id is provided, check only that specific staff for overlaps
        // اگر staff_id ارائه شده است، فقط آن کارمند خاص را برای همپوشانی بررسی کنید
        $query = BeautyBooking::where('salon_id', $salonId)
            ->where('staff_id', $staffId)
            ->where('status', '!=', 'cancelled')
            ->where(function($q) use ($bookingDateTime, $endDateTime) {
                $q->where(function($q2) use ($bookingDateTime, $endDateTime) {
                    $q2->whereBetween('booking_date_time', [$bookingDateTime, $endDateTime]);
                })
                ->orWhere(function($q3) use ($bookingDateTime, $endDateTime) {
                    // Use whereRaw to calculate booking end time dynamically from service duration
                    // استفاده از whereRaw برای محاسبه پویای زمان پایان رزرو از مدت زمان خدمت
                    // Parameter is properly bound to prevent SQL injection
                    // پارامتر به درستی bind شده است تا از SQL injection جلوگیری شود
                    // This subquery is necessary because service duration varies per booking
                    // این subquery ضروری است چون مدت زمان خدمت برای هر رزرو متفاوت است
                    // IMPORTANT: Check overlaps regardless of service existence to prevent double-booking
                    // مهم: بررسی همپوشانی‌ها بدون توجه به وجود خدمت برای جلوگیری از double-booking
                    // If service was deleted, use default 30 minutes to ensure overlap detection still works
                    // اگر خدمت حذف شده است، از پیش‌فرض 30 دقیقه استفاده کنید تا تشخیص همپوشانی همچنان کار کند
                    $q3->whereRaw('DATE_ADD(booking_date_time, INTERVAL COALESCE((SELECT duration_minutes FROM beauty_services WHERE id = beauty_bookings.service_id), 30) MINUTE) >= ?', [$bookingDateTime])
                       ->where('booking_date_time', '<=', $endDateTime);
                });
            });
        
        return $query->exists();
    }
}

