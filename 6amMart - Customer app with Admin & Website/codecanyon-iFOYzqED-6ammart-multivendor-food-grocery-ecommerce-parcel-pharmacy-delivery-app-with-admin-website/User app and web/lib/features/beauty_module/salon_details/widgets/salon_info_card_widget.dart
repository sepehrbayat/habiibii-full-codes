import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_salon_model.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class SalonInfoCardWidget extends StatelessWidget {
  final BeautySalonModel salon;
  
  const SalonInfoCardWidget({super.key, required this.salon});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        boxShadow: [BoxShadow(
          color: Colors.grey.withValues(alpha: 0.1),
          spreadRadius: 1,
          blurRadius: 5,
        )],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (salon.storeAddress != null) ...[
            _buildInfoRow(
              context,
              icon: Icons.location_on,
              text: salon.storeAddress!,
            ),
            const SizedBox(height: Dimensions.paddingSizeSmall),
          ],
          if (salon.storePhone != null) ...[
            _buildInfoRow(
              context,
              icon: Icons.phone,
              text: salon.storePhone!,
            ),
            const SizedBox(height: Dimensions.paddingSizeSmall),
          ],
          _buildInfoRow(
            context,
            icon: Icons.access_time,
            text: 'open_now'.tr,
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(BuildContext context, {required IconData icon, required String text}) {
    return Row(
      children: [
        Icon(icon, size: 20, color: Theme.of(context).primaryColor),
        const SizedBox(width: Dimensions.paddingSizeSmall),
        Expanded(
          child: Text(
            text,
            style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall),
          ),
        ),
      ],
    );
  }
}
