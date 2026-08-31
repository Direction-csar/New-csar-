import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';

class TicketScanScreen extends StatefulWidget {
  const TicketScanScreen({super.key});

  @override
  State<TicketScanScreen> createState() => _TicketScanScreenState();
}

class _TicketScanScreenState extends State<TicketScanScreen> {
  final _codeCtrl = TextEditingController();
  final MobileScannerController _scannerController = MobileScannerController();
  bool _processing = false;
  bool _scannerPaused = false;

  @override
  void dispose() {
    _codeCtrl.dispose();
    _scannerController.dispose();
    super.dispose();
  }

  Future<void> _onDetect(BarcodeCapture capture) async {
    if (_processing || _scannerPaused) return;
    final barcodes = capture.barcodes;
    if (barcodes.isEmpty) return;
    final code = barcodes.first.rawValue;
    if (code == null || code.isEmpty) return;
    setState(() => _scannerPaused = true);
    await _validate(code);
  }

  Future<void> _validate(String code) async {
    if (_processing) return;
    setState(() => _processing = true);
    final token = context.read<AuthService>().token;
    if (token == null) {
      setState(() => _processing = false);
      return;
    }

    try {
      final res = await ApiService.collectKit(token, code);
      if (!mounted) return;
      if (res['success'] == true) {
        _showResultDialog(
          success: true,
          title: 'Kit recupere!',
          beneficiaire: res['data']?['beneficiaire']?['full_name'] ?? res['data']?['beneficiaire'],
          quantite: res['data']?['beneficiaire']?['quantity_kg'] ?? res['data']?['quantite_kg'],
          ticketCode: res['data']?['ticket_code'] ?? code,
        );
      } else {
        _showResultDialog(success: false, title: res['message'] ?? 'Ticket invalide');
      }
    } catch (_) {
      if (!mounted) return;
      _showResultDialog(success: false, title: 'Erreur reseau. Verifiez votre connexion.');
    }
    setState(() => _processing = false);
  }

  void _showResultDialog({required bool success, required String title, String? beneficiaire, dynamic quantite, String? ticketCode}) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        icon: Icon(
          success ? Icons.check_circle : Icons.error_outline,
          color: success ? Colors.green : Colors.red,
          size: 48,
        ),
        title: Text(title, textAlign: TextAlign.center),
        content: success && beneficiaire != null
            ? Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (ticketCode != null) ...[
                    Text('Ticket: $ticketCode', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                    const SizedBox(height: 8),
                  ],
                  Text('Beneficiaire : $beneficiaire'),
                  if (quantite != null) Text('Quantite : $quantite kg'),
                ],
              )
            : null,
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              setState(() {
                _scannerPaused = false;
                _codeCtrl.clear();
              });
            },
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: const Color(0xFFD84315),
        title: const Text('Scanner un ticket'),
        actions: [
          IconButton(
            icon: const Icon(Icons.flash_on),
            onPressed: () => _scannerController.toggleTorch(),
          ),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: Stack(
              alignment: Alignment.center,
              children: [
                MobileScanner(
                  controller: _scannerController,
                  onDetect: _onDetect,
                ),
                Container(
                  width: 240,
                  height: 240,
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.white70, width: 2),
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
                if (_processing)
                  const CircularProgressIndicator(color: Colors.white),
              ],
            ),
          ),
          Container(
            color: Colors.white,
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _codeCtrl,
                    decoration: InputDecoration(
                      labelText: 'Ou saisir le code manuellement',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    onSubmitted: (v) {
                      if (v.trim().isNotEmpty) _validate(v.trim());
                    },
                  ),
                ),
                const SizedBox(width: 10),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFD84315),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                  ),
                  onPressed: _processing
                      ? null
                      : () {
                          if (_codeCtrl.text.trim().isNotEmpty) _validate(_codeCtrl.text.trim());
                        },
                  child: const Text('Valider'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
