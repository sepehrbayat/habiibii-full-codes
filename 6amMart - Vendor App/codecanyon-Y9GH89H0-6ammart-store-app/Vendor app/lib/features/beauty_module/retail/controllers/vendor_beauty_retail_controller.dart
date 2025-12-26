import 'package:get/get.dart';
import '../domain/models/vendor_beauty_retail_order_model.dart';
import '../domain/models/vendor_beauty_retail_product_model.dart';
import '../domain/services/vendor_beauty_retail_service_interface.dart';

class VendorBeautyRetailController extends GetxController implements GetxService {
  final VendorBeautyRetailServiceInterface retailService;

  VendorBeautyRetailController({required this.retailService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<VendorBeautyRetailProductModel>? _products;
  List<VendorBeautyRetailProductModel>? get products => _products;

  List<VendorBeautyRetailOrderModel>? _orders;
  List<VendorBeautyRetailOrderModel>? get orders => _orders;

  VendorBeautyRetailProductModel? _selectedProduct;
  VendorBeautyRetailProductModel? get selectedProduct => _selectedProduct;

  VendorBeautyRetailOrderModel? _selectedOrder;
  VendorBeautyRetailOrderModel? get selectedOrder => _selectedOrder;

  @override
  void onInit() {
    super.onInit();
    getProducts();
  }

  Future<void> getProducts({bool isRefresh = false}) async {
    _isLoading = true;
    update();

    try {
      _products = await retailService.getProducts();
    } catch (e) {
      print('Error loading products: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<void> getOrders() async {
    _isLoading = true;
    update();

    try {
      _orders = await retailService.getOrders();
    } catch (e) {
      print('Error loading orders: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<void> getProductDetails(int productId) async {
    _isLoading = true;
    update();

    try {
      _selectedProduct = await retailService.getProductDetails(productId);
    } catch (e) {
      print('Error loading product details: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<void> getOrderDetails(int orderId) async {
    _isLoading = true;
    update();

    try {
      _selectedOrder = await retailService.getOrderDetails(orderId);
    } catch (e) {
      print('Error loading order details: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<bool> createProduct(VendorBeautyRetailProductModel product) async {
    final success = await retailService.createProduct(product);
    if (success) {
      await getProducts(isRefresh: true);
    }
    return success;
  }

  Future<bool> updateProduct(int productId, VendorBeautyRetailProductModel product) async {
    final success = await retailService.updateProduct(productId, product);
    if (success) {
      await getProducts(isRefresh: true);
    }
    return success;
  }

  Future<bool> deleteProduct(int productId) async {
    final success = await retailService.deleteProduct(productId);
    if (success) {
      await getProducts(isRefresh: true);
    }
    return success;
  }
}
