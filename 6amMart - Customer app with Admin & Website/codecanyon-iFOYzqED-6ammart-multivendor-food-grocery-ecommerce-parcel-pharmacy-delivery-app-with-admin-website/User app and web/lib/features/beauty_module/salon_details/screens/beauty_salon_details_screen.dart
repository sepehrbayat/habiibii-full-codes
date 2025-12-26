import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/controllers/beauty_salon_controller.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/widgets/salon_header_widget.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/widgets/salon_info_card_widget.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/widgets/salon_services_tab_widget.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/widgets/salon_staff_tab_widget.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';

class BeautySalonDetailsScreen extends StatefulWidget {
  final int salonId;
  
  const BeautySalonDetailsScreen({super.key, required this.salonId});

  @override
  State<BeautySalonDetailsScreen> createState() => _BeautySalonDetailsScreenState();
}

class _BeautySalonDetailsScreenState extends State<BeautySalonDetailsScreen> with TickerProviderStateMixin {
  TabController? _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, initialIndex: 0, vsync: this);
    Get.find<BeautySalonController>().getSalonDetails(widget.salonId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(
        title: 'salon_details'.tr,
        backButton: true,
      ),
      body: GetBuilder<BeautySalonController>(
        builder: (controller) {
          if (controller.isLoading && controller.salon == null) {
            return const Center(child: CircularProgressIndicator());
          }

          final salon = controller.salon;
          if (salon == null) {
            return Center(child: Text('salon_not_found'.tr));
          }

          return Column(
            children: [
              // Salon Header
              SalonHeaderWidget(salon: salon),

              // Salon Info Card
              SalonInfoCardWidget(salon: salon),

              // Tab Bar
              Container(
                color: Theme.of(context).cardColor,
                child: TabBar(
                  controller: _tabController,
                  indicatorColor: Theme.of(context).primaryColor,
                  indicatorWeight: 3,
                  labelColor: Theme.of(context).primaryColor,
                  unselectedLabelColor: Theme.of(context).disabledColor,
                  labelStyle: robotoBold.copyWith(
                    fontSize: Dimensions.fontSizeSmall,
                    color: Theme.of(context).primaryColor,
                  ),
                  unselectedLabelStyle: robotoRegular.copyWith(
                    color: Theme.of(context).disabledColor,
                    fontSize: Dimensions.fontSizeSmall,
                  ),
                  tabs: [
                    Tab(text: 'services'.tr),
                    Tab(text: 'staff'.tr),
                  ],
                ),
              ),

              // Tab Bar View
              Expanded(
                child: TabBarView(
                  controller: _tabController,
                  children: [
                    SalonServicesTabWidget(salonId: widget.salonId),
                    SalonStaffTabWidget(salonId: widget.salonId),
                  ],
                ),
              ),
            ],
          );
        },
      ),
      bottomNavigationBar: GetBuilder<BeautySalonController>(
        builder: (controller) {
          if (controller.salon == null) return const SizedBox();
          
          return Container(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            decoration: BoxDecoration(
              color: Theme.of(context).cardColor,
              boxShadow: [BoxShadow(
                color: Colors.grey.withValues(alpha: 0.1),
                spreadRadius: 1,
                blurRadius: 5,
              )],
            ),
            child: CustomButton(
              buttonText: 'book_now'.tr,
              onPressed: () {
                controller.navigateToBooking();
              },
            ),
          );
        },
      ),
    );
  }
}
