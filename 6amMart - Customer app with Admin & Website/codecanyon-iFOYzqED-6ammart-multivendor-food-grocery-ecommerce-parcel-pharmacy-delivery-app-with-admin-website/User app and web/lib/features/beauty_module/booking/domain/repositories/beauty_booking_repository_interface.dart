import 'package:image_picker/image_picker.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/models/beauty_booking_model.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/models/beauty_booking_conversation_model.dart';
import 'package:sixam_mart/interfaces/repository_interface.dart';

abstract class BeautyBookingRepositoryInterface implements RepositoryInterface<BeautyBookingModel> {
  Future<BeautyBookingModel?> createBooking(Map<String, dynamic> bookingData);
  Future<List<BeautyBookingModel>> getBookings({int? offset, String? status});
  Future<BeautyBookingModel?> getBookingDetails(int bookingId);
  Future<bool> cancelBooking(int bookingId, String reason);
  Future<bool> rescheduleBooking(int bookingId, String date, String time, {String? notes, int? staffId});
  Future<Map<String, dynamic>?> checkAvailability(Map<String, dynamic> data);
  Future<BeautyBookingConversationModel?> getBookingConversation(int bookingId);
  Future<bool> sendBookingMessage(int bookingId, String message, {XFile? file});
  Future<List<Map<String, dynamic>>> getWaitlist({int? offset, int? limit});
  Future<bool> joinWaitlist(Map<String, dynamic> data);
  Future<bool> leaveWaitlist(int waitlistId);
}
