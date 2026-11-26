/// Magasin exposé par l'API comptable (filtres + alertes seuil).
class Warehouse {
  final int id;
  final String name;
  final String? region;
  final double? capacity;
  final double? currentStock;

  Warehouse({
    required this.id,
    required this.name,
    this.region,
    this.capacity,
    this.currentStock,
  });

  static double? _num(dynamic v) {
    if (v == null) return null;
    if (v is num) return v.toDouble();
    return double.tryParse('$v');
  }

  factory Warehouse.fromJson(Map<String, dynamic> json) {
    return Warehouse(
      id: json['id'],
      name: json['name'] ?? '',
      region: json['region'],
      capacity: _num(json['capacity']),
      currentStock: _num(json['current_stock']),
    );
  }

  /// Ratio de remplissage (0..1) si la capacité est connue.
  double? get fillRatio {
    if (capacity == null || capacity! <= 0 || currentStock == null) return null;
    return currentStock! / capacity!;
  }
}
