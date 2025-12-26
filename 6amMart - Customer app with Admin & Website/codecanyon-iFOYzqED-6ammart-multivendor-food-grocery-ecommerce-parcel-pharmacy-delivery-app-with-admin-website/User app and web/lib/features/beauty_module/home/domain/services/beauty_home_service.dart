
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_salon_model.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_service_category_model.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/repositories/beauty_home_repository_interface.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/services/beauty_home_service_interface.dart';

class BeautyHomeService implements BeautyHomeServiceInterface {
  final BeautyHomeRepositoryInterface beautyHomeRepositoryInterface;

  BeautyHomeService({required this.beautyHomeRepositoryInterface});

  @override
  Future<List<BeautySalonModel>> getPopularSalons({int? offset}) async {
    return await beautyHomeRepositoryInterface.getPopularSalons(offset: offset);
  }

  @override
  Future<List<BeautySalonModel>> getTopRatedSalons({int? offset}) async {
    return await beautyHomeRepositoryInterface.getTopRatedSalons(offset: offset);
  }

  @override
  Future<List<BeautySalonModel>> getMonthlyTopRatedSalons({int? offset}) async {
    return await beautyHomeRepositoryInterface.getMonthlyTopRatedSalons(offset: offset);
  }

  @override
  Future<List<BeautySalonModel>> getTrendingClinics({int? offset}) async {
    return await beautyHomeRepositoryInterface.getTrendingClinics(offset: offset);
  }

  @override
  Future<List<BeautySalonModel>> searchSalons({
    String? query,
    int? categoryId,
    double? minRating,
    double? maxDistance,
    int? offset,
  }) async {
    return await beautyHomeRepositoryInterface.searchSalons(
      query: query,
      categoryId: categoryId,
      minRating: minRating,
      maxDistance: maxDistance,
      offset: offset,
    );
  }

  @override
  Future<List<BeautyServiceCategoryModel>> getCategories() async {
    return await beautyHomeRepositoryInterface.getCategories();
  }

  @override
  Future<BeautySalonModel?> getSalonDetails(int salonId) async {
    return await beautyHomeRepositoryInterface.get(salonId.toString());
  }
}
