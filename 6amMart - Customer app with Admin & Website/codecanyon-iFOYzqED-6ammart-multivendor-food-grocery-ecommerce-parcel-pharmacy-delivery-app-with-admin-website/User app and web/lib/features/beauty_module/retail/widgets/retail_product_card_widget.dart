import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/retail/domain/models/beauty_retail_product_model.dart';
import 'package:sixam_mart/features/beauty_module/retail/controllers/beauty_retail_controller.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_image.dart';
import 'package:sixam_mart/common/widgets/rating_bar.dart';
import 'package:sixam_mart/helper/price_converter.dart';

class RetailProductCardWidget extends StatelessWidget {
  final BeautyRetailProductModel product;
  final VoidCallback onTap;
  final VoidCallback onAddToCart;
  
  const RetailProductCardWidget({
    Key? key,
    required this.product,
    required this.onTap,
    required this.onAddToCart,
  }) : super(key: key);
  
  @override
  Widget build(BuildContext context) {
    double discountedPrice = product.price! - ((product.price! * (product.discount ?? 0)) / 100);
    bool inStock = Get.find<BeautyRetailController>().isProductInStock(product);
    
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      child: Container(
        decoration: BoxDecoration(
          color: Theme.of(context).cardColor,
          borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          boxShadow: [
            BoxShadow(
              color: Colors.grey.withValues(alpha: 0.1),
              spreadRadius: 1,
              blurRadius: 5,
              offset: Offset(0, 1),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Product Image
            Expanded(
              flex: 3,
              child: Stack(
                children: [
                  Container(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.vertical(
                        top: Radius.circular(Dimensions.radiusDefault),
                      ),
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.vertical(
                        top: Radius.circular(Dimensions.radiusDefault),
                      ),
                      child: CustomImage(
                        image: product.image ?? (product.images != null && product.images!.isNotEmpty ? product.images!.first : ''),
                        fit: BoxFit.cover,
                        width: double.infinity,
                        height: double.infinity,
                      ),
                    ),
                  ),
                  
                  // Discount Badge
                  if (product.discount != null && product.discount! > 0)
                    Positioned(
                      top: Dimensions.paddingSizeSmall,
                      left: Dimensions.paddingSizeSmall,
                      child: Container(
                        padding: EdgeInsets.symmetric(
                          horizontal: Dimensions.paddingSizeExtraSmall,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.red,
                          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                        ),
                        child: Text(
                          '${product.discount}% OFF',
                          style: robotoMedium.copyWith(
                            color: Colors.white,
                            fontSize: Dimensions.fontSizeExtraSmall,
                          ),
                        ),
                      ),
                    ),
                  
                  // Out of Stock Overlay
                  if (!inStock)
                    Positioned.fill(
                      child: Container(
                        decoration: BoxDecoration(
                          color: Colors.black.withValues(alpha: 0.5),
                          borderRadius: BorderRadius.vertical(
                            top: Radius.circular(Dimensions.radiusDefault),
                          ),
                        ),
                        child: Center(
                          child: Container(
                            padding: EdgeInsets.symmetric(
                              horizontal: Dimensions.paddingSizeSmall,
                              vertical: Dimensions.paddingSizeExtraSmall,
                            ),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                            ),
                            child: Text(
                              'out_of_stock'.tr,
                              style: robotoBold.copyWith(
                                color: Colors.red,
                                fontSize: Dimensions.fontSizeSmall,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  
                  // Add to Cart Button
                  Positioned(
                    bottom: Dimensions.paddingSizeSmall,
                    right: Dimensions.paddingSizeSmall,
                    child: InkWell(
                      onTap: inStock ? onAddToCart : null,
                      child: Container(
                        padding: EdgeInsets.all(Dimensions.paddingSizeExtraSmall),
                        decoration: BoxDecoration(
                          color: inStock
                              ? Theme.of(context).primaryColor
                              : Theme.of(context).disabledColor,
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.1),
                              spreadRadius: 1,
                              blurRadius: 3,
                            ),
                          ],
                        ),
                        child: Icon(
                          Icons.add_shopping_cart,
                          color: Colors.white,
                          size: 18,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            
            // Product Info
            Expanded(
              flex: 2,
              child: Padding(
                padding: EdgeInsets.all(Dimensions.paddingSizeSmall),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    // Product Name
                    Text(
                      product.name ?? '',
                      style: robotoMedium.copyWith(
                        fontSize: Dimensions.fontSizeSmall,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    
                    // Brand
                    if (product.brand != null)
                      Text(
                        product.brand!,
                        style: robotoRegular.copyWith(
                          fontSize: Dimensions.fontSizeExtraSmall,
                          color: Theme.of(context).hintColor,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    
                    // Rating
                    Row(
                      children: [
                        RatingBar(
                          rating: product.rating ?? 0,
                          ratingCount: product.reviewCount ?? 0,
                          size: 12,
                        ),
                        SizedBox(width: Dimensions.paddingSizeExtraSmall),
                        Text(
                          '(${product.reviewCount ?? 0})',
                          style: robotoRegular.copyWith(
                            fontSize: Dimensions.fontSizeExtraSmall,
                            color: Theme.of(context).hintColor,
                          ),
                        ),
                      ],
                    ),
                    
                    // Price
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                PriceConverter.convertPrice(discountedPrice),
                                style: robotoBold.copyWith(
                                  fontSize: Dimensions.fontSizeDefault,
                                  color: Theme.of(context).primaryColor,
                                ),
                              ),
                              if (product.discount != null && product.discount! > 0)
                                Text(
                                  PriceConverter.convertPrice(product.price!),
                                  style: robotoRegular.copyWith(
                                    fontSize: Dimensions.fontSizeExtraSmall,
                                    decoration: TextDecoration.lineThrough,
                                    color: Theme.of(context).hintColor,
                                  ),
                                ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
