import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/common/widgets/custom_image.dart';
import 'package:sixam_mart/common/widgets/footer_view.dart';
import 'package:sixam_mart/common/widgets/menu_drawer.dart';
import 'package:sixam_mart/common/widgets/no_data_screen.dart';
import '../domain/models/beauty_package_model.dart';
import '../controllers/beauty_package_controller.dart';
import '../widgets/package_card_widget.dart';
import 'beauty_package_details_screen.dart';

class BeautyPackagesScreen extends StatefulWidget {
  final int? salonId;
  final String? salonName;
  
  const BeautyPackagesScreen({Key? key, this.salonId, this.salonName}) : super(key: key);
  
  @override
  State<BeautyPackagesScreen> createState() => _BeautyPackagesScreenState();
}

class _BeautyPackagesScreenState extends State<BeautyPackagesScreen> with SingleTickerProviderStateMixin {
  final ScrollController _scrollController = ScrollController();
  TabController? _tabController;
  
  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    
    Get.find<BeautyPackageController>().getPackages(1, reload: true);
    Get.find<BeautyPackageController>().getPurchasedPackages();
  }
  
  @override
  void dispose() {
    _scrollController.dispose();
    _tabController?.dispose();
    super.dispose();
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title: widget.salonName != null 
          ? '${widget.salonName} Packages' 
          : 'beauty_packages'.tr,
        onBackPressed: () => Get.back(),
      ),
      endDrawer: const MenuDrawer(),
      body: GetBuilder<BeautyPackageController>(
        builder: (packageController) {
          return Column(
            children: [
              // Tab Bar
              Container(
                color: Theme.of(context).cardColor,
                child: TabBar(
                  controller: _tabController,
                  indicatorColor: Theme.of(context).primaryColor,
                  labelColor: Theme.of(context).primaryColor,
                  unselectedLabelColor: Theme.of(context).disabledColor,
                  labelStyle: robotoMedium.copyWith(fontSize: Dimensions.fontSizeDefault),
                  tabs: [
                    Tab(text: 'all_packages'.tr),
                    Tab(text: 'my_packages'.tr),
                    Tab(text: 'popular'.tr),
                  ],
                  onTap: (index) {
                    if (index == 0) {
                      packageController.setFilter('all');
                    } else if (index == 1) {
                      // My packages tab - already loaded
                    } else if (index == 2) {
                      // Popular packages tab
                    }
                  },
                ),
              ),
              
              // Filter and Sort Row
              if (_tabController?.index == 0) Container(
                padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                color: Theme.of(context).cardColor,
                child: Row(
                  children: [
                    // Filter Chips
                    Expanded(
                      child: SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: Row(
                          children: [
                            _buildFilterChip(
                              'All',
                              'all',
                              packageController,
                            ),
                            const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                            _buildFilterChip(
                              'Basic',
                              'basic',
                              packageController,
                            ),
                            const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                            _buildFilterChip(
                              'Premium',
                              'premium',
                              packageController,
                            ),
                            const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                            _buildFilterChip(
                              'VIP',
                              'vip',
                              packageController,
                            ),
                          ],
                        ),
                      ),
                    ),
                    
                    // Sort Button
                    PopupMenuButton<String>(
                      icon: Icon(
                        Icons.sort,
                        color: Theme.of(context).primaryColor,
                      ),
                      onSelected: (value) {
                        packageController.setSortBy(value);
                      },
                      itemBuilder: (context) => [
                        PopupMenuItem(
                          value: 'default',
                          child: Text('default'.tr),
                        ),
                        PopupMenuItem(
                          value: 'price_low_to_high',
                          child: Text('price_low_to_high'.tr),
                        ),
                        PopupMenuItem(
                          value: 'price_high_to_low',
                          child: Text('price_high_to_low'.tr),
                        ),
                        PopupMenuItem(
                          value: 'popularity',
                          child: Text('most_popular'.tr),
                        ),
                        PopupMenuItem(
                          value: 'savings',
                          child: Text('highest_savings'.tr),
                        ),
                        PopupMenuItem(
                          value: 'validity',
                          child: Text('longest_validity'.tr),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              
              // Tab View
              Expanded(
                child: TabBarView(
                  controller: _tabController,
                  children: [
                    // All Packages Tab
                    _buildAllPackagesTab(packageController),
                    
                    // My Packages Tab
                    _buildMyPackagesTab(packageController),
                    
                    // Popular Packages Tab
                    _buildPopularPackagesTab(packageController),
                  ],
                ),
              ),
            ],
          );
        },
      ),
    );
  }
  
  Widget _buildFilterChip(
    String label,
    String value,
    BeautyPackageController controller,
  ) {
    bool isSelected = controller.selectedFilter == value;
    
    return FilterChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (selected) {
        if (selected) {
          controller.setFilter(value);
        }
      },
      selectedColor: Theme.of(context).primaryColor.withValues(alpha: 0.2),
      checkmarkColor: Theme.of(context).primaryColor,
      labelStyle: TextStyle(
        color: isSelected 
          ? Theme.of(context).primaryColor 
          : Theme.of(context).textTheme.bodyLarge?.color,
      ),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        side: BorderSide(
          color: isSelected 
            ? Theme.of(context).primaryColor 
            : Theme.of(context).disabledColor.withValues(alpha: 0.3),
        ),
      ),
    );
  }
  
  Widget _buildAllPackagesTab(BeautyPackageController controller) {
    if (controller.packageList == null) {
      return const Center(child: CircularProgressIndicator());
    }
    
    if (controller.packageList!.isEmpty) {
      return NoDataScreen(
        text: 'no_packages_found'.tr,
        showFooter: true,
      );
    }
    
    return RefreshIndicator(
      onRefresh: () async {
        await controller.getPackages(1, reload: true);
      },
      child: SingleChildScrollView(
        controller: _scrollController,
        physics: const AlwaysScrollableScrollPhysics(),
        child: FooterView(
          child: Column(
            children: [
              GridView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: GetPlatform.isDesktop ? 3 : 2,
                  childAspectRatio: GetPlatform.isDesktop ? 0.8 : 0.75,
                  crossAxisSpacing: Dimensions.paddingSizeSmall,
                  mainAxisSpacing: Dimensions.paddingSizeSmall,
                ),
                padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                itemCount: controller.packageList!.length,
                itemBuilder: (context, index) {
                  return PackageCardWidget(
                    package: controller.packageList![index],
                    onTap: () {
                      Get.to(() => BeautyPackageDetailsScreen(
                        packageId: controller.packageList![index].id!,
                      ));
                    },
                  );
                },
              ),
              
              if (controller.isLoading)
                const Padding(
                  padding: EdgeInsets.all(Dimensions.paddingSizeSmall),
                  child: CircularProgressIndicator(),
                ),
            ],
          ),
        ),
      ),
    );
  }
  
  Widget _buildMyPackagesTab(BeautyPackageController controller) {
    if (controller.purchasedPackages == null) {
      return const Center(child: CircularProgressIndicator());
    }
    
    if (controller.purchasedPackages!.isEmpty) {
      return NoDataScreen(
        text: 'no_purchased_packages'.tr,
        showFooter: true,
      );
    }
    
    return RefreshIndicator(
      onRefresh: () async {
        await controller.getPurchasedPackages();
      },
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: FooterView(
          child: ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
            itemCount: controller.purchasedPackages!.length,
            itemBuilder: (context, index) {
              final package = controller.purchasedPackages![index];
              final remaining = controller.remainingServices[package.id] ?? 0;
              final progress = controller.getPackageProgress(package);
              final isExpiring = controller.isExpiringSoon(package);
              
              return Card(
                margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
                child: InkWell(
                  onTap: () {
                    Get.to(() => BeautyPackageDetailsScreen(
                      packageId: package.id!,
                      isPurchased: true,
                    ));
                  },
                  borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                  child: Padding(
                    padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            // Package Image
                            ClipRRect(
                              borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                              child: CustomImage(
                                image: package.image ?? '',
                                height: 60,
                                width: 60,
                                fit: BoxFit.cover,
                              ),
                            ),
                            const SizedBox(width: Dimensions.paddingSizeSmall),
                            
                            // Package Info
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    package.name ?? '',
                                    style: robotoMedium.copyWith(
                                      fontSize: Dimensions.fontSizeLarge,
                                    ),
                                  ),
                                  const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: Dimensions.paddingSizeExtraSmall,
                                          vertical: 2,
                                        ),
                                        decoration: BoxDecoration(
                                      color: _getStatusColor(_getPackageStatus(package)),
                                          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                                        ),
                                        child: Text(
                                      controller.getStatusText(_getPackageStatus(package)),
                                          style: robotoRegular.copyWith(
                                            fontSize: Dimensions.fontSizeExtraSmall,
                                            color: Colors.white,
                                          ),
                                        ),
                                      ),
                                      if (isExpiring) ...[
                                        const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                                        Container(
                                          padding: const EdgeInsets.symmetric(
                                            horizontal: Dimensions.paddingSizeExtraSmall,
                                            vertical: 2,
                                          ),
                                          decoration: BoxDecoration(
                                            color: Colors.orange,
                                            borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                                          ),
                                          child: Text(
                                            'expiring_soon'.tr,
                                            style: robotoRegular.copyWith(
                                              fontSize: Dimensions.fontSizeExtraSmall,
                                              color: Colors.white,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),
                                ],
                              ),
                            ),
                            
                            // Remaining Services
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text(
                                  '$remaining',
                                  style: robotoBold.copyWith(
                                    fontSize: Dimensions.fontSizeExtraLarge,
                                    color: Theme.of(context).primaryColor,
                                  ),
                                ),
                                Text(
                                  'services_left'.tr,
                                  style: robotoRegular.copyWith(
                                    fontSize: Dimensions.fontSizeSmall,
                                    color: Theme.of(context).disabledColor,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                        
                        // Progress Bar
                        const SizedBox(height: Dimensions.paddingSizeSmall),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  'usage_progress'.tr,
                                  style: robotoRegular.copyWith(
                                    fontSize: Dimensions.fontSizeSmall,
                                  ),
                                ),
                                Text(
                                  '${progress.toStringAsFixed(0)}%',
                                  style: robotoMedium.copyWith(
                                    fontSize: Dimensions.fontSizeSmall,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                            LinearProgressIndicator(
                              value: progress / 100,
                              backgroundColor: Theme.of(context).disabledColor.withValues(alpha: 0.2),
                              valueColor: AlwaysStoppedAnimation<Color>(
                                Theme.of(context).primaryColor,
                              ),
                            ),
                          ],
                        ),
                        
                        // Validity
                        if (package.endDate != null) ...[
                          const SizedBox(height: Dimensions.paddingSizeSmall),
                          Row(
                            children: [
                              Icon(
                                Icons.calendar_today,
                                size: 14,
                                color: Theme.of(context).disabledColor,
                              ),
                              const SizedBox(width: Dimensions.paddingSizeExtraSmall),
                              Text(
                                'Valid till: ${_formatDate(package.endDate)}',
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
                ),
              );
            },
          ),
        ),
      ),
    );
  }
  
  Widget _buildPopularPackagesTab(BeautyPackageController controller) {
    if (controller.popularPackages == null) {
      return const Center(child: CircularProgressIndicator());
    }
    
    if (controller.popularPackages!.isEmpty) {
      return NoDataScreen(
        text: 'no_popular_packages'.tr,
        showFooter: true,
      );
    }
    
    return RefreshIndicator(
      onRefresh: () async {
        await controller.getPopularPackages();
      },
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: FooterView(
          child: GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: GetPlatform.isDesktop ? 3 : 2,
              childAspectRatio: GetPlatform.isDesktop ? 0.8 : 0.75,
              crossAxisSpacing: Dimensions.paddingSizeSmall,
              mainAxisSpacing: Dimensions.paddingSizeSmall,
            ),
            padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
            itemCount: controller.popularPackages!.length,
            itemBuilder: (context, index) {
              return PackageCardWidget(
                package: controller.popularPackages![index],
                showPopularBadge: true,
                onTap: () {
                  Get.to(() => BeautyPackageDetailsScreen(
                    packageId: controller.popularPackages![index].id!,
                  ));
                },
              );
            },
          ),
        ),
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

  String _getPackageStatus(BeautyPackageModel package) {
    if (package.isExpired == true) {
      return 'expired';
    }
    if (package.isActive == true) {
      return 'active';
    }
    return 'inactive';
  }
  
  String _formatDate(DateTime? date) {
    if (date == null) {
      return '-';
    }
    return '${date.day}/${date.month}/${date.year}';
  }
}
