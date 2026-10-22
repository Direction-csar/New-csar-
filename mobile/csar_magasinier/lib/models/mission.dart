/// Mission de sortie de stock affectée par le DTL au magasinier.
/// Correspond au payload de GET /api/warehouse/v1/missions.
class Mission {
  final int id;
  final String? trackingCode;
  final String? demandeur;
  final String? region;
  final String? instructions;
  final String status;
  final String? assignedAt;

  Mission({
    required this.id,
    this.trackingCode,
    this.demandeur,
    this.region,
    this.instructions,
    this.status = 'affectee',
    this.assignedAt,
  });

  factory Mission.fromJson(Map<String, dynamic> json) {
    return Mission(
      id: json['id'],
      trackingCode: json['tracking_code'],
      demandeur: json['demandeur'],
      region: json['region'],
      instructions: json['instructions'],
      status: json['status'] ?? 'affectee',
      assignedAt: json['assigned_at']?.toString(),
    );
  }

  bool get isOpen => status == 'affectee' || status == 'en_cours';
  bool get isDone => status == 'terminee';

  String get statusLabel => switch (status) {
        'affectee' => 'À exécuter',
        'en_cours' => 'En cours',
        'terminee' => 'Terminée',
        'annulee' => 'Annulée',
        _ => status,
      };
}
