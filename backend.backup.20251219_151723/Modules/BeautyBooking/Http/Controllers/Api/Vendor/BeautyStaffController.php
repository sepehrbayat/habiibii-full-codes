<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyStaff;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\CentralLogics\Helpers;

/**
 * Beauty Staff Controller (Vendor API)
 * کنترلر کارمندان (API فروشنده)
 */
class BeautyStaffController extends Controller
{
    use BeautyApiResponse;

    public function __construct(
        private BeautyStaff $staff
    ) {}

    /**
     * List staff
     * لیست کارمندان
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 25);
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        // Fixed: paginate() expects page number (1, 2, 3...), not offset
        // اصلاح شده: paginate() شماره صفحه را انتظار دارد (1, 2, 3...)، نه offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $staffList = $this->staff->where('salon_id', $salon->id)
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return $this->listResponse($staffList);
    }

    /**
     * Store new staff
     * ذخیره کارمند جدید
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(\Modules\BeautyBooking\Http\Requests\BeautyStaffApiStoreRequest $request): JsonResponse
    {

        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        try {
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = Helpers::upload('beauty/staff/', 'png', $request->file('avatar'));
            }

            $staff = $this->staff->create([
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

            return $this->successResponse('staff_added_successfully', $staff, 201);
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'staff', 'message' => translate('failed_to_add_staff')],
            ]);
        }
    }

    /**
     * Update staff
     * به‌روزرسانی کارمند
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(\Modules\BeautyBooking\Http\Requests\BeautyStaffApiUpdateRequest $request, int $id): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $staff = $this->staff->where('salon_id', $salon->id)->findOrFail($id);

        try {
            // Only update fields that are actually provided in the request
            // فقط فیلدهایی که واقعاً در درخواست ارائه شده‌اند را به‌روزرسانی کنید
            $data = [];
            
            if ($request->filled('name')) {
                $data['name'] = $request->name;
            }
            if ($request->filled('email')) {
                $data['email'] = $request->email;
            }
            if ($request->filled('phone')) {
                $data['phone'] = $request->phone;
            }
            if ($request->has('status')) {
                $data['status'] = $request->status;
            }
            // For array fields, only update if explicitly provided (not null)
            // برای فیلدهای آرایه، فقط در صورت ارائه صریح (نه null) به‌روزرسانی کنید
            if ($request->has('specializations') && $request->specializations !== null) {
                $data['specializations'] = $request->specializations;
            }
            if ($request->has('working_hours') && $request->working_hours !== null) {
                $data['working_hours'] = $request->working_hours;
            }
            if ($request->has('breaks') && $request->breaks !== null) {
                $data['breaks'] = $request->breaks;
            }
            if ($request->has('days_off') && $request->days_off !== null) {
                $data['days_off'] = $request->days_off;
            }

            if ($request->hasFile('avatar')) {
                $data['avatar'] = Helpers::upload('beauty/staff/', 'png', $request->file('avatar'));
                Helpers::delete($staff->avatar);
            }

            // Only update if there's data to update
            // فقط در صورت وجود داده برای به‌روزرسانی، به‌روزرسانی کنید
            if (!empty($data)) {
                $staff->update($data);
            }

            return $this->successResponse('staff_updated_successfully', $staff->fresh());
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'staff', 'message' => translate('failed_to_update_staff')],
            ]);
        }
    }

    /**
     * Get staff details
     * دریافت جزئیات کارمند
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function details(int $id, Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $staff = $this->staff->where('salon_id', $salon->id)
            ->with('services')
            ->findOrFail($id);

        return $this->successResponse('messages.data_retrieved_successfully', $staff);
    }

    /**
     * Delete staff
     * حذف کارمند
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $staff = $this->staff->where('salon_id', $salon->id)->findOrFail($id);

        // Check if staff has bookings
        if ($staff->bookings()->count() > 0) {
            return $this->errorResponse([
                ['code' => 'staff', 'message' => translate('cannot_delete_staff_with_bookings')],
            ]);
        }

        $staff->delete();

        return $this->successResponse('staff_deleted_successfully');
    }

    /**
     * Toggle staff status
     * تغییر وضعیت کارمند
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function status(int $id, Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $staff = $this->staff->where('salon_id', $salon->id)->findOrFail($id);
        $staff->update(['status' => !$staff->status]);

        return $this->successResponse('status_updated_successfully', $staff->fresh());
    }
}

