class VendorBeautyFinanceSummaryModel {
  double? totalEarnings;
  double? pendingPayout;
  double? completedPayout;
  int? totalOrders;

  VendorBeautyFinanceSummaryModel({
    this.totalEarnings,
    this.pendingPayout,
    this.completedPayout,
    this.totalOrders,
  });

  VendorBeautyFinanceSummaryModel.fromJson(Map<String, dynamic> json) {
    totalEarnings = json['total_earnings']?.toDouble();
    pendingPayout = json['pending_payout']?.toDouble();
    completedPayout = json['completed_payout']?.toDouble();
    totalOrders = json['total_orders'];
  }

  Map<String, dynamic> toJson() {
    return {
      'total_earnings': totalEarnings,
      'pending_payout': pendingPayout,
      'completed_payout': completedPayout,
      'total_orders': totalOrders,
    };
  }
}
