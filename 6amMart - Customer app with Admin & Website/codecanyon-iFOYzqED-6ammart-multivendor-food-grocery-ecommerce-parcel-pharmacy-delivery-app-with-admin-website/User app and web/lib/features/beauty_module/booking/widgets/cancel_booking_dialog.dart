import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/booking/controllers/beauty_booking_controller.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';

class CancelBookingDialog extends StatefulWidget {
  final int bookingId;
  
  const CancelBookingDialog({super.key, required this.bookingId});

  @override
  State<CancelBookingDialog> createState() => _CancelBookingDialogState();
}

class _CancelBookingDialogState extends State<CancelBookingDialog> {
  final TextEditingController _reasonController = TextEditingController();
  String? _selectedReason;
  
  final List<String> _cancellationReasons = [
    'schedule_conflict',
    'found_better_option',
    'service_no_longer_needed',
    'emergency',
    'other',
  ];

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      ),
      child: Container(
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Title
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'cancel_booking'.tr,
                  style: robotoBold.copyWith(
                    fontSize: Dimensions.fontSizeLarge,
                  ),
                ),
                IconButton(
                  onPressed: () => Get.back(),
                  icon: const Icon(Icons.close),
                  padding: EdgeInsets.zero,
                  constraints: const BoxConstraints(),
                ),
              ],
            ),
            
            const SizedBox(height: Dimensions.paddingSizeDefault),
            
            // Warning message
            Container(
              padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.error.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.warning_amber_rounded,
                    color: Theme.of(context).colorScheme.error,
                    size: 20,
                  ),
                  const SizedBox(width: Dimensions.paddingSizeSmall),
                  Expanded(
                    child: Text(
                      'cancel_booking_warning'.tr,
                      style: robotoRegular.copyWith(
                        fontSize: Dimensions.fontSizeSmall,
                        color: Theme.of(context).colorScheme.error,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: Dimensions.paddingSizeDefault),
            
            // Reason selection
            Text(
              'cancellation_reason'.tr,
              style: robotoMedium.copyWith(
                fontSize: Dimensions.fontSizeDefault,
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            
            Container(
              padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeSmall),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                border: Border.all(
                  color: Theme.of(context).primaryColor,
                  width: 1,
                ),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  isExpanded: true,
                  value: _selectedReason,
                  hint: Text('select_reason'.tr),
                  items: _cancellationReasons.map((String reason) {
                    return DropdownMenuItem<String>(
                      value: reason,
                      child: Text(reason.tr),
                    );
                  }).toList(),
                  onChanged: (String? value) {
                    setState(() {
                      _selectedReason = value;
                    });
                  },
                ),
              ),
            ),
            
            // Additional notes (shown when "other" is selected)
            if (_selectedReason == 'other') ...[
              const SizedBox(height: Dimensions.paddingSizeDefault),
              Text(
                'additional_notes'.tr,
                style: robotoMedium.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                ),
              ),
              const SizedBox(height: Dimensions.paddingSizeSmall),
              TextField(
                controller: _reasonController,
                maxLines: 3,
                decoration: InputDecoration(
                  hintText: 'enter_cancellation_reason'.tr,
                  hintStyle: robotoRegular.copyWith(
                    color: Theme.of(context).hintColor,
                  ),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    borderSide: BorderSide(
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    borderSide: BorderSide(
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    borderSide: BorderSide(
                      color: Theme.of(context).primaryColor,
                      width: 2,
                    ),
                  ),
                ),
              ),
            ],
            
            const SizedBox(height: Dimensions.paddingSizeLarge),
            
            // Action buttons
            Row(
              children: [
                Expanded(
                  child: CustomButton(
                    buttonText: 'keep_booking'.tr,
                    color: Theme.of(context).disabledColor,
                    onPressed: () => Get.back(),
                  ),
                ),
                const SizedBox(width: Dimensions.paddingSizeSmall),
                Expanded(
                  child: GetBuilder<BeautyBookingController>(
                    builder: (controller) {
                      return CustomButton(
                        buttonText: 'confirm_cancel'.tr,
                        color: Theme.of(context).colorScheme.error,
                        isLoading: controller.isCancelling,
                        onPressed: _selectedReason != null ? () => _cancelBooking(controller) : null,
                      );
                    },
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _cancelBooking(BeautyBookingController controller) async {
    String reason = _selectedReason!;
    if (_selectedReason == 'other' && _reasonController.text.trim().isNotEmpty) {
      reason = _reasonController.text.trim();
    }

    final success = await controller.cancelBooking(widget.bookingId, reason);
    
    if (success) {
      Get.back();
      Get.snackbar(
        'success'.tr,
        'booking_cancelled_successfully'.tr,
        backgroundColor: Colors.green,
        colorText: Colors.white,
      );
    }
  }
}
