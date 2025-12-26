
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_salon_model.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_service_category_model.dart';
import 'package:sixam_mart/interfaces/repository_interface.dart';

abstract class BeautyHomeRepositoryInterface implements RepositoryInterface<BeautySalonModel> {
  Future<List<BeautySalonModel>> getPopularSalons({int? offset});
  Future<List<BeautySalonModel>> getTopRatedSalons({int? offset});
  Future<List<BeautySalonModel>> getMonthlyTopRatedSalons({int? offset});
  Future<List<BeautySalonModel>> getTrendingClinics({int? offset});
  Future<List<BeautySalonModel>> searchSalons({
    String? query,
    int? categoryId,
    double? minRating,
    double? maxDistance,
    int? offset,
  });
  Future<List<BeautyServiceCategoryModel>> getCategories();
}
