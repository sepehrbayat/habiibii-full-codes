import '../models/beauty_gift_card_model.dart';

abstract class BeautyGiftCardServiceInterface {
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
}
