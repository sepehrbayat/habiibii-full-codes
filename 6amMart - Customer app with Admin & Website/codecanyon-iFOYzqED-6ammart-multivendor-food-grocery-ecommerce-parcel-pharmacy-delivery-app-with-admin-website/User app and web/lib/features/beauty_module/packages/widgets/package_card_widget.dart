import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_image.dart';
import '../domain/models/beauty_package_model.dart';
import '../controllers/beauty_package_controller.dart';

class PackageCardWidget extends StatelessWidget {
  final BeautyPackageModel package;
  final VoidCallback onTap;
  final bool showPopularBadge;
  
  const PackageCardWidget({
    Key? key,
    required this.package,
    required this.onTap,
    this.showPopularBadge = false,
  }) : super(key: key);
  
  @override
  Widget build(BuildContext context) {
    final packageController = Get.find<BeautyPackageController>();
    double savings = packageController.calculateSavings(package);
    double discountPercentage = packageController.getDiscountPercentage(package);
    
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      child: Container(
        decoration: BoxDecoration(
          color: Theme.of(context).cardColor,
          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          boxShadow: [
            BoxShadow(
              color: Colors.grey.withValues(alpha: 0.1),
              spreadRadius: 1,
              blurRadius: 5,
              offset: const Offset(0, 1),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image with badges
            Expanded(
              flex: 3,
              child: Stack(
                children: [
                  // Package Image
                  ClipRRect(
                    borderRadius: const BorderRadius.only(
                      topLeft: Radius.circular(Dimensions.radiusDefault),
                      topRight: Radius.circular(Dimensions.radiusDefault),
                    ),
                    child: CustomImage(
                      image: package.image ?? '',
                      height: double.infinity,
                      width: double.infinity,
                      fit: BoxFit.cover,
                    ),
                  ),
                  
                  // Discount Badge
                  if (discountPercentage > 0)
                    Positioned(
                      top: Dimensions.paddingSizeSmall,
                      left: Dimensions.paddingSizeSmall,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: Dimensions.paddingSizeSmall,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.red,
                          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                        ),
                        child: Text(
                          '${discountPercentage.toStringAsFixed(0)}% OFF',
                          style: robotoMedium.copyWith(
                            color: Colors.white,
                            fontSize: Dimensions.fontSizeExtraSmall,
                          ),
                        ),
                      ),
                    ),
                  
                  // Popular Badge
                  if (showPopularBadge)
                    Positioned(
                      top: Dimensions.paddingSizeSmall,
                      right: Dimensions.paddingSizeSmall,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: Dimensions.paddingSizeSmall,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.orange,
                          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(
                              Icons.star,
                              color: Colors.white,
                              size: 12,
                            ),
                            const SizedBox(width: 2),
                            Text(
                              'popular'.tr,
                              style: robotoMedium.copyWith(
                                color: Colors.white,
                                fontSize: Dimensions.fontSizeExtraSmall,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  
                  // Package Type Badge
                  Positioned(
                    bottom: Dimensions.paddingSizeSmall,
                    left: Dimensions.paddingSizeSmall,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: Dimensions.paddingSizeSmall,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: _getTypeColor(package.type ?? ''),
                        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                      ),
                      child: Text(
                        package.type?.toUpperCase() ?? '',
                        style: robotoMedium.copyWith(
                          color: Colors.white,
                          fontSize: Dimensions.fontSizeExtraSmall,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            
            // Package Details
            Expanded(
              flex: 2,
              child: Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    // Name
                    Text(
                      package.name ?? '',
                      style: robotoMedium.copyWith(
                        fontSize: Dimensions.fontSizeDefault,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    
                    // Services Count
                    if (package.services != null && package.services!.isNotEmpty)
                      Row(
                        children: [
                          Icon(
                            Icons.spa,
                            size: 14,
                            color: Theme.of(context).primaryColor,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            '${package.services!.length} services',
                            style: robotoRegular.copyWith(
                              fontSize: Dimensions.fontSizeExtraSmall,
                              color: Theme.of(context).disabledColor,
                            ),
                          ),
                        ],
                      ),
                    
                    // Price and Savings
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            // Current Price
                            Text(
                              '\$${package.price?.toStringAsFixed(2)}',
                              style: robotoBold.copyWith(
                                fontSize: Dimensions.fontSizeLarge,
                                color: Theme.of(context).primaryColor,
                              ),
                            ),
                            const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                            
                            // Original Price
                            if (package.originalPrice != null && 
                                package.originalPrice! > package.price!)
                              Text(
                                '\$${package.originalPrice?.toStringAsFixed(2)}',
                                style: robotoRegular.copyWith(
                                  fontSize: Dimensions.fontSizeSmall,
                                  decoration: TextDecoration.lineThrough,
                                  color: Theme.of(context).disabledColor,
                                ),
                              ),
                          ],
                        ),
                        
                        // Savings
                        if (savings > 0)
                          Text(
                            'Save \$${savings.toStringAsFixed(2)}',
                            style: robotoMedium.copyWith(
                              fontSize: Dimensions.fontSizeExtraSmall,
                              color: Colors.green,
                            ),
                          ),
                      ],
                    ),
                    
                    // Validity
                    Row(
                      children: [
                        Icon(
                          Icons.calendar_today,
                          size: 12,
                          color: Theme.of(context).disabledColor,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          '${package.validityDays ?? 0} days',
                          style: robotoRegular.copyWith(
                            fontSize: Dimensions.fontSizeExtraSmall,
                            color: Theme.of(context).disabledColor,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
  
  Color _getTypeColor(String type) {
    switch (type.toLowerCase()) {
      case 'basic':
        return Colors.blue;
      case 'premium':
        return Colors.purple;
      case 'vip':
        return Colors.amber;
      case 'gold':
        return Colors.orange;
      case 'platinum':
        return Colors.grey;
      default:
        return Colors.teal;
    }
  }
}
