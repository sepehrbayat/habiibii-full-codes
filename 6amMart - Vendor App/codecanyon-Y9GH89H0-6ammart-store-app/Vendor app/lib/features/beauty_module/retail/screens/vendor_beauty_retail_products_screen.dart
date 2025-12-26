import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart_store/features/beauty_module/retail/controllers/vendor_beauty_retail_controller.dart';
import 'package:sixam_mart_store/features/beauty_module/retail/widgets/vendor_beauty_product_card_widget.dart';
import 'package:sixam_mart_store/helper/route_helper.dart';
import 'package:sixam_mart_store/util/dimensions.dart';

class VendorBeautyRetailProductsScreen extends StatelessWidget {
  const VendorBeautyRetailProductsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Retail Products'),
        actions: [
          IconButton(
            onPressed: () => Get.toNamed(RouteHelper.getBeautyRetailProductFormRoute()),
            icon: const Icon(Icons.add),
          ),
        ],
      ),
      body: GetBuilder<VendorBeautyRetailController>(
        builder: (controller) {
          if (controller.isLoading && controller.products == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (controller.products == null || controller.products!.isEmpty) {
            return const Center(child: Text('No products found'));
          }

          return RefreshIndicator(
            onRefresh: () => controller.getProducts(isRefresh: true),
            child: ListView.separated(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              itemCount: controller.products!.length,
              separatorBuilder: (_, __) => const SizedBox(height: Dimensions.paddingSizeSmall),
              itemBuilder: (context, index) {
                final product = controller.products![index];
                return VendorBeautyProductCardWidget(
                  product: product,
                  onEdit: () => Get.toNamed(
                    RouteHelper.getBeautyRetailProductFormRoute(productId: product.id),
                  ),
                  onDelete: () => controller.deleteProduct(product.id ?? 0),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
