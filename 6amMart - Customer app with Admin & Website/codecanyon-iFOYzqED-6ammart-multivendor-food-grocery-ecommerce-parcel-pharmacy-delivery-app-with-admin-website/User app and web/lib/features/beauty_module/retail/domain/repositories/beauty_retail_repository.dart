import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/retail/domain/repositories/beauty_retail_repository_interface.dart';
import '../models/beauty_retail_product_model.dart';
import '../models/beauty_retail_order_model.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';

class BeautyRetailRepository implements BeautyRetailRepositoryInterface {
  final ApiClient apiClient;
  
  BeautyRetailRepository({required this.apiClient});
  
  @override
  Future<List<BeautyRetailProductModel>> getRetailProducts({
    int? salonId,
    int? categoryId,
    String? search,
    int? offset,
    int? limit,
  }) async {
    try {
      Map<String, dynamic> params = {};
      if (salonId != null) params['salon_id'] = salonId.toString();
      if (categoryId != null) params['category_id'] = categoryId.toString();
      if (search != null) params['search'] = search;
      if (offset != null) params['offset'] = offset.toString();
      if (limit != null) params['limit'] = limit.toString();
      
      Response response = await apiClient.getData(
        BeautyModuleConstants.beautyRetailProductsUri,
        query: params,
      );
      
      if (response.statusCode == 200) {
        List<BeautyRetailProductModel> products = [];
        response.body['products'].forEach((product) {
          products.add(BeautyRetailProductModel.fromJson(product));
        });
        return products;
      }
      return [];
    } catch (e) {
      print('Error getting retail products: $e');
      return [];
    }
  }
  
  @override
  Future<BeautyRetailProductModel?> getProductDetails(int productId) async {
    try {
      Response response = await apiClient.getData(
        '${BeautyModuleConstants.beautyRetailProductDetailsUri}/$productId',
      );
      
      if (response.statusCode == 200) {
        return BeautyRetailProductModel.fromJson(response.body['product']);
      }
      return null;
    } catch (e) {
      print('Error getting product details: $e');
      return null;
    }
  }
  
  @override
  Future<List<BeautyRetailProductModel>> getPopularProducts({int? salonId}) async {
    try {
      Map<String, dynamic> params = {};
      if (salonId != null) params['salon_id'] = salonId.toString();
      
      Response response = await apiClient.getData(
        BeautyModuleConstants.beautyPopularProductsUri,
        query: params,
      );
      
      if (response.statusCode == 200) {
        List<BeautyRetailProductModel> products = [];
        response.body['products'].forEach((product) {
          products.add(BeautyRetailProductModel.fromJson(product));
        });
        return products;
      }
      return [];
    } catch (e) {
      print('Error getting popular products: $e');
      return [];
    }
  }
  
  @override
  Future<List<BeautyRetailProductModel>> getRecommendedProducts({int? salonId}) async {
    try {
      Map<String, dynamic> params = {};
      if (salonId != null) params['salon_id'] = salonId.toString();
      
      Response response = await apiClient.getData(
        BeautyModuleConstants.beautyPopularProductsUri,
        query: params,
      );
      
      if (response.statusCode == 200) {
        List<BeautyRetailProductModel> products = [];
        response.body['products'].forEach((product) {
          products.add(BeautyRetailProductModel.fromJson(product));
        });
        return products;
      }
      return [];
    } catch (e) {
      print('Error getting recommended products: $e');
      return [];
    }
  }
  
  @override
  Future<bool> addToCart({
    required int productId,
    required int quantity,
    int? salonId,
  }) async {
    try {
      Map<String, dynamic> data = {
        'product_id': productId,
        'quantity': quantity,
      };
      if (salonId != null) data['salon_id'] = salonId;
      
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyAddToCartUri,
        data,
      );
      
      return response.statusCode == 200;
    } catch (e) {
      print('Error adding to cart: $e');
      return false;
    }
  }
  
  @override
  Future<bool> updateCartQuantity({
    required int cartItemId,
    required int quantity,
  }) async {
    try {
      Response response = await apiClient.putData(
        '${BeautyModuleConstants.beautyUpdateCartUri}/$cartItemId',
        {'quantity': quantity},
      );
      
      return response.statusCode == 200;
    } catch (e) {
      print('Error updating cart: $e');
      return false;
    }
  }
  
  @override
  Future<bool> removeFromCart(int cartItemId) async {
    try {
      Response response = await apiClient.deleteData(
        '${BeautyModuleConstants.beautyRemoveFromCartUri}/$cartItemId',
      );
      
      return response.statusCode == 200;
    } catch (e) {
      print('Error removing from cart: $e');
      return false;
    }
  }
  
  @override
  Future<List<BeautyRetailOrderModel>> getRetailOrders({
    String? status,
    int? offset,
    int? limit,
  }) async {
    try {
      Map<String, dynamic> params = {};
      if (status != null) params['status'] = status;
      if (offset != null) params['offset'] = offset.toString();
      if (limit != null) params['limit'] = limit.toString();
      
      Response response = await apiClient.getData(
        BeautyModuleConstants.beautyGetOrdersUri,
        query: params,
      );
      
      if (response.statusCode == 200) {
        List<BeautyRetailOrderModel> orders = [];
        response.body['orders'].forEach((order) {
          orders.add(BeautyRetailOrderModel.fromJson(order));
        });
        return orders;
      }
      return [];
    } catch (e) {
      print('Error getting retail orders: $e');
      return [];
    }
  }
  
  @override
  Future<BeautyRetailOrderModel?> getOrderDetails(int orderId) async {
    try {
      Response response = await apiClient.getData(
        '${BeautyModuleConstants.beautyGetOrderDetailsUri}/$orderId',
      );
      
      if (response.statusCode == 200) {
        return BeautyRetailOrderModel.fromJson(response.body['order']);
      }
      return null;
    } catch (e) {
      print('Error getting order details: $e');
      return null;
    }
  }
  
  @override
  Future<BeautyRetailOrderModel?> placeOrder({
    required List<Map<String, dynamic>> items,
    required String paymentMethod,
    required Map<String, dynamic> deliveryAddress,
    String? couponCode,
  }) async {
    try {
      Map<String, dynamic> data = {
        'items': items,
        'payment_method': paymentMethod,
        'delivery_address': deliveryAddress,
      };
      if (couponCode != null) data['coupon_code'] = couponCode;
      
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyPlaceOrderUri,
        data,
      );
      
      if (response.statusCode == 200) {
        return BeautyRetailOrderModel.fromJson(response.body['order']);
      }
      return null;
    } catch (e) {
      print('Error placing order: $e');
      return null;
    }
  }
  
  @override
  Future<bool> cancelOrder(int orderId, String reason) async {
    try {
      Response response = await apiClient.postData(
        '${BeautyModuleConstants.beautyCancelOrderUri}/$orderId',
        {'reason': reason},
      );
      
      return response.statusCode == 200;
    } catch (e) {
      print('Error canceling order: $e');
      return false;
    }
  }
  
  @override
  Future<bool> trackOrder(int orderId) async {
    try {
      Response response = await apiClient.getData(
        '${BeautyModuleConstants.beautyTrackOrderUri}/$orderId',
      );
      
      return response.statusCode == 200;
    } catch (e) {
      print('Error tracking order: $e');
      return false;
    }
  }
}
