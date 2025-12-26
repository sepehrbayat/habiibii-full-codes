import 'package:get/get.dart';
import 'package:sixam_mart_store/api/api_client.dart';
import 'package:sixam_mart_store/util/beauty_module_constants.dart';
import '../models/vendor_beauty_service_model.dart';
import 'vendor_beauty_service_repository_interface.dart';

class VendorBeautyServiceRepository implements VendorBeautyServiceRepositoryInterface {
  final ApiClient apiClient;

  VendorBeautyServiceRepository({required this.apiClient});

  @override
  Future<List<VendorBeautyServiceModel>?> getServiceList() async {
    Response response = await apiClient.getData(BeautyModuleConstants.vendorServiceListUri);
    if (response.statusCode == 200) {
      List<VendorBeautyServiceModel> services = [];
      response.body['services']?.forEach((service) {
        services.add(VendorBeautyServiceModel.fromJson(service));
      });
      return services;
    }
    return null;
  }

  @override
  Future<VendorBeautyServiceModel?> getServiceDetails(int serviceId) async {
    Response response = await apiClient.getData(
      '${BeautyModuleConstants.vendorServiceDetailsUri}/$serviceId',
    );
    if (response.statusCode == 200) {
      return VendorBeautyServiceModel.fromJson(response.body['service']);
    }
    return null;
  }

  @override
  Future<bool> createService(VendorBeautyServiceModel service) async {
    Response response = await apiClient.postData(
      BeautyModuleConstants.vendorServiceCreateUri,
      service.toJson(),
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> updateService(int serviceId, VendorBeautyServiceModel service) async {
    Response response = await apiClient.postData(
      '${BeautyModuleConstants.vendorServiceUpdateUri}/$serviceId',
      service.toJson(),
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> deleteService(int serviceId) async {
    Response response = await apiClient.deleteData(
      '${BeautyModuleConstants.vendorServiceDeleteUri}/$serviceId',
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> updateServiceStatus(int serviceId) async {
    Response response = await apiClient.getData(
      '${BeautyModuleConstants.vendorServiceStatusUri}/$serviceId',
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
