import '../models/beauty_loyalty_point_model.dart';
import '../models/beauty_loyalty_campaign_model.dart';

abstract class BeautyLoyaltyServiceInterface {
  Future<BeautyLoyaltyPointModel?> getLoyaltyPoints();
  Future<List<BeautyLoyaltyCampaignModel>> getActiveCampaigns();
  Future<bool> redeemPoints({
    required int points,
    required String rewardType,
    int? rewardId,
  });
}
