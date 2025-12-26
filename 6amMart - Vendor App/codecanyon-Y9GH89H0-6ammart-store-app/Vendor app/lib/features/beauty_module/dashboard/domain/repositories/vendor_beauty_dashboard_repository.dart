import 'package:get/get.dart';
import 'package:sixam_mart_store/api/api_client.dart';
import 'package:sixam_mart_store/util/beauty_module_constants.dart';
import '../models/vendor_beauty_dashboard_model.dart';
import 'vendor_beauty_dashboard_repository_interface.dart';

class VendorBeautyDashboardRepository implements VendorBeautyDashboardRepositoryInterface {
  final ApiClient apiClient;

  VendorBeautyDashboardRepository({required this.apiClient});

  @override
  Future<VendorBeautyDashboardModel?> getDashboard() async {
    Response response = await apiClient.getData(BeautyModuleConstants.vendorDashboardUri);
    if (response.statusCode == 200) {
      return VendorBeautyDashboardModel.fromJson(response.body);
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
