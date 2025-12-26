class VendorBeautyTransactionModel {
  int? id;
  String? type;
  double? amount;
  String? status;
  String? createdAt;

  VendorBeautyTransactionModel({
    this.id,
    this.type,
    this.amount,
    this.status,
    this.createdAt,
  });

  VendorBeautyTransactionModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    type = json['type'];
    amount = json['amount']?.toDouble();
    status = json['status'];
    createdAt = json['created_at'];
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'type': type,
      'amount': amount,
      'status': status,
      'created_at': createdAt,
    };
  }
}
