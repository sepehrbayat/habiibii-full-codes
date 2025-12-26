class BeautyServiceCategoryModel {
  final int? id;
  final String? name;
  final String? description;
  final String? image;
  final bool? isActive;
  final int? servicesCount;
  final String? createdAt;
  final String? updatedAt;

  BeautyServiceCategoryModel({
    this.id,
    this.name,
    this.description,
    this.image,
    this.isActive,
    this.servicesCount,
    this.createdAt,
    this.updatedAt,
  });

  factory BeautyServiceCategoryModel.fromJson(Map<String, dynamic> json) {
    return BeautyServiceCategoryModel(
      id: json['id'],
      name: json['name'],
      description: json['description'],
      image: json['image'],
      isActive: json['is_active'],
      servicesCount: json['services_count'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'image': image,
      'is_active': isActive,
      'services_count': servicesCount,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }
}
