import 'package:get/get.dart';
import '../domain/models/vendor_beauty_booking_model.dart';
import '../domain/services/vendor_beauty_booking_service_interface.dart';

class VendorBeautyBookingController extends GetxController implements GetxService {
  final VendorBeautyBookingServiceInterface bookingService;

  VendorBeautyBookingController({required this.bookingService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<VendorBeautyBookingModel>? _bookingList;
  List<VendorBeautyBookingModel>? get bookingList => _bookingList;

  VendorBeautyBookingModel? _selectedBooking;
  VendorBeautyBookingModel? get selectedBooking => _selectedBooking;

  String _status = 'all';
  String get status => _status;

  int _offset = 1;
  bool _hasMore = true;
  bool get hasMore => _hasMore;

  @override
  void onInit() {
    super.onInit();
    getBookings();
  }

  Future<void> getBookings({bool reload = false}) async {
    if (reload) {
      _bookingList = null;
      _offset = 1;
      _hasMore = true;
      update();
    }

    if (!_hasMore) return;

    _isLoading = true;
    update();

    try {
      List<VendorBeautyBookingModel>? bookings = await bookingService.getBookings(
        status: _status,
        offset: _offset,
        limit: 10,
      );

      if (bookings != null && bookings.isNotEmpty) {
        _bookingList ??= [];
        _bookingList!.addAll(bookings);
        _offset += 1;
        _hasMore = bookings.length == 10;
      } else {
        _hasMore = false;
      }
    } catch (e) {
      print('Error loading bookings: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  void setStatus(String status) {
    if (_status != status) {
      _status = status;
      getBookings(reload: true);
    }
  }

  Future<void> getBookingDetails(int bookingId) async {
    _isLoading = true;
    update();

    try {
      _selectedBooking = await bookingService.getBookingDetails(bookingId);
    } catch (e) {
      print('Error loading booking details: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<bool> confirmBooking(int bookingId) async {
    bool success = await bookingService.confirmBooking(bookingId);
    if (success) {
      await getBookings(reload: true);
    }
    return success;
  }

  Future<bool> completeBooking(int bookingId) async {
    bool success = await bookingService.completeBooking(bookingId);
    if (success) {
      await getBookings(reload: true);
    }
    return success;
  }

  Future<bool> markBookingPaid(int bookingId) async {
    bool success = await bookingService.markBookingPaid(bookingId);
    if (success) {
      await getBookings(reload: true);
    }
    return success;
  }

  Future<bool> cancelBooking(int bookingId, String reason) async {
    bool success = await bookingService.cancelBooking(bookingId, reason);
    if (success) {
      await getBookings(reload: true);
    }
    return success;
  }
}
