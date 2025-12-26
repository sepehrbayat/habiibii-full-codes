<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use App\CentralLogics\Helpers;
use App\Traits\FileManagerTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyStaff;
use Modules\BeautyBooking\Exports\BeautyStaffExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Staff Controller (Admin)
 * کنترلر کارمندان (ادمین)
 *
 * Handles staff management in admin panel
 * مدیریت کارمندان در پنل ادمین
 */
class BeautyStaffController extends Controller
{
    use FileManagerTrait;
    
    private BeautyStaff $staff;
    private BeautySalon $salon;
    private Helpers $helpers;

    public function __construct(BeautyStaff $staff, BeautySalon $salon, Helpers $helpers)
    {
        $this->staff = $staff;
        $this->salon = $salon;
        $this->helpers = $helpers;
    }

    /**
     * Display a listing of all staff across all salons
     * نمایش لیست تمام کارمندان در تمام سالن‌ها
     *
     * @param Request $request
     * @return Renderable
     */
    public function list(Request $request): Renderable
    {
        $staffList = $this->staff->with(['salon.store'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%')
                        ->orWhere('email', 'LIKE', '%' . $key . '%')
                        ->orWhere('phone', 'LIKE', '%' . $key . '%')
                        ->orWhereHas('salon.store', function($q) use ($key) {
                            $q->where('name', 'LIKE', '%' . $key . '%');
                        });
                }
            })
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->input('salon_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $salons = $this->salon->with('store')->latest()->get();
        
        $totalStaff = $this->staff->count();
        $activeStaff = $this->staff->where('status', 1)->count();
        $inactiveStaff = $this->staff->where('status', 0)->count();

        return view('beautybooking::admin.staff.list', compact('staffList', 'salons', 'totalStaff', 'activeStaff', 'inactiveStaff'));
    }

    /**
     * Show the form for creating a new staff member
     * نمایش فرم ایجاد کارمند جدید
     *
     * @param int $salonId
     * @return Renderable
     */
    public function create(int $salonId): Renderable
    {
        $salon = $this->salon->with('store')->findOrFail($salonId);
        return view('beautybooking::admin.staff.create', compact('salon'));
    }

    /**
     * Store a newly created staff member
     * ذخیره کارمند جدید
     *
     * @param Request $request
     * @param int $salonId
     * @return RedirectResponse
     */
    public function store(Request $request, int $salonId): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:beauty_staff,email',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'boolean',
            'specializations' => 'nullable|array',
            'working_hours' => 'nullable|array',
            'breaks' => 'nullable|array',
            'days_off' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $salon = $this->salon->findOrFail($salonId);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $this->upload('beauty/staff/', 'png', $request->file('avatar'));
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
        return redirect()->route('admin.beautybooking.staff.list');
    }

    /**
     * Show the form for editing the specified staff member
     * نمایش فرم ویرایش کارمند
     *
     * @param int $id
     * @return Renderable
     */
    public function edit(int $id): Renderable
    {
        $staff = $this->staff->with(['salon.store'])->findOrFail($id);
        return view('beautybooking::admin.staff.edit', compact('staff'));
    }

    /**
     * Update the specified staff member
     * به‌روزرسانی کارمند
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:beauty_staff,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'boolean',
            'specializations' => 'nullable|array',
            'working_hours' => 'nullable|array',
            'breaks' => 'nullable|array',
            'days_off' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $staff = $this->staff->findOrFail($id);

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
            $data['avatar'] = $this->updateAndUpload('beauty/staff/', $staff->avatar, 'png', $request->file('avatar'));
        }

        $staff->update($data);

        Toastr::success(translate('messages.staff_updated_successfully'));
        return back();
    }

    /**
     * Show the specified staff member details
     * نمایش جزئیات کارمند
     *
     * @param int $id
     * @return Renderable
     */
    public function details(int $id): Renderable
    {
        $staff = $this->staff->with(['salon.store', 'services', 'bookings.user', 'reviews'])->findOrFail($id);
        
        $bookings = BeautyBooking::where('staff_id', $staff->id)
            ->with(['user', 'service', 'salon.store'])
            ->latest()
            ->paginate(config('default_pagination'));

        $totalBookings = $staff->bookings()->count();
        $completedBookings = $staff->bookings()->where('status', 'completed')->count();
        $cancelledBookings = $staff->bookings()->where('status', 'cancelled')->count();

        return view('beautybooking::admin.staff.details', compact('staff', 'bookings', 'totalBookings', 'completedBookings', 'cancelledBookings'));
    }

    /**
     * Toggle staff status
     * تغییر وضعیت کارمند
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function status(Request $request, int $id): RedirectResponse
    {
        $staff = $this->staff->find($id);

        if (!$staff) {
            Toastr::error(translate('messages.staff_not_found'));
            return back();
        }

        $staff->update(['status' => !$staff->status]);

        Toastr::success(translate('messages.status_updated_successfully'));
        return back();
    }

    /**
     * Remove the specified staff member
     * حذف کارمند
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $staff = $this->staff->find($id);

        if (!$staff) {
            Toastr::error(translate('messages.staff_not_found'));
            return back();
        }

        // Check if staff has bookings
        if ($staff->bookings()->count() > 0) {
            Toastr::error(translate('messages.cannot_delete_staff_with_bookings'));
            return back();
        }

        if ($staff->avatar) {
            $this->helpers->check_and_delete('beauty/staff/', $staff->avatar);
        }

        $staff->delete();

        Toastr::success(translate('messages.staff_deleted_successfully'));
        
        // Return to salon details staff tab if coming from there
        if ($request->salon_id) {
            return redirect()->route('admin.beautybooking.salon.view', ['id' => $request->salon_id, 'tab' => 'staff']);
        }
        
        return redirect()->route('admin.beautybooking.staff.list');
    }

    /**
     * Export staff data
     * خروجی گرفتن از اطلاعات کارمندان
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $staffList = $this->staff->with(['salon.store'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%')
                        ->orWhere('email', 'LIKE', '%' . $key . '%')
                        ->orWhere('phone', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->input('salon_id'));
            })
            ->latest()
            ->get();

        $data = [
            'fileName' => 'Staff',
            'data' => $staffList,
            'search' => $request->search ?? null,
        ];

        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautyStaffExport($data), 'Staff.csv');
        }
        return Excel::download(new BeautyStaffExport($data), 'Staff.xlsx');
    }
}

