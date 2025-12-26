import 'package:sixam_mart_store/interface/repository_interface.dart';
import '../models/vendor_beauty_staff_model.dart';

abstract class VendorBeautyStaffRepositoryInterface implements RepositoryInterface {
  Future<List<VendorBeautyStaffModel>?> getStaffList();
  Future<VendorBeautyStaffModel?> getStaffDetails(int staffId);
  Future<bool> createStaff(VendorBeautyStaffModel staff);
  Future<bool> updateStaff(int staffId, VendorBeautyStaffModel staff);
  Future<bool> deleteStaff(int staffId);
  Future<bool> updateStaffStatus(int staffId);
}
