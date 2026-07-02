import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';
import 'receipt_screen.dart';

class ReceiptHistoryScreen extends StatefulWidget {
  const ReceiptHistoryScreen({super.key});

  @override
  State<ReceiptHistoryScreen> createState() => _ReceiptHistoryScreenState();
}

class _ReceiptHistoryScreenState extends State<ReceiptHistoryScreen> {
  List<Map<String, dynamic>> _receipts = [];
  List<Map<String, dynamic>> _filtered = [];
  bool _loading = true;

  String _searchQuery = '';
  String? _filterType;
  DateTime? _filterDateFrom;
  DateTime? _filterDateTo;

  @override
  void initState() {
    super.initState();
    _loadReceipts();
  }

  Future<void> _loadReceipts() async {
    setState(() => _loading = true);
    final prefs = await SharedPreferences.getInstance();
    final stored = prefs.getStringList('receipt_history') ?? [];

    final parsed = stored.map((s) {
      try {
        final parts = s.split('|');
        return {
          'reference': parts.length > 0 ? parts[0] : '',
          'type': parts.length > 1 ? parts[1] : '',
          'warehouse': parts.length > 2 ? parts[2] : '',
          'date': parts.length > 3 ? parts[3] : '',
          'label': parts.length > 4 ? parts[4] : '',
        };
      } catch (_) {
        return {'reference': s, 'type': '', 'warehouse': '', 'date': '', 'label': ''};
      }
    }).toList();

    // Trier par date décroissante
    parsed.sort((a, b) => (b['date'] ?? '').compareTo(a['date'] ?? ''));

    setState(() {
      _receipts = parsed;
      _applyFilters();
      _loading = false;
    });
  }

  void _applyFilters() {
    _filtered = _receipts.where((r) {
      final q = _searchQuery.toLowerCase();
      if (q.isNotEmpty) {
        final match = (r['reference'] ?? '').toLowerCase().contains(q) ||
                      (r['warehouse'] ?? '').toLowerCase().contains(q) ||
                      (r['label'] ?? '').toLowerCase().contains(q);
        if (!match) return false;
      }
      if (_filterType != null && (r['type'] ?? '') != _filterType) return false;
      if (_filterDateFrom != null || _filterDateTo != null) {
        final rDate = DateTime.tryParse(r['date'] ?? '');
        if (rDate == null) return false;
        if (_filterDateFrom != null && rDate.isBefore(_filterDateFrom!)) return false;
        if (_filterDateTo != null && rDate.isAfter(_filterDateTo!.add(const Duration(days: 1)))) return false;
      }
      return true;
    }).toList();
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
      default: return type.isEmpty ? '—' : type;
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
        title: const Text('Historique Reçus'),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadReceipts),
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
                TextField(
                  decoration: const InputDecoration(
                    hintText: 'Rechercher (référence, magasin, produit...)',
                    prefixIcon: Icon(Icons.search),
                    border: OutlineInputBorder(),
                    isDense: true,
                    contentPadding: EdgeInsets.symmetric(vertical: 10),
                  ),
                  onChanged: (v) => setState(() { _searchQuery = v; _applyFilters(); }),
                ),
                const SizedBox(height: 8),
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
                      child: OutlinedButton.icon(
                        icon: const Icon(Icons.calendar_today, size: 16),
                        label: Text(_filterDateFrom == null ? 'Du' : '${_filterDateFrom!.day}/${_filterDateFrom!.month}/${_filterDateFrom!.year}'),
                        onPressed: () => _pickDate(true),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: OutlinedButton.icon(
                        icon: const Icon(Icons.calendar_today, size: 16),
                        label: Text(_filterDateTo == null ? 'Au' : '${_filterDateTo!.day}/${_filterDateTo!.month}/${_filterDateTo!.year}'),
                        onPressed: () => _pickDate(false),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.clear),
                      onPressed: () => setState(() {
                        _searchQuery = '';
                        _filterType = null;
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
          // Liste
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _filtered.isEmpty
                    ? const Center(child: Text('Aucun reçu trouvé'))
                    : ListView.builder(
                        itemCount: _filtered.length,
                        itemBuilder: (context, i) {
                          final r = _filtered[i];
                          return Card(
                            margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                            child: ListTile(
                              leading: CircleAvatar(
                                backgroundColor: _typeColor(r['type'] ?? '').withOpacity(0.2),
                                child: Icon(Icons.receipt, color: _typeColor(r['type'] ?? ''), size: 18),
                              ),
                              title: Text(r['reference'] ?? '—', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              subtitle: Text('${r['warehouse'] ?? '—'} · ${r['date'] ?? '—'}', style: const TextStyle(fontSize: 11)),
                              trailing: const Icon(Icons.chevron_right),
                              onTap: () => Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => ReceiptScreen(reference: r['reference'] ?? '')),
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
