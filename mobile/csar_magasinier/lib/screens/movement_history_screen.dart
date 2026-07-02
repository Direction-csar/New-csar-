import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/stock_movement.dart';
import '../models/warehouse.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';
import 'receipt_screen.dart';

class MovementHistoryScreen extends StatefulWidget {
  const MovementHistoryScreen({super.key});

  @override
  State<MovementHistoryScreen> createState() => _MovementHistoryScreenState();
}

class _MovementHistoryScreenState extends State<MovementHistoryScreen> {
  List<StockMovement> _movements = [];
  List<StockMovement> _filtered = [];
  List<Warehouse> _warehouses = [];
  bool _loading = true;

  String? _filterType;
  String? _filterWarehouse;
  DateTime? _filterDateFrom;
  DateTime? _filterDateTo;

  final _dateFormat = DateFormat('dd/MM/yyyy');

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    try {
      // Charger magasins
      final wh = await ApiService.getWarehouses();
      setState(() => _warehouses = wh);
    } catch (e) {
      // fallback
    }

    // Charger mouvements locaux
    try {
      final local = await DatabaseService.getAllMovements();
      _movements = local;
    } catch (e) {
      _movements = [];
    }

    // Essayer de charger depuis l'API
    try {
      final hasNet = await ApiService.hasConnection();
      if (hasNet && _warehouses.isNotEmpty) {
        final allApi = <StockMovement>[];
        for (final w in _warehouses) {
          try {
            final data = await ApiService.getStockSheet(w.id);
            final apiMovs = (data['movements']?['data'] as List? ?? [])
                .map((e) => StockMovement.fromJson(e))
                .toList();
            allApi.addAll(apiMovs);
          } catch (_) {}
        }
        // Fusionner (API en priorité)
        _movements = allApi..addAll(_movements.where((m) => !m.isSynced));
      }
    } catch (_) {}

    _applyFilters();
    setState(() => _loading = false);
  }

  void _applyFilters() {
    _filtered = _movements.where((m) {
      if (_filterType != null && m.movementType != _filterType) return false;
      if (_filterWarehouse != null) {
        final wh = _warehouses.firstWhere(
          (w) => w.id.toString() == _filterWarehouse,
          orElse: () => Warehouse(id: -1, name: '', code: '', capacity: 0, currentStock: 0, status: 'active'),
        );
        if (wh.id != -1 && m.warehouseId != wh.id) return false;
      }
      if (_filterDateFrom != null) {
        final mDate = DateTime.tryParse(m.movementDate);
        if (mDate != null && mDate.isBefore(_filterDateFrom!)) return false;
      }
      if (_filterDateTo != null) {
        final mDate = DateTime.tryParse(m.movementDate);
        if (mDate != null && mDate.isAfter(_filterDateTo!.add(const Duration(days: 1)))) return false;
      }
      return true;
    }).toList();
    // Trier par date décroissante
    _filtered.sort((a, b) => b.movementDate.compareTo(a.movementDate));
  }

  Future<void> _pickDate(bool isFrom) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() {
        if (isFrom) _filterDateFrom = picked; else _filterDateTo = picked;
        _applyFilters();
      });
    }
  }

  String _typeLabel(String type) {
    switch (type) {
      case 'entry': return 'Entrée';
      case 'exit': return 'Sortie';
      case 'transfer': return 'Transfert';
      case 'adjustment': return 'Ajustement';
      default: return type;
    }
  }

  Color _typeColor(String type) {
    switch (type) {
      case 'entry': return Colors.green;
      case 'exit': return Colors.red;
      case 'transfer': return Colors.purple;
      case 'adjustment': return Colors.orange;
      default: return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Historique Mouvements'),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadData),
        ],
      ),
      body: Column(
        children: [
          // Filtres
          Container(
            padding: const EdgeInsets.all(12),
            color: Colors.grey.shade100,
            child: Column(
              children: [
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        isDense: true,
                        decoration: const InputDecoration(labelText: 'Type', border: OutlineInputBorder(), contentPadding: EdgeInsets.symmetric(horizontal: 8, vertical: 8)),
                        value: _filterType,
                        items: [
                          const DropdownMenuItem(value: null, child: Text('Tous')),
                          ...['entry', 'exit', 'transfer', 'adjustment'].map((t) => DropdownMenuItem(value: t, child: Text(_typeLabel(t)))),
                        ],
                        onChanged: (v) => setState(() { _filterType = v; _applyFilters(); }),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        isDense: true,
                        decoration: const InputDecoration(labelText: 'Magasin', border: OutlineInputBorder(), contentPadding: EdgeInsets.symmetric(horizontal: 8, vertical: 8)),
                        value: _filterWarehouse,
                        items: [
                          const DropdownMenuItem(value: null, child: Text('Tous')),
                          ..._warehouses.map((w) => DropdownMenuItem(value: w.id.toString(), child: Text(w.name, overflow: TextOverflow.ellipsis))),
                        ],
                        onChanged: (v) => setState(() { _filterWarehouse = v; _applyFilters(); }),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        icon: const Icon(Icons.calendar_today, size: 16),
                        label: Text(_filterDateFrom == null ? 'Du' : _dateFormat.format(_filterDateFrom!)),
                        onPressed: () => _pickDate(true),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: OutlinedButton.icon(
                        icon: const Icon(Icons.calendar_today, size: 16),
                        label: Text(_filterDateTo == null ? 'Au' : _dateFormat.format(_filterDateTo!)),
                        onPressed: () => _pickDate(false),
                      ),
                    ),
                    const SizedBox(width: 8),
                    IconButton(
                      icon: const Icon(Icons.clear),
                      onPressed: () => setState(() {
                        _filterType = null;
                        _filterWarehouse = null;
                        _filterDateFrom = null;
                        _filterDateTo = null;
                        _applyFilters();
                      }),
                    ),
                  ],
                ),
              ],
            ),
          ),
          // Résultats
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _filtered.isEmpty
                    ? const Center(child: Text('Aucun mouvement trouvé'))
                    : ListView.builder(
                        itemCount: _filtered.length,
                        itemBuilder: (context, i) {
                          final m = _filtered[i];
                          final wh = _warehouses.firstWhere(
                            (w) => w.id == m.warehouseId,
                            orElse: () => Warehouse(
                              id: m.warehouseId,
                              name: 'Magasin #${m.warehouseId}',
                              code: '',
                              capacity: 0,
                              currentStock: 0,
                              status: 'active',
                            ),
                          );
                          return Card(
                            margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                            child: ListTile(
                              leading: CircleAvatar(
                                backgroundColor: _typeColor(m.movementType).withOpacity(0.2),
                                child: Icon(
                                  m.movementType == 'entry' ? Icons.arrow_downward
                                  : m.movementType == 'exit' ? Icons.arrow_upward
                                  : m.movementType == 'transfer' ? Icons.swap_horiz
                                  : Icons.tune,
                                  color: _typeColor(m.movementType),
                                  size: 18,
                                ),
                              ),
                              title: Text(m.label, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              subtitle: Text('${wh.name} · ${m.movementDate} · ${m.reference}', style: const TextStyle(fontSize: 11)),
                              trailing: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(
                                    m.entrySacs > 0 ? '+${m.entrySacs.toStringAsFixed(0)}' : m.exitSacs > 0 ? '-${m.exitSacs.toStringAsFixed(0)}' : '0',
                                    style: TextStyle(fontWeight: FontWeight.bold, color: _typeColor(m.movementType), fontSize: 14),
                                  ),
                                  Text('sacs', style: TextStyle(fontSize: 10, color: Colors.grey.shade600)),
                                ],
                              ),
                              onTap: () => Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => ReceiptScreen(reference: m.reference)),
                              ),
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }
}
