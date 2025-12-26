import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/gift_cards/controllers/beauty_gift_card_controller.dart';
import 'package:sixam_mart/features/beauty_module/gift_cards/widgets/gift_card_card_widget.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';

class BeautyGiftCardsScreen extends StatelessWidget {
  const BeautyGiftCardsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Gift Cards')),
      body: GetBuilder<BeautyGiftCardController>(
        initState: (_) => Get.find<BeautyGiftCardController>().getMyGiftCards(),
        builder: (controller) {
          if (controller.isLoading && controller.giftCards == null) {
            return const Center(child: CircularProgressIndicator());
          }

          return RefreshIndicator(
            onRefresh: controller.getMyGiftCards,
            child: ListView(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              children: [
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton(
                        onPressed: () => _showPurchaseDialog(context),
                        child: const Text('Purchase Gift Card'),
                      ),
                    ),
                    const SizedBox(width: Dimensions.paddingSizeSmall),
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => _showRedeemDialog(context),
                        child: const Text('Redeem Gift Card'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                if (controller.giftCards != null && controller.giftCards!.isNotEmpty) ...[
                  GiftCardCardWidget(giftCard: controller.giftCards!.first),
                  const SizedBox(height: Dimensions.paddingSizeSmall),
                  Align(
                    alignment: Alignment.centerRight,
                    child: TextButton(
                      onPressed: () => Get.toNamed(BeautyRouteHelper.getBeautyGiftCardHistoryRoute()),
                      child: const Text('View history'),
                    ),
                  ),
                ] else
                  const SizedBox.shrink(),
              ],
            ),
          );
        },
      ),
    );
  }

  Future<void> _showPurchaseDialog(BuildContext context) async {
    final amountController = TextEditingController();
    final emailController = TextEditingController();
    final nameController = TextEditingController();
    final messageController = TextEditingController();

    await showDialog<void>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Purchase Gift Card'),
          content: SingleChildScrollView(
            child: Column(
              children: [
                TextField(
                  controller: amountController,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration: const InputDecoration(labelText: 'Amount'),
                ),
                TextField(
                  controller: emailController,
                  decoration: const InputDecoration(labelText: 'Recipient Email'),
                ),
                TextField(
                  controller: nameController,
                  decoration: const InputDecoration(labelText: 'Recipient Name'),
                ),
                TextField(
                  controller: messageController,
                  decoration: const InputDecoration(labelText: 'Message'),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel'),
            ),
            TextButton(
              onPressed: () async {
                final amount = double.tryParse(amountController.text);
                if (amount == null || emailController.text.isEmpty) return;

                await Get.find<BeautyGiftCardController>().purchaseGiftCard(
                  amount: amount,
                  recipientEmail: emailController.text,
                  recipientName: nameController.text,
                  message: messageController.text,
                );
                Navigator.pop(context);
              },
              child: const Text('Submit'),
            ),
          ],
        );
      },
    );
  }

  Future<void> _showRedeemDialog(BuildContext context) async {
    final codeController = TextEditingController();

    await showDialog<void>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Redeem Gift Card'),
          content: TextField(
            controller: codeController,
            decoration: const InputDecoration(labelText: 'Code'),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel'),
            ),
            TextButton(
              onPressed: () async {
                if (codeController.text.isEmpty) return;
                await Get.find<BeautyGiftCardController>().redeemGiftCard(codeController.text);
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
