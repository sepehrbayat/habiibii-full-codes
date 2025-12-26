import '../models/beauty_dashboard_summary_model.dart';
import '../repositories/beauty_dashboard_repository_interface.dart';
import 'beauty_dashboard_service_interface.dart';

class BeautyDashboardService implements BeautyDashboardServiceInterface {
  final BeautyDashboardRepositoryInterface dashboardRepository;

  BeautyDashboardService({required this.dashboardRepository});

  @override
  Future<BeautyDashboardSummaryModel?> getDashboardSummary() async {
    return await dashboardRepository.getDashboardSummary();
  }
}
