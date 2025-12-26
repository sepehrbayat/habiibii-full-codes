import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/loyalty/controllers/beauty_loyalty_controller.dart';
import 'package:sixam_mart/features/beauty_module/loyalty/widgets/points_balance_widget.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';

class BeautyLoyaltyScreen extends StatelessWidget {
  const BeautyLoyaltyScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Loyalty & Rewards')),
      body: GetBuilder<BeautyLoyaltyController>(
        initState: (_) {
          final controller = Get.find<BeautyLoyaltyController>();
          controller.getLoyaltyPoints();
          controller.getLoyaltyCampaigns();
        },
        builder: (controller) {
          if (controller.isLoading && controller.points == null) {
            return const Center(child: CircularProgressIndicator());
          }

          return RefreshIndicator(
            onRefresh: () async {
              await controller.getLoyaltyPoints();
              await controller.getLoyaltyCampaigns();
            },
            child: ListView(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              children: [
                PointsBalanceWidget(points: controller.points ?? 0),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton(
                        onPressed: () => Get.toNamed(BeautyRouteHelper.getBeautyLoyaltyPointsRoute()),
                        child: const Text('View points'),
                      ),
                    ),
                    const SizedBox(width: Dimensions.paddingSizeSmall),
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => _showRedeemDialog(context),
                        child: const Text('Redeem'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                Text('Campaigns', style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                ...?controller.campaigns?.map((campaign) {
                  return Card(
                    child: ListTile(
                      title: Text(campaign['name'] ?? ''),
                      subtitle: Text(campaign['description'] ?? ''),
                    ),
                  );
                }).toList(),
              ],
            ),
          );
        },
      ),
    );
  }

  Future<void> _showRedeemDialog(BuildContext context) async {
    final pointsController = TextEditingController();

    await showDialog<void>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Redeem Points'),
          content: TextField(
            controller: pointsController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(labelText: 'Points'),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel'),
            ),
            TextButton(
              onPressed: () async {
                final points = int.tryParse(pointsController.text);
                if (points == null) return;
                await Get.find<BeautyLoyaltyController>().redeemPoints(
                  points: points,
                  rewardType: 'wallet', // Default reward type
                );
                Navigator.pop(context);
              },
              child: const Text('Redeem'),
            ),
          ],
        );
      },
    );
  }
}
