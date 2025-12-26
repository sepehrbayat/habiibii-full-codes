import 'package:flutter/material.dart';
import '../domain/models/vendor_beauty_booking_model.dart';

class BookingListTileWidget extends StatelessWidget {
  final VendorBeautyBookingModel booking;
  final VoidCallback onTap;

  const BookingListTileWidget({
    super.key,
    required this.booking,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 6),
      child: ListTile(
        title: Text(booking.customerName ?? 'Customer'),
        subtitle: Text('${booking.serviceName ?? 'Service'} • ${booking.bookingDateTime ?? ''}'),
        trailing: Text(booking.status ?? ''),
        onTap: onTap,
      ),
    );
  }
}
