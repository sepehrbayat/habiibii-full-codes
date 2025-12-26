<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautySalon;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyPackageExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Package Controller (Admin)
 * کنترلر پکیج زیبایی (ادمین)
 */
class BeautyPackageController extends Controller
{
    public function __construct(
        private BeautyPackage $package
    ) {}

    /**
     * List all packages
     * لیست تمام پکیج‌ها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        $packages = $this->package->with(['salon.store', 'service'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->where(function($q) use ($key) {
                        $q->where('name', 'LIKE', '%' . $key . '%')
                          ->orWhereHas('salon.store', function($storeQuery) use ($key) {
                              $storeQuery->where('name', 'LIKE', '%' . $key . '%');
                          });
                    });
                }
            })
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->salon_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        $salons = BeautySalon::with('store')->get();

        return view('beautybooking::admin.package.index', compact('packages', 'salons'));
    }

    /**
     * View package details
     * مشاهده جزئیات پکیج
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function view(int $id)
    {
        $package = $this->package->with(['salon.store', 'service', 'usages.user'])->findOrFail($id);
        return view('beautybooking::admin.package.view', compact('package'));
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
        $packages = $this->package->with(['salon.store', 'service'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->where(function($q) use ($key) {
                        $q->where('name', 'LIKE', '%' . $key . '%')
                          ->orWhereHas('salon.store', function($storeQuery) use ($key) {
                              $storeQuery->where('name', 'LIKE', '%' . $key . '%');
                          });
                    });
                }
            })
            ->when($request->filled('salon_id'), function ($query) use ($request) {
                $query->where('salon_id', $request->salon_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
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

    /**
     * Toggle package status
     * تغییر وضعیت پکیج
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function status(int $id): RedirectResponse
    {
        $package = $this->package->findOrFail($id);
        $package->update(['status' => !$package->status]);
        
        Toastr::success(translate('messages.status_updated_successfully'));
        return back();
    }
}

