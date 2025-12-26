import '../models/vendor_beauty_service_model.dart';

abstract class VendorBeautyServiceServiceInterface {
  Future<List<VendorBeautyServiceModel>?> getServiceList();
  Future<VendorBeautyServiceModel?> getServiceDetails(int serviceId);
  Future<bool> createService(VendorBeautyServiceModel service);
  Future<bool> updateService(int serviceId, VendorBeautyServiceModel service);
  Future<bool> deleteService(int serviceId);
  Future<bool> updateServiceStatus(int serviceId);
}
