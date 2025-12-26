class VendorBeautyModuleConstants {
  // Beauty Module Vendor API Endpoints
  
  static const String base = '/api/v1/beautybooking/vendor';
  
  // Dashboard
  static const String vendorDashboardUri = '$base/bookings/list/all';
  
  // Bookings
  static const String vendorBookingsUri = '$base/bookings/list/all';
  static const String vendorBookingDetailsUri = '$base/bookings/details'; // pass booking_id as query
  static const String vendorConfirmBookingUri = '$base/bookings/confirm';
  static const String vendorCompleteBookingUri = '$base/bookings/complete';
  static const String vendorCancelBookingUri = '$base/bookings/cancel';
  static const String vendorMarkPaidBookingUri = '$base/bookings/mark-paid';
  
  // Services
  static const String vendorServicesUri = '$base/service/list';
  static const String vendorServiceDetailsUri = '$base/service/details'; // append /{id}
  static const String vendorCreateServiceUri = '$base/service/create';
  static const String vendorUpdateServiceUri = '$base/service/update'; // append /{id}
  static const String vendorDeleteServiceUri = '$base/service/delete'; // append /{id}
  static const String vendorToggleServiceStatusUri = '$base/service/status'; // append /{id}
  
  // Staff
  static const String vendorStaffUri = '$base/staff/list';
  static const String vendorStaffDetailsUri = '$base/staff/details'; // append /{id}
  static const String vendorCreateStaffUri = '$base/staff/create';
  static const String vendorUpdateStaffUri = '$base/staff/update'; // append /{id}
  static const String vendorDeleteStaffUri = '$base/staff/delete'; // append /{id}
  static const String vendorToggleStaffStatusUri = '$base/staff/status'; // append /{id}
  
  // Calendar
  static const String vendorCalendarAvailabilityUri = '$base/calendar/availability';
  static const String vendorCalendarBlocksUri = '$base/calendar/blocks';
  static const String vendorCreateCalendarBlockUri = '$base/calendar/blocks/create';
  static const String vendorDeleteCalendarBlockUri = '$base/calendar/blocks/delete';
  
  // Retail
  static const String vendorRetailProductsUri = '$base/retail/products';
  static const String vendorRetailProductDetailsUri = '$base/retail/products';
  static const String vendorRetailOrdersUri = '$base/retail/orders';
  static const String vendorRetailOrderDetailsUri = '$base/retail/orders';
  static const String vendorCreateRetailProductUri = '$base/retail/products';
  static const String vendorUpdateRetailProductUri = '$base/retail/products';
  static const String vendorDeleteRetailProductUri = '$base/retail/products';
  
  // Finance
  static const String vendorFinanceSummaryUri = '$base/finance/payout-summary';
  static const String vendorFinanceTransactionsUri = '$base/finance/transactions';
  static const String vendorFinancePayoutsUri = '$base/finance/payout-summary';
  static const String vendorRequestPayoutUri = '$base/finance/request-payout';
  
  // Badges
  static const String vendorBadgesUri = '$base/badge/status';
  static const String vendorBadgeStatusUri = '$base/badge/status';
  
  // Subscription
  static const String vendorSubscriptionPlansUri = '$base/subscription/plans';
  static const String vendorSubscriptionCurrentUri = '$base/subscription/history';
  static const String vendorSubscribeUri = '$base/subscription/purchase';
  static const String vendorCancelSubscriptionUri = '$base/subscription/history';
  static const String vendorSubscriptionHistoryUri = '$base/subscription/history';
}
