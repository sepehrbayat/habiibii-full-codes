class VendorBeautyStaffModel {
  final int? id;
  final String? name;
  final String? email;
  final String? phone;
  final String? image;
  final String? designation;
  final double? rating;
  final bool? isActive;
  final List<int>? serviceIds;
  final String? createdAt;
  final String? updatedAt;

  VendorBeautyStaffModel({
    this.id,
    this.name,
    this.email,
    this.phone,
    this.image,
    this.designation,
    this.rating,
    this.isActive,
    this.serviceIds,
    this.createdAt,
    this.updatedAt,
  });

  factory VendorBeautyStaffModel.fromJson(Map<String, dynamic> json) {
    return VendorBeautyStaffModel(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      phone: json['phone'],
      image: json['image'],
      designation: json['designation'],
      rating: json['rating']?.toDouble(),
      isActive: json['is_active'],
      serviceIds: json['service_ids'] != null
          ? List<int>.from(json['service_ids'])
          : null,
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'image': image,
      'designation': designation,
      'rating': rating,
      'is_active': isActive,
      'service_ids': serviceIds,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }
}
