import '../models/beauty_review_model.dart';

abstract class BeautyReviewServiceInterface {
  Future<List<BeautyReviewModel>> getSalonReviews({
    required int salonId,
    int? offset,
    int? limit,
  });
  Future<List<BeautyReviewModel>> getMyReviews();
  Future<bool> submitReview({
    required int bookingId,
    required int rating,
    required String comment,
  });
}
