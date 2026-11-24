/// Opération de stock (circuit DG → DTL → inspecteur → magasinier).
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
  final Map<String, dynamic>? magasinier;
  final Map<String, dynamic>? executor;
  final Map<String, dynamic>? verifier;
  final String? productName;
  final String? productUnit;
  final String? format;
  final double? quantityRequested;
  final double? quantityExecuted;
  final double? quantityExecutedKg;
  final String? dgInstruction;
  final String? dtlInstruction;
  final String? inspectorComment;
  final String? observation;
  final String? anomalyReport;
  final String? rejectionReason;
  final List<dynamic>? attachments;
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
    this.magasinier,
    this.executor,
    this.verifier,
    this.productName,
    this.productUnit,
    this.format,
    this.quantityRequested,
    this.quantityExecuted,
    this.quantityExecutedKg,
    this.dgInstruction,
    this.dtlInstruction,
    this.inspectorComment,
    this.observation,
    this.anomalyReport,
    this.rejectionReason,
    this.attachments,
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
        magasinier: j['magasinier'] as Map<String, dynamic>?,
        executor: j['executor'] as Map<String, dynamic>?,
        verifier: j['verifier'] as Map<String, dynamic>?,
        productName: j['product_name'] as String?,
        productUnit: j['product_unit'] as String?,
        format: j['format'] as String?,
        quantityRequested: _toDouble(j['quantity_requested']),
        quantityExecuted: _toDouble(j['quantity_executed']),
        quantityExecutedKg: _toDouble(j['quantity_executed_kg']),
        dgInstruction: j['dg_instruction'] as String?,
        dtlInstruction: j['dtl_instruction'] as String?,
        inspectorComment: j['inspector_comment'] as String?,
        observation: j['observation'] as String?,
        anomalyReport: j['anomaly_report'] as String?,
        rejectionReason: j['rejection_reason'] as String?,
        attachments: j['attachments'] as List<dynamic>?,
        createdAt: j['created_at'] as String?,
        executedAt: j['executed_at'] as String?,
        verifiedAt: j['verified_at'] as String?,
      );

  static double? _toDouble(dynamic v) {
    if (v == null) return null;
    if (v is num) return v.toDouble();
    return double.tryParse('$v');
  }

  /// Libellé court du magasin concerné.
  String get warehouseName => (warehouse?['name'] as String?) ?? '—';

  /// L'inspecteur peut transmettre les consignes quand l'opération lui est affectée.
  bool get canTransmit => status == 'affectee_inspecteur';

  /// Vérification possible une fois l'opération exécutée par le magasinier.
  bool get canVerify => status == 'executee';

  /// Signalement d'anomalie possible tant que non clôturée.
  bool get canReportAnomaly =>
      ['affectee_inspecteur', 'transmise_magasinier', 'executee'].contains(status);

  /// Rejet possible avant clôture.
  bool get canReject =>
      ['soumise', 'affectee_inspecteur', 'transmise_magasinier', 'executee', 'anomalie']
          .contains(status);
}
