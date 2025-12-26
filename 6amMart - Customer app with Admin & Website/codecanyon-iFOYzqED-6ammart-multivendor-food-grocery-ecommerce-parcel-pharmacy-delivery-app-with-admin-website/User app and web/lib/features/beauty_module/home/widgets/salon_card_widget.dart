import 'package:flutter/material.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_salon_model.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';

class SalonCardWidget extends StatelessWidget {
  final BeautySalonModel salon;
  
  const SalonCardWidget({super.key, required this.salon});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        boxShadow: [BoxShadow(
          color: Colors.grey.withValues(alpha: 0.1),
          spreadRadius: 1,
          blurRadius: 5,
        )],
      ),
      child: InkWell(
        onTap: () {
          Get.toNamed(BeautyRouteHelper.getBeautySalonDetailsRoute(salon.id!));
        },
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Salon Image
            ClipRRect(
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(Dimensions.radiusDefault),
              ),
              child: salon.storeCoverPhoto != null
                  ? CachedNetworkImage(
                      imageUrl: salon.storeCoverPhoto!,
                      height: 150,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      placeholder: (context, url) => Container(
                        height: 150,
                        color: Theme.of(context).disabledColor.withValues(alpha: 0.1),
                        child: const Center(child: CircularProgressIndicator()),
                      ),
                      errorWidget: (context, url, error) => Container(
                        height: 150,
                        color: Theme.of(context).disabledColor.withValues(alpha: 0.1),
                        child: const Icon(Icons.store, size: 50),
                      ),
                    )
                  : Container(
                      height: 150,
                      color: Theme.of(context).primaryColor.withValues(alpha: 0.1),
                      child: const Center(
                        child: Icon(Icons.store, size: 50),
                      ),
                    ),
            ),
            
            // Salon Info
            Padding(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    salon.storeName ?? 'N/A',
                    style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                  if (salon.storeAddress != null)
                    Text(
                      salon.storeAddress!,
                      style: robotoRegular.copyWith(
                        fontSize: Dimensions.fontSizeSmall,
                        color: Theme.of(context).disabledColor,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  const SizedBox(height: Dimensions.paddingSizeSmall),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.star, color: Colors.amber, size: 16),
                          const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                          Text(
                            '${salon.avgRating?.toStringAsFixed(1) ?? '0.0'}',
                            style: robotoMedium.copyWith(
                              fontSize: Dimensions.fontSizeSmall,
                            ),
                          ),
                          Text(
                            ' (${salon.totalReviews ?? 0})',
                            style: robotoRegular.copyWith(
                              fontSize: Dimensions.fontSizeSmall,
                              color: Theme.of(context).disabledColor,
                            ),
                          ),
                        ],
                      ),
                      if (salon.distance != null)
                        Row(
                          children: [
                            Icon(
                              Icons.location_on,
                              size: 16,
                              color: Theme.of(context).disabledColor,
                            ),
                            const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                            Text(
                              '${salon.distance!.toStringAsFixed(1)} km',
                              style: robotoRegular.copyWith(
                                fontSize: Dimensions.fontSizeSmall,
                                color: Theme.of(context).disabledColor,
                              ),
                            ),
                          ],
                        ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
