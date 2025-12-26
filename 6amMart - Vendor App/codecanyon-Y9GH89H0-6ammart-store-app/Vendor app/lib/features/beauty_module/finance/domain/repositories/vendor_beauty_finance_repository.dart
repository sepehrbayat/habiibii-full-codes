import 'package:get/get.dart';
import 'package:sixam_mart_store/api/api_client.dart';
import 'package:sixam_mart_store/features/util/vendor_beauty_module_constants.dart';
import '../models/vendor_beauty_finance_summary_model.dart';
import '../models/vendor_beauty_transaction_model.dart';
import 'vendor_beauty_finance_repository_interface.dart';

class VendorBeautyFinanceRepository implements VendorBeautyFinanceRepositoryInterface {
  final ApiClient apiClient;

  VendorBeautyFinanceRepository({required this.apiClient});

  @override
  Future<VendorBeautyFinanceSummaryModel?> getSummary() async {
    Response response = await apiClient.getData(
      VendorBeautyModuleConstants.vendorFinanceSummaryUri,
    );
    if (response.statusCode == 200) {
      final data = response.body['summary'] ?? response.body['data'] ?? response.body;
      if (data is Map<String, dynamic>) {
        return VendorBeautyFinanceSummaryModel.fromJson(data);
      }
    }
    return null;
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
    Map<String, dynamic> query = {};
    if (offset != null) query['offset'] = offset;
    if (limit != null) query['limit'] = limit;
    if (status != null) query['status'] = status;
    if (reference != null) query['reference'] = reference;
    if (type != null) query['transaction_type'] = type;
    if (dateFrom != null) query['date_from'] = dateFrom;
    if (dateTo != null) query['date_to'] = dateTo;

    Response response = await apiClient.getData(
      VendorBeautyModuleConstants.vendorFinanceTransactionsUri,
      query: query,
    );
    if (response.statusCode == 200) {
      final List<VendorBeautyTransactionModel> transactions = [];
      final data = response.body['transactions'] ?? response.body['data'] ?? response.body;
      if (data is List) {
        for (final item in data) {
          transactions.add(VendorBeautyTransactionModel.fromJson(item));
        }
      }
      return transactions;
    }
    return null;
  }

  @override
  Future<bool> requestPayout(double amount) async {
    Response response = await apiClient.postData(
      VendorBeautyModuleConstants.vendorRequestPayoutUri,
      {'amount': amount},
    );
    return response.statusCode == 200;
  }

  @override
  Future add(value) {
    throw UnimplementedError();
  }

  @override
  Future delete(int? id) {
    throw UnimplementedError();
  }

  @override
  Future get(int? id) {
    throw UnimplementedError();
  }

  @override
  Future getList() {
    throw UnimplementedError();
  }

  @override
  Future update(Map<String, dynamic> body) {
    throw UnimplementedError();
  }
}
