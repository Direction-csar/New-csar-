class Warehouse {
  final int id;
  final String name;
  final String code;
  final String? location;
  final double capacity;
  final double currentStock;
  final String status;

  Warehouse({
    required this.id,
    required this.name,
    required this.code,
    this.location,
    required this.capacity,
    required this.currentStock,
    required this.status,
  });

  factory Warehouse.fromJson(Map<String, dynamic> json) {
    return Warehouse(
      id: json['id'],
      name: json['name'],
      code: json['code'] ?? '',
      location: json['location'],
      capacity: (json['capacity'] ?? 0).toDouble(),
      currentStock: (json['current_stock'] ?? 0).toDouble(),
      status: json['status'] ?? 'active',
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'code': code,
        'location': location,
        'capacity': capacity,
        'current_stock': currentStock,
        'status': status,
      };
}
