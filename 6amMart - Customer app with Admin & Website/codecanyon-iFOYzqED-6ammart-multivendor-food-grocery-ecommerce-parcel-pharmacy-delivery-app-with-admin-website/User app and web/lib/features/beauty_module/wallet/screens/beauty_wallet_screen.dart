import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/common/widgets/menu_drawer.dart';
import 'package:sixam_mart/common/widgets/no_data_screen.dart';
import 'package:sixam_mart/helper/date_converter.dart';
import 'package:sixam_mart/helper/price_converter.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import '../controllers/beauty_wallet_controller.dart';

class BeautyWalletScreen extends StatefulWidget {
  const BeautyWalletScreen({super.key});

  @override
  State<BeautyWalletScreen> createState() => _BeautyWalletScreenState();
}

class _BeautyWalletScreenState extends State<BeautyWalletScreen> {
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  void _onScroll() {
    final controller = Get.find<BeautyWalletController>();
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      if (!controller.isPaginating && !controller.isLastPage) {
        controller.getTransactions();
      }
    }
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: const CustomAppBar(title: 'Beauty Wallet'),
      endDrawer: const MenuDrawer(),
      endDrawerEnableOpenDragGesture: false,
      body: GetBuilder<BeautyWalletController>(
        builder: (controller) {
          if (controller.isLoading && controller.transactions.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (controller.transactions.isEmpty) {
            return NoDataScreen(text: 'no_data_found'.tr, showFooter: false);
          }

          return RefreshIndicator(
            onRefresh: () => controller.getTransactions(reload: true),
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              itemCount: controller.transactions.length + (controller.isPaginating ? 1 : 0),
              itemBuilder: (context, index) {
                if (index >= controller.transactions.length) {
                  return const Padding(
                    padding: EdgeInsets.all(Dimensions.paddingSizeSmall),
                    child: Center(child: CircularProgressIndicator()),
                  );
                }

                final transaction = controller.transactions[index];
                final bool isCredit = (transaction.credit ?? 0) > 0;
                final double amount = isCredit
                    ? (transaction.credit ?? 0)
                    : (transaction.debit ?? 0);
                final String amountText = isCredit
                    ? '+${PriceConverter.convertPrice(amount)}'
                    : '-${PriceConverter.convertPrice(amount)}';

                final String dateText = transaction.createdAt != null
                    ? DateConverter.convertTodayYesterdayFormat(transaction.createdAt!)
                    : '-';

                return Container(
                  margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
                  padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                  decoration: BoxDecoration(
                    color: Theme.of(context).cardColor,
                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.04),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              transaction.transactionType ?? 'beauty_transaction'.tr,
                              style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeDefault),
                            ),
                          ),
                          Text(
                            amountText,
                            style: robotoBold.copyWith(
                              fontSize: Dimensions.fontSizeDefault,
                              color: isCredit ? Colors.green : Colors.red,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                      Text(
                        dateText,
                        style: robotoRegular.copyWith(
                          fontSize: Dimensions.fontSizeSmall,
                          color: Theme.of(context).disabledColor,
                        ),
                      ),
                      if (transaction.balance != null) ...[
                        const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                        Text(
                          'Balance: ${PriceConverter.convertPrice(transaction.balance ?? 0)}',
                          style: robotoRegular.copyWith(
                            fontSize: Dimensions.fontSizeSmall,
                            color: Theme.of(context).disabledColor,
                          ),
                        ),
                      ],
                    ],
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
