import 'package:flutter/material.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/models/beauty_booking_model.dart';
import 'package:sixam_mart/features/beauty_module/booking/widgets/booking_status_badge_widget.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class BookingCardWidget extends StatelessWidget {
  final BeautyBookingModel booking;
  
  const BookingCardWidget({super.key, required this.booking});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        boxShadow: [BoxShadow(
          color: Colors.grey.withValues(alpha: 0.1),
          spreadRadius: 1,
          blurRadius: 5,
        )],
      ),
      child: InkWell(
        onTap: () {
          // Navigate to booking details
          // Get.toNamed(RouteHelper.getBeautyBookingDetailsRoute(booking.id!));
        },
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        child: Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header with booking reference and status
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      booking.bookingReference ?? 'N/A',
                      style: robotoBold.copyWith(
                        fontSize: Dimensions.fontSizeDefault,
                        color: Theme.of(context).primaryColor,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  BookingStatusBadgeWidget(status: booking.status ?? ''),
                ],
              ),
              
              const SizedBox(height: Dimensions.paddingSizeSmall),
              
              // Salon name
              Text(
                booking.salonName ?? 'N/A',
                style: robotoMedium.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              
              const SizedBox(height: Dimensions.paddingSizeExtraSmall),
              
              // Service name
              Text(
                booking.serviceName ?? 'N/A',
                style: robotoRegular.copyWith(
                  fontSize: Dimensions.fontSizeSmall,
                  color: Theme.of(context).disabledColor,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              
              const SizedBox(height: Dimensions.paddingSizeSmall),
              
              // Date and time
              Row(
                children: [
                  Icon(
                    Icons.calendar_today,
                    size: 16,
                    color: Theme.of(context).disabledColor,
                  ),
                  const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                  Text(
                    booking.bookingDate ?? 'N/A',
                    style: robotoRegular.copyWith(
                      fontSize: Dimensions.fontSizeSmall,
                      color: Theme.of(context).disabledColor,
                    ),
                  ),
                  const SizedBox(width: Dimensions.paddingSizeDefault),
                  Icon(
                    Icons.access_time,
                    size: 16,
                    color: Theme.of(context).disabledColor,
                  ),
                  const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                  Text(
                    booking.bookingTime ?? 'N/A',
                    style: robotoRegular.copyWith(
                      fontSize: Dimensions.fontSizeSmall,
                      color: Theme.of(context).disabledColor,
                    ),
                  ),
                ],
              ),
              
              const SizedBox(height: Dimensions.paddingSizeSmall),
              
              // Amount
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  if (booking.staffName != null)
                    Expanded(
                      child: Row(
                        children: [
                          Icon(
                            Icons.person,
                            size: 16,
                            color: Theme.of(context).disabledColor,
                          ),
                          const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                          Flexible(
                            child: Text(
                              booking.staffName!,
                              style: robotoRegular.copyWith(
                                fontSize: Dimensions.fontSizeSmall,
                                color: Theme.of(context).disabledColor,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  Text(
                    '\$${booking.totalAmount?.toStringAsFixed(2) ?? '0.00'}',
                    style: robotoBold.copyWith(
                      fontSize: Dimensions.fontSizeDefault,
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
