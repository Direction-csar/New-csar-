import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';

class ScanScreen extends StatefulWidget {
  const ScanScreen({super.key});

  @override
  State<ScanScreen> createState() => _ScanScreenState();
}

class _ScanScreenState extends State<ScanScreen> {
  final _codeCtrl = TextEditingController();
  final MobileScannerController _scannerController = MobileScannerController();
  bool _processing = false;
  bool _scannerPaused = false;
  int _scanCount = 0;
  int _successCount = 0;
  int _failCount = 0;
  List<String> _recentScans = [];

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
      final now = DateTime.now();
      final timeStr = '${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')}:${now.second.toString().padLeft(2, '0')}';

      if (res['success'] == true) {
        final beneficiary = res['data']?['beneficiaire']?['full_name'] ?? res['data']?['beneficiaire'] ?? 'N/A';
        final qty = res['data']?['beneficiaire']?['quantity_kg'] ?? res['data']?['quantite_kg'] ?? '?';
        setState(() {
          _scanCount++;
          _successCount++;
          _recentScans.insert(0, '[$timeStr] OK - $beneficiary ($qty kg)');
          if (_recentScans.length > 10) _recentScans.removeLast();
        });
        _showResultDialog(
          success: true,
          title: 'Kit recupere!',
          beneficiaire: beneficiary,
          quantite: qty,
          ticketCode: res['data']?['ticket_code'] ?? code,
        );
      } else {
        setState(() {
          _scanCount++;
          _failCount++;
          _recentScans.insert(0, '[$timeStr] ECHEC - ${res['message'] ?? 'Ticket invalide'}');
          if (_recentScans.length > 10) _recentScans.removeLast();
        });
        _showResultDialog(success: false, title: res['message'] ?? 'Ticket invalide');
      }
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _scanCount++;
        _failCount++;
        _recentScans.insert(0, '[ERREUR] Erreur reseau');
        if (_recentScans.length > 10) _recentScans.removeLast();
      });
      _showResultDialog(success: false, title: 'Erreur reseau. Verifiez votre connexion.');
    }
    setState(() => _processing = false);
  }

  void _showResultDialog({required bool success, required String title, String? beneficiaire, dynamic quantite, String? ticketCode}) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => AlertDialog(
        icon: Icon(
          success ? Icons.check_circle : Icons.error_outline,
          color: success ? Colors.green : Colors.red,
          size: 48,
        ),
        title: Text(title, textAlign: TextAlign.center, style: const TextStyle(fontSize: 18)),
        content: success && beneficiaire != null
            ? Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (ticketCode != null) ...[
                    Text('Ticket: $ticketCode', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                    const SizedBox(height: 8),
                  ],
                  Text('Beneficiaire : $beneficiaire', style: const TextStyle(fontWeight: FontWeight.w600)),
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
            child: const Text('Scanner suivant'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthService>();

    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: const Color(0xFF1565C0),
        title: const Text('CSAR Scanner'),
        actions: [
          IconButton(
            icon: const Icon(Icons.flash_on),
            onPressed: () => _scannerController.toggleTorch(),
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await auth.logout();
              if (context.mounted) Navigator.of(context).popUntil((r) => r.isFirst);
            },
          ),
        ],
      ),
      body: Column(
        children: [
          Container(
            color: const Color(0xFF1565C0),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _statBox('Scans', _scanCount, Colors.white),
                _statBox('OK', _successCount, Colors.greenAccent),
                _statBox('Echecs', _failCount, Colors.redAccent),
              ],
            ),
          ),
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
                Positioned(
                  bottom: 10,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.black54,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      'Scannez le QR code du ticket',
                      style: TextStyle(color: Colors.white, fontSize: 12),
                    ),
                  ),
                ),
              ],
            ),
          ),
          Container(
            color: Colors.white,
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _codeCtrl,
                        decoration: InputDecoration(
                          labelText: 'Saisir le code manuellement',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          prefixIcon: const Icon(Icons.keyboard),
                        ),
                        onSubmitted: (v) {
                          if (v.trim().isNotEmpty) _validate(v.trim());
                        },
                      ),
                    ),
                    const SizedBox(width: 10),
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF1565C0),
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
                if (_recentScans.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  const Align(
                    alignment: Alignment.centerLeft,
                    child: Text('Scans recents', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey)),
                  ),
                  const SizedBox(height: 4),
                  ..._recentScans.take(5).map((s) => Padding(
                    padding: const EdgeInsets.symmetric(vertical: 1),
                    child: Align(
                      alignment: Alignment.centerLeft,
                      child: Text(s, style: TextStyle(
                        fontSize: 11,
                        color: s.contains('OK') ? Colors.green : (s.contains('ECHEC') || s.contains('ERREUR') ? Colors.red : Colors.grey),
                      )),
                    ),
                  )),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _statBox(String label, int count, Color color) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text('$count', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
        Text(label, style: TextStyle(fontSize: 10, color: color.withOpacity(0.7))),
      ],
    );
  }
}
