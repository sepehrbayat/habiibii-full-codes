import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:sixam_mart/features/beauty_module/retail/controllers/beauty_retail_controller.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class BeautyRetailCheckoutScreen extends StatefulWidget {
  const BeautyRetailCheckoutScreen({super.key});

  @override
  State<BeautyRetailCheckoutScreen> createState() => _BeautyRetailCheckoutScreenState();
}

class _BeautyRetailCheckoutScreenState extends State<BeautyRetailCheckoutScreen> {
  final _addressController = TextEditingController();
  final _cityController = TextEditingController();
  final _zipController = TextEditingController();
  String _paymentMethod = 'cash_on_delivery';

  @override
  void dispose() {
    _addressController.dispose();
    _cityController.dispose();
    _zipController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('checkout'.tr),
      ),
      body: GetBuilder<BeautyRetailController>(
        builder: (controller) {
          return SingleChildScrollView(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('delivery_address'.tr, style: robotoMedium),
                const SizedBox(height: 12),
                TextField(
                  controller: _addressController,
                  decoration: InputDecoration(
                    labelText: 'address'.tr,
                    border: const OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _cityController,
                  decoration: InputDecoration(
                    labelText: 'city'.tr,
                    border: const OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _zipController,
                  decoration: InputDecoration(
                    labelText: 'zip'.tr,
                    border: const OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 20),
                Text('payment_method'.tr, style: robotoMedium),
                const SizedBox(height: 8),
                RadioListTile<String>(
                  title: Text('cash_on_delivery'.tr),
                  value: 'cash_on_delivery',
                  groupValue: _paymentMethod,
                  onChanged: (value) => setState(() => _paymentMethod = value ?? 'cash_on_delivery'),
                ),
                RadioListTile<String>(
                  title: Text('digital_payment'.tr),
                  value: 'digital_payment',
                  groupValue: _paymentMethod,
                  onChanged: (value) => setState(() => _paymentMethod = value ?? 'digital_payment'),
                ),
                const SizedBox(height: 20),
                _SummaryRow(label: 'subtotal'.tr, value: '\$${controller.cartTotal.toStringAsFixed(2)}'),
                _SummaryRow(label: 'discount'.tr, value: '-\$${controller.discountAmount.toStringAsFixed(2)}'),
                const SizedBox(height: 8),
                _SummaryRow(
                  label: 'total'.tr,
                  value: '\$${controller.finalTotal.toStringAsFixed(2)}',
                  isTotal: true,
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: controller.isPlacingOrder
                        ? null
                        : () async {
                            if (_addressController.text.trim().isEmpty || _cityController.text.trim().isEmpty) {
                              showCustomSnackBar('Please provide address details');
                              return;
                            }

                            await controller.placeOrder(
                              paymentMethod: _paymentMethod,
                              deliveryAddress: {
                                'address': _addressController.text.trim(),
                                'city': _cityController.text.trim(),
                                'zip': _zipController.text.trim(),
                              },
                            );
                          },
                    child: controller.isPlacingOrder
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : Text('place_order'.tr),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _SummaryRow extends StatelessWidget {
  final String label;
  final String value;
  final bool isTotal;

  const _SummaryRow({
    required this.label,
    required this.value,
    this.isTotal = false,
  });

  @override
  Widget build(BuildContext context) {
    final style = isTotal ? robotoBold : robotoRegular;
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: style),
        Text(value, style: style),
      ],
    );
  }
}
