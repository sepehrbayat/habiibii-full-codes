<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Http\Requests\BeautyServiceStoreRequest;
use Modules\BeautyBooking\Http\Requests\BeautyServiceUpdateRequest;
use App\CentralLogics\Helpers;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyServiceExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Service Controller (Vendor)
 * کنترلر خدمات (فروشنده)
 */
class BeautyServiceController extends Controller
{
    public function __construct(
        private BeautyService $service
    ) {}

    /**
     * Index - List all services
     * نمایش لیست تمام خدمات
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $services = $this->service->where('salon_id', $salon->id)
            ->with('category')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $categories = BeautyServiceCategory::where('status', 1)->get();
        $staff = $salon->staff()->where('status', 1)->get();

        return view('beautybooking::vendor.service.index', compact('services', 'categories', 'staff', 'salon'));
    }

    /**
     * Show create form
     * نمایش فرم ایجاد خدمت
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $categories = BeautyServiceCategory::where('status', 1)->get();
        $staff = $salon->staff()->where('status', 1)->get();

        return view('beautybooking::vendor.service.create', compact('categories', 'staff', 'salon'));
    }

    /**
     * Store new service
     * ذخیره خدمت جدید
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(BeautyServiceStoreRequest $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = Helpers::upload('beauty/services/', 'png', $request->file('image'));
            }

            $service = $this->service->create([
                'salon_id' => $salon->id,
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'duration_minutes' => $request->duration_minutes,
                'price' => $request->price,
                'image' => $imagePath,
                'status' => $request->status ?? 1,
                'staff_ids' => $request->staff_ids ?? [],
            ]);

            // Sync staff members
            // همگام‌سازی کارمندان
            if ($request->has('staff_ids')) {
                $service->staff()->sync($request->staff_ids);
            }

            Toastr::success(translate('messages.service_added_successfully'));
            return redirect()->route('vendor.beautybooking.service.index');
        } catch (Exception $e) {
            Toastr::error(translate('messages.failed_to_add_service'));
        }

        return back();
    }

    /**
     * Edit service
     * ویرایش خدمت
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $service = $this->service->where('salon_id', $salon->id)
            ->with(['category', 'staff'])
            ->findOrFail($id);
        
        // Authorization check: Ensure service belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه خدمت متعلق به سالن فروشنده است
        $this->authorizeServiceAccess($service, $salon);

        $categories = BeautyServiceCategory::where('status', 1)->get();
        $staff = $salon->staff()->where('status', 1)->get();

        return view('beautybooking::vendor.service.edit', compact('service', 'categories', 'staff', 'salon'));
    }

    /**
     * Update service
     * به‌روزرسانی خدمت
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(BeautyServiceUpdateRequest $request, int $id): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $service = $this->service->where('salon_id', $salon->id)->findOrFail($id);
        
        // Authorization check: Ensure service belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه خدمت متعلق به سالن فروشنده است
        $this->authorizeServiceAccess($service, $salon);

        try {
            $data = [
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'duration_minutes' => $request->duration_minutes,
                'price' => $request->price,
                'status' => $request->status ?? $service->status,
                'staff_ids' => $request->staff_ids ?? [],
            ];

            if ($request->hasFile('image')) {
                $data['image'] = Helpers::upload('beauty/services/', 'png', $request->file('image'));
                Helpers::delete($service->image);
            }

            $service->update($data);

            // Sync staff members
            // همگام‌سازی کارمندان
            if ($request->has('staff_ids')) {
                $service->staff()->sync($request->staff_ids);
            }

            Toastr::success(translate('messages.service_updated_successfully'));
        } catch (Exception $e) {
            Toastr::error(translate('messages.failed_to_update_service'));
        }

        return back();
    }

    /**
     * Delete service
     * حذف خدمت
     *
     * @param int $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroy(int $id, Request $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $service = $this->service->where('salon_id', $salon->id)->findOrFail($id);
        
        // Authorization check: Ensure service belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه خدمت متعلق به سالن فروشنده است
        $this->authorizeServiceAccess($service, $salon);

        // Check if service has bookings
        if ($service->bookings()->count() > 0) {
            Toastr::error(translate('messages.cannot_delete_service_with_bookings'));
            return back();
        }

        $service->delete();
        Toastr::success(translate('messages.service_deleted_successfully'));

        return back();
    }

    /**
     * Toggle service status
     * تغییر وضعیت خدمت
     *
     * @param int $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(int $id, Request $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $service = $this->service->where('salon_id', $salon->id)->findOrFail($id);
        
        // Authorization check: Ensure service belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه خدمت متعلق به سالن فروشنده است
        $this->authorizeServiceAccess($service, $salon);
        
        $service->update(['status' => !$service->status]);

        Toastr::success(translate('messages.status_updated_successfully'));
        return back();
    }

    /**
     * Get vendor's salon with authorization check
     * دریافت سالن فروشنده با بررسی مجوز
     *
     * @param object $vendor
     * @return BeautySalon
     */
    private function getVendorSalon(object $vendor): BeautySalon
    {
        $salon = BeautySalon::where('store_id', $vendor->store_id)->first();
        
        if (!$salon) {
            abort(403, translate('messages.salon_not_found'));
        }
        
        // Authorization check: Ensure salon belongs to vendor
        // بررسی مجوز: اطمینان از اینکه سالن متعلق به فروشنده است
        if ($salon->store->vendor_id !== $vendor->id) {
            abort(403, translate('messages.unauthorized_access'));
        }
        
        return $salon;
    }

    /**
     * Authorize service access
     * مجوز دسترسی به خدمت
     *
     * @param BeautyService $service
     * @param BeautySalon $salon
     * @return void
     */
    private function authorizeServiceAccess(BeautyService $service, BeautySalon $salon): void
    {
        if ($service->salon_id !== $salon->id) {
            abort(403, translate('messages.unauthorized_access'));
        }
    }

    /**
     * Export services
     * خروجی گرفتن از خدمات
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $services = $this->service->where('salon_id', $salon->id)
            ->with('category')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()
            ->get();

        $data = [
            'fileName' => 'Services',
            'data' => $services,
            'search' => $request->search ?? null,
        ];

        // Use input() to properly read query parameter type
        // استفاده از input() برای خواندن صحیح پارامتر type از query string
        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautyServiceExport($data), 'Services.csv');
        }
        return Excel::download(new BeautyServiceExport($data), 'Services.xlsx');
    }
}

