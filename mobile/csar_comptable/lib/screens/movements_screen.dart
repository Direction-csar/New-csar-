import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/movement.dart';
import '../models/warehouse.dart';
import '../services/api_service.dart';
import 'movement_detail_screen.dart';

/// Liste des mouvements de stock avec filtres
/// (statut, magasin, type, recherche par référence/motif).
class MovementsScreen extends StatefulWidget {
  const MovementsScreen({super.key});

  @override
  State<MovementsScreen> createState() => _MovementsScreenState();
}

class _MovementsScreenState extends State<MovementsScreen> {
  List<Movement> _movements = [];
  List<Warehouse> _warehouses = [];
  bool _loading = true;
  String? _error;

  String _status = 'draft';
  int? _warehouseId;
  String? _type;
  final _searchController = TextEditingController();

  static const _statuses = {
    'draft': 'En attente',
    'validated': 'Validés',
    'rejected': 'Rejetés',
    'all': 'Tous',
  };

  @override
  void initState() {
    super.initState();
    _loadWarehouses();
    _load();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadWarehouses() async {
    try {
      final list = await ApiService.getWarehouses();
      if (mounted) setState(() => _warehouses = list);
    } catch (_) {}
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final list = await ApiService.getMovements(
        status: _status == 'all' ? null : _status,
        warehouseId: _warehouseId,
        type: _type,
        query: _searchController.text.trim(),
      );
      if (!mounted) return;
      setState(() {
        _movements = list;
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
    return Scaffold(
      appBar: AppBar(title: const Text('Mouvements de stock')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 10, 12, 0),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Référence ou motif...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: IconButton(
                  icon: const Icon(Icons.send),
                  onPressed: _load,
                ),
              ),
              onSubmitted: (_) => _load(),
            ),
          ),
          SizedBox(
            height: 46,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              children: [
                ..._statuses.entries.map((e) => Padding(
                      padding: const EdgeInsets.only(right: 6),
                      child: ChoiceChip(
                        label: Text(e.value),
                        selected: _status == e.key,
                        onSelected: (_) {
                          setState(() => _status = e.key);
                          _load();
                        },
                      ),
                    )),
                if (_warehouses.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: PopupMenuButton<int?>(
                      onSelected: (v) {
                        setState(() => _warehouseId = v);
                        _load();
                      },
                      itemBuilder: (_) => [
                        const PopupMenuItem(value: null, child: Text('Tous les magasins')),
                        ..._warehouses.map((w) =>
                            PopupMenuItem(value: w.id, child: Text(w.name))),
                      ],
                      child: Chip(
                        avatar: const Icon(Icons.store, size: 16),
                        label: Text(_warehouseId == null
                            ? 'Magasin'
                            : _warehouses
                                .firstWhere((w) => w.id == _warehouseId,
                                    orElse: () => _warehouses.first)
                                .name),
                      ),
                    ),
                  ),
                Padding(
                  padding: const EdgeInsets.only(right: 6),
                  child: PopupMenuButton<String?>(
                    onSelected: (v) {
                      setState(() => _type = v);
                      _load();
                    },
                    itemBuilder: (_) => const [
                      PopupMenuItem(value: null, child: Text('Tous types')),
                      PopupMenuItem(value: 'entry', child: Text('Entrées')),
                      PopupMenuItem(value: 'exit', child: Text('Sorties')),
                    ],
                    child: Chip(
                      avatar: const Icon(Icons.swap_vert, size: 16),
                      label: Text(_type == null
                          ? 'Type'
                          : (_type == 'entry' ? 'Entrées' : 'Sorties')),
                    ),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _error != null
                    ? _errorView()
                    : _movements.isEmpty
                        ? Center(
                            child: Text('Aucun mouvement pour ces filtres.',
                                style: TextStyle(color: Colors.grey.shade600)))
                        : RefreshIndicator(
                            onRefresh: _load,
                            child: ListView.builder(
                              padding: const EdgeInsets.all(12),
                              itemCount: _movements.length,
                              itemBuilder: (_, i) =>
                                  _movementCard(_movements[i]),
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _movementCard(Movement m) {
    final date = m.createdAt != null
        ? DateFormat('dd/MM/yyyy HH:mm').format(m.createdAt!)
        : '';
    return Card(
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: (m.isExit ? Colors.red : Colors.green)
              .withValues(alpha: 0.12),
          child: Icon(
            m.isExit ? Icons.arrow_upward : Icons.arrow_downward,
            color: m.isExit ? Colors.red : Colors.green,
          ),
        ),
        title: Text(m.reference ?? 'Sans référence',
            style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text(
          '${m.typeLabel} • ${m.warehouseName ?? '-'} • Qté ${m.quantity}\n$date${m.creatorName != null ? ' • ${m.creatorName}' : ''}',
          maxLines: 2,
        ),
        isThreeLine: true,
        trailing: _statusChip(m.status),
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => MovementDetailScreen(movementId: m.id),
          ),
        ).then((_) => _load()),
      ),
    );
  }

  Widget _statusChip(String status) {
    final (label, color) = switch (status) {
      'validated' => ('Validé', Colors.green),
      'rejected' => ('Rejeté', Colors.red),
      _ => ('Attente', Colors.orange),
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
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.cloud_off, size: 48, color: Colors.grey.shade400),
          const SizedBox(height: 12),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 24),
            child: Text(_error!, textAlign: TextAlign.center),
          ),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: _load,
            icon: const Icon(Icons.refresh),
            label: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }
}
