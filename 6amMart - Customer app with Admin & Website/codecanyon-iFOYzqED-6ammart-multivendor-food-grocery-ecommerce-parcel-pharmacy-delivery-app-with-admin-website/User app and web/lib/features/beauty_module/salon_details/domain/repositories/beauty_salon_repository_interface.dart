import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_salon_model.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/models/beauty_service_model.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/models/beauty_staff_model.dart';
import 'package:sixam_mart/interfaces/repository_interface.dart';

abstract class BeautySalonRepositoryInterface implements RepositoryInterface<BeautySalonModel> {
  Future<BeautySalonModel?> getSalonDetails(int salonId);
  Future<List<BeautyServiceModel>> getSalonServices(int salonId, {int? offset});
  Future<List<BeautyStaffModel>> getSalonStaff(int salonId, {int? offset});
  Future<BeautyServiceModel?> getServiceDetails(int serviceId);
  Future<BeautyStaffModel?> getStaffDetails(int staffId);
}
