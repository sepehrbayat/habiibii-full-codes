import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/booking/controllers/beauty_booking_controller.dart';
import 'package:sixam_mart/features/beauty_module/booking/widgets/booking_card_widget.dart';
import 'package:sixam_mart/helper/auth_helper.dart';
import 'package:sixam_mart/helper/responsive_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/common/widgets/menu_drawer.dart';
import 'package:sixam_mart/common/widgets/no_data_screen.dart';

class BeautyBookingListScreen extends StatefulWidget {
  const BeautyBookingListScreen({super.key});

  @override
  State<BeautyBookingListScreen> createState() => _BeautyBookingListScreenState();
}

class _BeautyBookingListScreenState extends State<BeautyBookingListScreen> with TickerProviderStateMixin {
  TabController? _tabController;
  final bool _isLoggedIn = AuthHelper.isLoggedIn();

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, initialIndex: 0, vsync: this);
    
    if (_isLoggedIn) {
      Get.find<BeautyBookingController>().getBookings(reload: true);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(
        title: 'my_bookings'.tr,
        backButton: ResponsiveHelper.isDesktop(context),
      ),
      endDrawer: const MenuDrawer(),
      endDrawerEnableOpenDragGesture: false,
      body: _isLoggedIn ? GetBuilder<BeautyBookingController>(
        builder: (bookingController) {
          return Column(
            children: [
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
                    Tab(text: 'all'.tr),
                    Tab(text: 'upcoming'.tr),
                    Tab(text: 'past'.tr),
                  ],
                ),
              ),

              // Tab Bar View
              Expanded(
                child: TabBarView(
                  controller: _tabController,
                  children: [
                    // All Bookings
                    _buildBookingList(bookingController.bookings, bookingController),
                    // Upcoming Bookings
                    _buildBookingList(bookingController.upcomingBookings, bookingController),
                    // Past Bookings
                    _buildBookingList(bookingController.pastBookings, bookingController),
                  ],
                ),
              ),
            ],
          );
        },
      ) : Center(
        child: Text('please_login_to_view_bookings'.tr),
      ),
    );
  }

  Widget _buildBookingList(List? bookings, BeautyBookingController controller) {
    if (controller.isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (bookings == null || bookings.isEmpty) {
      return NoDataScreen(
        text: 'no_bookings_found'.tr,
        showFooter: false,
      );
    }

    return RefreshIndicator(
      onRefresh: () async {
        await controller.getBookings(reload: true);
      },
      child: ListView.builder(
        padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
        itemCount: bookings.length,
        itemBuilder: (context, index) {
          return BookingCardWidget(booking: bookings[index]);
        },
      ),
    );
  }
}
