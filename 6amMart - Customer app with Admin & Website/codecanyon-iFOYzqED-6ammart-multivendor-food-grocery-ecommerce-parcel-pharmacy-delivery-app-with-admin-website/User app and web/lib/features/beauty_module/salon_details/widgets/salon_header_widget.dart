import 'package:flutter/material.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_salon_model.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:cached_network_image/cached_network_image.dart';

class SalonHeaderWidget extends StatelessWidget {
  final BeautySalonModel salon;
  
  const SalonHeaderWidget({super.key, required this.salon});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 200,
      width: double.infinity,
      decoration: BoxDecoration(
        image: salon.storeCoverPhoto != null
            ? DecorationImage(
                image: CachedNetworkImageProvider(salon.storeCoverPhoto!),
                fit: BoxFit.cover,
              )
            : null,
        color: salon.storeCoverPhoto == null ? Theme.of(context).primaryColor.withValues(alpha: 0.1) : null,
      ),
      child: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              Colors.transparent,
              Colors.black.withValues(alpha: 0.7),
            ],
          ),
        ),
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        alignment: Alignment.bottomLeft,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.end,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              salon.storeName ?? 'N/A',
              style: robotoBold.copyWith(
                fontSize: Dimensions.fontSizeExtraLarge,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeExtraSmall),
            Row(
              children: [
                Icon(Icons.star, color: Colors.amber, size: 16),
                const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                Text(
                  '${salon.avgRating?.toStringAsFixed(1) ?? '0.0'} (${salon.totalReviews ?? 0} reviews)',
                  style: robotoRegular.copyWith(
                    fontSize: Dimensions.fontSizeSmall,
                    color: Colors.white,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
