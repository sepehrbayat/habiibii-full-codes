import 'package:flutter/foundation.dart';
import 'package:get/get.dart';
import '../domain/models/beauty_dashboard_summary_model.dart';
import '../domain/services/beauty_dashboard_service_interface.dart';

class BeautyDashboardController extends GetxController implements GetxService {
  final BeautyDashboardServiceInterface dashboardService;

  BeautyDashboardController({required this.dashboardService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  BeautyDashboardSummaryModel? _summary;
  BeautyDashboardSummaryModel? get summary => _summary;

  @override
  void onInit() {
    super.onInit();
    loadSummary();
  }

  Future<void> loadSummary() async {
    _isLoading = true;
    update();

    try {
      _summary = await dashboardService.getDashboardSummary();
    } catch (e) {
      if(kDebugMode) print('Error loading beauty dashboard summary: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }
}