import 'package:get/get.dart';
import '../domain/models/vendor_beauty_staff_model.dart';
import '../domain/services/vendor_beauty_staff_service_interface.dart';

class VendorBeautyStaffController extends GetxController implements GetxService {
  final VendorBeautyStaffServiceInterface staffService;

  VendorBeautyStaffController({required this.staffService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<VendorBeautyStaffModel>? _staffList;
  List<VendorBeautyStaffModel>? get staffList => _staffList;

  VendorBeautyStaffModel? _selectedStaff;
  VendorBeautyStaffModel? get selectedStaff => _selectedStaff;

  @override
  void onInit() {
    super.onInit();
    getStaffList();
  }

  Future<void> getStaffList() async {
    _isLoading = true;
    update();

    try {
      _staffList = await staffService.getStaffList();
    } catch (e) {
      print('Error loading staff: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<void> getStaffDetails(int staffId) async {
    _isLoading = true;
    update();

    try {
      _selectedStaff = await staffService.getStaffDetails(staffId);
    } catch (e) {
      print('Error loading staff details: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<bool> createStaff(VendorBeautyStaffModel staff) async {
    bool success = await staffService.createStaff(staff);
    if (success) {
      await getStaffList();
    }
    return success;
  }

  Future<bool> updateStaff(int staffId, VendorBeautyStaffModel staff) async {
    bool success = await staffService.updateStaff(staffId, staff);
    if (success) {
      await getStaffList();
    }
    return success;
  }

  Future<bool> deleteStaff(int staffId) async {
    bool success = await staffService.deleteStaff(staffId);
    if (success) {
      await getStaffList();
    }
    return success;
  }

  Future<bool> toggleStaffStatus(int staffId) async {
    bool success = await staffService.updateStaffStatus(staffId);
    if (success) {
      await getStaffList();
    }
    return success;
  }
}
