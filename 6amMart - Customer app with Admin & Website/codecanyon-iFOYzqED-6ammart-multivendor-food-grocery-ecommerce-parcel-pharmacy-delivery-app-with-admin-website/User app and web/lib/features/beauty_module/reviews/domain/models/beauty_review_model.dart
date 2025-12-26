class BeautyReviewModel {
  final int? id;
  final int? userId;
  final int? salonId;
  final int? bookingId;
  final int? rating;
  final String? comment;
  final String? reply;
  final String? userName;
  final String? salonName;
  final String? createdAt;
  final String? updatedAt;

  BeautyReviewModel({
    this.id,
    this.userId,
    this.salonId,
    this.bookingId,
    this.rating,
    this.comment,
    this.reply,
    this.userName,
    this.salonName,
    this.createdAt,
    this.updatedAt,
  });

  factory BeautyReviewModel.fromJson(Map<String, dynamic> json) {
    return BeautyReviewModel(
      id: json['id'],
      userId: json['user_id'],
      salonId: json['salon_id'],
      bookingId: json['booking_id'],
      rating: json['rating'],
      comment: json['comment'],
      reply: json['reply'],
      userName: json['user']?['f_name'] != null && json['user']?['l_name'] != null
          ? '${json['user']['f_name']} ${json['user']['l_name']}'
          : null,
      salonName: json['salon']?['store']?['name'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'salon_id': salonId,
      'booking_id': bookingId,
      'rating': rating,
      'comment': comment,
      'reply': reply,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }
}
