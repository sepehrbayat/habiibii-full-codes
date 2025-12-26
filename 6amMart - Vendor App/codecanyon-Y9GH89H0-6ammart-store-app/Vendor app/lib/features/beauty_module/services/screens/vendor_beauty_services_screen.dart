import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/vendor_beauty_service_controller.dart';
import '../widgets/service_list_tile_widget.dart';
import 'vendor_beauty_service_form_screen.dart';

class VendorBeautyServicesScreen extends StatelessWidget {
  const VendorBeautyServicesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Beauty Services')),
      body: GetBuilder<VendorBeautyServiceController>(
        builder: (controller) {
          return RefreshIndicator(
            onRefresh: controller.getServiceList,
            child: controller.isLoading && controller.serviceList == null
                ? const Center(child: CircularProgressIndicator())
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: controller.serviceList?.length ?? 0,
                    itemBuilder: (context, index) {
                      final service = controller.serviceList![index];
                      return ServiceListTileWidget(
                        service: service,
                        onTap: () => Get.to(() => VendorBeautyServiceFormScreen(service: service)),
                      );
                    },
                  ),
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Get.to(() => const VendorBeautyServiceFormScreen()),
        child: const Icon(Icons.add),
      ),
    );
  }
}
