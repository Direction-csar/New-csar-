import 'dart:io';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:path_provider/path_provider.dart';
import 'package:printing/printing.dart' show Printer, Printing;
import 'package:provider/provider.dart';
import 'package:share_plus/share_plus.dart';

import '../models/ticket_receipt.dart';
import '../services/auth_service.dart';
import '../services/printing/thermal_printer_service.dart';
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
  bool _printing = false;
  bool _detecting = true;
  ThermalPrinterService? _printer;
  PrinterState _printerState = PrinterState.unknown;
  Printer? _selectedPrinter;
  String? _lastPrintError;

  @override
  void initState() {
    super.initState();
    _detectPrinter();
  }

  /// Détecte une imprimante thermique intégrée (SDK fabricant).
  /// Sans implémentation reconnue, l'écran garde le fallback PDF.
  Future<void> _detectPrinter() async {
    final service = await ThermalPrinterService.detect();
    if (!mounted) return;
    if (service == null) {
      setState(() => _detecting = false);
      return;
    }
    final state = await service.checkStatus();
    if (!mounted) return;
    setState(() {
      _printer = service;
      _printerState = state;
      _detecting = false;
    });
  }

  /// Impression / réimpression directe sur l'imprimante intégrée :
  /// même qr_token, aucun effet métier, anti-double-clic.
  Future<void> _printIntegrated(TicketReceipt receipt) async {
    final printer = _printer;
    if (printer == null || _printing) return;
    setState(() => _printing = true);
    try {
      final result = await printer.print(receipt);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            result.success
                ? 'Ticket imprimé.'
                : 'Échec impression : ${result.message}',
          ),
          backgroundColor: result.success ? null : Colors.red.shade700,
        ),
      );
    } finally {
      if (mounted) setState(() => _printing = false);
    }
  }

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

  /// Sélecteur natif d'imprimante (pilotes OS) — chemin de repli quand
  /// aucune imprimante intégrée n'est détectée sur le terminal.
  Future<void> _pickPrinter() async {
    final printer = await Printing.pickPrinter(context: context);
    if (!mounted) return;
    setState(() {
      _selectedPrinter = printer;
      _lastPrintError = null;
    });
    if (printer == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Aucune imprimante sélectionnée.')),
      );
    }
  }

  /// Impression/reprise sur l'imprimante choisie (résultat réel
  /// directPrintPdf) ou dialogue natif si aucune n'est sélectionnée.
  Future<void> _printFallback(TicketReceipt receipt) async {
    if (_printing) return;
    setState(() {
      _printing = true;
      _lastPrintError = null;
    });
    try {
      final printer = _selectedPrinter;
      if (printer != null) {
        final result = await TicketPrintService.printViaPrinter(
          printer,
          receipt,
        );
        if (!mounted) return;
        if (!result.success) {
          setState(() => _lastPrintError = result.message);
        }
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              result.success
                  ? 'Ticket imprimé.'
                  : 'Échec impression : ${result.message}',
            ),
            backgroundColor: result.success ? null : Colors.red.shade700,
          ),
        );
      } else {
        await _printTicket(receipt);
      }
    } finally {
      if (mounted) setState(() => _printing = false);
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
              _printerStatusChip(),
              const SizedBox(height: 10),
              if (_printer != null) ...[
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _printing
                        ? null
                        : () => _printIntegrated(receipt),
                    icon: _printing
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Icon(Icons.print),
                    label: Text(
                      _printing ? 'Impression...' : 'Imprimer le ticket',
                    ),
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
                    onPressed: _printing
                        ? null
                        : () => _printIntegrated(receipt),
                    icon: const Icon(Icons.replay),
                    label: const Text('Réimprimer le ticket'),
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
              ] else ...[
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: _printing ? null : _pickPrinter,
                    icon: const Icon(Icons.print_outlined),
                    label: Text(
                      _selectedPrinter == null
                          ? 'Choisir une imprimante'
                          : 'Changer d\'imprimante',
                    ),
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
                const SizedBox(height: 10),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _printing ? null : () => _printFallback(receipt),
                    icon: _printing
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Icon(Icons.print),
                    label: Text(
                      _printing ? 'Impression...' : 'Imprimer le ticket',
                    ),
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
                    onPressed: _printing ? null : () => _printFallback(receipt),
                    icon: const Icon(Icons.replay),
                    label: const Text('Réimprimer le ticket'),
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
            ],
          ),
        ),
      ),
    );
  }

  /// Pastille d'état de l'imprimante intégrée (état réel du SDK)
  /// ou indication du mode de repli PDF.
  Widget _printerStatusChip() {
    if (_detecting) {
      return const Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          SizedBox(
            width: 14,
            height: 14,
            child: CircularProgressIndicator(strokeWidth: 2),
          ),
          SizedBox(width: 8),
          Text(
            'Détection de l\'imprimante…',
            style: TextStyle(fontSize: 12, color: Colors.grey),
          ),
        ],
      );
    }
    final printer = _printer;
    if (printer == null) {
      // Repli : état de l'imprimante système choisie manuellement.
      final err = _lastPrintError;
      if (err != null) {
        return Text(
          'Imprimante : erreur — $err',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 12, color: Colors.red.shade700),
        );
      }
      final sel = _selectedPrinter;
      return Text(
        sel == null ? 'Imprimante : non connectée' : 'Imprimante : ${sel.name}',
        textAlign: TextAlign.center,
        style: const TextStyle(fontSize: 12, color: Colors.grey),
      );
    }
    final ok = _printerState == PrinterState.ready;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: ok ? Colors.green.shade50 : Colors.orange.shade50,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: ok ? Colors.green.shade300 : Colors.orange.shade300,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            ok ? Icons.check_circle : Icons.warning_amber,
            size: 16,
            color: ok ? Colors.green.shade700 : Colors.orange.shade800,
          ),
          const SizedBox(width: 6),
          Text(
            'Imprimante ${printer.vendor} : '
            '${ThermalPrinterService.stateLabel(_printerState)}',
            style: TextStyle(
              fontSize: 12,
              color: ok ? Colors.green.shade800 : Colors.orange.shade900,
            ),
          ),
        ],
      ),
    );
  }
}
