class BeautyBookingModel {
  final int? id;
  final int? userId;
  final int? salonId;
  final int? serviceId;
  final int? packageId;
  final int? mainServiceId;
  final int? staffId;
  final int? zoneId;
  final int? conversationId;
  final String? bookingDate;
  final String? bookingTime;
  final String? bookingDateTime;
  final String? bookingReference;
  final String? status;
  final String? paymentStatus;
  final String? paymentMethod;
  final double? totalAmount;
  final double? commissionAmount;
  final double? serviceFee;
  final double? taxAmount;
  final double? taxPercentage;
  final String? taxStatus;
  final double? cancellationFee;
  final double? additionalCharge;
  final String? additionalChargeNotes;
  final double? consultationCreditPercentage;
  final double? consultationCreditAmount;
  final List<Map<String, dynamic>>? additionalServices;
  final String? notes;
  final String? cancellationReason;
  final String? cancelledBy;
  final String? discountBy;
  final String? discountSource;
  final double? discountSubsidy;
  final String? paymentTransactionId;
  final String? paymentGateway;
  final String? createdAt;
  final String? updatedAt;
  
  // Relationships
  final String? salonName;
  final String? serviceName;
  final String? staffName;
  final String? userName;

  BeautyBookingModel({
    this.id,
    this.userId,
    this.salonId,
    this.serviceId,
    this.packageId,
    this.mainServiceId,
    this.staffId,
    this.zoneId,
    this.conversationId,
    this.bookingDate,
    this.bookingTime,
    this.bookingDateTime,
    this.bookingReference,
    this.status,
    this.paymentStatus,
    this.paymentMethod,
    this.totalAmount,
    this.commissionAmount,
    this.serviceFee,
    this.taxAmount,
    this.taxPercentage,
    this.taxStatus,
    this.cancellationFee,
    this.additionalCharge,
    this.additionalChargeNotes,
    this.consultationCreditPercentage,
    this.consultationCreditAmount,
    this.additionalServices,
    this.notes,
    this.cancellationReason,
    this.cancelledBy,
    this.discountBy,
    this.discountSource,
    this.discountSubsidy,
    this.paymentTransactionId,
    this.paymentGateway,
    this.createdAt,
    this.updatedAt,
    this.salonName,
    this.serviceName,
    this.staffName,
    this.userName,
  });

  factory BeautyBookingModel.fromJson(Map<String, dynamic> json) {
    return BeautyBookingModel(
      id: json['id'],
      userId: json['user_id'],
      salonId: json['salon_id'],
      serviceId: json['service_id'],
      packageId: json['package_id'],
      mainServiceId: json['main_service_id'],
      staffId: json['staff_id'],
      zoneId: json['zone_id'],
      conversationId: json['conversation_id'],
      bookingDate: json['booking_date'],
      bookingTime: json['booking_time'],
      bookingDateTime: json['booking_date_time'],
      bookingReference: json['booking_reference'],
      status: json['status'],
      paymentStatus: json['payment_status'],
      paymentMethod: json['payment_method'],
      totalAmount: json['total_amount']?.toDouble(),
      commissionAmount: json['commission_amount']?.toDouble(),
      serviceFee: json['service_fee']?.toDouble(),
      taxAmount: json['tax_amount']?.toDouble(),
      taxPercentage: json['tax_percentage']?.toDouble(),
      taxStatus: json['tax_status'],
      cancellationFee: json['cancellation_fee']?.toDouble(),
      additionalCharge: json['additional_charge']?.toDouble(),
      additionalChargeNotes: json['additional_charge_notes'],
      consultationCreditPercentage: json['consultation_credit_percentage']?.toDouble(),
      consultationCreditAmount: json['consultation_credit_amount']?.toDouble(),
      additionalServices: json['additional_services'] != null
          ? List<Map<String, dynamic>>.from(json['additional_services'])
          : null,
      notes: json['notes'],
      cancellationReason: json['cancellation_reason'],
      cancelledBy: json['cancelled_by'],
      discountBy: json['discount_by'],
      discountSource: json['discount_source'],
      discountSubsidy: json['discount_subsidy']?.toDouble(),
      paymentTransactionId: json['payment_transaction_id'],
      paymentGateway: json['payment_gateway'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
      salonName: json['salon']?['store']?['name'],
      serviceName: json['service']?['name'],
      staffName: json['staff']?['name'],
      userName: json['user']?['f_name'] != null && json['user']?['l_name'] != null
          ? '${json['user']['f_name']} ${json['user']['l_name']}'
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'salon_id': salonId,
      'service_id': serviceId,
      'package_id': packageId,
      'main_service_id': mainServiceId,
      'staff_id': staffId,
      'zone_id': zoneId,
      'conversation_id': conversationId,
      'booking_date': bookingDate,
      'booking_time': bookingTime,
      'booking_date_time': bookingDateTime,
      'booking_reference': bookingReference,
      'status': status,
      'payment_status': paymentStatus,
      'payment_method': paymentMethod,
      'total_amount': totalAmount,
      'commission_amount': commissionAmount,
      'service_fee': serviceFee,
      'tax_amount': taxAmount,
      'tax_percentage': taxPercentage,
      'tax_status': taxStatus,
      'cancellation_fee': cancellationFee,
      'additional_charge': additionalCharge,
      'additional_charge_notes': additionalChargeNotes,
      'consultation_credit_percentage': consultationCreditPercentage,
      'consultation_credit_amount': consultationCreditAmount,
      'additional_services': additionalServices,
      'notes': notes,
      'cancellation_reason': cancellationReason,
      'cancelled_by': cancelledBy,
      'discount_by': discountBy,
      'discount_source': discountSource,
      'discount_subsidy': discountSubsidy,
      'payment_transaction_id': paymentTransactionId,
      'payment_gateway': paymentGateway,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  bool get canCancel {
    if (status == 'cancelled' || status == 'completed') {
      return false;
    }
    
    if (bookingDateTime == null) return false;
    
    try {
      final bookingDateTimeParsed = DateTime.parse(bookingDateTime!);
      final now = DateTime.now();
      
      if (bookingDateTimeParsed.isBefore(now)) {
        return false;
      }
      
      final hoursUntilBooking = bookingDateTimeParsed.difference(now).inHours;
      return hoursUntilBooking >= 24;
    } catch (e) {
      return false;
    }
  }

  bool get canReschedule {
    if (status == 'cancelled' || status == 'completed') {
      return false;
    }
    
    if (bookingDateTime == null) return false;
    
    try {
      final bookingDateTimeParsed = DateTime.parse(bookingDateTime!);
      final now = DateTime.now();
      
      if (bookingDateTimeParsed.isBefore(now)) {
        return false;
      }
      
      final hoursUntilBooking = bookingDateTimeParsed.difference(now).inHours;
      return hoursUntilBooking >= 2;
    } catch (e) {
      return false;
    }
  }

  bool get isUpcoming {
    if (status == 'cancelled' || status == 'completed') {
      return false;
    }
    
    if (bookingDateTime == null) return false;
    
    try {
      final bookingDateTimeParsed = DateTime.parse(bookingDateTime!);
      return bookingDateTimeParsed.isAfter(DateTime.now());
    } catch (e) {
      return false;
    }
  }

  bool get isPast {
    if (bookingDateTime == null) return false;
    
    try {
      final bookingDateTimeParsed = DateTime.parse(bookingDateTime!);
      return bookingDateTimeParsed.isBefore(DateTime.now());
    } catch (e) {
      return false;
    }
  }

  BeautyBookingModel copyWith({
    int? id,
    int? userId,
    int? salonId,
    int? serviceId,
    int? packageId,
    int? mainServiceId,
    int? staffId,
    int? zoneId,
    int? conversationId,
    String? bookingDate,
    String? bookingTime,
    String? bookingDateTime,
    String? bookingReference,
    String? status,
    String? paymentStatus,
    String? paymentMethod,
    double? totalAmount,
    double? commissionAmount,
    double? serviceFee,
    double? taxAmount,
    double? taxPercentage,
    String? taxStatus,
    double? cancellationFee,
    double? additionalCharge,
    String? additionalChargeNotes,
    double? consultationCreditPercentage,
    double? consultationCreditAmount,
    List<Map<String, dynamic>>? additionalServices,
    String? notes,
    String? cancellationReason,
    String? cancelledBy,
    String? discountBy,
    String? discountSource,
    double? discountSubsidy,
    String? paymentTransactionId,
    String? paymentGateway,
    String? createdAt,
    String? updatedAt,
    String? salonName,
    String? serviceName,
    String? staffName,
    String? userName,
  }) {
    return BeautyBookingModel(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      salonId: salonId ?? this.salonId,
      serviceId: serviceId ?? this.serviceId,
      packageId: packageId ?? this.packageId,
      mainServiceId: mainServiceId ?? this.mainServiceId,
      staffId: staffId ?? this.staffId,
      zoneId: zoneId ?? this.zoneId,
      conversationId: conversationId ?? this.conversationId,
      bookingDate: bookingDate ?? this.bookingDate,
      bookingTime: bookingTime ?? this.bookingTime,
      bookingDateTime: bookingDateTime ?? this.bookingDateTime,
      bookingReference: bookingReference ?? this.bookingReference,
      status: status ?? this.status,
      paymentStatus: paymentStatus ?? this.paymentStatus,
      paymentMethod: paymentMethod ?? this.paymentMethod,
      totalAmount: totalAmount ?? this.totalAmount,
      commissionAmount: commissionAmount ?? this.commissionAmount,
      serviceFee: serviceFee ?? this.serviceFee,
      taxAmount: taxAmount ?? this.taxAmount,
      taxPercentage: taxPercentage ?? this.taxPercentage,
      taxStatus: taxStatus ?? this.taxStatus,
      cancellationFee: cancellationFee ?? this.cancellationFee,
      additionalCharge: additionalCharge ?? this.additionalCharge,
      additionalChargeNotes: additionalChargeNotes ?? this.additionalChargeNotes,
      consultationCreditPercentage: consultationCreditPercentage ?? this.consultationCreditPercentage,
      consultationCreditAmount: consultationCreditAmount ?? this.consultationCreditAmount,
      additionalServices: additionalServices ?? this.additionalServices,
      notes: notes ?? this.notes,
      cancellationReason: cancellationReason ?? this.cancellationReason,
      cancelledBy: cancelledBy ?? this.cancelledBy,
      discountBy: discountBy ?? this.discountBy,
      discountSource: discountSource ?? this.discountSource,
      discountSubsidy: discountSubsidy ?? this.discountSubsidy,
      paymentTransactionId: paymentTransactionId ?? this.paymentTransactionId,
      paymentGateway: paymentGateway ?? this.paymentGateway,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      salonName: salonName ?? this.salonName,
      serviceName: serviceName ?? this.serviceName,
      staffName: staffName ?? this.staffName,
      userName: userName ?? this.userName,
    );
  }
}
