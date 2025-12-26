<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyStaff;
use Modules\BeautyBooking\Http\Requests\BeautyStaffStoreRequest;
use Modules\BeautyBooking\Http\Requests\BeautyStaffUpdateRequest;
use App\CentralLogics\Helpers;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyStaffExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Staff Controller (Vendor)
 * کنترلر کارمندان (فروشنده)
 */
class BeautyStaffController extends Controller
{
    public function __construct(
        private BeautyStaff $staff
    ) {}

    /**
     * Index - List all staff
     * نمایش لیست تمام کارمندان
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $staffList = $this->staff->where('salon_id', $salon->id)
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::vendor.staff.index', compact('staffList', 'salon'));
    }

    /**
     * Show create form
     * نمایش فرم ایجاد کارمند
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        return view('beautybooking::vendor.staff.create', compact('salon'));
    }

    /**
     * Store new staff
     * ذخیره کارمند جدید
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(BeautyStaffStoreRequest $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        try {
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = Helpers::upload('beauty/staff/', 'png', $request->file('avatar'));
            }

            $this->staff->create([
                'salon_id' => $salon->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'avatar' => $avatarPath,
                'status' => $request->status ?? 1,
                'specializations' => $request->specializations ?? [],
                'working_hours' => $request->working_hours ?? [],
                'breaks' => $request->breaks ?? [],
                'days_off' => $request->days_off ?? [],
            ]);

            Toastr::success(translate('messages.staff_added_successfully'));
            return redirect()->route('vendor.beautybooking.staff.index');
        } catch (Exception $e) {
            Toastr::error(translate('messages.failed_to_add_staff'));
        }

        return back();
    }

    /**
     * Edit staff
     * ویرایش کارمند
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $staff = $this->staff->where('salon_id', $salon->id)->findOrFail($id);
        
        // Authorization check: Ensure staff belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه کارمند متعلق به سالن فروشنده است
        $this->authorizeStaffAccess($staff, $salon);

        return view('beautybooking::vendor.staff.edit', compact('staff', 'salon'));
    }

    /**
     * Update staff
     * به‌روزرسانی کارمند
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(BeautyStaffUpdateRequest $request, int $id): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $staff = $this->staff->where('salon_id', $salon->id)->findOrFail($id);
        
        // Authorization check: Ensure staff belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه کارمند متعلق به سالن فروشنده است
        $this->authorizeStaffAccess($staff, $salon);

        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status ?? $staff->status,
                'specializations' => $request->specializations ?? [],
                'working_hours' => $request->working_hours ?? [],
                'breaks' => $request->breaks ?? [],
                'days_off' => $request->days_off ?? [],
            ];

            if ($request->hasFile('avatar')) {
                $data['avatar'] = Helpers::upload('beauty/staff/', 'png', $request->file('avatar'));
                Helpers::delete($staff->avatar);
            }

            $staff->update($data);

            Toastr::success(translate('messages.staff_updated_successfully'));
        } catch (Exception $e) {
            Toastr::error(translate('messages.failed_to_update_staff'));
        }

        return back();
    }

    /**
     * Delete staff
     * حذف کارمند
     *
     * @param int $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroy(int $id, Request $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $staff = $this->staff->where('salon_id', $salon->id)->findOrFail($id);
        
        // Authorization check: Ensure staff belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه کارمند متعلق به سالن فروشنده است
        $this->authorizeStaffAccess($staff, $salon);

        // Check if staff has bookings
        if ($staff->bookings()->count() > 0) {
            Toastr::error(translate('messages.cannot_delete_staff_with_bookings'));
            return back();
        }

        $staff->delete();
        Toastr::success(translate('messages.staff_deleted_successfully'));

        return back();
    }

    /**
     * Toggle staff status
     * تغییر وضعیت کارمند
     *
     * @param int $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(int $id, Request $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $staff = $this->staff->where('salon_id', $salon->id)->findOrFail($id);
        
        // Authorization check: Ensure staff belongs to vendor's salon
        // بررسی مجوز: اطمینان از اینکه کارمند متعلق به سالن فروشنده است
        $this->authorizeStaffAccess($staff, $salon);
        
        $staff->update(['status' => !$staff->status]);

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
     * Authorize staff access
     * مجوز دسترسی به کارمند
     *
     * @param BeautyStaff $staff
     * @param BeautySalon $salon
     * @return void
     */
    private function authorizeStaffAccess(BeautyStaff $staff, BeautySalon $salon): void
    {
        if ($staff->salon_id !== $salon->id) {
            abort(403, translate('messages.unauthorized_access'));
        }
    }

    /**
     * Export staff
     * خروجی گرفتن از کارمندان
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $staffList = $this->staff->where('salon_id', $salon->id)
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()
            ->get();

        $data = [
            'fileName' => 'Staff',
            'data' => $staffList,
            'search' => $request->search ?? null,
        ];

        // Use input() to properly read query parameter type
        // استفاده از input() برای خواندن صحیح پارامتر type از query string
        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautyStaffExport($data), 'Staff.csv');
        }
        return Excel::download(new BeautyStaffExport($data), 'Staff.xlsx');
    }
}

