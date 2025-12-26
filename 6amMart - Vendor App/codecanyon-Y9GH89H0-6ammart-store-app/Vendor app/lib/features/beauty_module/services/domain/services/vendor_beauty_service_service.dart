import '../models/vendor_beauty_service_model.dart';
import '../repositories/vendor_beauty_service_repository_interface.dart';
import 'vendor_beauty_service_service_interface.dart';

class VendorBeautyServiceService implements VendorBeautyServiceServiceInterface {
  final VendorBeautyServiceRepositoryInterface serviceRepository;

  VendorBeautyServiceService({required this.serviceRepository});

  @override
  Future<List<VendorBeautyServiceModel>?> getServiceList() async {
    return await serviceRepository.getServiceList();
  }

  @override
  Future<VendorBeautyServiceModel?> getServiceDetails(int serviceId) async {
    return await serviceRepository.getServiceDetails(serviceId);
  }

  @override
  Future<bool> createService(VendorBeautyServiceModel service) async {
    return await serviceRepository.createService(service);
  }

  @override
  Future<bool> updateService(int serviceId, VendorBeautyServiceModel service) async {
    return await serviceRepository.updateService(serviceId, service);
  }

  @override
  Future<bool> deleteService(int serviceId) async {
    return await serviceRepository.deleteService(serviceId);
  }

  @override
  Future<bool> updateServiceStatus(int serviceId) async {
    return await serviceRepository.updateServiceStatus(serviceId);
  }
}
