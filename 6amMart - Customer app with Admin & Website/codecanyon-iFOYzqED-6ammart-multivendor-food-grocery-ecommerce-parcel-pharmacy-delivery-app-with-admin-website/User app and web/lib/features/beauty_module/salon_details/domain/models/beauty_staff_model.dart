class BeautyStaffModel {
  final int? id;
  final int? salonId;
  final String? name;
  final String? email;
  final String? phone;
  final String? image;
  final String? specialization;
  final int? experienceYears;
  final bool? isActive;
  final double? avgRating;
  final int? totalBookings;
  final List<int>? serviceIds;
  final List<Map<String, dynamic>>? services;
  final Map<String, dynamic>? availability;
  final String? createdAt;
  final String? updatedAt;

  BeautyStaffModel({
    this.id,
    this.salonId,
    this.name,
    this.email,
    this.phone,
    this.image,
    this.specialization,
    this.experienceYears,
    this.isActive,
    this.avgRating,
    this.totalBookings,
    this.serviceIds,
    this.services,
    this.availability,
    this.createdAt,
    this.updatedAt,
  });

  factory BeautyStaffModel.fromJson(Map<String, dynamic> json) {
    return BeautyStaffModel(
      id: json['id'],
      salonId: json['salon_id'],
      name: json['name'],
      email: json['email'],
      phone: json['phone'],
      image: json['image'],
      specialization: json['specialization'],
      experienceYears: json['experience_years'],
      isActive: json['is_active'],
      avgRating: json['avg_rating']?.toDouble(),
      totalBookings: json['total_bookings'],
      serviceIds: json['service_ids'] != null 
          ? List<int>.from(json['service_ids']) 
          : null,
      services: json['services'] != null
          ? List<Map<String, dynamic>>.from(json['services'])
          : null,
      availability: json['availability'] != null
          ? Map<String, dynamic>.from(json['availability'])
          : null,
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'salon_id': salonId,
      'name': name,
      'email': email,
      'phone': phone,
      'image': image,
      'specialization': specialization,
      'experience_years': experienceYears,
      'is_active': isActive,
      'avg_rating': avgRating,
      'total_bookings': totalBookings,
      'service_ids': serviceIds,
      'availability': availability,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  BeautyStaffModel copyWith({
    int? id,
    int? salonId,
    String? name,
    String? email,
    String? phone,
    String? image,
    String? specialization,
    int? experienceYears,
    bool? isActive,
    double? avgRating,
    int? totalBookings,
    List<int>? serviceIds,
    List<Map<String, dynamic>>? services,
    Map<String, dynamic>? availability,
    String? createdAt,
    String? updatedAt,
  }) {
    return BeautyStaffModel(
      id: id ?? this.id,
      salonId: salonId ?? this.salonId,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      image: image ?? this.image,
      specialization: specialization ?? this.specialization,
      experienceYears: experienceYears ?? this.experienceYears,
      isActive: isActive ?? this.isActive,
      avgRating: avgRating ?? this.avgRating,
      totalBookings: totalBookings ?? this.totalBookings,
      serviceIds: serviceIds ?? this.serviceIds,
      services: services ?? this.services,
      availability: availability ?? this.availability,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}
