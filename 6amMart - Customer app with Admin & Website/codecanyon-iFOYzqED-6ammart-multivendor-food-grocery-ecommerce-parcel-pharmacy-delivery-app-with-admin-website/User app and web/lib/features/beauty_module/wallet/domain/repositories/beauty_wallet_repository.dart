import 'package:get/get.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';
import '../models/beauty_wallet_transaction_model.dart';
import 'beauty_wallet_repository_interface.dart';

class BeautyWalletRepository implements BeautyWalletRepositoryInterface {
  final ApiClient apiClient;

  BeautyWalletRepository({required this.apiClient});

  @override
  Future<BeautyWalletTransactionListModel?> getTransactions({
    int offset = 0,
    int limit = 10,
    String? type,
  }) async {
    try {
      String url = '${BeautyModuleConstants.beautyWalletTransactionsUri}?offset=$offset&limit=$limit';
      if (type != null && type.isNotEmpty) {
        url += '&type=$type';
      }
      final Response response = await apiClient.getData(url);
      if (response.statusCode == 200) {
        return BeautyWalletTransactionListModel.fromJson(response.body);
      }
      return null;
    } catch (e) {
      print('Error getting beauty wallet transactions: $e');
      return null;
    }
  }
}
