import 'package:get/get.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';
import '../models/beauty_consultation_model.dart';
import '../models/beauty_consultant_model.dart';
import 'beauty_consultation_repository_interface.dart';

class BeautyConsultationRepository implements BeautyConsultationRepositoryInterface {
  final ApiClient apiClient;

  BeautyConsultationRepository({required this.apiClient});

  @override
  Future<List<BeautyConsultantModel>> getConsultants({
    int? salonId,
    String? specialization,
    int? offset,
    int? limit,
  }) async {
    return [];
  }

  @override
  Future<BeautyConsultantModel?> getConsultantDetails(int consultantId) async {
    return null;
  }

  @override
  Future<List<Map<String, dynamic>>> getAvailableSlots({
    required int consultantId,
    required DateTime date,
  }) async {
    Response response = await apiClient.postData(
      BeautyModuleConstants.beautyConsultationAvailabilityUri,
      {
        'consultant_id': consultantId,
        'date': date.toIso8601String().split('T')[0],
      },
    );

    if (response.statusCode == 200) {
      return List<Map<String, dynamic>>.from(response.body['slots'] ?? []);
    }
    return [];
  }

  @override
  Future<Map<String, dynamic>?> bookConsultation({
    required int consultantId,
    required DateTime dateTime,
    required String type,
    String? notes,
    List<String>? concerns,
  }) async {
    Response response = await apiClient.postData(
      BeautyModuleConstants.beautyBookConsultationUri,
      {
        'consultant_id': consultantId,
        'date_time': dateTime.toIso8601String(),
        'type': type,
        'notes': notes,
        'concerns': concerns,
      },
    );

    if (response.statusCode == 200) {
      return response.body;
    }
    return null;
  }

  @override
  Future<List<BeautyConsultationModel>> getMyConsultations({
    String? status,
    int? offset,
    int? limit,
  }) async {
    Map<String, dynamic> params = {};
    if (status != null) params['status'] = status;
    if (offset != null) params['offset'] = offset.toString();
    if (limit != null) params['limit'] = limit.toString();

    Response response = await apiClient.getData(
      BeautyModuleConstants.beautyConsultationsUri,
      query: params,
    );

    if (response.statusCode == 200) {
      List<BeautyConsultationModel> consultations = [];
      response.body['consultations']?.forEach((consultation) {
        consultations.add(BeautyConsultationModel.fromJson(consultation));
      });
      return consultations;
    }
    return [];
  }

  @override
  Future<BeautyConsultationModel?> getConsultationDetails(int consultationId) async {
    return null;
  }

  @override
  Future<bool> cancelConsultation(int consultationId, String reason) async {
    return false;
  }

  @override
  Future<bool> rescheduleConsultation({
    required int consultationId,
    required DateTime newDateTime,
  }) async {
    return false;
  }

  @override
  Future<Map<String, dynamic>?> startVideoConsultation(int consultationId) async {
    return null;
  }

  @override
  Future<bool> endConsultation(int consultationId) async {
    return false;
  }

  @override
  Future<bool> submitConsultationFeedback({
    required int consultationId,
    required int rating,
    String? feedback,
  }) async {
    return false;
  }

  @override
  Future<List<Map<String, dynamic>>> getConsultationHistory(int consultationId) async {
    return [];
  }

  @override
  Future<Map<String, dynamic>?> getConsultationReport(int consultationId) async {
    return null;
  }
}
