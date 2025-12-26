import 'beauty_package_service_interface.dart';
import '../repositories/beauty_package_repository_interface.dart';
import '../models/beauty_package_model.dart';

class BeautyPackageService implements BeautyPackageServiceInterface {
  final BeautyPackageRepositoryInterface packageRepository;
  
  BeautyPackageService({required this.packageRepository});
  
  @override
  Future<List<BeautyPackageModel>> getPackages({
    int? salonId,
    String? type,
    int? offset,
    int? limit,
  }) async {
    return await packageRepository.getPackages(
      salonId: salonId,
      type: type,
      offset: offset,
      limit: limit,
    );
  }
  
  @override
  Future<BeautyPackageModel?> getPackageDetails(int packageId) async {
    return await packageRepository.getPackageDetails(packageId);
  }
  
  @override
  Future<List<BeautyPackageModel>> getPopularPackages({int? salonId}) async {
    return await packageRepository.getPopularPackages(salonId: salonId);
  }
  
  @override
  Future<List<BeautyPackageModel>> getActivePackages() async {
    return await packageRepository.getActivePackages();
  }
  
  @override
  Future<List<BeautyPackageModel>> getPurchasedPackages({
    String? status,
    int? offset,
    int? limit,
  }) async {
    return await packageRepository.getPurchasedPackages(
      status: status,
      offset: offset,
      limit: limit,
    );
  }
  
  @override
  Future<Map<String, dynamic>?> purchasePackage({
    required int packageId,
    required String paymentMethod,
    String? couponCode,
  }) async {
    // Validate package before purchase
    BeautyPackageModel? package = await getPackageDetails(packageId);
    if (package == null || !isPackageActive(package)) {
      return null;
    }
    
    return await packageRepository.purchasePackage(
      packageId: packageId,
      paymentMethod: paymentMethod,
      couponCode: couponCode,
    );
  }
  
  @override
  Future<bool> activatePackage(int purchaseId) async {
    return await packageRepository.activatePackage(purchaseId);
  }
  
  @override
  Future<Map<String, dynamic>?> getPackageUsageHistory(int purchaseId) async {
    return await packageRepository.getPackageUsageHistory(purchaseId);
  }
  
  @override
  Future<bool> usePackageService({
    required int purchaseId,
    required int serviceId,
    required DateTime bookingDate,
    required String bookingTime,
    int? staffId,
  }) async {
    // Check if service can be used
    int remaining = await getRemainingServices(purchaseId, serviceId);
    if (remaining <= 0) {
      return false;
    }
    
    return await packageRepository.usePackageService(
      purchaseId: purchaseId,
      serviceId: serviceId,
      bookingDate: bookingDate,
      bookingTime: bookingTime,
      staffId: staffId,
    );
  }
  
  @override
  Future<int> getRemainingServices(int purchaseId, int serviceId) async {
    return await packageRepository.getRemainingServices(purchaseId, serviceId);
  }
  
  @override
  Future<bool> transferPackage({
    required int purchaseId,
    required String recipientEmail,
    String? message,
  }) async {
    return await packageRepository.transferPackage(
      purchaseId: purchaseId,
      recipientEmail: recipientEmail,
      message: message,
    );
  }
  
  @override
  Future<bool> cancelPackagePurchase(int purchaseId, String reason) async {
    return await packageRepository.cancelPackagePurchase(purchaseId, reason);
  }
  
  @override
  Future<DateTime?> getPackageExpiryDate(int purchaseId) async {
    return await packageRepository.getPackageExpiryDate(purchaseId);
  }
  
  @override
  Future<bool> extendPackageValidity({
    required int purchaseId,
    required int days,
  }) async {
    return await packageRepository.extendPackageValidity(
      purchaseId: purchaseId,
      days: days,
    );
  }
  
  // Business Logic Methods
  
  @override
  double calculatePackageSavings(BeautyPackageModel package) {
    double totalValue = 0.0;
    double packagePrice = package.price ?? 0.0;
    
    // Calculate total value of all services
    if (package.services != null) {
      for (var service in package.services!) {
        double servicePrice = service['price']?.toDouble() ?? 0.0;
        int quantity = service['quantity'] ?? 1;
        totalValue += servicePrice * quantity;
      }
    }
    
    // Calculate savings
    double savings = totalValue - packagePrice;
    return savings > 0 ? savings : 0.0;
  }
  
  @override
  bool isPackageActive(BeautyPackageModel package) {
    if (package.status?.toLowerCase() != 'active') {
      return false;
    }
    
    // Check if package is within validity period
    if (package.startDate != null && package.endDate != null) {
      DateTime now = DateTime.now();
      return now.isAfter(package.startDate!) && now.isBefore(package.endDate!);
    }
    
    return true;
  }
  
  @override
  bool canUsePackageService(BeautyPackageModel package, int serviceId) {
    if (!isPackageActive(package)) {
      return false;
    }
    
    // Check if service is included in package
    if (package.services != null) {
      for (var service in package.services!) {
        if (service['id'] == serviceId) {
          int remaining = service['remaining'] ?? 0;
          return remaining > 0;
        }
      }
    }
    
    return false;
  }
  
  @override
  int getTotalServicesInPackage(BeautyPackageModel package) {
    int total = 0;
    
    if (package.services != null) {
      for (var service in package.services!) {
        int quantity = service['quantity'] ?? 0;
        total += quantity;
      }
    }
    
    return total;
  }
  
  @override
  int getUsedServicesInPackage(BeautyPackageModel package) {
    int used = 0;
    
    if (package.services != null) {
      for (var service in package.services!) {
        int quantity = service['quantity'] ?? 0;
        int remaining = service['remaining'] ?? 0;
        used += (quantity - remaining);
      }
    }
    
    return used;
  }
  
  @override
  double getPackageProgress(BeautyPackageModel package) {
    int total = getTotalServicesInPackage(package);
    if (total == 0) return 0.0;
    
    int used = getUsedServicesInPackage(package);
    return (used / total) * 100;
  }
  
  @override
  bool isPackageExpiringSoon(BeautyPackageModel package, {int days = 7}) {
    if (package.endDate == null) return false;
    
    DateTime now = DateTime.now();
    DateTime expiryDate = package.endDate!;
    Duration difference = expiryDate.difference(now);
    
    return difference.inDays <= days && difference.inDays >= 0;
  }
  
  @override
  List<BeautyPackageModel> filterPackagesByType(
    List<BeautyPackageModel> packages,
    String type,
  ) {
    return packages.where((package) {
      return package.type?.toLowerCase() == type.toLowerCase();
    }).toList();
  }
  
  @override
  List<BeautyPackageModel> sortPackages(
    List<BeautyPackageModel> packages,
    String sortBy,
  ) {
    List<BeautyPackageModel> sortedPackages = List.from(packages);
    
    switch (sortBy.toLowerCase()) {
      case 'price_low_to_high':
        sortedPackages.sort((a, b) => 
          (a.price ?? 0.0).compareTo(b.price ?? 0.0));
        break;
      case 'price_high_to_low':
        sortedPackages.sort((a, b) => 
          (b.price ?? 0.0).compareTo(a.price ?? 0.0));
        break;
      case 'name':
        sortedPackages.sort((a, b) => 
          (a.name ?? '').compareTo(b.name ?? ''));
        break;
      case 'popularity':
        sortedPackages.sort((a, b) => 
          (b.purchaseCount ?? 0).compareTo(a.purchaseCount ?? 0));
        break;
      case 'savings':
        sortedPackages.sort((a, b) {
          double savingsA = calculatePackageSavings(a);
          double savingsB = calculatePackageSavings(b);
          return savingsB.compareTo(savingsA);
        });
        break;
      case 'validity':
        sortedPackages.sort((a, b) => 
          (b.validityDays ?? 0).compareTo(a.validityDays ?? 0));
        break;
      default:
        // Default sorting by ID
        sortedPackages.sort((a, b) => 
          (a.id ?? 0).compareTo(b.id ?? 0));
    }
    
    return sortedPackages;
  }
  
  @override
  String getPackageStatusText(String status) {
    switch (status.toLowerCase()) {
      case 'active':
        return 'Active';
      case 'expired':
        return 'Expired';
      case 'used':
        return 'Fully Used';
      case 'cancelled':
        return 'Cancelled';
      case 'transferred':
        return 'Transferred';
      case 'pending':
        return 'Pending Activation';
      default:
        return status;
    }
  }
  
  @override
  bool canCancelPackage(BeautyPackageModel package) {
    // Can only cancel if package is active and not used
    if (package.status?.toLowerCase() != 'active') {
      return false;
    }
    
    // Check if any services have been used
    int used = getUsedServicesInPackage(package);
    return used == 0;
  }
  
  @override
  bool canTransferPackage(BeautyPackageModel package) {
    // Can only transfer if package is active and transferable
    if (package.status?.toLowerCase() != 'active') {
      return false;
    }
    
    // Check if package is transferable
    return package.isTransferable ?? false;
  }
  
  @override
  double getPackageDiscountPercentage(BeautyPackageModel package) {
    double totalValue = 0.0;
    double packagePrice = package.price ?? 0.0;
    
    if (packagePrice == 0) return 0.0;
    
    // Calculate total value of all services
    if (package.services != null) {
      for (var service in package.services!) {
        double servicePrice = service['price']?.toDouble() ?? 0.0;
        int quantity = service['quantity'] ?? 1;
        totalValue += servicePrice * quantity;
      }
    }
    
    if (totalValue == 0) return 0.0;
    
    // Calculate discount percentage
    double discount = ((totalValue - packagePrice) / totalValue) * 100;
    return discount > 0 ? discount : 0.0;
  }
}
