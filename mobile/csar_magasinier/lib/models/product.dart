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

  /// Produit liquide (huile, lait...) exprimé en litres.
  bool get isLiquid {
    final u = unit.trim().toLowerCase();
    return u == 'l' || u.startsWith('litre');
  }

  /// Libellé d'unité pour les formats : 'L' ou 'kg'.
  String get formatUnit => isLiquid ? 'L' : 'kg';

  /// Nom du contenant : 'Bidons' pour les liquides, 'Sacs' sinon.
  String get containerLabel => isLiquid ? 'Bidons' : 'Sacs';

  /// Formats disponibles : ceux du produit, sinon des valeurs par défaut
  /// adaptées à l'unité.
  List<int> get formats => formatsKg.isNotEmpty
      ? formatsKg
      : (isLiquid ? const [1, 5, 10, 20, 25] : const [5, 10, 25, 50, 100]);
}
