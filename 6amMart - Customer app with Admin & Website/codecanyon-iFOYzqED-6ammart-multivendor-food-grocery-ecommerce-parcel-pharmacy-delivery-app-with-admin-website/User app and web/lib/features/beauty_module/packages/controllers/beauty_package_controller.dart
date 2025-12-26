import 'package:get/get.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';
import '../domain/services/beauty_package_service_interface.dart';
import '../domain/models/beauty_package_model.dart';
import 'package:sixam_mart/util/app_constants.dart';

class BeautyPackageController extends GetxController implements GetxService {
  final BeautyPackageServiceInterface packageService;
  
  BeautyPackageController({required this.packageService});
  
  // Observable states
  bool _isLoading = false;
  bool get isLoading => _isLoading;
  
  List<BeautyPackageModel>? _packageList;
  List<BeautyPackageModel>? get packageList => _packageList;
  
  List<BeautyPackageModel>? _popularPackages;
  List<BeautyPackageModel>? get popularPackages => _popularPackages;
  
  List<BeautyPackageModel>? _purchasedPackages;
  List<BeautyPackageModel>? get purchasedPackages => _purchasedPackages;
  
  BeautyPackageModel? _selectedPackage;
  BeautyPackageModel? get selectedPackage => _selectedPackage;
  
  String _selectedFilter = 'all';
  String get selectedFilter => _selectedFilter;
  
  String _sortBy = 'default';
  String get sortBy => _sortBy;
  
  int _offset = 1;
  int get offset => _offset;
  
  bool _hasMore = true;
  bool get hasMore => _hasMore;
  
  // Package usage tracking
  Map<int, int> _remainingServices = {};
  Map<int, int> get remainingServices => _remainingServices;
  
  @override
  void onInit() {
    super.onInit();
    getPackages(1);
    getPopularPackages();
    getPurchasedPackages();
  }
  
  Future<void> getPackages(int offset, {bool reload = false}) async {
    if (reload) {
      _packageList = null;
      _offset = 1;
      _hasMore = true;
      update();
    }
    
    if (!_hasMore) return;
    
    _isLoading = true;
    update();
    
    try {
      List<BeautyPackageModel> packages = await packageService.getPackages(
        offset: offset,
        limit: 10,
        type: _selectedFilter != 'all' ? _selectedFilter : null,
      );
      
      if (packages.isNotEmpty) {
        if (offset == 1) {
          _packageList = [];
        }
        _packageList!.addAll(packages);
        
        // Apply sorting
        if (_sortBy != 'default') {
          _packageList = packageService.sortPackages(_packageList!, _sortBy);
        }
        
        _offset = offset + 1;
        _hasMore = packages.length == 10;
      } else {
        _hasMore = false;
      }
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error getting packages: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }
  
  Future<void> getPopularPackages() async {
    try {
      _popularPackages = await packageService.getPopularPackages();
      update();
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error getting popular packages: $e');
    }
  }
  
  Future<void> getPurchasedPackages() async {
    try {
      _purchasedPackages = await packageService.getPurchasedPackages();
      
      // Load remaining services for each purchased package
      if (_purchasedPackages != null) {
        for (var package in _purchasedPackages!) {
          if (package.id != null) {
            int remaining = await packageService.getRemainingServices(
              package.id!,
              0, // Get total remaining
            );
            _remainingServices[package.id!] = remaining;
          }
        }
      }
      update();
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error getting purchased packages: $e');
    }
  }
  
  Future<void> getPackageDetails(int packageId) async {
    _isLoading = true;
    update();
    
    try {
      _selectedPackage = await packageService.getPackageDetails(packageId);
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error getting package details: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }
  
  Future<bool> purchasePackage({
    required int packageId,
    required String paymentMethod,
    String? couponCode,
  }) async {
    _isLoading = true;
    update();
    
    try {
      Map<String, dynamic>? result = await packageService.purchasePackage(
        packageId: packageId,
        paymentMethod: paymentMethod,
        couponCode: couponCode,
      );
      
      if (result != null && result['success'] == true) {
        // Refresh purchased packages
        await getPurchasedPackages();
        Get.snackbar(
          'Success',
          'Package purchased successfully!',
          snackPosition: SnackPosition.BOTTOM,
        );
        return true;
      } else {
        Get.snackbar(
          'Error',
          result?['message'] ?? 'Failed to purchase package',
          snackPosition: SnackPosition.BOTTOM,
        );
        return false;
      }
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error purchasing package: $e');
      Get.snackbar(
        'Error',
        'An error occurred while purchasing the package',
        snackPosition: SnackPosition.BOTTOM,
      );
      return false;
    } finally {
      _isLoading = false;
      update();
    }
  }
  
  Future<bool> usePackageService({
    required int purchaseId,
    required int serviceId,
    required DateTime bookingDate,
    required String bookingTime,
    int? staffId,
  }) async {
    try {
      bool success = await packageService.usePackageService(
        purchaseId: purchaseId,
        serviceId: serviceId,
        bookingDate: bookingDate,
        bookingTime: bookingTime,
        staffId: staffId,
      );
      
      if (success) {
        // Update remaining services
        int remaining = await packageService.getRemainingServices(
          purchaseId,
          serviceId,
        );
        _remainingServices[purchaseId] = remaining;
        update();
        
        Get.snackbar(
          'Success',
          'Service booked using package!',
          snackPosition: SnackPosition.BOTTOM,
        );
      }
      
      return success;
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error using package service: $e');
      Get.snackbar(
        'Error',
        'Failed to use package service',
        snackPosition: SnackPosition.BOTTOM,
      );
      return false;
    }
  }
  
  Future<bool> transferPackage({
    required int purchaseId,
    required String recipientEmail,
    String? message,
  }) async {
    _isLoading = true;
    update();
    
    try {
      bool success = await packageService.transferPackage(
        purchaseId: purchaseId,
        recipientEmail: recipientEmail,
        message: message,
      );
      
      if (success) {
        await getPurchasedPackages();
        Get.snackbar(
          'Success',
          'Package transferred successfully!',
          snackPosition: SnackPosition.BOTTOM,
        );
      } else {
        Get.snackbar(
          'Error',
          'Failed to transfer package',
          snackPosition: SnackPosition.BOTTOM,
        );
      }
      
      return success;
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error transferring package: $e');
      return false;
    } finally {
      _isLoading = false;
      update();
    }
  }
  
  Future<bool> cancelPackage(int purchaseId, String reason) async {
    _isLoading = true;
    update();
    
    try {
      bool success = await packageService.cancelPackagePurchase(
        purchaseId,
        reason,
      );
      
      if (success) {
        await getPurchasedPackages();
        Get.snackbar(
          'Success',
          'Package cancelled successfully!',
          snackPosition: SnackPosition.BOTTOM,
        );
      } else {
        Get.snackbar(
          'Error',
          'Failed to cancel package',
          snackPosition: SnackPosition.BOTTOM,
        );
      }
      
      return success;
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error cancelling package: $e');
      return false;
    } finally {
      _isLoading = false;
      update();
    }
  }
  
  Future<bool> extendPackageValidity({
    required int purchaseId,
    required int days,
  }) async {
    try {
      bool success = await packageService.extendPackageValidity(
        purchaseId: purchaseId,
        days: days,
      );
      
      if (success) {
        await getPurchasedPackages();
        Get.snackbar(
          'Success',
          'Package validity extended by $days days!',
          snackPosition: SnackPosition.BOTTOM,
        );
      }
      
      return success;
    } catch (e) {
      if(AppConstants.enableDebugLogs) print('Error extending package validity: $e');
      return false;
    }
  }
  
  void setFilter(String filter) {
    if (_selectedFilter != filter) {
      _selectedFilter = filter;
      getPackages(1, reload: true);
    }
  }
  
  void setSortBy(String sort) {
    if (_sortBy != sort) {
      _sortBy = sort;
      if (_packageList != null) {
        _packageList = packageService.sortPackages(_packageList!, sort);
        update();
      }
    }
  }
  
  double calculateSavings(BeautyPackageModel package) {
    return packageService.calculatePackageSavings(package);
  }
  
  double getDiscountPercentage(BeautyPackageModel package) {
    return packageService.getPackageDiscountPercentage(package);
  }
  
  bool isPackageActive(BeautyPackageModel package) {
    return packageService.isPackageActive(package);
  }
  
  bool canUseService(BeautyPackageModel package, int serviceId) {
    return packageService.canUsePackageService(package, serviceId);
  }
  
  bool canCancelPackage(BeautyPackageModel package) {
    return packageService.canCancelPackage(package);
  }
  
  bool canTransferPackage(BeautyPackageModel package) {
    return packageService.canTransferPackage(package);
  }
  
  bool isExpiringSoon(BeautyPackageModel package) {
    return packageService.isPackageExpiringSoon(package);
  }
  
  double getPackageProgress(BeautyPackageModel package) {
    return packageService.getPackageProgress(package);
  }
  
  int getTotalServices(BeautyPackageModel package) {
    return packageService.getTotalServicesInPackage(package);
  }
  
  int getUsedServices(BeautyPackageModel package) {
    return packageService.getUsedServicesInPackage(package);
  }
  
  String getStatusText(String status) {
    return packageService.getPackageStatusText(status);
  }
  
  void clearSelectedPackage() {
    _selectedPackage = null;
    update();
  }

  void navigateToPackageDetails(int packageId) {
    Get.toNamed(BeautyRouteHelper.getBeautyPackageDetailsRoute(packageId));
  }
}
