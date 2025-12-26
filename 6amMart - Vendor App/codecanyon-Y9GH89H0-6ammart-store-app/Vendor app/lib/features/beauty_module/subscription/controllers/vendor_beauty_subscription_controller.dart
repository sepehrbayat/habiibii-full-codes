import 'package:get/get.dart';
import '../domain/models/vendor_beauty_subscription_model.dart';
import '../domain/models/vendor_beauty_subscription_plan_model.dart';
import '../domain/services/vendor_beauty_subscription_service_interface.dart';

class VendorBeautySubscriptionController extends GetxController implements GetxService {
  final VendorBeautySubscriptionServiceInterface subscriptionService;

  VendorBeautySubscriptionController({required this.subscriptionService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<VendorBeautySubscriptionPlanModel>? _plans;
  List<VendorBeautySubscriptionPlanModel>? get plans => _plans;

  VendorBeautySubscriptionModel? _currentSubscription;
  VendorBeautySubscriptionModel? get currentSubscription => _currentSubscription;
  List<VendorBeautySubscriptionModel>? _history;
  List<VendorBeautySubscriptionModel>? get history => _history;

  @override
  void onInit() {
    super.onInit();
    getPlans();
    getCurrentSubscription();
    getHistory();
  }

  Future<void> getPlans() async {
    _isLoading = true;
    update();

    try {
      _plans = await subscriptionService.getPlans();
    } catch (e) {
      print('Error loading plans: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<void> getCurrentSubscription() async {
    _isLoading = true;
    update();

    try {
      _currentSubscription = await subscriptionService.getCurrentSubscription();
    } catch (e) {
      print('Error loading subscription: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<bool> subscribe(int planId) async {
    final success = await subscriptionService.subscribe(planId);
    if (success) {
      await getCurrentSubscription();
    }
    return success;
  }

  Future<bool> cancelSubscription() async {
    final success = await subscriptionService.cancelSubscription();
    if (success) {
      await getCurrentSubscription();
    }
    return success;
  }

  Future<void> getHistory() async {
    _isLoading = true;
    update();

    try {
      _history = await subscriptionService.getHistory();
    } catch (e) {
      print('Error loading subscription history: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }
}
