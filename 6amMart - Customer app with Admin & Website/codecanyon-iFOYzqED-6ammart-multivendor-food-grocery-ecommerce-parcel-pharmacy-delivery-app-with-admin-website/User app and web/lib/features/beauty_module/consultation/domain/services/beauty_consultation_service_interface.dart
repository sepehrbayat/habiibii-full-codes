import '../models/beauty_consultation_model.dart';

abstract class BeautyConsultationServiceInterface {
  Future<List<BeautyConsultationModel>> getMyConsultations({
    String? status,
    int? offset,
    int? limit,
  });

  Future<bool> bookConsultation({
    required int consultantId,
    required DateTime dateTime,
    required String type,
    String? notes,
  });

  Future<List<Map<String, dynamic>>> getAvailableSlots({
    required int consultantId,
    required DateTime date,
  });
}
