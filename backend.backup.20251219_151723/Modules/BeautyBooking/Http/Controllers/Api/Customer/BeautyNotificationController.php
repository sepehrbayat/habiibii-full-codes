<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\UserNotification;
use Modules\BeautyBooking\Traits\BeautyApiResponse;

/**
 * Beauty Notification Controller (Customer API)
 * کنترلر نوتیفیکیشن (API مشتری)
 */
class BeautyNotificationController extends Controller
{
    use BeautyApiResponse;

    /**
     * Get user notifications
     * دریافت نوتیفیکیشن‌های کاربر
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @queryParam limit integer optional Number of items per page (default: 25). Example: 25
     * @queryParam offset integer optional Offset for pagination (default: 0). Example: 0
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Booking Confirmed",
     *       "body": "Your booking #100001 has been confirmed",
     *       "type": "booking_confirmed",
     *       "is_read": false,
     *       "created_at": "2024-01-20 10:00:00"
     *     }
     *   ],
     *   "total": 10,
     *   "per_page": 25,
     *   "current_page": 1,
     *   "last_page": 1,
     *   "unread_count": 5
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->get('per_page', $request->get('limit', 25));
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $user = $request->user();
        
        // Get beauty booking related notifications
        // دریافت نوتیفیکیشن‌های مربوط به رزرو زیبایی
        $notifications = UserNotification::where('user_id', $user->id)
            ->where('order_type', 'beauty_booking')
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        // Get unread count
        // دریافت تعداد خوانده نشده
        $unreadCount = UserNotification::where('user_id', $user->id)
            ->where('order_type', 'beauty_booking')
            ->whereNull('read_at')
            ->count();

        // Format notifications
        // فرمت نوتیفیکیشن‌ها
        $formatted = $notifications->getCollection()->map(function ($notification) {
            $data = $notification->data ?? [];
            
            return [
                'id' => $notification->id,
                'title' => $data['title'] ?? translate('Booking_Notification'),
                'body' => $data['description'] ?? $data['message'] ?? '',
                'message' => $data['description'] ?? $data['message'] ?? '',
                'type' => $data['type'] ?? 'booking_notification',
                'is_read' => !is_null($notification->read_at),
                'created_at' => $notification->created_at ? $notification->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        $notifications->setCollection($formatted->values());

        $response = $this->listResponse($notifications);
        
        // Add unread_count to response
        // افزودن unread_count به پاسخ
        $responseData = $response->getData(true);
        $responseData['unread_count'] = $unreadCount;
        
        return response()->json($responseData, 200);
    }

    /**
     * Mark notifications as read
     * علامت‌گذاری نوتیفیکیشن‌ها به عنوان خوانده شده
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @bodyParam ids array required Array of notification IDs to mark as read. Example: [1, 2, 3]
     * 
     * @response 200 {
     *   "message": "Notifications marked as read successfully",
     *   "data": {
     *     "marked_count": 3
     *   }
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "validation",
     *       "message": "The ids field is required."
     *     }
     *   ]
     * }
     */
    public function markRead(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:user_notifications,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $user = $request->user();
        $ids = $request->ids;

        // Mark notifications as read (only user's own notifications)
        // علامت‌گذاری نوتیفیکیشن‌ها به عنوان خوانده شده (فقط نوتیفیکیشن‌های خود کاربر)
        $markedCount = UserNotification::where('user_id', $user->id)
            ->where('order_type', 'beauty_booking')
            ->whereIn('id', $ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->successResponse('notifications_marked_as_read_successfully', [
            'marked_count' => $markedCount,
        ]);
    }
}

