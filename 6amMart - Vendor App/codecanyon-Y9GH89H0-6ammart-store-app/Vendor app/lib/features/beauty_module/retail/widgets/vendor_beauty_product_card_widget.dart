import 'package:flutter/material.dart';
import 'package:sixam_mart_store/features/beauty_module/retail/domain/models/vendor_beauty_retail_product_model.dart';
import 'package:sixam_mart_store/util/dimensions.dart';
import 'package:sixam_mart_store/util/styles.dart';

class VendorBeautyProductCardWidget extends StatelessWidget {
  final VendorBeautyRetailProductModel product;
  final VoidCallback? onEdit;
  final VoidCallback? onDelete;

  const VendorBeautyProductCardWidget({
    super.key,
    required this.product,
    this.onEdit,
    this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
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
        children: [
          CircleAvatar(
            radius: 26,
            backgroundColor: Theme.of(context).primaryColor.withValues(alpha: 0.1),
            child: const Icon(Icons.shopping_bag_outlined),
          ),
          const SizedBox(width: Dimensions.paddingSizeDefault),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(product.name ?? 'Product', style: robotoMedium),
                const SizedBox(height: 4),
                Text(
                  '\$${product.finalPrice.toStringAsFixed(2)}',
                  style: robotoBold.copyWith(color: Theme.of(context).primaryColor),
                ),
                const SizedBox(height: 4),
                Text(
                  'Stock: ${product.stock ?? 0}',
                  style: robotoRegular.copyWith(color: Theme.of(context).disabledColor),
                ),
              ],
            ),
          ),
          IconButton(
            onPressed: onEdit,
            icon: const Icon(Icons.edit_outlined),
          ),
          IconButton(
            onPressed: onDelete,
            icon: const Icon(Icons.delete_outline),
          ),
        ],
      ),
    );
  }
}
