import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/retail/controllers/beauty_retail_controller.dart';
import 'package:sixam_mart/features/beauty_module/retail/domain/models/beauty_retail_product_model.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/common/widgets/custom_image.dart';
import 'package:sixam_mart/common/widgets/rating_bar.dart';
import 'package:sixam_mart/helper/price_converter.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';
import 'package:sixam_mart/helper/responsive_helper.dart';

class BeautyRetailProductDetailsScreen extends StatefulWidget {
  final int productId;
  
  const BeautyRetailProductDetailsScreen({
    Key? key,
    required this.productId,
  }) : super(key: key);
  
  @override
  State<BeautyRetailProductDetailsScreen> createState() => _BeautyRetailProductDetailsScreenState();
}

class _BeautyRetailProductDetailsScreenState extends State<BeautyRetailProductDetailsScreen> {
  int _quantity = 1;
  int _selectedImageIndex = 0;
  final PageController _pageController = PageController();
  
  @override
  void initState() {
    super.initState();
    Get.find<BeautyRetailController>().getProductDetails(widget.productId);
  }
  
  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('product_details'.tr),
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
        ],
      ),
      body: GetBuilder<BeautyRetailController>(
        builder: (controller) {
          BeautyRetailProductModel? product = controller.selectedProduct;
          
          if (controller.isLoading) {
            return Center(child: CircularProgressIndicator());
          }
          
          if (product == null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error_outline, size: 64, color: Colors.grey),
                  SizedBox(height: Dimensions.paddingSizeDefault),
                  Text('product_not_found'.tr, style: robotoMedium),
                ],
              ),
            );
          }
          
          bool inStock = controller.isProductInStock(product);
          double discountedPrice = product.price! - ((product.price! * (product.discount ?? 0)) / 100);
          final List<String> images = (product.images != null && product.images!.isNotEmpty)
              ? product.images!
              : (product.image != null ? [product.image!] : <String>[]);
          
          return Column(
            children: [
              Expanded(
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Product Images
                      Container(
                        height: ResponsiveHelper.isDesktop(context) ? 400 : 300,
                        child: Stack(
                          children: [
                            PageView.builder(
                              controller: _pageController,
                              itemCount: images.isNotEmpty ? images.length : 1,
                              onPageChanged: (index) {
                                setState(() {
                                  _selectedImageIndex = index;
                                });
                              },
                              itemBuilder: (context, index) {
                                String? imageUrl = images.isNotEmpty ? images[index] : null;
                                    
                                return CustomImage(
                                  image: imageUrl ?? '',
                                  fit: BoxFit.cover,
                                );
                              },
                            ),
                            
                            // Discount Badge
                            if (product.discount != null && product.discount! > 0)
                              Positioned(
                                top: Dimensions.paddingSizeDefault,
                                left: Dimensions.paddingSizeDefault,
                                child: Container(
                                  padding: EdgeInsets.symmetric(
                                    horizontal: Dimensions.paddingSizeSmall,
                                    vertical: Dimensions.paddingSizeExtraSmall,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.red,
                                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                                  ),
                                  child: Text(
                                    '${product.discount}% OFF',
                                    style: robotoMedium.copyWith(
                                      color: Colors.white,
                                      fontSize: Dimensions.fontSizeSmall,
                                    ),
                                  ),
                                ),
                              ),
                            
                            // Stock Badge
                            if (!inStock)
                              Positioned(
                                top: Dimensions.paddingSizeDefault,
                                right: Dimensions.paddingSizeDefault,
                                child: Container(
                                  padding: EdgeInsets.symmetric(
                                    horizontal: Dimensions.paddingSizeSmall,
                                    vertical: Dimensions.paddingSizeExtraSmall,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.grey,
                                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                                  ),
                                  child: Text(
                                    'out_of_stock'.tr,
                                    style: robotoMedium.copyWith(
                                      color: Colors.white,
                                      fontSize: Dimensions.fontSizeSmall,
                                    ),
                                  ),
                                ),
                              ),
                            
                            // Image Indicators
                            if (images.length > 1)
                              Positioned(
                                bottom: Dimensions.paddingSizeDefault,
                                left: 0,
                                right: 0,
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: List.generate(
                                    images.length,
                                    (index) => Container(
                                      margin: EdgeInsets.symmetric(horizontal: 2),
                                      width: _selectedImageIndex == index ? 24 : 8,
                                      height: 8,
                                      decoration: BoxDecoration(
                                        color: _selectedImageIndex == index
                                            ? Theme.of(context).primaryColor
                                            : Colors.grey.withValues(alpha: 0.5),
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                          ],
                        ),
                      ),
                      
                      // Product Info
                      Container(
                        padding: EdgeInsets.all(Dimensions.paddingSizeDefault),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Name and Rating
                            Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        product.name ?? '',
                                        style: robotoBold.copyWith(
                                          fontSize: Dimensions.fontSizeLarge,
                                        ),
                                      ),
                                      SizedBox(height: Dimensions.paddingSizeExtraSmall),
                                      if (product.brand != null)
                                        Text(
                                          product.brand!,
                                          style: robotoRegular.copyWith(
                                            color: Theme.of(context).hintColor,
                                          ),
                                        ),
                                    ],
                                  ),
                                ),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.end,
                                  children: [
                                    RatingBar(
                                      rating: product.rating ?? 0,
                                      ratingCount: product.reviewCount ?? 0,
                                      size: 16,
                                    ),
                                    SizedBox(height: 2),
                                    Text(
                                      '(${product.reviewCount ?? 0} ${'reviews'.tr})',
                                      style: robotoRegular.copyWith(
                                        fontSize: Dimensions.fontSizeSmall,
                                        color: Theme.of(context).hintColor,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            
                            SizedBox(height: Dimensions.paddingSizeDefault),
                            
                            // Price
                            Row(
                              children: [
                                Text(
                                  PriceConverter.convertPrice(discountedPrice),
                                  style: robotoBold.copyWith(
                                    fontSize: Dimensions.fontSizeExtraLarge,
                                    color: Theme.of(context).primaryColor,
                                  ),
                                ),
                                if (product.discount != null && product.discount! > 0) ...[
                                  SizedBox(width: Dimensions.paddingSizeSmall),
                                  Text(
                                    PriceConverter.convertPrice(product.price!),
                                    style: robotoRegular.copyWith(
                                      fontSize: Dimensions.fontSizeDefault,
                                      decoration: TextDecoration.lineThrough,
                                      color: Theme.of(context).hintColor,
                                    ),
                                  ),
                                ],
                              ],
                            ),
                            
                            SizedBox(height: Dimensions.paddingSizeDefault),
                            
                            // Description
                            Text(
                              'description'.tr,
                              style: robotoBold.copyWith(
                                fontSize: Dimensions.fontSizeDefault,
                              ),
                            ),
                            SizedBox(height: Dimensions.paddingSizeSmall),
                            Text(
                              product.description ?? 'No description available',
                              style: robotoRegular.copyWith(
                                color: Theme.of(context).textTheme.bodyMedium!.color,
                              ),
                            ),
                            
                            SizedBox(height: Dimensions.paddingSizeLarge),
                            
                            // Product Details
                            if (product.categoryName != null || product.unit != null || product.stock != null) ...[
                              Text(
                                'product_details'.tr,
                                style: robotoBold.copyWith(
                                  fontSize: Dimensions.fontSizeDefault,
                                ),
                              ),
                              SizedBox(height: Dimensions.paddingSizeSmall),
                              
                              if (product.categoryName != null)
                                _buildDetailRow('Category', product.categoryName!),
                              
                              if (product.unit != null)
                                _buildDetailRow('Unit', product.unit!),
                              
                              if (product.stock != null)
                                _buildDetailRow(
                                  'Stock',
                                  product.stock! > 0
                                      ? '${product.stock} available'
                                      : 'Out of stock',
                                ),
                              
                              if (product.sku != null)
                                _buildDetailRow('SKU', product.sku!),
                            ],
                            
                            SizedBox(height: Dimensions.paddingSizeLarge),
                            
                            // Related Products
                            if (controller.recommendedProducts != null &&
                                controller.recommendedProducts!.isNotEmpty) ...[
                              Text(
                                'related_products'.tr,
                                style: robotoBold.copyWith(
                                  fontSize: Dimensions.fontSizeDefault,
                                ),
                              ),
                              SizedBox(height: Dimensions.paddingSizeDefault),
                              Container(
                                height: 200,
                                child: ListView.builder(
                                  scrollDirection: Axis.horizontal,
                                  itemCount: controller.recommendedProducts!.length,
                                  itemBuilder: (context, index) {
                                    BeautyRetailProductModel relatedProduct =
                                        controller.recommendedProducts![index];
                                    return Container(
                                      width: 150,
                                      margin: EdgeInsets.only(
                                        right: Dimensions.paddingSizeDefault,
                                      ),
                                      child: InkWell(
                                        onTap: () {
                                          Get.off(() => BeautyRetailProductDetailsScreen(
                                            productId: relatedProduct.id!,
                                          ));
                                        },
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Container(
                                              height: 120,
                                              decoration: BoxDecoration(
                                                borderRadius: BorderRadius.circular(
                                                  Dimensions.radiusDefault,
                                                ),
                                              ),
                                              child: ClipRRect(
                                                borderRadius: BorderRadius.circular(
                                                  Dimensions.radiusDefault,
                                                ),
                                                child: CustomImage(
                                                  image: relatedProduct.image ?? (relatedProduct.images != null && relatedProduct.images!.isNotEmpty ? relatedProduct.images!.first : ''),
                                                  fit: BoxFit.cover,
                                                ),
                                              ),
                                            ),
                                            SizedBox(height: Dimensions.paddingSizeSmall),
                                            Text(
                                              relatedProduct.name ?? '',
                                              style: robotoMedium,
                                              maxLines: 2,
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                            SizedBox(height: 2),
                                            Text(
                                              PriceConverter.convertPrice(
                                                relatedProduct.price ?? 0,
                                              ),
                                              style: robotoBold.copyWith(
                                                color: Theme.of(context).primaryColor,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              
              // Bottom Bar
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
                  child: Row(
                    children: [
                      // Quantity Selector
                      Container(
                        decoration: BoxDecoration(
                          border: Border.all(
                            color: Theme.of(context).primaryColor.withValues(alpha: 0.3),
                          ),
                          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
                        ),
                        child: Row(
                          children: [
                            InkWell(
                              onTap: _quantity > 1
                                  ? () {
                                      setState(() {
                                        _quantity--;
                                      });
                                    }
                                  : null,
                              child: Container(
                                padding: EdgeInsets.all(Dimensions.paddingSizeSmall),
                                child: Icon(
                                  Icons.remove,
                                  size: 20,
                                  color: _quantity > 1
                                      ? Theme.of(context).primaryColor
                                      : Theme.of(context).disabledColor,
                                ),
                              ),
                            ),
                            Container(
                              padding: EdgeInsets.symmetric(
                                horizontal: Dimensions.paddingSizeDefault,
                              ),
                              child: Text(
                                _quantity.toString(),
                                style: robotoBold.copyWith(
                                  fontSize: Dimensions.fontSizeLarge,
                                ),
                              ),
                            ),
                            InkWell(
                              onTap: inStock
                                  ? () {
                                      setState(() {
                                        _quantity++;
                                      });
                                    }
                                  : null,
                              child: Container(
                                padding: EdgeInsets.all(Dimensions.paddingSizeSmall),
                                child: Icon(
                                  Icons.add,
                                  size: 20,
                                  color: inStock
                                      ? Theme.of(context).primaryColor
                                      : Theme.of(context).disabledColor,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      
                      SizedBox(width: Dimensions.paddingSizeDefault),
                      
                      // Add to Cart Button
                      Expanded(
                        child: CustomButton(
                          buttonText: 'add_to_cart'.tr,
                          onPressed: inStock
                              ? () {
                                  controller.addToCart(
                                    widget.productId,
                                    _quantity,
                                    salonId: product.salonId,
                                  );
                                }
                              : null,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
  
  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: Dimensions.paddingSizeExtraSmall),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            '$label: ',
            style: robotoRegular.copyWith(
              color: Theme.of(context).hintColor,
            ),
          ),
          const SizedBox(width: Dimensions.paddingSizeExtraSmall),
          Expanded(
            child: Text(
              value,
              style: robotoMedium,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              softWrap: true,
            ),
          ),
        ],
      ),
    );
  }
}
