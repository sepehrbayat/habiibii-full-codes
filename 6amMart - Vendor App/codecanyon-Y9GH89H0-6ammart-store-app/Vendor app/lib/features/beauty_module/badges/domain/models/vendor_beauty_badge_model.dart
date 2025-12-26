class VendorBeautyBadgeModel {
  final int? id;
  final String? name;
  final String? description;
  final String? status;
  final String? icon;
  final String? achievedAt;

  VendorBeautyBadgeModel({
    this.id,
    this.name,
    this.description,
    this.status,
    this.icon,
    this.achievedAt,
  });

  factory VendorBeautyBadgeModel.fromJson(Map<String, dynamic> json) {
    return VendorBeautyBadgeModel(
      id: json['id'],
      name: json['name'],
      description: json['description'],
      status: json['status'],
      icon: json['icon'],
      achievedAt: json['achieved_at'] ?? json['achievedAt'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'status': status,
      'icon': icon,
      'achieved_at': achievedAt,
    };
  }

  bool get isAchieved => status == 'achieved' || status == 'active';
}
