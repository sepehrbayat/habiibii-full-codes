import 'package:shared_preferences/shared_preferences.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_salon_model.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/models/beauty_service_model.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/models/beauty_staff_model.dart';
import 'package:sixam_mart/features/beauty_module/salon_details/domain/repositories/beauty_salon_repository_interface.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';

class BeautySalonRepository implements BeautySalonRepositoryInterface {
  final ApiClient apiClient;
  final SharedPreferences sharedPreferences;

  BeautySalonRepository({
    required this.apiClient,
    required this.sharedPreferences,
  });

  @override
  Future<BeautySalonModel?> getSalonDetails(int salonId) async {
    try {
      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautySalonDetailsUri}/$salonId',
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return BeautySalonModel.fromJson(response.body['data']);
      }
      return null;
    } catch (e) {
      print('Error getting salon details: $e');
      return null;
    }
  }

  @override
  Future<List<BeautyServiceModel>> getSalonServices(int salonId, {int? offset}) async {
    try {
      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautySalonDetailsUri}/$salonId/services?offset=${offset ?? 0}&limit=20',
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return (response.body['data'] as List)
            .map((json) => BeautyServiceModel.fromJson(json))
            .toList();
      }
      return [];
    } catch (e) {
      print('Error getting salon services: $e');
      return [];
    }
  }

  @override
  Future<List<BeautyStaffModel>> getSalonStaff(int salonId, {int? offset}) async {
    try {
      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautySalonDetailsUri}/$salonId/staff?offset=${offset ?? 0}&limit=20',
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return (response.body['data'] as List)
            .map((json) => BeautyStaffModel.fromJson(json))
            .toList();
      }
      return [];
    } catch (e) {
      print('Error getting salon staff: $e');
      return [];
    }
  }

  @override
  Future<BeautyServiceModel?> getServiceDetails(int serviceId) async {
    try {
      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautyServiceSuggestionsUri}/$serviceId/suggestions',
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return BeautyServiceModel.fromJson(response.body['data']);
      }
      return null;
    } catch (e) {
      print('Error getting service details: $e');
      return null;
    }
  }

  @override
  Future<BeautyStaffModel?> getStaffDetails(int staffId) async {
    try {
      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautySalonStaffUri}/$staffId',
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return BeautyStaffModel.fromJson(response.body['data']);
      }
      return null;
    } catch (e) {
      print('Error getting staff details: $e');
      return null;
    }
  }

  @override
  Future add(value) => throw UnimplementedError();

  @override
  Future delete(int? id) => throw UnimplementedError();

  @override
  Future get(String? id) async {
    if (id == null) return null;
    return await getSalonDetails(int.parse(id));
  }

  @override
  Future getList({int? offset}) => throw UnimplementedError();

  @override
  Future update(Map<String, dynamic> body, int? id) => throw UnimplementedError();
}
