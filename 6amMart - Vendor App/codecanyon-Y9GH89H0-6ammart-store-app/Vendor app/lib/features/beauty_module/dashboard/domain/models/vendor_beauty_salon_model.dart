class VendorBeautySalonModel {
  final int? id;
  final String? name;
  final String? address;
  final String? phone;
  final String? image;
  final double? rating;
  final int? totalReviews;
  final bool? isActive;
  final String? createdAt;
  final String? updatedAt;

  VendorBeautySalonModel({
    this.id,
    this.name,
    this.address,
    this.phone,
    this.image,
    this.rating,
    this.totalReviews,
    this.isActive,
    this.createdAt,
    this.updatedAt,
  });

  factory VendorBeautySalonModel.fromJson(Map<String, dynamic> json) {
    return VendorBeautySalonModel(
      id: json['id'],
      name: json['name'],
      address: json['address'],
      phone: json['phone'],
      image: json['image'],
      rating: json['rating']?.toDouble(),
      totalReviews: json['total_reviews'],
      isActive: json['is_active'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'address': address,
      'phone': phone,
      'image': image,
      'rating': rating,
      'total_reviews': totalReviews,
      'is_active': isActive,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }
}
