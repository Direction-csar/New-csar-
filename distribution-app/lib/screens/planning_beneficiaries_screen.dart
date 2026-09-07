import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';
import 'qr_ticket_screen.dart';

class PlanningBeneficiariesScreen extends StatefulWidget {
  final dynamic planning;

  const PlanningBeneficiariesScreen({super.key, required this.planning});

  @override
  State<PlanningBeneficiariesScreen> createState() => _PlanningBeneficiariesScreenState();
}

class _PlanningBeneficiariesScreenState extends State<PlanningBeneficiariesScreen> {
  List<dynamic> _beneficiaries = [];
  Map<String, dynamic> _stats = {};
  bool _loading = true;
  String _search = '';
  String? _statusFilter;
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadBeneficiaries();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadBeneficiaries() async {
    setState(() => _loading = true);
    final token = context.read<AuthService>().token;
    if (token == null) return;
    try {
      final res = await ApiService.getPlanningBeneficiaries(token, widget.planning['id']);
      if (res['success'] == true) {
        final raw = res['data'];
        setState(() {
          _beneficiaries = raw is List ? raw : (raw?['data'] ?? []);
          _stats = Map<String, dynamic>.from(res['stats'] ?? {});
        });
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  List<dynamic> get _filtered {
    final q = _search.trim().toLowerCase();
    return _beneficiaries.where((b) {
      if (_statusFilter != null && b['status'] != _statusFilter) return false;
      if (q.isEmpty) return true;
      final name = (b['full_name'] ?? '').toString().toLowerCase();
      final phone = (b['phone'] ?? '').toString().toLowerCase();
      final cni = (b['cni'] ?? '').toString().toLowerCase();
      final code = (b['ticket']?['ticket_code'] ?? '').toString().toLowerCase();
      return name.contains(q) || phone.contains(q) || cni.contains(q) || code.contains(q);
    }).toList();
  }

  int _count(String key) => (_stats[key] as int?) ?? _beneficiaries.where((b) => b['status'] == key).length;

  String _fmtDate(dynamic iso) {
    if (iso == null) return '';
    final d = DateTime.tryParse(iso.toString())?.toLocal();
    if (d == null) return '';
    return '${d.day.toString().padLeft(2, '0')}/${d.month.toString().padLeft(2, '0')}/${d.year} ${d.hour.toString().padLeft(2, '0')}:${d.minute.toString().padLeft(2, '0')}';
  }

  Widget _filterChip(String label, String? value, Color color) {
    final selected = _statusFilter == value;
    final count = value == null ? _beneficiaries.length : _count(value);
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: FilterChip(
        selected: selected,
        label: Text('$label ($count)', style: TextStyle(fontSize: 11, color: selected ? Colors.white : color, fontWeight: FontWeight.w600)),
        selectedColor: color,
        backgroundColor: color.withOpacity(0.1),
        checkmarkColor: Colors.white,
        side: BorderSide(color: color.withOpacity(0.4)),
        onSelected: (_) => setState(() => _statusFilter = selected ? null : value),
      ),
    );
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'pending':
        return Colors.grey;
      case 'validated':
        return Colors.blue;
      case 'ticket_issued':
        return Colors.orange;
      case 'kit_collected':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }

  String _statusLabel(String status) {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'validated':
        return 'Valide';
      case 'ticket_issued':
        return 'Ticket emis';
      case 'kit_collected':
        return 'Kit recupere';
      default:
        return status;
    }
  }

  Future<void> _validateBeneficiary(int id) async {
    final token = context.read<AuthService>().token;
    if (token == null) return;
    try {
      final res = await ApiService.validateBeneficiary(token, id);
      if (mounted) {
        if (res['success'] == true) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Beneficiaire valide'), backgroundColor: Colors.green),
          );
          _loadBeneficiaries();
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(res['message'] ?? 'Erreur'), backgroundColor: Colors.red),
          );
        }
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Erreur reseau'), backgroundColor: Colors.red),
        );
      }
    }
  }

  Future<void> _generateTicket(int id) async {
    final token = context.read<AuthService>().token;
    if (token == null) return;
    try {
      final res = await ApiService.generateTicket(token, id);
      if (mounted) {
        if (res['success'] == true) {
          final ticket = res['data'];
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Ticket genere!'), backgroundColor: Colors.green),
          );
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => QrTicketScreen(ticket: ticket)),
          );
          _loadBeneficiaries();
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(res['message'] ?? 'Erreur'), backgroundColor: Colors.red),
          );
        }
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Erreur reseau'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final planningName = widget.planning['event']?['name'] ?? widget.planning['name'] ?? 'Planning';

    return Scaffold(
      backgroundColor: const Color(0xFFFBE9E7),
      appBar: AppBar(
        backgroundColor: const Color(0xFFD84315),
        title: Text(planningName, style: const TextStyle(fontSize: 16)),
      ),
      body: Column(
        children: [
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(12, 10, 12, 6),
            child: Column(
              children: [
                TextField(
                  controller: _searchCtrl,
                  onChanged: (v) => setState(() => _search = v),
                  decoration: InputDecoration(
                    hintText: 'Rechercher : nom, telephone, CNI, code ticket',
                    hintStyle: const TextStyle(fontSize: 12),
                    prefixIcon: const Icon(Icons.search, size: 20),
                    suffixIcon: _search.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, size: 18),
                            onPressed: () {
                              _searchCtrl.clear();
                              setState(() => _search = '');
                            },
                          )
                        : null,
                    isDense: true,
                    contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
                const SizedBox(height: 8),
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _filterChip('Tous', null, const Color(0xFFD84315)),
                      _filterChip('En attente', 'pending', Colors.grey.shade700),
                      _filterChip('Valides', 'validated', Colors.blue),
                      _filterChip('Ticket emis', 'ticket_issued', Colors.orange),
                      _filterChip('Don recupere', 'kit_collected', Colors.green),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            color: Colors.orange.shade50,
            child: Text(
              '${_count('ticket_issued')} ticket(s) retire(s) sans don recupere  |  ${_count('kit_collected')} don(s) recupere(s)',
              style: TextStyle(fontSize: 11, color: Colors.orange.shade900, fontWeight: FontWeight.w600),
            ),
          ),
          Expanded(
            child: RefreshIndicator(
        onRefresh: _loadBeneficiaries,
        color: const Color(0xFFD84315),
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: Color(0xFFD84315)))
            : _filtered.isEmpty
                ? ListView(
                    children: [
                      const SizedBox(height: 100),
                      Center(
                        child: Text(
                          _beneficiaries.isEmpty ? 'Aucun beneficiaire' : 'Aucun resultat pour cette recherche',
                          style: const TextStyle(color: Colors.grey),
                        ),
                      ),
                    ],
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _filtered.length,
                    itemBuilder: (context, index) {
                      final b = _filtered[index];
                      final status = b['status'] as String? ?? 'pending';
                      final hasTicket = b['ticket'] != null;

                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: _statusColor(status).withOpacity(0.3)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    b['full_name'] ?? 'N/A',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: _statusColor(status).withOpacity(0.15),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Text(
                                    _statusLabel(status),
                                    style: TextStyle(fontSize: 10, color: _statusColor(status), fontWeight: FontWeight.w600),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            Text('Tel: ${b['phone'] ?? '—'}  |  CNI: ${b['cni'] ?? '—'}',
                                style: const TextStyle(fontSize: 11, color: Colors.grey)),
                            Text('Qté: ${b['quantity_kg'] ?? 0} kg',
                                style: const TextStyle(fontSize: 11, color: Colors.grey)),
                            if (hasTicket) ...[
                              const SizedBox(height: 4),
                              Text('Ticket: ${b['ticket']['ticket_code']}  (emis le ${_fmtDate(b['ticket']['issued_at'])})',
                                  style: const TextStyle(fontSize: 11, color: Colors.orange, fontWeight: FontWeight.w600)),
                              if (status == 'kit_collected')
                                Text('Don recupere le ${_fmtDate(b['ticket']['collected_at'])}',
                                    style: const TextStyle(fontSize: 11, color: Colors.green, fontWeight: FontWeight.w600))
                              else
                                const Text('Don NON recupere (ticket non scanne)',
                                    style: TextStyle(fontSize: 11, color: Colors.red, fontWeight: FontWeight.w600)),
                            ],
                            const SizedBox(height: 10),
                            Row(
                              children: [
                                if (status == 'pending')
                                  Expanded(
                                    child: ElevatedButton.icon(
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.blue,
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(vertical: 8),
                                      ),
                                      icon: const Icon(Icons.verified, size: 16),
                                      label: const Text('Valider', style: TextStyle(fontSize: 12)),
                                      onPressed: () => _validateBeneficiary(b['id']),
                                    ),
                                  ),
                                if (status == 'validated') ...[
                                  Expanded(
                                    child: ElevatedButton.icon(
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.orange,
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(vertical: 8),
                                      ),
                                      icon: const Icon(Icons.qr_code, size: 16),
                                      label: const Text('Generer ticket', style: TextStyle(fontSize: 12)),
                                      onPressed: () => _generateTicket(b['id']),
                                    ),
                                  ),
                                ],
                                if (status == 'ticket_issued' && hasTicket) ...[
                                  Expanded(
                                    child: ElevatedButton.icon(
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.teal,
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(vertical: 8),
                                      ),
                                      icon: const Icon(Icons.qr_code_2, size: 16),
                                      label: const Text('Voir QR', style: TextStyle(fontSize: 12)),
                                      onPressed: () => Navigator.push(
                                        context,
                                        MaterialPageRoute(builder: (_) => QrTicketScreen(ticket: b['ticket'])),
                                      ),
                                    ),
                                  ),
                                ],
                                if (status == 'kit_collected')
                                  const Expanded(
                                    child: Center(
                                      child: Icon(Icons.check_circle, color: Colors.green, size: 24),
                                    ),
                                  ),
                              ],
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
