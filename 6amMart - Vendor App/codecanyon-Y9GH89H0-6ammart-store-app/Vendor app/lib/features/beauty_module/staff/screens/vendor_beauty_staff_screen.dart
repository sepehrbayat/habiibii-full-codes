import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/vendor_beauty_staff_controller.dart';
import '../widgets/staff_list_tile_widget.dart';
import 'vendor_beauty_staff_form_screen.dart';

class VendorBeautyStaffScreen extends StatelessWidget {
  const VendorBeautyStaffScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Beauty Staff')),
      body: GetBuilder<VendorBeautyStaffController>(
        builder: (controller) {
          return RefreshIndicator(
            onRefresh: controller.getStaffList,
            child: controller.isLoading && controller.staffList == null
                ? const Center(child: CircularProgressIndicator())
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: controller.staffList?.length ?? 0,
                    itemBuilder: (context, index) {
                      final staff = controller.staffList![index];
                      return StaffListTileWidget(
                        staff: staff,
                        onTap: () {
                          Get.to(() => VendorBeautyStaffFormScreen(staff: staff));
                        },
                      );
                    },
                  ),
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Get.to(() => const VendorBeautyStaffFormScreen()),
        child: const Icon(Icons.add),
      ),
    );
  }
}
