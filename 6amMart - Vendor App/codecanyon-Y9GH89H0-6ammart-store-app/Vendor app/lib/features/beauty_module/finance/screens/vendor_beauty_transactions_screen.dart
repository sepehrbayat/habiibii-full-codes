import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart_store/features/beauty_module/finance/controllers/vendor_beauty_finance_controller.dart';
import 'package:sixam_mart_store/features/beauty_module/finance/widgets/vendor_beauty_transaction_card_widget.dart';
import 'package:sixam_mart_store/util/dimensions.dart';

class VendorBeautyTransactionsScreen extends StatelessWidget {
  const VendorBeautyTransactionsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Transactions')),
      body: GetBuilder<VendorBeautyFinanceController>(
        builder: (controller) {
          if (controller.isLoading && controller.transactions == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (controller.filteredTransactions == null || controller.filteredTransactions!.isEmpty) {
            return const Center(child: Text('No transactions found'));
          }

          return Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                child: Wrap(
                  spacing: 8,
                  children: [
                    FilterChip(
                      label: const Text('All'),
                      selected: controller.filterType == 'all',
                      onSelected: (_) => controller.setFilter('all'),
                    ),
                    FilterChip(
                      label: const Text('credit'),
                      selected: controller.filterType == 'credit',
                      onSelected: (_) => controller.setFilter('credit'),
                    ),
                    FilterChip(
                      label: const Text('debit'),
                      selected: controller.filterType == 'debit',
                      onSelected: (_) => controller.setFilter('debit'),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: RefreshIndicator(
                  onRefresh: () => controller.getTransactions(),
                  child: ListView.separated(
                    padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                    itemCount: controller.filteredTransactions!.length,
                    separatorBuilder: (_, __) => const SizedBox(height: Dimensions.paddingSizeSmall),
                    itemBuilder: (context, index) {
                      return VendorBeautyTransactionCardWidget(
                        transaction: controller.filteredTransactions![index],
                      );
                    },
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
