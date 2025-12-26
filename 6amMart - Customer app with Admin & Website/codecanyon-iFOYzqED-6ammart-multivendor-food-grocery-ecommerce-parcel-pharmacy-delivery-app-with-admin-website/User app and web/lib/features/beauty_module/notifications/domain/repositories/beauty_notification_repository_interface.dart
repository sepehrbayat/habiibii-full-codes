import '../models/beauty_notification_model.dart';

abstract class BeautyNotificationRepositoryInterface {
  Future<List<BeautyNotificationModel>> getNotifications({int? offset, int? limit});
  Future<bool> markNotificationsRead(List<int> ids);
}
