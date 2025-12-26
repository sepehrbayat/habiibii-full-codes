class BeautyLoyaltyCampaignModel {
  final int? id;
  final int? salonId;
  final String? name;
  final String? description;
  final int? pointsPerBooking;
  final int? pointsPerAmount;
  final int? redeemThreshold;
  final double? redeemValue;
  final String? startDate;
  final String? endDate;
  final bool? isActive;
  final String? salonName;
  final String? createdAt;
  final String? updatedAt;

  BeautyLoyaltyCampaignModel({
    this.id,
    this.salonId,
    this.name,
    this.description,
    this.pointsPerBooking,
    this.pointsPerAmount,
    this.redeemThreshold,
    this.redeemValue,
    this.startDate,
    this.endDate,
    this.isActive,
    this.salonName,
    this.createdAt,
    this.updatedAt,
  });

  factory BeautyLoyaltyCampaignModel.fromJson(Map<String, dynamic> json) {
    return BeautyLoyaltyCampaignModel(
      id: json['id'],
      salonId: json['salon_id'],
      name: json['name'],
      description: json['description'],
      pointsPerBooking: json['points_per_booking'],
      pointsPerAmount: json['points_per_amount'],
      redeemThreshold: json['redeem_threshold'],
      redeemValue: json['redeem_value']?.toDouble(),
      startDate: json['start_date'],
      endDate: json['end_date'],
      isActive: json['is_active'],
      salonName: json['salon']?['store']?['name'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'salon_id': salonId,
      'name': name,
      'description': description,
      'points_per_booking': pointsPerBooking,
      'points_per_amount': pointsPerAmount,
      'redeem_threshold': redeemThreshold,
      'redeem_value': redeemValue,
      'start_date': startDate,
      'end_date': endDate,
      'is_active': isActive,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }
}
