import 'package:sixam_mart_store/interface/repository_interface.dart';
import '../models/vendor_beauty_badge_model.dart';

abstract class VendorBeautyBadgeRepositoryInterface implements RepositoryInterface {
  Future<List<VendorBeautyBadgeModel>?> getBadges();
  Future<Map<String, dynamic>?> getBadgeStatus();
}
