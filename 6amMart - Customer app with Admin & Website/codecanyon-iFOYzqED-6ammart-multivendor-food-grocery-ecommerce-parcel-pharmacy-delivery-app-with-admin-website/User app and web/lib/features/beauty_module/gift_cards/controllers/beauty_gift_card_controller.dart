import 'package:get/get.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/common/models/response_model.dart';

class BeautyGiftCardController extends GetxController {
  final ApiClient apiClient;
  
  BeautyGiftCardController({required this.apiClient});

  bool isLoading = false;
  List<dynamic>? giftCards;
  Map<String, dynamic>? currentGiftCard;

  Future<ResponseModel> purchaseGiftCard({
    required double amount,
    required String recipientEmail,
    String? recipientName,
    String? message,
  }) async {
    isLoading = true;
    update();
    
    try {
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyPurchaseGiftCardUri,
        {
          'amount': amount,
          'recipient_email': recipientEmail,
          'recipient_name': recipientName,
          'message': message,
        },
      );
      
      if (response.statusCode == 200) {
        return ResponseModel(true, 'Gift card purchased successfully');
      } else {
        return ResponseModel(false, response.statusText);
      }
    } catch (e) {
      return ResponseModel(false, 'Failed to purchase gift card');
    } finally {
      isLoading = false;
      update();
    }
  }

  Future<ResponseModel> redeemGiftCard(String code) async {
    isLoading = true;
    update();
    
    try {
      Response response = await apiClient.postData(
        BeautyModuleConstants.beautyRedeemGiftCardUri,
        {'code': code},
      );
      
      if (response.statusCode == 200) {
        return ResponseModel(true, 'Gift card redeemed successfully');
      } else {
        return ResponseModel(false, response.body['message'] ?? 'Invalid gift card code');
      }
    } catch (e) {
      return ResponseModel(false, 'Failed to redeem gift card');
    } finally {
      isLoading = false;
      update();
    }
  }

  Future<void> getMyGiftCards() async {
    isLoading = true;
    update();
    
    try {
      Response response = await apiClient.getData(
        BeautyModuleConstants.beautyGiftCardsUri,
      );
      
      if (response.statusCode == 200) {
        giftCards = response.body['gift_cards'];
      }
    } catch (e) {
      print('Error getting gift cards: $e');
    } finally {
      isLoading = false;
      update();
    }
  }
}
