import '../models/beauty_package_model.dart';

abstract class BeautyPackageServiceInterface {
  Future<List<BeautyPackageModel>> getPackages({
    int? salonId,
    String? type,
    int? offset,
    int? limit,
  });
  
  Future<BeautyPackageModel?> getPackageDetails(int packageId);
  
  Future<List<BeautyPackageModel>> getPopularPackages({int? salonId});
  
  Future<List<BeautyPackageModel>> getActivePackages();
  
  Future<List<BeautyPackageModel>> getPurchasedPackages({
    String? status,
    int? offset,
    int? limit,
  });
  
  Future<Map<String, dynamic>?> purchasePackage({
    required int packageId,
    required String paymentMethod,
    String? couponCode,
  });
  
  Future<bool> activatePackage(int purchaseId);
  
  Future<Map<String, dynamic>?> getPackageUsageHistory(int purchaseId);
  
  Future<bool> usePackageService({
    required int purchaseId,
    required int serviceId,
    required DateTime bookingDate,
    required String bookingTime,
    int? staffId,
  });
  
  Future<int> getRemainingServices(int purchaseId, int serviceId);
  
  Future<bool> transferPackage({
    required int purchaseId,
    required String recipientEmail,
    String? message,
  });
  
  Future<bool> cancelPackagePurchase(int purchaseId, String reason);
  
  Future<DateTime?> getPackageExpiryDate(int purchaseId);
  
  Future<bool> extendPackageValidity({
    required int purchaseId,
    required int days,
  });
  
  // Business logic methods
  double calculatePackageSavings(BeautyPackageModel package);
  
  bool isPackageActive(BeautyPackageModel package);
  
  bool canUsePackageService(BeautyPackageModel package, int serviceId);
  
  int getTotalServicesInPackage(BeautyPackageModel package);
  
  int getUsedServicesInPackage(BeautyPackageModel package);
  
  double getPackageProgress(BeautyPackageModel package);
  
  bool isPackageExpiringSoon(BeautyPackageModel package, {int days = 7});
  
  List<BeautyPackageModel> filterPackagesByType(
    List<BeautyPackageModel> packages,
    String type,
  );
  
  List<BeautyPackageModel> sortPackages(
    List<BeautyPackageModel> packages,
    String sortBy,
  );
  
  String getPackageStatusText(String status);
  
  bool canCancelPackage(BeautyPackageModel package);
  
  bool canTransferPackage(BeautyPackageModel package);
  
  double getPackageDiscountPercentage(BeautyPackageModel package);
}
