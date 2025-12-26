class BeautyModuleConstants {
  static const String vendorBase = '/api/v1/beautybooking/vendor';

  // Dashboard
  static const String vendorDashboardUri = '$vendorBase/dashboard';

  // Bookings
  static const String vendorBookingListAllUri = '$vendorBase/bookings/list/all';
  static const String vendorBookingDetailsUri = '$vendorBase/bookings/details';
  static const String vendorBookingConfirmUri = '$vendorBase/bookings/confirm';
  static const String vendorBookingCompleteUri = '$vendorBase/bookings/complete';
  static const String vendorBookingMarkPaidUri = '$vendorBase/bookings/mark-paid';
  static const String vendorBookingCancelUri = '$vendorBase/bookings/cancel';

  // Staff
  static const String vendorStaffListUri = '$vendorBase/staff/list';
  static const String vendorStaffCreateUri = '$vendorBase/staff/create';
  static const String vendorStaffUpdateUri = '$vendorBase/staff/update';
  static const String vendorStaffDetailsUri = '$vendorBase/staff/details';
  static const String vendorStaffDeleteUri = '$vendorBase/staff/delete';
  static const String vendorStaffStatusUri = '$vendorBase/staff/status';

  // Services
  static const String vendorServiceListUri = '$vendorBase/service/list';
  static const String vendorServiceCreateUri = '$vendorBase/service/create';
  static const String vendorServiceUpdateUri = '$vendorBase/service/update';
  static const String vendorServiceDetailsUri = '$vendorBase/service/details';
  static const String vendorServiceDeleteUri = '$vendorBase/service/delete';
  static const String vendorServiceStatusUri = '$vendorBase/service/status';

  // Calendar
  static const String vendorCalendarAvailabilityUri = '$vendorBase/calendar/availability';
  static const String vendorCalendarBlockCreateUri = '$vendorBase/calendar/blocks/create';
  static const String vendorCalendarBlockDeleteUri = '$vendorBase/calendar/blocks/delete';
}
