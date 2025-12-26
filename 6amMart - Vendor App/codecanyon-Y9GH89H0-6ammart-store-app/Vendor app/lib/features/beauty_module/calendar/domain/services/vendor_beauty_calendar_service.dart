import '../models/vendor_beauty_calendar_block_model.dart';
import '../repositories/vendor_beauty_calendar_repository_interface.dart';
import 'vendor_beauty_calendar_service_interface.dart';

class VendorBeautyCalendarService implements VendorBeautyCalendarServiceInterface {
  final VendorBeautyCalendarRepositoryInterface calendarRepository;

  VendorBeautyCalendarService({required this.calendarRepository});

  @override
  Future<List<VendorBeautyCalendarBlockModel>?> getAvailability({
    required String date,
    int? staffId,
  }) async {
    return await calendarRepository.getAvailability(date: date, staffId: staffId);
  }

  @override
  Future<bool> createBlock(VendorBeautyCalendarBlockModel block) async {
    return await calendarRepository.createBlock(block);
  }

  @override
  Future<bool> deleteBlock(int blockId) async {
    return await calendarRepository.deleteBlock(blockId);
  }
}
