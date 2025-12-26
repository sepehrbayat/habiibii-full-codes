import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/vendor_beauty_booking_controller.dart';

class VendorBeautyBookingDetailsScreen extends StatelessWidget {
  final int bookingId;

  const VendorBeautyBookingDetailsScreen({super.key, required this.bookingId});

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<VendorBeautyBookingController>();
    controller.getBookingDetails(bookingId);

    return Scaffold(
      appBar: AppBar(title: const Text('Booking Details')),
      body: GetBuilder<VendorBeautyBookingController>(
        builder: (controller) {
          if (controller.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          final booking = controller.selectedBooking;
          if (booking == null) {
            return const Center(child: Text('Booking not found'));
          }

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              ListTile(
                title: const Text('Customer'),
                subtitle: Text(booking.customerName ?? ''),
              ),
              ListTile(
                title: const Text('Service'),
                subtitle: Text(booking.serviceName ?? ''),
              ),
              ListTile(
                title: const Text('Staff'),
                subtitle: Text(booking.staffName ?? ''),
              ),
              ListTile(
                title: const Text('Date & Time'),
                subtitle: Text(booking.bookingDateTime ?? ''),
              ),
              ListTile(
                title: const Text('Status'),
                subtitle: Text(booking.status ?? ''),
              ),
              ListTile(
                title: const Text('Payment Status'),
                subtitle: Text(booking.paymentStatus ?? ''),
              ),
              if (booking.totalAmount != null)
                ListTile(
                  title: const Text('Total Amount'),
                  subtitle: Text('\$${booking.totalAmount!.toStringAsFixed(2)}'),
                ),
              if (booking.notes != null)
                ListTile(
                  title: const Text('Notes'),
                  subtitle: Text(booking.notes ?? ''),
                ),
              const SizedBox(height: 16),
              if (booking.canConfirm)
                ElevatedButton(
                  onPressed: () => controller.confirmBooking(bookingId),
                  child: const Text('Confirm Booking'),
                ),
              if (booking.canComplete)
                ElevatedButton(
                  onPressed: () => controller.completeBooking(bookingId),
                  child: const Text('Complete Booking'),
                ),
              ElevatedButton(
                onPressed: () => controller.markBookingPaid(bookingId),
                child: const Text('Mark Paid'),
              ),
              if (booking.canCancel)
                OutlinedButton(
                  onPressed: () async {
                    final reason = await _showCancelDialog(context);
                    if (reason != null && reason.isNotEmpty) {
                      controller.cancelBooking(bookingId, reason);
                    }
                  },
                  child: const Text('Cancel Booking'),
                ),
            ],
          );
        },
      ),
    );
  }

  Future<String?> _showCancelDialog(BuildContext context) async {
    String reason = '';
    return showDialog<String>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Cancel Booking'),
          content: TextField(
            onChanged: (value) => reason = value,
            decoration: const InputDecoration(hintText: 'Reason'),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Close'),
            ),
            TextButton(
              onPressed: () => Navigator.pop(context, reason),
              child: const Text('Submit'),
            ),
          ],
        );
      },
    );
  }
}
