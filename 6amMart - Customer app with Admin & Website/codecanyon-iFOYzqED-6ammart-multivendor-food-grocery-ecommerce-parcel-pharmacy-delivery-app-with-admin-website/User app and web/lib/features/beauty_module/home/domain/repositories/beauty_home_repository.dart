
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_salon_model.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_service_category_model.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/repositories/beauty_home_repository_interface.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';

class BeautyHomeRepository implements BeautyHomeRepositoryInterface {
  final ApiClient apiClient;
  final SharedPreferences sharedPreferences;

  BeautyHomeRepository({
    required this.apiClient,
    required this.sharedPreferences,
  });

  @override
  Future<List<BeautySalonModel>> getPopularSalons({int? offset}) async {
    try {
      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautySalonPopularUri}?offset=${offset ?? 0}&limit=10',
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return (response.body['data'] as List)
            .map((json) => BeautySalonModel.fromJson(json))
            .toList();
      }
      return [];
    } catch (e) {
      return [];
    }
  }

  @override
  Future<List<BeautySalonModel>> getTopRatedSalons({int? offset}) async {
    try {
      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautySalonTopRatedUri}?offset=${offset ?? 0}&limit=10',
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return (response.body['data'] as List)
            .map((json) => BeautySalonModel.fromJson(json))
            .toList();
      }
      return [];
    } catch (e) {
      return [];
    }
  }

  @override
  Future<List<BeautySalonModel>> getMonthlyTopRatedSalons({int? offset}) async {
    try {
      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautySalonMonthlyTopRatedUri}?offset=${offset ?? 0}&limit=10',
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return (response.body['data'] as List)
            .map((json) => BeautySalonModel.fromJson(json))
            .toList();
      }
      return [];
    } catch (e) {
      return [];
    }
  }

  @override
  Future<List<BeautySalonModel>> getTrendingClinics({int? offset}) async {
    try {
      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautySalonTrendingUri}?offset=${offset ?? 0}&limit=10',
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return (response.body['data'] as List)
            .map((json) => BeautySalonModel.fromJson(json))
            .toList();
      }
      return [];
    } catch (e) {
      return [];
    }
  }

  @override
  Future<List<BeautySalonModel>> searchSalons({
    String? query,
    int? categoryId,
    double? minRating,
    double? maxDistance,
    int? offset,
  }) async {
    try {
      final queryParams = <String, dynamic>{
        'offset': offset ?? 0,
        'limit': 10,
        if (query != null && query.isNotEmpty) 'query': query,
        if (categoryId != null) 'category_id': categoryId,
        if (minRating != null) 'min_rating': minRating,
        if (maxDistance != null) 'max_distance': maxDistance,
      };

      final queryString = queryParams.entries
          .map((e) => '${e.key}=${e.value}')
          .join('&');

      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautySalonSearchUri}?$queryString',
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return (response.body['data'] as List)
            .map((json) => BeautySalonModel.fromJson(json))
            .toList();
      }
      return [];
    } catch (e) {
      return [];
    }
  }

  @override
  Future<List<BeautyServiceCategoryModel>> getCategories() async {
    try {
      final response = await apiClient.getData(
        BeautyModuleConstants.beautySalonCategoriesUri,
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return (response.body['data'] as List)
            .map((json) => BeautyServiceCategoryModel.fromJson(json))
            .toList();
      }
      return [];
    } catch (e) {
      return [];
    }
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
  Future get(String? id) async {
    try {
      final response = await apiClient.getData(
        '${BeautyModuleConstants.beautySalonDetailsUri}/$id',
      );
      
      if (response.statusCode == 200 && response.body['data'] != null) {
        return BeautySalonModel.fromJson(response.body['data']);
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  @override
  Future getList({int? offset}) async {
    return searchSalons(offset: offset);
  }

  @override
  Future update(Map<String, dynamic> body, int? id) {
    throw UnimplementedError();
  }
}
