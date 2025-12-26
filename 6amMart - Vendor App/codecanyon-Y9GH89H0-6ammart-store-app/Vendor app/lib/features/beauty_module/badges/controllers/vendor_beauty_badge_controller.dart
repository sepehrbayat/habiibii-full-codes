import 'package:get/get.dart';
import '../domain/models/vendor_beauty_badge_model.dart';
import '../domain/services/vendor_beauty_badge_service_interface.dart';

class VendorBeautyBadgeController extends GetxController implements GetxService {
  final VendorBeautyBadgeServiceInterface badgeService;

  VendorBeautyBadgeController({required this.badgeService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<VendorBeautyBadgeModel>? _badges;
  List<VendorBeautyBadgeModel>? get badges => _badges;

  Map<String, dynamic>? _badgeStatus;
  Map<String, dynamic>? get badgeStatus => _badgeStatus;

  @override
  void onInit() {
    super.onInit();
    getBadges();
    getBadgeStatus();
  }

  Future<void> getBadges() async {
    _isLoading = true;
    update();

    try {
      _badges = await badgeService.getBadges();
    } catch (e) {
      print('Error loading badges: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<void> getBadgeStatus() async {
    _isLoading = true;
    update();

    try {
      _badgeStatus = await badgeService.getBadgeStatus();
    } catch (e) {
      print('Error loading badge status: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }
}
