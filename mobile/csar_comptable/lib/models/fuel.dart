/// Modèles du module carburant — miroir des ressources API
/// /api/comptable/v1/fuel/* (FuelEntry, FuelExit, FuelReimbursement).
library;

class FuelEntry {
  final int id;
  final String reference;
  final String status;
  final String? operationDate;
  final int? warehouseId;
  final String? warehouseName;
  final String supplier;
  final String fuelType;
  final String? fuelTypeLabel;
  final double quantity;
  final double unitPrice;
  final double totalAmount;
  final String? invoiceNumber;
  final String? rejectionReason;
  final List<FuelAttachment> attachments;

  const FuelEntry({
    required this.id,
    required this.reference,
    required this.status,
    required this.supplier,
    required this.fuelType,
    required this.quantity,
    required this.unitPrice,
    required this.totalAmount,
    this.operationDate,
    this.warehouseId,
    this.warehouseName,
    this.fuelTypeLabel,
    this.invoiceNumber,
    this.rejectionReason,
    this.attachments = const [],
  });

  String get fuelLabel =>
      fuelType == 'autre' ? (fuelTypeLabel ?? 'Autre') : fuelType[0].toUpperCase() + fuelType.substring(1);

  bool get isDraft => status == 'draft';

  factory FuelEntry.fromJson(Map<String, dynamic> j) => FuelEntry(
        id: j['id'] as int,
        reference: j['reference'] ?? '',
        status: j['status'] ?? 'draft',
        operationDate: j['operation_date'],
        warehouseId: j['warehouse_id'],
        warehouseName: j['warehouse']?['name'],
        supplier: j['supplier'] ?? '',
        fuelType: j['fuel_type'] ?? '',
        fuelTypeLabel: j['fuel_type_label'],
        quantity: _num(j['quantity']),
        unitPrice: _num(j['unit_price']),
        totalAmount: _num(j['total_amount']),
        invoiceNumber: j['invoice_number'],
        rejectionReason: j['rejection_reason'],
        attachments: _attachments(j['attachments']),
      );
}

class FuelExit {
  final int id;
  final String exitNumber;
  final String status;
  final String? operationDate;
  final int? warehouseId;
  final String? warehouseName;
  final String beneficiaryName;
  final String? vehiclePlate;
  final String fuelType;
  final String? fuelTypeLabel;
  final String purpose;
  final String? destination;
  final String? missionNumber;
  final double quantityRequested;
  final double? quantityAuthorized;
  final double? quantityDelivered;
  final String? rejectionReason;
  final List<FuelAttachment> attachments;

  const FuelExit({
    required this.id,
    required this.exitNumber,
    required this.status,
    required this.beneficiaryName,
    required this.fuelType,
    required this.purpose,
    required this.quantityRequested,
    this.operationDate,
    this.warehouseId,
    this.warehouseName,
    this.vehiclePlate,
    this.fuelTypeLabel,
    this.destination,
    this.missionNumber,
    this.quantityAuthorized,
    this.quantityDelivered,
    this.rejectionReason,
    this.attachments = const [],
  });

  String get fuelLabel =>
      fuelType == 'autre' ? (fuelTypeLabel ?? 'Autre') : fuelType[0].toUpperCase() + fuelType.substring(1);

  factory FuelExit.fromJson(Map<String, dynamic> j) => FuelExit(
        id: j['id'] as int,
        exitNumber: j['exit_number'] ?? '',
        status: j['status'] ?? 'draft',
        operationDate: j['operation_date'],
        warehouseId: j['warehouse_id'],
        warehouseName: j['warehouse']?['name'],
        beneficiaryName: j['beneficiary_name'] ?? '',
        vehiclePlate: j['vehicle_plate'],
        fuelType: j['fuel_type'] ?? '',
        fuelTypeLabel: j['fuel_type_label'],
        purpose: j['purpose'] ?? '',
        destination: j['destination'],
        missionNumber: j['mission_number'],
        quantityRequested: _num(j['quantity_requested']),
        quantityAuthorized: j['quantity_authorized'] == null ? null : _num(j['quantity_authorized']),
        quantityDelivered: j['quantity_delivered'] == null ? null : _num(j['quantity_delivered']),
        rejectionReason: j['rejection_reason'],
        attachments: _attachments(j['attachments']),
      );
}

class FuelReimbursement {
  final int id;
  final String reference;
  final String status;
  final String agentName;
  final String? service;
  final String? expenseDate;
  final String purpose;
  final String? receiptRef;
  final String? duplicateOfRef;
  final double amountClaimed;
  final double amountRequested;
  final double? amountApproved;
  final double? amountPaid;
  final double? advanceAmount;
  final double? justifiedAmount;
  final double? complementAmount;
  final String? rejectionReason;
  final List<FuelAttachment> attachments;

  const FuelReimbursement({
    required this.id,
    required this.reference,
    required this.status,
    required this.agentName,
    required this.purpose,
    required this.amountClaimed,
    required this.amountRequested,
    this.service,
    this.expenseDate,
    this.receiptRef,
    this.duplicateOfRef,
    this.amountApproved,
    this.amountPaid,
    this.advanceAmount,
    this.justifiedAmount,
    this.complementAmount,
    this.rejectionReason,
    this.attachments = const [],
  });

  bool get hasDuplicate => duplicateOfRef != null;

  factory FuelReimbursement.fromJson(Map<String, dynamic> j) => FuelReimbursement(
        id: j['id'] as int,
        reference: j['reference'] ?? '',
        status: j['status'] ?? 'draft',
        agentName: j['agent_name'] ?? '',
        service: j['service'],
        expenseDate: j['expense_date'],
        purpose: j['purpose'] ?? '',
        receiptRef: j['receipt_ref'],
        duplicateOfRef: j['duplicate_of_ref'],
        amountClaimed: _num(j['amount_claimed']),
        amountRequested: _num(j['amount_requested']),
        amountApproved: j['amount_approved'] == null ? null : _num(j['amount_approved']),
        amountPaid: j['amount_paid'] == null ? null : _num(j['amount_paid']),
        advanceAmount: j['advance_amount'] == null ? null : _num(j['advance_amount']),
        justifiedAmount: j['justified_amount'] == null ? null : _num(j['justified_amount']),
        complementAmount: j['complement_amount'] == null ? null : _num(j['complement_amount']),
        rejectionReason: j['rejection_reason'],
        attachments: _attachments(j['attachments']),
      );
}

class FuelAttachment {
  final int id;
  final String originalName;
  final String kind;
  final String? mimeType;
  final int sizeBytes;

  const FuelAttachment({
    required this.id,
    required this.originalName,
    required this.kind,
    this.mimeType,
    this.sizeBytes = 0,
  });

  factory FuelAttachment.fromJson(Map<String, dynamic> j) => FuelAttachment(
        id: j['id'] as int,
        originalName: j['original_name'] ?? 'pièce',
        kind: j['kind'] ?? 'justificatif',
        mimeType: j['mime_type'],
        sizeBytes: j['size_bytes'] ?? 0,
      );
}

/// Libellés français des statuts (partagés par les 3 flux).
const Map<String, String> fuelStatusLabels = {
  'draft': 'Brouillon',
  'validated': 'Validée',
  'authorized': 'Autorisée',
  'rejected': 'Rejetée',
  'submitted': 'Soumise',
  'in_review': 'En instruction',
  'approved': 'Approuvée',
  'paid': 'Payée',
  'closed': 'Clôturée',
};

String fuelStatusLabel(String? status) => fuelStatusLabels[status] ?? (status ?? '—');

double _num(dynamic v) {
  if (v == null) return 0;
  if (v is num) return v.toDouble();
  return double.tryParse('$v') ?? 0;
}

List<FuelAttachment> _attachments(dynamic raw) =>
    (raw as List? ?? []).map((e) => FuelAttachment.fromJson(e)).toList();
