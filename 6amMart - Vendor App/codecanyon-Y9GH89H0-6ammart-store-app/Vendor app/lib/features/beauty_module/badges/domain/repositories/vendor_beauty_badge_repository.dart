import 'package:get/get.dart';
import 'package:sixam_mart_store/api/api_client.dart';
import 'package:sixam_mart_store/features/util/vendor_beauty_module_constants.dart';
import '../models/vendor_beauty_badge_model.dart';
import 'vendor_beauty_badge_repository_interface.dart';

class VendorBeautyBadgeRepository implements VendorBeautyBadgeRepositoryInterface {
  final ApiClient apiClient;

  VendorBeautyBadgeRepository({required this.apiClient});

  @override
  Future<List<VendorBeautyBadgeModel>?> getBadges() async {
    Response response = await apiClient.getData(
      VendorBeautyModuleConstants.vendorBadgesUri,
    );

    if (response.statusCode == 200) {
      final List<VendorBeautyBadgeModel> badges = [];
      final data = response.body['badges'] ?? response.body['data'] ?? response.body;
      if (data is List) {
        for (final item in data) {
          badges.add(VendorBeautyBadgeModel.fromJson(item));
        }
      }
      return badges;
    }

    return null;
  }

  @override
  Future<Map<String, dynamic>?> getBadgeStatus() async {
    Response response = await apiClient.getData(
      VendorBeautyModuleConstants.vendorBadgeStatusUri,
    );

    if (response.statusCode == 200) {
      final body = response.body;
      if (body is Map<String, dynamic>) {
        return body;
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
