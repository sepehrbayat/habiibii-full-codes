class VendorBeautyDashboardModel {
  final int? totalBookings;
  final int? pendingBookings;
  final int? completedBookings;
  final int? cancelledBookings;
  final double? totalRevenue;
  final double? todayRevenue;
  final int? totalStaff;
  final int? totalServices;

  VendorBeautyDashboardModel({
    this.totalBookings,
    this.pendingBookings,
    this.completedBookings,
    this.cancelledBookings,
    this.totalRevenue,
    this.todayRevenue,
    this.totalStaff,
    this.totalServices,
  });

  factory VendorBeautyDashboardModel.fromJson(Map<String, dynamic> json) {
    return VendorBeautyDashboardModel(
      totalBookings: json['total_bookings'],
      pendingBookings: json['pending_bookings'],
      completedBookings: json['completed_bookings'],
      cancelledBookings: json['cancelled_bookings'],
      totalRevenue: json['total_revenue']?.toDouble(),
      todayRevenue: json['today_revenue']?.toDouble(),
      totalStaff: json['total_staff'],
      totalServices: json['total_services'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'total_bookings': totalBookings,
      'pending_bookings': pendingBookings,
      'completed_bookings': completedBookings,
      'cancelled_bookings': cancelledBookings,
      'total_revenue': totalRevenue,
      'today_revenue': todayRevenue,
      'total_staff': totalStaff,
      'total_services': totalServices,
    };
  }
}
