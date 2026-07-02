class Product {
  final int id;
  final String name;
  final String category;
  final String unit;
  final List<int> formatsKg;

  Product({
    required this.id,
    required this.name,
    required this.category,
    required this.unit,
    required this.formatsKg,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'],
      name: json['name'],
      category: json['category'] ?? '',
      unit: json['unit'] ?? 'kg',
      formatsKg: (json['formats_kg'] as List<dynamic>? ?? [])
          .map((e) => (e as num).toInt())
          .toList(),
    );
  }
}
