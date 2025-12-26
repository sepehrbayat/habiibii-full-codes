import 'package:get/get.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/common/models/response_model.dart';

class BeautyLoyaltyController extends GetxController {
  final ApiClient apiClient;
  
  BeautyLoyaltyController({required this.apiClient});

  bool isLoading = false;
  int? points;
  List<dynamic>? transactions;
  List<dynamic>? campaigns;
  Map<String, dynamic>? redeemOptions;

  Future<void> getLoyaltyPoints() async {
    isLoading = true;
    update();
    
    try {
      Response response = await apiClient.getData(
        BeautyModuleConstants.beautyLoyaltyPointsUri,
      );
      
      if (response.statusCode == 200) {
        points = response.body['points'] as int?;
        transactions = response.body['transactions'] as List<dynamic>?;
      }
    } catch (e) {
      print('Error getting loyalty points: $e');
    } finally {
      isLoading = false;
      update();
    }
  }

  Future<void> getLoyaltyCampaigns() async {
    isLoading = true;
    update();
    
    try {
      Response response = await apiClient.getData(
        BeautyModuleConstants.beautyLoyaltyCampaignsUri,
      );
      
      if (response.statusCode == 200) {
        campaigns = response.body['campaigns'] as List<dynamic>?;
      }
    } catch (e) {
      print('Error getting loyalty campaigns: $e');
    } finally {
      isLoading = false;
      update();
    }
  }

  Future<ResponseModel> redeemPoints({
    required int points,
    required String rewardType,
    Map<String, dynamic>? details,
  }) async {
    isLoading = true;
    update();
    
    try {
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyRedeemPointsUri,
        {
          'points': points,
          'reward_type': rewardType,
          'details': details,
        },
      );
      
      if (response.statusCode == 200) {
        // Update points after redemption
        getLoyaltyPoints();
        return ResponseModel(true, 'Points redeemed successfully');
      } else {
        return ResponseModel(false, response.body['message'] ?? 'Failed to redeem points');
      }
    } catch (e) {
      return ResponseModel(false, 'Failed to redeem points');
    } finally {
      isLoading = false;
      update();
    }
  }
}
