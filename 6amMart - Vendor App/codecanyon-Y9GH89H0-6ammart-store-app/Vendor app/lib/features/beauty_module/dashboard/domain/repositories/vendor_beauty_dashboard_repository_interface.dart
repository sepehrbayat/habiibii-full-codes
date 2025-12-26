import 'package:sixam_mart_store/interface/repository_interface.dart';
import '../models/vendor_beauty_dashboard_model.dart';

abstract class VendorBeautyDashboardRepositoryInterface implements RepositoryInterface {
  Future<VendorBeautyDashboardModel?> getDashboard();
}
