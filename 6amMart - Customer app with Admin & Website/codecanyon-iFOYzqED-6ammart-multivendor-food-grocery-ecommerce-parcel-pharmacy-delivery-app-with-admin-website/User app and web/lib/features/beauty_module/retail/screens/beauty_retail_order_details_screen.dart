import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:sixam_mart/features/beauty_module/retail/controllers/beauty_retail_controller.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class BeautyRetailOrderDetailsScreen extends StatefulWidget {
  final int orderId;

  const BeautyRetailOrderDetailsScreen({
    super.key,
    required this.orderId,
  });

  @override
  State<BeautyRetailOrderDetailsScreen> createState() => _BeautyRetailOrderDetailsScreenState();
}

class _BeautyRetailOrderDetailsScreenState extends State<BeautyRetailOrderDetailsScreen> {
  @override
  void initState() {
    super.initState();
    Get.find<BeautyRetailController>().getOrderDetails(widget.orderId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('order_details'.tr)),
      body: GetBuilder<BeautyRetailController>(
        builder: (controller) {
          if (controller.isLoading && controller.selectedOrder == null) {
            return const Center(child: CircularProgressIndicator());
          }

          final order = controller.selectedOrder;
          if (order == null) {
            return Center(child: Text('no_order_found'.tr));
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(order.orderNumber ?? 'Order #${order.id ?? ''}', style: robotoBold),
                const SizedBox(height: 4),
                Text(order.status ?? '', style: robotoRegular),
                const SizedBox(height: 16),
                Text('items'.tr, style: robotoMedium),
                const SizedBox(height: 8),
                ...?order.items?.map((item) {
                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                    decoration: BoxDecoration(
                      color: Theme.of(context).cardColor,
                      borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(item.product?.name ?? 'product'.tr, style: robotoMedium),
                              const SizedBox(height: 4),
                              Text('${item.quantity ?? 0} x ${item.displayPrice}', style: robotoRegular),
                            ],
                          ),
                        ),
                        Text(item.displayTotal, style: robotoBold),
                      ],
                    ),
                  );
                }).toList(),
                const SizedBox(height: 12),
                _SummaryRow(label: 'subtotal'.tr, value: order.displaySubtotal),
                _SummaryRow(label: 'discount'.tr, value: '-\$${order.discount?.toStringAsFixed(2) ?? '0.00'}'),
                _SummaryRow(label: 'tax'.tr, value: '\$${order.tax?.toStringAsFixed(2) ?? '0.00'}'),
                _SummaryRow(label: 'delivery_fee'.tr, value: '\$${order.deliveryFee?.toStringAsFixed(2) ?? '0.00'}'),
                const SizedBox(height: 8),
                _SummaryRow(label: 'total'.tr, value: order.displayTotal, isTotal: true),
                const SizedBox(height: 16),
                if (order.canTrack)
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton(
                      onPressed: () => Get.toNamed(
                        BeautyRouteHelper.getBeautyRetailOrderTrackingRoute(order.id ?? 0),
                      ),
                      child: Text('track_order'.tr),
                    ),
                  ),
                if (order.canCancel)
                  SizedBox(
                    width: double.infinity,
                    child: TextButton(
                      onPressed: () => _showCancelDialog(context),
                      child: Text('cancel_order'.tr, style: TextStyle(color: Theme.of(context).colorScheme.error)),
                    ),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }

  Future<void> _showCancelDialog(BuildContext context) async {
    final reasonController = TextEditingController();

    await showDialog<void>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text('cancel_order'.tr),
          content: TextField(
            controller: reasonController,
            decoration: InputDecoration(hintText: 'cancel_reason'.tr),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text('cancel'.tr),
            ),
            TextButton(
              onPressed: () async {
                final reason = reasonController.text.trim();
                if (reason.isEmpty) {
                  showCustomSnackBar('Please provide a reason');
                  return;
                }
                await Get.find<BeautyRetailController>().cancelOrder(widget.orderId, reason);
                Navigator.pop(context);
              },
              child: Text('confirm'.tr),
            ),
          ],
        );
      },
    );
  }
}

class _SummaryRow extends StatelessWidget {
  final String label;
  final String value;
  final bool isTotal;

  const _SummaryRow({
    required this.label,
    required this.value,
    this.isTotal = false,
  });

  @override
  Widget build(BuildContext context) {
    final style = isTotal ? robotoBold : robotoRegular;
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: style),
          Text(value, style: style),
        ],
      ),
    );
  }
}
