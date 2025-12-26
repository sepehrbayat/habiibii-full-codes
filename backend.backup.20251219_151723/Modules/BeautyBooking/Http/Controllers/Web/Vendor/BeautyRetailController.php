<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Vendor;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyRetailProduct;
use Modules\BeautyBooking\Entities\BeautyRetailOrder;
use Modules\BeautyBooking\Http\Requests\BeautyRetailProductStoreRequest;
use App\CentralLogics\Helpers;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyRetailExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Retail Controller (Vendor)
 * کنترلر فروش خرده (فروشنده)
 */
class BeautyRetailController extends Controller
{
    public function __construct(
        private BeautyRetailProduct $product,
        private BeautyRetailOrder $order
    ) {}

    /**
     * Index - List all retail products
     * نمایش لیست تمام محصولات خرده فروشی
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $products = $this->product->where('salon_id', $salon->id)
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::vendor.retail.index', compact('products', 'salon'));
    }

    /**
     * Show create form
     * نمایش فرم ایجاد محصول
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        return view('beautybooking::vendor.retail.create', compact('salon'));
    }

    /**
     * Store new product
     * ذخیره محصول جدید
     *
     * @param BeautyRetailProductStoreRequest $request
     * @return RedirectResponse
     */
    public function store(BeautyRetailProductStoreRequest $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = Helpers::upload('beauty/retail/', 'png', $request->file('image'));
            }

            $this->product->create([
                'salon_id' => $salon->id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'stock_quantity' => $request->stock_quantity,
                'category' => $request->category,
                'image' => $imagePath,
                'status' => $request->status,
            ]);

            Toastr::success(translate('messages.product_created_successfully'));
            return redirect()->route('vendor.beautybooking.retail.index');
        } catch (Exception $e) {
            \Log::error('Product creation failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_create_product'));
        }

        return back();
    }

    /**
     * Show edit form
     * نمایش فرم ویرایش محصول
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $product = $this->product->where('salon_id', $salon->id)->findOrFail($id);

        return view('beautybooking::vendor.retail.edit', compact('product', 'salon'));
    }

    /**
     * Update product
     * به‌روزرسانی محصول
     *
     * @param int $id
     * @param BeautyRetailProductStoreRequest $request
     * @return RedirectResponse
     */
    public function update(int $id, BeautyRetailProductStoreRequest $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $product = $this->product->where('salon_id', $salon->id)->findOrFail($id);

        try {
            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'stock_quantity' => $request->stock_quantity,
                'category' => $request->category,
                'status' => $request->status,
            ];

            if ($request->hasFile('image')) {
                $data['image'] = Helpers::upload('beauty/retail/', 'png', $request->file('image'));
                Helpers::delete($product->image);
            }

            $product->update($data);

            Toastr::success(translate('messages.product_updated_successfully'));
            return redirect()->route('vendor.beautybooking.retail.index');
        } catch (Exception $e) {
            \Log::error('Product update failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_update_product'));
        }

        return back();
    }

    /**
     * Show product details
     * نمایش جزئیات محصول
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function view(int $id, Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $product = $this->product->where('salon_id', $salon->id)
            ->with('orders')
            ->findOrFail($id);

        return view('beautybooking::vendor.retail.view', compact('product', 'salon'));
    }

    /**
     * Orders list
     * لیست سفارشات
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function orders(Request $request)
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $orders = $this->order->where('salon_id', $salon->id)
            ->with(['user', 'items.product'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('order_number', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::vendor.retail.orders', compact('orders', 'salon'));
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
     * Export retail products
     * خروجی گرفتن از محصولات خرده‌فروشی
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $products = $this->product->where('salon_id', $salon->id)
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
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
     * Toggle retail product status
     * تغییر وضعیت محصول خرده‌فروشی
     *
     * @param int $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(int $id, Request $request): RedirectResponse
    {
        $vendor = $request->vendor;
        $salon = $this->getVendorSalon($vendor);

        $product = $this->product->where('salon_id', $salon->id)->findOrFail($id);
        $product->update(['status' => !$product->status]);
        
        Toastr::success(translate('messages.status_updated_successfully'));
        return back();
    }
}

