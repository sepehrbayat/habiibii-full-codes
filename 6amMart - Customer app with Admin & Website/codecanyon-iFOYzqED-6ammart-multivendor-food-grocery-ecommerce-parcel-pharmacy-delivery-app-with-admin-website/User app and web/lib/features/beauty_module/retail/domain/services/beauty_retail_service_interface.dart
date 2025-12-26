import '../models/beauty_retail_product_model.dart';
import '../models/beauty_retail_order_model.dart';

abstract class BeautyRetailServiceInterface {
  // Product Methods
  Future<List<BeautyRetailProductModel>> getRetailProducts({
    int? salonId,
    int? categoryId,
    String? search,
    int? offset,
    int? limit,
  });
  
  Future<BeautyRetailProductModel?> getProductDetails(int productId);
  
  Future<List<BeautyRetailProductModel>> getPopularProducts({int? salonId});
  
  Future<List<BeautyRetailProductModel>> getRecommendedProducts({int? salonId});
  
  // Cart Methods
  Future<bool> addToCart({
    required int productId,
    required int quantity,
    int? salonId,
  });
  
  Future<bool> updateCartQuantity({
    required int cartItemId,
    required int quantity,
  });
  
  Future<bool> removeFromCart(int cartItemId);
  
  // Order Methods
  Future<List<BeautyRetailOrderModel>> getRetailOrders({
    String? status,
    int? offset,
    int? limit,
  });
  
  Future<BeautyRetailOrderModel?> getOrderDetails(int orderId);
  
  Future<BeautyRetailOrderModel?> placeOrder({
    required List<Map<String, dynamic>> items,
    required String paymentMethod,
    required Map<String, dynamic> deliveryAddress,
    String? couponCode,
  });
  
  Future<bool> cancelOrder(int orderId, String reason);
  
  Future<bool> trackOrder(int orderId);
  
  // Business Logic Methods
  List<BeautyRetailProductModel> filterProductsByPrice(
    List<BeautyRetailProductModel> products,
    double minPrice,
    double maxPrice,
  );
  
  List<BeautyRetailProductModel> sortProducts(
    List<BeautyRetailProductModel> products,
    String sortBy,
  );
  
  double calculateCartTotal(List<Map<String, dynamic>> cartItems);
  
  double calculateDiscountAmount(double total, String? couponCode);
  
  String getOrderStatusText(String status);
  
  bool canCancelOrder(BeautyRetailOrderModel order);
  
  bool isProductInStock(BeautyRetailProductModel product);
  
  // Validation Methods
  bool validateProductQuantity(int productId, int quantity);
  
  bool validateDeliveryAddress(Map<String, dynamic> address);
  
  bool validatePaymentMethod(String paymentMethod);
  
  // Analytics Methods
  Future<void> trackProductView(int productId);
  
  Future<void> trackAddToCart(int productId, int quantity);
  
  Future<void> trackPurchase(BeautyRetailOrderModel order);
}
