import 'package:flutter/material.dart';
import '../models/stock_movement.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';

class SyncScreen extends StatefulWidget {
  const SyncScreen({super.key});

  @override
  State<SyncScreen> createState() => _SyncScreenState();
}

class _SyncScreenState extends State<SyncScreen> {
  List<StockMovement> _pending = [];
  bool _syncing = false;
  String _status = '';

  @override
  void initState() {
    super.initState();
    _loadPending();
  }

  Future<void> _loadPending() async {
    final list = await DatabaseService.getPendingSync();
    setState(() => _pending = list);
  }

  Future<void> _sync() async {
    if (_pending.isEmpty) return;

    final hasNet = await ApiService.hasConnection();
    if (!hasNet) {
      setState(() => _status = 'Pas de connexion internet');
      return;
    }

    setState(() {
      _syncing = true;
      _status = 'Synchronisation en cours...';
    });

    final movements = _pending.map((m) => {
      'warehouse_id': m.warehouseId,
      'movement_date': m.movementDate,
      'label': m.label,
      'position': m.position,
      'movement_type': m.movementType,
      'sacs': m.movementType == 'entry' ? m.entrySacs : m.exitSacs,
      'kg': m.movementType == 'entry' ? m.entryKg : m.exitKg,
      'observation': m.observation,
    }).toList();

    try {
      final result = await ApiService.syncMovements(movements);
      if (result['success'] == true) {
        for (final m in _pending) {
          if (m.id != null) await DatabaseService.markSynced(m.id!);
        }
        setState(() {
          _status = '${result['data']?['synced'] ?? movements.length} mouvements synchronisés';
        });
        await _loadPending();
      } else {
        setState(() => _status = 'Erreur : ${result['message']}');
      }
    } catch (e) {
      setState(() => _status = 'Erreur réseau : $e');
    }

    setState(() => _syncing = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Synchronisation')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: _pending.isEmpty ? Colors.green.shade50 : Colors.orange.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: _pending.isEmpty ? Colors.green : Colors.orange),
              ),
              child: Column(
                children: [
                  Icon(
                    _pending.isEmpty ? Icons.check_circle : Icons.pending_actions,
                    color: _pending.isEmpty ? Colors.green : Colors.orange,
                    size: 40,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    _pending.isEmpty ? 'Tout est synchronisé' : '${_pending.length} mouvement(s) en attente',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: _pending.isEmpty ? Colors.green : Colors.orange.shade800,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            if (_status.isNotEmpty)
              Text(_status, style: const TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),

            // Bouton sync
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _pending.isEmpty || _syncing ? null : _sync,
                icon: _syncing
                    ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Icon(Icons.sync),
                label: Text(_syncing ? 'SYNCHRONISATION...' : 'SYNCHRONISER MAINTENANT'),
              ),
            ),
            const SizedBox(height: 24),

            // Liste
            const Text('MOUVEMENTS EN ATTENTE', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.grey)),
            const SizedBox(height: 12),
            Expanded(
              child: _pending.isEmpty
                  ? const Center(child: Text('Rien à synchroniser'))
                  : ListView.builder(
                      itemCount: _pending.length,
                      itemBuilder: (ctx, i) {
                        final m = _pending[i];
                        return Card(
                          child: ListTile(
                            leading: Icon(
                              m.movementType == 'entry' ? Icons.arrow_downward : Icons.arrow_upward,
                              color: m.movementType == 'entry' ? Colors.green : Colors.red,
                            ),
                            title: Text(m.label),
                            subtitle: Text('${m.movementDate} | ${m.movementType == 'entry' ? '+' : '-'}${m.movementType == 'entry' ? m.entrySacs : m.exitSacs} sacs'),
                            trailing: const Chip(label: Text('OFFLINE'), backgroundColor: Colors.orange, labelStyle: TextStyle(color: Colors.white, fontSize: 10)),
                          ),
                        );
                      },
                    ),
            ),
          ],
        ),
      ),
    );
  }
}
