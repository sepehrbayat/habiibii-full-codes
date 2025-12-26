class VendorBeautySubscriptionModel {
  int? id;
  String? status;
  String? startDate;
  String? endDate;

  VendorBeautySubscriptionModel({
    this.id,
    this.status,
    this.startDate,
    this.endDate,
  });

  VendorBeautySubscriptionModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    status = json['status'];
    startDate = json['start_date'];
    endDate = json['end_date'];
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'status': status,
      'start_date': startDate,
      'end_date': endDate,
    };
  }
}
