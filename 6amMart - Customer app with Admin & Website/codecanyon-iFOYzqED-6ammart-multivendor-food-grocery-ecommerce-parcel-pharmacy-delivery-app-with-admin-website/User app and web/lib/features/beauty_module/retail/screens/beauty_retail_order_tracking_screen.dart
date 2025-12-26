import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/retail/controllers/beauty_retail_controller.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class BeautyRetailOrderTrackingScreen extends StatefulWidget {
  final int orderId;

  const BeautyRetailOrderTrackingScreen({
    super.key,
    required this.orderId,
  });

  @override
  State<BeautyRetailOrderTrackingScreen> createState() => _BeautyRetailOrderTrackingScreenState();
}

class _BeautyRetailOrderTrackingScreenState extends State<BeautyRetailOrderTrackingScreen> {
  @override
  void initState() {
    super.initState();
    Get.find<BeautyRetailController>().getOrderDetails(widget.orderId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('track_order'.tr)),
      body: GetBuilder<BeautyRetailController>(
        builder: (controller) {
          if (controller.isLoading && controller.selectedOrder == null) {
            return const Center(child: CircularProgressIndicator());
          }

          final order = controller.selectedOrder;
          if (order == null) {
            return Center(child: Text('no_order_found'.tr));
          }

          final steps = _buildSteps(order.status ?? 'pending');

          return ListView.separated(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            itemCount: steps.length,
            separatorBuilder: (_, __) => const SizedBox(height: 12),
            itemBuilder: (context, index) {
              final step = steps[index];
              return Row(
                children: [
                  Icon(
                    step.isComplete ? Icons.check_circle : Icons.radio_button_unchecked,
                    color: step.isComplete ? Theme.of(context).primaryColor : Theme.of(context).disabledColor,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(step.label, style: step.isComplete ? robotoMedium : robotoRegular),
                  ),
                ],
              );
            },
          );
        },
      ),
    );
  }

  List<_TrackingStep> _buildSteps(String status) {
    final normalized = status.toLowerCase();
    final stepLabels = [
      'order_placed'.tr,
      'confirmed'.tr,
      'processing'.tr,
      'out_for_delivery'.tr,
      'delivered'.tr,
    ];

    int currentIndex = 0;
    switch (normalized) {
      case 'confirmed':
        currentIndex = 1;
        break;
      case 'processing':
        currentIndex = 2;
        break;
      case 'out_for_delivery':
        currentIndex = 3;
        break;
      case 'delivered':
        currentIndex = 4;
        break;
      case 'cancelled':
        currentIndex = -1;
        break;
      default:
        currentIndex = 0;
    }

    if (currentIndex == -1) {
      return [
        _TrackingStep(label: 'order_cancelled'.tr, isComplete: true),
      ];
    }

    return List.generate(stepLabels.length, (index) {
      return _TrackingStep(label: stepLabels[index], isComplete: index <= currentIndex);
    });
  }
}

class _TrackingStep {
  final String label;
  final bool isComplete;

  _TrackingStep({
    required this.label,
    required this.isComplete,
  });
}
