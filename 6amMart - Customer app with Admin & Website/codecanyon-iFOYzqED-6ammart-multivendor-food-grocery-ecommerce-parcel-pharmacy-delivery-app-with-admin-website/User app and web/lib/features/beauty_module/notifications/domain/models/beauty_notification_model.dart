class BeautyNotificationModel {
  final int? id;
  final String? title;
  final String? message;
  final bool? isRead;
  final String? createdAt;

  BeautyNotificationModel({
    this.id,
    this.title,
    this.message,
    this.isRead,
    this.createdAt,
  });

  factory BeautyNotificationModel.fromJson(Map<String, dynamic> json) {
    return BeautyNotificationModel(
      id: json['id'],
      title: json['title'],
      message: json['message'],
      isRead: json['is_read'],
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'message': message,
      'is_read': isRead,
      'created_at': createdAt,
    };
  }
}
