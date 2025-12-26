class BeautyBookingConversationModel {
  final int? conversationId;
  final List<BeautyBookingMessageModel> messages;

  BeautyBookingConversationModel({
    this.conversationId,
    required this.messages,
  });

  factory BeautyBookingConversationModel.fromJson(Map<String, dynamic> json) {
    final List<BeautyBookingMessageModel> items = [];
    if (json['messages'] != null) {
      for (final message in json['messages']) {
        items.add(BeautyBookingMessageModel.fromJson(message));
      }
    }
    return BeautyBookingConversationModel(
      conversationId: json['conversation_id'],
      messages: items,
    );
  }
}

class BeautyBookingMessageModel {
  final int? id;
  final int? senderId;
  final String? message;
  final String? fileUrl;
  final String? senderType;
  final String? createdAt;

  BeautyBookingMessageModel({
    this.id,
    this.senderId,
    this.message,
    this.fileUrl,
    this.senderType,
    this.createdAt,
  });

  factory BeautyBookingMessageModel.fromJson(Map<String, dynamic> json) {
    return BeautyBookingMessageModel(
      id: json['id'],
      senderId: json['sender_id'],
      message: json['message'],
      fileUrl: json['file_url'],
      senderType: json['sender_type'],
      createdAt: json['created_at'],
    );
  }
}
