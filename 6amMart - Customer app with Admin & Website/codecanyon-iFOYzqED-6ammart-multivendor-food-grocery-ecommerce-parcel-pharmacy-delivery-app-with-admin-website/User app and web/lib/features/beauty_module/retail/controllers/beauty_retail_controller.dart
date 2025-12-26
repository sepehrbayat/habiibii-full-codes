import 'package:get/get.dart';
import '../domain/services/beauty_retail_service_interface.dart';
import '../domain/models/beauty_retail_product_model.dart';
import '../domain/models/beauty_retail_order_model.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:sixam_mart/util/app_constants.dart';

class BeautyRetailController extends GetxController implements GetxService {
  final BeautyRetailServiceInterface retailService;
  
  BeautyRetailController({required this.retailService});
  
  // Products
  List<BeautyRetailProductModel>? _retailProducts;
  List<BeautyRetailProductModel>? _popularProducts;
  List<BeautyRetailProductModel>? _recommendedProducts;
  BeautyRetailProductModel? _selectedProduct;
  
  // Orders
  List<BeautyRetailOrderModel>? _retailOrders;
  BeautyRetailOrderModel? _selectedOrder;
  
  // Cart
  List<Map<String, dynamic>> _cartItems = [];
  double _cartTotal = 0.0;
  String? _appliedCouponCode;
  double _discountAmount = 0.0;
  
  // Filters and Sorting
  int? _selectedCategoryId;
  String _searchQuery = '';
  String _sortBy = 'default';
  double _minPrice = 0.0;
  double _maxPrice = 10000.0;
  
  // Loading states
  bool _isLoading = false;
  bool _isLoadingProducts = false;
  bool _isLoadingOrders = false;
  bool _isPlacingOrder = false;
  bool _isAddingToCart = false;
  
  // Pagination
  int _productOffset = 1;
  int _orderOffset = 1;
  bool _hasMoreProducts = true;
  bool _hasMoreOrders = true;
  
  // Getters
  List<BeautyRetailProductModel>? get retailProducts => _retailProducts;
  List<BeautyRetailProductModel>? get popularProducts => _popularProducts;
  List<BeautyRetailProductModel>? get recommendedProducts => _recommendedProducts;
  BeautyRetailProductModel? get selectedProduct => _selectedProduct;
  List<BeautyRetailOrderModel>? get retailOrders => _retailOrders;
  BeautyRetailOrderModel? get selectedOrder => _selectedOrder;
  List<Map<String, dynamic>> get cartItems => _cartItems;
  double get cartTotal => _cartTotal;
  String? get appliedCouponCode => _appliedCouponCode;
  double get discountAmount => _discountAmount;
  int? get selectedCategoryId => _selectedCategoryId;
  String get searchQuery => _searchQuery;
  String get sortBy => _sortBy;
  double get minPrice => _minPrice;
  double get maxPrice => _maxPrice;
  bool get isLoading => _isLoading;
  bool get isLoadingProducts => _isLoadingProducts;
  bool get isLoadingOrders => _isLoadingOrders;
  bool get isPlacingOrder => _isPlacingOrder;
  bool get isAddingToCart => _isAddingToCart;
  int get cartItemCount => _cartItems.length;
  double get finalTotal => _cartTotal - _discountAmount;
  
  @override
  void onInit() {
    super.onInit();
    getRetailProducts();
    getPopularProducts();
    getRecommendedProducts();
  }
  
  // Product Methods
  Future<void> getRetailProducts({bool isRefresh = false}) async {
    if (isRefresh) {
      _productOffset = 1;
      _hasMoreProducts = true;
      _retailProducts = null;
    }
    
    if (!_hasMoreProducts) return;
    
    _isLoadingProducts = true;
    update();
    
    try {
      List<BeautyRetailProductModel> products = await retailService.getRetailProducts(
        salonId: null,
        categoryId: _selectedCategoryId,
        search: _searchQuery.isNotEmpty ? _searchQuery : null,
        offset: _productOffset,
        limit: 10,
      );
      
      if (products.isNotEmpty) {
        if (_retailProducts == null) {
          _retailProducts = [];
        }
        
        // Apply client-side filtering and sorting
        if (_minPrice > 0 || _maxPrice < 10000) {
          products = retailService.filterProductsByPrice(products, _minPrice, _maxPrice);
        }
        
        if (_sortBy != 'default') {
          products = retailService.sortProducts(products, _sortBy);
        }
        
        _retailProducts!.addAll(products);
        _productOffset++;
        
        if (products.length < 10) {
          _hasMoreProducts = false;
        }
      } else {
        _hasMoreProducts = false;
      }
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error getting retail products: $e');
      showCustomSnackBar('Failed to load products');
    }
    
    _isLoadingProducts = false;
    update();
  }
  
  Future<void> getPopularProducts() async {
    try {
      _popularProducts = await retailService.getPopularProducts();
      update();
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error getting popular products: $e');
    }
  }
  
  Future<void> getRecommendedProducts() async {
    try {
      _recommendedProducts = await retailService.getRecommendedProducts();
      update();
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error getting recommended products: $e');
    }
  }
  
  Future<void> getProductDetails(int productId) async {
    _isLoading = true;
    update();
    
    try {
      _selectedProduct = await retailService.getProductDetails(productId);
      
      if (_selectedProduct == null) {
        showCustomSnackBar('Product not found');
      }
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error getting product details: $e');
      showCustomSnackBar('Failed to load product details');
    }
    
    _isLoading = false;
    update();
  }
  
  // Cart Methods
  Future<void> addToCart(int productId, int quantity, {int? salonId}) async {
    _isAddingToCart = true;
    update();
    
    try {
      bool success = await retailService.addToCart(
        productId: productId,
        quantity: quantity,
        salonId: salonId,
      );
      
      if (success) {
        // Add to local cart
        BeautyRetailProductModel? product = await retailService.getProductDetails(productId);
        if (product != null) {
          _cartItems.add({
            'product_id': productId,
            'product': product,
            'quantity': quantity,
            'price': product.price,
            'discount': product.discount,
          });
          _updateCartTotal();
          showCustomSnackBar('Added to cart', isError: false);
        }
      } else {
        showCustomSnackBar('Failed to add to cart');
      }
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error adding to cart: $e');
      showCustomSnackBar('Failed to add to cart');
    }
    
    _isAddingToCart = false;
    update();
  }
  
  Future<void> updateCartQuantity(int index, int quantity) async {
    if (index < 0 || index >= _cartItems.length) return;
    
    try {
      if (quantity <= 0) {
        removeFromCart(index);
        return;
      }
      
      _cartItems[index]['quantity'] = quantity;
      _updateCartTotal();
      update();
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error updating cart quantity: $e');
      showCustomSnackBar('Failed to update quantity');
    }
  }
  
  void removeFromCart(int index) {
    if (index < 0 || index >= _cartItems.length) return;
    
    _cartItems.removeAt(index);
    _updateCartTotal();
    update();
    showCustomSnackBar('Removed from cart', isError: false);
  }
  
  void clearCart() {
    _cartItems.clear();
    _cartTotal = 0.0;
    _appliedCouponCode = null;
    _discountAmount = 0.0;
    update();
  }
  
  void _updateCartTotal() {
    _cartTotal = retailService.calculateCartTotal(_cartItems);
    if (_appliedCouponCode != null) {
      _discountAmount = retailService.calculateDiscountAmount(_cartTotal, _appliedCouponCode);
    }
  }
  
  void applyCouponCode(String couponCode) {
    _appliedCouponCode = couponCode;
    _discountAmount = retailService.calculateDiscountAmount(_cartTotal, couponCode);
    update();
    
    if (_discountAmount > 0) {
      showCustomSnackBar('Coupon applied successfully', isError: false);
    } else {
      showCustomSnackBar('Invalid coupon code');
      _appliedCouponCode = null;
      _discountAmount = 0.0;
    }
  }
  
  void removeCoupon() {
    _appliedCouponCode = null;
    _discountAmount = 0.0;
    update();
  }
  
  // Order Methods
  Future<void> getRetailOrders({bool isRefresh = false, String? status}) async {
    if (isRefresh) {
      _orderOffset = 1;
      _hasMoreOrders = true;
      _retailOrders = null;
    }
    
    if (!_hasMoreOrders) return;
    
    _isLoadingOrders = true;
    update();
    
    try {
      List<BeautyRetailOrderModel> orders = await retailService.getRetailOrders(
        status: status,
        offset: _orderOffset,
        limit: 10,
      );
      
      if (orders.isNotEmpty) {
        if (_retailOrders == null) {
          _retailOrders = [];
        }
        _retailOrders!.addAll(orders);
        _orderOffset++;
        
        if (orders.length < 10) {
          _hasMoreOrders = false;
        }
      } else {
        _hasMoreOrders = false;
      }
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error getting retail orders: $e');
      showCustomSnackBar('Failed to load orders');
    }
    
    _isLoadingOrders = false;
    update();
  }
  
  Future<void> getOrderDetails(int orderId) async {
    _isLoading = true;
    update();
    
    try {
      _selectedOrder = await retailService.getOrderDetails(orderId);
      
      if (_selectedOrder == null) {
        showCustomSnackBar('Order not found');
      }
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error getting order details: $e');
      showCustomSnackBar('Failed to load order details');
    }
    
    _isLoading = false;
    update();
  }
  
  Future<void> placeOrder({
    required String paymentMethod,
    required Map<String, dynamic> deliveryAddress,
  }) async {
    if (_cartItems.isEmpty) {
      showCustomSnackBar('Cart is empty');
      return;
    }
    
    _isPlacingOrder = true;
    update();
    
    try {
      BeautyRetailOrderModel? order = await retailService.placeOrder(
        items: _cartItems,
        paymentMethod: paymentMethod,
        deliveryAddress: deliveryAddress,
        couponCode: _appliedCouponCode,
      );
      
      if (order != null) {
        clearCart();
        showCustomSnackBar('Order placed successfully', isError: false);
        Get.toNamed(BeautyRouteHelper.getBeautyOrderDetailsRoute(order.id!));
      } else {
        showCustomSnackBar('Failed to place order');
      }
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error placing order: $e');
      showCustomSnackBar('Failed to place order');
    }
    
    _isPlacingOrder = false;
    update();
  }
  
  Future<void> cancelOrder(int orderId, String reason) async {
    _isLoading = true;
    update();
    
    try {
      bool success = await retailService.cancelOrder(orderId, reason);
      
      if (success) {
        // Update local order status
        if (_selectedOrder?.id == orderId) {
          _selectedOrder?.status = 'cancelled';
        }
        
        // Update in orders list
        int index = _retailOrders?.indexWhere((order) => order.id == orderId) ?? -1;
        if (index != -1) {
          _retailOrders![index].status = 'cancelled';
        }
        
        showCustomSnackBar('Order cancelled successfully', isError: false);
      } else {
        showCustomSnackBar('Failed to cancel order');
      }
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error cancelling order: $e');
      showCustomSnackBar('Failed to cancel order');
    }
    
    _isLoading = false;
    update();
  }
  
  Future<void> trackOrder(int orderId) async {
    try {
      bool success = await retailService.trackOrder(orderId);
      
      if (success) {
        Get.toNamed(BeautyRouteHelper.getBeautyOrderTrackingRoute(orderId));
      } else {
        showCustomSnackBar('Unable to track order');
      }
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error tracking order: $e');
      showCustomSnackBar('Failed to track order');
    }
  }
  
  // Filter and Sort Methods
  void setCategory(int? categoryId) {
    _selectedCategoryId = categoryId;
    getRetailProducts(isRefresh: true);
  }
  
  void setSearchQuery(String query) {
    _searchQuery = query;
    getRetailProducts(isRefresh: true);
  }
  
  void setSortBy(String sortBy) {
    _sortBy = sortBy;
    if (_retailProducts != null) {
      _retailProducts = retailService.sortProducts(_retailProducts!, sortBy);
      update();
    }
  }
  
  void setPriceRange(double min, double max) {
    _minPrice = min;
    _maxPrice = max;
    if (_retailProducts != null) {
      getRetailProducts(isRefresh: true);
    }
  }
  
  void clearFilters() {
    _selectedCategoryId = null;
    _searchQuery = '';
    _sortBy = 'default';
    _minPrice = 0.0;
    _maxPrice = 10000.0;
    getRetailProducts(isRefresh: true);
  }
  
  // Helper Methods
  String getOrderStatusText(String status) {
    return retailService.getOrderStatusText(status);
  }
  
  bool canCancelOrder(BeautyRetailOrderModel order) {
    return retailService.canCancelOrder(order);
  }
  
  bool isProductInStock(BeautyRetailProductModel product) {
    return retailService.isProductInStock(product);
  }
}
