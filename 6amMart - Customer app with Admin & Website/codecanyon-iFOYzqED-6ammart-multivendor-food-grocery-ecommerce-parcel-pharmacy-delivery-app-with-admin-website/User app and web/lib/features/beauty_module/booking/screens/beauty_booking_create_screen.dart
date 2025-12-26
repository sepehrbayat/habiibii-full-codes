import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/booking/controllers/beauty_booking_controller.dart';
import 'package:sixam_mart/features/beauty_module/booking/widgets/booking_service_selector_widget.dart';
import 'package:sixam_mart/features/beauty_module/booking/widgets/booking_staff_selector_widget.dart';
import 'package:sixam_mart/features/beauty_module/booking/widgets/booking_date_time_picker_widget.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/common/widgets/custom_text_field.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';

class BeautyBookingCreateScreen extends StatefulWidget {
  final int? salonId;
  final int? serviceId;
  
  const BeautyBookingCreateScreen({
    super.key,
    this.salonId,
    this.serviceId,
  });

  @override
  State<BeautyBookingCreateScreen> createState() => _BeautyBookingCreateScreenState();
}

class _BeautyBookingCreateScreenState extends State<BeautyBookingCreateScreen> {
  final TextEditingController _notesController = TextEditingController();

  @override
  void initState() {
    super.initState();
    final controller = Get.find<BeautyBookingController>();
    
    if (widget.salonId != null || widget.serviceId != null) {
      controller.setBookingFormData(
        salonId: widget.salonId,
        serviceId: widget.serviceId,
      );
    }
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.surface,
      appBar: CustomAppBar(
        title: 'create_booking'.tr,
        backButton: true,
      ),
      body: GetBuilder<BeautyBookingController>(
        builder: (controller) {
          return SingleChildScrollView(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Service Selection
                Text(
                  'select_service'.tr,
                  style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                BookingServiceSelectorWidget(
                  salonId: controller.selectedSalonId ?? widget.salonId,
                  selectedServiceId: controller.selectedServiceId,
                  onServiceSelected: (serviceId) {
                    controller.setBookingFormData(serviceId: serviceId);
                  },
                ),

                const SizedBox(height: Dimensions.paddingSizeDefault),

                // Staff Selection
                Text(
                  'select_staff'.tr,
                  style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                BookingStaffSelectorWidget(
                  salonId: controller.selectedSalonId ?? widget.salonId,
                  serviceId: controller.selectedServiceId,
                  selectedStaffId: controller.selectedStaffId,
                  onStaffSelected: (staffId) {
                    controller.setBookingFormData(staffId: staffId);
                  },
                ),

                const SizedBox(height: Dimensions.paddingSizeDefault),

                // Date & Time Selection
                Text(
                  'select_date_time'.tr,
                  style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                BookingDateTimePickerWidget(
                  selectedDate: controller.selectedDate,
                  selectedTime: controller.selectedTime,
                  onDateTimeSelected: (date, time) {
                    controller.setBookingFormData(date: date, time: time);
                  },
                ),

                const SizedBox(height: Dimensions.paddingSizeDefault),

                // Notes
                Text(
                  'notes_optional'.tr,
                  style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                CustomTextField(
                  controller: _notesController,
                  hintText: 'enter_any_special_requests'.tr,
                  maxLines: 3,
                  inputType: TextInputType.multiline,
                  onChanged: (value) {
                    controller.setBookingFormData(notes: value);
                  },
                ),

                const SizedBox(height: Dimensions.paddingSizeLarge),

                // Create Booking Button
                CustomButton(
                  buttonText: 'create_booking'.tr,
                  isLoading: controller.isCreating,
                  onPressed: _canCreateBooking(controller) ? () => _createBooking(controller) : null,
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  bool _canCreateBooking(BeautyBookingController controller) {
    return controller.selectedServiceId != null &&
           controller.selectedDate != null &&
           controller.selectedTime != null;
  }

  Future<void> _createBooking(BeautyBookingController controller) async {
    // Check availability first
    final availabilityData = {
      'salon_id': controller.selectedSalonId ?? widget.salonId,
      'service_id': controller.selectedServiceId,
      'staff_id': controller.selectedStaffId,
      'booking_date': controller.selectedDate,
      'booking_time': controller.selectedTime,
    };

    final isAvailable = await controller.checkAvailability(availabilityData);
    
    if (!isAvailable) {
      return;
    }

    // Create booking
    final bookingData = {
      'salon_id': controller.selectedSalonId ?? widget.salonId,
      'service_id': controller.selectedServiceId,
      'staff_id': controller.selectedStaffId,
      'booking_date': controller.selectedDate,
      'booking_time': controller.selectedTime,
      'notes': _notesController.text.trim(),
    };

    final booking = await controller.createBooking(bookingData);
    
    if (booking != null) {
      Get.back();
      // Navigate to booking list
      Get.toNamed(BeautyRouteHelper.getBeautyBookingsRoute());
    }
  }
}
