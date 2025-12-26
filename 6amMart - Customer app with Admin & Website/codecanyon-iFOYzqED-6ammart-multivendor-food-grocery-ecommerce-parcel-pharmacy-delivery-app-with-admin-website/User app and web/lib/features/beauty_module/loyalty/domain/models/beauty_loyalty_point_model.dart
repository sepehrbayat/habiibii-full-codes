class BeautyLoyaltyPointModel {
  final int? id;
  final int? userId;
  final int? salonId;
  final int? campaignId;
  final int? bookingId;
  final int? points;
  final String? type;
  final String? description;
  final String? salonName;
  final String? campaignName;
  final String? createdAt;

  BeautyLoyaltyPointModel({
    this.id,
    this.userId,
    this.salonId,
    this.campaignId,
    this.bookingId,
    this.points,
    this.type,
    this.description,
    this.salonName,
    this.campaignName,
    this.createdAt,
  });

  factory BeautyLoyaltyPointModel.fromJson(Map<String, dynamic> json) {
    return BeautyLoyaltyPointModel(
      id: json['id'],
      userId: json['user_id'],
      salonId: json['salon_id'],
      campaignId: json['campaign_id'],
      bookingId: json['booking_id'],
      points: json['points'],
      type: json['type'],
      description: json['description'],
      salonName: json['salon']?['store']?['name'],
      campaignName: json['campaign']?['name'],
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'salon_id': salonId,
      'campaign_id': campaignId,
      'booking_id': bookingId,
      'points': points,
      'type': type,
      'description': description,
      'created_at': createdAt,
    };
  }
}
