class VendorBeautyRetailProductModel {
  int? id;
  String? name;
  String? description;
  double? price;
  double? discount;
  int? stock;
  String? image;
  bool? isActive;
  String? createdAt;
  String? updatedAt;

  VendorBeautyRetailProductModel({
    this.id,
    this.name,
    this.description,
    this.price,
    this.discount,
    this.stock,
    this.image,
    this.isActive,
    this.createdAt,
    this.updatedAt,
  });

  VendorBeautyRetailProductModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    description = json['description'];
    price = json['price']?.toDouble();
    discount = json['discount']?.toDouble();
    stock = json['stock'];
    image = json['image'];
    isActive = json['is_active'] ?? json['status'] == 'active';
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final data = <String, dynamic>{};
    data['id'] = id;
    data['name'] = name;
    data['description'] = description;
    data['price'] = price;
    data['discount'] = discount;
    data['stock'] = stock;
    data['image'] = image;
    data['is_active'] = isActive;
    return data;
  }

  double get finalPrice => price != null && discount != null
      ? price! - (price! * discount! / 100)
      : price ?? 0;
}
