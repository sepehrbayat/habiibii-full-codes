import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_salon_model.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/models/beauty_service_model.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/models/beauty_staff_model.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/services/beauty_salon_service_interface.dart';
import 'package:sixam_mart/features/beauty_module/booking/controllers/beauty_booking_controller.dart';

class BeautySalonController extends GetxController implements GetxService {
  final BeautySalonServiceInterface beautySalonServiceInterface;

  BeautySalonController({required this.beautySalonServiceInterface});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  BeautySalonModel? _salon;
  BeautySalonModel? get salon => _salon;

  List<BeautyServiceModel>? _services;
  List<BeautyServiceModel>? get services => _services;

  List<BeautyStaffModel>? _staff;
  List<BeautyStaffModel>? get staff => _staff;

  BeautyServiceModel? _selectedService;
  BeautyServiceModel? get selectedService => _selectedService;

  BeautyStaffModel? _selectedStaff;
  BeautyStaffModel? get selectedStaff => _selectedStaff;

  int _selectedTabIndex = 0;
  int get selectedTabIndex => _selectedTabIndex;

  /// Get salon details
  Future<void> getSalonDetails(int salonId, {bool reload = false}) async {
    if (reload || _salon == null || _salon!.id != salonId) {
      _salon = null;
      _services = null;
      _staff = null;
    }

    _isLoading = true;
    update();

    try {
      _salon = await beautySalonServiceInterface.getSalonDetails(salonId);
      
      if (_salon != null) {
        // Load services and staff in parallel
        await Future.wait([
          getSalonServices(salonId),
          getSalonStaff(salonId),
        ]);
      }
    } catch (e) {
      print('Error loading salon details: $e');
      Get.showSnackbar(GetSnackBar(
        message: 'Failed to load salon details',
        duration: Duration(seconds: 2),
        backgroundColor: Get.theme.colorScheme.error,
      ));
    }

    _isLoading = false;
    update();
  }

  /// Get salon services
  Future<void> getSalonServices(int salonId, {int offset = 0}) async {
    try {
      final services = await beautySalonServiceInterface.getSalonServices(
        salonId,
        offset: offset,
      );
      
      if (offset == 0) {
        _services = services;
      } else {
        _services?.addAll(services);
      }
      update();
    } catch (e) {
      print('Error loading services: $e');
    }
  }

  Future<void> getServices(int salonId, {int offset = 0}) async {
    return getSalonServices(salonId, offset: offset);
  }

  /// Get salon staff
  Future<void> getSalonStaff(int salonId, {int offset = 0}) async {
    try {
      final staff = await beautySalonServiceInterface.getSalonStaff(
        salonId,
        offset: offset,
      );
      
      if (offset == 0) {
        _staff = staff;
      } else {
        _staff?.addAll(staff);
      }
      update();
    } catch (e) {
      print('Error loading staff: $e');
    }
  }

  Future<void> getStaff(int salonId, {int offset = 0}) async {
    return getSalonStaff(salonId, offset: offset);
  }

  /// Get service details
  Future<void> getServiceDetails(int serviceId) async {
    _isLoading = true;
    update();

    try {
      _selectedService = await beautySalonServiceInterface.getServiceDetails(serviceId);
    } catch (e) {
      print('Error loading service details: $e');
    }

    _isLoading = false;
    update();
  }

  /// Get staff details
  Future<void> getStaffDetails(int staffId) async {
    _isLoading = true;
    update();

    try {
      _selectedStaff = await beautySalonServiceInterface.getStaffDetails(staffId);
    } catch (e) {
      print('Error loading staff details: $e');
    }

    _isLoading = false;
    update();
  }

  /// Set selected tab
  void setSelectedTab(int index) {
    _selectedTabIndex = index;
    update();
  }

  /// Select service
  void selectService(BeautyServiceModel service) {
    _selectedService = service;
    update();
  }

  /// Select staff
  void selectStaff(BeautyStaffModel staffMember) {
    _selectedStaff = staffMember;
    update();
  }

  /// Clear selections
  void clearSelections() {
    _selectedService = null;
    _selectedStaff = null;
    update();
  }

  /// Navigate to booking with pre-selected data
  void navigateToBooking({
    int? serviceId,
    int? staffId,
  }) {
    Get.find<BeautyBookingController>().navigateToCreateBooking(
      salonId: _salon?.id,
      serviceId: serviceId ?? _selectedService?.id,
    );
  }
}
