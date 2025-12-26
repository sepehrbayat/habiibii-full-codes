
import 'package:get/get.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_salon_model.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/models/beauty_service_category_model.dart';
import 'package:sixam_mart/features/beauty_module/home/domain/services/beauty_home_service_interface.dart';

class BeautyHomeController extends GetxController implements GetxService {
  final BeautyHomeServiceInterface beautyHomeServiceInterface;

  BeautyHomeController({required this.beautyHomeServiceInterface});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<BeautySalonModel>? _popularSalons;
  List<BeautySalonModel>? get popularSalons => _popularSalons;

  List<BeautySalonModel>? _topRatedSalons;
  List<BeautySalonModel>? get topRatedSalons => _topRatedSalons;

  List<BeautySalonModel>? _trendingClinics;
  List<BeautySalonModel>? get trendingClinics => _trendingClinics;

  List<BeautySalonModel>? _searchResults;
  List<BeautySalonModel>? get searchResults => _searchResults;

  List<BeautyServiceCategoryModel>? _categories;
  List<BeautyServiceCategoryModel>? get categories => _categories;

  int? _selectedCategoryId;
  int? get selectedCategoryId => _selectedCategoryId;

  double? _minRating;
  double? get minRating => _minRating;

  double? _maxDistance;
  double? get maxDistance => _maxDistance;

  Future<void> getPopularSalons({int offset = 0, bool reload = false}) async {
    if (reload) {
      _popularSalons = null;
    }
    _isLoading = true;
    update();

    try {
      final salons = await beautyHomeServiceInterface.getPopularSalons(offset: offset);
      if (offset == 0) {
        _popularSalons = salons;
      } else {
        _popularSalons?.addAll(salons);
      }
    } catch (e) {
      print('Error loading popular salons: \$e');
    }

    _isLoading = false;
    update();
  }

  Future<void> getTopRatedSalons({int offset = 0, bool reload = false}) async {
    if (reload) {
      _topRatedSalons = null;
    }
    _isLoading = true;
    update();

    try {
      final salons = await beautyHomeServiceInterface.getTopRatedSalons(offset: offset);
      if (offset == 0) {
        _topRatedSalons = salons;
      } else {
        _topRatedSalons?.addAll(salons);
      }
    } catch (e) {
      print('Error loading top rated salons: \$e');
    }

    _isLoading = false;
    update();
  }

  Future<void> getTrendingClinics({int offset = 0, bool reload = false}) async {
    if (reload) {
      _trendingClinics = null;
    }
    _isLoading = true;
    update();

    try {
      final salons = await beautyHomeServiceInterface.getTrendingClinics(offset: offset);
      if (offset == 0) {
        _trendingClinics = salons;
      } else {
        _trendingClinics?.addAll(salons);
      }
    } catch (e) {
      print('Error loading trending clinics: \$e');
    }

    _isLoading = false;
    update();
  }

  Future<void> searchSalons({
    String? query,
    int offset = 0,
    bool reload = false,
  }) async {
    if (reload) {
      _searchResults = null;
    }
    _isLoading = true;
    update();

    try {
      final salons = await beautyHomeServiceInterface.searchSalons(
        query: query,
        categoryId: _selectedCategoryId,
        minRating: _minRating,
        maxDistance: _maxDistance,
        offset: offset,
      );
      if (offset == 0) {
        _searchResults = salons;
      } else {
        _searchResults?.addAll(salons);
      }
    } catch (e) {
      print('Error searching salons: \$e');
    }

    _isLoading = false;
    update();
  }

  Future<void> getCategories() async {
    try {
      _categories = await beautyHomeServiceInterface.getCategories();
      update();
    } catch (e) {
      print('Error loading categories: \$e');
    }
  }

  void setSelectedCategory(int? categoryId) {
    _selectedCategoryId = categoryId;
    update();
    searchSalons(reload: true);
  }

  void setMinRating(double? rating) {
    _minRating = rating;
    update();
    searchSalons(reload: true);
  }

  void setMaxDistance(double? distance) {
    _maxDistance = distance;
    update();
    searchSalons(reload: true);
  }

  void clearFilters() {
    _selectedCategoryId = null;
    _minRating = null;
    _maxDistance = null;
    update();
    searchSalons(reload: true);
  }
}
