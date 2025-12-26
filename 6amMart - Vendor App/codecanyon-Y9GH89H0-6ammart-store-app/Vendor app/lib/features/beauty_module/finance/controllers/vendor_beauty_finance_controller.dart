import 'package:get/get.dart';
import '../domain/models/vendor_beauty_finance_summary_model.dart';
import '../domain/models/vendor_beauty_transaction_model.dart';
import '../domain/services/vendor_beauty_finance_service_interface.dart';

class VendorBeautyFinanceController extends GetxController implements GetxService {
  final VendorBeautyFinanceServiceInterface financeService;

  VendorBeautyFinanceController({required this.financeService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  VendorBeautyFinanceSummaryModel? _summary;
  VendorBeautyFinanceSummaryModel? get summary => _summary;

  List<VendorBeautyTransactionModel>? _transactions;
  List<VendorBeautyTransactionModel>? get transactions => _transactions;
  List<VendorBeautyTransactionModel>? _filteredTransactions;
  List<VendorBeautyTransactionModel>? get filteredTransactions => _filteredTransactions ?? _transactions;
  String _filterType = 'all';
  String get filterType => _filterType;

  @override
  void onInit() {
    super.onInit();
    getSummary();
    getTransactions();
  }

  Future<void> getSummary() async {
    _isLoading = true;
    update();

    try {
      _summary = await financeService.getSummary();
    } catch (e) {
      print('Error loading finance summary: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<void> getTransactions() async {
    _isLoading = true;
    update();

    try {
      _transactions = await financeService.getTransactions(
        status: null,
        reference: null,
        type: null,
        dateFrom: null,
        dateTo: null,
      );
      _applyFilter(_filterType);
    } catch (e) {
      print('Error loading transactions: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  void _applyFilter(String type) {
    _filterType = type;
    if (_transactions == null) {
      _filteredTransactions = null;
      return;
    }
    if (type == 'all') {
      _filteredTransactions = _transactions;
    } else {
      _filteredTransactions = _transactions!.where((t) => (t.type ?? '').toLowerCase() == type).toList();
    }
  }

  void setFilter(String type) {
    _applyFilter(type);
    update();
  }

  Future<bool> requestPayout(double amount) async {
    _isLoading = true;
    update();
    try {
      final success = await financeService.requestPayout(amount);
      return success;
    } finally {
      _isLoading = false;
      update();
    }
  }
}
