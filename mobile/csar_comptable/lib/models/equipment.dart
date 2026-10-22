/// Solde carburant (produit × magasin) exposé par GET /fuel/summary.
class FuelBalance {
  final String produit;
  final int? warehouseId;
  final String? warehouseName;
  final double totalIn;
  final double totalOut;
  final double balance;

  FuelBalance({
    required this.produit,
    this.warehouseId,
    this.warehouseName,
    required this.totalIn,
    required this.totalOut,
    required this.balance,
  });

  static double _num(dynamic v) {
    if (v == null) return 0;
    if (v is num) return v.toDouble();
    return double.tryParse('$v') ?? 0;
  }

  factory FuelBalance.fromJson(Map<String, dynamic> json) {
    return FuelBalance(
      produit: json['produit'] ?? 'Carburant',
      warehouseId: json['warehouse_id'],
      warehouseName: json['warehouse_name'],
      totalIn: _num(json['total_in']),
      totalOut: _num(json['total_out']),
      balance: _num(json['balance']),
    );
  }
}

/// Affectation de matériel à un agent.
class EquipmentAssignment {
  final int id;
  final String produit;
  final String? unite;
  final double quantity;
  final String agentName;
  final String? numeroBon;
  final String status;
  final String? warehouseName;
  final String? assignerName;
  final DateTime? assignedAt;
  final DateTime? returnedAt;
  final String? returnNote;

  EquipmentAssignment({
    required this.id,
    required this.produit,
    this.unite,
    required this.quantity,
    required this.agentName,
    this.numeroBon,
    required this.status,
    this.warehouseName,
    this.assignerName,
    this.assignedAt,
    this.returnedAt,
    this.returnNote,
  });

  static double _num(dynamic v) {
    if (v == null) return 0;
    if (v is num) return v.toDouble();
    return double.tryParse('$v') ?? 0;
  }

  factory EquipmentAssignment.fromJson(Map<String, dynamic> json) {
    return EquipmentAssignment(
      id: json['id'],
      produit: json['produit'] ?? '',
      unite: json['unite'],
      quantity: _num(json['quantity']),
      agentName: json['agent_name'] ?? '',
      numeroBon: json['numero_bon'],
      status: json['status'] ?? 'affectee',
      warehouseName: json['warehouse'] is Map ? json['warehouse']['name'] : null,
      assignerName: json['assigner'] is Map ? json['assigner']['name'] : null,
      assignedAt:
          json['assigned_at'] != null ? DateTime.tryParse(json['assigned_at']) : null,
      returnedAt:
          json['returned_at'] != null ? DateTime.tryParse(json['returned_at']) : null,
      returnNote: json['return_note'],
    );
  }

  String get statusLabel {
    switch (status) {
      case 'retournee':
        return 'Retourné';
      case 'perdue':
        return 'Perdu';
      default:
        return 'Affecté';
    }
  }

  bool get isActive => status == 'affectee';
}
