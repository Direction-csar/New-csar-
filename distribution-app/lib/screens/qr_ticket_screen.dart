import 'dart:io';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:path_provider/path_provider.dart';
import 'package:provider/provider.dart';
import 'package:share_plus/share_plus.dart';

import '../models/ticket_receipt.dart';
import '../services/auth_service.dart';
import '../services/ticket_print_service.dart';
import '../widgets/thermal_ticket_view.dart';

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

  /// Impression / réimpression : envoie le même qr_token au PDF, ne
  /// touche à aucun statut métier (validation, collecte, etc.).
  Future<void> _printTicket(TicketReceipt receipt) async {
    final paper = await showDialog<double>(
      context: context,
      builder: (ctx) => SimpleDialog(
        title: const Text('Format papier thermique'),
        children: [
          SimpleDialogOption(
            onPressed: () => Navigator.pop(ctx, 58.0),
            child: const Text('58 mm'),
          ),
          SimpleDialogOption(
            onPressed: () => Navigator.pop(ctx, 80.0),
            child: const Text('80 mm'),
          ),
        ],
      ),
    );
    if (paper == null) return;
    try {
      await TicketPrintService.printTicket(receipt, paperMm: paper);
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(const SnackBar(content: Text('Impression impossible.')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final agentName = context.read<AuthService>().user?['name']?.toString();
    final receipt = TicketReceipt.fromTicket(
      widget.ticket,
      agentName: agentName,
    );

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
                child: ThermalTicketView(receipt: receipt),
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
              const SizedBox(height: 10),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () => _printTicket(receipt),
                  icon: const Icon(Icons.print),
                  label: const Text('Imprimer / Réimprimer'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: const Color(0xFFD84315),
                    side: const BorderSide(color: Color(0xFFD84315)),
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
}
