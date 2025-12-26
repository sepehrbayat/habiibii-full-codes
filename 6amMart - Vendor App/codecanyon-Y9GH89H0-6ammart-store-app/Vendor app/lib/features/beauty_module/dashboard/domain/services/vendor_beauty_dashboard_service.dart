import '../repositories/vendor_beauty_dashboard_repository_interface.dart';
import '../models/vendor_beauty_dashboard_model.dart';
import 'vendor_beauty_dashboard_service_interface.dart';

class VendorBeautyDashboardService implements VendorBeautyDashboardServiceInterface {
  final VendorBeautyDashboardRepositoryInterface dashboardRepository;

  VendorBeautyDashboardService({required this.dashboardRepository});

  @override
  Future<VendorBeautyDashboardModel?> getDashboard() async {
    return await dashboardRepository.getDashboard();
  }
}
