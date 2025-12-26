import 'package:flutter/material.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/models/beauty_staff_model.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:cached_network_image/cached_network_image.dart';

class StaffCardWidget extends StatelessWidget {
  final BeautyStaffModel staff;
  
  const StaffCardWidget({super.key, required this.staff});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
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
      child: Row(
        children: [
          CircleAvatar(
            radius: 30,
            backgroundImage: staff.image != null
                ? CachedNetworkImageProvider(staff.image!)
                : null,
            child: staff.image == null ? const Icon(Icons.person, size: 30) : null,
          ),
          const SizedBox(width: Dimensions.paddingSizeDefault),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  staff.name ?? 'N/A',
                  style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                ),
                const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                if (staff.specialization != null)
                  Text(
                    staff.specialization!,
                    style: robotoRegular.copyWith(
                      fontSize: Dimensions.fontSizeSmall,
                      color: Theme.of(context).disabledColor,
                    ),
                  ),
                if (staff.experienceYears != null) ...[
                  const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                  Text(
                    '${staff.experienceYears} years experience',
                    style: robotoRegular.copyWith(
                      fontSize: Dimensions.fontSizeSmall,
                      color: Theme.of(context).disabledColor,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
