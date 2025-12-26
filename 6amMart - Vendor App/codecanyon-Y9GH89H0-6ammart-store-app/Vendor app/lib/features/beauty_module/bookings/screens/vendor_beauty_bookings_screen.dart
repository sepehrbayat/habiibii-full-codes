import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/vendor_beauty_booking_controller.dart';
import '../widgets/booking_list_tile_widget.dart';
import 'vendor_beauty_booking_details_screen.dart';

class VendorBeautyBookingsScreen extends StatelessWidget {
  const VendorBeautyBookingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Beauty Bookings')),
      body: GetBuilder<VendorBeautyBookingController>(
        builder: (controller) {
          return Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(16),
                child: DropdownButtonFormField<String>(
                  value: controller.status,
                  decoration: const InputDecoration(labelText: 'Status'),
                  items: const [
                    DropdownMenuItem(value: 'all', child: Text('All')),
                    DropdownMenuItem(value: 'pending', child: Text('Pending')),
                    DropdownMenuItem(value: 'confirmed', child: Text('Confirmed')),
                    DropdownMenuItem(value: 'completed', child: Text('Completed')),
                    DropdownMenuItem(value: 'cancelled', child: Text('Cancelled')),
                  ],
                  onChanged: (value) {
                    if (value != null) {
                      controller.setStatus(value);
                    }
                  },
                ),
              ),
              Expanded(
                child: controller.isLoading && (controller.bookingList == null)
                    ? const Center(child: CircularProgressIndicator())
                    : RefreshIndicator(
                        onRefresh: () => controller.getBookings(reload: true),
                        child: ListView.builder(
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          itemCount: controller.bookingList?.length ?? 0,
                          itemBuilder: (context, index) {
                            final booking = controller.bookingList![index];
                            return BookingListTileWidget(
                              booking: booking,
                              onTap: () {
                                Get.to(() => VendorBeautyBookingDetailsScreen(bookingId: booking.id ?? 0));
                              },
                            );
                          },
                        ),
                      ),
              ),
            ],
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Get.find<VendorBeautyBookingController>().getBookings(reload: true),
        child: const Icon(Icons.refresh),
      ),
    );
  }
}
