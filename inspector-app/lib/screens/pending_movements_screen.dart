import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';

/// File de vérification des mouvements directs (hors circuit stock_operations) :
/// entrées, sorties, transferts et inventaires saisis par les magasiniers
/// via Web, APK ou synchronisation hors ligne. Rien ne compte au stock
/// officiel tant que le mouvement n'est pas vérifié ici.
class PendingMovementsScreen extends StatefulWidget {
  const PendingMovementsScreen({super.key});

  @override
  State<PendingMovementsScreen> createState() => _PendingMovementsScreenState();
}

class _PendingMovementsScreenState extends State<PendingMovementsScreen> {
  List<Map<String, dynamic>> _movements = [];
  bool _loading = false;
  String? _error;

  String? get _token => context.read<AuthService>().token;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await ApiService.getPendingMovements(_token!);
      if (!mounted) return;
      if (res['success'] == true) {
        setState(() {
          _movements = (res['movements'] as List? ?? [])
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
        });
      } else {
        setState(() => _error = res['message'] as String? ?? 'Erreur de chargement.');
      }
    } catch (_) {
      if (mounted) setState(() => _error = 'Erreur réseau.');
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _verify(Map<String, dynamic> m) async {
    final id = m['id'] as int;
    final res = await ApiService.verifyMovement(_token!, id);
    if (!mounted) return;
    _toast(res['success'] == true ? 'Mouvement vérifié — intégré au stock officiel.'
        : (res['message'] as String? ?? 'Vérification refusée.'));
    _load();
  }

  Future<void> _reject(Map<String, dynamic> m) async {
    final reason = await _askReason('Rejeter ${m['reference']}');
    if (reason == null || reason.trim().isEmpty) return;
    final res = await ApiService.rejectMovement(_token!, m['id'] as int, reason.trim());
    if (!mounted) return;
    _toast(res['success'] == true ? 'Mouvement rejeté.' : (res['message'] as String? ?? 'Rejet refusé.'));
    _load();
  }

  Future<void> _confirmReceipt(Map<String, dynamic> m) async {
    final res = await ApiService.confirmReceipt(_token!, m['id'] as int);
    if (!mounted) return;
    _toast(res['success'] == true ? 'Réception confirmée.' : (res['message'] as String? ?? 'Action refusée.'));
    _load();
  }

  void _toast(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  Future<String?> _askReason(String title) {
    final ctrl = TextEditingController();
    return showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(title, style: const TextStyle(fontSize: 16)),
        content: TextField(
          controller: ctrl,
          maxLines: 2,
          decoration: const InputDecoration(
            labelText: 'Motif du rejet *',
            border: OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Annuler')),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, ctrl.text),
            child: const Text('Rejeter'),
          ),
        ],
      ),
    );
  }

  bool _canVerify(Map<String, dynamic> m) {
    // Ajustements/inventaires : réservés DTL/DG (l'API tranchera aussi).
    return m['is_adjustment'] != true;
  }

  @override
  Widget build(BuildContext context) {
    // Intégré comme onglet de HomeScreen : pas de Scaffold/AppBar propre.
    return Column(
      children: [
        Material(
          color: Colors.orange.shade50,
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            child: Row(
              children: [
                Icon(Icons.info_outline, size: 16, color: Colors.orange.shade800),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    'Vérification obligatoire — rien ne compte au stock officiel tant que non vérifié.',
                    style: TextStyle(fontSize: 11, color: Colors.orange.shade900),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.refresh, size: 20),
                  onPressed: _load,
                  visualDensity: VisualDensity.compact,
                ),
              ],
            ),
          ),
        ),
        Expanded(child: _buildBody()),
      ],
    );
  }

  Widget _buildBody() {
    if (_loading && _movements.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(_error!, textAlign: TextAlign.center),
            const SizedBox(height: 12),
            FilledButton(onPressed: _load, child: const Text('Réessayer')),
          ],
        ),
      );
    }
    if (_movements.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24),
          child: Text(
            'Aucun mouvement en attente de vérification.\n'
            'Les entrées, sorties, transferts et inventaires saisis par les '
            'magasiniers apparaîtront ici.',
            textAlign: TextAlign.center,
          ),
        ),
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.all(12),
        itemCount: _movements.length,
        separatorBuilder: (_, _) => const SizedBox(height: 8),
        itemBuilder: (_, i) => _movementCard(_movements[i]),
      ),
    );
  }

  Widget _movementCard(Map<String, dynamic> m) {
    final isTransferIn = m['is_transfer_in'] == true;
    final receiptConfirmed = m['receipt_confirmed'] == true;
    final warehouse = m['warehouse'] as Map<String, dynamic>?;
    final creator = m['creator'] as Map<String, dynamic>?;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text('${m['reference']}',
                      style: const TextStyle(fontWeight: FontWeight.bold)),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.orange.shade50,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.orange.shade200),
                  ),
                  child: Text('En attente',
                      style: TextStyle(
                          fontSize: 12,
                          color: Colors.orange.shade800,
                          fontWeight: FontWeight.w600)),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              '${m['type_label'] ?? m['type']} — ${warehouse?['name'] ?? '—'}',
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
            const SizedBox(height: 4),
            Text(
              [
                'Quantité : ${m['quantity']}',
                if (creator?['name'] != null) 'Saisi par : ${creator!['name']}',
                if (m['created_at'] != null) '${m['created_at']}',
              ].join(' · '),
              style: const TextStyle(fontSize: 12, color: Colors.black54),
            ),
            if ((m['reason'] as String?)?.isNotEmpty == true) ...[
              const SizedBox(height: 4),
              Text(m['reason'] as String,
                  style: const TextStyle(fontSize: 12, color: Colors.black45),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis),
            ],
            if (isTransferIn && !receiptConfirmed) ...[
              const SizedBox(height: 6),
              Text('Réception non encore confirmée par le magasin destinataire.',
                  style: TextStyle(fontSize: 12, color: Colors.blue.shade800)),
            ],
            const SizedBox(height: 8),
            Row(
              children: [
                if (isTransferIn && !receiptConfirmed)
                  TextButton.icon(
                    onPressed: () => _confirmReceipt(m),
                    icon: const Icon(Icons.move_to_inbox_outlined, size: 18),
                    label: const Text('Confirmer réception'),
                  ),
                const Spacer(),
                if (_canVerify(m) && (!isTransferIn || receiptConfirmed)) ...[
                  OutlinedButton.icon(
                    onPressed: () => _reject(m),
                    icon: const Icon(Icons.close, size: 18),
                    label: const Text('Rejeter'),
                    style: OutlinedButton.styleFrom(foregroundColor: Colors.red.shade700),
                  ),
                  const SizedBox(width: 8),
                  FilledButton.icon(
                    onPressed: () => _verify(m),
                    icon: const Icon(Icons.check, size: 18),
                    label: const Text('Vérifier'),
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }
}
