import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/common/widgets/no_data_screen.dart';
import 'package:sixam_mart/features/beauty_module/retail/controllers/beauty_retail_controller.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class BeautyRetailOrdersScreen extends StatefulWidget {
  const BeautyRetailOrdersScreen({super.key});

  @override
  State<BeautyRetailOrdersScreen> createState() => _BeautyRetailOrdersScreenState();
}

class _BeautyRetailOrdersScreenState extends State<BeautyRetailOrdersScreen> {
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    Get.find<BeautyRetailController>().getRetailOrders(isRefresh: true);
    _scrollController.addListener(_scrollListener);
  }

  void _scrollListener() {
    if (_scrollController.position.pixels == _scrollController.position.maxScrollExtent &&
        !Get.find<BeautyRetailController>().isLoadingOrders) {
      Get.find<BeautyRetailController>().getRetailOrders();
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
      appBar: AppBar(title: Text('orders'.tr)),
      body: GetBuilder<BeautyRetailController>(
        builder: (controller) {
          if (controller.retailOrders == null && controller.isLoadingOrders) {
            return const Center(child: CircularProgressIndicator());
          }

          if (controller.retailOrders == null || controller.retailOrders!.isEmpty) {
            return NoDataScreen(text: 'no_order_found'.tr);
          }

          return RefreshIndicator(
            onRefresh: () => controller.getRetailOrders(isRefresh: true),
            child: ListView.separated(
              controller: _scrollController,
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              itemCount: controller.retailOrders!.length,
              separatorBuilder: (_, __) => const SizedBox(height: Dimensions.paddingSizeSmall),
              itemBuilder: (context, index) {
                final order = controller.retailOrders![index];
                return InkWell(
                  onTap: () => Get.toNamed(
                    BeautyRouteHelper.getBeautyRetailOrderDetailsRoute(order.id ?? 0),
                  ),
                  child: Container(
                    padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                    decoration: BoxDecoration(
                      color: Theme.of(context).cardColor,
                      borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.grey.withValues(alpha: 0.08),
                          blurRadius: 6,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              order.orderNumber ?? 'Order #${order.id ?? ''}',
                              style: robotoMedium,
                            ),
                            const SizedBox(height: 4),
                            Text(
                              order.status ?? '',
                              style: robotoRegular.copyWith(color: Theme.of(context).disabledColor),
                            ),
                          ],
                        ),
                        Text(
                          order.displayTotal,
                          style: robotoBold.copyWith(color: Theme.of(context).primaryColor),
                        ),
                      ],
                    ),
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
