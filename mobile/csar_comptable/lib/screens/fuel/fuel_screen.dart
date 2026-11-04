import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import 'fuel_entries_screen.dart';
import 'fuel_exits_screen.dart';
import 'fuel_reimbs_screen.dart';
import 'fuel_widgets.dart';

/// Hub « Carburant » : 4 onglets — soldes, entrées, sorties, remboursements.
class FuelScreen extends StatelessWidget {
  const FuelScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 4,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Carburant'),
          bottom: const TabBar(
            isScrollable: true,
            labelColor: Colors.white,
            unselectedLabelColor: Colors.white70,
            indicatorColor: Colors.white,
            tabs: [
              Tab(icon: Icon(Icons.local_gas_station), text: 'Soldes'),
              Tab(icon: Icon(Icons.south), text: 'Entrées'),
              Tab(icon: Icon(Icons.north), text: 'Sorties'),
              Tab(icon: Icon(Icons.payments_outlined), text: 'Rembours.'),
            ],
          ),
        ),
        body: const TabBarView(
          children: [
            FuelStockTab(),
            FuelEntriesScreen(),
            FuelExitsScreen(),
            FuelReimbsScreen(),
          ],
        ),
      ),
    );
  }
}

/// Onglet 1 — soldes par produit/magasin + dossiers en attente.
class FuelStockTab extends StatefulWidget {
  const FuelStockTab({super.key});

  @override
  State<FuelStockTab> createState() => _FuelStockTabState();
}

class _FuelStockTabState extends State<FuelStockTab> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = ApiService.getFuelDashboard();
  }

  Future<void> _refresh() async {
    setState(() => _future = ApiService.getFuelDashboard());
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _refresh,
      child: FutureBuilder<Map<String, dynamic>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return FuelErrorView(
              error: snap.error!,
              onRetry: _refresh,
            );
          }
          final data = snap.data!;
          final stock = (data['stock'] as List? ?? []);
          final stockValue = (data['stock_value'] as List? ?? []);
          final pending = (data['pending'] as Map? ?? {});

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _PendingCard(pending: pending),
              const SizedBox(height: 16),
              const Text('Soldes par carburant',
                  style:
                      TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
              const SizedBox(height: 8),
              if (stock.isEmpty)
                const Card(
                  child: Padding(
                    padding: EdgeInsets.all(16),
                    child: Text('Aucun stock carburant enregistré.'),
                  ),
                )
              else
                ...stock.map((s) => Card(
                      child: ListTile(
                        leading: const Icon(Icons.local_gas_station,
                            color: Color(0xFF0D47A1)),
                        title: Text('${s['produit'] ?? 'Carburant'}'),
                        subtitle: Text('${s['warehouse'] ?? '—'}'),
                        trailing: Text(
                          fuelLiters(s['balance']),
                          style: const TextStyle(
                              fontWeight: FontWeight.w700, fontSize: 16),
                        ),
                      ),
                    )),
              if (stockValue.isNotEmpty) ...[
                const SizedBox(height: 16),
                const Text('Valorisation du stock',
                    style:
                        TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
                const SizedBox(height: 8),
                ...stockValue.map((v) => Card(
                      child: ListTile(
                        dense: true,
                        title: Text('${v['produit'] ?? ''}'),
                        subtitle: Text(
                            '${fuelLiters(v['balance'])} × ${fuelAmount(v['avg_unit_cost'])}/L'),
                        trailing: Text(
                          fuelAmount(v['stock_value']),
                          style:
                              const TextStyle(fontWeight: FontWeight.w700),
                        ),
                      ),
                    )),
              ],
              const SizedBox(height: 24),
            ],
          );
        },
      ),
    );
  }
}

class _PendingCard extends StatelessWidget {
  final Map pending;
  const _PendingCard({required this.pending});

  @override
  Widget build(BuildContext context) {
    int n(String k) => (pending[k] as num?)?.toInt() ?? 0;
    final items = [
      (n('entries'), 'entrées à valider'),
      (n('exits_draft'), 'sorties à autoriser'),
      (n('exits_authorized'), 'sorties à livrer'),
      (n('reimbursements'), 'remboursements à instruire'),
    ];
    final total = items.fold<int>(0, (s, e) => s + e.$1);

    return Card(
      color: const Color(0xFF0D47A1),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('$total dossier(s) en attente',
                style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                    fontSize: 16)),
            const SizedBox(height: 8),
            ...items.map((e) => Padding(
                  padding: const EdgeInsets.symmetric(vertical: 1),
                  child: Text('${e.$1} ${e.$2}',
                      style: const TextStyle(color: Colors.white70)),
                )),
          ],
        ),
      ),
    );
  }
}

