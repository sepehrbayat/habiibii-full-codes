import 'package:get/get.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';
import '../models/beauty_review_model.dart';
import 'beauty_review_repository_interface.dart';

class BeautyReviewRepository implements BeautyReviewRepositoryInterface {
  final ApiClient apiClient;

  BeautyReviewRepository({required this.apiClient});

  @override
  Future<List<BeautyReviewModel>> getSalonReviews({
    required int salonId,
    int? offset,
    int? limit,
  }) async {
    Response response = await apiClient.getData(
      '${BeautyModuleConstants.beautySalonReviewsUri}/$salonId',
      query: {
        if (offset != null) 'offset': offset.toString(),
        if (limit != null) 'limit': limit.toString(),
      },
    );
    if (response.statusCode == 200) {
      List<BeautyReviewModel> reviews = [];
      response.body['reviews']?.forEach((review) {
        reviews.add(BeautyReviewModel.fromJson(review));
      });
      return reviews;
    }
    return [];
  }

  @override
  Future<List<BeautyReviewModel>> getServiceReviews({
    required int serviceId,
    int? offset,
    int? limit,
  }) async {
    return [];
  }

  @override
  Future<List<BeautyReviewModel>> getMyReviews() async {
    Response response = await apiClient.getData(BeautyModuleConstants.beautySubmitReviewUri);
    if (response.statusCode == 200) {
      List<BeautyReviewModel> reviews = [];
      response.body['reviews']?.forEach((review) {
        reviews.add(BeautyReviewModel.fromJson(review));
      });
      return reviews;
    }
    return [];
  }

  @override
  Future<bool> submitReview({
    required int bookingId,
    required int rating,
    required String comment,
    List<String>? images,
    Map<String, int>? categoryRatings,
  }) async {
    Response response = await apiClient.postData(
      BeautyModuleConstants.beautySubmitReviewUri,
      {
        'booking_id': bookingId,
        'rating': rating,
        'comment': comment,
        'images': images,
        'category_ratings': categoryRatings,
      },
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> updateReview({
    required int reviewId,
    required int rating,
    required String comment,
    List<String>? images,
  }) async {
    Response response = await apiClient.putData(
      '${BeautyModuleConstants.beautyUpdateReviewUri}/$reviewId',
      {
        'rating': rating,
        'comment': comment,
        'images': images,
      },
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> deleteReview(int reviewId) async {
    Response response = await apiClient.deleteData(
      '${BeautyModuleConstants.beautyDeleteReviewUri}/$reviewId',
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> reportReview(int reviewId, String reason) async {
    return false;
  }

  @override
  Future<Map<String, dynamic>> getReviewStats(int salonId) async {
    return {};
  }
}
