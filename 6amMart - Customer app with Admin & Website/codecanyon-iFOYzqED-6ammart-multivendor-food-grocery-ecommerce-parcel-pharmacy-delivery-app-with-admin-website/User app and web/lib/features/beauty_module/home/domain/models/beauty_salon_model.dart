class BeautySalonModel {
  final int? id;
  final int? storeId;
  final int? zoneId;
  final String? businessType;
  final String? licenseNumber;
  final String? licenseExpiry;
  final List<String>? documents;
  final int? verificationStatus;
  final String? verificationNotes;
  final bool? isVerified;
  final bool? isFeatured;
  final Map<String, dynamic>? workingHours;
  final List<String>? holidays;
  final double? avgRating;
  final int? totalBookings;
  final int? totalReviews;
  final int? totalCancellations;
  final double? cancellationRate;
  final List<String>? badgesList;
  final String? verificationStatusText;
  final String? businessModel;
  
  // Store relationship fields
  final String? storeName;
  final String? storeAddress;
  final String? storePhone;
  final String? storeEmail;
  final String? storeLogo;
  final String? storeCoverPhoto;
  final double? storeLatitude;
  final double? storeLongitude;
  final bool? storeActive;
  final int? storeStatus;
  final double? distance;

  BeautySalonModel({
    this.id,
    this.storeId,
    this.zoneId,
    this.businessType,
    this.licenseNumber,
    this.licenseExpiry,
    this.documents,
    this.verificationStatus,
    this.verificationNotes,
    this.isVerified,
    this.isFeatured,
    this.workingHours,
    this.holidays,
    this.avgRating,
    this.totalBookings,
    this.totalReviews,
    this.totalCancellations,
    this.cancellationRate,
    this.badgesList,
    this.verificationStatusText,
    this.businessModel,
    this.storeName,
    this.storeAddress,
    this.storePhone,
    this.storeEmail,
    this.storeLogo,
    this.storeCoverPhoto,
    this.storeLatitude,
    this.storeLongitude,
    this.storeActive,
    this.storeStatus,
    this.distance,
  });

  factory BeautySalonModel.fromJson(Map<String, dynamic> json) {
    return BeautySalonModel(
      id: json['id'],
      storeId: json['store_id'],
      zoneId: json['zone_id'],
      businessType: json['business_type'],
      licenseNumber: json['license_number'],
      licenseExpiry: json['license_expiry'],
      documents: json['documents'] != null 
          ? List<String>.from(json['documents']) 
          : null,
      verificationStatus: json['verification_status'],
      verificationNotes: json['verification_notes'],
      isVerified: json['is_verified'],
      isFeatured: json['is_featured'],
      workingHours: json['working_hours'] != null 
          ? Map<String, dynamic>.from(json['working_hours']) 
          : null,
      holidays: json['holidays'] != null 
          ? List<String>.from(json['holidays']) 
          : null,
      avgRating: json['avg_rating']?.toDouble(),
      totalBookings: json['total_bookings'],
      totalReviews: json['total_reviews'],
      totalCancellations: json['total_cancellations'],
      cancellationRate: json['cancellation_rate']?.toDouble(),
      badgesList: json['badges_list'] != null 
          ? List<String>.from(json['badges_list']) 
          : null,
      verificationStatusText: json['verification_status_text'],
      businessModel: json['business_model'],
      storeName: json['store']?['name'],
      storeAddress: json['store']?['address'],
      storePhone: json['store']?['phone'],
      storeEmail: json['store']?['email'],
      storeLogo: json['store']?['logo'],
      storeCoverPhoto: json['store']?['cover_photo'],
      storeLatitude: json['store']?['latitude']?.toDouble(),
      storeLongitude: json['store']?['longitude']?.toDouble(),
      storeActive: json['store']?['active'],
      storeStatus: json['store']?['status'],
      distance: json['distance']?.toDouble(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'store_id': storeId,
      'zone_id': zoneId,
      'business_type': businessType,
      'license_number': licenseNumber,
      'license_expiry': licenseExpiry,
      'documents': documents,
      'verification_status': verificationStatus,
      'verification_notes': verificationNotes,
      'is_verified': isVerified,
      'is_featured': isFeatured,
      'working_hours': workingHours,
      'holidays': holidays,
      'avg_rating': avgRating,
      'total_bookings': totalBookings,
      'total_reviews': totalReviews,
      'total_cancellations': totalCancellations,
      'cancellation_rate': cancellationRate,
      'badges_list': badgesList,
      'verification_status_text': verificationStatusText,
      'business_model': businessModel,
      'distance': distance,
    };
  }

  BeautySalonModel copyWith({
    int? id,
    int? storeId,
    int? zoneId,
    String? businessType,
    String? licenseNumber,
    String? licenseExpiry,
    List<String>? documents,
    int? verificationStatus,
    String? verificationNotes,
    bool? isVerified,
    bool? isFeatured,
    Map<String, dynamic>? workingHours,
    List<String>? holidays,
    double? avgRating,
    int? totalBookings,
    int? totalReviews,
    int? totalCancellations,
    double? cancellationRate,
    List<String>? badgesList,
    String? verificationStatusText,
    String? businessModel,
    String? storeName,
    String? storeAddress,
    String? storePhone,
    String? storeEmail,
    String? storeLogo,
    String? storeCoverPhoto,
    double? storeLatitude,
    double? storeLongitude,
    bool? storeActive,
    int? storeStatus,
    double? distance,
  }) {
    return BeautySalonModel(
      id: id ?? this.id,
      storeId: storeId ?? this.storeId,
      zoneId: zoneId ?? this.zoneId,
      businessType: businessType ?? this.businessType,
      licenseNumber: licenseNumber ?? this.licenseNumber,
      licenseExpiry: licenseExpiry ?? this.licenseExpiry,
      documents: documents ?? this.documents,
      verificationStatus: verificationStatus ?? this.verificationStatus,
      verificationNotes: verificationNotes ?? this.verificationNotes,
      isVerified: isVerified ?? this.isVerified,
      isFeatured: isFeatured ?? this.isFeatured,
      workingHours: workingHours ?? this.workingHours,
      holidays: holidays ?? this.holidays,
      avgRating: avgRating ?? this.avgRating,
      totalBookings: totalBookings ?? this.totalBookings,
      totalReviews: totalReviews ?? this.totalReviews,
      totalCancellations: totalCancellations ?? this.totalCancellations,
      cancellationRate: cancellationRate ?? this.cancellationRate,
      badgesList: badgesList ?? this.badgesList,
      verificationStatusText: verificationStatusText ?? this.verificationStatusText,
      businessModel: businessModel ?? this.businessModel,
      storeName: storeName ?? this.storeName,
      storeAddress: storeAddress ?? this.storeAddress,
      storePhone: storePhone ?? this.storePhone,
      storeEmail: storeEmail ?? this.storeEmail,
      storeLogo: storeLogo ?? this.storeLogo,
      storeCoverPhoto: storeCoverPhoto ?? this.storeCoverPhoto,
      storeLatitude: storeLatitude ?? this.storeLatitude,
      storeLongitude: storeLongitude ?? this.storeLongitude,
      storeActive: storeActive ?? this.storeActive,
      storeStatus: storeStatus ?? this.storeStatus,
      distance: distance ?? this.distance,
    );
  }
}
