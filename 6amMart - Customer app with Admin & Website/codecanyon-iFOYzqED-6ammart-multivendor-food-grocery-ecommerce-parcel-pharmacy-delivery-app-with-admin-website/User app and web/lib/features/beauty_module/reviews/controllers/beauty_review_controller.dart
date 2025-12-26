import 'package:get/get.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/common/models/response_model.dart';

class BeautyReviewController extends GetxController {
  final ApiClient apiClient;
  
  BeautyReviewController({required this.apiClient});

  bool isLoading = false;
  List<dynamic>? reviews;
  Map<String, dynamic>? salonReviews;
  double? averageRating;

  Future<ResponseModel> submitReview({
    required int bookingId,
    required int rating,
    required String comment,
  }) async {
    isLoading = true;
    update();
    
    try {
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautySubmitReviewUri,
        {
          'booking_id': bookingId,
          'rating': rating,
          'comment': comment,
        },
      );
      
      if (response.statusCode == 200) {
        return ResponseModel(true, 'Review submitted successfully');
      } else {
        return ResponseModel(false, response.statusText);
      }
    } catch (e) {
      return ResponseModel(false, 'Failed to submit review');
    } finally {
      isLoading = false;
      update();
    }
  }

  Future<ResponseModel> updateReview({
    required int reviewId,
    required int rating,
    required String comment,
  }) async {
    isLoading = true;
    update();
    
    try {
      Response response = await apiClient.putData(
        '${BeautyModuleConstants.beautyUpdateReviewUri}/$reviewId',
        {
          'rating': rating,
          'comment': comment,
        },
      );
      
      if (response.statusCode == 200) {
        return ResponseModel(true, 'Review updated successfully');
      } else {
        return ResponseModel(false, response.statusText);
      }
    } catch (e) {
      return ResponseModel(false, 'Failed to update review');
    } finally {
      isLoading = false;
      update();
    }
  }

  Future<ResponseModel> deleteReview(int reviewId) async {
    isLoading = true;
    update();
    
    try {
      Response response = await apiClient.deleteData(
        '${BeautyModuleConstants.beautyDeleteReviewUri}/$reviewId',
      );
      
      if (response.statusCode == 200) {
        return ResponseModel(true, 'Review deleted successfully');
      } else {
        return ResponseModel(false, response.statusText);
      }
    } catch (e) {
      return ResponseModel(false, 'Failed to delete review');
    } finally {
      isLoading = false;
      update();
    }
  }

  Future<void> getMyReviews() async {
    isLoading = true;
    update();
    
    try {
      Response response = await apiClient.getData(BeautyModuleConstants.beautySubmitReviewUri);
      if (response.statusCode == 200) {
        reviews = response.body;
      }
    } catch (e) {
      // Handle error
    } finally {
      isLoading = false;
      update();
    }
  }

  Future<void> getSalonReviews(int salonId) async {
    isLoading = true;
    update();
    
    try {
      Response response = await apiClient.getData(
        '${BeautyModuleConstants.beautySalonReviewsUri}?salon_id=$salonId',
      );
      
      if (response.statusCode == 200) {
        salonReviews = response.body;
        averageRating = (response.body['average_rating'] as num?)?.toDouble() ?? 0.0;
        reviews = response.body['reviews'];
      }
    } catch (e) {
      print('Error getting salon reviews: $e');
    } finally {
      isLoading = false;
      update();
    }
  }
}
