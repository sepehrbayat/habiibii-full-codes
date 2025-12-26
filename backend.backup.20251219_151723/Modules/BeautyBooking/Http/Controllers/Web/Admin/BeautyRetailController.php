<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautyRetailProduct;
use Modules\BeautyBooking\Entities\BeautyRetailOrder;
use Modules\BeautyBooking\Entities\BeautySalon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyRetailExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Retail Controller (Admin)
 * کنترلر خرده‌فروشی زیبایی (ادمین)
 */
class BeautyRetailController extends Controller
{
    public function __construct(
        private BeautyRetailProduct $product,
        private BeautyRetailOrder $order
    ) {}

    /**
     * List all retail products
     * لیست تمام محصولات خرده‌فروشی
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        $products = $this->product->with(['salon.store'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->where(function($q) use ($key) {
                        $q->where('name', 'LIKE', '%' . $key . '%')
                          ->orWhere('category', 'LIKE', '%' . $key . '%')
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

        $orders = $this->order->with(['salon.store', 'user'])
            ->when($request->filled('order_status'), function ($query) use ($request) {
                $query->where('status', $request->order_status);
            })
            ->latest()
            ->limit(10)
            ->get();

        $salons = BeautySalon::with('store')->get();

        return view('beautybooking::admin.retail.index', compact('products', 'orders', 'salons'));
    }

    /**
     * Export retail products
     * خروجی گرفتن از محصولات خرده‌فروشی
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $products = $this->product->with(['salon.store'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->where(function($q) use ($key) {
                        $q->where('name', 'LIKE', '%' . $key . '%')
                          ->orWhere('category', 'LIKE', '%' . $key . '%')
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
            'fileName' => 'Retail Products',
            'data' => $products,
            'search' => $request->search ?? null,
        ];

        // Use input() to properly read query parameter type
        // استفاده از input() برای خواندن صحیح پارامتر type از query string
        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautyRetailExport($data), 'Retail_Products.csv');
        }
        return Excel::download(new BeautyRetailExport($data), 'Retail_Products.xlsx');
    }

    /**
     * View retail product details
     * مشاهده جزئیات محصول خرده‌فروشی
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function view(int $id)
    {
        $product = $this->product->with(['salon.store', 'orders.user'])->findOrFail($id);
        
        // Calculate statistics
        // محاسبه آمار
        $totalOrders = $product->orders()->count();
        
        return view('beautybooking::admin.retail.view', compact('product', 'totalOrders'));
    }

    /**
     * Toggle retail product status
     * تغییر وضعیت محصول خرده‌فروشی
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function status(int $id): RedirectResponse
    {
        $product = $this->product->findOrFail($id);
        $product->update(['status' => !$product->status]);
        
        Toastr::success(translate('messages.status_updated_successfully') ?? 'Status updated successfully');
        return back();
    }
}

