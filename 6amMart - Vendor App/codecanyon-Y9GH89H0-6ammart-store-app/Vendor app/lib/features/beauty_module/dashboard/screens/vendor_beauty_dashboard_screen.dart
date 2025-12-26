import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/vendor_beauty_dashboard_controller.dart';
import '../widgets/dashboard_stat_card_widget.dart';

class VendorBeautyDashboardScreen extends StatelessWidget {
  const VendorBeautyDashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Beauty Dashboard')),
      body: GetBuilder<VendorBeautyDashboardController>(
        builder: (controller) {
          if (controller.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          final dashboard = controller.dashboard;
          if (dashboard == null) {
            return const Center(child: Text('No dashboard data')); 
          }

          return RefreshIndicator(
            onRefresh: controller.getDashboard,
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                DashboardStatCardWidget(
                  title: 'Total Bookings',
                  value: '${dashboard.totalBookings ?? 0}',
                  icon: Icons.calendar_today,
                ),
                const SizedBox(height: 12),
                DashboardStatCardWidget(
                  title: 'Pending Bookings',
                  value: '${dashboard.pendingBookings ?? 0}',
                  icon: Icons.schedule,
                ),
                const SizedBox(height: 12),
                DashboardStatCardWidget(
                  title: 'Completed Bookings',
                  value: '${dashboard.completedBookings ?? 0}',
                  icon: Icons.check_circle,
                ),
                const SizedBox(height: 12),
                DashboardStatCardWidget(
                  title: 'Cancelled Bookings',
                  value: '${dashboard.cancelledBookings ?? 0}',
                  icon: Icons.cancel,
                ),
                const SizedBox(height: 12),
                DashboardStatCardWidget(
                  title: 'Total Revenue',
                  value: '\$${(dashboard.totalRevenue ?? 0).toStringAsFixed(2)}',
                  icon: Icons.attach_money,
                ),
                const SizedBox(height: 12),
                DashboardStatCardWidget(
                  title: 'Today Revenue',
                  value: '\$${(dashboard.todayRevenue ?? 0).toStringAsFixed(2)}',
                  icon: Icons.payments,
                ),
                const SizedBox(height: 12),
                DashboardStatCardWidget(
                  title: 'Total Staff',
                  value: '${dashboard.totalStaff ?? 0}',
                  icon: Icons.people,
                ),
                const SizedBox(height: 12),
                DashboardStatCardWidget(
                  title: 'Total Services',
                  value: '${dashboard.totalServices ?? 0}',
                  icon: Icons.design_services,
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
