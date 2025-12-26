class BeautyPackageModel {
  final int? id;
  final int? salonId;
  final String? name;
  final String? description;
  final double? price;
  final double? originalPrice;
  final String? type;
  final String? status;
  final DateTime? startDate;
  final DateTime? endDate;
  final int? purchaseCount;
  final bool? isTransferable;
  final int? sessionsCount;
  final int? validityDays;
  final List<int>? serviceIds;
  final List<Map<String, dynamic>>? services;
  final bool? isActive;
  final String? image;
  final String? salonName;
  final int? remainingSessions;
  final int? usedSessions;
  final String? purchaseDate;
  final String? expiryDate;
  final bool? isExpired;
  final List<String>? terms;
  final String? createdAt;
  final String? updatedAt;

  BeautyPackageModel({
    this.id,
    this.salonId,
    this.name,
    this.description,
    this.price,
    this.originalPrice,
    this.type,
    this.status,
    this.startDate,
    this.endDate,
    this.purchaseCount,
    this.isTransferable,
    this.sessionsCount,
    this.validityDays,
    this.serviceIds,
    this.services,
    this.isActive,
    this.image,
    this.salonName,
    this.remainingSessions,
    this.usedSessions,
    this.purchaseDate,
    this.expiryDate,
    this.isExpired,
    this.terms,
    this.createdAt,
    this.updatedAt,
  });

  factory BeautyPackageModel.fromJson(Map<String, dynamic> json) {
    return BeautyPackageModel(
      id: json['id'],
      salonId: json['salon_id'],
      name: json['name'],
      description: json['description'],
      price: json['price']?.toDouble(),
      originalPrice: json['original_price']?.toDouble(),
      type: json['type'],
      status: json['status'],
      startDate: json['start_date'] != null ? DateTime.tryParse(json['start_date']) : null,
      endDate: json['end_date'] != null ? DateTime.tryParse(json['end_date']) : null,
      purchaseCount: json['purchase_count'],
      isTransferable: json['is_transferable'],
      sessionsCount: json['sessions_count'],
      validityDays: json['validity_days'],
      serviceIds: json['service_ids'] != null 
          ? List<int>.from(json['service_ids']) 
          : null,
      services: json['services'] != null
          ? List<Map<String, dynamic>>.from(json['services'])
          : null,
      isActive: json['is_active'],
      image: json['image'],
      salonName: json['salon']?['store']?['name'],
      remainingSessions: json['remaining_sessions'],
      usedSessions: json['used_sessions'],
      purchaseDate: json['purchase_date'],
      expiryDate: json['expiry_date'],
      isExpired: json['is_expired'],
      terms: json['terms'] != null ? List<String>.from(json['terms']) : null,
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
      'price': price,
      'original_price': originalPrice,
      'type': type,
      'status': status,
      'start_date': startDate?.toIso8601String(),
      'end_date': endDate?.toIso8601String(),
      'purchase_count': purchaseCount,
      'is_transferable': isTransferable,
      'sessions_count': sessionsCount,
      'validity_days': validityDays,
      'service_ids': serviceIds,
      'is_active': isActive,
      'image': image,
      'remaining_sessions': remainingSessions,
      'used_sessions': usedSessions,
      'purchase_date': purchaseDate,
      'expiry_date': expiryDate,
      'is_expired': isExpired,
      'terms': terms,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  BeautyPackageModel copyWith({
    int? id,
    int? salonId,
    String? name,
    String? description,
    double? price,
    double? originalPrice,
    String? type,
    String? status,
    DateTime? startDate,
    DateTime? endDate,
    int? purchaseCount,
    bool? isTransferable,
    int? sessionsCount,
    int? validityDays,
    List<int>? serviceIds,
    List<Map<String, dynamic>>? services,
    bool? isActive,
    String? image,
    String? salonName,
    int? remainingSessions,
    int? usedSessions,
    String? purchaseDate,
    String? expiryDate,
    bool? isExpired,
    List<String>? terms,
    String? createdAt,
    String? updatedAt,
  }) {
    return BeautyPackageModel(
      id: id ?? this.id,
      salonId: salonId ?? this.salonId,
      name: name ?? this.name,
      description: description ?? this.description,
      price: price ?? this.price,
      originalPrice: originalPrice ?? this.originalPrice,
      type: type ?? this.type,
      status: status ?? this.status,
      startDate: startDate ?? this.startDate,
      endDate: endDate ?? this.endDate,
      purchaseCount: purchaseCount ?? this.purchaseCount,
      isTransferable: isTransferable ?? this.isTransferable,
      sessionsCount: sessionsCount ?? this.sessionsCount,
      validityDays: validityDays ?? this.validityDays,
      serviceIds: serviceIds ?? this.serviceIds,
      services: services ?? this.services,
      isActive: isActive ?? this.isActive,
      image: image ?? this.image,
      salonName: salonName ?? this.salonName,
      remainingSessions: remainingSessions ?? this.remainingSessions,
      usedSessions: usedSessions ?? this.usedSessions,
      purchaseDate: purchaseDate ?? this.purchaseDate,
      expiryDate: expiryDate ?? this.expiryDate,
      isExpired: isExpired ?? this.isExpired,
      terms: terms ?? this.terms,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}
