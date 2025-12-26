<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyRetailProduct;
use Modules\BeautyBooking\Entities\BeautyRetailOrder;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\CentralLogics\Helpers;

/**
 * Beauty Retail Controller (Vendor API)
 * کنترلر خرده‌فروشی (API فروشنده)
 */
class BeautyRetailController extends Controller
{
    use BeautyApiResponse;

    public function __construct(
        private BeautyRetailProduct $product,
        private BeautyRetailOrder $order
    ) {}

    /**
     * List products
     * لیست محصولات
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listProducts(Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $products = $this->product->where('salon_id', $salon->id)
            ->latest()
            ->paginate($request->get('limit', 25));

        return $this->listResponse($products);
    }

    /**
     * Store new product
     * ذخیره محصول جدید
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeProduct(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'category' => 'nullable|string|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'status' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = Helpers::upload('beauty/retail/', 'png', $request->file('image'));
            }

            $product = $this->product->create([
                'salon_id' => $salon->id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'image' => $imagePath,
                'category' => $request->category,
                'stock_quantity' => $request->stock_quantity,
                'min_stock_level' => $request->min_stock_level ?? 0,
                'status' => $request->status ?? 1,
            ]);

            return $this->successResponse('messages.product_created_successfully', $product, 201);
        } catch (\Exception $e) {
            return $this->errorResponse([
                ['code' => 'product', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * List orders
     * لیست سفارشات
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listOrders(Request $request): JsonResponse
    {
        $vendor = $request->vendor;
        $salon = BeautySalon::where('store_id', $vendor->store_id)->firstOrFail();

        $orders = $this->order->where('salon_id', $salon->id)
            ->with('user')
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate($request->get('limit', 25));

        return $this->listResponse($orders);
    }
}

