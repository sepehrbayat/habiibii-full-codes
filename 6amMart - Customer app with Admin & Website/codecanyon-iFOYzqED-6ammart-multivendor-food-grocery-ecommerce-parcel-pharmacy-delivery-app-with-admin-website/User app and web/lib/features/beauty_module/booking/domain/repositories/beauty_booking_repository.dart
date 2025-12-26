import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/models/beauty_booking_model.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/models/beauty_booking_conversation_model.dart';
import 'package:sixam_mart/features/beauty_module/booking/domain/repositories/beauty_booking_repository_interface.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';

class BeautyBookingRepository implements BeautyBookingRepositoryInterface {
  final ApiClient apiClient;
  final SharedPreferences sharedPreferences;

  BeautyBookingRepository({
    required this.apiClient,
    required this.sharedPreferences,
  });

  @override
  Future<BeautyBookingModel?> createBooking(Map<String, dynamic> bookingData) async {
    try {
      final response = await apiClient.postData(
        BeautyModuleConstants.beautyCreateBookingUri,
        bookingData,
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return BeautyBookingModel.fromJson(response.body['data']);
      }
      return null;
    } catch (e) {
      print('Error creating booking: $e');
      return null;
    }
  }

  @override
  Future<List<BeautyBookingModel>> getBookings({int? offset, String? status}) async {
    try {
      String url = '${BeautyModuleConstants.beautyBookingsUri}?offset=${offset ?? 0}&limit=10';
      if (status != null && status.isNotEmpty) {
        url += '&status=$status';
      }
      
      final response = await apiClient.getData(url);
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return (response.body['data'] as List)
            .map((json) => BeautyBookingModel.fromJson(json))
            .toList();
      }
      return [];
    } catch (e) {
      print('Error getting bookings: $e');
      return [];
    }
  }

  @override
  Future<BeautyBookingModel?> getBookingDetails(int bookingId) async {
    try {
      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautyBookingDetailsUri}/$bookingId',
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return BeautyBookingModel.fromJson(response.body['data']);
      }
      return null;
    } catch (e) {
      print('Error getting booking details: $e');
      return null;
    }
  }

  @override
  Future<bool> cancelBooking(int bookingId, String reason) async {
    try {
      final response = await apiClient.putData(
        '${BeautyModuleConstants.beautyCancelBookingUri}/$bookingId/cancel',
        {'cancellation_reason': reason},
      );
      
      return response.statusCode == 200;
    } catch (e) {
      print('Error cancelling booking: $e');
      return false;
    }
  }

  @override
  Future<bool> rescheduleBooking(int bookingId, String date, String time, {String? notes, int? staffId}) async {
    try {
      final response = await apiClient.putData(
        '${BeautyModuleConstants.beautyRescheduleBookingUri}/$bookingId/reschedule',
        {
          'booking_date': date,
          'booking_time': time,
          if (notes != null && notes.isNotEmpty) 'notes': notes,
          if (staffId != null) 'staff_id': staffId,
        },
      );
      
      return response.statusCode == 200;
    } catch (e) {
      print('Error rescheduling booking: $e');
      return false;
    }
  }

  @override
  Future<Map<String, dynamic>?> checkAvailability(Map<String, dynamic> data) async {
    try {
      final response = await apiClient.postData(
        BeautyModuleConstants.beautyBookingSlotsUri,
        data,
      );
      
      if (response.statusCode == 200) {
        return response.body;
      }
      return null;
    } catch (e) {
      print('Error checking availability: $e');
      return null;
    }
  }

  @override
  Future<BeautyBookingConversationModel?> getBookingConversation(int bookingId) async {
    try {
      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautyBookingConversationUri}/$bookingId/conversation',
      );
      if (response.statusCode == 200 && response.body['data'] != null) {
        return BeautyBookingConversationModel.fromJson(response.body['data']);
      }
      return null;
    } catch (e) {
      print('Error loading booking conversation: $e');
      return null;
    }
  }

  @override
  Future<bool> sendBookingMessage(int bookingId, String message, {XFile? file}) async {
    try {
      final List<MultipartBody> multipartBody = [];
      if (file != null) {
        multipartBody.add(MultipartBody('file', file));
      }
      final response = await apiClient.postMultipartData(
        '${BeautyModuleConstants.beautyBookingConversationUri}/$bookingId/conversation',
        {'message': message},
        multipartBody,
      );
      return response.statusCode == 200;
    } catch (e) {
      print('Error sending booking message: $e');
      return false;
    }
  }

  @override
  Future<List<Map<String, dynamic>>> getWaitlist({int? offset, int? limit}) async {
    try {
      String url = '${BeautyModuleConstants.beautyWaitlistUri}?offset=${offset ?? 0}&limit=${limit ?? 10}';
      final response = await apiClient.getData(url);
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return List<Map<String, dynamic>>.from(response.body['data']);
      }
      return [];
    } catch (e) {
      print('Error getting waitlist: $e');
      return [];
    }
  }

  @override
  Future<bool> joinWaitlist(Map<String, dynamic> data) async {
    try {
      final response = await apiClient.postData(
        BeautyModuleConstants.beautyJoinWaitlistUri,
        data,
      );
      return response.statusCode == 200;
    } catch (e) {
      print('Error joining waitlist: $e');
      return false;
    }
  }

  @override
  Future<bool> leaveWaitlist(int waitlistId) async {
    try {
      final response = await apiClient.deleteData(
        '${BeautyModuleConstants.beautyLeaveWaitlistUri}/$waitlistId',
      );
      return response.statusCode == 200;
    } catch (e) {
      print('Error leaving waitlist: $e');
      return false;
    }
  }


  @override
  Future add(value) {
    throw UnimplementedError();
  }

  @override
  Future delete(int? id) {
    throw UnimplementedError();
  }

  @override
  Future get(String? id) async {
    if (id == null) return null;
    return await getBookingDetails(int.parse(id));
  }

  @override
  Future getList({int? offset}) async {
    return await getBookings(offset: offset);
  }

  @override
  Future update(Map<String, dynamic> body, int? id) {
    throw UnimplementedError();
  }
}
