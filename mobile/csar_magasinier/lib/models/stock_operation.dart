/// Opération de stock transmise au magasinier par l'inspecteur.
/// Correspond au payload de GET /api/warehouse/v1/operations.
class StockOperation {
  final int id;
  final String reference;
  final String type;
  final String typeLabel;
  final String status;
  final String statusLabel;
  final Map<String, dynamic>? warehouse;
  final Map<String, dynamic>? destinationWarehouse;
  final Map<String, dynamic>? inspector;
  final String? produit;
  final String? unite;
  final String? format;
  final double? quantityRequested;
  final double? quantityRequestedKg;
  final double? quantityExecuted;
  final double? quantityExecutedKg;
  final String? instructions;
  final String? dtlComment;
  final String? inspectorComment;
  final String? anomalyReport;
  final String? rejectionReason;
  final String? createdAt;
  final String? executedAt;
  final String? verifiedAt;

  StockOperation({
    required this.id,
    required this.reference,
    required this.type,
    required this.typeLabel,
    required this.status,
    required this.statusLabel,
    this.warehouse,
    this.destinationWarehouse,
    this.inspector,
    this.produit,
    this.unite,
    this.format,
    this.quantityRequested,
    this.quantityRequestedKg,
    this.quantityExecuted,
    this.quantityExecutedKg,
    this.instructions,
    this.dtlComment,
    this.inspectorComment,
    this.anomalyReport,
    this.rejectionReason,
    this.createdAt,
    this.executedAt,
    this.verifiedAt,
  });

  factory StockOperation.fromJson(Map<String, dynamic> j) => StockOperation(
        id: j['id'] as int,
        reference: (j['reference'] ?? '') as String,
        type: (j['type'] ?? '') as String,
        typeLabel: (j['type_label'] ?? j['type'] ?? '') as String,
        status: (j['status'] ?? '') as String,
        statusLabel: (j['status_label'] ?? j['status'] ?? '') as String,
        warehouse: j['warehouse'] as Map<String, dynamic>?,
        destinationWarehouse: j['destination_warehouse'] as Map<String, dynamic>?,
        inspector: j['inspector'] as Map<String, dynamic>?,
        produit: j['produit'] as String?,
        unite: j['unite'] as String?,
        format: j['format'] as String?,
        quantityRequested: _toDouble(j['quantity_requested']),
        quantityRequestedKg: _toDouble(j['quantity_requested_kg']),
        quantityExecuted: _toDouble(j['quantity_executed']),
        quantityExecutedKg: _toDouble(j['quantity_executed_kg']),
        instructions: j['instructions'] as String?,
        dtlComment: j['dtl_comment'] as String?,
        inspectorComment: j['inspector_comment'] as String?,
        anomalyReport: j['anomaly_report'] as String?,
        rejectionReason: j['rejection_reason'] as String?,
        createdAt: j['created_at'] as String?,
        executedAt: j['executed_at'] as String?,
        verifiedAt: j['verified_at'] as String?,
      );

  static double? _toDouble(dynamic v) {
    if (v == null) return null;
    if (v is num) return v.toDouble();
    return double.tryParse('$v');
  }

  String get warehouseName => (warehouse?['name'] as String?) ?? '—';
  String? get destinationName => destinationWarehouse?['name'] as String?;
  String? get inspectorName => inspector?['name'] as String?;

  /// Le magasinier peut exécuter uniquement les opérations qui lui sont transmises.
  bool get canExecute => status == 'transmise_magasinier';

  bool get isFinal => ['verifiee', 'rejetee', 'annulee'].contains(status);
}
