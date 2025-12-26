import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/controllers/beauty_salon_controller.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/models/beauty_staff_model.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:cached_network_image/cached_network_image.dart';

class BookingStaffSelectorWidget extends StatefulWidget {
  final int? salonId;
  final int? serviceId;
  final int? selectedStaffId;
  final Function(int?) onStaffSelected;
  
  const BookingStaffSelectorWidget({
    super.key,
    required this.salonId,
    this.serviceId,
    this.selectedStaffId,
    required this.onStaffSelected,
  });

  @override
  State<BookingStaffSelectorWidget> createState() => _BookingStaffSelectorWidgetState();
}

class _BookingStaffSelectorWidgetState extends State<BookingStaffSelectorWidget> {
  @override
  void initState() {
    super.initState();
    if (widget.salonId != null) {
      Get.find<BeautySalonController>().getStaff(widget.salonId!);
    }
  }

  @override
  Widget build(BuildContext context) {
    return GetBuilder<BeautySalonController>(
      builder: (controller) {
        final staff = controller.staff ?? [];
        BeautyStaffModel? selectedStaff;
        
        if (widget.selectedStaffId != null && staff.isNotEmpty) {
          try {
            selectedStaff = staff.firstWhere(
              (s) => s.id == widget.selectedStaffId,
            );
          } catch (e) {
            selectedStaff = null;
          }
        }

        return InkWell(
          onTap: widget.salonId != null ? () => _showStaffSelector(context, staff) : null,
          child: Container(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
              border: Border.all(
                color: Theme.of(context).primaryColor,
                width: 1,
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Row(
                    children: [
                      if (selectedStaff != null && selectedStaff.image != null)
                        CircleAvatar(
                          radius: 20,
                          backgroundImage: CachedNetworkImageProvider(selectedStaff.image!),
                        )
                      else if (selectedStaff != null)
                        CircleAvatar(
                          radius: 20,
                          backgroundColor: Theme.of(context).primaryColor.withValues(alpha: 0.1),
                          child: Icon(
                            Icons.person,
                            size: 20,
                            color: Theme.of(context).primaryColor,
                          ),
                        ),
                      if (selectedStaff != null)
                        const SizedBox(width: Dimensions.paddingSizeSmall),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              selectedStaff?.name ?? 'select_staff_optional'.tr,
                              style: robotoRegular.copyWith(
                                fontSize: Dimensions.fontSizeDefault,
                                color: selectedStaff != null
                                    ? Theme.of(context).textTheme.bodyLarge!.color
                                    : Theme.of(context).hintColor,
                              ),
                            ),
                            if (selectedStaff?.specialization != null) ...[
                              const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                              Text(
                                selectedStaff!.specialization!,
                                style: robotoRegular.copyWith(
                                  fontSize: Dimensions.fontSizeSmall,
                                  color: Theme.of(context).disabledColor,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                Icon(
                  Icons.arrow_drop_down,
                  color: Theme.of(context).primaryColor,
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _showStaffSelector(BuildContext context, List<BeautyStaffModel> staff) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        height: MediaQuery.of(context).size.height * 0.7,
        decoration: BoxDecoration(
          color: Theme.of(context).cardColor,
          borderRadius: const BorderRadius.only(
            topLeft: Radius.circular(Dimensions.radiusLarge),
            topRight: Radius.circular(Dimensions.radiusLarge),
          ),
        ),
        child: Column(
          children: [
            // Handle bar
            Container(
              margin: const EdgeInsets.only(top: Dimensions.paddingSizeSmall),
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: Theme.of(context).disabledColor.withValues(alpha: 0.3),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            
            // Title
            Padding(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'select_staff'.tr,
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
            ),
            
            const Divider(height: 1),
            
            // Any staff option
            Container(
              margin: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              decoration: BoxDecoration(
                color: widget.selectedStaffId == null
                    ? Theme.of(context).primaryColor.withValues(alpha: 0.1)
                    : Theme.of(context).cardColor,
                borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                border: Border.all(
                  color: widget.selectedStaffId == null
                      ? Theme.of(context).primaryColor
                      : Theme.of(context).disabledColor.withValues(alpha: 0.3),
                  width: widget.selectedStaffId == null ? 2 : 1,
                ),
              ),
              child: InkWell(
                onTap: () {
                  widget.onStaffSelected(null);
                  Get.back();
                },
                borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                child: Padding(
                  padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 25,
                        backgroundColor: Theme.of(context).primaryColor.withValues(alpha: 0.1),
                        child: Icon(
                          Icons.groups,
                          color: Theme.of(context).primaryColor,
                        ),
                      ),
                      const SizedBox(width: Dimensions.paddingSizeDefault),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'any_available_staff'.tr,
                              style: robotoMedium.copyWith(
                                fontSize: Dimensions.fontSizeDefault,
                              ),
                            ),
                            const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                            Text(
                              'let_salon_assign_staff'.tr,
                              style: robotoRegular.copyWith(
                                fontSize: Dimensions.fontSizeSmall,
                                color: Theme.of(context).disabledColor,
                              ),
                            ),
                          ],
                        ),
                      ),
                      if (widget.selectedStaffId == null)
                        Icon(
                          Icons.check_circle,
                          color: Theme.of(context).primaryColor,
                          size: 20,
                        ),
                    ],
                  ),
                ),
              ),
            ),
            
            if (staff.isEmpty)
              Expanded(
                child: Center(
                  child: Text(
                    'no_staff_available'.tr,
                    style: robotoRegular.copyWith(
                      fontSize: Dimensions.fontSizeDefault,
                      color: Theme.of(context).disabledColor,
                    ),
                  ),
                ),
              )
            else
              // Staff list
              Expanded(
                child: ListView.builder(
                  padding: const EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
                  itemCount: staff.length,
                  itemBuilder: (context, index) {
                    final staffMember = staff[index];
                    final isSelected = staffMember.id == widget.selectedStaffId;
                    
                    return Container(
                      margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
                      decoration: BoxDecoration(
                        color: isSelected
                            ? Theme.of(context).primaryColor.withValues(alpha: 0.1)
                            : Theme.of(context).cardColor,
                        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                        border: Border.all(
                          color: isSelected
                              ? Theme.of(context).primaryColor
                              : Theme.of(context).disabledColor.withValues(alpha: 0.3),
                          width: isSelected ? 2 : 1,
                        ),
                      ),
                      child: InkWell(
                        onTap: () {
                          widget.onStaffSelected(staffMember.id);
                          Get.back();
                        },
                        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                        child: Padding(
                          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                          child: Row(
                            children: [
                              CircleAvatar(
                                radius: 25,
                                backgroundImage: staffMember.image != null
                                    ? CachedNetworkImageProvider(staffMember.image!)
                                    : null,
                                child: staffMember.image == null
                                    ? const Icon(Icons.person, size: 25)
                                    : null,
                              ),
                              const SizedBox(width: Dimensions.paddingSizeDefault),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      staffMember.name ?? 'N/A',
                                      style: robotoMedium.copyWith(
                                        fontSize: Dimensions.fontSizeDefault,
                                      ),
                                    ),
                                    if (staffMember.specialization != null) ...[
                                      const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                                      Text(
                                        staffMember.specialization!,
                                        style: robotoRegular.copyWith(
                                          fontSize: Dimensions.fontSizeSmall,
                                          color: Theme.of(context).disabledColor,
                                        ),
                                      ),
                                    ],
                                    if (staffMember.experienceYears != null) ...[
                                      const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                                      Text(
                                        '${staffMember.experienceYears} years_experience'.tr,
                                        style: robotoRegular.copyWith(
                                          fontSize: Dimensions.fontSizeSmall,
                                          color: Theme.of(context).disabledColor,
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                              if (isSelected)
                                Icon(
                                  Icons.check_circle,
                                  color: Theme.of(context).primaryColor,
                                  size: 20,
                                ),
                            ],
                          ),
                        ),
                      ),
                    );
                  },
                ),
              ),
          ],
        ),
      ),
    );
  }
}
