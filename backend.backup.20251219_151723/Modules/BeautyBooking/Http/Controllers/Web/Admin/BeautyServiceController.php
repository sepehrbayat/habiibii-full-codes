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
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Exports\BeautyServiceExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Service Controller (Admin)
 * کنترلر خدمات (ادمین)
 *
 * Handles service management in admin panel
 * مدیریت خدمات در پنل ادمین
 */
class BeautyServiceController extends Controller
{
    use FileManagerTrait;
    
    private BeautyService $service;
    private BeautySalon $salon;
    private BeautyServiceCategory $category;
    private Helpers $helpers;

    public function __construct(BeautyService $service, BeautySalon $salon, BeautyServiceCategory $category, Helpers $helpers)
    {
        $this->service = $service;
        $this->salon = $salon;
        $this->category = $category;
        $this->helpers = $helpers;
    }

    /**
     * Display a listing of all services across all salons
     * نمایش لیست تمام خدمات در تمام سالن‌ها
     *
     * @param Request $request
     * @return Renderable
     */
    public function list(Request $request): Renderable
    {
        $services = $this->service->with([
                'salon.store',
                'category',
                'activeRelations.relatedService',
            ])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%')
                        ->orWhereHas('salon.store', function($q) use ($key) {
                            $q->where('name', 'LIKE', '%' . $key . '%');
                        });
                }
            })
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->input('salon_id'));
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->input('category_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $salons = $this->salon->with('store')->latest()->get();
        $categories = $this->category->where('status', 1)->get();
        
        $totalServices = $this->service->count();
        $activeServices = $this->service->where('status', 1)->count();
        $inactiveServices = $this->service->where('status', 0)->count();

        return view('beautybooking::admin.service.list', compact('services', 'salons', 'categories', 'totalServices', 'activeServices', 'inactiveServices'));
    }

    /**
     * Show the form for creating a new service
     * نمایش فرم ایجاد خدمت جدید
     *
     * @return Renderable
     */
    public function create(): Renderable
    {
        $salons = $this->salon->with('store')
            ->whereHas('store', function($query) {
                $query->where('status', 1);
            })
            ->latest()
            ->get();
            
        $categories = $this->category->where('status', 1)->latest()->get();

        return view('beautybooking::admin.service.create', compact('salons', 'categories'));
    }

    /**
     * Store a newly created service
     * ذخیره خدمت جدید
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'salon_id' => 'required|integer|exists:beauty_salons,id',
            'category_id' => 'required|integer|exists:beauty_service_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'boolean',
            'staff_ids' => 'nullable|array',
            'staff_ids.*' => 'exists:beauty_staff,id',
            'service_type' => 'nullable|in:service,consultation',
            'consultation_credit_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $salon = $this->salon->findOrFail($request->salon_id);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->upload('beauty/services/', 'png', $request->file('image'));
        }

        $service = $this->service->create([
            'salon_id' => $request->salon_id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'price' => $request->price,
            'image' => $imagePath,
            'status' => $request->status ?? 1,
            'staff_ids' => $request->staff_ids ?? [],
            'service_type' => $request->service_type ?? 'service',
            'consultation_credit_percentage' => $request->consultation_credit_percentage ?? 0,
        ]);

        // Sync staff members if provided
        if ($request->has('staff_ids')) {
            $service->staff()->sync($request->staff_ids);
        }

        Toastr::success(translate('messages.service_added_successfully'));
        return redirect()->route('admin.beautybooking.service.list');
    }

    /**
     * Show the form for editing the specified service
     * نمایش فرم ویرایش خدمت
     *
     * @param int $id
     * @return Renderable
     */
    public function edit(int $id): Renderable
    {
        $service = $this->service->with(['salon.store', 'category', 'staff'])->findOrFail($id);
        
        $salons = $this->salon->with('store')
            ->whereHas('store', function($query) {
                $query->where('status', 1);
            })
            ->latest()
            ->get();
            
        $categories = $this->category->where('status', 1)->latest()->get();
        
        $staffList = $service->salon->staff()->where('status', 1)->get();

        return view('beautybooking::admin.service.edit', compact('service', 'salons', 'categories', 'staffList'));
    }

    /**
     * Update the specified service
     * به‌روزرسانی خدمت
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'salon_id' => 'required|integer|exists:beauty_salons,id',
            'category_id' => 'required|integer|exists:beauty_service_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'boolean',
            'staff_ids' => 'nullable|array',
            'staff_ids.*' => 'exists:beauty_staff,id',
            'service_type' => 'nullable|in:service,consultation',
            'consultation_credit_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $service = $this->service->findOrFail($id);

        $data = [
            'salon_id' => $request->salon_id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'price' => $request->price,
            'status' => $request->status ?? $service->status,
            'staff_ids' => $request->staff_ids ?? [],
            'service_type' => $request->service_type ?? 'service',
            'consultation_credit_percentage' => $request->consultation_credit_percentage ?? 0,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $this->updateAndUpload('beauty/services/', $service->image, 'png', $request->file('image'));
        }

        $service->update($data);

        // Sync staff members if provided
        if ($request->has('staff_ids')) {
            $service->staff()->sync($request->staff_ids);
        } else {
            $service->staff()->detach();
        }

        Toastr::success(translate('messages.service_updated_successfully'));
        return back();
    }

    /**
     * Show the specified service details
     * نمایش جزئیات خدمت
     *
     * @param int $id
     * @return Renderable
     */
    public function details(int $id, Request $request): Renderable
    {
        $service = $this->service->with([
                'salon.store',
                'category',
                'staff',
                'bookings.user',
                'reviews',
                'activeRelations.relatedService',
            ])->findOrFail($id);
        
        // Get paginated bookings for this service
        // دریافت رزروهای صفحه‌بندی شده برای این خدمت
        $bookings = BeautyBooking::where('service_id', $service->id)
            ->with(['user', 'salon.store', 'staff'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->where(function($q) use ($key) {
                        $q->where('id', 'LIKE', '%' . $key . '%')
                          ->orWhereHas('user', function($userQuery) use ($key) {
                              $userQuery->where('f_name', 'LIKE', '%' . $key . '%')
                                       ->orWhere('l_name', 'LIKE', '%' . $key . '%')
                                       ->orWhere('phone', 'LIKE', '%' . $key . '%');
                          });
                    });
                }
            })
            ->latest()
            ->paginate(config('default_pagination'));
        
        $totalBookings = $service->bookings()->count();
        $completedBookings = $service->bookings()->where('status', 'completed')->count();
        $cancelledBookings = $service->bookings()->where('status', 'cancelled')->count();
        
        $totalRevenue = $service->bookings()
            ->where('status', 'completed')
            ->sum('total_amount');
            
        $avgRating = $service->reviews()->avg('rating') ?? 0;

        return view('beautybooking::admin.service.details', compact('service', 'bookings', 'totalBookings', 'completedBookings', 'cancelledBookings', 'totalRevenue', 'avgRating'));
    }

    /**
     * Toggle service status
     * تغییر وضعیت خدمت
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function status(Request $request, int $id): RedirectResponse
    {
        $service = $this->service->find($id);

        if (!$service) {
            Toastr::error(translate('messages.service_not_found'));
            return back();
        }

        $service->update(['status' => !$service->status]);

        Toastr::success(translate('messages.status_updated_successfully'));
        return back();
    }

    /**
     * Remove the specified service
     * حذف خدمت
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $service = $this->service->find($id);

        if (!$service) {
            Toastr::error(translate('messages.service_not_found'));
            return back();
        }

        // Check if service has bookings
        if ($service->bookings()->count() > 0) {
            Toastr::error(translate('messages.cannot_delete_service_with_bookings'));
            return back();
        }

        if ($service->image) {
            $this->helpers->check_and_delete('beauty/services/', $service->image);
        }

        // Detach staff relationships
        $service->staff()->detach();
        
        $service->delete();

        Toastr::success(translate('messages.service_deleted_successfully'));
        
        // Return to salon details services tab if coming from there
        if ($request->salon_id) {
            return redirect()->route('admin.beautybooking.salon.view', ['id' => $request->salon_id, 'tab' => 'services']);
        }
        
        return redirect()->route('admin.beautybooking.service.list');
    }

    /**
     * Export service data
     * خروجی گرفتن از اطلاعات خدمات
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $services = $this->service->with(['salon.store', 'category'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->input('salon_id'));
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->input('category_id'));
            })
            ->latest()
            ->get();

        $data = [
            'fileName' => 'Services',
            'data' => $services,
            'search' => $request->search ?? null,
        ];

        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautyServiceExport($data), 'Services.csv');
        }
        return Excel::download(new BeautyServiceExport($data), 'Services.xlsx');
    }
}

