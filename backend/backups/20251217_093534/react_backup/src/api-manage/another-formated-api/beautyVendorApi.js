import MainApi from "../MainApi";

export const BeautyVendorApi = {
  // Vendor authentication
  loginVendor: (credentials) => {
    return MainApi.post(`/api/v1/auth/seller/login`, credentials);
  },

  // Booking Management APIs
  listBookings: (params) => {
    const queryParams = new URLSearchParams();
    const allParam = params.all || 'all';
    // Apply status filter only when fetching a specific subset
    if (params.status && params.all !== 'all') queryParams.append("status", params.status);
    if (params.date_from) queryParams.append("date_from", params.date_from);
    if (params.date_to) queryParams.append("date_to", params.date_to);
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/vendor/bookings/list/${allParam}?${queryParams.toString()}`);
  },

  getBookingDetails: (bookingId) => {
    const queryParams = new URLSearchParams();
    queryParams.append("booking_id", bookingId);
    return MainApi.get(`/api/v1/beautybooking/vendor/bookings/details?${queryParams.toString()}`);
  },

  confirmBooking: (bookingId) => {
    return MainApi.put(`/api/v1/beautybooking/vendor/bookings/confirm`, {
      booking_id: bookingId,
    });
  },

  completeBooking: (bookingId) => {
    return MainApi.put(`/api/v1/beautybooking/vendor/bookings/complete`, {
      booking_id: bookingId,
    });
  },

  markBookingPaid: (bookingId) => {
    return MainApi.put(`/api/v1/beautybooking/vendor/bookings/mark-paid`, {
      booking_id: bookingId,
    });
  },

  cancelBooking: (bookingId, cancellationReason) => {
    return MainApi.put(`/api/v1/beautybooking/vendor/bookings/cancel`, {
      booking_id: bookingId,
      cancellation_reason: cancellationReason,
    });
  },

  // Staff Management APIs
  listStaff: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/vendor/staff/list?${queryParams.toString()}`);
  },

  createStaff: (staffData) => {
    const formData = new FormData();
    formData.append('name', staffData.name);
    formData.append('email', staffData.email);
    formData.append('phone', staffData.phone);
    if (staffData.avatar) formData.append('avatar', staffData.avatar);
    if (staffData.specializations) formData.append('specializations', JSON.stringify(staffData.specializations));
    if (staffData.working_hours) formData.append('working_hours', JSON.stringify(staffData.working_hours));
    if (staffData.breaks) formData.append('breaks', JSON.stringify(staffData.breaks));
    if (staffData.days_off) formData.append('days_off', JSON.stringify(staffData.days_off));
    formData.append('status', staffData.status || 1);
    return MainApi.post(`/api/v1/beautybooking/vendor/staff/create`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  },

  updateStaff: (id, staffData) => {
    const formData = new FormData();
    formData.append('name', staffData.name);
    formData.append('email', staffData.email);
    formData.append('phone', staffData.phone);
    if (staffData.avatar) formData.append('avatar', staffData.avatar);
    if (staffData.specializations) formData.append('specializations', JSON.stringify(staffData.specializations));
    if (staffData.working_hours) formData.append('working_hours', JSON.stringify(staffData.working_hours));
    if (staffData.breaks) formData.append('breaks', JSON.stringify(staffData.breaks));
    if (staffData.days_off) formData.append('days_off', JSON.stringify(staffData.days_off));
    if (staffData.status !== undefined) formData.append('status', staffData.status);
    return MainApi.post(`/api/v1/beautybooking/vendor/staff/update/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  },

  getStaffDetails: (id) => {
    return MainApi.get(`/api/v1/beautybooking/vendor/staff/details/${id}`);
  },

  deleteStaff: (id) => {
    return MainApi.delete(`/api/v1/beautybooking/vendor/staff/delete/${id}`);
  },

  toggleStaffStatus: (id) => {
    return MainApi.get(`/api/v1/beautybooking/vendor/staff/status/${id}`);
  },

  // Service Management APIs
  listServices: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/vendor/service/list?${queryParams.toString()}`);
  },

  createService: (serviceData) => {
    const formData = new FormData();
    formData.append('category_id', serviceData.category_id);
    formData.append('name', serviceData.name);
    formData.append('description', serviceData.description);
    formData.append('duration_minutes', serviceData.duration_minutes);
    formData.append('price', serviceData.price);
    if (serviceData.image) formData.append('image', serviceData.image);
    if (serviceData.staff_ids) formData.append('staff_ids', JSON.stringify(serviceData.staff_ids));
    formData.append('status', serviceData.status || 1);
    return MainApi.post(`/api/v1/beautybooking/vendor/service/create`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  },

  updateService: (id, serviceData) => {
    const formData = new FormData();
    formData.append('category_id', serviceData.category_id);
    formData.append('name', serviceData.name);
    formData.append('description', serviceData.description);
    formData.append('duration_minutes', serviceData.duration_minutes);
    formData.append('price', serviceData.price);
    if (serviceData.image) formData.append('image', serviceData.image);
    if (serviceData.staff_ids) formData.append('staff_ids', JSON.stringify(serviceData.staff_ids));
    if (serviceData.status !== undefined) formData.append('status', serviceData.status);
    return MainApi.post(`/api/v1/beautybooking/vendor/service/update/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  },

  getServiceDetails: (id) => {
    return MainApi.get(`/api/v1/beautybooking/vendor/service/details/${id}`);
  },

  deleteService: (id) => {
    return MainApi.delete(`/api/v1/beautybooking/vendor/service/delete/${id}`);
  },

  toggleServiceStatus: (id) => {
    return MainApi.get(`/api/v1/beautybooking/vendor/service/status/${id}`);
  },

  // Calendar Management APIs
  getCalendarAvailability: (params) => {
    const queryParams = new URLSearchParams();
    queryParams.append("date", params.date);
    if (params.staff_id) queryParams.append("staff_id", params.staff_id);
    if (params.service_id) queryParams.append("service_id", params.service_id);
    return MainApi.get(`/api/v1/beautybooking/vendor/calendar/availability?${queryParams.toString()}`);
  },

  createCalendarBlock: (blockData) => {
    return MainApi.post(`/api/v1/beautybooking/vendor/calendar/blocks/create`, {
      date: blockData.date,
      start_time: blockData.start_time,
      end_time: blockData.end_time,
      type: blockData.type, // 'break', 'holiday', 'manual_block'
      reason: blockData.reason,
      staff_id: blockData.staff_id || undefined,
    });
  },

  deleteCalendarBlock: (id) => {
    return MainApi.delete(`/api/v1/beautybooking/vendor/calendar/blocks/delete/${id}`);
  },

  // Salon Registration & Profile APIs
  getVendorProfile: () => {
    return MainApi.get(`/api/v1/beautybooking/vendor/profile`);
  },

  registerSalon: (salonData) => {
    return MainApi.post(`/api/v1/beautybooking/vendor/salon/register`, {
      business_type: salonData.business_type, // 'salon' or 'clinic'
      license_number: salonData.license_number,
      license_expiry: salonData.license_expiry,
      working_hours: salonData.working_hours, // Array of {day, open, close}
    });
  },

  uploadDocuments: (documents) => {
    const formData = new FormData();
    documents.forEach((doc, index) => {
      formData.append(`documents[${index}]`, doc);
    });
    return MainApi.post(`/api/v1/beautybooking/vendor/salon/documents/upload`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  },

  updateWorkingHours: (workingHours) => {
    return MainApi.post(`/api/v1/beautybooking/vendor/salon/working-hours/update`, {
      working_hours: workingHours, // Array of {day, open, close}
    });
  },

  manageHolidays: (action, holidays) => {
    return MainApi.post(`/api/v1/beautybooking/vendor/salon/holidays/manage`, {
      action: action, // 'add', 'remove', 'replace'
      holidays: holidays, // Array of date strings
    });
  },

  updateProfile: (profileData) => {
    return MainApi.post(`/api/v1/beautybooking/vendor/profile/update`, {
      license_number: profileData.license_number,
      license_expiry: profileData.license_expiry,
      business_type: profileData.business_type,
      working_hours: profileData.working_hours,
      holidays: profileData.holidays,
    });
  },

  // Retail Management APIs
  listRetailProducts: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/vendor/retail/products?${queryParams.toString()}`);
  },

  createRetailProduct: (productData) => {
    const formData = new FormData();
    formData.append('name', productData.name);
    formData.append('description', productData.description);
    formData.append('price', productData.price);
    formData.append('stock_quantity', productData.stock_quantity);
    formData.append('category', productData.category);
    if (productData.image) formData.append('image', productData.image);
    return MainApi.post(`/api/v1/beautybooking/vendor/retail/products`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  },

  listRetailOrders: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    if (params.status) queryParams.append("status", params.status);
    return MainApi.get(`/api/v1/beautybooking/vendor/retail/orders?${queryParams.toString()}`);
  },

  // Subscription Management APIs
  getSubscriptionPlans: () => {
    return MainApi.get(`/api/v1/beautybooking/vendor/subscription/plans`);
  },

  purchaseSubscription: (subscriptionData) => {
    // Convert 'online' to 'digital_payment' for Laravel compatibility
    const convertedPaymentMethod = subscriptionData.payment_method === 'online' ? 'digital_payment' : subscriptionData.payment_method;
    return MainApi.post(`/api/v1/beautybooking/vendor/subscription/purchase`, {
      subscription_type: subscriptionData.subscription_type, // featured_listing | boost_ads | banner_ads | dashboard_subscription
      duration_days: subscriptionData.duration_days, // 7 | 30 | 365 etc.
      payment_method: convertedPaymentMethod, // 'wallet', 'digital_payment', 'cash_payment'
      ad_position: subscriptionData.ad_position, // homepage | category | search_results (banner_ads only)
      banner_image: subscriptionData.banner_image, // optional
    });
  },

  getSubscriptionHistory: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/vendor/subscription/history?${queryParams.toString()}`);
  },

  // Finance & Reports APIs
  getPayoutSummary: (params) => {
    const queryParams = new URLSearchParams();
    if (params.date_from) queryParams.append("date_from", params.date_from);
    if (params.date_to) queryParams.append("date_to", params.date_to);
    return MainApi.get(`/api/v1/beautybooking/vendor/finance/payout-summary?${queryParams.toString()}`);
  },

  getTransactionHistory: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    if (params.transaction_type) queryParams.append("transaction_type", params.transaction_type);
    if (params.date_from) queryParams.append("date_from", params.date_from);
    if (params.date_to) queryParams.append("date_to", params.date_to);
    return MainApi.get(`/api/v1/beautybooking/vendor/finance/transactions?${queryParams.toString()}`);
  },

  // Badge Status API
  getBadgeStatus: () => {
    return MainApi.get(`/api/v1/beautybooking/vendor/badge/status`);
  },

  // Package Management APIs
  listPackages: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/vendor/packages/list?${queryParams.toString()}`);
  },

  getPackageUsageStats: (params) => {
    const queryParams = new URLSearchParams();
    if (params.date_from) queryParams.append("date_from", params.date_from);
    if (params.date_to) queryParams.append("date_to", params.date_to);
    return MainApi.get(`/api/v1/beautybooking/vendor/packages/usage-stats?${queryParams.toString()}`);
  },

  // Gift Card Management APIs
  listGiftCards: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/vendor/gift-cards/list?${queryParams.toString()}`);
  },

  getRedemptionHistory: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    if (params.date_from) queryParams.append("date_from", params.date_from);
    if (params.date_to) queryParams.append("date_to", params.date_to);
    return MainApi.get(`/api/v1/beautybooking/vendor/gift-cards/redemption-history?${queryParams.toString()}`);
  },

  // Loyalty Campaign Management APIs
  listLoyaltyCampaigns: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    return MainApi.get(`/api/v1/beautybooking/vendor/loyalty/campaigns?${queryParams.toString()}`);
  },

  getPointsHistory: (params) => {
    const queryParams = new URLSearchParams();
    if (params.limit) queryParams.append("limit", params.limit);
    if (params.offset) queryParams.append("offset", params.offset);
    if (params.user_id) queryParams.append("user_id", params.user_id);
    return MainApi.get(`/api/v1/beautybooking/vendor/loyalty/points-history?${queryParams.toString()}`);
  },

  getCampaignStats: (campaignId) => {
    return MainApi.get(`/api/v1/beautybooking/vendor/loyalty/campaign/${campaignId}/stats`);
  },
};

