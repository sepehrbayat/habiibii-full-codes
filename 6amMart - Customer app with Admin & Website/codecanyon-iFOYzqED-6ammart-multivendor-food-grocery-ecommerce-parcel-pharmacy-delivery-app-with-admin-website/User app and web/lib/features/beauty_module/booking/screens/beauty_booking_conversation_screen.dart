import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/features/beauty_module/booking/controllers/beauty_booking_controller.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class BeautyBookingConversationScreen extends StatefulWidget {
  final int bookingId;
  const BeautyBookingConversationScreen({super.key, required this.bookingId});

  @override
  State<BeautyBookingConversationScreen> createState() => _BeautyBookingConversationScreenState();
}

class _BeautyBookingConversationScreenState extends State<BeautyBookingConversationScreen> {
  final TextEditingController _messageController = TextEditingController();

  @override
  void initState() {
    super.initState();
    Get.find<BeautyBookingController>().getBookingConversation(widget.bookingId);
  }

  @override
  void dispose() {
    _messageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: 'conversation'.tr),
      body: Column(
        children: [
          Expanded(
            child: GetBuilder<BeautyBookingController>(
              builder: (controller) {
                if (controller.isConversationLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                final messages = controller.conversation?.messages ?? [];
                if (messages.isEmpty) {
                  return Center(child: Text('no_messages_found'.tr));
                }

                return ListView.builder(
                  padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                  itemCount: messages.length,
                  itemBuilder: (context, index) {
                    final message = messages[index];
                    final bool isCustomer = message.senderType == 'customer';
                    return Align(
                      alignment: isCustomer ? Alignment.centerRight : Alignment.centerLeft,
                      child: Container(
                        margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
                        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                        constraints: BoxConstraints(
                          maxWidth: MediaQuery.of(context).size.width * 0.75,
                        ),
                        decoration: BoxDecoration(
                          color: isCustomer
                              ? Theme.of(context).primaryColor.withValues(alpha: 0.1)
                              : Theme.of(context).cardColor,
                          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              message.message ?? '',
                              style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeDefault),
                            ),
                            if (message.createdAt != null) ...[
                              const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                              Text(
                                message.createdAt!,
                                style: robotoRegular.copyWith(
                                  fontSize: Dimensions.fontSizeSmall,
                                  color: Theme.of(context).disabledColor,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ),
          Container(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            decoration: BoxDecoration(
              color: Theme.of(context).cardColor,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 6,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _messageController,
                    decoration: InputDecoration(
                      hintText: 'type_message'.tr,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: Dimensions.paddingSizeSmall,
                        vertical: Dimensions.paddingSizeSmall,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: Dimensions.paddingSizeSmall),
                ElevatedButton(
                  onPressed: () async {
                    final text = _messageController.text.trim();
                    if (text.isEmpty) return;
                    final success = await Get.find<BeautyBookingController>()
                        .sendBookingMessage(widget.bookingId, text);
                    if (success) {
                      _messageController.clear();
                    }
                  },
                  child: Text('send'.tr),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
