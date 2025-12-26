import 'beauty_retail_service_interface.dart';
import '../repositories/beauty_retail_repository_interface.dart';
import '../models/beauty_retail_product_model.dart';
import '../models/beauty_retail_order_model.dart';

class BeautyRetailService implements BeautyRetailServiceInterface {
  final BeautyRetailRepositoryInterface retailRepository;
  
  BeautyRetailService({required this.retailRepository});
  
  @override
  Future<List<BeautyRetailProductModel>> getRetailProducts({
    int? salonId,
    int? categoryId,
    String? search,
    int? offset,
    int? limit,
  }) async {
    return await retailRepository.getRetailProducts(
      salonId: salonId,
      categoryId: categoryId,
      search: search,
      offset: offset,
      limit: limit,
    );
  }
  
  @override
  Future<BeautyRetailProductModel?> getProductDetails(int productId) async {
    return await retailRepository.getProductDetails(productId);
  }
  
  @override
  Future<List<BeautyRetailProductModel>> getPopularProducts({int? salonId}) async {
    return await retailRepository.getPopularProducts(salonId: salonId);
  }
  
  @override
  Future<List<BeautyRetailProductModel>> getRecommendedProducts({int? salonId}) async {
    return await retailRepository.getRecommendedProducts(salonId: salonId);
  }
  
  @override
  Future<bool> addToCart({
    required int productId,
    required int quantity,
    int? salonId,
  }) async {
    // Validate quantity
    if (quantity <= 0) {
      return false;
    }
    
    // Check if product is in stock
    BeautyRetailProductModel? product = await getProductDetails(productId);
    if (product != null && !isProductInStock(product)) {
      return false;
    }
    
    return await retailRepository.addToCart(
      productId: productId,
      quantity: quantity,
      salonId: salonId,
    );
  }
  
  @override
  Future<bool> updateCartQuantity({
    required int cartItemId,
    required int quantity,
  }) async {
    // Validate quantity
    if (quantity < 0) {
      return false;
    }
    
    // If quantity is 0, remove from cart
    if (quantity == 0) {
      return await removeFromCart(cartItemId);
    }
    
    return await retailRepository.updateCartQuantity(
      cartItemId: cartItemId,
      quantity: quantity,
    );
  }
  
  @override
  Future<bool> removeFromCart(int cartItemId) async {
    return await retailRepository.removeFromCart(cartItemId);
  }
  
  @override
  Future<List<BeautyRetailOrderModel>> getRetailOrders({
    String? status,
    int? offset,
    int? limit,
  }) async {
    return await retailRepository.getRetailOrders(
      status: status,
      offset: offset,
      limit: limit,
    );
  }
  
  @override
  Future<BeautyRetailOrderModel?> getOrderDetails(int orderId) async {
    return await retailRepository.getOrderDetails(orderId);
  }
  
  @override
  Future<BeautyRetailOrderModel?> placeOrder({
    required List<Map<String, dynamic>> items,
    required String paymentMethod,
    required Map<String, dynamic> deliveryAddress,
    String? couponCode,
  }) async {
    // Validate items
    if (items.isEmpty) {
      return null;
    }
    
    // Validate payment method
    if (paymentMethod.isEmpty) {
      return null;
    }
    
    // Validate delivery address
    if (deliveryAddress.isEmpty) {
      return null;
    }
    
    return await retailRepository.placeOrder(
      items: items,
      paymentMethod: paymentMethod,
      deliveryAddress: deliveryAddress,
      couponCode: couponCode,
    );
  }
  
  @override
  Future<bool> cancelOrder(int orderId, String reason) async {
    // Check if order can be cancelled
    BeautyRetailOrderModel? order = await getOrderDetails(orderId);
    if (order != null && !canCancelOrder(order)) {
      return false;
    }
    
    return await retailRepository.cancelOrder(orderId, reason);
  }
  
  @override
  Future<bool> trackOrder(int orderId) async {
    return await retailRepository.trackOrder(orderId);
  }
  
  @override
  double calculateCartTotal(List<Map<String, dynamic>> cartItems) {
    double total = 0.0;
    
    for (var item in cartItems) {
      double price = (item['price'] ?? 0.0).toDouble();
      int quantity = (item['quantity'] ?? 0);
      double discount = (item['discount'] ?? 0.0).toDouble();
      
      double itemTotal = (price * quantity);
      double discountAmount = itemTotal * (discount / 100);
      total += (itemTotal - discountAmount);
    }
    
    return total;
  }
  
  @override
  bool isProductInStock(BeautyRetailProductModel product) {
    return product.stock != null && product.stock! > 0;
  }
  
  @override
  String getOrderStatusText(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'Order Pending';
      case 'confirmed':
        return 'Order Confirmed';
      case 'processing':
        return 'Processing';
      case 'out_for_delivery':
        return 'Out for Delivery';
      case 'delivered':
        return 'Delivered';
      case 'cancelled':
        return 'Cancelled';
      case 'refunded':
        return 'Refunded';
      case 'failed':
        return 'Failed';
      default:
        return status;
    }
  }
  
  @override
  bool canCancelOrder(BeautyRetailOrderModel order) {
    // Can only cancel if order is pending or confirmed
    String status = order.status?.toLowerCase() ?? '';
    return status == 'pending' || status == 'confirmed';
  }
  
  @override
  double calculateDiscountAmount(double total, String? couponCode) {
    if (couponCode == null || couponCode.isEmpty) {
      return 0.0;
    }
    
    // This is a simplified implementation
    // In real app, you would validate the coupon code with backend
    double discountPercentage = 10.0; // Default 10% discount
    
    // Special coupon codes
    if (couponCode.toUpperCase() == 'BEAUTY20') {
      discountPercentage = 20.0;
    } else if (couponCode.toUpperCase() == 'FIRST50') {
      discountPercentage = 50.0;
    } else if (couponCode.toUpperCase() == 'SAVE15') {
      discountPercentage = 15.0;
    }
    
    return total * (discountPercentage / 100);
  }
  
  @override
  List<BeautyRetailProductModel> filterProductsByPrice(
    List<BeautyRetailProductModel> products,
    double minPrice,
    double maxPrice,
  ) {
    return products.where((product) {
      double price = product.price ?? 0.0;
      return price >= minPrice && price <= maxPrice;
    }).toList();
  }
  
  @override
  List<BeautyRetailProductModel> sortProducts(
    List<BeautyRetailProductModel> products,
    String sortBy,
  ) {
    List<BeautyRetailProductModel> sortedProducts = List.from(products);
    
    switch (sortBy.toLowerCase()) {
      case 'price_low_to_high':
        sortedProducts.sort((a, b) => 
          (a.price ?? 0.0).compareTo(b.price ?? 0.0));
        break;
      case 'price_high_to_low':
        sortedProducts.sort((a, b) => 
          (b.price ?? 0.0).compareTo(a.price ?? 0.0));
        break;
      case 'name_a_to_z':
        sortedProducts.sort((a, b) => 
          (a.name ?? '').compareTo(b.name ?? ''));
        break;
      case 'name_z_to_a':
        sortedProducts.sort((a, b) => 
          (b.name ?? '').compareTo(a.name ?? ''));
        break;
      case 'rating':
        sortedProducts.sort((a, b) => 
          (b.rating ?? 0.0).compareTo(a.rating ?? 0.0));
        break;
      case 'popularity':
        sortedProducts.sort((a, b) => 
          (b.reviewCount ?? 0).compareTo(a.reviewCount ?? 0));
        break;
      case 'newest':
        sortedProducts.sort((a, b) {
          DateTime aDate = a.createdAt ?? DateTime.now();
          DateTime bDate = b.createdAt ?? DateTime.now();
          return bDate.compareTo(aDate);
        });
        break;
      default:
        // Default sorting by ID
        sortedProducts.sort((a, b) => 
          (a.id ?? 0).compareTo(b.id ?? 0));
    }
    
    return sortedProducts;
  }
  
  // Validation Methods
  @override
  bool validateProductQuantity(int productId, int quantity) {
    // Basic validation - in real app, would check against actual stock
    return quantity > 0 && quantity <= 100;
  }
  
  @override
  bool validateDeliveryAddress(Map<String, dynamic> address) {
    // Check required fields
    return address.containsKey('street') &&
           address.containsKey('city') &&
           address.containsKey('postal_code') &&
           address['street'].toString().isNotEmpty &&
           address['city'].toString().isNotEmpty &&
           address['postal_code'].toString().isNotEmpty;
  }
  
  @override
  bool validatePaymentMethod(String paymentMethod) {
    // Check if payment method is valid
    List<String> validMethods = ['cash', 'card', 'wallet', 'online'];
    return validMethods.contains(paymentMethod.toLowerCase());
  }
  
  // Analytics Methods
  @override
  Future<void> trackProductView(int productId) async {
    // Track product view for analytics
    // In real app, this would send data to analytics service
    print('Product viewed: $productId');
  }
  
  @override
  Future<void> trackAddToCart(int productId, int quantity) async {
    // Track add to cart event
    // In real app, this would send data to analytics service
    print('Added to cart: Product $productId, Quantity: $quantity');
  }
  
  @override
  Future<void> trackPurchase(BeautyRetailOrderModel order) async {
    // Track purchase event
    // In real app, this would send data to analytics service
    print('Purchase tracked: Order ${order.orderNumber}, Total: ${order.total}');
  }
}
