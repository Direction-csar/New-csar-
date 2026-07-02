import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../models/warehouse.dart';
import 'stock_sheet_screen.dart';
import 'movement_form_screen.dart';
import 'sync_screen.dart';
import 'transfer_form_screen.dart';
import 'inventory_form_screen.dart';
import 'stock_status_screen.dart';
import 'receipt_screen.dart';
import 'movement_history_screen.dart';
import 'receipt_history_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  List<Warehouse> _warehouses = [];
  bool _loading = true;
  Map<String, dynamic> _stats = {};
  List<dynamic> _alerts = [];
  int _alertCount = 0;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    try {
      final hasNet = await ApiService.hasConnection();
      if (hasNet) {
        final wh = await ApiService.getWarehouses();
        final stats = await ApiService.getStats();
        final alerts = await ApiService.getAlerts();
        setState(() {
          _warehouses = wh;
          _stats = stats;
          _alerts = alerts['alerts'] ?? [];
          _alertCount = alerts['count'] ?? 0;
          _loading = false;
        });
      } else {
        setState(() => _loading = false);
      }
    } catch (e) {
      setState(() => _loading = false);
    }
  }

  void _logout() {
    context.read<AuthProvider>().logout();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Tableau de Bord'),
        actions: [
          if (_alertCount > 0)
            Badge(
              label: Text('$_alertCount'),
              child: IconButton(
                icon: const Icon(Icons.notifications),
                onPressed: _showAlerts,
              ),
            )
          else
            IconButton(
              icon: const Icon(Icons.notifications_outlined),
              onPressed: _showAlerts,
            ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: _logout,
            tooltip: 'Déconnexion',
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Stats cards
                    Row(
                      children: [
                        _statCard('Entrées auj.', '${_stats['entries_today_sacs'] ?? 0}', Icons.arrow_downward, Colors.green),
                        const SizedBox(width: 12),
                        _statCard('Sorties auj.', '${_stats['exits_today_sacs'] ?? 0}', Icons.arrow_upward, Colors.orange),
                        const SizedBox(width: 12),
                        _statCard('Total', '${_stats['total_movements'] ?? 0}', Icons.assignment, Colors.blue),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // Alertes
                    if (_alerts.isNotEmpty) ...[
                      _alertBanner(),
                      const SizedBox(height: 20),
                    ],

                    // Actions rapides
                    const Text('ACTIONS RAPIDES', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.grey)),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: _actionCard(
                            'Mouvement',
                            Icons.add_circle,
                            Colors.green,
                            () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MovementFormScreen())),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _actionCard(
                            'Transfert',
                            Icons.swap_horiz,
                            Colors.purple,
                            () => Navigator.push(context, MaterialPageRoute(builder: (_) => TransferFormScreen(warehouse: _warehouses.isNotEmpty ? _warehouses.first : null))),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: _actionCard(
                            'Inventaire',
                            Icons.fact_check,
                            Colors.orange,
                            () => Navigator.push(context, MaterialPageRoute(builder: (_) => InventoryFormScreen(warehouse: _warehouses.isNotEmpty ? _warehouses.first : null))),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _actionCard(
                            'État Stock',
                            Icons.bar_chart,
                            Colors.teal,
                            () => Navigator.push(context, MaterialPageRoute(builder: (_) => StockStatusScreen(warehouse: _warehouses.isNotEmpty ? _warehouses.first : null))),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: _actionCard(
                            'Historique',
                            Icons.history,
                            Colors.blue,
                            () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MovementHistoryScreen())),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _actionCard(
                            'Reçus',
                            Icons.receipt_long,
                            Colors.indigo,
                            () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ReceiptHistoryScreen())),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: _actionCard(
                            'Synchroniser',
                            Icons.sync,
                            Colors.cyan,
                            () => Navigator.push(context, MaterialPageRoute(builder: (_) => const SyncScreen())),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _actionCard(
                            'Recherche Reçu',
                            Icons.search,
                            Colors.grey,
                            () => _showReceiptDialog(),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // Magasins
                    const Text('MES MAGASINS', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.grey)),
                    const SizedBox(height: 12),
                    ..._warehouses.map((w) => _warehouseCard(w)),
                  ],
                ),
              ),
            ),
    );
  }

  void _showAlerts() {
    showModalBottomSheet(
      context: context,
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('ALERTES STOCK BAS', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            if (_alerts.isEmpty)
              const Center(child: Text('Aucune alerte')),
            ..._alerts.map((a) => ListTile(
              leading: Icon(
                a['severity'] == 'critical' ? Icons.error : Icons.warning,
                color: a['severity'] == 'critical' ? Colors.red : Colors.orange,
              ),
              title: Text(a['warehouse_name'] ?? ''),
              subtitle: Text('${a['region']} - ${a['current_stock']}/${a['capacity']} sacs (${a['percentage']}%)'),
            )),
          ],
        ),
      ),
    );
  }

  Widget _alertBanner() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.red.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.red.withOpacity(0.3)),
      ),
      child: Row(
        children: [
          const Icon(Icons.warning, color: Colors.red),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              '$_alertCount magasin(s) avec stock bas',
              style: const TextStyle(color: Colors.red, fontWeight: FontWeight.bold),
            ),
          ),
          TextButton(
            onPressed: _showAlerts,
            child: const Text('Voir', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
  }

  void _showReceiptDialog() {
    final ctrl = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Rechercher un reçu'),
        content: TextField(
          controller: ctrl,
          decoration: const InputDecoration(labelText: 'Référence (ex: STK-XXXX)'),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Annuler')),
          ElevatedButton(
            onPressed: () {
              if (ctrl.text.isNotEmpty) {
                Navigator.pop(ctx);
                Navigator.push(context, MaterialPageRoute(builder: (_) => ReceiptScreen(reference: ctrl.text.trim())));
              }
            },
            child: const Text('Voir'),
          ),
        ],
      ),
    );
  }

  Widget _statCard(String label, String value, IconData icon, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withOpacity(0.3)),
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 28),
            const SizedBox(height: 8),
            Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
            Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
          ],
        ),
      ),
    );
  }

  Widget _actionCard(String label, IconData icon, Color color, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 40),
            const SizedBox(height: 8),
            Text(label, textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.bold, color: color)),
          ],
        ),
      ),
    );
  }

  Widget _warehouseCard(Warehouse w) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ListTile(
        leading: const CircleAvatar(
          backgroundColor: Color(0xFF1B5E20),
          child: Icon(Icons.warehouse, color: Colors.white),
        ),
        title: Text(w.name, style: const TextStyle(fontWeight: FontWeight.bold)),
        subtitle: Text('Code: ${w.code} | Stock: ${w.currentStock.toStringAsFixed(0)} sacs'),
        trailing: const Icon(Icons.chevron_right),
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => StockSheetScreen(warehouse: w)),
        ),
      ),
    );
  }
}
