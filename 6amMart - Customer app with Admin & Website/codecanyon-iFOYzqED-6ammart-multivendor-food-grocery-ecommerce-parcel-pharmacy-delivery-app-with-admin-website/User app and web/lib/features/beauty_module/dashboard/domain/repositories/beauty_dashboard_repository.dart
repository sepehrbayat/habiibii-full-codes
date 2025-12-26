import 'package:get/get.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';
import '../models/beauty_dashboard_summary_model.dart';
import 'beauty_dashboard_repository_interface.dart';

class BeautyDashboardRepository implements BeautyDashboardRepositoryInterface {
  final ApiClient apiClient;

  BeautyDashboardRepository({required this.apiClient});

  @override
  Future<BeautyDashboardSummaryModel?> getDashboardSummary() async {
    Response response = await apiClient.getData(BeautyModuleConstants.beautyDashboardSummaryUri);
    if (response.statusCode == 200) {
      return BeautyDashboardSummaryModel.fromJson(response.body['data'] ?? response.body);
    }
    return null;
  }
}
