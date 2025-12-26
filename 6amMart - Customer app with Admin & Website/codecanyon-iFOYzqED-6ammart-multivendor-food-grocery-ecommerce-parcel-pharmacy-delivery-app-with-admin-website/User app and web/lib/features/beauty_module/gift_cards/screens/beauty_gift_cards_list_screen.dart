import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/gift_cards/controllers/beauty_gift_card_controller.dart';
import 'package:sixam_mart/features/beauty_module/gift_cards/widgets/gift_card_card_widget.dart';

class BeautyGiftCardsListScreen extends StatelessWidget {
  const BeautyGiftCardsListScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return GetBuilder<BeautyGiftCardController>(
      initState: (_) => Get.find<BeautyGiftCardController>().getMyGiftCards(),
      builder: (controller) {
        return Scaffold(
          appBar: AppBar(
            title: Text('My Gift Cards'),
          ),
          body: controller.isLoading
              ? Center(child: CircularProgressIndicator())
              : controller.giftCards == null || controller.giftCards!.isEmpty
                  ? Center(child: Text('No gift cards found'))
                  : ListView.builder(
                      padding: EdgeInsets.all(16),
                      itemCount: controller.giftCards!.length,
                      itemBuilder: (context, index) {
                        final giftCard = controller.giftCards![index];
                        return GiftCardCardWidget(giftCard: giftCard);
                      },
                    ),
        );
      },
    );
  }
}
