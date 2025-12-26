class BeautyModuleConstants {
  // Beauty Module API Endpoints
  
  static const String base = '/api/v1/beautybooking';

  // Retail Products
  static const String beautyRetailProductsUri = '$base/retail/products';
  static const String beautyRetailProductDetailsUri = '$base/retail/products';
  static const String beautyPopularProductsUri = '$base/retail/products';
  // Retail orders (server does not support cart endpoints; call order APIs directly)
  static const String beautyAddToCartUri = '$base/retail/orders';
  static const String beautyUpdateCartUri = '$base/retail/orders';
  static const String beautyRemoveFromCartUri = '$base/retail/orders';
  static const String beautyGetCartUri = '$base/retail/orders';
  static const String beautyClearCartUri = '$base/retail/orders';
  static const String beautyPlaceOrderUri = '$base/retail/orders';
  static const String beautyGetOrdersUri = '$base/retail/orders';
  static const String beautyGetOrderDetailsUri = '$base/retail/orders';
  static const String beautyCancelOrderUri = '$base/retail/orders/cancel';
  static const String beautyTrackOrderUri = '$base/retail/orders/track';
  
  // Packages
  static const String beautyPackagesUri = '$base/packages';
  static const String beautyPackageDetailsUri = '$base/packages';
  static const String beautyMyPackagesUri = '$base/packages/my';
  static const String beautyActivePackagesUri = '$base/packages/active';
  static const String beautyPurchasedPackagesUri = '$base/packages/purchased';
  static const String beautyPopularPackagesUri = '$base/packages/popular';
  static const String beautyPurchasePackageUri = '$base/packages';
  static const String beautyActivatePackageUri = '$base/packages/activate';
  static const String beautyUsePackageServiceUri = '$base/packages/use-service';
  static const String beautyTransferPackageUri = '$base/packages/transfer';
  static const String beautyPackageHistoryUri = '$base/packages/history';
  static const String beautyPackageUsageUri = '$base/packages/usage';
  static const String beautyPackageUsageHistoryUri = '$base/packages';
  static const String beautyPackageStatusUri = '$base/packages';
  static const String beautyExtendPackageUri = '$base/packages/extend';
  static const String beautyCancelPackageUri = '$base/packages/cancel';
  static const String beautyPackageRefundUri = '$base/packages/refund';
  // Unsupported package extras removed (no backend routes for gift/share/review)
  
  // Bookings
  static const String beautyBookingsUri = '$base/bookings';
  static const String beautyBookingDetailsUri = '$base/bookings';
  static const String beautyCreateBookingUri = '$base/bookings';
  static const String beautyCancelBookingUri = '$base/bookings'; // append /{id}/cancel
  static const String beautyRescheduleBookingUri = '$base/bookings'; // append /{id}/reschedule
  static const String beautyBookingSlotsUri = '$base/availability/check';
  static const String beautyBookingConversationUri = '$base/bookings'; // append /{id}/conversation
  static const String beautyWaitlistUri = '$base/waitlist';
  static const String beautyJoinWaitlistUri = '$base/waitlist/join';
  static const String beautyLeaveWaitlistUri = '$base/waitlist'; // append /{id}
  
  // Salons
  static const String beautySalonsUri = '$base/salons';
  static const String beautySalonDetailsUri = '$base/salons';
  static const String beautySalonServicesUri = '$base/salons/services';
  static const String beautySalonStaffUri = '$base/salons/staff';
  static const String beautySalonReviewsUri = '$base/reviews';
  static const String beautySalonSearchUri = '$base/salons/search';
  static const String beautySalonPopularUri = '$base/salons/popular';
  static const String beautySalonTopRatedUri = '$base/salons/top-rated';
  static const String beautySalonMonthlyTopRatedUri = '$base/salons/monthly-top-rated';
  static const String beautySalonTrendingUri = '$base/salons/trending-clinics';
  static const String beautySalonCategoriesUri = '$base/salons/category-list';
  static const String beautyServiceSuggestionsUri = '$base/services'; // append /{id}/suggestions
  static const String beautyServiceSuggestionsDetailUri = '$base/services'; // append /{id}
  
  // Gift Cards
  static const String beautyGiftCardsUri = '$base/gift-card/list';
  static const String beautyPurchaseGiftCardUri = '$base/gift-card/purchase';
  static const String beautyRedeemGiftCardUri = '$base/gift-card/redeem';
  
  // Loyalty Points (for future implementation)
  static const String beautyLoyaltyPointsUri = '$base/loyalty/points';
  static const String beautyLoyaltyCampaignsUri = '$base/loyalty/campaigns';
  static const String beautyRedeemPointsUri = '$base/loyalty/redeem';
  
  // Reviews (for future implementation)
  static const String beautySubmitReviewUri = '$base/reviews';
  static const String beautyUpdateReviewUri = '$base/reviews';
  static const String beautyDeleteReviewUri = '$base/reviews';
  
  // Consultations (for future implementation)
  static const String beautyConsultationsUri = '$base/consultations/list';
  static const String beautyBookConsultationUri = '$base/consultations/book';
  static const String beautyCancelConsultationUri = '$base/consultations/cancel';
  static const String beautyConsultationAvailabilityUri = '$base/consultations/check-availability';

  // Notifications & Wallet
  static const String beautyDashboardSummaryUri = '$base/dashboard/summary';
  static const String beautyNotificationsUri = '$base/notifications';
  static const String beautyNotificationsMarkReadUri = '$base/notifications/mark-read';
  static const String beautyWalletTransactionsUri = '$base/wallet/transactions';
  static const String beautyPaymentUri = '$base/payment';
}
