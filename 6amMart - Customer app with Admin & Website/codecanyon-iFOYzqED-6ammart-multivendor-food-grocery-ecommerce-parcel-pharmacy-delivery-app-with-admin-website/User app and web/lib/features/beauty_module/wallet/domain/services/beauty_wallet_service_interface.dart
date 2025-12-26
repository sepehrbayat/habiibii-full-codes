import '../models/beauty_wallet_transaction_model.dart';

abstract class BeautyWalletServiceInterface {
  Future<BeautyWalletTransactionListModel?> getTransactions({
    int offset = 0,
    int limit = 10,
    String? type,
  });
}
