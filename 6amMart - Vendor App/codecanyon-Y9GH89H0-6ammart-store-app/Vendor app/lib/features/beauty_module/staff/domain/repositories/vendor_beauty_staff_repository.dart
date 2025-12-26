import 'package:get/get.dart';
import 'package:sixam_mart_store/api/api_client.dart';
import 'package:sixam_mart_store/util/beauty_module_constants.dart';
import '../models/vendor_beauty_staff_model.dart';
import 'vendor_beauty_staff_repository_interface.dart';

class VendorBeautyStaffRepository implements VendorBeautyStaffRepositoryInterface {
  final ApiClient apiClient;

  VendorBeautyStaffRepository({required this.apiClient});

  @override
  Future<List<VendorBeautyStaffModel>?> getStaffList() async {
    Response response = await apiClient.getData(BeautyModuleConstants.vendorStaffListUri);
    if (response.statusCode == 200) {
      List<VendorBeautyStaffModel> staffList = [];
      response.body['staff']?.forEach((staff) {
        staffList.add(VendorBeautyStaffModel.fromJson(staff));
      });
      return staffList;
    }
    return null;
  }

  @override
  Future<VendorBeautyStaffModel?> getStaffDetails(int staffId) async {
    Response response = await apiClient.getData(
      '${BeautyModuleConstants.vendorStaffDetailsUri}/$staffId',
    );
    if (response.statusCode == 200) {
      return VendorBeautyStaffModel.fromJson(response.body['staff']);
    }
    return null;
  }

  @override
  Future<bool> createStaff(VendorBeautyStaffModel staff) async {
    Response response = await apiClient.postData(
      BeautyModuleConstants.vendorStaffCreateUri,
      staff.toJson(),
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> updateStaff(int staffId, VendorBeautyStaffModel staff) async {
    Response response = await apiClient.postData(
      '${BeautyModuleConstants.vendorStaffUpdateUri}/$staffId',
      staff.toJson(),
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> deleteStaff(int staffId) async {
    Response response = await apiClient.deleteData(
      '${BeautyModuleConstants.vendorStaffDeleteUri}/$staffId',
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> updateStaffStatus(int staffId) async {
    Response response = await apiClient.getData(
      '${BeautyModuleConstants.vendorStaffStatusUri}/$staffId',
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
