import 'package:sixam_mart_store/interface/repository_interface.dart';
import '../models/vendor_beauty_booking_model.dart';

abstract class VendorBeautyBookingRepositoryInterface implements RepositoryInterface {
  Future<List<VendorBeautyBookingModel>?> getBookings({
    required String status,
    required int offset,
    int limit = 10,
  });

  Future<VendorBeautyBookingModel?> getBookingDetails(int bookingId);

  Future<bool> confirmBooking(int bookingId);
  Future<bool> completeBooking(int bookingId);
  Future<bool> markBookingPaid(int bookingId);
  Future<bool> cancelBooking(int bookingId, String reason);
}
