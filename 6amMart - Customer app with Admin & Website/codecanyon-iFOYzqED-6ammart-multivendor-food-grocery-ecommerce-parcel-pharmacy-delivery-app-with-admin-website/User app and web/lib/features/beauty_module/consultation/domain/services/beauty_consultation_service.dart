import '../models/beauty_consultation_model.dart';
import '../repositories/beauty_consultation_repository_interface.dart';
import 'beauty_consultation_service_interface.dart';

class BeautyConsultationService implements BeautyConsultationServiceInterface {
  final BeautyConsultationRepositoryInterface consultationRepository;

  BeautyConsultationService({required this.consultationRepository});

  @override
  Future<List<BeautyConsultationModel>> getMyConsultations({
    String? status,
    int? offset,
    int? limit,
  }) async {
    return await consultationRepository.getMyConsultations(
      status: status,
      offset: offset,
      limit: limit,
    );
  }

  @override
  Future<bool> bookConsultation({
    required int consultantId,
    required DateTime dateTime,
    required String type,
    String? notes,
  }) async {
    final response = await consultationRepository.bookConsultation(
      consultantId: consultantId,
      dateTime: dateTime,
      type: type,
      notes: notes,
    );
    return response != null;
  }

  @override
  Future<List<Map<String, dynamic>>> getAvailableSlots({
    required int consultantId,
    required DateTime date,
  }) async {
    return await consultationRepository.getAvailableSlots(
      consultantId: consultantId,
      date: date,
    );
  }
}
