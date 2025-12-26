import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart_store/features/beauty_module/retail/controllers/vendor_beauty_retail_controller.dart';
import 'package:sixam_mart_store/util/dimensions.dart';
import 'package:sixam_mart_store/util/styles.dart';

class VendorBeautyRetailOrderDetailsScreen extends StatefulWidget {
  final int orderId;
  const VendorBeautyRetailOrderDetailsScreen({super.key, required this.orderId});

  @override
  State<VendorBeautyRetailOrderDetailsScreen> createState() => _VendorBeautyRetailOrderDetailsScreenState();
}

class _VendorBeautyRetailOrderDetailsScreenState extends State<VendorBeautyRetailOrderDetailsScreen> {
  @override
  void initState() {
    super.initState();
    Get.find<VendorBeautyRetailController>().getOrderDetails(widget.orderId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Order Details')),
      body: GetBuilder<VendorBeautyRetailController>(
        builder: (controller) {
          if (controller.isLoading && controller.selectedOrder == null) {
            return const Center(child: CircularProgressIndicator());
          }

          final order = controller.selectedOrder;
          if (order == null) {
            return const Center(child: Text('Order not found'));
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(order.orderNumber ?? 'Order #${order.id ?? ''}', style: robotoBold),
                const SizedBox(height: 4),
                Text(order.status ?? '', style: robotoRegular),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                _SummaryRow(label: 'Payment Method', value: order.paymentMethod ?? ''),
                _SummaryRow(label: 'Payment Status', value: order.paymentStatus ?? ''),
                _SummaryRow(label: 'Total', value: '\\$${order.total?.toStringAsFixed(2) ?? '0.00'}'),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _SummaryRow extends StatelessWidget {
  final String label;
  final String value;

  const _SummaryRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: robotoMedium),
          Text(value, style: robotoRegular),
        ],
      ),
    );
  }
}
