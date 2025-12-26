<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Console\Commands;

use Illuminate\Console\Command;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Emails\BookingReminder;
use Modules\BeautyBooking\Traits\BeautyPushNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Send Booking Reminders Command
 * دستور ارسال یادآوری رزرو
 *
 * Sends reminder notifications 24 hours before booking time
 * ارسال نوتیفیکیشن یادآوری 24 ساعت قبل از زمان رزرو
 */
class SendBookingReminders extends Command
{
    use BeautyPushNotification;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beautybooking:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send booking reminder notifications 24 hours before booking time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Starting booking reminders process...');
        
        // Get reminder hours from config
        // دریافت ساعات یادآوری از config
        $reminderHours = config('beautybooking.booking.reminder_hours_before', 24);
        
        // Calculate precise reminder window (e.g., 24h to 25h from now)
        // محاسبه بازه دقیق یادآوری (مثلاً از ۲۴ تا ۲۵ ساعت بعد از اکنون)
        $windowStart = Carbon::now()->addHours($reminderHours);
        $windowEnd = $windowStart->copy()->addHour();
        
        // Find bookings that need reminders
        // یافتن رزروهایی که نیاز به یادآوری دارند
        // Get bookings confirmed and not completed/cancelled, within the reminder window
        // دریافت رزروهای تأیید شده و تکمیل/لغو نشده، در بازه یادآوری
        $bookings = BeautyBooking::where('status', 'confirmed')
            ->whereBetween(
                DB::raw("COALESCE(booking_date_time, CONCAT(booking_date, ' ', booking_time))"),
                [$windowStart->format('Y-m-d H:i:s'), $windowEnd->format('Y-m-d H:i:s')]
            )
            ->whereDoesntHave('review') // Only send if not already reviewed
            ->with(['user', 'salon.store', 'service', 'staff'])
            ->get();
        
        $sentCount = 0;
        $failedCount = 0;
        
        foreach ($bookings as $booking) {
            try {
                // Send email reminder
                // ارسال ایمیل یادآوری
                if (config('mail.status') && config('beautybooking.notification.channels.email', true)) {
                    Mail::to($booking->user->email)->send(new BookingReminder($booking->id));
                }
                
                // Send push notification
                // ارسال نوتیفیکیشن پوش
                if (config('beautybooking.notification.channels.push', true)) {
                    $this->sendBookingNotificationCustomer($booking, 'reminder');
                }
                
                $sentCount++;
                
            } catch (\Exception $e) {
                Log::error('Failed to send booking reminder', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }
        
        $this->info("Reminders sent: {$sentCount}, Failed: {$failedCount}");
        
        return Command::SUCCESS;
    }
}

