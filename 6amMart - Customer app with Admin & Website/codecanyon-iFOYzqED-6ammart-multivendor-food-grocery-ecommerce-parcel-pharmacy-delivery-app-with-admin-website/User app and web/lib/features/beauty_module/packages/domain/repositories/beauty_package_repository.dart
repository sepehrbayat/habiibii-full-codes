import 'package:get/get.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';
import '../models/beauty_package_model.dart';
import 'beauty_package_repository_interface.dart';

class BeautyPackageRepository implements BeautyPackageRepositoryInterface {
  final ApiClient apiClient;

  BeautyPackageRepository({required this.apiClient});

  @override
  Future<List<BeautyPackageModel>> getPackages({
    int? salonId,
    String? type,
    int? offset,
    int? limit,
  }) async {
    try {
      Map<String, dynamic> params = {};
      if (salonId != null) params['salon_id'] = salonId.toString();
      if (type != null) params['type'] = type;
      if (offset != null) params['offset'] = offset.toString();
      if (limit != null) params['limit'] = limit.toString();

      Response response = await apiClient.getData(
        BeautyModuleConstants.beautyPackagesUri,
        query: params,
      );

      if (response.statusCode == 200) {
        List<BeautyPackageModel> packages = [];
        if (response.body['packages'] != null) {
          response.body['packages'].forEach((package) {
            packages.add(BeautyPackageModel.fromJson(package));
          });
        }
        return packages;
      }
      return [];
    } catch (e) {
      print('Error getting packages: $e');
      return [];
    }
  }

  @override
  Future<BeautyPackageModel?> getPackageDetails(int packageId) async {
    try {
      Response response = await apiClient.getData(
        '${BeautyModuleConstants.beautyPackageDetailsUri}/$packageId',
      );

      if (response.statusCode == 200) {
        return BeautyPackageModel.fromJson(response.body['package']);
      }
      return null;
    } catch (e) {
      print('Error getting package details: $e');
      return null;
    }
  }

  @override
  Future<List<BeautyPackageModel>> getPopularPackages({int? salonId}) async {
    try {
      Map<String, dynamic> params = {};
      if (salonId != null) params['salon_id'] = salonId.toString();

      Response response = await apiClient.getData(
        BeautyModuleConstants.beautyPopularPackagesUri,
        query: params,
      );

      if (response.statusCode == 200) {
        List<BeautyPackageModel> packages = [];
        if (response.body['packages'] != null) {
          response.body['packages'].forEach((package) {
            packages.add(BeautyPackageModel.fromJson(package));
          });
        }
        return packages;
      }
      return [];
    } catch (e) {
      print('Error getting popular packages: $e');
      return [];
    }
  }

  @override
  Future<List<BeautyPackageModel>> getActivePackages() async {
    try {
      Response response = await apiClient.getData(
        BeautyModuleConstants.beautyActivePackagesUri,
      );

      if (response.statusCode == 200) {
        List<BeautyPackageModel> packages = [];
        if (response.body['packages'] != null) {
          response.body['packages'].forEach((package) {
            packages.add(BeautyPackageModel.fromJson(package));
          });
        }
        return packages;
      }
      return [];
    } catch (e) {
      print('Error getting active packages: $e');
      return [];
    }
  }

  @override
  Future<List<BeautyPackageModel>> getPurchasedPackages({
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
        BeautyModuleConstants.beautyPurchasedPackagesUri,
        query: params,
      );

      if (response.statusCode == 200) {
        List<BeautyPackageModel> packages = [];
        if (response.body['packages'] != null) {
          response.body['packages'].forEach((package) {
            packages.add(BeautyPackageModel.fromJson(package));
          });
        }
        return packages;
      }
      return [];
    } catch (e) {
      print('Error getting purchased packages: $e');
      return [];
    }
  }

  @override
  Future<Map<String, dynamic>?> purchasePackage({
    required int packageId,
    required String paymentMethod,
    String? couponCode,
  }) async {
    try {
      Map<String, dynamic> data = {
        'package_id': packageId,
        'payment_method': paymentMethod,
      };
      if (couponCode != null) data['coupon_code'] = couponCode;

      Response response = await apiClient.postData(
        '${BeautyModuleConstants.beautyPurchasePackageUri}/$packageId/purchase',
        data,
      );

      if (response.statusCode == 200) {
        return response.body;
      }
      return null;
    } catch (e) {
      print('Error purchasing package: $e');
      return null;
    }
  }

  @override
  Future<bool> activatePackage(int purchaseId) async {
    try {
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyActivatePackageUri,
        {'purchase_id': purchaseId},
      );

      return response.statusCode == 200;
    } catch (e) {
      print('Error activating package: $e');
      return false;
    }
  }

  @override
  Future<Map<String, dynamic>?> getPackageUsageHistory(int purchaseId) async {
    try {
      Response response = await apiClient.getData(
        '${BeautyModuleConstants.beautyPackageUsageHistoryUri}/$purchaseId/usage-history',
      );

      if (response.statusCode == 200) {
        return response.body;
      }
      return null;
    } catch (e) {
      print('Error getting package usage history: $e');
      return null;
    }
  }

  @override
  Future<bool> usePackageService({
    required int purchaseId,
    required int serviceId,
    required DateTime bookingDate,
    required String bookingTime,
    int? staffId,
  }) async {
    try {
      Map<String, dynamic> data = {
        'purchase_id': purchaseId,
        'service_id': serviceId,
        'booking_date': bookingDate.toIso8601String().split('T')[0],
        'booking_time': bookingTime,
      };
      if (staffId != null) data['staff_id'] = staffId;

      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyUsePackageServiceUri,
        data,
      );

      return response.statusCode == 200;
    } catch (e) {
      print('Error using package service: $e');
      return false;
    }
  }

  @override
  Future<int> getRemainingServices(int purchaseId, int serviceId) async {
    try {
      Response response = await apiClient.getData(
        BeautyModuleConstants.beautyPackageUsageUri,
        query: {
          'purchase_id': purchaseId.toString(),
          'service_id': serviceId.toString(),
        },
      );

      if (response.statusCode == 200) {
        return response.body['remaining'] ?? 0;
      }
      return 0;
    } catch (e) {
      print('Error getting remaining services: $e');
      return 0;
    }
  }

  @override
  Future<bool> transferPackage({
    required int purchaseId,
    required String recipientEmail,
    String? message,
  }) async {
    try {
      Map<String, dynamic> data = {
        'purchase_id': purchaseId,
        'recipient_email': recipientEmail,
      };
      if (message != null) data['message'] = message;

      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyTransferPackageUri,
        data,
      );

      return response.statusCode == 200;
    } catch (e) {
      print('Error transferring package: $e');
      return false;
    }
  }

  @override
  Future<bool> cancelPackagePurchase(int purchaseId, String reason) async {
    try {
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyCancelPackageUri,
        {
          'purchase_id': purchaseId,
          'reason': reason,
        },
      );

      return response.statusCode == 200;
    } catch (e) {
      print('Error canceling package purchase: $e');
      return false;
    }
  }

  @override
  Future<DateTime?> getPackageExpiryDate(int purchaseId) async {
    try {
      Response response = await apiClient.getData(
        '${BeautyModuleConstants.beautyPackageStatusUri}/$purchaseId/status',
      );

      if (response.statusCode == 200) {
        String? expiryDate = response.body['expiry_date'];
        if (expiryDate == null && response.body['package'] != null) {
          expiryDate = response.body['package']['expiry_date'];
        }
        if (expiryDate != null) {
          return DateTime.tryParse(expiryDate);
        }
      }
      return null;
    } catch (e) {
      print('Error getting package expiry date: $e');
      return null;
    }
  }

  @override
  Future<bool> extendPackageValidity({
    required int purchaseId,
    required int days,
  }) async {
    try {
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyExtendPackageUri,
        {
          'purchase_id': purchaseId,
          'days': days,
        },
      );

      return response.statusCode == 200;
    } catch (e) {
      print('Error extending package validity: $e');
      return false;
    }
  }
}
