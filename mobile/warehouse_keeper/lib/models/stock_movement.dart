class StockMovement {
  final int? id;
  final int warehouseId;
  final String movementDate;
  final String label;
  final String? position;
  final double entrySacs;
  final double entryKg;
  final double exitSacs;
  final double exitKg;
  final double balanceSacs;
  final double balanceKg;
  final String? observation;
  final String reference;
  final String movementType;
  final String status;
  final String? creatorName;
  final bool isSynced;

  StockMovement({
    this.id,
    required this.warehouseId,
    required this.movementDate,
    required this.label,
    this.position,
    this.entrySacs = 0,
    this.entryKg = 0,
    this.exitSacs = 0,
    this.exitKg = 0,
    this.balanceSacs = 0,
    this.balanceKg = 0,
    this.observation,
    required this.reference,
    required this.movementType,
    this.status = 'draft',
    this.creatorName,
    this.isSynced = false,
  });

  factory StockMovement.fromJson(Map<String, dynamic> json) {
    final meta = json['metadata'] != null
        ? (json['metadata'] is String ? Map<String, dynamic>.from(jsonDecode(json['metadata'])) : json['metadata'])
        : {};

    return StockMovement(
      id: json['id'],
      warehouseId: json['warehouse_id'],
      movementDate: meta['movement_date'] ?? json['created_at']?.toString().split('T')[0] ?? '',
      label: json['reason'] ?? json['label'] ?? '',
      position: meta['position'],
      entrySacs: (meta['entry_sacs'] ?? 0).toDouble(),
      entryKg: (meta['entry_kg'] ?? 0).toDouble(),
      exitSacs: (meta['exit_sacs'] ?? 0).toDouble(),
      exitKg: (meta['exit_kg'] ?? 0).toDouble(),
      balanceSacs: (meta['balance_sacs'] ?? json['quantity_after'] ?? 0).toDouble(),
      balanceKg: (meta['balance_kg'] ?? 0).toDouble(),
      observation: meta['observation'],
      reference: json['reference'] ?? '',
      movementType: json['type'] ?? 'entry',
      status: json['status'] ?? 'synced',
      creatorName: json['creator']?['name'],
      isSynced: true,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'warehouse_id': warehouseId,
        'movement_date': movementDate,
        'label': label,
        'position': position,
        'entry_sacs': entrySacs,
        'entry_kg': entryKg,
        'exit_sacs': exitSacs,
        'exit_kg': exitKg,
        'balance_sacs': balanceSacs,
        'balance_kg': balanceKg,
        'observation': observation,
        'reference': reference,
        'movement_type': movementType,
        'status': status,
      };

  Map<String, dynamic> toLocalDb() => {
        'warehouse_id': warehouseId,
        'movement_date': movementDate,
        'label': label,
        'position': position,
        'entry_sacs': entrySacs,
        'entry_kg': entryKg,
        'exit_sacs': exitSacs,
        'exit_kg': exitKg,
        'balance_sacs': balanceSacs,
        'balance_kg': balanceKg,
        'observation': observation,
        'reference': reference,
        'movement_type': movementType,
        'status': status,
        'is_synced': isSynced ? 1 : 0,
      };

  static StockMovement fromLocalDb(Map<String, dynamic> map) {
    return StockMovement(
      id: map['id'],
      warehouseId: map['warehouse_id'],
      movementDate: map['movement_date'],
      label: map['label'],
      position: map['position'],
      entrySacs: map['entry_sacs']?.toDouble() ?? 0,
      entryKg: map['entry_kg']?.toDouble() ?? 0,
      exitSacs: map['exit_sacs']?.toDouble() ?? 0,
      exitKg: map['exit_kg']?.toDouble() ?? 0,
      balanceSacs: map['balance_sacs']?.toDouble() ?? 0,
      balanceKg: map['balance_kg']?.toDouble() ?? 0,
      observation: map['observation'],
      reference: map['reference'],
      movementType: map['movement_type'],
      status: map['status'],
      isSynced: map['is_synced'] == 1,
    );
  }
}
