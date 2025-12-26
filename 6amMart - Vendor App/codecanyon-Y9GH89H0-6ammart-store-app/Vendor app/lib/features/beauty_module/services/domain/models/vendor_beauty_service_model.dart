class VendorBeautyServiceModel {
  final int? id;
  final String? name;
  final String? description;
  final int? durationMinutes;
  final double? price;
  final double? discount;
  final int? categoryId;
  final String? image;
  final bool? isActive;
  final String? createdAt;
  final String? updatedAt;

  VendorBeautyServiceModel({
    this.id,
    this.name,
    this.description,
    this.durationMinutes,
    this.price,
    this.discount,
    this.categoryId,
    this.image,
    this.isActive,
    this.createdAt,
    this.updatedAt,
  });

  factory VendorBeautyServiceModel.fromJson(Map<String, dynamic> json) {
    return VendorBeautyServiceModel(
      id: json['id'],
      name: json['name'],
      description: json['description'],
      durationMinutes: json['duration_minutes'],
      price: json['price']?.toDouble(),
      discount: json['discount']?.toDouble(),
      categoryId: json['category_id'],
      image: json['image'],
      isActive: json['is_active'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'duration_minutes': durationMinutes,
      'price': price,
      'discount': discount,
      'category_id': categoryId,
      'image': image,
      'is_active': isActive,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  double get finalPrice {
    if (price == null || discount == null) return price ?? 0;
    return price! - (price! * discount! / 100);
  }
}
