import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/retail/controllers/beauty_retail_controller.dart';
import 'package:sixam_mart/features/beauty_module/retail/widgets/retail_product_card_widget.dart';
import 'package:sixam_mart/features/beauty_module/retail/widgets/retail_product_filter_widget.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_text_field.dart';
import 'package:sixam_mart/common/widgets/no_data_screen.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';

class BeautyRetailProductsScreen extends StatefulWidget {
  final int? salonId;
  final String? salonName;
  
  const BeautyRetailProductsScreen({
    Key? key,
    this.salonId,
    this.salonName,
  }) : super(key: key);
  
  @override
  State<BeautyRetailProductsScreen> createState() => _BeautyRetailProductsScreenState();
}

class _BeautyRetailProductsScreenState extends State<BeautyRetailProductsScreen> {
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();
  
  @override
  void initState() {
    super.initState();
    _initializeData();
    _scrollController.addListener(_scrollListener);
  }
  
  void _initializeData() {
    Get.find<BeautyRetailController>().getRetailProducts(isRefresh: true);
  }
  
  void _scrollListener() {
    if (_scrollController.position.pixels == _scrollController.position.maxScrollExtent &&
        !Get.find<BeautyRetailController>().isLoadingProducts) {
      Get.find<BeautyRetailController>().getRetailProducts();
    }
  }
  
  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.salonName ?? 'beauty_products'.tr),
        actions: [
          IconButton(
            icon: Stack(
              children: [
                const Icon(Icons.shopping_cart_outlined),
                GetBuilder<BeautyRetailController>(
                  builder: (controller) {
                    return controller.cartItemCount > 0
                        ? Positioned(
                            right: 0,
                            top: 0,
                            child: Container(
                              padding: EdgeInsets.all(2),
                              decoration: BoxDecoration(
                                color: Theme.of(context).primaryColor,
                                shape: BoxShape.circle,
                              ),
                              constraints: BoxConstraints(
                                minWidth: 16,
                                minHeight: 16,
                              ),
                              child: Text(
                                '${controller.cartItemCount}',
                                style: robotoRegular.copyWith(
                                  color: Colors.white,
                                  fontSize: 10,
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ),
                          )
                        : const SizedBox();
                  },
                ),
              ],
            ),
            onPressed: () {
              Get.toNamed(BeautyRouteHelper.getBeautyRetailCartRoute());
            },
          ),
          IconButton(
            icon: Icon(Icons.filter_list),
            onPressed: () {
              _showFilterBottomSheet();
            },
          ),
        ],
      ),
      body: GetBuilder<BeautyRetailController>(
        builder: (controller) {
          return RefreshIndicator(
            onRefresh: () async {
              await controller.getRetailProducts(isRefresh: true);
            },
            child: Column(
              children: [
                // Search Bar
                Container(
                  padding: EdgeInsets.all(Dimensions.paddingSizeDefault),
                  color: Theme.of(context).cardColor,
                  child: CustomTextField(
                    controller: _searchController,
                    hintText: 'search_products'.tr,
                    prefixIcon: Icons.search,
                    suffixChild: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear),
                            onPressed: () {
                              _searchController.clear();
                              controller.setSearchQuery('');
                            },
                          )
                        : null,
                    onChanged: (value) {
                      if (value.length > 2 || value.isEmpty) {
                        controller.setSearchQuery(value);
                      }
                    },
                  ),
                ),
                
                // Sort Options
                Container(
                  height: 40,
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    padding: EdgeInsets.symmetric(horizontal: Dimensions.paddingSizeDefault),
                    children: [
                      _buildSortChip('default', 'Default'),
                      SizedBox(width: 8),
                      _buildSortChip('price_low_to_high', 'Price: Low to High'),
                      SizedBox(width: 8),
                      _buildSortChip('price_high_to_low', 'Price: High to Low'),
                      SizedBox(width: 8),
                      _buildSortChip('rating', 'Top Rated'),
                      SizedBox(width: 8),
                      _buildSortChip('popularity', 'Most Popular'),
                      SizedBox(width: 8),
                      _buildSortChip('newest', 'Newest'),
                    ],
                  ),
                ),
                
                SizedBox(height: Dimensions.paddingSizeSmall),
                
                // Products Grid
                Expanded(
                  child: controller.retailProducts != null
                      ? controller.retailProducts!.isNotEmpty
                          ? GridView.builder(
                              controller: _scrollController,
                              padding: EdgeInsets.all(Dimensions.paddingSizeDefault),
                              gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: 2,
                                childAspectRatio: 0.7,
                                crossAxisSpacing: Dimensions.paddingSizeDefault,
                                mainAxisSpacing: Dimensions.paddingSizeDefault,
                              ),
                              itemCount: controller.retailProducts!.length +
                                  (controller.isLoadingProducts ? 2 : 0),
                              itemBuilder: (context, index) {
                                if (index < controller.retailProducts!.length) {
                                  return RetailProductCardWidget(
                                    product: controller.retailProducts![index],
                                    onTap: () {
                                      Get.toNamed(
                                        BeautyRouteHelper.getBeautyRetailProductDetailsRoute(
                                          controller.retailProducts![index].id!,
                                        ),
                                      );
                                    },
                                    onAddToCart: () {
                                      _showQuantityDialog(
                                        controller.retailProducts![index].id!,
                                      );
                                    },
                                  );
                                } else {
                                  return _buildShimmerProduct();
                                }
                              },
                            )
                          : NoDataScreen(text: 'no_products_found'.tr)
                      : _buildShimmerGrid(),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
  
  Widget _buildSortChip(String value, String label) {
    return GetBuilder<BeautyRetailController>(
      builder: (controller) {
        bool isSelected = controller.sortBy == value;
        return ChoiceChip(
          label: Text(label),
          selected: isSelected,
          onSelected: (selected) {
            if (selected) {
              controller.setSortBy(value);
            }
          },
          selectedColor: Theme.of(context).primaryColor.withValues(alpha: 0.2),
          labelStyle: TextStyle(
            color: isSelected
                ? Theme.of(context).primaryColor
                : Theme.of(context).textTheme.bodyMedium!.color,
          ),
        );
      },
    );
  }
  
  Widget _buildShimmerProduct() {
    return Container(
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
          Container(
            height: 120,
            decoration: BoxDecoration(
              color: Colors.grey[300],
              borderRadius: BorderRadius.vertical(
                top: Radius.circular(Dimensions.radiusDefault),
              ),
            ),
          ),
          Padding(
            padding: EdgeInsets.all(Dimensions.paddingSizeSmall),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  height: 15,
                  width: 100,
                  color: Colors.grey[300],
                ),
                SizedBox(height: 5),
                Container(
                  height: 12,
                  width: 80,
                  color: Colors.grey[300],
                ),
                SizedBox(height: 5),
                Container(
                  height: 20,
                  width: 60,
                  color: Colors.grey[300],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
  
  Widget _buildShimmerGrid() {
    return GridView.builder(
      padding: EdgeInsets.all(Dimensions.paddingSizeDefault),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 0.7,
        crossAxisSpacing: Dimensions.paddingSizeDefault,
        mainAxisSpacing: Dimensions.paddingSizeDefault,
      ),
      itemCount: 6,
      itemBuilder: (context, index) {
        return _buildShimmerProduct();
      },
    );
  }
  
  void _showFilterBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return RetailProductFilterWidget(
          onApply: () {
            Get.find<BeautyRetailController>().getRetailProducts(isRefresh: true);
            Get.back();
          },
        );
      },
    );
  }
  
  void _showQuantityDialog(int productId) {
    int quantity = 1;
    
    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: Text('select_quantity'.tr),
              content: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  IconButton(
                    onPressed: quantity > 1
                        ? () {
                            setState(() {
                              quantity--;
                            });
                          }
                        : null,
                    icon: Icon(Icons.remove_circle_outline),
                  ),
                  Container(
                    padding: EdgeInsets.symmetric(
                      horizontal: Dimensions.paddingSizeDefault,
                      vertical: Dimensions.paddingSizeSmall,
                    ),
                    decoration: BoxDecoration(
                      border: Border.all(color: Theme.of(context).primaryColor),
                      borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    ),
                    child: Text(
                      quantity.toString(),
                      style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge),
                    ),
                  ),
                  IconButton(
                    onPressed: () {
                      setState(() {
                        quantity++;
                      });
                    },
                    icon: Icon(Icons.add_circle_outline),
                  ),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Get.back(),
                  child: Text('cancel'.tr),
                ),
                ElevatedButton(
                  onPressed: () {
                    Get.find<BeautyRetailController>().addToCart(
                      productId,
                      quantity,
                      salonId: widget.salonId,
                    );
                    Get.back();
                  },
                  child: Text('add_to_cart'.tr),
                ),
              ],
            );
          },
        );
      },
    );
  }
}
