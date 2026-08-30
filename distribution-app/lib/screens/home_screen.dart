import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';
import '../services/sync_service.dart';
import '../services/local_db_service.dart';
import 'beneficiaire_form_screen.dart';
import 'ticket_scan_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<dynamic> _plannings = [];
  bool _loading = true;
  bool _syncing = false;
  int _pendingCount = 0;
  StreamSubscription<ConnectivityResult>? _connectivitySub;

  @override
  void initState() {
    super.initState();
    _loadPlannings();
    _refreshPendingCount();
    _connectivitySub = Connectivity().onConnectivityChanged.listen((result) {
      if (result != ConnectivityResult.none) {
        _sync(silent: true);
      }
    });
  }

  @override
  void dispose() {
    _connectivitySub?.cancel();
    super.dispose();
  }

  Future<void> _refreshPendingCount() async {
    final count = await LocalDbService.countPendingBeneficiaires();
    if (mounted) setState(() => _pendingCount = count);
  }

  Future<void> _loadPlannings() async {
    setState(() => _loading = true);
    final token = context.read<AuthService>().token;
    if (token == null) return;
    try {
      final res = await ApiService.sync(token);
      if (res['success'] == true) {
        setState(() => _plannings = res['data'] ?? []);
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  Future<void> _sync({bool silent = false}) async {
    if (_syncing) return;
    final token = context.read<AuthService>().token;
    if (token == null) return;

    final connectivity = await Connectivity().checkConnectivity();
    if (connectivity == ConnectivityResult.none) {
      if (!silent && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Aucune connexion reseau'), backgroundColor: Colors.orange),
        );
      }
      return;
    }

    setState(() => _syncing = true);
    final result = await SyncService.syncPendingBeneficiaires(token);
    setState(() {
      _syncing = false;
      _pendingCount = result.remaining;
    });
    await _loadPlannings();

    if (!mounted) return;
    if (result.synced > 0 || !silent) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            result.synced > 0
                ? '${result.synced} beneficiaire(s) synchronise(s)${result.remaining > 0 ? ', ${result.remaining} restant(s)' : ''}'
                : (result.remaining > 0 ? '${result.remaining} en attente' : 'Aucun element a synchroniser'),
          ),
          backgroundColor: result.synced > 0 ? Colors.green : Colors.blueGrey,
        ),
      );
    }
  }

  int _beneficiaireCount(dynamic planning) {
    final list = planning['beneficiaires'] as List<dynamic>? ?? [];
    return list.length;
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthService>();

    return Scaffold(
      backgroundColor: const Color(0xFFFBE9E7),
      appBar: AppBar(
        backgroundColor: const Color(0xFFD84315),
        title: const Text('Distribution CSAR'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await auth.logout();
              if (context.mounted) Navigator.of(context).popUntil((r) => r.isFirst);
            },
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: const Color(0xFFD84315),
        icon: const Icon(Icons.qr_code_scanner),
        label: const Text('Scanner un ticket'),
        onPressed: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const TicketScanScreen()),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _loadPlannings,
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: Color(0xFFD84315)))
            : ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(colors: [Color(0xFFD84315), Color(0xFFEF6C00)]),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Bonjour, ${auth.user?['name'] ?? 'Agent'}',
                                style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${_plannings.length} planning(s) actif(s)',
                                style: const TextStyle(color: Colors.white70, fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                        Column(
                          children: [
                            IconButton(
                              icon: _syncing
                                  ? const SizedBox(
                                      width: 22,
                                      height: 22,
                                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                    )
                                  : const Icon(Icons.sync, color: Colors.white),
                              onPressed: _syncing ? null : () => _sync(),
                            ),
                            if (_pendingCount > 0)
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(20)),
                                child: Text('$_pendingCount en attente', style: const TextStyle(color: Colors.white, fontSize: 10)),
                              ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                  const Text(
                    'Plannings de distribution',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFFD84315)),
                  ),
                  const SizedBox(height: 12),
                  if (_plannings.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 40),
                      child: Center(child: Text('Aucun planning assigne', style: TextStyle(color: Colors.grey))),
                    )
                  else
                    ..._plannings.map((p) => _PlanningCard(
                          planning: p,
                          beneficiaireCount: _beneficiaireCount(p),
                          onRegister: () => Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => BeneficiaireFormScreen(planning: p)),
                          ).then((_) {
                            _refreshPendingCount();
                            _loadPlannings();
                          }),
                        )),
                  const SizedBox(height: 80),
                ],
              ),
      ),
    );
  }
}

class _PlanningCard extends StatelessWidget {
  final dynamic planning;
  final int beneficiaireCount;
  final VoidCallback onRegister;

  const _PlanningCard({required this.planning, required this.beneficiaireCount, required this.onRegister});

  @override
  Widget build(BuildContext context) {
    final campaign = planning['campaign'];
    final warehouse = planning['warehouse'];

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFD84315).withValues(alpha: 0.2)),
        boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            campaign?['name'] ?? planning['name'] ?? 'Planning #${planning['id']}',
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Colors.black87),
          ),
          if (warehouse != null) ...[
            const SizedBox(height: 4),
            Text('Entrepot : ${warehouse['name'] ?? ''}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
          ],
          const SizedBox(height: 8),
          Row(
            children: [
              Icon(Icons.people_outline, size: 16, color: Colors.grey.shade600),
              const SizedBox(width: 4),
              Text('$beneficiaireCount beneficiaire(s)', style: TextStyle(fontSize: 12, color: Colors.grey.shade700)),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFFD84315),
                side: const BorderSide(color: Color(0xFFD84315)),
              ),
              icon: const Icon(Icons.person_add_alt_1),
              label: const Text('Nouveau beneficiaire'),
              onPressed: onRegister,
            ),
          ),
        ],
      ),
    );
  }
}
