import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';
import 'history_screen.dart';

class ScanScreen extends StatefulWidget {
  /// Planning sur lequel l'agent controle les remises (null = accueil direct).
  final Map<String, dynamic>? planning;

  const ScanScreen({super.key, this.planning});

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
  bool _dashLoading = true;
  int _totalCollected = 0;
  int _todayCollected = 0;
  int _totalKg = 0;

  @override
  void dispose() {
    _codeCtrl.dispose();
    _scannerController.dispose();
    super.dispose();
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadDashboard());
  }

  Future<void> _loadDashboard() async {
    final token = context.read<AuthService>().token;
    if (token == null) return;
    try {
      final res = await ApiService.getScanHistory(token, planningId: widget.planning?['id']);
      if (res['success'] == true && mounted) {
        final List items = res['data'] ?? [];
        final today = DateTime.now();
        final todayStr = '${today.year}-${today.month.toString().padLeft(2, '0')}-${today.day.toString().padLeft(2, '0')}';
        int collected = 0;
        int todayCount = 0;
        int kg = 0;
        for (final item in items) {
          collected++;
          final scannedAt = item['scanned_at']?.toString() ?? '';
          if (scannedAt.startsWith(todayStr)) todayCount++;
          final qty = item['beneficiary']?['quantity_kg'] ?? item['quantity_kg'] ?? 0;
          kg += (qty is int) ? qty : int.tryParse(qty.toString()) ?? 0;
        }
        setState(() {
          _totalCollected = collected;
          _todayCollected = todayCount;
          _totalKg = kg;
          _dashLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _dashLoading = false);
    }
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

  /// Etape 1 : verification du ticket SANS effet de bord, puis apercu.
  /// Etape 2 : la confirmation de remise n'a lieu que sur action de l'agent.
  Future<void> _validate(String code) async {
    if (_processing) return;
    setState(() => _processing = true);
    final token = context.read<AuthService>().token;
    if (token == null) {
      setState(() => _processing = false);
      return;
    }

    try {
      final res = await ApiService.verifyTicket(token, code);
      if (!mounted) return;
      setState(() => _processing = false);

      if (ApiService.isAuthError(res)) {
        await _sessionExpired();
        return;
      }
      if (res['success'] == true) {
        _showConfirmDialog(code, res['data'] ?? {});
      } else {
        _countFailure('[$_time] ECHEC - ${res['message'] ?? 'Ticket invalide'}');
        _showResultDialog(success: false, title: res['message'] ?? 'Ticket invalide');
      }
    } catch (_) {
      if (!mounted) return;
      setState(() => _processing = false);
      _countFailure('[$_time] ERREUR - Erreur reseau');
      _showResultDialog(success: false, title: 'Connexion requise : impossible de verifier le ticket.');
    }
  }

  String get _time {
    final now = DateTime.now();
    return '${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')}:${now.second.toString().padLeft(2, '0')}';
  }

  void _countFailure(String entry) {
    setState(() {
      _scanCount++;
      _failCount++;
      _recentScans.insert(0, entry);
      if (_recentScans.length > 10) _recentScans.removeLast();
    });
  }

  /// Apercu du ticket avant confirmation : identite du beneficiaire, quantite, planning.
  void _showConfirmDialog(String code, Map<String, dynamic> data) {
    final b = data['beneficiary'] ?? {};
    final pl = data['planning'] ?? {};
    bool confirming = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogCtx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          icon: const Icon(Icons.verified_user, color: Color(0xFF1565C0), size: 48),
          title: const Text('Confirmer la remise ?', textAlign: TextAlign.center, style: TextStyle(fontSize: 18)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('Ticket : ${data['ticket_code'] ?? code}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
              const SizedBox(height: 10),
              Text('${b['full_name'] ?? 'N/A'}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              if (b['phone_masked'] != null) Text('Tel : ${b['phone_masked']}'),
              const SizedBox(height: 6),
              Text('${b['quantity_kg'] ?? '?'} kg — ${pl['name'] ?? ''}', style: const TextStyle(color: Colors.blueGrey)),
            ],
          ),
          actions: [
            TextButton(
              onPressed: confirming
                  ? null
                  : () {
                      Navigator.pop(dialogCtx);
                      setState(() {
                        _scannerPaused = false;
                        _codeCtrl.clear();
                      });
                    },
              child: const Text('Annuler'),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: Colors.green, foregroundColor: Colors.white),
              onPressed: confirming
                  ? null
                  : () {
                      setDialogState(() => confirming = true);
                      _confirmCollect(code).then((_) {
                        if (dialogCtx.mounted) Navigator.pop(dialogCtx);
                      });
                    },
              child: confirming
                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('Confirmer'),
            ),
          ],
        ),
      ),
    );
  }

  /// Etape 2 : remise effective (en ligne uniquement).
  Future<void> _confirmCollect(String code) async {
    setState(() => _processing = true);
    final token = context.read<AuthService>().token;
    if (token == null) {
      setState(() => _processing = false);
      return;
    }

    try {
      final res = await ApiService.collectKit(token, code);
      if (!mounted) return;

      if (ApiService.isAuthError(res)) {
        if (mounted) setState(() => _processing = false);
        await _sessionExpired();
        return;
      }
      if (res['success'] == true) {
        final beneficiary = res['data']?['beneficiaire']?['full_name'] ?? res['data']?['beneficiaire'] ?? 'N/A';
        final qtyRaw = res['data']?['beneficiaire']?['quantity_kg'] ?? res['data']?['quantite_kg'] ?? 0;
        final kg = int.tryParse(qtyRaw.toString()) ?? 0;
        // Mise a jour immediate des compteurs : pas de rechargement complet
        // du tableau de bord, qui ralentissait la confirmation.
        setState(() {
          _scanCount++;
          _successCount++;
          _totalCollected++;
          _todayCollected++;
          _totalKg += kg;
          _dashLoading = false;
          _recentScans.insert(0, '[$_time] OK - $beneficiary ($qtyRaw kg)');
          if (_recentScans.length > 10) _recentScans.removeLast();
        });
        _showResultDialog(
          success: true,
          title: 'Kit recupere!',
          beneficiaire: beneficiary,
          quantite: qtyRaw,
          ticketCode: res['data']?['ticket_code'] ?? code,
        );
      } else {
        _countFailure('[$_time] ECHEC - ${res['message'] ?? 'Ticket invalide'}');
        _showResultDialog(success: false, title: res['message'] ?? 'Ticket invalide');
      }
    } catch (_) {
      if (!mounted) return;
      _countFailure('[$_time] ERREUR - Erreur reseau');
      _showResultDialog(success: false, title: 'Connexion requise : la remise n\'a PAS ete enregistree.');
    }
    if (mounted) setState(() => _processing = false);
  }

  Future<void> _sessionExpired() async {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Session expirée ou compte désactivé : reconnectez-vous.'),
        backgroundColor: Colors.red,
      ),
    );
    await context.read<AuthService>().logout();
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
            icon: const Icon(Icons.history),
            tooltip: 'Historique des dons recuperes',
            onPressed: () {
              setState(() => _scannerPaused = true);
              Navigator.push(context, MaterialPageRoute(builder: (_) => const HistoryScreen()))
                  .then((_) => setState(() => _scannerPaused = false));
            },
          ),
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
          _buildDashboard(),
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

  Widget _buildDashboard() {
    return Container(
      color: const Color(0xFF0D47A1),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      child: _dashLoading
          ? const Center(child: SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)))
          : Row(
              children: [
                Expanded(child: _dashCard('Total collectes', '$_totalCollected', Icons.inventory, Colors.white)),
                const SizedBox(width: 8),
                Expanded(child: _dashCard("Aujourd'hui", '$_todayCollected', Icons.today, Colors.greenAccent)),
                const SizedBox(width: 8),
                Expanded(child: _dashCard('Quantite (kg)', '$_totalKg', Icons.scale, Colors.orangeAccent)),
              ],
            ),
    );
  }

  Widget _dashCard(String label, String value, IconData icon, Color color) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 18, color: color),
        const SizedBox(height: 2),
        Text(value, style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: color)),
        const SizedBox(height: 1),
        Text(label, style: const TextStyle(fontSize: 9, color: Colors.white70), textAlign: TextAlign.center),
      ],
    );
  }
}
