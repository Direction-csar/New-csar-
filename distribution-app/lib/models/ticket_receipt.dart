import 'package:intl/intl.dart';

/// Une ligne de la section « ARTICLES DISTRIBUÉS ».
class TicketReceiptItem {
  final String name;
  final String quantity;

  const TicketReceiptItem({required this.name, required this.quantity});
}

/// Données normalisées du reçu thermique, construites à partir du JSON
/// du ticket serveur. Les champs absents restent null : l'affichage les
/// masque ou affiche « Non renseigné » — jamais de fausses données.
class TicketReceipt {
  static const String orgLine1 = 'COMMISSARIAT À LA SÉCURITÉ';
  static const String orgLine2 = 'ALIMENTAIRE ET À LA RÉSILIENCE';
  static const String title = 'TICKET DE DISTRIBUTION';
  static const String copyLabel = 'COPIE CLIENT';
  static const String scanHint = 'SCANNEZ POUR VÉRIFIER LE TICKET';
  static const String keepHint =
      'Conservez ce ticket pour le suivi de votre distribution.';
  static const String footer = 'CSAR — Sécurité alimentaire et résilience';

  final String ticketCode;
  final String qrToken;
  final String? cardRef;
  final String? beneficiaryName;
  final String? phone;
  final String? householdSize;
  final String? site;
  final String? campaign;
  final String? servedBy;
  final String? couponId;
  final String? distributionType;
  final String? terminalId;
  final String? status;
  final DateTime? issuedAt;
  final DateTime? collectedAt;
  final List<TicketReceiptItem> items;

  const TicketReceipt({
    required this.ticketCode,
    required this.qrToken,
    this.cardRef,
    this.beneficiaryName,
    this.phone,
    this.householdSize,
    this.site,
    this.campaign,
    this.servedBy,
    this.couponId,
    this.distributionType,
    this.terminalId,
    this.status,
    this.issuedAt,
    this.collectedAt,
    this.items = const [],
  });

  static DateTime? _parseDate(dynamic v) {
    if (v == null) return null;
    return DateTime.tryParse(v.toString());
  }

  static String _fmtQty(dynamic qty, [String unit = 'kg']) {
    if (qty == null) return '—';
    final s = qty.toString();
    return s.toLowerCase().contains(unit) ? s : '$s $unit';
  }

  /// Extraction tolérante des articles réels : listes `items`, `articles`,
  /// `products` ou `kit` sur le bénéficiaire ou le ticket. Si aucune liste
  /// n'existe, une seule ligne réelle est produite à partir de la
  /// `category` et de `quantity_kg`.
  static List<TicketReceiptItem> _parseItems(Map? b, Map? t) {
    final sources = <dynamic>[
      b?['items'],
      b?['articles'],
      b?['products'],
      b?['kit'],
      t?['items'],
      t?['articles'],
      t?['products'],
    ];
    for (final src in sources) {
      if (src is List && src.isNotEmpty) {
        return src.whereType<Map>().map((it) {
          final name =
              (it['name'] ??
                      it['article'] ??
                      it['product'] ??
                      it['label'] ??
                      'Article')
                  .toString();
          final qty = it['quantity'] ?? it['quantity_kg'] ?? it['qty'];
          final unit = (it['unit'] ?? 'kg').toString();
          return TicketReceiptItem(name: name, quantity: _fmtQty(qty, unit));
        }).toList();
      }
    }
    final qty = b?['quantity_kg'];
    if (qty == null) return const [];
    final name = (b?['category'] ?? 'Kit alimentaire').toString();
    return [TicketReceiptItem(name: name, quantity: _fmtQty(qty))];
  }

  static Map<String, dynamic>? _asMap(dynamic v) =>
      v is Map ? Map<String, dynamic>.from(v) : null;

  factory TicketReceipt.fromTicket(dynamic ticket, {String? agentName}) {
    final t = _asMap(ticket);
    final b = _asMap(t?['beneficiary']);
    final p = _asMap(t?['planning']);
    final e = _asMap(p?['event']);
    final scanner = _asMap(t?['scanner']);

    final code = t?['ticket_code']?.toString() ?? 'N/A';
    final token = t?['qr_token']?.toString() ?? code;

    final cni = b?['cni']?.toString();
    final benId = b?['id'];
    final scannerName = scanner?['name']?.toString();

    return TicketReceipt(
      ticketCode: code,
      qrToken: token,
      cardRef: cni != null && cni.isNotEmpty
          ? cni
          : (benId != null ? 'BEN-$benId' : null),
      beneficiaryName: b?['full_name']?.toString(),
      phone: b?['phone']?.toString(),
      householdSize: b?['household_size']?.toString(),
      site: p?['location']?.toString() ?? b?['address']?.toString(),
      campaign: e?['name']?.toString() ?? p?['name']?.toString(),
      servedBy: scannerName ?? agentName,
      couponId: t?['id']?.toString(),
      distributionType: b?['category']?.toString(),
      terminalId: t?['scan_location']?.toString(),
      status: t?['status']?.toString(),
      issuedAt: _parseDate(t?['issued_at']),
      collectedAt: _parseDate(t?['collected_at']),
      items: _parseItems(b, t),
    );
  }

  String get transactionDate {
    final d = issuedAt ?? collectedAt;
    if (d == null) return 'Non renseigné';
    return DateFormat('dd/MM/yyyy HH:mm').format(d.toLocal());
  }

  String get datePart {
    final d = issuedAt ?? DateTime.now();
    return DateFormat('dd/MM/yyyy').format(d.toLocal());
  }

  String get timePart {
    final d = issuedAt ?? DateTime.now();
    return DateFormat('HH:mm:ss').format(d.toLocal());
  }
}
