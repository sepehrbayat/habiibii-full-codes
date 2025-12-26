import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart_store/features/beauty_module/subscription/controllers/vendor_beauty_subscription_controller.dart';
import 'package:sixam_mart_store/helper/route_helper.dart';
import 'package:sixam_mart_store/util/dimensions.dart';

class VendorBeautySubscriptionStatusScreen extends StatelessWidget {
  const VendorBeautySubscriptionStatusScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Subscription Status')),
      body: GetBuilder<VendorBeautySubscriptionController>(
        builder: (controller) {
          if (controller.isLoading && controller.currentSubscription == null) {
            return const Center(child: CircularProgressIndicator());
          }

          final subscription = controller.currentSubscription;
          if (subscription == null) {
            return Center(
              child: ElevatedButton(
                onPressed: () => Get.toNamed(RouteHelper.getBeautySubscriptionPlansRoute()),
                child: const Text('View Plans'),
              ),
            );
          }

          return Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Status: ${subscription.status ?? ''}'),
                const SizedBox(height: 8),
                Text('Start: ${subscription.startDate ?? ''}'),
                const SizedBox(height: 8),
                Text('End: ${subscription.endDate ?? ''}'),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton(
                    onPressed: () => controller.cancelSubscription(),
                    child: const Text('Cancel Subscription'),
                  ),
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                SizedBox(
                  width: double.infinity,
                  child: TextButton(
                    onPressed: () => Get.toNamed(RouteHelper.getBeautySubscriptionHistoryRoute()),
                    child: const Text('View History'),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
