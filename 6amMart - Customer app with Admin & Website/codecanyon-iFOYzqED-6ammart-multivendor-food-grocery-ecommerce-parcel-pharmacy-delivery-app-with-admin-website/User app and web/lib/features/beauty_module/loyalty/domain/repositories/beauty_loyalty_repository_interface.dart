import '../models/beauty_loyalty_point_model.dart';
import '../models/beauty_loyalty_campaign_model.dart';

abstract class BeautyLoyaltyRepositoryInterface {
  Future<BeautyLoyaltyPointModel?> getLoyaltyPoints();
  Future<List<Map<String, dynamic>>> getPointsHistory({
    int? offset,
    int? limit,
  });
  Future<List<BeautyLoyaltyCampaignModel>> getActiveCampaigns();
  Future<bool> redeemPoints({
    required int points,
    required String rewardType,
    int? rewardId,
  });
  Future<Map<String, dynamic>?> convertPointsToCredit(int points);
  Future<List<Map<String, dynamic>>> getAvailableRewards();
  Future<bool> joinCampaign(int campaignId);
}
