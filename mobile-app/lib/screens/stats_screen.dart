import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';

class StatsScreen extends StatefulWidget {
  const StatsScreen({super.key});

  @override
  State<StatsScreen> createState() => _StatsScreenState();
}

class _StatsScreenState extends State<StatsScreen> {
  bool _loading = true;
  Map<String, dynamic>? _stats;
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
    final token = context.read<AuthService>().token!;
    try {
      final res = await ApiService.getStats(token);
      if (res['success'] == true) {
        setState(() {
          _stats = res['data'];
          _loading = false;
        });
      } else {
        setState(() {
          _error = res['message'] ?? 'Erreur lors du chargement';
          _loading = false;
        });
      }
    } catch (_) {
      setState(() {
        _error = 'Erreur réseau';
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mes statistiques'), centerTitle: true),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!, style: const TextStyle(color: Colors.red)),
                      const SizedBox(height: 12),
                      ElevatedButton(onPressed: _load, child: const Text('Réessayer')),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      GridView.count(
                        crossAxisCount: 2,
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        crossAxisSpacing: 14,
                        mainAxisSpacing: 14,
                        children: [
                          _statCard('Aujourd\'hui', _stats?['today'], Icons.today, Colors.teal),
                          _statCard('Cette semaine', _stats?['this_week'], Icons.view_week, Colors.indigo),
                          _statCard('Ce mois', _stats?['this_month'], Icons.calendar_month, Colors.orange),
                          _statCard('Total', _stats?['total'], Icons.bar_chart, const Color(0xFF1B5E20)),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Card(
                        elevation: 2,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        child: ListTile(
                          leading: const Icon(Icons.sync_problem, color: Colors.red),
                          title: const Text('En attente de synchronisation'),
                          trailing: Text(
                            '${_stats?['pending_sync'] ?? 0}',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),
                      Card(
                        elevation: 2,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        child: ListTile(
                          leading: const Icon(Icons.history, color: Colors.grey),
                          title: const Text('Dernière synchronisation'),
                          subtitle: Text(_stats?['last_sync']?.toString() ?? 'Jamais'),
                        ),
                      ),
                    ],
                  ),
                ),
    );
  }

  Widget _statCard(String label, dynamic value, IconData icon, Color color) => Container(
    decoration: BoxDecoration(
      color: color.withOpacity(0.08),
      borderRadius: BorderRadius.circular(14),
      border: Border.all(color: color.withOpacity(0.25)),
    ),
    padding: const EdgeInsets.all(14),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(icon, color: color, size: 26),
        const SizedBox(height: 8),
        Text(
          '${value ?? 0}',
          style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: color),
        ),
        const SizedBox(height: 4),
        Text(label, style: const TextStyle(fontSize: 12, color: Colors.black54)),
      ],
    ),
  );
}
