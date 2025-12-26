import 'package:get/get.dart';
import 'package:sixam_mart_store/api/api_client.dart';
import 'package:sixam_mart_store/features/util/vendor_beauty_module_constants.dart';
import '../models/vendor_beauty_retail_product_model.dart';
import '../models/vendor_beauty_retail_order_model.dart';
import 'vendor_beauty_retail_repository_interface.dart';

class VendorBeautyRetailRepository implements VendorBeautyRetailRepositoryInterface {
  final ApiClient apiClient;

  VendorBeautyRetailRepository({required this.apiClient});

  @override
  Future<List<VendorBeautyRetailProductModel>?> getProducts({int? offset, int? limit}) async {
    String uri = VendorBeautyModuleConstants.vendorRetailProductsUri;
    if (offset != null) {
      uri += '?offset=$offset&limit=${limit ?? 10}';
    }

    Response response = await apiClient.getData(uri);
    if (response.statusCode == 200) {
      final List<VendorBeautyRetailProductModel> products = [];
      final data = response.body['products'] ?? response.body['data'] ?? response.body;
      if (data is List) {
        for (final item in data) {
          products.add(VendorBeautyRetailProductModel.fromJson(item));
        }
      }
      return products;
    }
    return null;
  }

  @override
  Future<VendorBeautyRetailProductModel?> getProductDetails(int productId) async {
    Response response = await apiClient.getData(
      '${VendorBeautyModuleConstants.vendorRetailProductDetailsUri}/$productId',
    );
    if (response.statusCode == 200) {
      final data = response.body['product'] ?? response.body['data'] ?? response.body;
      if (data is Map<String, dynamic>) {
        return VendorBeautyRetailProductModel.fromJson(data);
      }
    }
    return null;
  }

  @override
  Future<bool> createProduct(VendorBeautyRetailProductModel product) async {
    Response response = await apiClient.postData(
      VendorBeautyModuleConstants.vendorCreateRetailProductUri,
      product.toJson(),
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> updateProduct(int productId, VendorBeautyRetailProductModel product) async {
    Response response = await apiClient.postData(
      '${VendorBeautyModuleConstants.vendorUpdateRetailProductUri}/$productId',
      product.toJson(),
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> deleteProduct(int productId) async {
    Response response = await apiClient.deleteData(
      '${VendorBeautyModuleConstants.vendorDeleteRetailProductUri}/$productId',
    );
    return response.statusCode == 200;
  }

  @override
  Future<List<VendorBeautyRetailOrderModel>?> getOrders({int? offset, int? limit}) async {
    String uri = VendorBeautyModuleConstants.vendorRetailOrdersUri;
    if (offset != null) {
      uri += '?offset=$offset&limit=${limit ?? 10}';
    }

    Response response = await apiClient.getData(uri);
    if (response.statusCode == 200) {
      final List<VendorBeautyRetailOrderModel> orders = [];
      final data = response.body['orders'] ?? response.body['data'] ?? response.body;
      if (data is List) {
        for (final item in data) {
          orders.add(VendorBeautyRetailOrderModel.fromJson(item));
        }
      }
      return orders;
    }
    return null;
  }

  @override
  Future<VendorBeautyRetailOrderModel?> getOrderDetails(int orderId) async {
    Response response = await apiClient.getData(
      '${VendorBeautyModuleConstants.vendorRetailOrderDetailsUri}/$orderId',
    );
    if (response.statusCode == 200) {
      final data = response.body['order'] ?? response.body['data'] ?? response.body;
      if (data is Map<String, dynamic>) {
        return VendorBeautyRetailOrderModel.fromJson(data);
      }
    }
    return null;
  }

  @override
  Future add(value) {
    throw UnimplementedError();
  }

  @override
  Future delete(int? id) {
    throw UnimplementedError();
  }

  @override
  Future get(int? id) {
    throw UnimplementedError();
  }

  @override
  Future getList() {
    throw UnimplementedError();
  }

  @override
  Future update(Map<String, dynamic> body) {
    throw UnimplementedError();
  }
}
