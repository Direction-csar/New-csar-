import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../models/warehouse.dart';
import 'stock_sheet_screen.dart';
import 'movement_form_screen.dart';
import 'sync_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  List<Warehouse> _warehouses = [];
  bool _loading = true;
  Map<String, dynamic> _stats = {};

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
        setState(() {
          _warehouses = wh;
          _stats = stats;
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

                    // Actions rapides
                    const Text('ACTIONS RAPIDES', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.grey)),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: _actionCard(
                            'Nouveau Mouvement',
                            Icons.add_circle,
                            Colors.green,
                            () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MovementFormScreen())),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _actionCard(
                            'Synchroniser',
                            Icons.sync,
                            Colors.blue,
                            () => Navigator.push(context, MaterialPageRoute(builder: (_) => const SyncScreen())),
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
