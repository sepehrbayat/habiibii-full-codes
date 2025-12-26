import 'package:flutter/material.dart';
import 'package:sixam_mart_store/features/beauty_module/badges/domain/models/vendor_beauty_badge_model.dart';
import 'package:sixam_mart_store/util/styles.dart';
import 'package:sixam_mart_store/util/dimensions.dart';

class BadgeCardWidget extends StatelessWidget {
  final VendorBeautyBadgeModel badge;

  const BadgeCardWidget({
    super.key,
    required this.badge,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        border: Border.all(
          color: badge.isAchieved ? Theme.of(context).primaryColor : Theme.of(context).disabledColor,
        ),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 22,
            backgroundColor: badge.isAchieved
                ? Theme.of(context).primaryColor.withValues(alpha: 0.1)
                : Theme.of(context).disabledColor.withValues(alpha: 0.1),
            child: Icon(
              badge.isAchieved ? Icons.verified : Icons.lock_outline,
              color: badge.isAchieved ? Theme.of(context).primaryColor : Theme.of(context).disabledColor,
            ),
          ),
          const SizedBox(width: Dimensions.paddingSizeDefault),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(badge.name ?? 'Badge', style: robotoMedium),
                const SizedBox(height: 4),
                Text(
                  badge.description ?? '',
                  style: robotoRegular.copyWith(color: Theme.of(context).disabledColor),
                ),
              ],
            ),
          ),
          if (badge.isAchieved)
            Icon(Icons.check_circle, color: Theme.of(context).primaryColor),
        ],
      ),
    );
  }
}
