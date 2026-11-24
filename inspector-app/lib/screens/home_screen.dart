import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/stock_operation.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import 'operation_detail_screen.dart';
import 'pending_movements_screen.dart';
import 'warehouse_stock_screen.dart';

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
  String? _error;

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
  }

  String? get _token => context.read<AuthService>().token;

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final ops = await ApiService.getOperations(_token!, status: _statusFilter);
      final wh = await ApiService.getWarehouses(_token!);
      if (!mounted) return;
      setState(() {
        if (ops['success'] == true) {
          _operations = (ops['operations'] as List? ?? [])
              .map((e) => StockOperation.fromJson(Map<String, dynamic>.from(e as Map)))
              .toList();
        } else {
          _error = ops['message'] as String? ?? 'Erreur de chargement.';
        }
        if (wh['success'] == true) {
          _warehouses = (wh['warehouses'] as List? ?? [])
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
        }
      });
    } catch (_) {
      if (mounted) setState(() => _error = 'Erreur réseau.');
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Color _statusColor(String status) => switch (status) {
        'affectee_inspecteur' => Colors.orange.shade700,
        'transmise_magasinier' => Colors.blue.shade700,
        'executee' => Colors.purple.shade700,
        'verifiee' => Colors.green.shade700,
        'anomalie' => Colors.red.shade700,
        'rejetee' || 'annulee' => Colors.grey.shade600,
        _ => Colors.teal.shade700,
      };

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthService>();
    return Scaffold(
      appBar: AppBar(
        title: const Text('CSAR Inspecteur'),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _load),
          PopupMenuButton<String>(
            icon: const Icon(Icons.account_circle),
            itemBuilder: (_) => [
              PopupMenuItem(enabled: false, child: Text(auth.userName)),
              const PopupMenuItem(value: 'logout', child: Text('Déconnexion')),
            ],
            onSelected: (v) {
              if (v == 'logout') auth.logout();
            },
          ),
        ],
      ),
      body: _tab == 0 ? _operationsTab() : (_tab == 2 ? const PendingMovementsScreen() : _body()),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _tab,
        onDestinationSelected: (i) => setState(() => _tab = i),
        destinations: const [
          NavigationDestination(icon: Icon(Icons.assignment_outlined), label: 'Opérations'),
          NavigationDestination(icon: Icon(Icons.warehouse_outlined), label: 'Magasins'),
          NavigationDestination(icon: Icon(Icons.fact_check_outlined), label: 'En attente'),
        ],
      ),
    );
  }

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

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(_error!, textAlign: TextAlign.center),
            const SizedBox(height: 12),
            FilledButton(onPressed: _load, child: const Text('Réessayer')),
          ],
        ),
      );
    }
    if (_tab == 0) {
      if (_operations.isEmpty) {
        return const Center(child: Text('Aucune opération dans ce filtre.'));
      }
      return RefreshIndicator(
        onRefresh: _load,
        child: ListView.separated(
          padding: const EdgeInsets.all(12),
          itemCount: _operations.length,
          separatorBuilder: (_, _) => const SizedBox(height: 8),
          itemBuilder: (_, i) => _operationCard(_operations[i]),
        ),
      );
    }
    if (_warehouses.isEmpty) {
      return const Center(child: Text('Aucun magasin disponible.'));
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.all(12),
        itemCount: _warehouses.length,
        separatorBuilder: (_, _) => const SizedBox(height: 8),
        itemBuilder: (_, i) {
          final w = _warehouses[i];
          return Card(
            child: ListTile(
              leading: const Icon(Icons.warehouse_outlined),
              title: Text((w['name'] ?? '') as String),
              subtitle: Text([
                w['region'],
                w['city'],
              ].whereType<String>().where((s) => s.isNotEmpty).join(' — ')),
              trailing: const Icon(Icons.chevron_right),
              onTap: () => Navigator.of(context).push(MaterialPageRoute(
                builder: (_) => WarehouseStockScreen(warehouse: w),
              )),
            ),
          );
        },
      ),
    );
  }

  Widget _operationCard(StockOperation op) {
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () async {
          await Navigator.of(context).push(MaterialPageRoute(
            builder: (_) => OperationDetailScreen(operationId: op.id),
          ));
          _load();
        },
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(op.reference,
                        style: const TextStyle(fontWeight: FontWeight.bold)),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: _statusColor(op.status).withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(op.statusLabel,
                        style: TextStyle(
                            fontSize: 12,
                            color: _statusColor(op.status),
                            fontWeight: FontWeight.w600)),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text('${op.typeLabel} — ${op.warehouseName}',
                  style: const TextStyle(fontWeight: FontWeight.w500)),
              const SizedBox(height: 4),
              Text(
                [
                  if (op.productName != null) '${op.productName}${op.format != null ? ' (${op.format})' : ''}',
                  if (op.quantityRequested != null)
                    'qté demandée : ${op.quantityRequested} ${op.productUnit ?? ''}',
                  if (op.quantityExecuted != null)
                    'qté exécutée : ${op.quantityExecuted}',
                ].join(' · '),
                style: const TextStyle(fontSize: 12, color: Colors.black54),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
