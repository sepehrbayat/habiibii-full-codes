import '../models/beauty_retail_product_model.dart';
import '../models/beauty_retail_order_model.dart';

abstract class BeautyRetailRepositoryInterface {
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
}
