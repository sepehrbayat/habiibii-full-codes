import '../models/vendor_beauty_retail_order_model.dart';
import '../models/vendor_beauty_retail_product_model.dart';

abstract class VendorBeautyRetailServiceInterface {
  Future<List<VendorBeautyRetailProductModel>?> getProducts({int? offset, int? limit});
  Future<VendorBeautyRetailProductModel?> getProductDetails(int productId);
  Future<bool> createProduct(VendorBeautyRetailProductModel product);
  Future<bool> updateProduct(int productId, VendorBeautyRetailProductModel product);
  Future<bool> deleteProduct(int productId);

  Future<List<VendorBeautyRetailOrderModel>?> getOrders({int? offset, int? limit});
  Future<VendorBeautyRetailOrderModel?> getOrderDetails(int orderId);
}
