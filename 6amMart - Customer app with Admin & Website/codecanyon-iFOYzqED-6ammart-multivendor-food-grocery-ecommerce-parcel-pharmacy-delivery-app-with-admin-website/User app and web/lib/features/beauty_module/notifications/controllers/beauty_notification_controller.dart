import 'package:get/get.dart';
import '../domain/models/beauty_notification_model.dart';
import '../domain/services/beauty_notification_service_interface.dart';

class BeautyNotificationController extends GetxController implements GetxService {
  final BeautyNotificationServiceInterface notificationService;

  BeautyNotificationController({required this.notificationService});

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  List<BeautyNotificationModel>? _notifications;
  List<BeautyNotificationModel>? get notifications => _notifications;

  @override
  void onInit() {
    super.onInit();
    getNotifications();
  }

  Future<void> getNotifications() async {
    _isLoading = true;
    update();

    try {
      _notifications = await notificationService.getNotifications();
    } catch (e) {
      print('Error loading notifications: $e');
    } finally {
      _isLoading = false;
      update();
    }
  }

  Future<bool> markAllRead() async {
    if (_notifications == null || _notifications!.isEmpty) return true;

    final ids = _notifications!
        .where((notification) => notification.id != null)
        .map((notification) => notification.id!)
        .toList();

    if (ids.isEmpty) return true;

    final success = await notificationService.markNotificationsRead(ids);
    if (success) {
      await getNotifications();
    }
    return success;
  }
}
