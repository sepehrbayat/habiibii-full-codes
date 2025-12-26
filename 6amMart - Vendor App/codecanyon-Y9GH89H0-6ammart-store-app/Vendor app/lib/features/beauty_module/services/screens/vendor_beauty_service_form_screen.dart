import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/vendor_beauty_service_controller.dart';
import '../domain/models/vendor_beauty_service_model.dart';

class VendorBeautyServiceFormScreen extends StatefulWidget {
  final VendorBeautyServiceModel? service;

  const VendorBeautyServiceFormScreen({super.key, this.service});

  @override
  State<VendorBeautyServiceFormScreen> createState() => _VendorBeautyServiceFormScreenState();
}

class _VendorBeautyServiceFormScreenState extends State<VendorBeautyServiceFormScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nameController;
  late final TextEditingController _descriptionController;
  late final TextEditingController _durationController;
  late final TextEditingController _priceController;

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: widget.service?.name ?? '');
    _descriptionController = TextEditingController(text: widget.service?.description ?? '');
    _durationController = TextEditingController(text: widget.service?.durationMinutes?.toString() ?? '');
    _priceController = TextEditingController(text: widget.service?.price?.toString() ?? '');
  }

  @override
  void dispose() {
    _nameController.dispose();
    _descriptionController.dispose();
    _durationController.dispose();
    _priceController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<VendorBeautyServiceController>();
    final isEdit = widget.service != null;

    return Scaffold(
      appBar: AppBar(title: Text(isEdit ? 'Edit Service' : 'Add Service')),
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
                controller: _descriptionController,
                decoration: const InputDecoration(labelText: 'Description'),
                maxLines: 3,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _durationController,
                decoration: const InputDecoration(labelText: 'Duration (minutes)'),
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _priceController,
                decoration: const InputDecoration(labelText: 'Price'),
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () async {
                  if (!_formKey.currentState!.validate()) return;

                  final service = VendorBeautyServiceModel(
                    id: widget.service?.id,
                    name: _nameController.text,
                    description: _descriptionController.text,
                    durationMinutes: int.tryParse(_durationController.text),
                    price: double.tryParse(_priceController.text),
                  );

                  bool success;
                  if (isEdit && widget.service?.id != null) {
                    success = await controller.updateService(widget.service!.id!, service);
                  } else {
                    success = await controller.createService(service);
                  }

                  if (success) {
                    Get.back();
                  }
                },
                child: Text(isEdit ? 'Update Service' : 'Create Service'),
              ),
              if (isEdit && widget.service?.id != null)
                TextButton(
                  onPressed: () => controller.deleteService(widget.service!.id!),
                  child: const Text('Delete Service'),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
