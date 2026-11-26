import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/stock_operation.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../theme/csar_theme.dart';
import '../widgets/csar_ui.dart';
import 'operation_detail_screen.dart';
import 'pending_movements_screen.dart';
import 'warehouse_stock_screen.dart';

/// Espace « Gestion du contrôle » — tableau de bord inspecteur.
/// Données réelles de l'API ; cache local pour consultation hors ligne.
class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _tab = 0;
  String? _statusFilter;
  List<StockOperation> _operations = [];
  List<Map<String, dynamic>> _warehouses = [];
  bool _loading = false;
  bool _offline = false;
  String? _error;
  int _pendingCount = 0;

  static const _opsCacheKey = 'inspector_ops_cache';
  static const _whCacheKey = 'inspector_warehouses_cache';

  static const _filters = <String?, String>{
    null: 'Toutes',
    'affectee_inspecteur': 'À traiter',
    'transmise_magasinier': 'Chez magasinier',
    'executee': 'À vérifier',
    'verifiee': 'Vérifiées',
    'anomalie': 'Anomalies',
  };

  @override
  void initState() {
    super.initState();
    _load();
    _loadPendingCount();
  }

  String? get _token => context.read<AuthService>().token;

  Future<void> _loadPendingCount() async {
    final res = await ApiService.getPendingMovements(_token!);
    if (!mounted) return;
    if (res['success'] == true) {
      setState(() => _pendingCount = (res['movements'] as List? ?? []).length);
    }
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final ops = await ApiService.getOperations(_token!, status: _statusFilter);
      final wh = await ApiService.getWarehouses(_token!);
      if (!mounted) return;
      final prefs = await SharedPreferences.getInstance();

      if (ops['success'] == true) {
        _operations = (ops['operations'] as List? ?? [])
            .map((e) => StockOperation.fromJson(Map<String, dynamic>.from(e as Map)))
            .toList();
        await prefs.setString(_opsCacheKey, jsonEncode(
            _operations.map((o) => o.toJson()).toList()));
        _offline = false;
        _error = null;
      } else {
        // Repli sur le cache local
        final cached = prefs.getString(_opsCacheKey);
        if (cached != null) {
          _operations = (jsonDecode(cached) as List)
              .map((e) => StockOperation.fromJson(Map<String, dynamic>.from(e as Map)))
              .toList();
          _offline = true;
        } else {
          _error = ops['message'] as String? ?? 'Erreur de chargement.';
        }
      }

      if (wh['success'] == true) {
        _warehouses = (wh['warehouses'] as List? ?? [])
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
        await prefs.setString(_whCacheKey, jsonEncode(_warehouses));
      } else {
        final cachedWh = prefs.getString(_whCacheKey);
        if (cachedWh != null) {
          _warehouses = (jsonDecode(cachedWh) as List)
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
        }
      }
    } catch (_) {
      // Erreur réseau : tenter le cache
      try {
        final prefs = await SharedPreferences.getInstance();
        final cached = prefs.getString(_opsCacheKey);
        final cachedWh = prefs.getString(_whCacheKey);
        if (cached != null) {
          _operations = (jsonDecode(cached) as List)
              .map((e) => StockOperation.fromJson(Map<String, dynamic>.from(e as Map)))
              .toList();
        }
        if (cachedWh != null) {
          _warehouses = (jsonDecode(cachedWh) as List)
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
        }
        if (mounted) setState(() => _offline = true);
      } catch (_) {
        if (mounted) setState(() => _error = 'Erreur réseau.');
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  // Statistiques calculées sur les opérations chargées (données réelles).
  Map<String, int> get _stats {
    int aTraiter = 0, aVerifier = 0, enCours = 0, verifiees = 0, anomalies = 0;
    for (final o in _operations) {
      switch (o.status) {
        case 'affectee_inspecteur':
          aTraiter++;
          break;
        case 'transmise_magasinier':
          enCours++;
          break;
        case 'executee':
          aVerifier++;
          enCours++;
          break;
        case 'verifiee':
          verifiees++;
          break;
        case 'anomalie':
          anomalies++;
          break;
      }
    }
    return {
      'aTraiter': aTraiter,
      'aVerifier': aVerifier,
      'enCours': enCours,
      'verifiees': verifiees,
      'anomalies': anomalies,
    };
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthService>();
    return Scaffold(
      body: IndexedStack(
        index: _tab,
        children: [
          _dashboardTab(auth),
          _operationsTab(),
          _warehousesTab(),
          const PendingMovementsScreen(),
        ],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _tab,
        onDestinationSelected: (i) {
          setState(() => _tab = i);
          if (i == 3) _loadPendingCount();
        },
        height: 66,
        destinations: [
          NavigationDestination(
            icon: Badge.count(
              count: _stats['aTraiter']! + _stats['aVerifier']!,
              isLabelVisible: _stats['aTraiter']! + _stats['aVerifier']! > 0,
              child: const Icon(Icons.space_dashboard_outlined),
            ),
            selectedIcon: Badge.count(
              count: _stats['aTraiter']! + _stats['aVerifier']!,
              isLabelVisible: _stats['aTraiter']! + _stats['aVerifier']! > 0,
              child: const Icon(Icons.space_dashboard_rounded),
            ),
            label: 'Tableau de bord',
          ),
          const NavigationDestination(icon: Icon(Icons.assignment_outlined), selectedIcon: Icon(Icons.assignment_rounded), label: 'Opérations'),
          const NavigationDestination(icon: Icon(Icons.storefront_outlined), selectedIcon: Icon(Icons.storefront_rounded), label: 'Magasins'),
          NavigationDestination(
            icon: Badge.count(
              count: _pendingCount,
              isLabelVisible: _pendingCount > 0,
              child: const Icon(Icons.fact_check_outlined),
            ),
            selectedIcon: Badge.count(
              count: _pendingCount,
              isLabelVisible: _pendingCount > 0,
              child: const Icon(Icons.fact_check_rounded),
            ),
            label: 'En attente',
          ),
        ],
      ),
    );
  }

  // ------------------------------------------------------------------
  // Onglet 0 — Tableau de bord
  // ------------------------------------------------------------------
  Widget _dashboardTab(AuthService auth) {
    final s = _stats;
    return RefreshIndicator(
      onRefresh: _load,
      color: CsarColors.blue,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: EdgeInsets.zero,
        children: [
          CsarHeader(
            title: 'Gestion du contrôle',
            subtitle: 'Bonjour ${auth.userName} — périmètre d\'affectation',
            trailing: PopupMenuButton<String>(
              offset: const Offset(0, 46),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              onSelected: (v) {
                if (v == 'logout') auth.logout();
              },
              itemBuilder: (_) => [
                PopupMenuItem(enabled: false, child: Text(auth.userName, style: const TextStyle(fontWeight: FontWeight.bold))),
                const PopupMenuItem(value: 'logout', child: Text('Déconnexion', style: TextStyle(color: CsarColors.red))),
              ],
              child: Container(
                padding: const EdgeInsets.all(2),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white.withValues(alpha: 0.4)),
                ),
                child: const CircleAvatar(
                  radius: 18,
                  backgroundColor: Colors.white,
                  child: Icon(Icons.person_rounded, color: CsarColors.navy, size: 20),
                ),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                OfflineBanner(
                  offline: _offline,
                  message: _error,
                  onRetry: _error != null ? _load : null,
                ),
                const SizedBox(height: 14),
                if (_loading && _operations.isEmpty)
                  const Center(child: Padding(padding: EdgeInsets.all(40), child: CircularProgressIndicator(color: CsarColors.blue)))
                else ...[
                  GridView.count(
                    crossAxisCount: 2,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    mainAxisSpacing: 10,
                    crossAxisSpacing: 10,
                    childAspectRatio: 1.5,
                    children: [
                      KpiCard(icon: Icons.assignment_turned_in_rounded, value: '${s['aTraiter']}', label: 'À traiter', subtitle: 'instructions affectées', color: CsarColors.amber,
                          onTap: () { setState(() { _tab = 1; _statusFilter = 'affectee_inspecteur'; }); _load(); }),
                      KpiCard(icon: Icons.fact_check_rounded, value: '${s['aVerifier']}', label: 'À vérifier', subtitle: 'exécutées par le magasinier', color: const Color(0xFF7C3AED),
                          onTap: () { setState(() { _tab = 1; _statusFilter = 'executee'; }); _load(); }),
                      KpiCard(icon: Icons.sync_rounded, value: '${s['enCours']}', label: 'En cours', subtitle: 'circuit opérationnel', color: CsarColors.blue,
                          onTap: () { setState(() { _tab = 1; _statusFilter = null; }); _load(); }),
                      KpiCard(icon: Icons.verified_rounded, value: '${s['verifiees']}', label: 'Vérifiées', subtitle: 'contrôles validés', color: CsarColors.green,
                          onTap: () { setState(() { _tab = 1; _statusFilter = 'verifiee'; }); _load(); }),
                      KpiCard(icon: Icons.storefront_rounded, value: '${_warehouses.length}', label: 'Magasins affectés', subtitle: 'mon périmètre', color: CsarColors.navy,
                          onTap: () => setState(() => _tab = 2)),
                      KpiCard(icon: Icons.warning_amber_rounded, value: '${s['anomalies']}', label: 'Anomalies', subtitle: 'signalées', color: CsarColors.red,
                          onTap: () { setState(() { _tab = 1; _statusFilter = 'anomalie'; }); _load(); }),
                    ],
                  ),
                  const SizedBox(height: 16),
                  // Opérations prioritaires
                  Card(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        SectionHeader(
                          title: 'Priorités du jour',
                          icon: Icons.priority_high_rounded,
                          trailing: TextButton(
                            onPressed: () => setState(() => _tab = 1),
                            child: const Text('Tout voir', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: CsarColors.blue)),
                          ),
                        ),
                        if (_priorities.isEmpty)
                          const Padding(
                            padding: EdgeInsets.fromLTRB(16, 0, 16, 16),
                            child: EmptyState(icon: Icons.check_circle_outline_rounded, title: 'Aucune opération en attente', subtitle: 'Toutes les instructions sont traitées.'),
                          )
                        else
                          ..._priorities.take(4).map(_operationCard),
                        const SizedBox(height: 8),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  List<StockOperation> get _priorities => _operations
      .where((o) => ['affectee_inspecteur', 'executee', 'transmise_magasinier'].contains(o.status))
      .toList();

  // ------------------------------------------------------------------
  // Onglet 1 — Opérations
  // ------------------------------------------------------------------
  Widget _operationsTab() {
    return Column(
      children: [
        SizedBox(
          height: 52,
          child: ListView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            children: _filters.entries.map((e) {
              final selected = _statusFilter == e.key;
              return Padding(
                padding: const EdgeInsets.only(right: 8),
                child: ChoiceChip(
                  label: Text(e.value),
                  selected: selected,
                  selectedColor: CsarColors.blueLight,
                  labelStyle: TextStyle(
                    fontSize: 12.5,
                    fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
                    color: selected ? CsarColors.blue : CsarColors.textMuted,
                  ),
                  side: BorderSide(color: selected ? CsarColors.blue : CsarColors.border),
                  onSelected: (_) {
                    setState(() => _statusFilter = e.key);
                    _load();
                  },
                ),
              );
            }).toList(),
          ),
        ),
        Expanded(child: _body()),
      ],
    );
  }

  // ------------------------------------------------------------------
  // Onglet 2 — Magasins affectés + magasiniers
  // ------------------------------------------------------------------
  Widget _warehousesTab() {
    return RefreshIndicator(
      onRefresh: _load,
      color: CsarColors.blue,
      child: _warehouses.isEmpty && !_loading
          ? ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              children: const [
                SizedBox(height: 120),
                EmptyState(icon: Icons.storefront_outlined, title: 'Aucun magasin affecté', subtitle: 'Votre périmètre d\'inspection sera affiché ici.'),
              ],
            )
          : ListView.separated(
              padding: const EdgeInsets.all(12),
              itemCount: _warehouses.length,
              separatorBuilder: (_, _) => const SizedBox(height: 8),
              itemBuilder: (_, i) {
                final w = _warehouses[i];
                final mags = (w['magasiniers'] as List? ?? []);
                return Card(
                  child: InkWell(
                    borderRadius: BorderRadius.circular(14),
                    onTap: () => Navigator.of(context).push(MaterialPageRoute(
                      builder: (_) => WarehouseStockScreen(warehouse: w),
                    )),
                    child: Padding(
                      padding: const EdgeInsets.all(14),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(9),
                                decoration: BoxDecoration(
                                  color: CsarColors.blueLight,
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Icon(Icons.storefront_rounded, color: CsarColors.blue, size: 20),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text((w['name'] ?? '') as String,
                                        style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14.5, color: CsarColors.text)),
                                    Text(_warehouseSubtitle(w),
                                        style: const TextStyle(fontSize: 12, color: CsarColors.textMuted)),
                                  ],
                                ),
                              ),
                              const Icon(Icons.chevron_right_rounded, color: CsarColors.textMuted),
                            ],
                          ),
                          if (mags.isNotEmpty) ...[
                            const SizedBox(height: 10),
                            Wrap(
                              spacing: 6,
                              runSpacing: 6,
                              children: mags.map((m) {
                                final name = (m as Map)['name']?.toString() ?? '';
                                return Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: CsarColors.orangeLight,
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.person_rounded, size: 12, color: CsarColors.orange),
                                      const SizedBox(width: 4),
                                      Text(name, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600, color: Color(0xFF9A3412))),
                                    ],
                                  ),
                                );
                              }).toList(),
                            ),
                          ] else
                            const Padding(
                              padding: EdgeInsets.only(top: 8),
                              child: Text('Aucun magasinier rattaché', style: TextStyle(fontSize: 12, color: CsarColors.amber, fontWeight: FontWeight.w600)),
                            ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
    );
  }

  Widget _body() {
    if (_loading && _operations.isEmpty) {
      return const Center(child: CircularProgressIndicator(color: CsarColors.blue));
    }
    if (_error != null && _operations.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.cloud_off_rounded, size: 44, color: CsarColors.textMuted),
            const SizedBox(height: 12),
            Text(_error!, textAlign: TextAlign.center, style: const TextStyle(color: CsarColors.textMuted)),
            const SizedBox(height: 12),
            FilledButton(onPressed: _load, child: const Text('Réessayer')),
          ],
        ),
      );
    }
    if (_tab == 1) {
      if (_operations.isEmpty) {
        return const Center(
          child: EmptyState(icon: Icons.assignment_outlined, title: 'Aucune opération', subtitle: 'Aucune opération ne correspond à ce filtre.'),
        );
      }
      return RefreshIndicator(
        onRefresh: _load,
        color: CsarColors.blue,
        child: ListView.separated(
          padding: const EdgeInsets.fromLTRB(12, 8, 12, 16),
          itemCount: _operations.length,
          separatorBuilder: (_, _) => const SizedBox(height: 8),
          itemBuilder: (_, i) => _operationCard(_operations[i]),
        ),
      );
    }
    return const SizedBox.shrink();
  }

  String _warehouseSubtitle(Map<String, dynamic> w) {
    final place = [w['region'], w['city']]
        .whereType<String>()
        .where((s) => s.isNotEmpty)
        .join(' — ');
    return place.isEmpty ? 'Magasin CSAR' : place;
  }

  Widget _operationCard(StockOperation op) {
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: () async {
          await Navigator.of(context).push(MaterialPageRoute(
            builder: (_) => OperationDetailScreen(operationId: op.id),
          ));
          _load();
        },
        child: Padding(
          padding: const EdgeInsets.all(13),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(op.reference,
                        style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: CsarColors.textMuted, letterSpacing: 0.3)),
                  ),
                  StatusBadge.of(op.status),
                ],
              ),
              const SizedBox(height: 8),
              Text('${op.typeLabel} — ${op.warehouseName}',
                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14.5, color: CsarColors.text)),
              const SizedBox(height: 5),
              Text(
                [
                  if (op.productName != null) '${op.productName}${op.format != null ? ' (${op.format})' : ''}',
                  if (op.quantityRequested != null)
                    'qté demandée : ${op.quantityRequested} ${op.productUnit ?? ''}',
                  if (op.quantityExecuted != null)
                    'qté exécutée : ${op.quantityExecuted}',
                ].join(' · '),
                style: const TextStyle(fontSize: 12, color: CsarColors.textMuted),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
