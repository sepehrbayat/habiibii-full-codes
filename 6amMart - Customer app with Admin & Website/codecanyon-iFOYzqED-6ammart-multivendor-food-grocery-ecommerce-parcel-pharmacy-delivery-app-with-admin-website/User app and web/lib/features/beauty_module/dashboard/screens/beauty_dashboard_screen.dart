import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/common/widgets/menu_drawer.dart';
import 'package:sixam_mart/common/widgets/no_data_screen.dart';
import 'package:sixam_mart/helper/price_converter.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import '../controllers/beauty_dashboard_controller.dart';

class BeautyDashboardScreen extends StatelessWidget {
  const BeautyDashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: const CustomAppBar(title: 'Beauty Dashboard'),
      endDrawer: const MenuDrawer(),
      endDrawerEnableOpenDragGesture: false,
      body: GetBuilder<BeautyDashboardController>(
        builder: (controller) {
          if (controller.isLoading && controller.summary == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (controller.summary == null) {
            return NoDataScreen(text: 'no_data_found'.tr, showFooter: false);
          }

          final summary = controller.summary!;

          return RefreshIndicator(
            onRefresh: controller.loadSummary,
            child: ListView(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              children: [
                _SummaryCard(
                  title: 'Total Bookings',
                  value: '${summary.totalBookings ?? 0}',
                ),
                _SummaryCard(
                  title: 'Upcoming Bookings',
                  value: '${summary.upcomingBookings ?? 0}',
                ),
                _SummaryCard(
                  title: 'Active Packages',
                  value: '${summary.activePackages ?? 0}',
                ),
                _SummaryCard(
                  title: 'Pending Consultations',
                  value: '${summary.pendingConsultations ?? 0}',
                ),
                _SummaryCard(
                  title: 'Total Spent',
                  value: PriceConverter.convertPrice(summary.totalSpent ?? 0),
                ),
                _SummaryCard(
                  title: 'Gift Card Balance',
                  value: PriceConverter.convertPrice(summary.giftCardBalance ?? 0),
                ),
                _SummaryCard(
                  title: 'Loyalty Points',
                  value: '${summary.loyaltyPoints ?? 0}',
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  final String title;
  final String value;

  const _SummaryCard({
    required this.title,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              title,
              style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeDefault),
            ),
          ),
          Text(
            value,
            style: robotoBold.copyWith(
              fontSize: Dimensions.fontSizeDefault,
              color: Theme.of(context).primaryColor,
            ),
          ),
        ],
      ),
    );
  }
}
