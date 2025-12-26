import 'package:get/get.dart';
import '../domain/models/vendor_beauty_dashboard_model.dart';
import '../domain/services/vendor_beauty_dashboard_service_interface.dart';

class VendorBeautyDashboardController extends GetxController implements GetxService {
  final VendorBeautyDashboardServiceInterface dashboardService;

  VendorBeautyDashboardController({required this.dashboardService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  VendorBeautyDashboardModel? _dashboard;
  VendorBeautyDashboardModel? get dashboard => _dashboard;

  @override
  void onInit() {
    super.onInit();
    getDashboard();
  }

  Future<void> getDashboard() async {
    _isLoading = true;
    update();

    try {
      _dashboard = await dashboardService.getDashboard();
    } catch (e) {
      print('Error loading dashboard: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }
}
