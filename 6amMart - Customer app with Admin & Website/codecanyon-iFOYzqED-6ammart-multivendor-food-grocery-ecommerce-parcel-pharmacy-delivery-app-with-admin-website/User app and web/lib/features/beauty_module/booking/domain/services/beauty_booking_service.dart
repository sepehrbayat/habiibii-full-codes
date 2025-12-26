import 'package:image_picker/image_picker.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/models/beauty_booking_model.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/models/beauty_booking_conversation_model.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/repositories/beauty_booking_repository_interface.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/services/beauty_booking_service_interface.dart';

class BeautyBookingService implements BeautyBookingServiceInterface {
  final BeautyBookingRepositoryInterface beautyBookingRepositoryInterface;

  BeautyBookingService({required this.beautyBookingRepositoryInterface});

  @override
  Future<BeautyBookingModel?> createBooking(Map<String, dynamic> bookingData) async {
    return await beautyBookingRepositoryInterface.createBooking(bookingData);
  }

  @override
  Future<List<BeautyBookingModel>> getBookings({int? offset, String? status}) async {
    return await beautyBookingRepositoryInterface.getBookings(
      offset: offset,
      status: status,
    );
  }

  @override
  Future<BeautyBookingModel?> getBookingDetails(int bookingId) async {
    return await beautyBookingRepositoryInterface.getBookingDetails(bookingId);
  }

  @override
  Future<bool> cancelBooking(int bookingId, String reason) async {
    return await beautyBookingRepositoryInterface.cancelBooking(bookingId, reason);
  }

  @override
  Future<bool> rescheduleBooking(int bookingId, String date, String time, {String? notes, int? staffId}) async {
    return await beautyBookingRepositoryInterface.rescheduleBooking(
      bookingId,
      date,
      time,
      notes: notes,
      staffId: staffId,
    );
  }

  @override
  Future<Map<String, dynamic>?> checkAvailability(Map<String, dynamic> data) async {
    return await beautyBookingRepositoryInterface.checkAvailability(data);
  }

  @override
  Future<BeautyBookingConversationModel?> getBookingConversation(int bookingId) async {
    return await beautyBookingRepositoryInterface.getBookingConversation(bookingId);
  }

  @override
  Future<bool> sendBookingMessage(int bookingId, String message, {XFile? file}) async {
    return await beautyBookingRepositoryInterface.sendBookingMessage(bookingId, message, file: file);
  }

  @override
  Future<List<Map<String, dynamic>>> getWaitlist({int? offset, int? limit}) async {
    return await beautyBookingRepositoryInterface.getWaitlist(offset: offset, limit: limit);
  }

  @override
  Future<bool> joinWaitlist(Map<String, dynamic> data) async {
    return await beautyBookingRepositoryInterface.joinWaitlist(data);
  }

  @override
  Future<bool> leaveWaitlist(int waitlistId) async {
    return await beautyBookingRepositoryInterface.leaveWaitlist(waitlistId);
  }
}
