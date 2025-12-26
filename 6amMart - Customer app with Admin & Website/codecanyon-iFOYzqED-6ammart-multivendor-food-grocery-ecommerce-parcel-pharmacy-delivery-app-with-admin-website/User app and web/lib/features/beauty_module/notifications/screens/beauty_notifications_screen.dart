import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/beauty_notification_controller.dart';

class BeautyNotificationsScreen extends StatelessWidget {
  const BeautyNotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Beauty Notifications'),
        actions: [
          IconButton(
            icon: const Icon(Icons.done_all),
            onPressed: () => Get.find<BeautyNotificationController>().markAllRead(),
          ),
        ],
      ),
      body: GetBuilder<BeautyNotificationController>(
        builder: (controller) {
          if (controller.isLoading && controller.notifications == null) {
            return const Center(child: CircularProgressIndicator());
          }

          return RefreshIndicator(
            onRefresh: controller.getNotifications,
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: controller.notifications?.length ?? 0,
              itemBuilder: (context, index) {
                final notification = controller.notifications![index];
                return Card(
                  child: ListTile(
                    title: Text(notification.title ?? ''),
                    subtitle: Text(notification.message ?? ''),
                    trailing: notification.isRead == true
                        ? const Icon(Icons.check_circle, color: Colors.green)
                        : const Icon(Icons.circle, size: 10),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
