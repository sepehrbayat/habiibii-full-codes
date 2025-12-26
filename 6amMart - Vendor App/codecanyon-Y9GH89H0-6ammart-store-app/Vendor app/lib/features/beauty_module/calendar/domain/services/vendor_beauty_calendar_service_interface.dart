import '../models/vendor_beauty_calendar_block_model.dart';

abstract class VendorBeautyCalendarServiceInterface {
  Future<List<VendorBeautyCalendarBlockModel>?> getAvailability({
    required String date,
    int? staffId,
  });

  Future<bool> createBlock(VendorBeautyCalendarBlockModel block);
  Future<bool> deleteBlock(int blockId);
}
