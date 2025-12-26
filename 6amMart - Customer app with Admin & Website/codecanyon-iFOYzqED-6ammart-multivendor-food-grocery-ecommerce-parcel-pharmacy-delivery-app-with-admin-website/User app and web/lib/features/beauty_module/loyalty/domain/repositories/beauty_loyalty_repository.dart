import 'package:get/get.dart';
import 'beauty_loyalty_repository_interface.dart';
import '../models/beauty_loyalty_point_model.dart';
import '../models/beauty_loyalty_campaign_model.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';

class BeautyLoyaltyRepository implements BeautyLoyaltyRepositoryInterface {
  final ApiClient apiClient;
  
  BeautyLoyaltyRepository({required this.apiClient});
  
  @override
  Future<BeautyLoyaltyPointModel?> getLoyaltyPoints() async {
    try {
      Response response = await apiClient.getData(BeautyModuleConstants.beautyLoyaltyPointsUri);
      if (response.statusCode == 200) {
        return BeautyLoyaltyPointModel.fromJson(response.body['loyalty']);
      }
      return null;
    } catch (e) {
      print('Error getting loyalty points: \$e');
      return null;
    }
  }
  
  @override
  Future<List<Map<String, dynamic>>> getPointsHistory({
    int? offset,
    int? limit,
  }) async {
    try {
      return [];
    } catch (e) {
      print('Error getting points history: \$e');
      return [];
    }
  }
  
  @override
  Future<List<BeautyLoyaltyCampaignModel>> getActiveCampaigns() async {
    try {
      Response response = await apiClient.getData(BeautyModuleConstants.beautyLoyaltyCampaignsUri);
      if (response.statusCode == 200) {
        List<BeautyLoyaltyCampaignModel> campaigns = [];
        response.body['campaigns'].forEach((campaign) {
          campaigns.add(BeautyLoyaltyCampaignModel.fromJson(campaign));
        });
        return campaigns;
      }
      return [];
    } catch (e) {
      print('Error getting campaigns: \$e');
      return [];
    }
  }
  
  @override
  Future<bool> redeemPoints({
    required int points,
    required String rewardType,
    int? rewardId,
  }) async {
    try {
      Map<String, dynamic> data = {
        'points': points,
        'reward_type': rewardType,
      };
      if (rewardId != null) data['reward_id'] = rewardId;
      
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyRedeemPointsUri,
        data,
      );
      return response.statusCode == 200;
    } catch (e) {
      print('Error redeeming points: \$e');
      return false;
    }
  }
  
  @override
  Future<Map<String, dynamic>?> convertPointsToCredit(int points) async {
    try {
      return null;
    } catch (e) {
      print('Error converting points: \$e');
      return null;
    }
  }
  
  @override
  Future<List<Map<String, dynamic>>> getAvailableRewards() async {
    try {
      return [];
    } catch (e) {
      print('Error getting rewards: \$e');
      return [];
    }
  }
  
  @override
  Future<bool> joinCampaign(int campaignId) async {
    try {
      return false;
    } catch (e) {
      print('Error joining campaign: \$e');
      return false;
    }
  }
}
