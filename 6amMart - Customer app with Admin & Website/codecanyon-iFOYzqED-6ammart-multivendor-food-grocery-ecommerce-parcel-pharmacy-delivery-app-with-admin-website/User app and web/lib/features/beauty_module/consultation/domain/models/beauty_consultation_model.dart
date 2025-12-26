class BeautyConsultationModel {
  final int? id;
  final int? consultantId;
  final String? consultantName;
  final String? status;
  final String? scheduledAt;
  final String? type;
  final String? notes;
  final String? createdAt;

  BeautyConsultationModel({
    this.id,
    this.consultantId,
    this.consultantName,
    this.status,
    this.scheduledAt,
    this.type,
    this.notes,
    this.createdAt,
  });

  factory BeautyConsultationModel.fromJson(Map<String, dynamic> json) {
    return BeautyConsultationModel(
      id: json['id'],
      consultantId: json['consultant_id'],
      consultantName: json['consultant']?['name'] ?? json['consultant_name'],
      status: json['status'],
      scheduledAt: json['scheduled_at'] ?? json['date_time'],
      type: json['type'],
      notes: json['notes'],
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'consultant_id': consultantId,
      'status': status,
      'scheduled_at': scheduledAt,
      'type': type,
      'notes': notes,
      'created_at': createdAt,
    };
  }
}
