<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyReview;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Http\Requests\BeautyReviewStoreRequest;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\CentralLogics\Helpers;

/**
 * Beauty Review Controller (Customer API)
 * کنترلر نظرات (API مشتری)
 */
class BeautyReviewController extends Controller
{
    use BeautyApiResponse;

    public function __construct(
        private BeautyReview $review
    ) {}

    /**
     * Store new review
     * ذخیره نظر جدید
     *
     * @param BeautyReviewStoreRequest $request
     * @return JsonResponse
     * 
     * @bodyParam booking_id integer required Booking ID. Example: 100001
     * @bodyParam rating integer required Rating (1-5). Example: 5
     * @bodyParam comment string optional Review comment (max: 1000). Example: "Great service!"
     * @bodyParam attachments array optional Array of image files (FormData). Example: [file1, file2]
     * 
     * @response 201 {
     *   "message": "Review submitted successfully",
     *   "data": {
     *     "id": 1,
     *     "booking_id": 100001,
     *     "rating": 5,
     *     "comment": "Great service!",
     *     "status": "pending",
     *     "attachments": [
     *       "http://example.com/storage/beauty/reviews/image1.png"
     *     ],
     *     "created_at": "2024-01-20 10:00:00"
     *   }
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "booking",
     *       "message": "Can only review completed bookings"
     *     }
     *   ]
     * }
     */
    public function store(BeautyReviewStoreRequest $request): JsonResponse
    {
        $booking = BeautyBooking::where('user_id', $request->user()->id)
            ->findOrFail($request->booking_id);
        
        // Authorization check: Ensure booking belongs to current user
        // بررسی مجوز: اطمینان از اینکه رزرو متعلق به کاربر فعلی است
        if ($booking->user_id !== $request->user()->id) {
            return $this->errorResponse([
                ['code' => 'unauthorized', 'message' => translate('messages.unauthorized_access')],
            ]);
        }

        // Check if booking is completed
        // بررسی تکمیل بودن رزرو
        if ($booking->status !== 'completed') {
            return $this->errorResponse([
                ['code' => 'booking', 'message' => translate('can_only_review_completed_bookings')],
            ]);
        }

        // Check if already reviewed
        // بررسی وجود نظر قبلی
        if ($booking->review) {
            return $this->errorResponse([
                ['code' => 'review', 'message' => translate('booking_already_reviewed')],
            ]);
        }

        try {
            // Upload attachments (handles FormData from React)
            // آپلود فایل‌های ضمیمه (مدیریت FormData از React)
            $attachments = [];
            if ($request->hasFile('attachments')) {
                // Handle both single file and array of files
                // مدیریت هم فایل تکی و هم آرایه فایل‌ها
                $files = $request->file('attachments');
                if (!is_array($files)) {
                    $files = [$files];
                }
                
                foreach ($files as $file) {
                    // Validate file type and size (already validated in FormRequest, but double-check)
                    // اعتبارسنجی نوع و اندازه فایل (قبلاً در FormRequest اعتبارسنجی شده، اما بررسی مجدد)
                    if (!$file->isValid()) {
                        continue; // Skip invalid files
                    }
                    
                    // Get actual file extension from uploaded file
                    // دریافت پسوند واقعی فایل از فایل آپلود شده
                    $extension = $file->getClientOriginalExtension() ?: 'png';
                    
                    // Upload file and get path
                    // آپلود فایل و دریافت مسیر
                    $uploadedPath = Helpers::upload('beauty/reviews/', $extension, $file);
                    if ($uploadedPath) {
                        $attachments[] = $uploadedPath;
                    }
                }
            }

            // Check if auto-publish is enabled
            // بررسی فعال بودن auto-publish
            $autoPublish = config('beautybooking.review.auto_publish', false);
            $status = $autoPublish ? 'approved' : 'pending';

            $review = $this->review->create([
                'booking_id' => $booking->id,
                'user_id' => $request->user()->id,
                'salon_id' => $booking->salon_id,
                'service_id' => $booking->service_id,
                'staff_id' => $booking->staff_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'attachments' => $attachments,
                'status' => $status,
            ]);

            // If auto-publish, update salon rating statistics immediately
            // در صورت auto-publish، فوراً آمار رتبه‌بندی سالن را به‌روزرسانی کنید
            if ($autoPublish) {
                $salonService = app(\Modules\BeautyBooking\Services\BeautySalonService::class);
                $salonService->updateRatingStatistics($booking->salon_id);
            }

            // Format review response with full attachment URLs
            // فرمت پاسخ نظر با URL کامل فایل‌های ضمیمه
            $reviewData = [
                'id' => $review->id,
                'booking_id' => $review->booking_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'status' => $review->status,
                'attachments' => array_map(function ($path) {
                    return asset('storage/' . $path);
                }, $attachments),
                'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                'salon' => [
                    'id' => $booking->salon->id ?? null,
                    'name' => $booking->salon->store->name ?? '',
                ],
                'service' => [
                    'id' => $booking->service->id ?? null,
                    'name' => $booking->service->name ?? '',
                ],
            ];

            return $this->successResponse('review_submitted_successfully', $reviewData, 201);
        } catch (\Exception $e) {
            \Log::error('Review submission failed', [
                'booking_id' => $request->booking_id ?? null,
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse([
                ['code' => 'review', 'message' => translate('failed_to_submit_review')],
            ], 500);
        }
    }

    /**
     * Index - List user's reviews
     * لیست نظرات کاربر
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @queryParam limit integer optional Number of items per page (default: 25). Example: 25
     * @queryParam offset integer optional Offset for pagination (default: 0). Example: 0
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": [...],
     *   "total": 10,
     *   "per_page": 25,
     *   "current_page": 1,
     *   "last_page": 1
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 25);
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        // Fixed: paginate() expects page number (1, 2, 3...), not offset
        // اصلاح شده: paginate() شماره صفحه را انتظار دارد (1, 2, 3...)، نه offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $reviews = $this->review->where('user_id', $request->user()->id)
            ->with(['salon.store', 'service', 'staff', 'booking'])
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return $this->listResponse($reviews);
    }

    /**
     * Get salon reviews (public)
     * دریافت نظرات سالن (عمومی)
     *
     * @param int $salonId Salon ID
     * @param Request $request
     * @return JsonResponse
     * 
     * @queryParam limit integer optional Number of items per page (default: 25). Example: 25
     * @queryParam offset integer optional Offset for pagination (default: 0). Example: 0
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": [...],
     *   "total": 50,
     *   "per_page": 25,
     *   "current_page": 1,
     *   "last_page": 2
     * }
     */
    public function getSalonReviews(int $salonId, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 25);
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        // Fixed: paginate() expects page number (1, 2, 3...), not offset
        // اصلاح شده: paginate() شماره صفحه را انتظار دارد (1, 2, 3...)، نه offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $reviews = $this->review->where('salon_id', $salonId)
            ->approved()
            ->with(['user', 'service', 'staff'])
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $formatted = $reviews->getCollection()->map(function ($review) {
            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'attachments' => $review->attachments ? array_map(function($path) {
                    return asset('storage/' . $path);
                }, $review->attachments) : [],
                'user' => [
                    'id' => $review->user?->id ?? null,
                    'name' => trim(($review->user?->f_name ?? '') . ' ' . ($review->user?->l_name ?? '')) ?: 'Anonymous',
                    'image' => $review->user?->image ? asset('storage/' . $review->user->image) : null,
                ],
                'service' => [
                    'id' => $review->service?->id ?? null,
                    'name' => $review->service?->name ?? '',
                ],
                'staff' => $review->staff ? [
                    'id' => $review->staff->id,
                    'name' => $review->staff->name,
                ] : null,
                'created_at' => $review->created_at->format('Y-m-d H:i:s'),
            ];
        });

        $reviews->setCollection($formatted->values());

        return $this->listResponse($reviews);
    }
}

