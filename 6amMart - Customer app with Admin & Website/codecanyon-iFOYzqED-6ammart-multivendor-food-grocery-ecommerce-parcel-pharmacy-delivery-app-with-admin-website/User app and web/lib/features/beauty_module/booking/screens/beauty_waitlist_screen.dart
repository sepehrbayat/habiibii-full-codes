import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/features/beauty_module/booking/controllers/beauty_booking_controller.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class BeautyWaitlistScreen extends StatefulWidget {
  const BeautyWaitlistScreen({super.key});

  @override
  State<BeautyWaitlistScreen> createState() => _BeautyWaitlistScreenState();
}

class _BeautyWaitlistScreenState extends State<BeautyWaitlistScreen> {
  final TextEditingController _salonIdController = TextEditingController();
  final TextEditingController _serviceIdController = TextEditingController();
  final TextEditingController _dateController = TextEditingController();
  final TextEditingController _timeController = TextEditingController();
  final TextEditingController _noteController = TextEditingController();

  List<Map<String, dynamic>> _entries = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadWaitlist();
  }

  @override
  void dispose() {
    _salonIdController.dispose();
    _serviceIdController.dispose();
    _dateController.dispose();
    _timeController.dispose();
    _noteController.dispose();
    super.dispose();
  }

  Future<void> _loadWaitlist() async {
    setState(() {
      _isLoading = true;
    });
    final entries = await Get.find<BeautyBookingController>().getWaitlist();
    setState(() {
      _entries = entries;
      _isLoading = false;
    });
  }

  Future<void> _joinWaitlist() async {
    final int? salonId = int.tryParse(_salonIdController.text.trim());
    final int? serviceId = int.tryParse(_serviceIdController.text.trim());
    if (salonId == null || serviceId == null) {
      Get.snackbar('error'.tr, 'invalid_input'.tr);
      return;
    }

    final payload = <String, dynamic>{
      'salon_id': salonId,
      'service_id': serviceId,
      if (_dateController.text.trim().isNotEmpty) 'date': _dateController.text.trim(),
      if (_timeController.text.trim().isNotEmpty) 'time': _timeController.text.trim(),
      if (_noteController.text.trim().isNotEmpty) 'notes': _noteController.text.trim(),
    };

    final success = await Get.find<BeautyBookingController>().joinWaitlist(payload);
    if (success) {
      _salonIdController.clear();
      _serviceIdController.clear();
      _dateController.clear();
      _timeController.clear();
      _noteController.clear();
      await _loadWaitlist();
    } else {
      Get.snackbar('error'.tr, 'failed_to_join_waitlist'.tr);
    }
  }

  Future<void> _leaveWaitlist(int waitlistId) async {
    final success = await Get.find<BeautyBookingController>().leaveWaitlist(waitlistId);
    if (success) {
      await _loadWaitlist();
    } else {
      Get.snackbar('error'.tr, 'failed_to_leave_waitlist'.tr);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(title: 'waitlist'.tr),
      body: RefreshIndicator(
        onRefresh: _loadWaitlist,
        child: ListView(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          children: [
            Text('join_waitlist'.tr, style: robotoBold),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            _buildTextField(_salonIdController, 'salon_id'.tr),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            _buildTextField(_serviceIdController, 'service_id'.tr),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            _buildTextField(_dateController, 'date'.tr),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            _buildTextField(_timeController, 'time'.tr),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            _buildTextField(_noteController, 'notes'.tr),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            ElevatedButton(
              onPressed: _joinWaitlist,
              child: Text('submit'.tr),
            ),
            const SizedBox(height: Dimensions.paddingSizeLarge),
            Text('my_waitlist'.tr, style: robotoBold),
            const SizedBox(height: Dimensions.paddingSizeSmall),
            if (_isLoading) const Center(child: CircularProgressIndicator()),
            if (!_isLoading && _entries.isEmpty)
              Text('no_data_found'.tr, style: robotoRegular),
            if (!_isLoading)
              ..._entries.map((entry) {
                final int? id = entry['id'] is int ? entry['id'] : int.tryParse('${entry['id']}');
                return Container(
                  margin: const EdgeInsets.only(bottom: Dimensions.paddingSizeSmall),
                  padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                  decoration: BoxDecoration(
                    color: Theme.of(context).cardColor,
                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.04),
                        blurRadius: 4,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          entry.toString(),
                          style: robotoRegular.copyWith(fontSize: Dimensions.fontSizeSmall),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (id != null)
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => _leaveWaitlist(id),
                        ),
                    ],
                  ),
                );
              }).toList(),
          ],
        ),
      ),
    );
  }

  Widget _buildTextField(TextEditingController controller, String hint) {
    return TextField(
      controller: controller,
      decoration: InputDecoration(
        hintText: hint,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
        ),
        contentPadding: const EdgeInsets.symmetric(
          horizontal: Dimensions.paddingSizeSmall,
          vertical: Dimensions.paddingSizeSmall,
        ),
      ),
    );
  }
}
