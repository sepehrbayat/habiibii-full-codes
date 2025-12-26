class BeautyRetailProductModel {
  int? id;
  String? name;
  String? description;
  double? price;
  double? discount;
  int? stock;
  String? image;
  int? salonId;
  String? salonName;
  int? categoryId;
  String? categoryName;
  String? brand;
  List<String>? images;
  double? rating;
  int? reviewCount;
  bool? isPopular;
  bool? isRecommended;
  bool? isFeatured;
  String? unit;
  double? weight;
  String? sku;
  Map<String, dynamic>? specifications;
  List<String>? tags;
  DateTime? createdAt;
  DateTime? updatedAt;

  BeautyRetailProductModel({
    this.id,
    this.name,
    this.description,
    this.price,
    this.discount,
    this.stock,
    this.image,
    this.salonId,
    this.salonName,
    this.categoryId,
    this.categoryName,
    this.brand,
    this.images,
    this.rating,
    this.reviewCount,
    this.isPopular,
    this.isRecommended,
    this.isFeatured,
    this.unit,
    this.weight,
    this.sku,
    this.specifications,
    this.tags,
    this.createdAt,
    this.updatedAt,
  });

  BeautyRetailProductModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    description = json['description'];
    price = json['price']?.toDouble();
    discount = json['discount']?.toDouble();
    stock = json['stock'];
    image = json['image'];
    salonId = json['salon_id'];
    salonName = json['salon_name'];
    categoryId = json['category_id'];
    categoryName = json['category_name'];
    brand = json['brand'];
    images = json['images'] != null ? List<String>.from(json['images']) : null;
    rating = json['rating']?.toDouble();
    reviewCount = json['review_count'];
    isPopular = json['is_popular'];
    isRecommended = json['is_recommended'];
    isFeatured = json['is_featured'];
    unit = json['unit'];
    weight = json['weight']?.toDouble();
    sku = json['sku'];
    specifications = json['specifications'];
    tags = json['tags'] != null ? List<String>.from(json['tags']) : null;
    createdAt = json['created_at'] != null ? DateTime.parse(json['created_at']) : null;
    updatedAt = json['updated_at'] != null ? DateTime.parse(json['updated_at']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['id'] = id;
    data['name'] = name;
    data['description'] = description;
    data['price'] = price;
    data['discount'] = discount;
    data['stock'] = stock;
    data['image'] = image;
    data['salon_id'] = salonId;
    data['salon_name'] = salonName;
    data['category_id'] = categoryId;
    data['category_name'] = categoryName;
    data['brand'] = brand;
    data['images'] = images;
    data['rating'] = rating;
    data['review_count'] = reviewCount;
    data['is_popular'] = isPopular;
    data['is_recommended'] = isRecommended;
    data['is_featured'] = isFeatured;
    data['unit'] = unit;
    data['weight'] = weight;
    data['sku'] = sku;
    data['specifications'] = specifications;
    data['tags'] = tags;
    data['created_at'] = createdAt?.toIso8601String();
    data['updated_at'] = updatedAt?.toIso8601String();
    return data;
  }

  double get finalPrice => price != null && discount != null 
      ? price! - (price! * discount! / 100) 
      : price ?? 0;
  
  bool get isInStock => stock != null && stock! > 0;
  
  bool get hasDiscount => discount != null && discount! > 0;
  
  String get displayPrice => '\$${price?.toStringAsFixed(2) ?? '0.00'}';
  
  String get displayFinalPrice => '\$${finalPrice.toStringAsFixed(2)}';
}
