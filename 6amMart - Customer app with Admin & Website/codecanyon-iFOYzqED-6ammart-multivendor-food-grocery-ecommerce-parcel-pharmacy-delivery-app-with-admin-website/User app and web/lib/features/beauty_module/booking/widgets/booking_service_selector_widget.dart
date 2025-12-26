import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/controllers/beauty_salon_controller.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/models/beauty_service_model.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class BookingServiceSelectorWidget extends StatefulWidget {
  final int? salonId;
  final int? selectedServiceId;
  final Function(int) onServiceSelected;
  
  const BookingServiceSelectorWidget({
    super.key,
    required this.salonId,
    this.selectedServiceId,
    required this.onServiceSelected,
  });

  @override
  State<BookingServiceSelectorWidget> createState() => _BookingServiceSelectorWidgetState();
}

class _BookingServiceSelectorWidgetState extends State<BookingServiceSelectorWidget> {
  @override
  void initState() {
    super.initState();
    if (widget.salonId != null) {
      Get.find<BeautySalonController>().getServices(widget.salonId!);
    }
  }

  @override
  Widget build(BuildContext context) {
    return GetBuilder<BeautySalonController>(
      builder: (controller) {
        final services = controller.services ?? [];
        BeautyServiceModel? selectedService;
        
        if (widget.selectedServiceId != null && services.isNotEmpty) {
          try {
            selectedService = services.firstWhere(
              (service) => service.id == widget.selectedServiceId,
            );
          } catch (e) {
            selectedService = null;
          }
        }

        return InkWell(
          onTap: widget.salonId != null ? () => _showServiceSelector(context, services) : null,
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
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        selectedService?.name ?? 'select_service'.tr,
                        style: robotoRegular.copyWith(
                          fontSize: Dimensions.fontSizeDefault,
                          color: selectedService != null
                              ? Theme.of(context).textTheme.bodyLarge!.color
                              : Theme.of(context).hintColor,
                        ),
                      ),
                      if (selectedService != null) ...[
                        const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                        Row(
                          children: [
                            Text(
                              '\$${selectedService.price?.toStringAsFixed(2) ?? '0.00'}',
                              style: robotoMedium.copyWith(
                                fontSize: Dimensions.fontSizeSmall,
                                color: Theme.of(context).primaryColor,
                              ),
                            ),
                            const SizedBox(width: Dimensions.paddingSizeSmall),
                            Text(
                              '${selectedService.duration ?? 0} min',
                              style: robotoRegular.copyWith(
                                fontSize: Dimensions.fontSizeSmall,
                                color: Theme.of(context).disabledColor,
                              ),
                            ),
                          ],
                        ),
                      ],
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

  void _showServiceSelector(BuildContext context, List<BeautyServiceModel> services) {
    if (services.isEmpty) {
      Get.snackbar(
        'no_services'.tr,
        'no_services_available'.tr,
        backgroundColor: Theme.of(context).colorScheme.error,
        colorText: Colors.white,
      );
      return;
    }

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
                    'select_service'.tr,
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
            
            // Services list
            Expanded(
              child: ListView.builder(
                padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                itemCount: services.length,
                itemBuilder: (context, index) {
                  final service = services[index];
                  final isSelected = service.id == widget.selectedServiceId;
                  
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
                        widget.onServiceSelected(service.id!);
                        Get.back();
                      },
                      borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                      child: Padding(
                        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                        child: Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    service.name ?? 'N/A',
                                    style: robotoMedium.copyWith(
                                      fontSize: Dimensions.fontSizeDefault,
                                    ),
                                  ),
                                  if (service.description != null) ...[
                                    const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                                    Text(
                                      service.description!,
                                      style: robotoRegular.copyWith(
                                        fontSize: Dimensions.fontSizeSmall,
                                        color: Theme.of(context).disabledColor,
                                      ),
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ],
                                  const SizedBox(height: Dimensions.paddingSizeSmall),
                                  Row(
                                    children: [
                                      Icon(
                                        Icons.access_time,
                                        size: 14,
                                        color: Theme.of(context).disabledColor,
                                      ),
                                      const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                                      Text(
                                        '${service.duration ?? 0} min',
                                        style: robotoRegular.copyWith(
                                          fontSize: Dimensions.fontSizeSmall,
                                          color: Theme.of(context).disabledColor,
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text(
                                  '\$${service.price?.toStringAsFixed(2) ?? '0.00'}',
                                  style: robotoBold.copyWith(
                                    fontSize: Dimensions.fontSizeDefault,
                                    color: Theme.of(context).primaryColor,
                                  ),
                                ),
                                if (isSelected) ...[
                                  const SizedBox(height: Dimensions.paddingSizeSmall),
                                  Icon(
                                    Icons.check_circle,
                                    color: Theme.of(context).primaryColor,
                                    size: 20,
                                  ),
                                ],
                              ],
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
