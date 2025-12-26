import '../models/vendor_beauty_staff_model.dart';
import '../repositories/vendor_beauty_staff_repository_interface.dart';
import 'vendor_beauty_staff_service_interface.dart';

class VendorBeautyStaffService implements VendorBeautyStaffServiceInterface {
  final VendorBeautyStaffRepositoryInterface staffRepository;

  VendorBeautyStaffService({required this.staffRepository});

  @override
  Future<List<VendorBeautyStaffModel>?> getStaffList() async {
    return await staffRepository.getStaffList();
  }

  @override
  Future<VendorBeautyStaffModel?> getStaffDetails(int staffId) async {
    return await staffRepository.getStaffDetails(staffId);
  }

  @override
  Future<bool> createStaff(VendorBeautyStaffModel staff) async {
    return await staffRepository.createStaff(staff);
  }

  @override
  Future<bool> updateStaff(int staffId, VendorBeautyStaffModel staff) async {
    return await staffRepository.updateStaff(staffId, staff);
  }

  @override
  Future<bool> deleteStaff(int staffId) async {
    return await staffRepository.deleteStaff(staffId);
  }

  @override
  Future<bool> updateStaffStatus(int staffId) async {
    return await staffRepository.updateStaffStatus(staffId);
  }
}
