import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/vendor_beauty_calendar_controller.dart';
import '../domain/models/vendor_beauty_calendar_block_model.dart';

class VendorBeautyCalendarScreen extends StatefulWidget {
  const VendorBeautyCalendarScreen({super.key});

  @override
  State<VendorBeautyCalendarScreen> createState() => _VendorBeautyCalendarScreenState();
}

class _VendorBeautyCalendarScreenState extends State<VendorBeautyCalendarScreen> {
  DateTime _selectedDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    Get.find<VendorBeautyCalendarController>().getAvailability(
      date: _formatDate(_selectedDate),
    );
  }

  String _formatDate(DateTime date) {
    return date.toIso8601String().split('T')[0];
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Beauty Calendar')),
      body: GetBuilder<VendorBeautyCalendarController>(
        builder: (controller) {
          return Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Expanded(
                      child: Text('Date: ${_formatDate(_selectedDate)}'),
                    ),
                    TextButton(
                      onPressed: () async {
                        final picked = await showDatePicker(
                          context: context,
                          initialDate: _selectedDate,
                          firstDate: DateTime(2020),
                          lastDate: DateTime(2100),
                        );
                        if (picked != null) {
                          setState(() => _selectedDate = picked);
                          _load();
                        }
                      },
                      child: const Text('Select Date'),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: controller.isLoading
                    ? const Center(child: CircularProgressIndicator())
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: controller.blocks?.length ?? 0,
                        itemBuilder: (context, index) {
                          final block = controller.blocks![index];
                          return Card(
                            child: ListTile(
                              title: Text('${block.startTime ?? ''} - ${block.endTime ?? ''}'),
                              subtitle: Text(block.reason ?? 'Blocked'),
                              trailing: IconButton(
                                icon: const Icon(Icons.delete),
                                onPressed: () => controller.deleteBlock(
                                  block.id ?? 0,
                                  date: _formatDate(_selectedDate),
                                ),
                              ),
                            ),
                          );
                        },
                      ),
              ),
            ],
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final block = await _showCreateBlockDialog(context, _formatDate(_selectedDate));
          if (block != null) {
            Get.find<VendorBeautyCalendarController>().createBlock(block);
          }
        },
        child: const Icon(Icons.add),
      ),
    );
  }

  Future<VendorBeautyCalendarBlockModel?> _showCreateBlockDialog(BuildContext context, String date) async {
    final startController = TextEditingController();
    final endController = TextEditingController();
    final reasonController = TextEditingController();

    return showDialog<VendorBeautyCalendarBlockModel>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Block Time Slot'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: startController,
                decoration: const InputDecoration(labelText: 'Start Time (e.g. 09:00)'),
              ),
              TextField(
                controller: endController,
                decoration: const InputDecoration(labelText: 'End Time (e.g. 10:00)'),
              ),
              TextField(
                controller: reasonController,
                decoration: const InputDecoration(labelText: 'Reason'),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel'),
            ),
            TextButton(
              onPressed: () {
                Navigator.pop(
                  context,
                  VendorBeautyCalendarBlockModel(
                    date: date,
                    startTime: startController.text,
                    endTime: endController.text,
                    reason: reasonController.text,
                  ),
                );
              },
              child: const Text('Create'),
            ),
          ],
        );
      },
    );
  }
}
