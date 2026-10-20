import 'dart:io';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:path_provider/path_provider.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'package:share_plus/share_plus.dart';

class QrTicketScreen extends StatefulWidget {
  final dynamic ticket;

  const QrTicketScreen({super.key, required this.ticket});

  @override
  State<QrTicketScreen> createState() => _QrTicketScreenState();
}

class _QrTicketScreenState extends State<QrTicketScreen> {
  final GlobalKey _ticketKey = GlobalKey();
  bool _sharing = false;

  Future<void> _shareTicket() async {
    if (_sharing) return;
    setState(() => _sharing = true);
    try {
      final boundary =
          _ticketKey.currentContext?.findRenderObject()
              as RenderRepaintBoundary?;
      if (boundary == null) return;

      final image = await boundary.toImage(pixelRatio: 3.0);
      final byteData = await image.toByteData(format: ui.ImageByteFormat.png);
      if (byteData == null) return;

      final dir = await getTemporaryDirectory();
      final code = widget.ticket?['ticket_code'] ?? 'csar';
      final file = File('${dir.path}/ticket_$code.png');
      await file.writeAsBytes(byteData.buffer.asUint8List());

      await SharePlus.instance.share(
        ShareParams(
          files: [XFile(file.path)],
          text: 'Ticket de distribution CSAR — $code',
        ),
      );
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Impossible de partager le ticket.')),
        );
      }
    } finally {
      if (mounted) setState(() => _sharing = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final ticketCode = widget.ticket?['ticket_code'] as String? ?? 'N/A';
    final qrToken = widget.ticket?['qr_token'] as String? ?? ticketCode;
    final beneficiary = widget.ticket?['beneficiary'];
    final planning = widget.ticket?['planning'];

    return Scaffold(
      backgroundColor: const Color(0xFFFBE9E7),
      appBar: AppBar(
        backgroundColor: const Color(0xFFD84315),
        title: const Text('Ticket QR'),
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              RepaintBoundary(
                key: _ticketKey,
                child: Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: const [
                      BoxShadow(
                        color: Colors.black12,
                        blurRadius: 8,
                        offset: Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Image.asset(
                        'assets/logo_distrubition.jpg',
                        height: 56,
                        fit: BoxFit.contain,
                      ),
                      const SizedBox(height: 12),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFD84315),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text(
                          'CSAR - Ticket de distribution',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                          ),
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
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFFD84315),
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Divider(),
                      const SizedBox(height: 12),
                      if (beneficiary != null) ...[
                        _infoRow(
                          'Beneficiaire',
                          beneficiary['full_name'] ?? 'N/A',
                        ),
                        _infoRow('Telephone', beneficiary['phone'] ?? '—'),
                        _infoRow(
                          'Quantite',
                          '${beneficiary['quantity_kg'] ?? 0} kg',
                        ),
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
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: _sharing ? null : _shareTicket,
                  icon: _sharing
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Icon(Icons.share),
                  label: Text(_sharing ? 'Partage...' : 'Partager le ticket'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFD84315),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
            ],
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
          Text(
            value,
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }
}
