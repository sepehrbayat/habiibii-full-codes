import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart_store/features/beauty_module/subscription/controllers/vendor_beauty_subscription_controller.dart';
import 'package:sixam_mart_store/features/beauty_module/subscription/widgets/vendor_beauty_plan_card_widget.dart';
import 'package:sixam_mart_store/util/dimensions.dart';

class VendorBeautySubscriptionPlansScreen extends StatelessWidget {
  const VendorBeautySubscriptionPlansScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Subscription Plans')),
      body: GetBuilder<VendorBeautySubscriptionController>(
        builder: (controller) {
          if (controller.isLoading && controller.plans == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (controller.plans == null || controller.plans!.isEmpty) {
            return const Center(child: Text('No plans found'));
          }

          return ListView.separated(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            itemCount: controller.plans!.length,
            separatorBuilder: (_, __) => const SizedBox(height: Dimensions.paddingSizeSmall),
            itemBuilder: (context, index) {
              final plan = controller.plans![index];
              return VendorBeautyPlanCardWidget(
                plan: plan,
                onSelect: () => controller.subscribe(plan.id ?? 0),
              );
            },
          );
        },
      ),
    );
  }
}
