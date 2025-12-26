import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/controllers/beauty_salon_controller.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/widgets/staff_card_widget.dart';
import 'package:sixam_mart/common/widgets/no_data_screen.dart';
import 'package:sixam_mart/util/dimensions.dart';

class SalonStaffTabWidget extends StatelessWidget {
  final int salonId;
  
  const SalonStaffTabWidget({super.key, required this.salonId});

  @override
  Widget build(BuildContext context) {
    return GetBuilder<BeautySalonController>(
      builder: (controller) {
        if (controller.isLoading && controller.staff == null) {
          return const Center(child: CircularProgressIndicator());
        }

        final staff = controller.staff ?? [];
        
        if (staff.isEmpty) {
          return NoDataScreen(text: 'no_staff_available'.tr, showFooter: false);
        }

        return ListView.builder(
          padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
          itemCount: staff.length,
          itemBuilder: (context, index) {
            return StaffCardWidget(staff: staff[index]);
          },
        );
      },
    );
  }
}
