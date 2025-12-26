import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/common/widgets/custom_image.dart';
import 'package:sixam_mart/common/widgets/footer_view.dart';
import 'package:sixam_mart/common/widgets/menu_drawer.dart';
import '../controllers/beauty_package_controller.dart';
import '../domain/models/beauty_package_model.dart';

class BeautyPackageDetailsScreen extends StatefulWidget {
  final int packageId;
  final bool isPurchased;
  
  const BeautyPackageDetailsScreen({
    Key? key,
    required this.packageId,
    this.isPurchased = false,
  }) : super(key: key);
  
  @override
  State<BeautyPackageDetailsScreen> createState() => _BeautyPackageDetailsScreenState();
}

class _BeautyPackageDetailsScreenState extends State<BeautyPackageDetailsScreen> {
  final ScrollController _scrollController = ScrollController();
  
  @override
  void initState() {
    super.initState();
    Get.find<BeautyPackageController>().getPackageDetails(widget.packageId);
  }
  
  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: 'package_details'.tr,
        onBackPressed: () {
          Get.find<BeautyPackageController>().clearSelectedPackage();
          Get.back();
        },
      ),
      endDrawer: const MenuDrawer(),
      body: GetBuilder<BeautyPackageController>(
        builder: (packageController) {
          BeautyPackageModel? package = packageController.selectedPackage;
          
          if (packageController.isLoading || package == null) {
            return const Center(child: CircularProgressIndicator());
          }
          
          double savings = packageController.calculateSavings(package);
          double discountPercentage = packageController.getDiscountPercentage(package);
          bool isActive = packageController.isPackageActive(package);
          final String statusText = package.isExpired == true
              ? 'expired'
              : (isActive ? 'active' : 'inactive');
          bool canCancel = packageController.canCancelPackage(package);
          bool canTransfer = packageController.canTransferPackage(package);
          bool isExpiring = packageController.isExpiringSoon(package);
          
          return SingleChildScrollView(
            controller: _scrollController,
            child: FooterView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Package Image
                  Container(
                    height: 250,
                    width: double.infinity,
                    color: Theme.of(context).cardColor,
                    child: Stack(
                      children: [
                        CustomImage(
                          image: package.image ?? '',
                          height: 250,
                          width: double.infinity,
                          fit: BoxFit.cover,
                        ),
                        
                        // Discount Badge
                        if (discountPercentage > 0)
                          Positioned(
                            top: Dimensions.paddingSizeDefault,
                            right: Dimensions.paddingSizeDefault,
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: Dimensions.paddingSizeSmall,
                                vertical: Dimensions.paddingSizeExtraSmall,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.red,
                                borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                              ),
                              child: Text(
                                '${discountPercentage.toStringAsFixed(0)}% OFF',
                                style: robotoMedium.copyWith(
                                  color: Colors.white,
                                  fontSize: Dimensions.fontSizeSmall,
                                ),
                              ),
                            ),
                          ),
                        
                        // Status Badge
                        if (widget.isPurchased)
                          Positioned(
                            top: Dimensions.paddingSizeDefault,
                            left: Dimensions.paddingSizeDefault,
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: Dimensions.paddingSizeSmall,
                                vertical: Dimensions.paddingSizeExtraSmall,
                              ),
                              decoration: BoxDecoration(
                                color: _getStatusColor(statusText),
                                borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                              ),
                              child: Text(
                                packageController.getStatusText(statusText),
                                style: robotoMedium.copyWith(
                                  color: Colors.white,
                                  fontSize: Dimensions.fontSizeSmall,
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                  
                  // Package Info
                  Container(
                    padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Name and Type
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    package.name ?? '',
                                    style: robotoBold.copyWith(
                                      fontSize: Dimensions.fontSizeExtraLarge,
                                    ),
                                  ),
                                  const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: Dimensions.paddingSizeSmall,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: Theme.of(context).primaryColor.withValues(alpha: 0.1),
                                      borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                                    ),
                                    child: Text(
                                      package.type?.toUpperCase() ?? '',
                                      style: robotoMedium.copyWith(
                                        fontSize: Dimensions.fontSizeSmall,
                                        color: Theme.of(context).primaryColor,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            
                            // Price
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                if (package.originalPrice != null && package.originalPrice! > package.price!)
                                  Text(
                                    '\$${package.originalPrice?.toStringAsFixed(2)}',
                                    style: robotoRegular.copyWith(
                                      fontSize: Dimensions.fontSizeDefault,
                                      decoration: TextDecoration.lineThrough,
                                      color: Theme.of(context).disabledColor,
                                    ),
                                  ),
                                Text(
                                  '\$${package.price?.toStringAsFixed(2)}',
                                  style: robotoBold.copyWith(
                                    fontSize: Dimensions.fontSizeExtraLarge,
                                    color: Theme.of(context).primaryColor,
                                  ),
                                ),
                                if (savings > 0)
                                  Text(
                                    'Save \$${savings.toStringAsFixed(2)}',
                                    style: robotoMedium.copyWith(
                                      fontSize: Dimensions.fontSizeSmall,
                                      color: Colors.green,
                                    ),
                                  ),
                              ],
                            ),
                          ],
                        ),
                        
                        // Description
                        if (package.description != null) ...[
                          const SizedBox(height: Dimensions.paddingSizeDefault),
                          Text(
                            'description'.tr,
                            style: robotoMedium.copyWith(
                              fontSize: Dimensions.fontSizeLarge,
                            ),
                          ),
                          const SizedBox(height: Dimensions.paddingSizeSmall),
                          Text(
                            package.description!,
                            style: robotoRegular.copyWith(
                              fontSize: Dimensions.fontSizeDefault,
                              color: Theme.of(context).disabledColor,
                            ),
                          ),
                        ],
                        
                        // Validity
                        const SizedBox(height: Dimensions.paddingSizeDefault),
                        Container(
                          padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                          decoration: BoxDecoration(
                            color: Theme.of(context).cardColor,
                            borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                            border: Border.all(
                              color: Theme.of(context).disabledColor.withValues(alpha: 0.3),
                            ),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                Icons.calendar_today,
                                size: 20,
                                color: Theme.of(context).primaryColor,
                              ),
                              const SizedBox(width: Dimensions.paddingSizeSmall),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'validity'.tr,
                                      style: robotoRegular.copyWith(
                                        fontSize: Dimensions.fontSizeSmall,
                                        color: Theme.of(context).disabledColor,
                                      ),
                                    ),
                                    Text(
                                      '${package.validityDays ?? 0} days',
                                      style: robotoMedium.copyWith(
                                        fontSize: Dimensions.fontSizeDefault,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              if (isExpiring)
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: Dimensions.paddingSizeSmall,
                                    vertical: Dimensions.paddingSizeExtraSmall,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.orange,
                                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                                  ),
                                  child: Text(
                                    'expiring_soon'.tr,
                                    style: robotoMedium.copyWith(
                                      fontSize: Dimensions.fontSizeExtraSmall,
                                      color: Colors.white,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ),
                        
                        // Services Included
                        if (package.services != null && package.services!.isNotEmpty) ...[
                          const SizedBox(height: Dimensions.paddingSizeDefault),
                          Text(
                            'services_included'.tr,
                            style: robotoMedium.copyWith(
                              fontSize: Dimensions.fontSizeLarge,
                            ),
                          ),
                          const SizedBox(height: Dimensions.paddingSizeSmall),
                          ...package.services!.map((service) {
                            int quantity = service['quantity'] ?? 1;
                            int remaining = service['remaining'] ?? quantity;
                            String serviceName = service['name'] ?? '';
                            double servicePrice = service['price']?.toDouble() ?? 0.0;
                            
                            return Container(
                              margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
                              padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                              decoration: BoxDecoration(
                                color: Theme.of(context).cardColor,
                                borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                                border: Border.all(
                                  color: Theme.of(context).disabledColor.withValues(alpha: 0.3),
                                ),
                              ),
                              child: Row(
                                children: [
                                  // Service Icon
                                  Container(
                                    height: 40,
                                    width: 40,
                                    decoration: BoxDecoration(
                                      color: Theme.of(context).primaryColor.withValues(alpha: 0.1),
                                      borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                                    ),
                                    child: Icon(
                                      Icons.spa,
                                      color: Theme.of(context).primaryColor,
                                      size: 20,
                                    ),
                                  ),
                                  const SizedBox(width: Dimensions.paddingSizeSmall),
                                  
                                  // Service Details
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          serviceName,
                                          style: robotoMedium.copyWith(
                                            fontSize: Dimensions.fontSizeDefault,
                                          ),
                                        ),
                                        Text(
                                          'Value: \$${servicePrice.toStringAsFixed(2)}',
                                          style: robotoRegular.copyWith(
                                            fontSize: Dimensions.fontSizeSmall,
                                            color: Theme.of(context).disabledColor,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  
                                  // Quantity
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.end,
                                    children: [
                                      Text(
                                        '${quantity}x',
                                        style: robotoBold.copyWith(
                                          fontSize: Dimensions.fontSizeLarge,
                                          color: Theme.of(context).primaryColor,
                                        ),
                                      ),
                                      if (widget.isPurchased)
                                        Text(
                                          '$remaining left',
                                          style: robotoRegular.copyWith(
                                            fontSize: Dimensions.fontSizeExtraSmall,
                                            color: Theme.of(context).disabledColor,
                                          ),
                                        ),
                                    ],
                                  ),
                                ],
                              ),
                            );
                          }).toList(),
                        ],
                        
                        // Terms and Conditions
                        if (package.terms != null && package.terms!.isNotEmpty) ...[
                          const SizedBox(height: Dimensions.paddingSizeDefault),
                          Text(
                            'terms_and_conditions'.tr,
                            style: robotoMedium.copyWith(
                              fontSize: Dimensions.fontSizeLarge,
                            ),
                          ),
                          const SizedBox(height: Dimensions.paddingSizeSmall),
                          ...package.terms!.map((term) {
                            return Padding(
                              padding: const EdgeInsets.only(bottom: Dimensions.paddingSizeExtraSmall),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Icon(Icons.check_circle, size: 16, color: Colors.green),
                                  const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                                  Expanded(
                                    child: Text(
                                      term,
                                      style: robotoRegular.copyWith(
                                        fontSize: Dimensions.fontSizeSmall,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }).toList(),
                        ],
                        
                        // Usage Progress (for purchased packages)
                        if (widget.isPurchased) ...[
                          const SizedBox(height: Dimensions.paddingSizeDefault),
                          Container(
                            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                            decoration: BoxDecoration(
                              color: Theme.of(context).cardColor,
                              borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                              border: Border.all(
                                color: Theme.of(context).disabledColor.withValues(alpha: 0.3),
                              ),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'usage_summary'.tr,
                                  style: robotoMedium.copyWith(
                                    fontSize: Dimensions.fontSizeLarge,
                                  ),
                                ),
                                const SizedBox(height: Dimensions.paddingSizeDefault),
                                
                                // Progress Bar
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      'Services Used',
                                      style: robotoRegular.copyWith(
                                        fontSize: Dimensions.fontSizeDefault,
                                      ),
                                    ),
                                    Text(
                                      '${packageController.getUsedServices(package)}/${packageController.getTotalServices(package)}',
                                      style: robotoMedium.copyWith(
                                        fontSize: Dimensions.fontSizeDefault,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: Dimensions.paddingSizeSmall),
                                LinearProgressIndicator(
                                  value: packageController.getPackageProgress(package) / 100,
                                  backgroundColor: Theme.of(context).disabledColor.withValues(alpha: 0.2),
                                  valueColor: AlwaysStoppedAnimation<Color>(
                                    Theme.of(context).primaryColor,
                                  ),
                                ),
                                
                                // Actions
                                const SizedBox(height: Dimensions.paddingSizeDefault),
                                Row(
                                  children: [
                                    if (canTransfer)
                                      Expanded(
                                        child: CustomButton(
                                          buttonText: 'transfer'.tr,
                                          color: Colors.orange,
                                          onPressed: () => _showTransferDialog(context, packageController),
                                        ),
                                      ),
                                    if (canTransfer && canCancel)
                                      const SizedBox(width: Dimensions.paddingSizeSmall),
                                    if (canCancel)
                                      Expanded(
                                        child: CustomButton(
                                          buttonText: 'cancel'.tr,
                                          color: Colors.red,
                                          onPressed: () => _showCancelDialog(context, packageController),
                                        ),
                                      ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
      
      // Bottom Bar for Purchase
      bottomNavigationBar: GetBuilder<BeautyPackageController>(
        builder: (packageController) {
          if (widget.isPurchased || packageController.selectedPackage == null) {
            return const SizedBox();
          }
          
          return Container(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            decoration: BoxDecoration(
              color: Theme.of(context).cardColor,
              boxShadow: [
                BoxShadow(
                  color: Colors.grey.withValues(alpha: 0.2),
                  spreadRadius: 1,
                  blurRadius: 5,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            child: SafeArea(
              child: CustomButton(
                buttonText: 'purchase_package'.tr,
                onPressed: packageController.isPackageActive(packageController.selectedPackage!)
                  ? () => _showPurchaseDialog(context, packageController)
                  : null,
              ),
            ),
          );
        },
      ),
    );
  }
  
  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'active':
        return Colors.green;
      case 'expired':
        return Colors.red;
      case 'used':
        return Colors.grey;
      case 'cancelled':
        return Colors.orange;
      default:
        return Theme.of(context).primaryColor;
    }
  }
  
  void _showPurchaseDialog(BuildContext context, BeautyPackageController controller) {
    Get.dialog(
      AlertDialog(
        title: Text('confirm_purchase'.tr),
        content: Text('are_you_sure_purchase_package'.tr),
        actions: [
          TextButton(
            onPressed: () => Get.back(),
            child: Text('cancel'.tr),
          ),
          ElevatedButton(
            onPressed: () async {
              Get.back();
              bool success = await controller.purchasePackage(
                packageId: widget.packageId,
                paymentMethod: 'card', // This should come from payment selection
              );
              if (success) {
                Get.back();
              }
            },
            child: Text('confirm'.tr),
          ),
        ],
      ),
    );
  }
  
  void _showTransferDialog(BuildContext context, BeautyPackageController controller) {
    final TextEditingController emailController = TextEditingController();
    final TextEditingController messageController = TextEditingController();
    
    Get.dialog(
      AlertDialog(
        title: Text('transfer_package'.tr),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: emailController,
              decoration: InputDecoration(
                labelText: 'recipient_email'.tr,
                border: const OutlineInputBorder(),
              ),
              keyboardType: TextInputType.emailAddress,
            ),
            const SizedBox(height: Dimensions.paddingSizeDefault),
            TextField(
              controller: messageController,
              decoration: InputDecoration(
                labelText: 'message_optional'.tr,
                border: const OutlineInputBorder(),
              ),
              maxLines: 3,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Get.back(),
            child: Text('cancel'.tr),
          ),
          ElevatedButton(
            onPressed: () async {
              if (emailController.text.isNotEmpty) {
                Get.back();
                await controller.transferPackage(
                  purchaseId: widget.packageId,
                  recipientEmail: emailController.text,
                  message: messageController.text,
                );
              }
            },
            child: Text('transfer'.tr),
          ),
        ],
      ),
    );
  }
  
  void _showCancelDialog(BuildContext context, BeautyPackageController controller) {
    final TextEditingController reasonController = TextEditingController();
    
    Get.dialog(
      AlertDialog(
        title: Text('cancel_package'.tr),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('are_you_sure_cancel_package'.tr),
            const SizedBox(height: Dimensions.paddingSizeDefault),
            TextField(
              controller: reasonController,
              decoration: InputDecoration(
                labelText: 'reason'.tr,
                border: const OutlineInputBorder(),
              ),
              maxLines: 3,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Get.back(),
            child: Text('no'.tr),
          ),
          ElevatedButton(
            onPressed: () async {
              if (reasonController.text.isNotEmpty) {
                Get.back();
                await controller.cancelPackage(
                  widget.packageId,
                  reasonController.text,
                );
              }
            },
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: Text('yes_cancel'.tr),
          ),
        ],
      ),
    );
  }
}
