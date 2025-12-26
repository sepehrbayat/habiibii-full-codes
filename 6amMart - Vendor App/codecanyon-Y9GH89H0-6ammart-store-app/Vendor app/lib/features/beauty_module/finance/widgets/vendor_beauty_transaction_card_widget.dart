import 'package:flutter/material.dart';
import 'package:sixam_mart_store/features/beauty_module/finance/domain/models/vendor_beauty_transaction_model.dart';
import 'package:sixam_mart_store/util/dimensions.dart';
import 'package:sixam_mart_store/util/styles.dart';

class VendorBeautyTransactionCardWidget extends StatelessWidget {
  final VendorBeautyTransactionModel transaction;

  const VendorBeautyTransactionCardWidget({
    super.key,
    required this.transaction,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(transaction.type ?? 'Transaction', style: robotoMedium),
              const SizedBox(height: 4),
              Text(transaction.createdAt ?? '', style: robotoRegular.copyWith(color: Theme.of(context).disabledColor)),
            ],
          ),
          Text(
            '\$${transaction.amount?.toStringAsFixed(2) ?? '0.00'}',
            style: robotoBold.copyWith(color: Theme.of(context).primaryColor),
          ),
        ],
      ),
    );
  }
}
