import '../models/vendor_beauty_dashboard_model.dart';

abstract class VendorBeautyDashboardServiceInterface {
  Future<VendorBeautyDashboardModel?> getDashboard();
}
