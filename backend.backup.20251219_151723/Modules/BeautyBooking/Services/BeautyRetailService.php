<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Services;

use Modules\BeautyBooking\Entities\BeautyRetailProduct;
use Modules\BeautyBooking\Entities\BeautyRetailOrder;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Services\BeautyRevenueService;
use App\CentralLogics\Helpers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * Beauty Retail Service
 * سرویس خرده‌فروشی
 *
 * Handles retail product sales
 * مدیریت فروش محصولات خرده‌فروشی
 */
class BeautyRetailService
{
    public function __construct(
        private BeautyRetailProduct $product,
        private BeautyRetailOrder $order,
        private BeautyRevenueService $revenueService
    ) {}
    
    /**
     * Create a retail order
     * ایجاد سفارش خرده‌فروشی
     *
     * @param int $userId
     * @param int $salonId
     * @param array $orderData
     * @return BeautyRetailOrder
     * @throws \Exception
     */
    public function createOrder(int $userId, int $salonId, array $orderData): BeautyRetailOrder
    {
        // Wrap entire order creation in transaction to prevent race conditions
        // قرار دادن کل فرآیند ایجاد سفارش در transaction برای جلوگیری از race condition
        // Fixed: Stock validation, order creation, and stock update are now atomic
        // اصلاح شده: اعتبارسنجی موجودی، ایجاد سفارش و به‌روزرسانی موجودی اکنون atomic است
        return DB::transaction(function () use ($userId, $salonId, $orderData) {
            // Validate products and check stock WITH LOCKS to prevent race conditions
            // اعتبارسنجی محصولات و بررسی موجودی با قفل برای جلوگیری از race condition
            $products = $this->validateProductsWithLock($salonId, $orderData['products']);
            
            // Calculate amounts
            // محاسبه مبالغ
            $amounts = $this->calculateOrderAmounts($products, $orderData);
            
            // Get salon with store to get zone_id
            // دریافت سالن با store برای دریافت zone_id
            $salon = BeautySalon::with('store')->findOrFail($salonId);
            
            // Generate order reference
            // تولید شماره مرجع سفارش
            $orderReference = $this->generateOrderReference();
            
            // Create order
            // ایجاد سفارش
            $order = $this->order->create([
                'user_id' => $userId,
                'salon_id' => $salonId,
                'zone_id' => $salon->store?->zone_id ?? $salon->zone_id ?? null,
                'order_reference' => $orderReference,
                'products' => $products,
                'subtotal' => $amounts['subtotal'],
                'tax_amount' => $amounts['tax_amount'],
                'shipping_fee' => $amounts['shipping_fee'],
                'discount' => $amounts['discount'],
                'total_amount' => $amounts['total'],
                'commission_amount' => $amounts['commission'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $orderData['payment_method'] ?? null,
                'shipping_address' => $orderData['shipping_address'] ?? null,
                'shipping_phone' => $orderData['shipping_phone'] ?? null,
                'notes' => $orderData['notes'] ?? null,
            ]);
            
            // Update product stock (already validated with locks above)
            // به‌روزرسانی موجودی محصولات (قبلاً با قفل اعتبارسنجی شده است)
            $this->updateProductStock($products);
            
            return $order;
        });
    }
    
    /**
     * Validate products and check stock WITH LOCKS to prevent race conditions
     * اعتبارسنجی محصولات و بررسی موجودی با قفل برای جلوگیری از race condition
     *
     * @param int $salonId
     * @param array $productsData
     * @return array
     * @throws \Exception
     */
    private function validateProductsWithLock(int $salonId, array $productsData): array
    {
        $validatedProducts = [];
        
        foreach ($productsData as $productData) {
            if (!isset($productData['product_id']) || !isset($productData['quantity'])) {
                throw new \Exception(translate('messages.invalid_product_data'));
            }
            
            // Lock product row for update to prevent concurrent stock changes
            // قفل کردن ردیف محصول برای به‌روزرسانی برای جلوگیری از تغییرات همزمان موجودی
            $product = $this->product->where('id', $productData['product_id'])
                ->lockForUpdate()
                ->firstOrFail();
            
            // Verify product belongs to salon
            // تأیید اینکه محصول متعلق به سالن است
            if ($product->salon_id != $salonId) {
                throw new \Exception(translate('messages.product_not_belongs_to_salon'));
            }
            
            // Check stock AFTER locking to ensure accurate check
            // بررسی موجودی پس از قفل کردن برای اطمینان از بررسی دقیق
            if (!$product->isInStock($productData['quantity'])) {
                throw new \Exception(translate('messages.product_out_of_stock', ['name' => $product->name]));
            }
            
            $validatedProducts[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $productData['quantity'],
                'price' => $product->price,
                'subtotal' => $product->price * $productData['quantity'],
            ];
        }
        
        return $validatedProducts;
    }
    
    /**
     * Calculate order amounts
     * محاسبه مبالغ سفارش
     *
     * @param array $products
     * @param array $orderData
     * @return array
     */
    private function calculateOrderAmounts(array $products, array $orderData): array
    {
        $subtotal = array_sum(array_column($products, 'subtotal'));
        
        // Calculate tax
        // محاسبه مالیات
        $taxPercentage = config('beautybooking.tax.percentage', 0);
        $taxAmount = $subtotal * ($taxPercentage / 100);
        
        // Calculate shipping fee
        // محاسبه هزینه ارسال
        $shippingFee = $orderData['shipping_fee'] ?? 0.0;
        
        // Calculate discount (if any)
        // محاسبه تخفیف (در صورت وجود)
        $discount = $orderData['discount'] ?? 0.0;
        
        // Calculate commission
        // محاسبه کمیسیون
        $commissionPercentage = config('beautybooking.retail.commission_percentage', 10.0);
        $commission = $subtotal * ($commissionPercentage / 100);
        
        // Total amount
        // مبلغ کل
        $total = $subtotal + $taxAmount + $shippingFee - $discount;
        
        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_fee' => $shippingFee,
            'discount' => $discount,
            'commission' => $commission,
            'total' => $total,
        ];
    }
    
    /**
     * Update product stock after order
     * به‌روزرسانی موجودی محصولات پس از سفارش
     *
     * @param array $products
     * @return void
     */
    private function updateProductStock(array $products): void
    {
        foreach ($products as $productData) {
            $product = $this->product->find($productData['product_id']);
            if ($product) {
                $product->decrement('stock_quantity', $productData['quantity']);
            }
        }
    }
    
    /**
     * Generate unique order reference
     * تولید شماره مرجع منحصر به فرد سفارش
     *
     * @return string
     */
    private function generateOrderReference(): string
    {
        do {
            $reference = 'RT' . strtoupper(Str::random(8));
        } while ($this->order->where('order_reference', $reference)->exists());
        
        return $reference;
    }
    
    /**
     * Record retail sale revenue
     * ثبت درآمد فروش خرده‌فروشی
     *
     * @param BeautyRetailOrder $order
     * @return void
     */
    public function recordRevenue(BeautyRetailOrder $order): void
    {
        $this->revenueService->recordRetailSale($order->salon_id, $order->total_amount);
    }
}

