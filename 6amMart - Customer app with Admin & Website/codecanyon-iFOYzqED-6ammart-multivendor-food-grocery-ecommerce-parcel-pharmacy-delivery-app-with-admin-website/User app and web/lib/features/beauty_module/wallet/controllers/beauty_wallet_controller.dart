import 'package:get/get.dart';
import '../domain/models/beauty_wallet_transaction_model.dart';
import '../domain/services/beauty_wallet_service_interface.dart';

class BeautyWalletController extends GetxController implements GetxService {
  final BeautyWalletServiceInterface walletService;

  BeautyWalletController({required this.walletService});

  final int _limit = 10;
  bool _isLoading = false;
  bool _isPaginating = false;
  bool _isLastPage = false;
  int _offset = 0;
  String? _currentType;

  bool get isLoading => _isLoading;
  bool get isPaginating => _isPaginating;
  bool get isLastPage => _isLastPage;
  List<BeautyWalletTransactionModel> _transactions = [];
  List<BeautyWalletTransactionModel> get transactions => _transactions;

  @override
  void onInit() {
    super.onInit();
    getTransactions(reload: true);
  }

  Future<void> getTransactions({bool reload = false, String? type}) async {
    if (reload) {
      _offset = 0;
      _isLastPage = false;
      _transactions = [];
      _currentType = type;
    }

    if (_isLastPage) {
      return;
    }

    if (_offset == 0) {
      _isLoading = true;
    } else {
      _isPaginating = true;
    }
    update();

    try {
      final response = await walletService.getTransactions(
        offset: _offset,
        limit: _limit,
        type: _currentType,
      );

      if (response != null) {
        _transactions.addAll(response.transactions);
        _offset += response.transactions.length;
        if (_transactions.length >= response.total) {
          _isLastPage = true;
        }
      } else {
        _isLastPage = true;
      }
    } catch (e) {
      print('Error loading beauty wallet transactions: $e');
    } finally {
      _isLoading = false;
      _isPaginating = false;
      update();
    }
  }
}
