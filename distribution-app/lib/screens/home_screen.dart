import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';
import '../services/sync_service.dart';
import '../services/local_db_service.dart';
import 'beneficiaire_form_screen.dart';
import 'planning_beneficiaries_screen.dart';
import 'tickets_history_screen.dart';

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
  String _search = '';
  StreamSubscription<ConnectivityResult>? _connectivitySub;
  Timer? _pollTimer;
  String? _lastCheck;
  int _newCollections = 0;

  Future<void> _pollCollections() async {
    final token = context.read<AuthService>().token;
    if (token == null) return;
    try {
      final res = await ApiService.getTicketsHistory(token, collectedSince: _lastCheck);
      if (res['success'] != true || !mounted) return;
      final serverTime = res['server_time']?.toString();
      if (_lastCheck != null) {
        final List items = res['data'] ?? [];
        if (items.isNotEmpty) {
          setState(() => _newCollections += items.length);
          final first = items.first;
          final name = first['beneficiary']?['full_name'] ?? 'Un beneficiaire';
          final more = items.length > 1 ? ' (+${items.length - 1} autre(s))' : '';
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Don recupere : $name$more'),
              backgroundColor: Colors.green.shade700,
              duration: const Duration(seconds: 5),
              action: SnackBarAction(
                label: 'Voir',
                textColor: Colors.white,
                onPressed: _openHistory,
              ),
            ),
          );
          _loadPlannings();
        }
      }
      if (serverTime != null) _lastCheck = serverTime;
    } catch (_) {}
  }

  void _openHistory() {
    setState(() => _newCollections = 0);
    Navigator.push(context, MaterialPageRoute(builder: (_) => const TicketsHistoryScreen()))
        .then((_) => _loadPlannings());
  }

  Map<String, List<dynamic>> get _groupedByEvent {
    final q = _search.trim().toLowerCase();
    final Map<String, List<dynamic>> groups = {};
    for (final p in _plannings) {
      final eventName = (p['event']?['name'] ?? 'Sans evenement').toString();
      final haystack = '${eventName} ${p['name'] ?? ''} ${p['location'] ?? ''}'.toLowerCase();
      if (q.isNotEmpty && !haystack.contains(q)) continue;
      groups.putIfAbsent(eventName, () => []).add(p);
    }
    return groups;
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadPlannings();
      _refreshPendingCount();
      _pollCollections();
      _pollTimer = Timer.periodic(const Duration(seconds: 30), (_) => _pollCollections());
    });
    _connectivitySub = Connectivity().onConnectivityChanged.listen((result) {
      if (result != ConnectivityResult.none) {
        _sync(silent: true);
      }
    });
  }

  @override
  void dispose() {
    _connectivitySub?.cancel();
    _pollTimer?.cancel();
    super.dispose();
  }

  Future<void> _refreshPendingCount() async {
    final count = await LocalDbService.countPendingBeneficiaires();
    if (mounted) setState(() => _pendingCount = count);
  }

  Future<void> _loadPlannings() async {
    setState(() => _loading = true);
    final token = context.read<AuthService>().token;
    if (token == null) {
      if (mounted) setState(() => _loading = false);
      return;
    }
    try {
      final res = await ApiService.getMyPlannings(token);
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
    return planning['beneficiaries_count'] as int? ?? (planning['beneficiaires'] as List<dynamic>?)?.length ?? 0;
  }

  int _validatedCount(dynamic planning) {
    return planning['validated_count'] as int? ?? 0;
  }

  int _ticketsCount(dynamic planning) {
    return planning['tickets_count'] as int? ?? 0;
  }

  int _collectedCount(dynamic planning) {
    return planning['collected_count'] as int? ?? 0;
  }

  Widget _buildDashboard() {
    int totalBenef = 0, totalValidated = 0, totalTickets = 0, totalCollected = 0;
    int plannedKg = 0, executedKg = 0;
    for (final p in _plannings) {
      totalBenef += _beneficiaireCount(p);
      totalValidated += _validatedCount(p);
      totalTickets += _ticketsCount(p);
      totalCollected += _collectedCount(p);
      plannedKg += (p['planned_quota_kg'] as int?) ?? 0;
      executedKg += (p['executed_kg'] as int?) ?? 0;
    }
    final collectRate = totalTickets > 0 ? (totalCollected / totalTickets * 100).round() : 0;
    final execRate = plannedKg > 0 ? (executedKg / plannedKg * 100).round() : 0;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFD84315).withValues(alpha: 0.15)),
        boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.dashboard, size: 18, color: Color(0xFFD84315)),
              const SizedBox(width: 6),
              const Text('Tableau de bord', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFFD84315))),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(child: _kpiCard('Benediciaires', '$totalBenef', Colors.blue, Icons.people)),
              const SizedBox(width: 8),
              Expanded(child: _kpiCard('Valides', '$totalValidated', Colors.green, Icons.verified)),
              const SizedBox(width: 8),
              Expanded(child: _kpiCard('Tickets', '$totalTickets', Colors.orange, Icons.qr_code)),
              const SizedBox(width: 8),
              Expanded(child: _kpiCard('Recuperes', '$totalCollected', Colors.teal, Icons.check_circle)),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(child: _kpiCard('Planifie (kg)', '$plannedKg', Colors.indigo, Icons.scale)),
              const SizedBox(width: 8),
              Expanded(child: _kpiCard('Execute (kg)', '$executedKg', Colors.deepOrange, Icons.local_dining)),
              const SizedBox(width: 8),
              Expanded(child: _kpiCard('Taux collecte', '$collectRate%', Colors.green, Icons.percent)),
              const SizedBox(width: 8),
              Expanded(child: _kpiCard('Taux exec.', '$execRate%', Colors.purple, Icons.trending_up)),
            ],
          ),
          const SizedBox(height: 12),
          ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(
              value: totalTickets > 0 ? (totalCollected / totalTickets).clamp(0.0, 1.0) : 0,
              minHeight: 8,
              backgroundColor: Colors.orange.shade100,
              color: Colors.green,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            '$totalCollected / $totalTickets dons recuperes ($collectRate%)',
            style: const TextStyle(fontSize: 11, color: Colors.black54),
          ),
        ],
      ),
    );
  }

  Widget _kpiCard(String label, String value, Color color, IconData icon) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 6),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        children: [
          Icon(icon, size: 20, color: color),
          const SizedBox(height: 4),
          Text(value, style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: color)),
          const SizedBox(height: 2),
          Text(label, style: const TextStyle(fontSize: 9, color: Colors.black54), textAlign: TextAlign.center),
        ],
      ),
    );
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
          Stack(
            alignment: Alignment.center,
            children: [
              IconButton(
                icon: const Icon(Icons.history),
                tooltip: 'Historique des tickets',
                onPressed: _openHistory,
              ),
              if (_newCollections > 0)
                Positioned(
                  right: 6,
                  top: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                    decoration: BoxDecoration(color: Colors.green, borderRadius: BorderRadius.circular(10)),
                    child: Text('$_newCollections', style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                  ),
                ),
            ],
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await auth.logout();
              if (context.mounted) Navigator.of(context).popUntil((r) => r.isFirst);
            },
          ),
        ],
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
                  const SizedBox(height: 16),
                  _buildDashboard(),
                  const SizedBox(height: 16),
                  TextField(
                    onChanged: (v) => setState(() => _search = v),
                    decoration: InputDecoration(
                      hintText: 'Rechercher un don, un site, un lieu...',
                      hintStyle: const TextStyle(fontSize: 12),
                      prefixIcon: const Icon(Icons.search, size: 20),
                      isDense: true,
                      filled: true,
                      fillColor: Colors.white,
                      contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                    ),
                  ),
                  const SizedBox(height: 16),
                  if (_plannings.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 40),
                      child: Center(child: Text('Aucun planning assigne', style: TextStyle(color: Colors.grey))),
                    )
                  else if (_groupedByEvent.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 40),
                      child: Center(child: Text('Aucun resultat', style: TextStyle(color: Colors.grey))),
                    )
                  else
                    ..._groupedByEvent.entries.expand((entry) => [
                          Padding(
                            padding: const EdgeInsets.only(bottom: 10, top: 4),
                            child: Row(
                              children: [
                                const Icon(Icons.inventory_2_outlined, size: 18, color: Color(0xFFD84315)),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Text(
                                    entry.key,
                                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFFD84315)),
                                  ),
                                ),
                                Text('${entry.value.length} site(s)', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                              ],
                            ),
                          ),
                          ...entry.value.map((p) => _PlanningCard(
                                planning: p,
                                beneficiaireCount: _beneficiaireCount(p),
                                validatedCount: _validatedCount(p),
                                ticketsCount: _ticketsCount(p),
                                collectedCount: _collectedCount(p),
                                onRegister: () => Navigator.push(
                                  context,
                                  MaterialPageRoute(builder: (_) => BeneficiaireFormScreen(planning: p)),
                                ).then((_) {
                                  _refreshPendingCount();
                                  _loadPlannings();
                                }),
                                onViewBeneficiaries: () => Navigator.push(
                                  context,
                                  MaterialPageRoute(builder: (_) => PlanningBeneficiariesScreen(planning: p)),
                                ).then((_) => _loadPlannings()),
                                onViewTickets: () => Navigator.push(
                                  context,
                                  MaterialPageRoute(builder: (_) => TicketsHistoryScreen(planningId: p['id'], planningName: p['name'])),
                                ).then((_) => _loadPlannings()),
                              )),
                          const SizedBox(height: 10),
                        ]),
                  const SizedBox(height: 40),
                ],
              ),
      ),
    );
  }
}

class _PlanningCard extends StatelessWidget {
  final dynamic planning;
  final int beneficiaireCount;
  final int validatedCount;
  final int ticketsCount;
  final int collectedCount;
  final VoidCallback onRegister;
  final VoidCallback onViewBeneficiaries;
  final VoidCallback onViewTickets;

  const _PlanningCard({
    required this.planning,
    required this.beneficiaireCount,
    required this.validatedCount,
    required this.ticketsCount,
    required this.collectedCount,
    required this.onRegister,
    required this.onViewBeneficiaries,
    required this.onViewTickets,
  });

  Widget _statChip(IconData icon, String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(color: color.withOpacity(0.1), borderRadius: BorderRadius.circular(8)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: color),
          const SizedBox(width: 4),
          Text(label, style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final event = planning['event'];

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
            planning['name'] ?? event?['name'] ?? 'Planning #${planning['id']}',
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Colors.black87),
          ),
          if (planning['location'] != null) ...[
            const SizedBox(height: 4),
            Text('Lieu : ${planning['location']}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
          ],
          if (planning['distribution_date'] != null)
            Text('Date : ${planning['distribution_date'].toString().substring(0, 10)}',
                style: const TextStyle(fontSize: 12, color: Colors.grey)),
          const SizedBox(height: 8),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: ticketsCount == 0 ? 0 : (collectedCount / ticketsCount).clamp(0, 1),
              minHeight: 6,
              backgroundColor: Colors.orange.shade100,
              color: Colors.green,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            '$collectedCount / $ticketsCount dons recuperes  (${ticketsCount - collectedCount} ticket(s) non retire(s))',
            style: const TextStyle(fontSize: 11, color: Colors.black54),
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            runSpacing: 6,
            children: [
              _statChip(Icons.people_outline, '$beneficiaireCount benef.', Colors.blue),
              _statChip(Icons.verified, '$validatedCount valides', Colors.green),
              _statChip(Icons.qr_code, '$ticketsCount tickets', Colors.orange),
              _statChip(Icons.check_circle, '$collectedCount recuperes', Colors.teal),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    foregroundColor: const Color(0xFFD84315),
                    side: const BorderSide(color: Color(0xFFD84315)),
                  ),
                  icon: const Icon(Icons.person_add_alt_1),
                  label: const Text('Beneficiaire'),
                  onPressed: onRegister,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.blue.shade700,
                    side: BorderSide(color: Colors.blue.shade700),
                  ),
                  icon: const Icon(Icons.list_alt),
                  label: const Text('Liste'),
                  onPressed: onViewBeneficiaries,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.teal.shade700,
                    side: BorderSide(color: Colors.teal.shade700),
                  ),
                  icon: const Icon(Icons.confirmation_number_outlined),
                  label: const Text('Tickets'),
                  onPressed: onViewTickets,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
