import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/movement.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import 'movement_detail_screen.dart';

/// Tableau de bord comptable : volumes par statut, alertes seuil
/// et derniers mouvements saisis par les magasiniers.
class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  DashboardData? _data;
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
      final data = await ApiService.getDashboard();
      if (!mounted) return;
      setState(() {
        _data = data;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString().replaceFirst('Exception: ', '');
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    return Scaffold(
      appBar: AppBar(
        title: const Text('Tableau de bord'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Déconnexion',
            onPressed: () => _confirmLogout(context),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading && _data == null
            ? const Center(child: CircularProgressIndicator())
            : _error != null && _data == null
                ? _errorView()
                : ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      Text('Bonjour ${user?['name'] ?? ''}',
                          style: const TextStyle(
                              fontSize: 18, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 4),
                      Text('Mouvements à traiter',
                          style: TextStyle(color: Colors.grey.shade600)),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          _statCard('En attente', _data?.draftCount ?? 0,
                              Colors.orange, Icons.pending_actions),
                          const SizedBox(width: 10),
                          _statCard('Validés', _data?.validatedCount ?? 0,
                              Colors.green, Icons.check_circle_outline),
                          const SizedBox(width: 10),
                          _statCard('Rejetés', _data?.rejectedCount ?? 0,
                              Colors.red, Icons.cancel_outlined),
                        ],
                      ),
                      const SizedBox(height: 20),
                      if ((_data?.lowStockWarehouses ?? []).isNotEmpty) ...[
                        const Text('Alertes stock bas',
                            style: TextStyle(
                                fontWeight: FontWeight.bold, fontSize: 15)),
                        const SizedBox(height: 8),
                        ..._data!.lowStockWarehouses.map((w) => Card(
                              color: Colors.orange.shade50,
                              child: ListTile(
                                dense: true,
                                leading: const Icon(Icons.warning_amber,
                                    color: Colors.orange),
                                title: Text(w['name'] ?? ''),
                                subtitle: Text(
                                    'Stock ${w['current_stock']} / ${w['capacity']} (${w['ratio']}%)'),
                              ),
                            )),
                        const SizedBox(height: 12),
                      ],
                      const Text('Derniers mouvements',
                          style: TextStyle(
                              fontWeight: FontWeight.bold, fontSize: 15)),
                      const SizedBox(height: 8),
                      if ((_data?.recentMovements ?? []).isEmpty)
                        Text('Aucun mouvement récent.',
                            style: TextStyle(color: Colors.grey.shade600)),
                      ..._data!.recentMovements.map((m) => Card(
                            child: ListTile(
                              leading: Icon(
                                m.isExit
                                    ? Icons.arrow_upward
                                    : Icons.arrow_downward,
                                color: m.isExit ? Colors.red : Colors.green,
                              ),
                              title: Text(m.reference ?? 'Sans référence'),
                              subtitle: Text(
                                  '${m.typeLabel} • ${m.warehouseName ?? ''} • ${m.quantity}'),
                              trailing: _statusChip(m),
                              onTap: () => Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) =>
                                      MovementDetailScreen(movementId: m.id),
                                ),
                              ).then((_) => _load()),
                            ),
                          )),
                    ],
                  ),
      ),
    );
  }

  Widget _statCard(String label, int count, Color color, IconData icon) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
          child: Column(
            children: [
              Icon(icon, color: color),
              const SizedBox(height: 6),
              Text('$count',
                  style: TextStyle(
                      fontSize: 22, fontWeight: FontWeight.bold, color: color)),
              Text(label,
                  style: const TextStyle(fontSize: 11),
                  textAlign: TextAlign.center),
            ],
          ),
        ),
      ),
    );
  }

  Widget _statusChip(Movement m) {
    final (label, color) = switch (m.status) {
      'validated' => ('Validé', Colors.green),
      'rejected' => ('Rejeté', Colors.red),
      _ => ('En attente', Colors.orange),
    };
    return Chip(
      label: Text(label, style: const TextStyle(fontSize: 11)),
      backgroundColor: color.withValues(alpha: 0.15),
      side: BorderSide.none,
      padding: EdgeInsets.zero,
    );
  }

  Widget _errorView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.cloud_off, size: 48, color: Colors.grey.shade400),
            const SizedBox(height: 12),
            Text(_error!, textAlign: TextAlign.center),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: _load,
              icon: const Icon(Icons.refresh),
              label: const Text('Réessayer'),
            ),
          ],
        ),
      ),
    );
  }

  void _confirmLogout(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Déconnexion'),
        content: const Text('Voulez-vous vous déconnecter ?'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx), child: const Text('Annuler')),
          FilledButton(
            onPressed: () {
              Navigator.pop(ctx);
              context.read<AuthProvider>().logout();
            },
            child: const Text('Déconnexion'),
          ),
        ],
      ),
    );
  }
}
