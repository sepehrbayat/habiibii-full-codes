class BeautyConsultantModel {
  final int? id;
  final String? name;
  final String? specialization;
  final double? rating;
  final String? image;
  final String? bio;

  BeautyConsultantModel({
    this.id,
    this.name,
    this.specialization,
    this.rating,
    this.image,
    this.bio,
  });

  factory BeautyConsultantModel.fromJson(Map<String, dynamic> json) {
    return BeautyConsultantModel(
      id: json['id'],
      name: json['name'],
      specialization: json['specialization'],
      rating: json['rating']?.toDouble(),
      image: json['image'],
      bio: json['bio'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'specialization': specialization,
      'rating': rating,
      'image': image,
      'bio': bio,
    };
  }
}
