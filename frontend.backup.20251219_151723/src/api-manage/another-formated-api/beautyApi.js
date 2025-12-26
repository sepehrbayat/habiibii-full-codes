import MainApi from "../MainApi";

export const BeautyApi = {
  // Salon APIs
  searchSalons: (params) => {
    const queryParams = new URLSearchParams();
    if (params.search) queryParams.append("search", params.search);
    if (params.latitude) queryParams.append("latitude", params.latitude);
    if (params.longitude) queryParams.append("longitude", params.longitude);
    if (params.category_id) queryParams.append("category_id", params.category_id);
    if (params.business_type) queryParams.append("business_type", params.business_type);
    if (params.min_rating) queryParams.append("min_rating", params.min_rating);
    if (params.radius) queryParams.append("radius", params.radius);
    return MainApi.get(`/api/v1/beautybooking/salons/search?${queryParams.toString()}`);
  },
  
  getSalonDetails: (id) => {
    return MainApi.get(`/api/v1/beautybooking/salons/${id}`);
  },
  
  getPopularSalons: () => {
    return MainApi.get("/api/v1/beautybooking/salons/popular");
  },
  
  getTopRatedSalons: () => {
    return MainApi.get("/api/v1/beautybooking/salons/top-rated");
  },
  
  getMonthlyTopRatedSalons: (year, month) => {
    const queryParams = new URLSearchParams();
    if (year) queryParams.append("year", year);
    if (month) queryParams.append("month", month);
    return MainApi.get(`/api/v1/beautybooking/salons/monthly-top-rated?${queryParams.toString()}`);
  },
  
  getTrendingClinics: (year, month) => {
    const queryParams = new URLSearchParams();
    if (year) queryParams.append("year", year);
    if (month) queryParams.append("month", month);
    return MainApi.get(`/api/v1/beautybooking/salons/trending-clinics?${queryParams.toString()}`);
  },
  
  getSalonReviews: (salonId, params = {}) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/reviews/${salonId}?${queryParams.toString()}`);
  },
  
  // Booking APIs
  createBooking: (bookingData) => {
    const payload = { ...bookingData };
    if (payload.payment_method === "online") {
      payload.payment_method = "digital_payment";
    }
    return MainApi.post("/api/v1/beautybooking/bookings", payload);
  },
  
  getBookings: (params = {}) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    if (params.status) queryParams.append("status", params.status);
    if (params.type) queryParams.append("type", params.type);
    // Support date_range -> date_from/date_to conversion for Laravel
    if (params.date_range) {
      const dates = params.date_range.split(",");
      if (dates.length === 2) {
        queryParams.append("date_from", dates[0].trim());
        queryParams.append("date_to", dates[1].trim());
      }
    } else {
      if (params.date_from) queryParams.append("date_from", params.date_from);
      if (params.date_to) queryParams.append("date_to", params.date_to);
    }
    // Alias service_type to service_id
    if (params.service_type) {
      queryParams.append("service_id", params.service_type);
    } else if (params.service_id) {
      queryParams.append("service_id", params.service_id);
    }
    if (params.staff_id) queryParams.append("staff_id", params.staff_id);
    return MainApi.get(`/api/v1/beautybooking/bookings?${queryParams.toString()}`);
  },
  
  getBookingDetails: (id) => {
    return MainApi.get(`/api/v1/beautybooking/bookings/${id}`);
  },

  rescheduleBooking: (id, rescheduleData) => {
    return MainApi.put(`/api/v1/beautybooking/bookings/${id}/reschedule`, rescheduleData);
  },
  
  cancelBooking: (id, cancellationReason) => {
    return MainApi.put(`/api/v1/beautybooking/bookings/${id}/cancel`, {
      cancellation_reason: cancellationReason,
    });
  },
  
  checkAvailability: (availabilityData) => {
    return MainApi.post("/api/v1/beautybooking/availability/check", availabilityData);
  },
  
  getBookingConversation: (id) => {
    return MainApi.get(`/api/v1/beautybooking/bookings/${id}/conversation`);
  },

  sendBookingMessage: (id, messageData) => {
    return MainApi.post(`/api/v1/beautybooking/bookings/${id}/conversation`, messageData);
  },

  getDashboardSummary: () => {
    return MainApi.get("/api/v1/beautybooking/dashboard/summary");
  },

  // Notifications
  getNotifications: (params = {}) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(
      `/api/v1/beautybooking/notifications?${queryParams.toString()}`
    );
  },

  markReadNotifications: (ids = []) => {
    return MainApi.post("/api/v1/beautybooking/notifications/mark-read", { ids });
  },
  
  processPayment: (paymentData) => {
    return MainApi.post("/api/v1/beautybooking/payment", paymentData);
  },
  
  // Package APIs
  getPackages: (params) => {
    const queryParams = new URLSearchParams();
    if (params.salon_id) queryParams.append("salon_id", params.salon_id);
    if (params.service_id) queryParams.append("service_id", params.service_id);
    if (params.per_page) queryParams.append("per_page", params.per_page);
    if (params.limit) queryParams.append("limit", params.limit); // پشتیبانی از هر دو
    if (params.offset) queryParams.append("offset", params.offset); // اضافه کردن offset
    return MainApi.get(`/api/v1/beautybooking/packages?${queryParams.toString()}`);
  },
  
  getPackageDetails: (id) => {
    return MainApi.get(`/api/v1/beautybooking/packages/${id}`);
  },

  getPackageUsageHistory: (id) => {
    return MainApi.get(`/api/v1/beautybooking/packages/${id}/usage-history`);
  },
  
  purchasePackage: (id, paymentMethod) => {
    // Convert 'online' to 'digital_payment' for Laravel compatibility
    const convertedPaymentMethod = paymentMethod === 'online' ? 'digital_payment' : paymentMethod;
    return MainApi.post(`/api/v1/beautybooking/packages/${id}/purchase`, {
      payment_method: convertedPaymentMethod,
    });
  },
  
  getPackageStatus: (id) => {
    return MainApi.get(`/api/v1/beautybooking/packages/${id}/status`);
  },
  
  // Gift Card APIs
  purchaseGiftCard: (giftCardData) => {
    const payload = { ...giftCardData };
    if (payload.payment_method === "online") {
      payload.payment_method = "digital_payment";
    }
    return MainApi.post("/api/v1/beautybooking/gift-card/purchase", payload);
  },
  
  redeemGiftCard: (code) => {
    return MainApi.post("/api/v1/beautybooking/gift-card/redeem", { code });
  },
  
  getGiftCards: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/gift-card/list?${queryParams.toString()}`);
  },
  
  // Loyalty APIs
  getLoyaltyPoints: () => {
    return MainApi.get("/api/v1/beautybooking/loyalty/points");
  },
  
  getLoyaltyCampaigns: (params = {}) => {
    const queryParams = new URLSearchParams();
    if (params.salon_id) queryParams.append("salon_id", params.salon_id);
    if (params.per_page) queryParams.append("per_page", params.per_page);
    if (params.limit) queryParams.append("limit", params.limit); // پشتیبانی از هر دو
    if (params.offset) queryParams.append("offset", params.offset); // اضافه کردن offset
    return MainApi.get(`/api/v1/beautybooking/loyalty/campaigns?${queryParams.toString()}`);
  },
  
  redeemLoyaltyPoints: (redeemData) => {
    return MainApi.post("/api/v1/beautybooking/loyalty/redeem", redeemData);
  },
  
  // Consultation APIs
  getConsultations: (params) => {
    if (!params?.salon_id) {
      throw new Error("salon_id is required");
    }
    const queryParams = new URLSearchParams();
    queryParams.append("salon_id", params.salon_id);
    if (params.consultation_type) queryParams.append("consultation_type", params.consultation_type);
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/consultations/list?${queryParams.toString()}`);
  },
  
  bookConsultation: (consultationData) => {
    const payload = { ...consultationData };
    if (payload.payment_method === "online") {
      payload.payment_method = "digital_payment";
    }
    return MainApi.post("/api/v1/beautybooking/consultations/book", payload);
  },
  
  checkConsultationAvailability: (availabilityData) => {
    return MainApi.post("/api/v1/beautybooking/consultations/check-availability", availabilityData);
  },
  
  // Retail APIs
  getRetailProducts: (params = {}) => {
    if (!params.salon_id) {
      throw new Error("salon_id is required");
    }
    const queryParams = new URLSearchParams();
    queryParams.append("salon_id", params.salon_id);
    if (params.category_id) queryParams.append("category_id", params.category_id);
    if (params.category) queryParams.append("category", params.category); // پشتیبانی از هر دو
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/retail/products?${queryParams.toString()}`);
  },
  
  createRetailOrder: (orderData) => {
    const payload = { ...orderData };
    if (payload.payment_method === "online") {
      payload.payment_method = "digital_payment";
    }
    return MainApi.post("/api/v1/beautybooking/retail/orders", payload);
  },

  getRetailOrders: (params = {}) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    if (params.status) queryParams.append("status", params.status);
    return MainApi.get(`/api/v1/beautybooking/retail/orders?${queryParams.toString()}`);
  },

  getRetailOrderDetails: (id) => {
    return MainApi.get(`/api/v1/beautybooking/retail/orders/${id}`);
  },
  
  // Service Suggestions
  getServiceSuggestions: (serviceId, salonId) => {
    const queryParams = new URLSearchParams();
    if (salonId) queryParams.append("salon_id", salonId);
    return MainApi.get(`/api/v1/beautybooking/services/${serviceId}/suggestions?${queryParams.toString()}`);
  },
  
  // Category APIs
  getCategories: () => {
    return MainApi.get("/api/v1/beautybooking/salons/category-list");
  },
  
  // Review APIs
  submitReview: (reviewData) => {
    // MainApi.post will handle FormData automatically if reviewData is FormData
    // Otherwise it will send as JSON
    return MainApi.post("/api/v1/beautybooking/reviews", reviewData);
  },
  
  getReviews: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/reviews?${queryParams.toString()}`);
  },

  // Wallet
  getWalletTransactions: (params = {}) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    if (params.transaction_type) queryParams.append("transaction_type", params.transaction_type);
    return MainApi.get(
      `/api/v1/beautybooking/wallet/transactions?${queryParams.toString()}`
    );
  },
};

