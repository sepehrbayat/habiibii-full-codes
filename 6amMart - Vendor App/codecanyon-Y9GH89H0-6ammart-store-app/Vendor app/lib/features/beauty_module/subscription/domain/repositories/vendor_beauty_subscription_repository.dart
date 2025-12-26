import 'package:get/get.dart';
import 'package:sixam_mart_store/api/api_client.dart';
import 'package:sixam_mart_store/features/util/vendor_beauty_module_constants.dart';
import '../models/vendor_beauty_subscription_model.dart';
import '../models/vendor_beauty_subscription_plan_model.dart';
import 'vendor_beauty_subscription_repository_interface.dart';

class VendorBeautySubscriptionRepository implements VendorBeautySubscriptionRepositoryInterface {
  final ApiClient apiClient;

  VendorBeautySubscriptionRepository({required this.apiClient});

  @override
  Future<List<VendorBeautySubscriptionPlanModel>?> getPlans() async {
    Response response = await apiClient.getData(
      VendorBeautyModuleConstants.vendorSubscriptionPlansUri,
    );
    if (response.statusCode == 200) {
      final List<VendorBeautySubscriptionPlanModel> plans = [];
      final data = response.body['plans'] ?? response.body['data'] ?? response.body;
      if (data is List) {
        for (final item in data) {
          plans.add(VendorBeautySubscriptionPlanModel.fromJson(item));
        }
      }
      return plans;
    }
    return null;
  }

  @override
  Future<VendorBeautySubscriptionModel?> getCurrentSubscription() async {
    Response response = await apiClient.getData(
      VendorBeautyModuleConstants.vendorSubscriptionCurrentUri,
    );
    if (response.statusCode == 200) {
      final data = response.body['subscription'] ?? response.body['data'] ?? response.body;
      if (data is Map<String, dynamic>) {
        return VendorBeautySubscriptionModel.fromJson(data);
      }
    }
    return null;
  }

  @override
  Future<bool> subscribe(int planId) async {
    Response response = await apiClient.postData(
      VendorBeautyModuleConstants.vendorSubscribeUri,
      {'plan_id': planId},
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> cancelSubscription() async {
    Response response = await apiClient.postData(
      VendorBeautyModuleConstants.vendorCancelSubscriptionUri,
      {},
    );
    return response.statusCode == 200;
  }

  @override
  Future<List<VendorBeautySubscriptionModel>?> getHistory() async {
    Response response = await apiClient.getData(
      VendorBeautyModuleConstants.vendorSubscriptionHistoryUri,
    );
    if (response.statusCode == 200) {
      final List<VendorBeautySubscriptionModel> history = [];
      final data = response.body['history'] ?? response.body['data'] ?? response.body;
      if (data is List) {
        for (final item in data) {
          history.add(VendorBeautySubscriptionModel.fromJson(item));
        }
      }
      return history;
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
