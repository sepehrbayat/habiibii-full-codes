import 'package:get/get.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/util/beauty_module_constants.dart';
import '../models/beauty_notification_model.dart';
import 'beauty_notification_repository_interface.dart';

class BeautyNotificationRepository implements BeautyNotificationRepositoryInterface {
  final ApiClient apiClient;

  BeautyNotificationRepository({required this.apiClient});

  @override
  Future<List<BeautyNotificationModel>> getNotifications({int? offset, int? limit}) async {
    Response response = await apiClient.getData(
      BeautyModuleConstants.beautyNotificationsUri,
      query: {
        if (offset != null) 'offset': offset.toString(),
        if (limit != null) 'limit': limit.toString(),
      },
    );
    if (response.statusCode == 200) {
      List<BeautyNotificationModel> notifications = [];
      response.body['notifications']?.forEach((notification) {
        notifications.add(BeautyNotificationModel.fromJson(notification));
      });
      return notifications;
    }
    return [];
  }

  @override
  Future<bool> markNotificationsRead(List<int> ids) async {
    Response response = await apiClient.postData(
      BeautyModuleConstants.beautyNotificationsMarkReadUri,
      {'ids': ids},
    );
    return response.statusCode == 200;
  }
}
