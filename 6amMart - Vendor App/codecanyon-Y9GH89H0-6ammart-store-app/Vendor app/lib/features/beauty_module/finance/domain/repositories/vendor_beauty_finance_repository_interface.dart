import 'package:sixam_mart_store/interface/repository_interface.dart';
import '../models/vendor_beauty_finance_summary_model.dart';
import '../models/vendor_beauty_transaction_model.dart';

abstract class VendorBeautyFinanceRepositoryInterface implements RepositoryInterface {
  Future<VendorBeautyFinanceSummaryModel?> getSummary();
  Future<List<VendorBeautyTransactionModel>?> getTransactions({
    int? offset,
    int? limit,
    String? status,
    String? reference,
    String? type,
    String? dateFrom,
    String? dateTo,
  });
  Future<bool> requestPayout(double amount);
}
