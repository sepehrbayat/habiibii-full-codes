<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\CentralLogics\Helpers;

/**
 * Beauty Vendor Controller (Vendor API)
 * کنترلر فروشنده (API فروشنده)
 */
class BeautyVendorController extends Controller
{
    use BeautyApiResponse;
    /**
     * Get vendor profile
     * دریافت پروفایل فروشنده
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)
            ->with(['store', 'badges', 'subscriptions'])
            ->firstOrFail();

        return $this->successResponse('messages.data_retrieved_successfully', [
            'salon' => $salon,
            'store' => $salon->store,
            'badges' => $salon->badges()->active()->get(),
            'active_subscriptions' => $salon->subscriptions()
                ->where('status', 'active')
                ->where('end_date', '>=', now()->toDateString())
                ->get(),
        ]);
    }

    /**
     * Register salon (onboarding)
     * ثبت‌نام سالن (فرآیند ورود)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(\Modules\BeautyBooking\Http\Requests\BeautyVendorRegisterRequest $request): JsonResponse
    {

        $vendor = $request->vendor;
        
        // Check if salon already exists
        // بررسی وجود سالن
        $existingSalon = BeautySalon::where('store_id', $vendor->store_id)->first();
        if ($existingSalon) {
            return $this->errorResponse([
                ['code' => 'salon', 'message' => translate('salon_already_registered')]
            ]);
        }

        try {
            // Format working hours
            // فرمت‌دهی ساعات کاری
            $workingHours = [];
            foreach ($request->working_hours as $day) {
                $workingHours[$day['day']] = [
                    'open' => $day['open'],
                    'close' => $day['close'],
                ];
            }

            $salon = BeautySalon::create([
                'store_id' => $vendor->store_id,
                'zone_id' => $vendor->store->zone_id ?? null,
                'business_type' => $request->business_type,
                'license_number' => $request->license_number,
                'license_expiry' => $request->license_expiry,
                'working_hours' => $workingHours,
                'verification_status' => 0, // pending
                'is_verified' => false,
            ]);

            // Send registration email
            // ارسال ایمیل ثبت‌نام
            if ($vendor->email) {
                \Mail::to($vendor->email)->send(
                    new \Modules\BeautyBooking\Emails\SalonRegistration($vendor->store->name ?? '')
                );
            }

            return $this->successResponse('salon_registered_successfully', $salon->load(['store', 'badges']), 201);
        } catch (\Exception $e) {
            \Log::error('Salon registration failed: ' . $e->getMessage(), [
                'vendor_id' => $vendor->id,
                'error' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse([
                ['code' => 'registration', 'message' => translate('failed_to_register_salon')]
            ], 500);
        }
    }

    /**
     * Upload documents
     * آپلود مدارک
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadDocuments(\Modules\BeautyBooking\Http\Requests\BeautyVendorUploadDocumentsRequest $request): JsonResponse
    {

        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        try {
            $uploadedDocuments = [];
            foreach ($request->file('documents') as $document) {
                // Get actual file extension from the uploaded file
                // دریافت پسوند واقعی از فایل آپلود شده
                $extension = strtolower($document->getClientOriginalExtension());
                // Validate extension matches allowed types
                // اعتبارسنجی پسوند با انواع مجاز
                $allowedExtensions = ['pdf', 'jpeg', 'jpg', 'png'];
                if (!in_array($extension, $allowedExtensions)) {
                    $extension = 'png'; // Fallback to png if extension is invalid
                }
                $path = Helpers::upload('beauty/salons/documents/', $extension, $document);
                $uploadedDocuments[] = $path;
            }

            // Merge with existing documents
            // ادغام با مدارک موجود
            $existingDocuments = $salon->documents ?? [];
            $allDocuments = array_merge($existingDocuments, $uploadedDocuments);

            $salon->update([
                'documents' => $allDocuments,
            ]);

            return $this->successResponse('documents_uploaded_successfully', [
                'documents' => $allDocuments,
                'uploaded_count' => count($uploadedDocuments),
            ]);
        } catch (\Exception $e) {
            \Log::error('Document upload failed: ' . $e->getMessage());
            return $this->errorResponse([
                ['code' => 'upload', 'message' => translate('failed_to_upload_documents')]
            ], 500);
        }
    }

    /**
     * Update working hours
     * به‌روزرسانی ساعات کاری
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateWorkingHours(\Modules\BeautyBooking\Http\Requests\BeautyVendorUpdateWorkingHoursRequest $request): JsonResponse
    {

        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        try {
            // Format working hours
            // فرمت‌دهی ساعات کاری
            $workingHours = [];
            foreach ($request->working_hours as $day) {
                $workingHours[$day['day']] = [
                    'open' => $day['open'],
                    'close' => $day['close'],
                ];
            }

            $salon->update([
                'working_hours' => $workingHours,
            ]);

            return $this->successResponse('working_hours_updated_successfully', $salon->fresh(['store']));
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'working_hours', 'message' => translate('failed_to_update_working_hours')]
            ], 500);
        }
    }

    /**
     * Manage holidays
     * مدیریت تعطیلات
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function manageHolidays(\Modules\BeautyBooking\Http\Requests\BeautyVendorManageHolidaysRequest $request): JsonResponse
    {

        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        try {
            $existingHolidays = $salon->holidays ?? [];
            $newHolidays = array_unique($request->holidays);

            switch ($request->action) {
                case 'add':
                    $allHolidays = array_unique(array_merge($existingHolidays, $newHolidays));
                    break;
                case 'remove':
                    $allHolidays = array_values(array_diff($existingHolidays, $newHolidays));
                    break;
                case 'replace':
                    $allHolidays = $newHolidays;
                    break;
                default:
                    $allHolidays = $existingHolidays;
            }

            $salon->update([
                'holidays' => array_values($allHolidays),
            ]);

            return $this->successResponse('holidays_updated_successfully', [
                'holidays' => $salon->holidays,
                'total_count' => count($salon->holidays),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'holidays', 'message' => translate('failed_to_update_holidays')]
            ], 500);
        }
    }

    /**
     * Update vendor profile
     * به‌روزرسانی پروفایل فروشنده
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profileUpdate(\Modules\BeautyBooking\Http\Requests\BeautyVendorProfileUpdateRequest $request): JsonResponse
    {

        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        try {
            $salon->update([
                'license_number' => $request->license_number ?? $salon->license_number,
                'license_expiry' => $request->license_expiry ?? $salon->license_expiry,
                'business_type' => $request->business_type ?? $salon->business_type,
                'working_hours' => $request->working_hours ?? $salon->working_hours,
                'holidays' => $request->holidays ?? $salon->holidays,
            ]);

            return $this->successResponse('profile_updated_successfully', $salon->fresh());
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'profile', 'message' => translate('failed_to_update_profile')]
            ], 403);
        }
    }
}

