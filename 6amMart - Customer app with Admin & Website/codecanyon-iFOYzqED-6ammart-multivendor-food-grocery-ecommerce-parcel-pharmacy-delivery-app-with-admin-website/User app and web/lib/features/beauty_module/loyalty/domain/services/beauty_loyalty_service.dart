import '../models/beauty_loyalty_point_model.dart';
import '../models/beauty_loyalty_campaign_model.dart';
import '../repositories/beauty_loyalty_repository_interface.dart';
import 'beauty_loyalty_service_interface.dart';

class BeautyLoyaltyService implements BeautyLoyaltyServiceInterface {
  final BeautyLoyaltyRepositoryInterface loyaltyRepository;

  BeautyLoyaltyService({required this.loyaltyRepository});

  @override
  Future<BeautyLoyaltyPointModel?> getLoyaltyPoints() async {
    return await loyaltyRepository.getLoyaltyPoints();
  }

  @override
  Future<List<BeautyLoyaltyCampaignModel>> getActiveCampaigns() async {
    return await loyaltyRepository.getActiveCampaigns();
  }

  @override
  Future<bool> redeemPoints({
    required int points,
    required String rewardType,
    int? rewardId,
  }) async {
    return await loyaltyRepository.redeemPoints(
      points: points,
      rewardType: rewardType,
      rewardId: rewardId,
    );
  }
}
