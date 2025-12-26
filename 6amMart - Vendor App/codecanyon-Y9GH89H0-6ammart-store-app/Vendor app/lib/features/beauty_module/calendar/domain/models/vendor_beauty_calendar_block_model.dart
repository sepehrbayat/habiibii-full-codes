class VendorBeautyCalendarBlockModel {
  final int? id;
  final String? date;
  final String? startTime;
  final String? endTime;
  final int? staffId;
  final String? reason;

  VendorBeautyCalendarBlockModel({
    this.id,
    this.date,
    this.startTime,
    this.endTime,
    this.staffId,
    this.reason,
  });

  factory VendorBeautyCalendarBlockModel.fromJson(Map<String, dynamic> json) {
    return VendorBeautyCalendarBlockModel(
      id: json['id'],
      date: json['date'],
      startTime: json['start_time'],
      endTime: json['end_time'],
      staffId: json['staff_id'],
      reason: json['reason'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'date': date,
      'start_time': startTime,
      'end_time': endTime,
      'staff_id': staffId,
      'reason': reason,
    };
  }
}
