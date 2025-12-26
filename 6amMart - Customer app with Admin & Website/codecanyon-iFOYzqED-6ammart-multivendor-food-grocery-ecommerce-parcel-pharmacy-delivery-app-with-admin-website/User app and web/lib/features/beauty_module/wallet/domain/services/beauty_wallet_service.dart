import '../models/beauty_wallet_transaction_model.dart';
import '../repositories/beauty_wallet_repository_interface.dart';
import 'beauty_wallet_service_interface.dart';

class BeautyWalletService implements BeautyWalletServiceInterface {
  final BeautyWalletRepositoryInterface walletRepository;

  BeautyWalletService({required this.walletRepository});

  @override
  Future<BeautyWalletTransactionListModel?> getTransactions({
    int offset = 0,
    int limit = 10,
    String? type,
  }) async {
    return await walletRepository.getTransactions(
      offset: offset,
      limit: limit,
      type: type,
    );
  }
}
