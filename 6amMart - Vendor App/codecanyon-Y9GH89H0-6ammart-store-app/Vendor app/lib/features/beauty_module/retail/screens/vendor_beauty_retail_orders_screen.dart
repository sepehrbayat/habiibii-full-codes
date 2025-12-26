import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart_store/features/beauty_module/retail/controllers/vendor_beauty_retail_controller.dart';
import 'package:sixam_mart_store/helper/route_helper.dart';
import 'package:sixam_mart_store/util/dimensions.dart';
import 'package:sixam_mart_store/util/styles.dart';

class VendorBeautyRetailOrdersScreen extends StatelessWidget {
  const VendorBeautyRetailOrdersScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Retail Orders')),
      body: GetBuilder<VendorBeautyRetailController>(
        initState: (_) => Get.find<VendorBeautyRetailController>().getOrders(),
        builder: (controller) {
          if (controller.isLoading && controller.orders == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (controller.orders == null || controller.orders!.isEmpty) {
            return const Center(child: Text('No orders found'));
          }

          return RefreshIndicator(
            onRefresh: () => controller.getOrders(),
            child: ListView.separated(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              itemCount: controller.orders!.length,
              separatorBuilder: (_, __) => const SizedBox(height: Dimensions.paddingSizeSmall),
              itemBuilder: (context, index) {
                final order = controller.orders![index];
                return ListTile(
                  contentPadding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                  tileColor: Theme.of(context).cardColor,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                  ),
                  title: Text(order.orderNumber ?? 'Order #${order.id ?? ''}', style: robotoMedium),
                  subtitle: Text(order.status ?? '', style: robotoRegular.copyWith(color: Theme.of(context).disabledColor)),
                  trailing: Text(
                    '\\$${order.total?.toStringAsFixed(2) ?? '0.00'}',
                    style: robotoBold.copyWith(color: Theme.of(context).primaryColor),
                  ),
                  onTap: () => Get.toNamed(RouteHelper.getBeautyRetailOrderDetailsRoute(order.id ?? 0)),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
