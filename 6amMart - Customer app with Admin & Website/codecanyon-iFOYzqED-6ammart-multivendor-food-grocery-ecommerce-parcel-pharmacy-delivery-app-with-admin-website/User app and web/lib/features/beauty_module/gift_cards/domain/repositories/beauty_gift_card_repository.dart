import 'package:get/get.dart';
import 'beauty_gift_card_repository_interface.dart';
import '../models/beauty_gift_card_model.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';

class BeautyGiftCardRepository implements BeautyGiftCardRepositoryInterface {
  final ApiClient apiClient;
  
  BeautyGiftCardRepository({required this.apiClient});
  
  @override
  Future<List<BeautyGiftCardModel>> getAvailableGiftCards() async {
    try {
      Response response = await apiClient.getData('${BeautyModuleConstants.beautyGiftCardsUri}/list');
      if (response.statusCode == 200) {
        List<BeautyGiftCardModel> cards = [];
        response.body['gift_cards'].forEach((card) {
          cards.add(BeautyGiftCardModel.fromJson(card));
        });
        return cards;
      }
      return [];
    } catch (e) {
      print('Error getting gift cards: \$e');
      return [];
    }
  }
  
  @override
  Future<List<BeautyGiftCardModel>> getMyGiftCards() async {
    try {
      Response response = await apiClient.getData('${BeautyModuleConstants.beautyGiftCardsUri}/list');
      if (response.statusCode == 200) {
        List<BeautyGiftCardModel> cards = [];
        response.body['gift_cards'].forEach((card) {
          cards.add(BeautyGiftCardModel.fromJson(card));
        });
        return cards;
      }
      return [];
    } catch (e) {
      print('Error getting my gift cards: \$e');
      return [];
    }
  }
  
  @override
  Future<BeautyGiftCardModel?> getGiftCardDetails(int cardId) async {
    try {
      Response response = await apiClient.getData(
        '${BeautyModuleConstants.beautyGiftCardsUri}/list?card_id=$cardId',
      );
      if (response.statusCode == 200) {
        return BeautyGiftCardModel.fromJson(response.body['gift_card']);
      }
      return null;
    } catch (e) {
      print('Error getting gift card details: \$e');
      return null;
    }
  }
  
  @override
  Future<Map<String, dynamic>?> purchaseGiftCard({
    required double amount,
    required String recipientEmail,
    String? recipientName,
    String? message,
    String? paymentMethod,
  }) async {
    try {
      Map<String, dynamic> data = {
        'amount': amount,
        'recipient_email': recipientEmail,
        'recipient_name': recipientName,
        'message': message,
        'payment_method': paymentMethod ?? 'card',
      };
      
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyPurchaseGiftCardUri,
        data,
      );
      
      if (response.statusCode == 200) {
        return response.body;
      }
      return null;
    } catch (e) {
      print('Error purchasing gift card: \$e');
      return null;
    }
  }
  
  @override
  Future<bool> redeemGiftCard(String code) async {
    try {
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyRedeemGiftCardUri,
        {'code': code},
      );
      return response.statusCode == 200;
    } catch (e) {
      print('Error redeeming gift card: \$e');
      return false;
    }
  }
  
  @override
  Future<double> checkGiftCardBalance(String code) async {
    try {
      Response response = await apiClient.getData(
        '${BeautyModuleConstants.beautyGiftCardsUri}/list?code=$code',
      );
      if (response.statusCode == 200) {
        return response.body['balance']?.toDouble() ?? 0.0;
      }
      return 0.0;
    } catch (e) {
      print('Error checking gift card balance: \$e');
      return 0.0;
    }
  }
  
  @override
  Future<bool> transferGiftCard({
    required int cardId,
    required String recipientEmail,
  }) async {
    try {
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyGiftCardsUri,
        {'card_id': cardId, 'recipient_email': recipientEmail},
      );
      return response.statusCode == 200;
    } catch (e) {
      print('Error transferring gift card: \$e');
      return false;
    }
  }
  
  @override
  Future<List<Map<String, dynamic>>> getGiftCardHistory(int cardId) async {
    try {
      Response response = await apiClient.getData(
        '${BeautyModuleConstants.beautyGiftCardsUri}/list?card_id=$cardId',
      );
      if (response.statusCode == 200) {
        return List<Map<String, dynamic>>.from(response.body['history']);
      }
      return [];
    } catch (e) {
      print('Error getting gift card history: \$e');
      return [];
    }
  }
}
