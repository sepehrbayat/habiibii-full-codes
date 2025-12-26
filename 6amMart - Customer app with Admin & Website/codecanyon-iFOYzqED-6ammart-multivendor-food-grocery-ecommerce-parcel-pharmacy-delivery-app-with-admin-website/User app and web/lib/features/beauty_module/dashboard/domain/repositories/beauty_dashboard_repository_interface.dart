import '../models/beauty_dashboard_summary_model.dart';

abstract class BeautyDashboardRepositoryInterface {
  Future<BeautyDashboardSummaryModel?> getDashboardSummary();
}
