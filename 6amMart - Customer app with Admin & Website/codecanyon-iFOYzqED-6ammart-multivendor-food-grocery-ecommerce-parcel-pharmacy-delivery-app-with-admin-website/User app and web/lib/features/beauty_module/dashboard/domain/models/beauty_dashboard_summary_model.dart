class BeautyDashboardSummaryModel {
  final int? totalBookings;
  final int? upcomingBookings;
  final double? totalSpent;
  final int? activePackages;
  final int? pendingConsultations;
  final double? giftCardBalance;
  final int? loyaltyPoints;

  BeautyDashboardSummaryModel({
    this.totalBookings,
    this.upcomingBookings,
    this.totalSpent,
    this.activePackages,
    this.pendingConsultations,
    this.giftCardBalance,
    this.loyaltyPoints,
  });

  factory BeautyDashboardSummaryModel.fromJson(Map<String, dynamic> json) {
    return BeautyDashboardSummaryModel(
      totalBookings: json['total_bookings'],
      upcomingBookings: json['upcoming_bookings'],
      totalSpent: json['total_spent']?.toDouble(),
      activePackages: json['active_packages'],
      pendingConsultations: json['pending_consultations'],
      giftCardBalance: json['gift_card_balance']?.toDouble(),
      loyaltyPoints: json['loyalty_points'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'total_bookings': totalBookings,
      'upcoming_bookings': upcomingBookings,
      'total_spent': totalSpent,
      'active_packages': activePackages,
      'pending_consultations': pendingConsultations,
      'gift_card_balance': giftCardBalance,
      'loyalty_points': loyaltyPoints,
    };
  }
}
