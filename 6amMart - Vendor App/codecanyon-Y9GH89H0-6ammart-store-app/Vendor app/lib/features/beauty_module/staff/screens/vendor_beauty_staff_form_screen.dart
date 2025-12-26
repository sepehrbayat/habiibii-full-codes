import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/vendor_beauty_staff_controller.dart';
import '../domain/models/vendor_beauty_staff_model.dart';

class VendorBeautyStaffFormScreen extends StatefulWidget {
  final VendorBeautyStaffModel? staff;

  const VendorBeautyStaffFormScreen({super.key, this.staff});

  @override
  State<VendorBeautyStaffFormScreen> createState() => _VendorBeautyStaffFormScreenState();
}

class _VendorBeautyStaffFormScreenState extends State<VendorBeautyStaffFormScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nameController;
  late final TextEditingController _emailController;
  late final TextEditingController _phoneController;
  late final TextEditingController _designationController;

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: widget.staff?.name ?? '');
    _emailController = TextEditingController(text: widget.staff?.email ?? '');
    _phoneController = TextEditingController(text: widget.staff?.phone ?? '');
    _designationController = TextEditingController(text: widget.staff?.designation ?? '');
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _designationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<VendorBeautyStaffController>();
    final isEdit = widget.staff != null;

    return Scaffold(
      appBar: AppBar(title: Text(isEdit ? 'Edit Staff' : 'Add Staff')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
              TextFormField(
                controller: _nameController,
                decoration: const InputDecoration(labelText: 'Name'),
                validator: (value) => value == null || value.isEmpty ? 'Name is required' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _emailController,
                decoration: const InputDecoration(labelText: 'Email'),
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _phoneController,
                decoration: const InputDecoration(labelText: 'Phone'),
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _designationController,
                decoration: const InputDecoration(labelText: 'Designation'),
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () async {
                  if (!_formKey.currentState!.validate()) return;

                  final staff = VendorBeautyStaffModel(
                    id: widget.staff?.id,
                    name: _nameController.text,
                    email: _emailController.text,
                    phone: _phoneController.text,
                    designation: _designationController.text,
                  );

                  bool success;
                  if (isEdit && widget.staff?.id != null) {
                    success = await controller.updateStaff(widget.staff!.id!, staff);
                  } else {
                    success = await controller.createStaff(staff);
                  }

                  if (success) {
                    Get.back();
                  }
                },
                child: Text(isEdit ? 'Update Staff' : 'Create Staff'),
              ),
              if (isEdit && widget.staff?.id != null)
                TextButton(
                  onPressed: () => controller.deleteStaff(widget.staff!.id!),
                  child: const Text('Delete Staff'),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
