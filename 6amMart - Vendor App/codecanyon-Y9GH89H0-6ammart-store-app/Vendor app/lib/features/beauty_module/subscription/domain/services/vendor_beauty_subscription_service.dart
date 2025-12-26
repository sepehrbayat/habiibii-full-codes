import '../models/vendor_beauty_subscription_model.dart';
import '../models/vendor_beauty_subscription_plan_model.dart';
import '../repositories/vendor_beauty_subscription_repository_interface.dart';
import 'vendor_beauty_subscription_service_interface.dart';

class VendorBeautySubscriptionService implements VendorBeautySubscriptionServiceInterface {
  final VendorBeautySubscriptionRepositoryInterface subscriptionRepository;

  VendorBeautySubscriptionService({required this.subscriptionRepository});

  @override
  Future<List<VendorBeautySubscriptionPlanModel>?> getPlans() async {
    return await subscriptionRepository.getPlans();
  }

  @override
  Future<VendorBeautySubscriptionModel?> getCurrentSubscription() async {
    return await subscriptionRepository.getCurrentSubscription();
  }

  @override
  Future<bool> subscribe(int planId) async {
    return await subscriptionRepository.subscribe(planId);
  }

  @override
  Future<bool> cancelSubscription() async {
    return await subscriptionRepository.cancelSubscription();
  }

  @override
  Future<List<VendorBeautySubscriptionModel>?> getHistory() async {
    return await subscriptionRepository.getHistory();
  }
}
