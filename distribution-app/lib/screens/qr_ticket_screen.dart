import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';

class QrTicketScreen extends StatelessWidget {
  final dynamic ticket;

  const QrTicketScreen({super.key, required this.ticket});

  @override
  Widget build(BuildContext context) {
    final ticketCode = ticket?['ticket_code'] as String? ?? 'N/A';
    final qrToken = ticket?['qr_token'] as String? ?? ticketCode;
    final beneficiary = ticket?['beneficiary'];
    final planning = ticket?['planning'];

    return Scaffold(
      backgroundColor: const Color(0xFFFBE9E7),
      appBar: AppBar(
        backgroundColor: const Color(0xFFD84315),
        title: const Text('Ticket QR'),
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 8, offset: Offset(0, 4))],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                  decoration: BoxDecoration(
                    color: const Color(0xFFD84315),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Text(
                    'CSAR - Ticket de distribution',
                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ),
                const SizedBox(height: 20),
                QrImageView(
                  data: qrToken,
                  version: QrVersions.auto,
                  size: 220,
                  backgroundColor: Colors.white,
                  eyeStyle: const QrEyeStyle(
                    eyeShape: QrEyeShape.square,
                    color: Color(0xFFD84315),
                  ),
                  dataModuleStyle: const QrDataModuleStyle(
                    dataModuleShape: QrDataModuleShape.square,
                    color: Colors.black87,
                  ),
                ),
                const SizedBox(height: 20),
                Text(
                  ticketCode,
                  style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Color(0xFFD84315)),
                ),
                const SizedBox(height: 16),
                const Divider(),
                const SizedBox(height: 12),
                if (beneficiary != null) ...[
                  _infoRow('Beneficiaire', beneficiary['full_name'] ?? 'N/A'),
                  _infoRow('Telephone', beneficiary['phone'] ?? '—'),
                  _infoRow('Quantite', '${beneficiary['quantity_kg'] ?? 0} kg'),
                ],
                if (planning != null) ...[
                  _infoRow('Planning', planning['name'] ?? '—'),
                ],
                const SizedBox(height: 16),
                const Text(
                  'Presentez ce QR code lors de la collecte du kit',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 11, color: Colors.grey),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
          Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}
