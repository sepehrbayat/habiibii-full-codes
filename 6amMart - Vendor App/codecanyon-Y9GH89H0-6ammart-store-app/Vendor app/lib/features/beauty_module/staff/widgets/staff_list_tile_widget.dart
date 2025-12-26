import 'package:flutter/material.dart';
import '../domain/models/vendor_beauty_staff_model.dart';

class StaffListTileWidget extends StatelessWidget {
  final VendorBeautyStaffModel staff;
  final VoidCallback onTap;

  const StaffListTileWidget({
    super.key,
    required this.staff,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 6),
      child: ListTile(
        title: Text(staff.name ?? 'Staff'),
        subtitle: Text(staff.designation ?? ''),
        trailing: Icon(staff.isActive == true ? Icons.check_circle : Icons.cancel,
            color: staff.isActive == true ? Colors.green : Colors.red),
        onTap: onTap,
      ),
    );
  }
}
