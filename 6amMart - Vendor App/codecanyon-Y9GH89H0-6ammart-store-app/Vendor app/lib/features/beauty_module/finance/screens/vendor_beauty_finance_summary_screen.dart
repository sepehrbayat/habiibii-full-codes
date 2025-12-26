import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart_store/features/beauty_module/finance/controllers/vendor_beauty_finance_controller.dart';
import 'package:sixam_mart_store/helper/route_helper.dart';
import 'package:sixam_mart_store/util/dimensions.dart';
import 'package:sixam_mart_store/util/styles.dart';

class VendorBeautyFinanceSummaryScreen extends StatelessWidget {
  const VendorBeautyFinanceSummaryScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final amountController = TextEditingController();
    return Scaffold(
      appBar: AppBar(
        title: const Text('Finance Summary'),
        actions: [
          IconButton(
            onPressed: () => Get.toNamed(RouteHelper.getBeautyFinanceTransactionsRoute()),
            icon: const Icon(Icons.receipt_long),
          ),
        ],
      ),
      body: GetBuilder<VendorBeautyFinanceController>(
        builder: (controller) {
          if (controller.isLoading && controller.summary == null) {
            return const Center(child: CircularProgressIndicator());
          }

          final summary = controller.summary;
          if (summary == null) {
            return const Center(child: Text('No finance data'));
          }

          return Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Column(
              children: [
                _SummaryCard(label: 'Total Earnings', value: summary.totalEarnings ?? 0),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                _SummaryCard(label: 'Pending Payout', value: summary.pendingPayout ?? 0),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                _SummaryCard(label: 'Completed Payout', value: summary.completedPayout ?? 0),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                _SummaryCard(label: 'Total Orders', value: summary.totalOrders?.toDouble() ?? 0),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: controller.isLoading
                        ? null
                        : () async {
                            final amount = double.tryParse(amountController.text.trim());
                            if (amount == null || amount <= 0) {
                              return;
                            }
                            await controller.requestPayout(amount);
                          },
                    child: const Text('Request Payout'),
                  ),
                ),
                TextField(
                  controller: amountController,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration: const InputDecoration(labelText: 'Amount'),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  final String label;
  final double value;

  const _SummaryCard({
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: robotoMedium),
          const SizedBox(height: 4),
          Text('\\$${value.toStringAsFixed(2)}', style: robotoBold.copyWith(color: Theme.of(context).primaryColor)),
        ],
      ),
    );
  }
}
