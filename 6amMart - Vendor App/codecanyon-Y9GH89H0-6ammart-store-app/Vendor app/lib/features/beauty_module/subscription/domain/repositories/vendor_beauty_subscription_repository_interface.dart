import 'package:sixam_mart_store/interface/repository_interface.dart';
import '../models/vendor_beauty_subscription_model.dart';
import '../models/vendor_beauty_subscription_plan_model.dart';

abstract class VendorBeautySubscriptionRepositoryInterface implements RepositoryInterface {
  Future<List<VendorBeautySubscriptionPlanModel>?> getPlans();
  Future<VendorBeautySubscriptionModel?> getCurrentSubscription();
  Future<bool> subscribe(int planId);
  Future<bool> cancelSubscription();
  Future<List<VendorBeautySubscriptionModel>?> getHistory();
}
