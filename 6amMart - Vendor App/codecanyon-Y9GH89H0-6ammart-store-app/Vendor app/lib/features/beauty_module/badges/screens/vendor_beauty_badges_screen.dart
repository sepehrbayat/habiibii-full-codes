import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart_store/features/beauty_module/badges/controllers/vendor_beauty_badge_controller.dart';
import 'package:sixam_mart_store/features/beauty_module/badges/widgets/badge_card_widget.dart';
import 'package:sixam_mart_store/util/dimensions.dart';

class VendorBeautyBadgesScreen extends StatelessWidget {
  const VendorBeautyBadgesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Badges')),
      body: GetBuilder<VendorBeautyBadgeController>(
        builder: (controller) {
          if (controller.isLoading && controller.badges == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (controller.badges == null || controller.badges!.isEmpty) {
            return const Center(child: Text('No badges found'));
          }

          return RefreshIndicator(
            onRefresh: () async {
              await controller.getBadges();
              await controller.getBadgeStatus();
            },
            child: ListView.separated(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              itemCount: controller.badges!.length,
              separatorBuilder: (_, __) => const SizedBox(height: Dimensions.paddingSizeSmall),
              itemBuilder: (context, index) {
                return BadgeCardWidget(badge: controller.badges![index]);
              },
            ),
          );
        },
      ),
    );
  }
}
