import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_salon_model.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/models/beauty_service_model.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/models/beauty_staff_model.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/repositories/beauty_salon_repository_interface.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/services/beauty_salon_service_interface.dart';

class BeautySalonService implements BeautySalonServiceInterface {
  final BeautySalonRepositoryInterface beautySalonRepositoryInterface;

  BeautySalonService({required this.beautySalonRepositoryInterface});

  @override
  Future<BeautySalonModel?> getSalonDetails(int salonId) async {
    return await beautySalonRepositoryInterface.getSalonDetails(salonId);
  }

  @override
  Future<List<BeautyServiceModel>> getSalonServices(int salonId, {int? offset}) async {
    return await beautySalonRepositoryInterface.getSalonServices(salonId, offset: offset);
  }

  @override
  Future<List<BeautyStaffModel>> getSalonStaff(int salonId, {int? offset}) async {
    return await beautySalonRepositoryInterface.getSalonStaff(salonId, offset: offset);
  }

  @override
  Future<BeautyServiceModel?> getServiceDetails(int serviceId) async {
    return await beautySalonRepositoryInterface.getServiceDetails(serviceId);
  }

  @override
  Future<BeautyStaffModel?> getStaffDetails(int staffId) async {
    return await beautySalonRepositoryInterface.getStaffDetails(staffId);
  }
}
