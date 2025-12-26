import 'package:get/get.dart';
import '../domain/models/vendor_beauty_calendar_block_model.dart';
import '../domain/services/vendor_beauty_calendar_service_interface.dart';

class VendorBeautyCalendarController extends GetxController implements GetxService {
  final VendorBeautyCalendarServiceInterface calendarService;

  VendorBeautyCalendarController({required this.calendarService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<VendorBeautyCalendarBlockModel>? _blocks;
  List<VendorBeautyCalendarBlockModel>? get blocks => _blocks;

  Future<void> getAvailability({required String date, int? staffId}) async {
    _isLoading = true;
    update();

    try {
      _blocks = await calendarService.getAvailability(date: date, staffId: staffId);
    } catch (e) {
      print('Error loading calendar availability: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<bool> createBlock(VendorBeautyCalendarBlockModel block) async {
    bool success = await calendarService.createBlock(block);
    if (success && block.date != null) {
      await getAvailability(date: block.date!);
    }
    return success;
  }

  Future<bool> deleteBlock(int blockId, {String? date}) async {
    bool success = await calendarService.deleteBlock(blockId);
    if (success && date != null) {
      await getAvailability(date: date);
    }
    return success;
  }
}
