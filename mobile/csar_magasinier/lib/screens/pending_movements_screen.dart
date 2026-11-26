import 'package:flutter/material.dart';
import '../services/api_service.dart';

/// Mouvements saisis par le magasinier encore « en_attente » de
/// vérification (entrées, sorties, transferts, inventaires — Web, APK
/// ou synchronisation hors ligne). Ne modifient pas le stock officiel.
/// Le magasinier peut ici confirmer la réception d'un transfert entrant.
class PendingMovementsScreen extends StatefulWidget {
  const PendingMovementsScreen({super.key});

  @override
  State<PendingMovementsScreen> createState() => _PendingMovementsScreenState();
}

class _PendingMovementsScreenState extends State<PendingMovementsScreen> {
  List<Map<String, dynamic>> _movements = [];
  bool _loading = true;
  String? _error;

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
      final list = await ApiService.getPendingMovements();
      if (!mounted) return;
      setState(() => _movements = list);
    } catch (e) {
      if (mounted) setState(() => _error = 'Impossible de charger les mouvements en attente.');
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _confirmReceipt(Map<String, dynamic> m) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Confirmer la réception'),
        content: Text(
          'Confirmez-vous avoir physiquement reçu le transfert ${m['reference']} ?',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Confirmer')),
        ],
      ),
    );
    if (ok != true) return;

    final res = await ApiService.confirmTransferReceipt(m['id'] as int);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(res['message'] as String? ?? 'Action effectuée')),
    );
    _load();
  }

  String _typeLabel(String type) => switch (type) {
        'entry' => 'Entrée',
        'exit' => 'Sortie',
        'in' => 'Entrée',
        'out' => 'Sortie',
        'transfer' => 'Transfert',
        'adjustment' => 'Ajustement',
        'report' => 'Report',
        _ => type,
      };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('En attente de vérification'),
        actions: [IconButton(icon: const Icon(Icons.refresh), onPressed: _load)],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(_error!),
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
            'Aucun mouvement en attente.\n'
            'Vos entrées, sorties et inventaires restent « en attente » '
            'jusqu\'à vérification par l\'inspecteur.',
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
        separatorBuilder: (ctx, i) => const SizedBox(height: 8),
        itemBuilder: (_, i) => _card(_movements[i]),
      ),
    );
  }

  Widget _card(Map<String, dynamic> m) {
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
              '${m['type_label'] ?? _typeLabel('${m['type']}')} — ${warehouse?['name'] ?? '—'}',
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
            if (isTransferIn) ...[
              const SizedBox(height: 6),
              Row(
                children: [
                  Icon(
                    receiptConfirmed ? Icons.check_circle : Icons.hourglass_top,
                    size: 16,
                    color: receiptConfirmed ? Colors.green : Colors.blueGrey,
                  ),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      receiptConfirmed
                          ? 'Réception confirmée — en attente de vérification.'
                          : 'Réception à confirmer : vérifiez la marchandise puis confirmez.',
                      style: TextStyle(
                        fontSize: 12,
                        color: receiptConfirmed ? Colors.green.shade800 : Colors.blueGrey.shade800,
                      ),
                    ),
                  ),
                ],
              ),
            ],
            if (isTransferIn && !receiptConfirmed) ...[
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                child: FilledButton.icon(
                  onPressed: () => _confirmReceipt(m),
                  icon: const Icon(Icons.move_to_inbox_outlined, size: 18),
                  label: const Text('Confirmer la réception'),
                  style: FilledButton.styleFrom(backgroundColor: Colors.green.shade700),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
