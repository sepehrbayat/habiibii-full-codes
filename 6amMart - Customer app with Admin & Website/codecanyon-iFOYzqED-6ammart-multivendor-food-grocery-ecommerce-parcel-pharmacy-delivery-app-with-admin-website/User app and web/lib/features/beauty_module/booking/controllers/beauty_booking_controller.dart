import 'package:flutter/foundation.dart';
import 'package:get/get.dart';
import 'package:image_picker/image_picker.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/models/beauty_booking_model.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/models/beauty_booking_conversation_model.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/services/beauty_booking_service_interface.dart';
import 'package:sixam_mart/helper/beauty_route_helper.dart';

class BeautyBookingController extends GetxController implements GetxService {
  final BeautyBookingServiceInterface beautyBookingServiceInterface;

  BeautyBookingController({required this.beautyBookingServiceInterface});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  bool _isCreating = false;
  bool get isCreating => _isCreating;

  bool _isCancelling = false;
  bool get isCancelling => _isCancelling;

  bool _isRescheduling = false;
  bool get isRescheduling => _isRescheduling;

  List<BeautyBookingModel>? _bookings;
  List<BeautyBookingModel>? get bookings => _bookings;

  List<BeautyBookingModel>? _upcomingBookings;
  List<BeautyBookingModel>? get upcomingBookings => _upcomingBookings;

  List<BeautyBookingModel>? _pastBookings;
  List<BeautyBookingModel>? get pastBookings => _pastBookings;

  BeautyBookingModel? _selectedBooking;
  BeautyBookingModel? get selectedBooking => _selectedBooking;

  String? _selectedStatus;
  String? get selectedStatus => _selectedStatus;

  Map<String, dynamic>? _availabilityData;
  Map<String, dynamic>? get availabilityData => _availabilityData;

  BeautyBookingConversationModel? _conversation;
  BeautyBookingConversationModel? get conversation => _conversation;

  bool _isConversationLoading = false;
  bool get isConversationLoading => _isConversationLoading;

  // Booking creation form data
  int? _selectedSalonId;
  int? get selectedSalonId => _selectedSalonId;

  int? _selectedServiceId;
  int? get selectedServiceId => _selectedServiceId;

  int? _selectedStaffId;
  int? get selectedStaffId => _selectedStaffId;

  String? _selectedDate;
  String? get selectedDate => _selectedDate;

  String? _selectedTime;
  String? get selectedTime => _selectedTime;

  String? _bookingNotes;
  String? get bookingNotes => _bookingNotes;

  /// Create a new booking
  Future<BeautyBookingModel?> createBooking(Map<String, dynamic> bookingData) async {
    _isCreating = true;
    update();

    try {
      final booking = await beautyBookingServiceInterface.createBooking(bookingData);
      
      if (booking != null) {
        // Add to bookings list if it exists
        if (_bookings != null) {
          _bookings!.insert(0, booking);
        }
        
        // Clear form data
        clearBookingForm();
        
        // Show success message
        Get.showSnackbar(GetSnackBar(
          message: 'Booking created successfully',
          duration: Duration(seconds: 2),
          backgroundColor: Get.theme.primaryColor,
        ));
      }
      
      return booking;
    } catch (e) {
      if(kDebugMode) print('Error creating booking: $e');
      Get.showSnackbar(GetSnackBar(
        message: 'Failed to create booking. Please try again.',
        duration: Duration(seconds: 3),
        backgroundColor: Get.theme.colorScheme.error,
      ));
      return null;
    } finally {
      _isCreating = false;
      update();
    }
  }

  /// Get list of bookings with optional status filter
  Future<void> getBookings({int offset = 0, bool reload = false}) async {
    if (reload) {
      _bookings = null;
    }
    
    _isLoading = true;
    update();

    try {
      final bookings = await beautyBookingServiceInterface.getBookings(
        offset: offset,
        status: _selectedStatus,
      );
      
      if (offset == 0) {
        _bookings = bookings;
      } else {
        _bookings?.addAll(bookings);
      }
      
      // Separate into upcoming and past
      _separateBookings();
    } catch (e) {
      if(kDebugMode) print('Error loading bookings: $e');
    }

    _isLoading = false;
    update();
  }

  /// Get booking details
  Future<void> getBookingDetails(int bookingId) async {
    _isLoading = true;
    update();

    try {
      _selectedBooking = await beautyBookingServiceInterface.getBookingDetails(bookingId);
    } catch (e) {
      if(kDebugMode) print('Error loading booking details: $e');
      Get.showSnackbar(GetSnackBar(
        message: 'Failed to load booking details',
        duration: Duration(seconds: 2),
        backgroundColor: Get.theme.colorScheme.error,
      ));
    }

    _isLoading = false;
    update();
  }

  /// Cancel a booking
  Future<bool> cancelBooking(int bookingId, String reason) async {
    _isLoading = true;
    _isCancelling = true;
    update();

    try {
      final success = await beautyBookingServiceInterface.cancelBooking(bookingId, reason);
      
      if (success) {
        // Refresh bookings list
        await getBookings(reload: true);
        
        // Update selected booking if it's the one being cancelled
        if (_selectedBooking?.id == bookingId) {
          await getBookingDetails(bookingId);
        }
        
        Get.showSnackbar(GetSnackBar(
          message: 'Booking cancelled successfully',
          duration: Duration(seconds: 2),
          backgroundColor: Get.theme.primaryColor,
        ));
      } else {
        Get.showSnackbar(GetSnackBar(
          message: 'Failed to cancel booking',
          duration: Duration(seconds: 2),
          backgroundColor: Get.theme.colorScheme.error,
        ));
      }
      
      return success;
    } catch (e) {
      if(kDebugMode) print('Error cancelling booking: $e');
      Get.showSnackbar(GetSnackBar(
        message: 'An error occurred while cancelling',
        duration: Duration(seconds: 2),
        backgroundColor: Get.theme.colorScheme.error,
      ));
      return false;
    } finally {
      _isLoading = false;
      _isCancelling = false;
      update();
    }
  }

  /// Reschedule a booking
  Future<bool> rescheduleBooking(int bookingId, String date, String time, {String? notes, int? staffId}) async {
    _isLoading = true;
    _isRescheduling = true;
    update();

    try {
      final success = await beautyBookingServiceInterface.rescheduleBooking(
        bookingId,
        date,
        time,
        notes: notes,
        staffId: staffId,
      );
      
      if (success) {
        // Refresh bookings list
        await getBookings(reload: true);
        
        // Update selected booking
        if (_selectedBooking?.id == bookingId) {
          await getBookingDetails(bookingId);
        }
        
        Get.showSnackbar(GetSnackBar(
          message: 'Booking rescheduled successfully',
          duration: Duration(seconds: 2),
          backgroundColor: Get.theme.primaryColor,
        ));
      } else {
        Get.showSnackbar(GetSnackBar(
          message: 'Failed to reschedule booking',
          duration: Duration(seconds: 2),
          backgroundColor: Get.theme.colorScheme.error,
        ));
      }
      
      return success;
    } catch (e) {
      if(kDebugMode) print('Error rescheduling booking: $e');
      Get.showSnackbar(GetSnackBar(
        message: 'An error occurred while rescheduling',
        duration: Duration(seconds: 2),
        backgroundColor: Get.theme.colorScheme.error,
      ));
      return false;
    } finally {
      _isLoading = false;
      _isRescheduling = false;
      update();
    }
  }

  /// Check availability for a booking
  Future<bool> checkAvailability(Map<String, dynamic> data) async {
    _isLoading = true;
    update();

    try {
      _availabilityData = await beautyBookingServiceInterface.checkAvailability(data);
      
      if (_availabilityData != null && _availabilityData!['available'] == true) {
        return true;
      } else {
        Get.showSnackbar(GetSnackBar(
          message: _availabilityData?['message'] ?? 'Selected time slot is not available',
          duration: Duration(seconds: 3),
          backgroundColor: Get.theme.colorScheme.secondary,
        ));
        return false;
      }
    } catch (e) {
      if(kDebugMode) print('Error checking availability: $e');
      return false;
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<void> getBookingConversation(int bookingId) async {
    _isConversationLoading = true;
    update();

    try {
      _conversation = await beautyBookingServiceInterface.getBookingConversation(bookingId);
    } catch (e) {
      if(kDebugMode) print('Error loading booking conversation: $e');
    } finally {
      _isConversationLoading = false;
      update();
    }
  }

  Future<bool> sendBookingMessage(int bookingId, String message, {XFile? file}) async {
    try {
      final success = await beautyBookingServiceInterface.sendBookingMessage(bookingId, message, file: file);
      if (success) {
        await getBookingConversation(bookingId);
      }
      return success;
    } catch (e) {
      if(kDebugMode) print('Error sending booking message: $e');
      return false;
    }
  }

  Future<List<Map<String, dynamic>>> getWaitlist({int? offset, int? limit}) async {
    return await beautyBookingServiceInterface.getWaitlist(offset: offset, limit: limit);
  }

  Future<bool> joinWaitlist(Map<String, dynamic> data) async {
    return await beautyBookingServiceInterface.joinWaitlist(data);
  }

  Future<bool> leaveWaitlist(int waitlistId) async {
    return await beautyBookingServiceInterface.leaveWaitlist(waitlistId);
  }

  /// Set selected status filter
  void setSelectedStatus(String? status) {
    _selectedStatus = status;
    update();
    getBookings(reload: true);
  }

  /// Set booking form data
  void setBookingFormData({
    int? salonId,
    int? serviceId,
    int? staffId,
    String? date,
    String? time,
    String? notes,
  }) {
    if (salonId != null) _selectedSalonId = salonId;
    if (serviceId != null) _selectedServiceId = serviceId;
    if (staffId != null) _selectedStaffId = staffId;
    if (date != null) _selectedDate = date;
    if (time != null) _selectedTime = time;
    if (notes != null) _bookingNotes = notes;
    update();
  }

  /// Clear booking form
  void clearBookingForm() {
    _selectedSalonId = null;
    _selectedServiceId = null;
    _selectedStaffId = null;
    _selectedDate = null;
    _selectedTime = null;
    _bookingNotes = null;
    _availabilityData = null;
    update();
  }

  /// Separate bookings into upcoming and past
  void _separateBookings() {
    if (_bookings == null) return;
    
    _upcomingBookings = _bookings!.where((booking) => booking.isUpcoming).toList();
    _pastBookings = _bookings!.where((booking) => booking.isPast).toList();
  }

  /// Clear selected booking
  void clearSelectedBooking() {
    _selectedBooking = null;
    update();
  }

  /// Navigate to booking details
  void navigateToBookingDetails(int bookingId) {
    // Get.toNamed(RouteHelper.getBeautyBookingDetailsRoute(bookingId));
  }

  /// Navigate to create booking
  void navigateToCreateBooking({int? salonId, int? serviceId}) {
    if (salonId != null) _selectedSalonId = salonId;
    if (serviceId != null) _selectedServiceId = serviceId;
    Get.toNamed(BeautyRouteHelper.getBeautyCreateBookingRoute(
      salonId: salonId,
      serviceId: serviceId,
    ));
  }
}