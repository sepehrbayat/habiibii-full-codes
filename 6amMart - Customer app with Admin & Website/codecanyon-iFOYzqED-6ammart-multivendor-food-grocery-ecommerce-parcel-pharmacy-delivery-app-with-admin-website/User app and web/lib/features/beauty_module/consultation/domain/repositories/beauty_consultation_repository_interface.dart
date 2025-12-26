import '../models/beauty_consultation_model.dart';
import '../models/beauty_consultant_model.dart';

abstract class BeautyConsultationRepositoryInterface {
  Future<List<BeautyConsultantModel>> getConsultants({
    int? salonId,
    String? specialization,
    int? offset,
    int? limit,
  });
  Future<BeautyConsultantModel?> getConsultantDetails(int consultantId);
  Future<List<Map<String, dynamic>>> getAvailableSlots({
    required int consultantId,
    required DateTime date,
  });
  Future<Map<String, dynamic>?> bookConsultation({
    required int consultantId,
    required DateTime dateTime,
    required String type,
    String? notes,
    List<String>? concerns,
  });
  Future<List<BeautyConsultationModel>> getMyConsultations({
    String? status,
    int? offset,
    int? limit,
  });
  Future<BeautyConsultationModel?> getConsultationDetails(int consultationId);
  Future<bool> cancelConsultation(int consultationId, String reason);
  Future<bool> rescheduleConsultation({
    required int consultationId,
    required DateTime newDateTime,
  });
  Future<Map<String, dynamic>?> startVideoConsultation(int consultationId);
  Future<bool> endConsultation(int consultationId);
  Future<bool> submitConsultationFeedback({
    required int consultationId,
    required int rating,
    String? feedback,
  });
  Future<List<Map<String, dynamic>>> getConsultationHistory(int consultationId);
  Future<Map<String, dynamic>?> getConsultationReport(int consultationId);
}
