import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  List<dynamic> _items = [];
  Map<String, dynamic> _stats = {};
  bool _loading = true;
  bool _todayOnly = false;
  String _search = '';
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    final token = context.read<AuthService>().token;
    if (token == null) return;
    try {
      final now = DateTime.now();
      final date = _todayOnly
          ? '${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}'
          : null;
      final res = await ApiService.getScanHistory(token, search: _search, date: date);
      if (res['success'] == true && mounted) {
        setState(() {
          _items = res['data'] ?? [];
          _stats = Map<String, dynamic>.from(res['stats'] ?? {});
        });
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  String _fmt(dynamic iso) {
    if (iso == null) return '';
    final d = DateTime.tryParse(iso.toString())?.toLocal();
    if (d == null) return '';
    return '${d.day.toString().padLeft(2, '0')}/${d.month.toString().padLeft(2, '0')}/${d.year} ${d.hour.toString().padLeft(2, '0')}:${d.minute.toString().padLeft(2, '0')}';
  }

  Widget _stat(String label, String value) {
    return Expanded(
      child: Column(
        children: [
          Text(value, style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)),
          Text(label, style: const TextStyle(color: Colors.white70, fontSize: 11)),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF3F6FB),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1565C0),
        title: const Text('Historique des dons recuperes'),
      ),
      body: Column(
        children: [
          Container(
            color: const Color(0xFF1565C0),
            padding: const EdgeInsets.fromLTRB(16, 4, 16, 14),
            child: Row(
              children: [
                _stat("Aujourd'hui", '${_stats['today'] ?? 0}'),
                _stat('Total', '${_stats['total'] ?? 0}'),
                _stat('Kg servis', '${(_stats['total_kg'] ?? 0).toStringAsFixed(0)}'),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 10, 12, 4),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchCtrl,
                    onSubmitted: (v) {
                      _search = v;
                      _load();
                    },
                    decoration: InputDecoration(
                      hintText: 'Nom, telephone ou code ticket',
                      hintStyle: const TextStyle(fontSize: 12),
                      prefixIcon: const Icon(Icons.search, size: 20),
                      isDense: true,
                      filled: true,
                      fillColor: Colors.white,
                      contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                FilterChip(
                  selected: _todayOnly,
                  label: const Text("Aujourd'hui", style: TextStyle(fontSize: 11)),
                  selectedColor: const Color(0xFF1565C0),
                  labelStyle: TextStyle(color: _todayOnly ? Colors.white : Colors.black87),
                  checkmarkColor: Colors.white,
                  onSelected: (v) {
                    setState(() => _todayOnly = v);
                    _load();
                  },
                ),
              ],
            ),
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: _load,
              child: _loading
                  ? const Center(child: CircularProgressIndicator(color: Color(0xFF1565C0)))
                  : _items.isEmpty
                      ? ListView(children: const [
                          SizedBox(height: 100),
                          Center(child: Text('Aucun don recupere', style: TextStyle(color: Colors.grey))),
                        ])
                      : ListView.builder(
                          padding: const EdgeInsets.all(12),
                          itemCount: _items.length,
                          itemBuilder: (_, i) {
                            final t = _items[i];
                            final b = t['beneficiary'] ?? {};
                            final p = t['planning'] ?? {};
                            return Container(
                              margin: const EdgeInsets.only(bottom: 10),
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: Colors.green.withOpacity(0.3)),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.check_circle, color: Colors.green, size: 28),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(b['full_name'] ?? 'N/A', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                        Text('${t['ticket_code']}  |  ${b['quantity_kg'] ?? 0} kg', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                                        Text('${p['event']?['name'] ?? ''} - ${p['name'] ?? ''}', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                                        Text('Recupere le ${_fmt(t['collected_at'])}', style: const TextStyle(fontSize: 11, color: Colors.green, fontWeight: FontWeight.w600)),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
            ),
          ),
        ],
      ),
    );
  }
}
