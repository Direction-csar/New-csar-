/// Mouvement de stock exposé par l'API comptable.
class Movement {
  final int id;
  final String? reference;
  final String type;
  final double quantity;
  final double? quantityBefore;
  final double? quantityAfter;
  final String? reason;
  final String status;
  final String? rejectionReason;
  final int? warehouseId;
  final String? warehouseName;
  final String? creatorName;
  final String? validatedByName;
  final DateTime? validatedAt;
  final int? requestMissionId;
  final DateTime? createdAt;

  Movement({
    required this.id,
    this.reference,
    required this.type,
    required this.quantity,
    this.quantityBefore,
    this.quantityAfter,
    this.reason,
    required this.status,
    this.rejectionReason,
    this.warehouseId,
    this.warehouseName,
    this.creatorName,
    this.validatedByName,
    this.validatedAt,
    this.requestMissionId,
    this.createdAt,
  });

  static double _num(dynamic v) {
    if (v == null) return 0;
    if (v is num) return v.toDouble();
    return double.tryParse('$v') ?? 0;
  }

  factory Movement.fromJson(Map<String, dynamic> json) {
    return Movement(
      id: json['id'],
      reference: json['reference'],
      type: json['type'] ?? 'unknown',
      quantity: _num(json['quantity']),
      quantityBefore: json['quantity_before'] != null ? _num(json['quantity_before']) : null,
      quantityAfter: json['quantity_after'] != null ? _num(json['quantity_after']) : null,
      reason: json['reason'],
      status: json['status'] ?? 'draft',
      rejectionReason: json['rejection_reason'],
      warehouseId: json['warehouse_id'],
      warehouseName: json['warehouse'] is Map ? json['warehouse']['name'] : null,
      creatorName: json['creator'] is Map ? json['creator']['name'] : null,
      validatedAt: json['validated_at'] != null ? DateTime.tryParse(json['validated_at']) : null,
      requestMissionId: json['request_mission_id'],
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
    );
  }

  String get typeLabel {
    switch (type) {
      case 'exit':
      case 'out':
        return 'Sortie';
      case 'entry':
      case 'in':
        return 'Entrée';
      case 'transfer':
        return 'Transfert';
      case 'adjustment':
        return 'Ajustement';
      default:
        return type;
    }
  }

  bool get isExit => ['exit', 'out'].contains(type);
  bool get isEntry => ['entry', 'in'].contains(type);

  String get statusLabel {
    switch (status) {
      case 'draft':
        return 'En attente';
      case 'validated':
        return 'Validé';
      case 'rejected':
        return 'Rejeté';
      default:
        return status;
    }
  }
}

/// Détail d'un mouvement : inclut la mission liée le cas échéant.
class MovementDetail {
  final Movement movement;
  final Map<String, dynamic>? mission;

  MovementDetail({required this.movement, this.mission});

  factory MovementDetail.fromJson(Map<String, dynamic> json) {
    return MovementDetail(
      movement: Movement.fromJson(json['movement']),
      mission: json['mission'] as Map<String, dynamic>?,
    );
  }
}

/// Données du tableau de bord comptable.
class DashboardData {
  final int draftCount;
  final int validatedCount;
  final int rejectedCount;
  final List<Map<String, dynamic>> lowStockWarehouses;
  final List<Movement> recentMovements;

  DashboardData({
    required this.draftCount,
    required this.validatedCount,
    required this.rejectedCount,
    required this.lowStockWarehouses,
    required this.recentMovements,
  });

  factory DashboardData.fromJson(Map<String, dynamic> json) {
    final stats = json['stats'] ?? {};
    return DashboardData(
      draftCount: stats['draft'] ?? 0,
      validatedCount: stats['validated'] ?? 0,
      rejectedCount: stats['rejected'] ?? 0,
      lowStockWarehouses:
          List<Map<String, dynamic>>.from(json['low_stock_warehouses'] ?? []),
      recentMovements: (json['recent_movements'] as List? ?? [])
          .map((e) => Movement.fromJson(e))
          .toList(),
    );
  }
}
