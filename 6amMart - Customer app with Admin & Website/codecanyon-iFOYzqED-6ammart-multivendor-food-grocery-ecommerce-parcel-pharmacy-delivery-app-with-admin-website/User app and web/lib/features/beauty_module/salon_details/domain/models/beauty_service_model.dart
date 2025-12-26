class BeautyServiceModel {
  final int? id;
  final int? salonId;
  final int? categoryId;
  final String? name;
  final String? description;
  final double? price;
  final int? duration;
  final String? image;
  final bool? isActive;
  final bool? isConsultation;
  final double? consultationCreditPercentage;
  final int? consultationDuration;
  final String? categoryName;
  final List<int>? staffIds;
  final List<Map<String, dynamic>>? staff;
  final String? createdAt;
  final String? updatedAt;

  BeautyServiceModel({
    this.id,
    this.salonId,
    this.categoryId,
    this.name,
    this.description,
    this.price,
    this.duration,
    this.image,
    this.isActive,
    this.isConsultation,
    this.consultationCreditPercentage,
    this.consultationDuration,
    this.categoryName,
    this.staffIds,
    this.staff,
    this.createdAt,
    this.updatedAt,
  });

  factory BeautyServiceModel.fromJson(Map<String, dynamic> json) {
    return BeautyServiceModel(
      id: json['id'],
      salonId: json['salon_id'],
      categoryId: json['category_id'],
      name: json['name'],
      description: json['description'],
      price: json['price']?.toDouble(),
      duration: json['duration'],
      image: json['image'],
      isActive: json['is_active'],
      isConsultation: json['is_consultation'],
      consultationCreditPercentage: json['consultation_credit_percentage']?.toDouble(),
      consultationDuration: json['consultation_duration'],
      categoryName: json['category']?['name'],
      staffIds: json['staff_ids'] != null 
          ? List<int>.from(json['staff_ids']) 
          : null,
      staff: json['staff'] != null
          ? List<Map<String, dynamic>>.from(json['staff'])
          : null,
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'salon_id': salonId,
      'category_id': categoryId,
      'name': name,
      'description': description,
      'price': price,
      'duration': duration,
      'image': image,
      'is_active': isActive,
      'is_consultation': isConsultation,
      'consultation_credit_percentage': consultationCreditPercentage,
      'consultation_duration': consultationDuration,
      'staff_ids': staffIds,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  BeautyServiceModel copyWith({
    int? id,
    int? salonId,
    int? categoryId,
    String? name,
    String? description,
    double? price,
    int? duration,
    String? image,
    bool? isActive,
    bool? isConsultation,
    double? consultationCreditPercentage,
    int? consultationDuration,
    String? categoryName,
    List<int>? staffIds,
    List<Map<String, dynamic>>? staff,
    String? createdAt,
    String? updatedAt,
  }) {
    return BeautyServiceModel(
      id: id ?? this.id,
      salonId: salonId ?? this.salonId,
      categoryId: categoryId ?? this.categoryId,
      name: name ?? this.name,
      description: description ?? this.description,
      price: price ?? this.price,
      duration: duration ?? this.duration,
      image: image ?? this.image,
      isActive: isActive ?? this.isActive,
      isConsultation: isConsultation ?? this.isConsultation,
      consultationCreditPercentage: consultationCreditPercentage ?? this.consultationCreditPercentage,
      consultationDuration: consultationDuration ?? this.consultationDuration,
      categoryName: categoryName ?? this.categoryName,
      staffIds: staffIds ?? this.staffIds,
      staff: staff ?? this.staff,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}
