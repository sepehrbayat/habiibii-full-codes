class BeautyWalletTransactionListModel {
  final List<BeautyWalletTransactionModel> transactions;
  final int total;
  final int perPage;
  final int currentPage;
  final int lastPage;

  BeautyWalletTransactionListModel({
    required this.transactions,
    required this.total,
    required this.perPage,
    required this.currentPage,
    required this.lastPage,
  });

  factory BeautyWalletTransactionListModel.fromJson(Map<String, dynamic> json) {
    final List<BeautyWalletTransactionModel> items = [];
    if (json['data'] != null) {
      for (final item in json['data']) {
        items.add(BeautyWalletTransactionModel.fromJson(item));
      }
    }
    return BeautyWalletTransactionListModel(
      transactions: items,
      total: json['total'] ?? 0,
      perPage: json['per_page'] ?? 0,
      currentPage: json['current_page'] ?? 0,
      lastPage: json['last_page'] ?? 0,
    );
  }
}

class BeautyWalletTransactionModel {
  final int? id;
  final String? transactionId;
  final String? transactionType;
  final double? credit;
  final double? debit;
  final double? balance;
  final String? reference;
  final String? createdAt;

  BeautyWalletTransactionModel({
    this.id,
    this.transactionId,
    this.transactionType,
    this.credit,
    this.debit,
    this.balance,
    this.reference,
    this.createdAt,
  });

  factory BeautyWalletTransactionModel.fromJson(Map<String, dynamic> json) {
    return BeautyWalletTransactionModel(
      id: json['id'],
      transactionId: json['transaction_id'],
      transactionType: json['transaction_type'],
      credit: json['credit']?.toDouble(),
      debit: json['debit']?.toDouble(),
      balance: json['balance']?.toDouble(),
      reference: json['reference'],
      createdAt: json['created_at'],
    );
  }
}
