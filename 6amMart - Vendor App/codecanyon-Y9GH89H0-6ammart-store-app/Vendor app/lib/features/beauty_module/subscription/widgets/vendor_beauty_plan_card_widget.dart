import 'package:flutter/material.dart';
import 'package:sixam_mart_store/features/beauty_module/subscription/domain/models/vendor_beauty_subscription_plan_model.dart';
import 'package:sixam_mart_store/util/dimensions.dart';
import 'package:sixam_mart_store/util/styles.dart';

class VendorBeautyPlanCardWidget extends StatelessWidget {
  final VendorBeautySubscriptionPlanModel plan;
  final VoidCallback? onSelect;

  const VendorBeautyPlanCardWidget({
    super.key,
    required this.plan,
    this.onSelect,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(plan.name ?? 'Plan', style: robotoMedium),
          const SizedBox(height: 4),
          Text('\$${plan.price?.toStringAsFixed(2) ?? '0.00'}', style: robotoBold.copyWith(color: Theme.of(context).primaryColor)),
          if (plan.durationDays != null) ...[
            const SizedBox(height: 4),
            Text('${plan.durationDays} days', style: robotoRegular),
          ],
          const SizedBox(height: 8),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: onSelect,
              child: const Text('Select'),
            ),
          ),
        ],
      ),
    );
  }
}
