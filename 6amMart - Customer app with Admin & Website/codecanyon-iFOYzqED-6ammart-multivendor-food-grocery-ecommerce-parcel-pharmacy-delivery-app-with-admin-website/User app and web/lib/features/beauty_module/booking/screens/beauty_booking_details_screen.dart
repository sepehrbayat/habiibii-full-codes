import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/features/beauty_module/booking/controllers/beauty_booking_controller.dart';
import 'package:sixam_mart/features/beauty_module/booking/widgets/booking_status_badge_widget.dart';
import 'package:sixam_mart/features/beauty_module/booking/widgets/cancel_booking_dialog.dart';
import 'package:sixam_mart/features/beauty_module/booking/widgets/reschedule_booking_dialog.dart';
import 'package:sixam_mart/helper/price_converter.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class BeautyBookingDetailsScreen extends StatefulWidget {
  final int bookingId;
  const BeautyBookingDetailsScreen({super.key, required this.bookingId});

  @override
  State<BeautyBookingDetailsScreen> createState() => _BeautyBookingDetailsScreenState();
}

class _BeautyBookingDetailsScreenState extends State<BeautyBookingDetailsScreen> {
  @override
  void initState() {
    super.initState();
    Get.find<BeautyBookingController>().getBookingDetails(widget.bookingId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(
        title: 'booking_details'.tr,
        backButton: true,
      ),
      body: GetBuilder<BeautyBookingController>(
        builder: (controller) {
          if (controller.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          final booking = controller.selectedBooking;
          if (booking == null) {
            return Center(child: Text('booking_not_found'.tr));
          }

          final String totalAmount = PriceConverter.convertPrice(booking.totalAmount ?? 0);

          return SingleChildScrollView(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                  decoration: BoxDecoration(
                    color: Theme.of(context).cardColor,
                    borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.grey.withValues(alpha: 0.1),
                        spreadRadius: 1,
                        blurRadius: 5,
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'booking_ref'.tr,
                            style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeDefault),
                          ),
                          BookingStatusBadgeWidget(status: booking.status ?? ''),
                        ],
                      ),
                      const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                      Text(
                        booking.bookingReference ?? '',
                        style: robotoBold.copyWith(
                          fontSize: Dimensions.fontSizeLarge,
                          color: Theme.of(context).primaryColor,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                _InfoCard(title: 'salon'.tr, content: booking.salonName ?? 'N/A'),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                _InfoCard(title: 'service'.tr, content: booking.serviceName ?? 'N/A'),
                if (booking.staffName != null) ...[
                  const SizedBox(height: Dimensions.paddingSizeDefault),
                  _InfoCard(title: 'staff'.tr, content: booking.staffName!),
                ],
                const SizedBox(height: Dimensions.paddingSizeDefault),
                _InfoCard(
                  title: 'date_time'.tr,
                  content: '${booking.bookingDate ?? ''} ${booking.bookingTime ?? ''}',
                ),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                _InfoCard(title: 'total_amount'.tr, content: totalAmount),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                _InfoCard(title: 'payment_status'.tr, content: booking.paymentStatus ?? 'N/A'),
                if (booking.notes != null && booking.notes!.isNotEmpty) ...[
                  const SizedBox(height: Dimensions.paddingSizeDefault),
                  _InfoCard(title: 'notes'.tr, content: booking.notes!),
                ],
                const SizedBox(height: Dimensions.paddingSizeLarge),
                Row(
                  children: [
                    Expanded(
                      child: CustomButton(
                        buttonText: 'cancel'.tr,
                        color: Theme.of(context).colorScheme.error,
                        onPressed: () {
                          showDialog(
                            context: context,
                            builder: (context) => CancelBookingDialog(bookingId: booking.id ?? 0),
                          );
                        },
                      ),
                    ),
                    const SizedBox(width: Dimensions.paddingSizeSmall),
                    Expanded(
                      child: CustomButton(
                        buttonText: 'reschedule'.tr,
                        onPressed: () {
                          showDialog(
                            context: context,
                            builder: (context) => RescheduleBookingDialog(bookingId: booking.id ?? 0),
                          );
                        },
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () {
                          Get.toNamed(
                            BeautyRouteHelper.getBeautyBookingConversationRoute(widget.bookingId),
                          );
                        },
                        child: Text('conversation'.tr),
                      ),
                    ),
                    const SizedBox(width: Dimensions.paddingSizeSmall),
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () {
                          Get.toNamed(BeautyRouteHelper.getBeautyWaitlistRoute());
                        },
                        child: Text('waitlist'.tr),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _InfoCard extends StatelessWidget {
  final String title;
  final String content;

  const _InfoCard({required this.title, required this.content});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withValues(alpha: 0.08),
            spreadRadius: 1,
            blurRadius: 4,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: robotoMedium.copyWith(fontSize: Dimensions.fontSizeDefault),
          ),
          const SizedBox(height: Dimensions.paddingSizeExtraSmall),
          Text(
            content,
            style: robotoRegular.copyWith(
              fontSize: Dimensions.fontSizeDefault,
              color: Theme.of(context).disabledColor,
            ),
          ),
        ],
      ),
    );
  }
}
