<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Http\Requests\BeautyPackageStoreRequest;
use Modules\BeautyBooking\Http\Requests\BeautyPackageUpdateRequest;
use App\CentralLogics\Helpers;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyPackageExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Package Controller (Vendor)
 * کنترلر پکیج (فروشنده)
 */
class BeautyPackageController extends Controller
{
    public function __construct(
        private BeautyPackage $package
    ) {}

    /**
     * Index - List all packages
     * نمایش لیست تمام پکیج‌ها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $packages = $this->package->where('salon_id', $salon->id)
            ->with('service')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $services = BeautyService::where('salon_id', $salon->id)->where('status', 1)->get();

        return view('beautybooking::vendor.package.index', compact('packages', 'services', 'salon'));
    }

    /**
     * Show create form
     * نمایش فرم ایجاد پکیج
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $services = BeautyService::where('salon_id', $salon->id)->where('status', 1)->get();

        return view('beautybooking::vendor.package.create', compact('services', 'salon'));
    }

    /**
     * Store new package
     * ذخیره پکیج جدید
     *
     * @param BeautyPackageStoreRequest $request
     * @return RedirectResponse
     */
    public function store(BeautyPackageStoreRequest $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        // Verify service belongs to salon
        // تأیید اینکه سرویس متعلق به سالن است
        $service = BeautyService::where('id', $request->service_id)
            ->where('salon_id', $salon->id)
            ->firstOrFail();

        try {
            $this->package->create([
                'salon_id' => $salon->id,
                'service_id' => $request->service_id,
                'name' => $request->name,
                'sessions_count' => $request->sessions_count,
                'total_price' => $request->total_price,
                'discount_percentage' => $request->discount_percentage,
                'validity_days' => $request->validity_days ?? config('beautybooking.package.default_validity_days', 90),
                'status' => $request->status ?? 1,
            ]);

            Toastr::success(translate('messages.package_created_successfully'));
            return redirect()->route('vendor.beautybooking.package.index');
        } catch (Exception $e) {
            \Log::error('Package creation failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_create_package'));
        }

        return back();
    }

    /**
     * Show edit form
     * نمایش فرم ویرایش پکیج
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $package = $this->package->where('salon_id', $salon->id)
            ->with('service')
            ->findOrFail($id);

        $services = BeautyService::where('salon_id', $salon->id)->where('status', 1)->get();

        return view('beautybooking::vendor.package.edit', compact('package', 'services', 'salon'));
    }

    /**
     * Show package details
     * نمایش جزئیات پکیج
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function view(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $package = $this->package->where('salon_id', $salon->id)
            ->with(['service', 'bookings'])
            ->findOrFail($id);

        return view('beautybooking::vendor.package.view', compact('package', 'salon'));
    }

    /**
     * Update package
     * به‌روزرسانی پکیج
     *
     * @param int $id
     * @param BeautyPackageUpdateRequest $request
     * @return RedirectResponse
     */
    public function update(int $id, BeautyPackageUpdateRequest $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $package = $this->package->where('salon_id', $salon->id)->findOrFail($id);

        try {
            $package->update([
                'name' => $request->name,
                'sessions_count' => $request->sessions_count,
                'total_price' => $request->total_price,
                'discount_percentage' => $request->discount_percentage,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            Toastr::success(translate('messages.package_updated_successfully'));
            return redirect()->route('vendor.beautybooking.package.index');
        } catch (Exception $e) {
            \Log::error('Package update failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_update_package'));
        }

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
        
        if ($salon->store->vendor_id !== $vendor->id) {
            abort(403, translate('messages.unauthorized_access'));
        }
        
        return $salon;
    }

    /**
     * Export packages
     * خروجی گرفتن از پکیج‌ها
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $packages = $this->package->where('salon_id', $salon->id)
            ->with('service')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()
            ->get();

        $data = [
            'fileName' => 'Packages',
            'data' => $packages,
            'search' => $request->search ?? null,
        ];

        // Use input() to properly read query parameter type
        // استفاده از input() برای خواندن صحیح پارامتر type از query string
        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautyPackageExport($data), 'Packages.csv');
        }
        return Excel::download(new BeautyPackageExport($data), 'Packages.xlsx');
    }
}

