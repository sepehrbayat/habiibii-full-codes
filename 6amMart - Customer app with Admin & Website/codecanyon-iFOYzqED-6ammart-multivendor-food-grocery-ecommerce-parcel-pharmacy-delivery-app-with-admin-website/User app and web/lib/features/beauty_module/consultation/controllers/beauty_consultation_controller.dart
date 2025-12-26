import 'package:get/get.dart';
import '../domain/models/beauty_consultation_model.dart';
import '../domain/services/beauty_consultation_service_interface.dart';

class BeautyConsultationController extends GetxController implements GetxService {
  final BeautyConsultationServiceInterface consultationService;

  BeautyConsultationController({required this.consultationService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<BeautyConsultationModel>? _consultations;
  List<BeautyConsultationModel>? get consultations => _consultations;

  @override
  void onInit() {
    super.onInit();
    getConsultations();
  }

  Future<void> getConsultations() async {
    _isLoading = true;
    update();

    try {
      _consultations = await consultationService.getMyConsultations();
    } catch (e) {
      print('Error loading consultations: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<bool> bookConsultation({
    required int consultantId,
    required DateTime dateTime,
    required String type,
    String? notes,
  }) async {
    _isLoading = true;
    update();

    try {
      final success = await consultationService.bookConsultation(
        consultantId: consultantId,
        dateTime: dateTime,
        type: type,
        notes: notes,
      );
      if (success) {
        await getConsultations();
      }
      return success;
    } catch (e) {
      print('Error booking consultation: $e');
      return false;
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<List<Map<String, dynamic>>> getAvailableSlots({
    required int consultantId,
    required DateTime date,
  }) async {
    return await consultationService.getAvailableSlots(
      consultantId: consultantId,
      date: date,
    );
  }
}
