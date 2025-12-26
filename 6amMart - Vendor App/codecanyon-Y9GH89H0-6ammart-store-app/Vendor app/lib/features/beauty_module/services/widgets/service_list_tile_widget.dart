import 'package:flutter/material.dart';
import '../domain/models/vendor_beauty_service_model.dart';

class ServiceListTileWidget extends StatelessWidget {
  final VendorBeautyServiceModel service;
  final VoidCallback onTap;

  const ServiceListTileWidget({
    super.key,
    required this.service,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 6),
      child: ListTile(
        title: Text(service.name ?? 'Service'),
        subtitle: Text('Duration: ${service.durationMinutes ?? 0} min'),
        trailing: Text('\$${(service.price ?? 0).toStringAsFixed(2)}'),
        onTap: onTap,
      ),
    );
  }
}
