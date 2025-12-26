import '../models/vendor_beauty_retail_order_model.dart';
import '../models/vendor_beauty_retail_product_model.dart';
import '../repositories/vendor_beauty_retail_repository_interface.dart';
import 'vendor_beauty_retail_service_interface.dart';

class VendorBeautyRetailService implements VendorBeautyRetailServiceInterface {
  final VendorBeautyRetailRepositoryInterface retailRepository;

  VendorBeautyRetailService({required this.retailRepository});

  @override
  Future<List<VendorBeautyRetailProductModel>?> getProducts({int? offset, int? limit}) async {
    return await retailRepository.getProducts(offset: offset, limit: limit);
  }

  @override
  Future<VendorBeautyRetailProductModel?> getProductDetails(int productId) async {
    return await retailRepository.getProductDetails(productId);
  }

  @override
  Future<bool> createProduct(VendorBeautyRetailProductModel product) async {
    return await retailRepository.createProduct(product);
  }

  @override
  Future<bool> updateProduct(int productId, VendorBeautyRetailProductModel product) async {
    return await retailRepository.updateProduct(productId, product);
  }

  @override
  Future<bool> deleteProduct(int productId) async {
    return await retailRepository.deleteProduct(productId);
  }

  @override
  Future<List<VendorBeautyRetailOrderModel>?> getOrders({int? offset, int? limit}) async {
    return await retailRepository.getOrders(offset: offset, limit: limit);
  }

  @override
  Future<VendorBeautyRetailOrderModel?> getOrderDetails(int orderId) async {
    return await retailRepository.getOrderDetails(orderId);
  }
}
