import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/retail/controllers/beauty_retail_controller.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/helper/price_converter.dart';

class RetailProductFilterWidget extends StatefulWidget {
  final VoidCallback onApply;
  
  const RetailProductFilterWidget({
    Key? key,
    required this.onApply,
  }) : super(key: key);
  
  @override
  State<RetailProductFilterWidget> createState() => _RetailProductFilterWidgetState();
}

class _RetailProductFilterWidgetState extends State<RetailProductFilterWidget> {
  late RangeValues _priceRange;
  int? _selectedCategoryId;
  String _selectedSortBy = 'default';
  
  final List<Map<String, dynamic>> _categories = [
    {'id': 1, 'name': 'Skincare', 'icon': Icons.face},
    {'id': 2, 'name': 'Haircare', 'icon': Icons.face_retouching_natural},
    {'id': 3, 'name': 'Makeup', 'icon': Icons.brush},
    {'id': 4, 'name': 'Fragrance', 'icon': Icons.local_florist},
    {'id': 5, 'name': 'Tools', 'icon': Icons.construction},
    {'id': 6, 'name': 'Bath & Body', 'icon': Icons.bathtub},
  ];
  
  final List<Map<String, String>> _sortOptions = [
    {'value': 'default', 'label': 'Default'},
    {'value': 'price_low_to_high', 'label': 'Price: Low to High'},
    {'value': 'price_high_to_low', 'label': 'Price: High to Low'},
    {'value': 'rating', 'label': 'Top Rated'},
    {'value': 'popularity', 'label': 'Most Popular'},
    {'value': 'newest', 'label': 'Newest First'},
    {'value': 'name_a_to_z', 'label': 'Name: A to Z'},
    {'value': 'name_z_to_a', 'label': 'Name: Z to A'},
  ];
  
  @override
  void initState() {
    super.initState();
    BeautyRetailController controller = Get.find<BeautyRetailController>();
    _priceRange = RangeValues(controller.minPrice, controller.maxPrice);
    _selectedCategoryId = controller.selectedCategoryId;
    _selectedSortBy = controller.sortBy;
  }
  
  @override
  Widget build(BuildContext context) {
    return Container(
      height: MediaQuery.of(context).size.height * 0.85,
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.vertical(
          top: Radius.circular(Dimensions.radiusLarge),
        ),
      ),
      child: Column(
        children: [
          // Handle
          Container(
            margin: EdgeInsets.only(top: Dimensions.paddingSizeDefault),
            width: 40,
            height: 5,
            decoration: BoxDecoration(
              color: Theme.of(context).disabledColor.withValues(alpha: 0.3),
              borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
            ),
          ),
          
          // Title
          Container(
            padding: EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'filter_products'.tr,
                  style: robotoBold.copyWith(fontSize: Dimensions.fontSizeLarge),
                ),
                TextButton(
                  onPressed: _clearFilters,
                  child: Text(
                    'clear_all'.tr,
                    style: robotoRegular.copyWith(
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                ),
              ],
            ),
          ),
          
          Divider(height: 1),
          
          // Filter Content
          Expanded(
            child: SingleChildScrollView(
              padding: EdgeInsets.all(Dimensions.paddingSizeDefault),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Categories
                  Text(
                    'categories'.tr,
                    style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                  ),
                  SizedBox(height: Dimensions.paddingSizeDefault),
                  
                  GridView.builder(
                    shrinkWrap: true,
                    physics: NeverScrollableScrollPhysics(),
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 3,
                      childAspectRatio: 1.2,
                      crossAxisSpacing: Dimensions.paddingSizeSmall,
                      mainAxisSpacing: Dimensions.paddingSizeSmall,
                    ),
                    itemCount: _categories.length,
                    itemBuilder: (context, index) {
                      bool isSelected = _selectedCategoryId == _categories[index]['id'];
                      return InkWell(
                        onTap: () {
                          setState(() {
                            if (_selectedCategoryId == _categories[index]['id']) {
                              _selectedCategoryId = null;
                            } else {
                              _selectedCategoryId = _categories[index]['id'];
                            }
                          });
                        },
                        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                        child: Container(
                          decoration: BoxDecoration(
                            color: isSelected
                                ? Theme.of(context).primaryColor.withValues(alpha: 0.1)
                                : Theme.of(context).cardColor,
                            border: Border.all(
                              color: isSelected
                                  ? Theme.of(context).primaryColor
                                  : Theme.of(context).disabledColor.withValues(alpha: 0.3),
                              width: isSelected ? 2 : 1,
                            ),
                            borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                _categories[index]['icon'],
                                color: isSelected
                                    ? Theme.of(context).primaryColor
                                    : Theme.of(context).textTheme.bodyMedium!.color,
                                size: 24,
                              ),
                              SizedBox(height: Dimensions.paddingSizeExtraSmall),
                              Text(
                                _categories[index]['name'],
                                style: robotoRegular.copyWith(
                                  fontSize: Dimensions.fontSizeSmall,
                                  color: isSelected
                                      ? Theme.of(context).primaryColor
                                      : Theme.of(context).textTheme.bodyMedium!.color,
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                  
                  SizedBox(height: Dimensions.paddingSizeLarge),
                  
                  // Price Range
                  Text(
                    'price_range'.tr,
                    style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                  ),
                  SizedBox(height: Dimensions.paddingSizeDefault),
                  
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        PriceConverter.convertPrice(_priceRange.start),
                        style: robotoMedium,
                      ),
                      Text(
                        PriceConverter.convertPrice(_priceRange.end),
                        style: robotoMedium,
                      ),
                    ],
                  ),
                  
                  RangeSlider(
                    values: _priceRange,
                    min: 0,
                    max: 10000,
                    divisions: 100,
                    activeColor: Theme.of(context).primaryColor,
                    inactiveColor: Theme.of(context).disabledColor.withValues(alpha: 0.3),
                    onChanged: (RangeValues values) {
                      setState(() {
                        _priceRange = values;
                      });
                    },
                  ),
                  
                  SizedBox(height: Dimensions.paddingSizeLarge),
                  
                  // Sort By
                  Text(
                    'sort_by'.tr,
                    style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                  ),
                  SizedBox(height: Dimensions.paddingSizeDefault),
                  
                  Wrap(
                    spacing: Dimensions.paddingSizeSmall,
                    runSpacing: Dimensions.paddingSizeSmall,
                    children: _sortOptions.map((option) {
                      bool isSelected = _selectedSortBy == option['value'];
                      return ChoiceChip(
                        label: Text(option['label']!),
                        selected: isSelected,
                        onSelected: (selected) {
                          setState(() {
                            _selectedSortBy = option['value']!;
                          });
                        },
                        selectedColor: Theme.of(context).primaryColor.withValues(alpha: 0.2),
                        labelStyle: TextStyle(
                          color: isSelected
                              ? Theme.of(context).primaryColor
                              : Theme.of(context).textTheme.bodyMedium!.color,
                        ),
                      );
                    }).toList(),
                  ),
                  
                  SizedBox(height: Dimensions.paddingSizeLarge),
                  
                  // Additional Filters
                  Text(
                    'additional_filters'.tr,
                    style: robotoBold.copyWith(fontSize: Dimensions.fontSizeDefault),
                  ),
                  SizedBox(height: Dimensions.paddingSizeDefault),
                  
                  _buildFilterOption(
                    'In Stock Only',
                    'Show only products that are currently in stock',
                    Icons.inventory,
                  ),
                  
                  _buildFilterOption(
                    'On Sale',
                    'Show only discounted products',
                    Icons.local_offer,
                  ),
                  
                  _buildFilterOption(
                    'Top Rated',
                    'Show products with 4+ star ratings',
                    Icons.star,
                  ),
                  
                  _buildFilterOption(
                    'New Arrivals',
                    'Show products added in last 30 days',
                    Icons.new_releases,
                  ),
                ],
              ),
            ),
          ),
          
          // Apply Button
          Container(
            padding: EdgeInsets.all(Dimensions.paddingSizeDefault),
            decoration: BoxDecoration(
              color: Theme.of(context).cardColor,
              boxShadow: [
                BoxShadow(
                  color: Colors.grey.withValues(alpha: 0.1),
                  spreadRadius: 1,
                  blurRadius: 5,
                  offset: Offset(0, -2),
                ),
              ],
            ),
            child: SafeArea(
              child: CustomButton(
                buttonText: 'apply_filters'.tr,
                onPressed: () {
                  BeautyRetailController controller = Get.find<BeautyRetailController>();
                  controller.setCategory(_selectedCategoryId);
                  controller.setPriceRange(_priceRange.start, _priceRange.end);
                  controller.setSortBy(_selectedSortBy);
                  widget.onApply();
                },
              ),
            ),
          ),
        ],
      ),
    );
  }
  
  Widget _buildFilterOption(String title, String subtitle, IconData icon) {
    return Container(
      margin: EdgeInsets.only(bottom: Dimensions.paddingSizeDefault),
      child: InkWell(
        onTap: () {
          // Toggle filter option
        },
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        child: Container(
          padding: EdgeInsets.all(Dimensions.paddingSizeDefault),
          decoration: BoxDecoration(
            border: Border.all(
              color: Theme.of(context).disabledColor.withValues(alpha: 0.3),
            ),
            borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          ),
          child: Row(
            children: [
              Icon(
                icon,
                color: Theme.of(context).primaryColor,
                size: 24,
              ),
              SizedBox(width: Dimensions.paddingSizeDefault),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: robotoMedium,
                    ),
                    SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: robotoRegular.copyWith(
                        fontSize: Dimensions.fontSizeSmall,
                        color: Theme.of(context).hintColor,
                      ),
                    ),
                  ],
                ),
              ),
              Switch(
                value: false,
                onChanged: (value) {
                  // Handle switch change
                },
                activeColor: Theme.of(context).primaryColor,
              ),
            ],
          ),
        ),
      ),
    );
  }
  
  void _clearFilters() {
    setState(() {
      _priceRange = RangeValues(0, 10000);
      _selectedCategoryId = null;
      _selectedSortBy = 'default';
    });
  }
}
