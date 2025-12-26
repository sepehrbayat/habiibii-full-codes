import '../models/beauty_gift_card_model.dart';
import '../repositories/beauty_gift_card_repository_interface.dart';
import 'beauty_gift_card_service_interface.dart';

class BeautyGiftCardService implements BeautyGiftCardServiceInterface {
  final BeautyGiftCardRepositoryInterface giftCardRepository;

  BeautyGiftCardService({required this.giftCardRepository});

  @override
  Future<List<BeautyGiftCardModel>> getAvailableGiftCards() async {
    return await giftCardRepository.getAvailableGiftCards();
  }

  @override
  Future<List<BeautyGiftCardModel>> getMyGiftCards() async {
    return await giftCardRepository.getMyGiftCards();
  }

  @override
  Future<BeautyGiftCardModel?> getGiftCardDetails(int cardId) async {
    return await giftCardRepository.getGiftCardDetails(cardId);
  }

  @override
  Future<Map<String, dynamic>?> purchaseGiftCard({
    required double amount,
    required String recipientEmail,
    String? recipientName,
    String? message,
    String? paymentMethod,
  }) async {
    return await giftCardRepository.purchaseGiftCard(
      amount: amount,
      recipientEmail: recipientEmail,
      recipientName: recipientName,
      message: message,
      paymentMethod: paymentMethod,
    );
  }

  @override
  Future<bool> redeemGiftCard(String code) async {
    return await giftCardRepository.redeemGiftCard(code);
  }
}
