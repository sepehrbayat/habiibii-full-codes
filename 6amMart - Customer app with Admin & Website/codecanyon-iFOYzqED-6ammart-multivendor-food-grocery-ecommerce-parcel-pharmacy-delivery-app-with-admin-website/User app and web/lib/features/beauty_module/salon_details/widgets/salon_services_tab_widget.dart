import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/controllers/beauty_salon_controller.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/widgets/service_card_widget.dart';
import 'package:sixam_mart/common/widgets/no_data_screen.dart';
import 'package:sixam_mart/util/dimensions.dart';

class SalonServicesTabWidget extends StatelessWidget {
  final int salonId;
  
  const SalonServicesTabWidget({super.key, required this.salonId});

  @override
  Widget build(BuildContext context) {
    return GetBuilder<BeautySalonController>(
      builder: (controller) {
        if (controller.isLoading && controller.services == null) {
          return const Center(child: CircularProgressIndicator());
        }

        final services = controller.services ?? [];
        
        if (services.isEmpty) {
          return NoDataScreen(text: 'no_services_available'.tr, showFooter: false);
        }

        return ListView.builder(
          padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
          itemCount: services.length,
          itemBuilder: (context, index) {
            return ServiceCardWidget(service: services[index]);
          },
        );
      },
    );
  }
}
