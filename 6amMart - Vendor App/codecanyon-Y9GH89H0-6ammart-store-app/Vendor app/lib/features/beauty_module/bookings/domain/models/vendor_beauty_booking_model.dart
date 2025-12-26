class VendorBeautyBookingModel {
  final int? id;
  final int? userId;
  final int? salonId;
  final int? serviceId;
  final int? staffId;
  final String? bookingDate;
  final String? bookingTime;
  final String? bookingDateTime;
  final String? status;
  final String? paymentStatus;
  final double? totalAmount;
  final String? notes;
  final String? cancellationReason;
  final String? createdAt;
  final String? updatedAt;
  final String? customerName;
  final String? serviceName;
  final String? staffName;

  VendorBeautyBookingModel({
    this.id,
    this.userId,
    this.salonId,
    this.serviceId,
    this.staffId,
    this.bookingDate,
    this.bookingTime,
    this.bookingDateTime,
    this.status,
    this.paymentStatus,
    this.totalAmount,
    this.notes,
    this.cancellationReason,
    this.createdAt,
    this.updatedAt,
    this.customerName,
    this.serviceName,
    this.staffName,
  });

  factory VendorBeautyBookingModel.fromJson(Map<String, dynamic> json) {
    return VendorBeautyBookingModel(
      id: json['id'],
      userId: json['user_id'],
      salonId: json['salon_id'],
      serviceId: json['service_id'],
      staffId: json['staff_id'],
      bookingDate: json['booking_date'],
      bookingTime: json['booking_time'],
      bookingDateTime: json['booking_date_time'],
      status: json['status'],
      paymentStatus: json['payment_status'],
      totalAmount: json['total_amount']?.toDouble(),
      notes: json['notes'],
      cancellationReason: json['cancellation_reason'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
      customerName: json['user']?['f_name'] != null && json['user']?['l_name'] != null
          ? '${json['user']['f_name']} ${json['user']['l_name']}'
          : null,
      serviceName: json['service']?['name'],
      staffName: json['staff']?['name'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'salon_id': salonId,
      'service_id': serviceId,
      'staff_id': staffId,
      'booking_date': bookingDate,
      'booking_time': bookingTime,
      'booking_date_time': bookingDateTime,
      'status': status,
      'payment_status': paymentStatus,
      'total_amount': totalAmount,
      'notes': notes,
      'cancellation_reason': cancellationReason,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  bool get canConfirm => status == 'pending';
  bool get canComplete => status == 'confirmed';
  bool get canCancel => status != 'cancelled' && status != 'completed';
}
