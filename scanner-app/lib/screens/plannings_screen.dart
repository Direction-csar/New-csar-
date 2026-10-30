import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';
import 'scan_screen.dart';
import 'history_screen.dart';

/// Liste des plannings auxquels l'agent est affecte pour le controle (scanner).
/// En lecture seule hors ligne : la remise exige toujours le serveur.
class PlanningsScreen extends StatefulWidget {
  const PlanningsScreen({super.key});

  @override
  State<PlanningsScreen> createState() => _PlanningsScreenState();
}

class _PlanningsScreenState extends State<PlanningsScreen> {
  bool _loading = true;
  bool _offline = false;
  List<dynamic> _plannings = [];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final token = context.read<AuthService>().token;
    if (token == null) {
      if (mounted) setState(() => _loading = false);
      return;
    }
    try {
      final res = await ApiService.getScanPlannings(token);
      if (!mounted) return;
      if (res['success'] == true) {
        setState(() => _plannings = res['data'] ?? []);
      } else if (ApiService.isAuthError(res)) {
        await _sessionExpired();
        return;
      } else {
        setState(() => _offline = true);
      }
    } catch (_) {
      if (mounted) setState(() => _offline = true);
    }
    if (mounted) setState(() => _loading = false);
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

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthService>();
    final userName = auth.user?['name'] ?? '';

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('CSAR Scanner', style: TextStyle(fontSize: 18)),
            if (userName.isNotEmpty)
              Text('$userName — contrôle des remises', style: const TextStyle(fontSize: 11)),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.history),
            tooltip: 'Historique des remises',
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HistoryScreen())),
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () => auth.logout(),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _plannings.isEmpty
                ? ListView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    children: [
                      const SizedBox(height: 120),
                      Icon(Icons.event_busy, size: 64, color: Colors.grey.shade400),
                      const SizedBox(height: 16),
                      Center(
                        child: Text(
                          _offline
                              ? 'Impossible de joindre le serveur.\nVérifiez votre connexion et tirez pour actualiser.'
                              : 'Aucun planning ne vous est affecté.\nContactez l\'administration.',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.grey.shade600),
                        ),
                      ),
                    ],
                  )
                : ListView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(12),
                    children: [
                      if (_offline)
                        Container(
                          margin: const EdgeInsets.only(bottom: 8),
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(color: Colors.orange.shade50, borderRadius: BorderRadius.circular(8)),
                          child: const Text('Données en cache — connexion requise pour confirmer une remise.',
                              style: TextStyle(fontSize: 12, color: Colors.orange)),
                        ),
                      Text(
                        '${_plannings.length} planning(s) affecté(s) au contrôle',
                        style: const TextStyle(fontSize: 13, color: Colors.grey),
                      ),
                      const SizedBox(height: 8),
                      ..._plannings.map((p) => _planningCard(p)),
                    ],
                  ),
      ),
    );
  }

  Widget _planningCard(dynamic p) {
    final stats = p['stats'] ?? {};
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => ScanScreen(planning: Map<String, dynamic>.from(p))),
        ),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              const Icon(Icons.qr_code_scanner, color: Color(0xFF1565C0), size: 36),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(p['name'] ?? 'Planning', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                    if (p['event'] != null && p['event']['name'] != null)
                      Text('${p['event']['name']}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                    const SizedBox(height: 4),
                    Text(
                      '${stats['tickets'] ?? 0} tickets · ${stats['collected'] ?? 0} remis',
                      style: const TextStyle(fontSize: 12, color: Colors.blueGrey),
                    ),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right, color: Colors.grey),
            ],
          ),
        ),
      ),
    );
  }
}
