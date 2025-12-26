import '../models/beauty_review_model.dart';

abstract class BeautyReviewRepositoryInterface {
  Future<List<BeautyReviewModel>> getSalonReviews({
    required int salonId,
    int? offset,
    int? limit,
  });
  Future<List<BeautyReviewModel>> getServiceReviews({
    required int serviceId,
    int? offset,
    int? limit,
  });
  Future<List<BeautyReviewModel>> getMyReviews();
  Future<bool> submitReview({
    required int bookingId,
    required int rating,
    required String comment,
    List<String>? images,
    Map<String, int>? categoryRatings,
  });
  Future<bool> updateReview({
    required int reviewId,
    required int rating,
    required String comment,
    List<String>? images,
  });
  Future<bool> deleteReview(int reviewId);
  Future<bool> reportReview(int reviewId, String reason);
  Future<Map<String, dynamic>> getReviewStats(int salonId);
}
