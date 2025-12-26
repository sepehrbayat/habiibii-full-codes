import '../models/beauty_gift_card_model.dart';

abstract class BeautyGiftCardRepositoryInterface {
  Future<List<BeautyGiftCardModel>> getAvailableGiftCards();
  Future<List<BeautyGiftCardModel>> getMyGiftCards();
  Future<BeautyGiftCardModel?> getGiftCardDetails(int cardId);
  Future<Map<String, dynamic>?> purchaseGiftCard({
    required double amount,
    required String recipientEmail,
    String? recipientName,
    String? message,
    String? paymentMethod,
  });
  Future<bool> redeemGiftCard(String code);
  Future<double> checkGiftCardBalance(String code);
  Future<bool> transferGiftCard({
    required int cardId,
    required String recipientEmail,
  });
  Future<List<Map<String, dynamic>>> getGiftCardHistory(int cardId);
}
