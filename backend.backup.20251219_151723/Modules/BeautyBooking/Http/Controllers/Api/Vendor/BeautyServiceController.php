<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\CentralLogics\Helpers;

/**
 * Beauty Service Controller (Vendor API)
 * کنترلر خدمات (API فروشنده)
 */
class BeautyServiceController extends Controller
{
    use BeautyApiResponse;

    public function __construct(
        private BeautyService $service
    ) {}

    /**
     * List services
     * لیست خدمات
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

        $services = $this->service->where('salon_id', $salon->id)
            ->with('category')
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        return $this->listResponse($services);
    }

    /**
     * Store new service
     * ذخیره خدمت جدید
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(\Modules\BeautyBooking\Http\Requests\BeautyServiceApiStoreRequest $request): JsonResponse
    {

        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

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
            if ($request->has('staff_ids')) {
                $service->staff()->sync($request->staff_ids);
            }

            return $this->successResponse('service_added_successfully', $service->load('category', 'staff'), 201);
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'service', 'message' => translate('failed_to_add_service')],
            ]);
        }
    }

    /**
     * Update service
     * به‌روزرسانی خدمت
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(\Modules\BeautyBooking\Http\Requests\BeautyServiceApiUpdateRequest $request, int $id): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $service = $this->service->where('salon_id', $salon->id)->findOrFail($id);

        try {
            // Only update fields that are actually provided in the request
            // فقط فیلدهایی که واقعاً در درخواست ارائه شده‌اند را به‌روزرسانی کنید
            $data = [];
            
            if ($request->filled('category_id')) {
                $data['category_id'] = $request->category_id;
            }
            if ($request->filled('name')) {
                $data['name'] = $request->name;
            }
            if ($request->filled('description')) {
                $data['description'] = $request->description;
            }
            if ($request->filled('duration_minutes')) {
                $data['duration_minutes'] = $request->duration_minutes;
            }
            if ($request->filled('price')) {
                $data['price'] = $request->price;
            }
            if ($request->has('status')) {
                $data['status'] = $request->status;
            }
            // For array fields, only update if explicitly provided (not null)
            // برای فیلدهای آرایه، فقط در صورت ارائه صریح (نه null) به‌روزرسانی کنید
            if ($request->has('staff_ids') && $request->staff_ids !== null) {
                $data['staff_ids'] = $request->staff_ids;
            }

            if ($request->hasFile('image')) {
                $data['image'] = Helpers::upload('beauty/services/', 'png', $request->file('image'));
                Helpers::delete($service->image);
            }

            // Only update if there's data to update
            // فقط در صورت وجود داده برای به‌روزرسانی، به‌روزرسانی کنید
            if (!empty($data)) {
                $service->update($data);
            }

            // Sync staff members only if staff_ids is explicitly provided (not null)
            // همگام‌سازی کارمندان فقط در صورت ارائه صریح staff_ids (نه null)
            if ($request->has('staff_ids') && $request->staff_ids !== null) {
                $service->staff()->sync($request->staff_ids);
            }

            return $this->successResponse('service_updated_successfully', $service->fresh()->load('category', 'staff'));
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'service', 'message' => translate('failed_to_update_service')],
            ]);
        }
    }

    /**
     * Get service details
     * دریافت جزئیات خدمت
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function details(int $id, Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $service = $this->service->where('salon_id', $salon->id)
            ->with(['category', 'staff'])
            ->findOrFail($id);

        return $this->successResponse('messages.data_retrieved_successfully', $service);
    }

    /**
     * Delete service
     * حذف خدمت
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $service = $this->service->where('salon_id', $salon->id)->findOrFail($id);

        // Check if service has bookings
        if ($service->bookings()->count() > 0) {
            return $this->errorResponse([
                ['code' => 'service', 'message' => translate('cannot_delete_service_with_bookings')],
            ]);
        }

        $service->delete();

        return $this->successResponse('service_deleted_successfully');
    }

    /**
     * Toggle service status
     * تغییر وضعیت خدمت
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function status(int $id, Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $service = $this->service->where('salon_id', $salon->id)->findOrFail($id);
        $service->update(['status' => !$service->status]);

        return $this->successResponse('status_updated_successfully', $service->fresh());
    }
}

