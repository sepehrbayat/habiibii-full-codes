<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Modules\BeautyBooking\Entities\BeautyRetailProduct;
use Modules\BeautyBooking\Entities\BeautyRetailOrder;
use Modules\BeautyBooking\Services\BeautyRetailService;
use Modules\BeautyBooking\Traits\BeautyApiResponse;
use App\CentralLogics\CustomerLogic;

/**
 * Beauty Retail Controller (Customer API)
 * کنترلر خرده‌فروشی (API مشتری)
 */
class BeautyRetailController extends Controller
{
    use BeautyApiResponse;

    public function __construct(
        private BeautyRetailProduct $product,
        private BeautyRetailService $retailService
    ) {}

    /**
     * List products for a salon
     * لیست محصولات یک سالن
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @queryParam salon_id integer required Salon ID. Example: 1
     * @queryParam category string optional Filter by category. Example: skincare
     * @queryParam limit integer optional Number of items per page (default: 25). Example: 25
     * @queryParam offset integer optional Offset for pagination (default: 0). Example: 0
     * 
     * @response 200 {
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Product Name",
     *       "price": 100000,
     *       "stock_quantity": 10
     *     }
     *   ],
     *   "total": 20,
     *   "per_page": 25,
     *   "current_page": 1,
     *   "last_page": 1
     * }
     */
    public function listProducts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'salon_id' => 'required|integer|exists:beauty_salons,id',
            'category' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $limit = $request->get('limit', 25);
        $offset = $request->get('offset', 0);
        
        // Calculate page number from offset
        // محاسبه شماره صفحه از offset
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $query = $this->product->where('salon_id', $request->salon_id)
            ->active()
            ->inStock();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate($limit, ['*'], 'page', $page);

        $formatted = $products->getCollection()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image' => $product->image ? asset('storage/' . $product->image) : null,
                'category' => $product->category,
                'stock_quantity' => $product->stock_quantity,
            ];
        });

        // Format response using standardized listResponse method
        // فرمت پاسخ با استفاده از متد استاندارد listResponse
        $products->setCollection($formatted->values());
        return $this->listResponse($products);
    }

    /**
     * Create retail order
     * ایجاد سفارش خرده‌فروشی
     *
     * @param Request $request
     * @return JsonResponse
     * 
     * @bodyParam salon_id integer required Salon ID. Example: 1
     * @bodyParam products array required Array of products. Example: [{"product_id": 1, "quantity": 2}]
     * @bodyParam products.*.product_id integer required Product ID. Example: 1
     * @bodyParam products.*.quantity integer required Quantity (min: 1). Example: 2
     * @bodyParam payment_method string required Payment method (online/wallet/cash_payment). Example: wallet
     * @bodyParam shipping_address string optional Shipping address (max: 500). Example: "123 Main St"
     * @bodyParam shipping_phone string optional Shipping phone (max: 20). Example: "09123456789"
     * @bodyParam shipping_fee numeric optional Shipping fee (min: 0). Example: 10000
     * @bodyParam discount numeric optional Discount amount (min: 0). Example: 5000
     * 
     * @response 201 {
     *   "message": "Order created successfully",
     *   "data": {
     *     "id": 1,
     *     "total_amount": 210000,
     *     "payment_status": "paid",
     *     "status": "pending"
     *   }
     * }
     * 
     * @response 403 {
     *   "errors": [
     *     {
     *       "code": "validation",
     *       "message": "The products field is required."
     *     }
     *   ]
     * }
     */
    public function createOrder(Request $request): JsonResponse
    {
        // Convert 'online' to 'digital_payment' for backward compatibility
        // تبدیل 'online' به 'digital_payment' برای سازگاری با نسخه‌های قبلی
        if ($request->payment_method === 'online') {
            $request->merge(['payment_method' => 'digital_payment']);
        }
        
        $validator = Validator::make($request->all(), [
            'salon_id' => 'required|integer|exists:beauty_salons,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:beauty_retail_products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:digital_payment,wallet,cash_payment',
            'shipping_address' => 'nullable|string|max:500',
            'shipping_phone' => 'nullable|string|max:20',
            'shipping_fee' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            DB::beginTransaction();
            
            $order = $this->retailService->createOrder(
                $request->user()->id,
                $request->salon_id,
                $request->all()
            );
            
            // Process payment
            // پردازش پرداخت
            // Payment method already converted before validation
            // روش پرداخت قبلاً قبل از اعتبارسنجی تبدیل شده است
            $paymentMethod = $request->payment_method;
            
            $paymentProcessed = false;
            
            switch ($paymentMethod) {
                case 'wallet':
                    // Wallet payment
                    // پرداخت از کیف پول
                    $walletTransaction = CustomerLogic::create_wallet_transaction(
                        $request->user()->id,
                        -$order->total_amount,
                        'beauty_retail_order',
                        $order->id
                    );
                    
                    if ($walletTransaction) {
                        $order->update([
                            'payment_status' => 'paid',
                            'payment_method' => 'wallet',
                        ]);
                        $paymentProcessed = true;
                    } else {
                        DB::rollBack();
                        return $this->errorResponse([
                            ['code' => 'payment', 'message' => translate('insufficient_wallet_balance')],
                        ]);
                    }
                    break;
                    
                case 'digital_payment':
                    // Digital payment would be handled through payment gateway
                    // پرداخت دیجیتال از طریق درگاه پرداخت انجام می‌شود
                    // For now, mark as pending
                    // فعلاً به عنوان pending علامت بزنید
                    $order->update([
                        'payment_status' => 'unpaid',
                        'payment_method' => 'digital_payment',
                    ]);
                    $paymentProcessed = true;
                    break;
                    
                case 'cash_payment':
                    // Cash payment - mark as unpaid, will be paid on delivery
                    // پرداخت نقدی - به عنوان پرداخت نشده علامت بزنید، هنگام تحویل پرداخت می‌شود
                    $order->update([
                        'payment_status' => 'unpaid',
                        'payment_method' => 'cash_payment',
                    ]);
                    $paymentProcessed = true;
                    break;
            }
            
            // Record revenue if payment is successful
            // ثبت درآمد در صورت موفقیت پرداخت
            if ($paymentProcessed && $order->payment_status === 'paid') {
                $this->retailService->recordRevenue($order);
            }
            
            DB::commit();
            
            return $this->successResponse('messages.order_created_successfully', $this->formatOrder($order->fresh(), true), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Retail order creation failed', [
                'salon_id' => $request->salon_id ?? null,
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->errorResponse([
                ['code' => 'order', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * List retail orders for user
     * لیست سفارشات خرده‌فروشی کاربر
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrders(Request $request): JsonResponse
    {
        $limit = $request->get('per_page', $request->get('limit', 25));
        $offset = $request->get('offset', 0);
        $page = $offset > 0 ? max(1, (int)floor($offset / $limit) + 1) : 1;

        $orders = BeautyRetailOrder::where('user_id', $request->user()->id)
            ->with('salon.store')
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate($limit, ['*'], 'page', $page);

        $formatted = $orders->getCollection()->map(function ($order) {
            return $this->formatOrder($order, false);
        });

        $orders->setCollection($formatted->values());

        return $this->listResponse($orders);
    }

    /**
     * Get order details
     * دریافت جزئیات سفارش خرده‌فروشی
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getOrderDetails(Request $request, int $id): JsonResponse
    {
        $order = BeautyRetailOrder::where('user_id', $request->user()->id)
            ->with('salon.store')
            ->findOrFail($id);

        return $this->successResponse('messages.data_retrieved_successfully', $this->formatOrder($order, true));
    }

    /**
     * Format retail order for API responses
     * فرمت سفارش خرده‌فروشی برای پاسخ API
     *
     * @param BeautyRetailOrder $order
     * @param bool $includeProducts
     * @return array
     */
    private function formatOrder(BeautyRetailOrder $order, bool $includeProducts = true): array
    {
        $data = [
            'id' => $order->id,
            'order_reference' => $order->order_reference ?? ('RT-' . $order->id),
            'total_amount' => $order->total_amount,
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'salon' => $order->salon ? [
                'id' => $order->salon->id,
                'name' => $order->salon->store?->name ?? '',
            ] : null,
            'created_at' => $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : null,
        ];

        if ($includeProducts) {
            $data['products'] = collect($order->products ?? [])->map(function ($product) {
                return [
                    'id' => $product['product_id'] ?? null,
                    'name' => $product['name'] ?? '',
                    'quantity' => $product['quantity'] ?? 0,
                    'price' => $product['price'] ?? 0,
                    'subtotal' => $product['subtotal'] ?? 0,
                ];
            });

            $data['shipping_address'] = $order->shipping_address;
            $data['shipping_phone'] = $order->shipping_phone;
        }

        return $data;
    }
}

