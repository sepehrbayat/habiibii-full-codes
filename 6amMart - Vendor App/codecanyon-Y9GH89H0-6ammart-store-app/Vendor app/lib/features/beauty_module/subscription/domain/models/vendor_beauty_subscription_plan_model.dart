class VendorBeautySubscriptionPlanModel {
  int? id;
  String? name;
  double? price;
  int? durationDays;
  String? description;

  VendorBeautySubscriptionPlanModel({
    this.id,
    this.name,
    this.price,
    this.durationDays,
    this.description,
  });

  VendorBeautySubscriptionPlanModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    price = json['price']?.toDouble();
    durationDays = json['duration_days'] ?? json['duration'];
    description = json['description'];
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'price': price,
      'duration_days': durationDays,
      'description': description,
    };
  }
}
