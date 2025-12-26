import '../models/vendor_beauty_finance_summary_model.dart';
import '../models/vendor_beauty_transaction_model.dart';
import '../repositories/vendor_beauty_finance_repository_interface.dart';
import 'vendor_beauty_finance_service_interface.dart';

class VendorBeautyFinanceService implements VendorBeautyFinanceServiceInterface {
  final VendorBeautyFinanceRepositoryInterface financeRepository;

  VendorBeautyFinanceService({required this.financeRepository});

  @override
  Future<VendorBeautyFinanceSummaryModel?> getSummary() async {
    return await financeRepository.getSummary();
  }

  @override
  Future<List<VendorBeautyTransactionModel>?> getTransactions({
    int? offset,
    int? limit,
    String? status,
    String? reference,
    String? type,
    String? dateFrom,
    String? dateTo,
  }) async {
    return await financeRepository.getTransactions(
      offset: offset,
      limit: limit,
      status: status,
      reference: reference,
      type: type,
      dateFrom: dateFrom,
      dateTo: dateTo,
    );
  }

  @override
  Future<bool> requestPayout(double amount) async {
    return await financeRepository.requestPayout(amount);
  }
}
