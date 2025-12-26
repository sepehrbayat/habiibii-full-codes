import '../models/beauty_dashboard_summary_model.dart';

abstract class BeautyDashboardServiceInterface {
  Future<BeautyDashboardSummaryModel?> getDashboardSummary();
}
