import '../models/vendor_beauty_badge_model.dart';
import '../repositories/vendor_beauty_badge_repository_interface.dart';
import 'vendor_beauty_badge_service_interface.dart';

class VendorBeautyBadgeService implements VendorBeautyBadgeServiceInterface {
  final VendorBeautyBadgeRepositoryInterface badgeRepository;

  VendorBeautyBadgeService({required this.badgeRepository});

  @override
  Future<List<VendorBeautyBadgeModel>?> getBadges() async {
    return await badgeRepository.getBadges();
  }

  @override
  Future<Map<String, dynamic>?> getBadgeStatus() async {
    return await badgeRepository.getBadgeStatus();
  }
}
