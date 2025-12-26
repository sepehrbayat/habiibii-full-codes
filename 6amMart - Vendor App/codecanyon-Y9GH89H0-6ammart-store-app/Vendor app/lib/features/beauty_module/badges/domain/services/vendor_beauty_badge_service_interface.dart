import '../models/vendor_beauty_badge_model.dart';

abstract class VendorBeautyBadgeServiceInterface {
  Future<List<VendorBeautyBadgeModel>?> getBadges();
  Future<Map<String, dynamic>?> getBadgeStatus();
}
