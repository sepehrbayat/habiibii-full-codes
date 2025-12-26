<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Http\Requests\BeautyVendorRegisterRequest;
use Modules\BeautyBooking\Http\Requests\BeautyVendorUploadDocumentsRequest;
use Modules\BeautyBooking\Http\Requests\BeautyVendorUpdateWorkingHoursRequest;
use Modules\BeautyBooking\Http\Requests\BeautyVendorManageHolidaysRequest;
use Modules\BeautyBooking\Http\Requests\BeautyVendorProfileUpdateRequest;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\BeautyBooking\Emails\SalonRegistration;

/**
 * Beauty Salon Controller (Vendor Web)
 * کنترلر سالن زیبایی (وب فروشنده)
 */
class BeautySalonController extends Controller
{
    /**
     * Get authenticated vendor and store data
     * دریافت داده‌های فروشنده و فروشگاه احراز هویت شده
     *
     * @return array ['vendor' => object, 'store' => object]
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function getVendorAndStore(): array
    {
        $vendor = \App\CentralLogics\Helpers::get_vendor_data();
        
        if (!$vendor || $vendor === 0 || !is_object($vendor)) {
            abort(403, translate('messages.unauthorized_access'));
        }
        
        $store = \App\CentralLogics\Helpers::get_store_data();
        
        if (!$store) {
            abort(404, translate('messages.store_not_found'));
        }
        
        return ['vendor' => $vendor, 'store' => $store];
    }
    
    /**
     * Show salon registration form
     * نمایش فرم ثبت‌نام سالن
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function registerForm(Request $request)
    {
        ['vendor' => $vendor, 'store' => $store] = $this->getVendorAndStore();
        
        // Check if salon already exists
        // بررسی وجود سالن
        $existingSalon = BeautySalon::where('store_id', $store->id)->first();
        if ($existingSalon) {
            Toastr::info(translate('messages.salon_already_registered'));
            return redirect()->route('vendor.beautybooking.salon.profile');
        }
        
        return view('beautybooking::vendor.salon.register', compact('vendor'));
    }

    /**
     * Store salon registration
     * ذخیره ثبت‌نام سالن
     *
     * @param BeautyVendorRegisterRequest $request
     * @return RedirectResponse
     */
    public function register(BeautyVendorRegisterRequest $request): RedirectResponse
    {
        ['vendor' => $vendor, 'store' => $store] = $this->getVendorAndStore();
        
        // Check if salon already exists
        // بررسی وجود سالن
        $existingSalon = BeautySalon::where('store_id', $store->id)->first();
        if ($existingSalon) {
            Toastr::error(translate('messages.salon_already_registered'));
            return redirect()->route('vendor.beautybooking.salon.profile');
        }

        try {
            DB::beginTransaction();

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
                'store_id' => $store->id,
                'zone_id' => $store->zone_id ?? null,
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
                Mail::to($vendor->email)->send(
                    new SalonRegistration($store->name ?? '')
                );
            }

            DB::commit();
            
            Toastr::success(translate('messages.salon_registered_successfully'));
            Toastr::info(translate('messages.salon_pending_verification'));
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Salon registration failed: ' . $e->getMessage(), [
                'vendor_id' => $vendor->id,
                'error' => $e->getTraceAsString(),
            ]);
            Toastr::error(translate('messages.failed_to_register_salon'));
        }

        return redirect()->route('vendor.beautybooking.salon.profile');
    }

    /**
     * Show salon profile page
     * نمایش صفحه پروفایل سالن
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function profile(Request $request)
    {
        ['vendor' => $vendor, 'store' => $store] = $this->getVendorAndStore();
        
        $salon = BeautySalon::where('store_id', $store->id)
            ->with(['store', 'badges', 'subscriptions'])
            ->first();
        
        // If salon doesn't exist, redirect to registration
        // اگر سالن وجود نداشت، به صفحه ثبت‌نام هدایت شود
        if (!$salon) {
            return redirect()->route('vendor.beautybooking.salon.register');
        }
        
        return view('beautybooking::vendor.salon.profile', compact('salon', 'vendor'));
    }

    /**
     * Update salon profile
     * به‌روزرسانی پروفایل سالن
     *
     * @param BeautyVendorProfileUpdateRequest $request
     * @return RedirectResponse
     */
    public function updateProfile(BeautyVendorProfileUpdateRequest $request): RedirectResponse
    {
        ['vendor' => $vendor, 'store' => $store] = $this->getVendorAndStore();
        
        $salon = BeautySalon::where('store_id', $store->id)->firstOrFail();

        try {
            $salon->update([
                'license_number' => $request->license_number ?? $salon->license_number,
                'license_expiry' => $request->license_expiry ?? $salon->license_expiry,
                'business_type' => $request->business_type ?? $salon->business_type,
            ]);

            Toastr::success(translate('messages.profile_updated_successfully'));
        } catch (Exception $e) {
            \Log::error('Profile update failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_update_profile'));
        }

        return back();
    }

    /**
     * Upload documents
     * آپلود مدارک
     *
     * @param BeautyVendorUploadDocumentsRequest $request
     * @return RedirectResponse
     */
    public function uploadDocuments(BeautyVendorUploadDocumentsRequest $request): RedirectResponse
    {
        ['vendor' => $vendor, 'store' => $store] = $this->getVendorAndStore();
        
        $salon = BeautySalon::where('store_id', $store->id)->firstOrFail();

        try {
            $uploadedDocuments = [];
            foreach ($request->file('documents') as $document) {
                $extension = strtolower($document->getClientOriginalExtension());
                $allowedExtensions = ['pdf', 'jpeg', 'jpg', 'png'];
                if (!in_array($extension, $allowedExtensions)) {
                    $extension = 'png';
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

            Toastr::success(translate('messages.documents_uploaded_successfully'));
        } catch (Exception $e) {
            \Log::error('Document upload failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_upload_documents'));
        }

        return back();
    }

    /**
     * Delete document
     * حذف مدرک
     *
     * @param Request $request
     * @param int $index
     * @return RedirectResponse
     */
    public function deleteDocument(Request $request, int $index): RedirectResponse
    {
        ['vendor' => $vendor, 'store' => $store] = $this->getVendorAndStore();
        
        $salon = BeautySalon::where('store_id', $store->id)->firstOrFail();

        try {
            $documents = $salon->documents ?? [];
            if (isset($documents[$index])) {
                $documentPath = $documents[$index];
                Helpers::delete($documentPath);
                unset($documents[$index]);
                $salon->update([
                    'documents' => array_values($documents),
                ]);
                Toastr::success(translate('messages.document_deleted_successfully'));
            } else {
                Toastr::error(translate('messages.document_not_found'));
            }
        } catch (Exception $e) {
            \Log::error('Document deletion failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_delete_document'));
        }

        return back();
    }

    /**
     * Update working hours
     * به‌روزرسانی ساعات کاری
     *
     * @param BeautyVendorUpdateWorkingHoursRequest $request
     * @return RedirectResponse
     */
    public function updateWorkingHours(BeautyVendorUpdateWorkingHoursRequest $request): RedirectResponse
    {
        ['vendor' => $vendor, 'store' => $store] = $this->getVendorAndStore();
        
        $salon = BeautySalon::where('store_id', $store->id)->firstOrFail();

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

            Toastr::success(translate('messages.working_hours_updated_successfully'));
        } catch (Exception $e) {
            \Log::error('Working hours update failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_update_working_hours'));
        }

        return back();
    }

    /**
     * Manage holidays
     * مدیریت تعطیلات
     *
     * @param BeautyVendorManageHolidaysRequest $request
     * @return RedirectResponse
     */
    public function manageHolidays(BeautyVendorManageHolidaysRequest $request): RedirectResponse
    {
        ['vendor' => $vendor, 'store' => $store] = $this->getVendorAndStore();
        
        $salon = BeautySalon::where('store_id', $store->id)->firstOrFail();

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

            Toastr::success(translate('messages.holidays_updated_successfully'));
        } catch (Exception $e) {
            \Log::error('Holidays update failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_update_holidays'));
        }

        return back();
    }

    /**
     * Show settings page
     * نمایش صفحه تنظیمات
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function settings(Request $request)
    {
        ['vendor' => $vendor, 'store' => $store] = $this->getVendorAndStore();
        
        $salon = BeautySalon::where('store_id', $store->id)
            ->with(['store', 'store.module'])
            ->firstOrFail();
        
        $zones = \App\Models\Zone::orderBy('name')->get();
        
        return view('beautybooking::vendor.settings.settings', compact('salon', 'zones'));
    }

    /**
     * Update settings
     * به‌روزرسانی تنظیمات
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        ['vendor' => $vendor, 'store' => $store] = $this->getVendorAndStore();
        
        $salon = BeautySalon::where('store_id', $store->id)->firstOrFail();
        $store = $salon->store;

        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'schedule_booking' => 'nullable|boolean',
                'gst_status' => 'nullable|boolean',
                'gst' => 'nullable|string|max:50',
                'minimum_service_time' => 'nullable|integer|min:1',
                'maximum_service_time' => 'nullable|integer|min:1|gte:minimum_service_time',
                'service_time_type' => 'nullable|in:min,hours',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // Update store settings
            // به‌روزرسانی تنظیمات فروشگاه
            $deliveryTime = ($request->minimum_service_time ?? 20) . '-' . ($request->maximum_service_time ?? 30) . ' ' . ($request->service_time_type ?? 'min');
            
            $store->update([
                'schedule_order' => $request->has('schedule_booking') ? 1 : 0,
                'gst_status' => $request->has('gst_status') ? 1 : 0,
                'gst_code' => $request->gst ?? $store->gst_code,
                'delivery_time' => $deliveryTime,
            ]);

            Toastr::success(translate('messages.settings_updated_successfully'));
        } catch (Exception $e) {
            \Log::error('Settings update failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_update_settings'));
        }

        return back();
    }
}

