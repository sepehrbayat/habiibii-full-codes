import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/reviews/controllers/beauty_review_controller.dart';
import 'package:sixam_mart/features/beauty_module/reviews/widgets/review_rating_widget.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';

class BeautyReviewsScreen extends StatelessWidget {
  final int? salonId;

  const BeautyReviewsScreen({super.key, this.salonId});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<BeautyReviewController>();
    if (salonId != null) {
      controller.getSalonReviews(salonId!);
    } else {
      controller.getMyReviews();
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Reviews')),
      body: GetBuilder<BeautyReviewController>(
        builder: (controller) {
          if (controller.isLoading && controller.reviews == null) {
            return const Center(child: CircularProgressIndicator());
          }

          return ListView(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            children: [
              ...?controller.reviews?.map((review) {
                return Card(
                  child: ListTile(
                    title: Text(review.userName ?? 'User'),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        ReviewRatingWidget(rating: review.rating ?? 0),
                        const SizedBox(height: 4),
                        Text(review.comment ?? ''),
                      ],
                    ),
                  ),
                );
              }).toList(),
            ],
          );
        },
      ),
      floatingActionButton: salonId == null
          ? FloatingActionButton(
              onPressed: () => Get.toNamed(
                BeautyRouteHelper.getBeautyReviewCreateRoute(salonId: salonId),
              ),
              child: const Icon(Icons.add),
            )
          : null,
    );
  }
}
