class VendorBeautyRetailOrderModel {
  int? id;
  String? orderNumber;
  double? total;
  String? status;
  String? paymentMethod;
  String? paymentStatus;
  String? createdAt;

  VendorBeautyRetailOrderModel({
    this.id,
    this.orderNumber,
    this.total,
    this.status,
    this.paymentMethod,
    this.paymentStatus,
    this.createdAt,
  });

  VendorBeautyRetailOrderModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    orderNumber = json['order_number'];
    total = json['total']?.toDouble();
    status = json['status'];
    paymentMethod = json['payment_method'];
    paymentStatus = json['payment_status'];
    createdAt = json['created_at'];
  }

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['id'] = id;
    data['order_number'] = orderNumber;
    data['total'] = total;
    data['status'] = status;
    data['payment_method'] = paymentMethod;
    data['payment_status'] = paymentStatus;
    data['created_at'] = createdAt;
    return data;
  }
}
