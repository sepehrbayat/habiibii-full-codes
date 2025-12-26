import '../models/vendor_beauty_booking_model.dart';
import '../repositories/vendor_beauty_booking_repository_interface.dart';
import 'vendor_beauty_booking_service_interface.dart';

class VendorBeautyBookingService implements VendorBeautyBookingServiceInterface {
  final VendorBeautyBookingRepositoryInterface bookingRepository;

  VendorBeautyBookingService({required this.bookingRepository});

  @override
  Future<List<VendorBeautyBookingModel>?> getBookings({
    required String status,
    required int offset,
    int limit = 10,
  }) async {
    return await bookingRepository.getBookings(
      status: status,
      offset: offset,
      limit: limit,
    );
  }

  @override
  Future<VendorBeautyBookingModel?> getBookingDetails(int bookingId) async {
    return await bookingRepository.getBookingDetails(bookingId);
  }

  @override
  Future<bool> confirmBooking(int bookingId) async {
    return await bookingRepository.confirmBooking(bookingId);
  }

  @override
  Future<bool> completeBooking(int bookingId) async {
    return await bookingRepository.completeBooking(bookingId);
  }

  @override
  Future<bool> markBookingPaid(int bookingId) async {
    return await bookingRepository.markBookingPaid(bookingId);
  }

  @override
  Future<bool> cancelBooking(int bookingId, String reason) async {
    return await bookingRepository.cancelBooking(bookingId, reason);
  }
}
