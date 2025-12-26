class BeautyGiftCardModel {
  final int? id;
  final int? salonId;
  final int? userId;
  final String? code;
  final double? amount;
  final double? balance;
  final String? status;
  final String? expiryDate;
  final String? salonName;
  final String? createdAt;
  final String? updatedAt;

  BeautyGiftCardModel({
    this.id,
    this.salonId,
    this.userId,
    this.code,
    this.amount,
    this.balance,
    this.status,
    this.expiryDate,
    this.salonName,
    this.createdAt,
    this.updatedAt,
  });

  factory BeautyGiftCardModel.fromJson(Map<String, dynamic> json) {
    return BeautyGiftCardModel(
      id: json['id'],
      salonId: json['salon_id'],
      userId: json['user_id'],
      code: json['code'],
      amount: json['amount']?.toDouble(),
      balance: json['balance']?.toDouble(),
      status: json['status'],
      expiryDate: json['expiry_date'],
      salonName: json['salon']?['store']?['name'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'salon_id': salonId,
      'user_id': userId,
      'code': code,
      'amount': amount,
      'balance': balance,
      'status': status,
      'expiry_date': expiryDate,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  bool get isActive => status == 'active' && balance != null && balance! > 0;
  bool get isExpired {
    if (expiryDate == null) return false;
    try {
      return DateTime.parse(expiryDate!).isBefore(DateTime.now());
    } catch (e) {
      return false;
    }
  }
}
