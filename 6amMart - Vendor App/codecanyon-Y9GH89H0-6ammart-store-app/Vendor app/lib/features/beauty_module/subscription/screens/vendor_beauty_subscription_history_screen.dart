import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart_store/features/beauty_module/subscription/controllers/vendor_beauty_subscription_controller.dart';
import 'package:sixam_mart_store/util/dimensions.dart';
import 'package:sixam_mart_store/util/styles.dart';

class VendorBeautySubscriptionHistoryScreen extends StatelessWidget {
  const VendorBeautySubscriptionHistoryScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Subscription History')),
      body: GetBuilder<VendorBeautySubscriptionController>(
        builder: (controller) {
          if (controller.isLoading && controller.history == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (controller.history == null || controller.history!.isEmpty) {
            return const Center(child: Text('No history found'));
          }

          return ListView.separated(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            itemCount: controller.history!.length,
            separatorBuilder: (_, __) => const SizedBox(height: Dimensions.paddingSizeSmall),
            itemBuilder: (context, index) {
              final sub = controller.history![index];
              return Container(
                padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                decoration: BoxDecoration(
                  color: Theme.of(context).cardColor,
                  borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Status: ${sub.status ?? ''}', style: robotoMedium),
                    const SizedBox(height: 4),
                    Text('Start: ${sub.startDate ?? ''}', style: robotoRegular),
                    Text('End: ${sub.endDate ?? ''}', style: robotoRegular),
                  ],
                ),
              );
            },
          );
        },
      ),
    );
  }
}
