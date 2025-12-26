<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Traits;

use App\CentralLogics\Helpers;
use App\Models\NotificationMessage;
use App\Models\UserNotification;
use Modules\BeautyBooking\Entities\BeautyBooking;

/**
 * Beauty Push Notification Trait
 * Trait نوتیفیکیشن پوش برای زیبایی
 *
 * Handles all push notifications for booking events
 * مدیریت تمام نوتیفیکیشن‌های پوش برای رویدادهای رزرو
 */
trait BeautyPushNotification
{
    /**
     * Get booking status message from notification messages
     * دریافت پیام وضعیت رزرو از پیام‌های نوتیفیکیشن
     *
     * @param string $status
     * @param string $moduleType
     * @param string $lang
     * @return string|false
     */
    public static function getBookingStatusMessage(string $status, string $moduleType = 'beautybooking', string $lang = 'en')
    {
        $statusMap = [
            'confirmed' => 'confirm',
            'completed' => 'complete',
            'cancelled' => 'cancel'
        ];

        $status = $statusMap[$status] ?? $status;
        $status = 'booking_' . $status . '_message';
        $data = NotificationMessage::select(['id', 'message', 'status'])->with(['translations' => function ($query) use ($lang) {
            $query->where('locale', $lang)->limit(1);
        }])->where('module_type', $moduleType)->where('key', $status)->first();
        
        if ($data?->status == 1) {
            return count($data->translations) > 0 ? $data->translations[0]->value : $data['message'];
        }
        return false;
    }

    /**
     * Send booking notification to all parties
     * ارسال نوتیفیکیشن رزرو به همه طرفین
     *
     * @param BeautyBooking $booking
     * @param string $event
     * @return bool
     */
    public static function sendBookingNotificationToAll(BeautyBooking $booking, string $event): bool
    {
        // Eager load relationships to avoid N+1 queries
        // بارگذاری eager روابط برای جلوگیری از کوئری‌های N+1
        $booking->loadMissing(['salon.store.vendor', 'user']);
        
        self::sendBookingNotificationAdminPanel($booking, $event);
        self::sendBookingNotificationSalonPanel($booking, $event);
        self::sendBookingNotificationSalonApp($booking, $event);
        self::sendBookingNotificationCustomer($booking, $event);
        return true;
    }

    /**
     * Send booking notification to admin panel
     * ارسال نوتیفیکیشن رزرو به پنل ادمین
     *
     * @param BeautyBooking $booking
     * @param string $event
     * @return bool
     */
    public static function sendBookingNotificationAdminPanel(BeautyBooking $booking, string $event): bool
    {
        $data = self::makeNotifyData(
            title: translate('Booking_Notification'),
            description: translate('messages.You have a new beauty booking'),
            booking: $booking,
            orderType: 'beauty_booking',
            type: $event
        );
        Helpers::send_push_notif_to_topic($data, 'admin_message', 'order_request', url('/') . '/backoffice/beautybooking/booking/list');
        return true;
    }

    /**
     * Send booking notification to salon panel
     * ارسال نوتیفیکیشن رزرو به پنل سالن
     *
     * @param BeautyBooking $booking
     * @param string $event
     * @return bool
     */
    public static function sendBookingNotificationSalonPanel(BeautyBooking $booking, string $event): bool
    {
        $salon = $booking->salon;
        if (!$salon) {
            return false;
        }
        
        $store = $salon->store;
        if (!$store) {
            return false;
        }

        $data = self::makeNotifyData(
            title: translate('Booking_Notification'),
            description: translate('messages.You have a new beauty booking'),
            booking: $booking,
            orderType: 'beauty_booking',
            type: $event
        );
        
        $web_push_link = url('/') . '/vendor-panel/beautybooking/booking/list';
        Helpers::send_push_notif_to_topic($data, "store_panel_{$store->id}_message", 'new_order', $web_push_link);
        
        if ($store->vendor?->firebase_token) {
            Helpers::send_push_notif_to_device($store->vendor->firebase_token, $data);
            UserNotification::create([
                'data' => json_encode($data),
                'vendor_id' => $store->vendor_id,
                'order_type' => 'beauty_booking',
            ]);
        }
        
        return true;
    }

    /**
     * Send booking notification to salon app
     * ارسال نوتیفیکیشن رزرو به اپلیکیشن سالن
     *
     * @param BeautyBooking $booking
     * @param string $event
     * @return bool
     */
    public static function sendBookingNotificationSalonApp(BeautyBooking $booking, string $event): bool
    {
        $salon = $booking->salon;
        if (!$salon) {
            return false;
        }
        
        $store = $salon->store;
        if (!$store || !$store->vendor?->firebase_token) {
            return false;
        }

        $data = self::makeNotifyData(
            title: translate('Booking_Notification'),
            description: translate('messages.You have a new beauty booking'),
            booking: $booking,
            orderType: 'beauty_booking',
            type: $event
        );

        Helpers::send_push_notif_to_device($store->vendor->firebase_token, $data);
        UserNotification::create([
            'data' => json_encode($data),
            'vendor_id' => $store->vendor_id,
            'order_type' => 'beauty_booking',
        ]);
        
        return true;
    }

    /**
     * Send booking notification to customer
     * ارسال نوتیفیکیشن رزرو به مشتری
     *
     * @param BeautyBooking $booking
     * @param string $event
     * @return bool
     */
    public static function sendBookingNotificationCustomer(BeautyBooking $booking, string $event): bool
    {
        $customer = $booking->user;
        if (!$customer) {
            return false;
        }
        
        $salon = $booking->salon;
        $storeName = $salon && $salon->store ? $salon->store->name : '';
        
        // Safely get language key with null safety
        // دریافت ایمن کلید زبان با null safety
        $languageKey = 'en'; // Default language
        if (property_exists($customer, 'current_language_key') || isset($customer->current_language_key)) {
            $languageKey = $customer->current_language_key ?? 'en';
        } elseif (method_exists($customer, 'getAttribute') && $customer->getAttribute('current_language_key')) {
            $languageKey = $customer->getAttribute('current_language_key');
        }
        
        $value = self::getBookingStatusMessage($booking->status, 'beautybooking', $languageKey);
        if ($value) {
            $value = Helpers::text_variable_data_format(
                value: $value,
                store_name: $storeName,
                order_id: $booking->id,
                user_name: "{$customer->f_name} {$customer->l_name}"
            );
        }
        
        $userFcm = $customer->cm_firebase_token ?? null;

        if ($value && $userFcm) {
            $data = self::makeNotifyData(
                title: translate('Booking_Notification'),
                description: $value,
                booking: $booking,
                orderType: 'beauty_booking',
                type: $event
            );
            Helpers::send_push_notif_to_device($userFcm, $data);
            UserNotification::create([
                'data' => json_encode($data),
                'user_id' => $booking->user_id,
                'order_type' => 'beauty_booking',
            ]);
        }
        
        return true;
    }

    /**
     * Make notification data array
     * ساخت آرایه داده نوتیفیکیشن
     *
     * @param string $title
     * @param string $description
     * @param BeautyBooking $booking
     * @param string $orderType
     * @param string $type
     * @return array
     */
    public static function makeNotifyData(string $title, string $description, BeautyBooking $booking, string $orderType, string $type): array
    {
        // Get module_id from salon's store if available
        // دریافت module_id از فروشگاه سالن در صورت موجود بودن
        $moduleId = null;
        if ($booking->salon && $booking->salon->store) {
            $moduleId = $booking->salon->store->module_id;
        }
        
        return [
            'title' => $title,
            'description' => $description,
            'order_id' => $booking->id,
            'module_id' => $moduleId,
            'order_type' => $orderType,
            'status' => $booking->status,
            'image' => '',
            'type' => $type,
            'zone_id' => $booking->zone_id,
        ];
    }
}

