import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart_store/features/beauty_module/retail/controllers/vendor_beauty_retail_controller.dart';
import 'package:sixam_mart_store/features/beauty_module/retail/domain/models/vendor_beauty_retail_product_model.dart';
import 'package:sixam_mart_store/util/dimensions.dart';

class VendorBeautyRetailProductFormScreen extends StatefulWidget {
  final int? productId;

  const VendorBeautyRetailProductFormScreen({
    super.key,
    this.productId,
  });

  @override
  State<VendorBeautyRetailProductFormScreen> createState() => _VendorBeautyRetailProductFormScreenState();
}

class _VendorBeautyRetailProductFormScreenState extends State<VendorBeautyRetailProductFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _priceController = TextEditingController();
  final _stockController = TextEditingController();
  final _descriptionController = TextEditingController();

  @override
  void initState() {
    super.initState();
    if (widget.productId != null) {
      _loadProduct();
    }
  }

  Future<void> _loadProduct() async {
    final controller = Get.find<VendorBeautyRetailController>();
    await controller.getProductDetails(widget.productId!);
    final product = controller.selectedProduct;
    if (product != null) {
      _nameController.text = product.name ?? '';
      _priceController.text = product.price?.toString() ?? '';
      _stockController.text = product.stock?.toString() ?? '';
      _descriptionController.text = product.description ?? '';
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _priceController.dispose();
    _stockController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final controller = Get.find<VendorBeautyRetailController>();
    return Scaffold(
      appBar: AppBar(title: Text(widget.productId == null ? 'New Product' : 'Edit Product')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              TextFormField(
                controller: _nameController,
                decoration: const InputDecoration(labelText: 'Name'),
                validator: (value) => value == null || value.trim().isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: Dimensions.paddingSizeDefault),
              TextFormField(
                controller: _priceController,
                decoration: const InputDecoration(labelText: 'Price'),
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                validator: (value) => value == null || value.trim().isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: Dimensions.paddingSizeDefault),
              TextFormField(
                controller: _stockController,
                decoration: const InputDecoration(labelText: 'Stock'),
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: Dimensions.paddingSizeDefault),
              TextFormField(
                controller: _descriptionController,
                decoration: const InputDecoration(labelText: 'Description'),
                maxLines: 4,
              ),
              const SizedBox(height: Dimensions.paddingSizeDefault),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () async {
                    if (!_formKey.currentState!.validate()) return;

                    final product = VendorBeautyRetailProductModel(
                      name: _nameController.text.trim(),
                      price: double.tryParse(_priceController.text.trim()),
                      stock: int.tryParse(_stockController.text.trim()),
                      description: _descriptionController.text.trim(),
                    );

                    if (widget.productId == null) {
                      await controller.createProduct(product);
                    } else {
                      await controller.updateProduct(widget.productId!, product);
                    }

                    Get.back();
                  },
                  child: Text(widget.productId == null ? 'Create' : 'Update'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
