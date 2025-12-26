import 'beauty_retail_product_model.dart';

class BeautyRetailOrderModel {
  int? id;
  String? orderNumber;
  int? userId;
  int? salonId;
  String? salonName;
  List<OrderItemModel>? items;
  double? subtotal;
  double? discount;
  double? tax;
  double? deliveryFee;
  double? total;
  String? status;
  String? paymentMethod;
  String? paymentStatus;
  Map<String, dynamic>? deliveryAddress;
  String? couponCode;
  String? notes;
  DateTime? estimatedDelivery;
  DateTime? deliveredAt;
  String? trackingNumber;
  String? cancelReason;
  DateTime? createdAt;
  DateTime? updatedAt;

  BeautyRetailOrderModel({
    this.id,
    this.orderNumber,
    this.userId,
    this.salonId,
    this.salonName,
    this.items,
    this.subtotal,
    this.discount,
    this.tax,
    this.deliveryFee,
    this.total,
    this.status,
    this.paymentMethod,
    this.paymentStatus,
    this.deliveryAddress,
    this.couponCode,
    this.notes,
    this.estimatedDelivery,
    this.deliveredAt,
    this.trackingNumber,
    this.cancelReason,
    this.createdAt,
    this.updatedAt,
  });

  BeautyRetailOrderModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    orderNumber = json['order_number'];
    userId = json['user_id'];
    salonId = json['salon_id'];
    salonName = json['salon_name'];
    if (json['items'] != null) {
      items = <OrderItemModel>[];
      json['items'].forEach((v) {
        items!.add(OrderItemModel.fromJson(v));
      });
    }
    subtotal = json['subtotal']?.toDouble();
    discount = json['discount']?.toDouble();
    tax = json['tax']?.toDouble();
    deliveryFee = json['delivery_fee']?.toDouble();
    total = json['total']?.toDouble();
    status = json['status'];
    paymentMethod = json['payment_method'];
    paymentStatus = json['payment_status'];
    deliveryAddress = json['delivery_address'];
    couponCode = json['coupon_code'];
    notes = json['notes'];
    estimatedDelivery = json['estimated_delivery'] != null 
        ? DateTime.parse(json['estimated_delivery']) 
        : null;
    deliveredAt = json['delivered_at'] != null 
        ? DateTime.parse(json['delivered_at']) 
        : null;
    trackingNumber = json['tracking_number'];
    cancelReason = json['cancel_reason'];
    createdAt = json['created_at'] != null 
        ? DateTime.parse(json['created_at']) 
        : null;
    updatedAt = json['updated_at'] != null 
        ? DateTime.parse(json['updated_at']) 
        : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['id'] = id;
    data['order_number'] = orderNumber;
    data['user_id'] = userId;
    data['salon_id'] = salonId;
    data['salon_name'] = salonName;
    if (items != null) {
      data['items'] = items!.map((v) => v.toJson()).toList();
    }
    data['subtotal'] = subtotal;
    data['discount'] = discount;
    data['tax'] = tax;
    data['delivery_fee'] = deliveryFee;
    data['total'] = total;
    data['status'] = status;
    data['payment_method'] = paymentMethod;
    data['payment_status'] = paymentStatus;
    data['delivery_address'] = deliveryAddress;
    data['coupon_code'] = couponCode;
    data['notes'] = notes;
    data['estimated_delivery'] = estimatedDelivery?.toIso8601String();
    data['delivered_at'] = deliveredAt?.toIso8601String();
    data['tracking_number'] = trackingNumber;
    data['cancel_reason'] = cancelReason;
    data['created_at'] = createdAt?.toIso8601String();
    data['updated_at'] = updatedAt?.toIso8601String();
    return data;
  }

  bool get canCancel => status == 'pending' || status == 'confirmed';
  bool get canTrack => status != 'cancelled' && status != 'delivered';
  bool get isDelivered => status == 'delivered';
  bool get isCancelled => status == 'cancelled';
  String get displayTotal => '\$${total?.toStringAsFixed(2) ?? '0.00'}';
  String get displaySubtotal => '\$${subtotal?.toStringAsFixed(2) ?? '0.00'}';
}

class OrderItemModel {
  int? id;
  int? productId;
  BeautyRetailProductModel? product;
  int? quantity;
  double? price;
  double? discount;
  double? total;
  String? notes;

  OrderItemModel({
    this.id,
    this.productId,
    this.product,
    this.quantity,
    this.price,
    this.discount,
    this.total,
    this.notes,
  });

  OrderItemModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    productId = json['product_id'];
    product = json['product'] != null 
        ? BeautyRetailProductModel.fromJson(json['product']) 
        : null;
    quantity = json['quantity'];
    price = json['price']?.toDouble();
    discount = json['discount']?.toDouble();
    total = json['total']?.toDouble();
    notes = json['notes'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['id'] = id;
    data['product_id'] = productId;
    if (product != null) {
      data['product'] = product!.toJson();
    }
    data['quantity'] = quantity;
    data['price'] = price;
    data['discount'] = discount;
    data['total'] = total;
    data['notes'] = notes;
    return data;
  }

  double get itemTotal => (price ?? 0) * (quantity ?? 0) - (discount ?? 0);
  String get displayPrice => '\$${price?.toStringAsFixed(2) ?? '0.00'}';
  String get displayTotal => '\$${total?.toStringAsFixed(2) ?? '0.00'}';
}
