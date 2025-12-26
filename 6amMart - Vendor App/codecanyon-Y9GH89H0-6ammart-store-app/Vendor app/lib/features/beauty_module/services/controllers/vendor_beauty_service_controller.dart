import 'package:get/get.dart';
import '../domain/models/vendor_beauty_service_model.dart';
import '../domain/services/vendor_beauty_service_service_interface.dart';

class VendorBeautyServiceController extends GetxController implements GetxService {
  final VendorBeautyServiceServiceInterface serviceService;

  VendorBeautyServiceController({required this.serviceService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<VendorBeautyServiceModel>? _serviceList;
  List<VendorBeautyServiceModel>? get serviceList => _serviceList;

  VendorBeautyServiceModel? _selectedService;
  VendorBeautyServiceModel? get selectedService => _selectedService;

  @override
  void onInit() {
    super.onInit();
    getServiceList();
  }

  Future<void> getServiceList() async {
    _isLoading = true;
    update();

    try {
      _serviceList = await serviceService.getServiceList();
    } catch (e) {
      print('Error loading services: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<void> getServiceDetails(int serviceId) async {
    _isLoading = true;
    update();

    try {
      _selectedService = await serviceService.getServiceDetails(serviceId);
    } catch (e) {
      print('Error loading service details: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<bool> createService(VendorBeautyServiceModel service) async {
    bool success = await serviceService.createService(service);
    if (success) {
      await getServiceList();
    }
    return success;
  }

  Future<bool> updateService(int serviceId, VendorBeautyServiceModel service) async {
    bool success = await serviceService.updateService(serviceId, service);
    if (success) {
      await getServiceList();
    }
    return success;
  }

  Future<bool> deleteService(int serviceId) async {
    bool success = await serviceService.deleteService(serviceId);
    if (success) {
      await getServiceList();
    }
    return success;
  }

  Future<bool> toggleServiceStatus(int serviceId) async {
    bool success = await serviceService.updateServiceStatus(serviceId);
    if (success) {
      await getServiceList();
    }
    return success;
  }
}
