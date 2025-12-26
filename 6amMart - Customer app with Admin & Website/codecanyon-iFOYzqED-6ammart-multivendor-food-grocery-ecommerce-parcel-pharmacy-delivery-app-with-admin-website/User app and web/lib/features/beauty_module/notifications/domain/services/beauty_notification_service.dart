import '../models/beauty_notification_model.dart';
import '../repositories/beauty_notification_repository_interface.dart';
import 'beauty_notification_service_interface.dart';

class BeautyNotificationService implements BeautyNotificationServiceInterface {
  final BeautyNotificationRepositoryInterface notificationRepository;

  BeautyNotificationService({required this.notificationRepository});

  @override
  Future<List<BeautyNotificationModel>> getNotifications({int? offset, int? limit}) async {
    return await notificationRepository.getNotifications(offset: offset, limit: limit);
  }

  @override
  Future<bool> markNotificationsRead(List<int> ids) async {
    return await notificationRepository.markNotificationsRead(ids);
  }
}
