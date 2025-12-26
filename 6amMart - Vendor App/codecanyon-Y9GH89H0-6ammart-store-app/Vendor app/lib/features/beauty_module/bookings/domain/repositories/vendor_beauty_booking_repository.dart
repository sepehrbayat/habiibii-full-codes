import 'package:get/get.dart';
import 'package:sixam_mart_store/api/api_client.dart';
import 'package:sixam_mart_store/util/beauty_module_constants.dart';
import '../models/vendor_beauty_booking_model.dart';
import 'vendor_beauty_booking_repository_interface.dart';

class VendorBeautyBookingRepository implements VendorBeautyBookingRepositoryInterface {
  final ApiClient apiClient;

  VendorBeautyBookingRepository({required this.apiClient});

  @override
  Future<List<VendorBeautyBookingModel>?> getBookings({
    required String status,
    required int offset,
    int limit = 10,
  }) async {
    Response response = await apiClient.getData(
      '${BeautyModuleConstants.vendorBookingListAllUri}?offset=$offset&limit=$limit',
    );
    if (response.statusCode == 200) {
      List<VendorBeautyBookingModel> bookings = [];
      response.body['bookings']?.forEach((booking) {
        bookings.add(VendorBeautyBookingModel.fromJson(booking));
      });
      return bookings;
    }
    return null;
  }

  @override
  Future<VendorBeautyBookingModel?> getBookingDetails(int bookingId) async {
    Response response = await apiClient.getData(
      '${BeautyModuleConstants.vendorBookingDetailsUri}?booking_id=$bookingId',
    );
    if (response.statusCode == 200) {
      return VendorBeautyBookingModel.fromJson(response.body['booking']);
    }
    return null;
  }

  @override
  Future<bool> confirmBooking(int bookingId) async {
    Response response = await apiClient.putData(
      BeautyModuleConstants.vendorBookingConfirmUri,
      {'booking_id': bookingId},
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> completeBooking(int bookingId) async {
    Response response = await apiClient.putData(
      BeautyModuleConstants.vendorBookingCompleteUri,
      {'booking_id': bookingId},
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> markBookingPaid(int bookingId) async {
    Response response = await apiClient.putData(
      BeautyModuleConstants.vendorBookingMarkPaidUri,
      {'booking_id': bookingId},
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> cancelBooking(int bookingId, String reason) async {
    Response response = await apiClient.putData(
      BeautyModuleConstants.vendorBookingCancelUri,
      {'booking_id': bookingId, 'reason': reason},
    );
    return response.statusCode == 200;
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
  Future get(int? id) {
    throw UnimplementedError();
  }

  @override
  Future getList() {
    throw UnimplementedError();
  }

  @override
  Future update(Map<String, dynamic> body) {
    throw UnimplementedError();
  }
}
