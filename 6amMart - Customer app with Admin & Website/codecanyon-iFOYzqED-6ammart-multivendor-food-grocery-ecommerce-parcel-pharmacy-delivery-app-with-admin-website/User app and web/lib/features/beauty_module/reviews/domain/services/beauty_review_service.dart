import '../models/beauty_review_model.dart';
import '../repositories/beauty_review_repository_interface.dart';
import 'beauty_review_service_interface.dart';

class BeautyReviewService implements BeautyReviewServiceInterface {
  final BeautyReviewRepositoryInterface reviewRepository;

  BeautyReviewService({required this.reviewRepository});

  @override
  Future<List<BeautyReviewModel>> getSalonReviews({
    required int salonId,
    int? offset,
    int? limit,
  }) async {
    return await reviewRepository.getSalonReviews(
      salonId: salonId,
      offset: offset,
      limit: limit,
    );
  }

  @override
  Future<List<BeautyReviewModel>> getMyReviews() async {
    return await reviewRepository.getMyReviews();
  }

  @override
  Future<bool> submitReview({
    required int bookingId,
    required int rating,
    required String comment,
  }) async {
    return await reviewRepository.submitReview(
      bookingId: bookingId,
      rating: rating,
      comment: comment,
    );
  }
}
