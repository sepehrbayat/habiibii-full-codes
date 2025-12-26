import '../models/vendor_beauty_subscription_model.dart';
import '../models/vendor_beauty_subscription_plan_model.dart';

abstract class VendorBeautySubscriptionServiceInterface {
  Future<List<VendorBeautySubscriptionPlanModel>?> getPlans();
  Future<VendorBeautySubscriptionModel?> getCurrentSubscription();
  Future<bool> subscribe(int planId);
  Future<bool> cancelSubscription();
  Future<List<VendorBeautySubscriptionModel>?> getHistory();
}
