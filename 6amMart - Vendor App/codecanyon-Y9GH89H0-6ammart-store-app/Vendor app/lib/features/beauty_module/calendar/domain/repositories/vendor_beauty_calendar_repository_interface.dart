import 'package:sixam_mart_store/interface/repository_interface.dart';
import '../models/vendor_beauty_calendar_block_model.dart';

abstract class VendorBeautyCalendarRepositoryInterface implements RepositoryInterface {
  Future<List<VendorBeautyCalendarBlockModel>?> getAvailability({
    required String date,
    int? staffId,
  });

  Future<bool> createBlock(VendorBeautyCalendarBlockModel block);
  Future<bool> deleteBlock(int blockId);
}
