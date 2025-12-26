import 'package:get/get.dart';
import 'package:sixam_mart_store/api/api_client.dart';
import 'package:sixam_mart_store/util/beauty_module_constants.dart';
import '../models/vendor_beauty_calendar_block_model.dart';
import 'vendor_beauty_calendar_repository_interface.dart';

class VendorBeautyCalendarRepository implements VendorBeautyCalendarRepositoryInterface {
  final ApiClient apiClient;

  VendorBeautyCalendarRepository({required this.apiClient});

  @override
  Future<List<VendorBeautyCalendarBlockModel>?> getAvailability({
    required String date,
    int? staffId,
  }) async {
    Map<String, dynamic> query = {'date': date};
    if (staffId != null) query['staff_id'] = staffId.toString();

    Response response = await apiClient.getData(
      BeautyModuleConstants.vendorCalendarAvailabilityUri,
      query: query,
    );
    if (response.statusCode == 200) {
      List<VendorBeautyCalendarBlockModel> blocks = [];
      response.body['blocks']?.forEach((block) {
        blocks.add(VendorBeautyCalendarBlockModel.fromJson(block));
      });
      return blocks;
    }
    return null;
  }

  @override
  Future<bool> createBlock(VendorBeautyCalendarBlockModel block) async {
    Response response = await apiClient.postData(
      BeautyModuleConstants.vendorCalendarBlockCreateUri,
      block.toJson(),
    );
    return response.statusCode == 200;
  }

  @override
  Future<bool> deleteBlock(int blockId) async {
    Response response = await apiClient.deleteData(
      '${BeautyModuleConstants.vendorCalendarBlockDeleteUri}/$blockId',
    );
    return response.statusCode == 200;
  }

  @override
  Future add(value) {
    throw UnimplementedError();
  }

  @override
  Future delete(int? id) {
    throw UnimplementedError();
  }

  @override
  Future get(int? id) {
    throw UnimplementedError();
  }

  @override
  Future getList() {
    throw UnimplementedError();
  }

  @override
  Future update(Map<String, dynamic> body) {
    throw UnimplementedError();
  }
}
