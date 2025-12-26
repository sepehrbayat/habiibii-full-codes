import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/home/controllers/beauty_home_controller.dart';
import 'package:sixam_mart/features/beauty_module/home/widgets/salon_card_widget.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/common/widgets/no_data_screen.dart';
import 'package:sixam_mart/util/dimensions.dart';

class BeautyHomeScreen extends StatefulWidget {
  const BeautyHomeScreen({super.key});

  @override
  State<BeautyHomeScreen> createState() => _BeautyHomeScreenState();
}

class _BeautyHomeScreenState extends State<BeautyHomeScreen> {
  @override
  void initState() {
    super.initState();
    Get.find<BeautyHomeController>().getPopularSalons(reload: true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(title: 'beauty_salons'.tr),
      body: GetBuilder<BeautyHomeController>(
        builder: (controller) {
          if (controller.isLoading && controller.popularSalons == null) {
            return const Center(child: CircularProgressIndicator());
          }

          final salons = controller.popularSalons ?? [];
          
          if (salons.isEmpty) {
            return NoDataScreen(text: 'no_salons_available'.tr, showFooter: false);
          }

          return RefreshIndicator(
            onRefresh: () async {
              await controller.getPopularSalons(reload: true);
            },
            child: ListView.builder(
              padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
              itemCount: salons.length,
              itemBuilder: (context, index) {
                return SalonCardWidget(salon: salons[index]);
              },
            ),
          );
        },
      ),
    );
  }
}
