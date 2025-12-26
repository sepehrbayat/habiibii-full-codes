class BeautyRouteHelper {
  // Beauty Module Routes
  
  // Retail Products Routes
  static const String beautyRetailProducts = '/beauty-retail-products';
  static const String beautyRetailProductDetails = '/beauty-retail-product-details';
  static const String beautyRetailCart = '/beauty-retail-cart';
  static const String beautyRetailCheckout = '/beauty-retail-checkout';
  static const String beautyRetailOrders = '/beauty-retail-orders';
  static const String beautyRetailOrderDetails = '/beauty-retail-order-details';
  static const String beautyRetailOrderTracking = '/beauty-retail-order-tracking';
  
  // Package Routes
  static const String beautyPackages = '/beauty-packages';
  static const String beautyPackageDetails = '/beauty-package-details';
  static const String beautyMyPackages = '/beauty-my-packages';
  static const String beautyPackagePurchase = '/beauty-package-purchase';
  static const String beautyPackageUsage = '/beauty-package-usage';
  
  // Booking Routes
  static const String beautyBookings = '/beauty-bookings';
  static const String beautyBookingDetails = '/beauty-booking-details';
  static const String beautyCreateBooking = '/beauty-create-booking';
  static const String beautyRescheduleBooking = '/beauty-reschedule-booking';
  static const String beautyBookingConversation = '/beauty-booking-conversation';
  static const String beautyWaitlist = '/beauty-waitlist';
  
  // Salon Routes
  static const String beautySalons = '/beauty-salons';
  static const String beautySalonDetails = '/beauty-salon-details';
  static const String beautySalonServices = '/beauty-salon-services';
  static const String beautySalonStaff = '/beauty-salon-staff';
  
  // Gift Card Routes (for future implementation)
  static const String beautyGiftCards = '/beauty-gift-cards';
  static const String beautyGiftCardHistory = '/beauty-gift-card-history';
  static const String beautyPurchaseGiftCard = '/beauty-purchase-gift-card';
  static const String beautyRedeemGiftCard = '/beauty-redeem-gift-card';
  
  // Loyalty Routes (for future implementation)
  static const String beautyLoyaltyPoints = '/beauty-loyalty-points';
  static const String beautyLoyaltyCampaigns = '/beauty-loyalty-campaigns';
  static const String beautyRedeemPoints = '/beauty-redeem-points';
  
  // Review Routes (for future implementation)
  static const String beautySubmitReview = '/beauty-submit-review';
  static const String beautyEditReview = '/beauty-edit-review';
  static const String beautyReviewCreate = '/beauty-review-create';
  static const String beautyNotifications = '/beauty-notifications';
  static const String beautyDashboard = '/beauty-dashboard';
  static const String beautyWallet = '/beauty-wallet';
  
  // Consultation Routes (for future implementation)
  static const String beautyConsultations = '/beauty-consultations';
  static const String beautyBookConsultation = '/beauty-book-consultation';
  static const String beautyConsultationDetails = '/beauty-consultation-details';
  
  // Route Methods
  static String getBeautyRetailProductsRoute() => beautyRetailProducts;
  
  static String getBeautyRetailProductDetailsRoute(int productId) {
    return '$beautyRetailProductDetails?id=$productId';
  }
  
  static String getBeautyRetailCartRoute() => beautyRetailCart;
  
  static String getBeautyRetailCheckoutRoute() => beautyRetailCheckout;
  
  static String getBeautyRetailOrdersRoute() => beautyRetailOrders;
  
  static String getBeautyRetailOrderDetailsRoute(int orderId) {
    return '$beautyRetailOrderDetails?id=$orderId';
  }
  
  static String getBeautyOrderDetailsRoute(int orderId) {
    return getBeautyRetailOrderDetailsRoute(orderId);
  }
  
  static String getBeautyRetailOrderTrackingRoute(int orderId) {
    return '$beautyRetailOrderTracking?id=$orderId';
  }
  
  static String getBeautyOrderTrackingRoute(int orderId) {
    return getBeautyRetailOrderTrackingRoute(orderId);
  }
  
  static String getBeautyPackagesRoute() => beautyPackages;
  
  static String getBeautyPackageDetailsRoute(int packageId) {
    return '$beautyPackageDetails?id=$packageId';
  }
  
  static String getBeautyMyPackagesRoute() => beautyMyPackages;
  
  static String getBeautyPackagePurchaseRoute(int packageId) {
    return '$beautyPackagePurchase?id=$packageId';
  }
  
  static String getBeautyPackageUsageRoute(int packageId) {
    return '$beautyPackageUsage?id=$packageId';
  }
  
  static String getBeautyBookingsRoute() => beautyBookings;
  
  static String getBeautyBookingDetailsRoute(int bookingId) {
    return '$beautyBookingDetails?id=$bookingId';
  }
  
  static String getBeautyCreateBookingRoute({int? salonId, int? serviceId}) {
    String route = beautyCreateBooking;
    if (salonId != null) {
      route += '?salon_id=$salonId';
      if (serviceId != null) {
        route += '&service_id=$serviceId';
      }
    } else if (serviceId != null) {
      route += '?service_id=$serviceId';
    }
    return route;
  }
  
  static String getBeautyRescheduleBookingRoute(int bookingId) {
    return '$beautyRescheduleBooking?id=$bookingId';
  }

  static String getBeautyBookingConversationRoute(int bookingId) {
    return '$beautyBookingConversation?id=$bookingId';
  }

  static String getBeautyWaitlistRoute() => beautyWaitlist;
  
  static String getBeautySalonsRoute() => beautySalons;
  
  static String getBeautySalonDetailsRoute(int salonId) {
    return '$beautySalonDetails?id=$salonId';
  }
  
  static String getBeautySalonServicesRoute(int salonId) {
    return '$beautySalonServices?salon_id=$salonId';
  }
  
  static String getBeautySalonStaffRoute(int salonId) {
    return '$beautySalonStaff?salon_id=$salonId';
  }
  
  // Gift Card Routes
  static String getBeautyGiftCardsRoute() => beautyGiftCards;

  static String getBeautyGiftCardHistoryRoute() => beautyGiftCardHistory;
  
  static String getBeautyPurchaseGiftCardRoute() => beautyPurchaseGiftCard;
  
  static String getBeautyRedeemGiftCardRoute() => beautyRedeemGiftCard;
  
  // Loyalty Routes
  static String getBeautyLoyaltyPointsRoute() => beautyLoyaltyPoints;
  
  static String getBeautyLoyaltyCampaignsRoute() => beautyLoyaltyCampaigns;
  
  static String getBeautyRedeemPointsRoute() => beautyRedeemPoints;
  
  // Review Routes
  static String getBeautySubmitReviewRoute({int? salonId, int? productId, int? packageId}) {
    String route = beautySubmitReview;
    List<String> params = [];
    if (salonId != null) params.add('salon_id=$salonId');
    if (productId != null) params.add('product_id=$productId');
    if (packageId != null) params.add('package_id=$packageId');
    if (params.isNotEmpty) {
      route += '?' + params.join('&');
    }
    return route;
  }

  static String getBeautyReviewCreateRoute({int? salonId, int? productId, int? packageId}) {
    String route = beautyReviewCreate;
    List<String> params = [];
    if (salonId != null) params.add('salon_id=$salonId');
    if (productId != null) params.add('product_id=$productId');
    if (packageId != null) params.add('package_id=$packageId');
    if (params.isNotEmpty) {
      route += '?' + params.join('&');
    }
    return route;
  }
  
  static String getBeautyEditReviewRoute(int reviewId) {
    return '$beautyEditReview?id=$reviewId';
  }

  static String getBeautyNotificationsRoute() => beautyNotifications;
  static String getBeautyDashboardRoute() => beautyDashboard;
  static String getBeautyWalletRoute() => beautyWallet;
  
  // Consultation Routes
  static String getBeautyConsultationsRoute() => beautyConsultations;
  
  static String getBeautyBookConsultationRoute({int? consultantId}) {
    if (consultantId != null) {
      return '$beautyBookConsultation?consultant_id=$consultantId';
    }
    return beautyBookConsultation;
  }
  
  static String getBeautyConsultationDetailsRoute(int consultationId) {
    return '$beautyConsultationDetails?id=$consultationId';
  }
}
