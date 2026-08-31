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
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadBeneficiaries();
  }

  Future<void> _loadBeneficiaries() async {
    setState(() => _loading = true);
    final token = context.read<AuthService>().token;
    if (token == null) return;
    try {
      final res = await ApiService.getPlanningBeneficiaries(token, widget.planning['id']);
      if (res['success'] == true) {
        setState(() => _beneficiaries = res['data'] ?? []);
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
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
      body: RefreshIndicator(
        onRefresh: _loadBeneficiaries,
        color: const Color(0xFFD84315),
        child: _loading
            ? const Center(child: CircularProgressIndicator(color: Color(0xFFD84315)))
            : _beneficiaries.isEmpty
                ? ListView(
                    children: const [
                      SizedBox(height: 100),
                      Center(child: Text('Aucun beneficiaire', style: TextStyle(color: Colors.grey))),
                    ],
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _beneficiaries.length,
                    itemBuilder: (context, index) {
                      final b = _beneficiaries[index];
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
                              Text('Ticket: ${b['ticket']['ticket_code']}',
                                  style: const TextStyle(fontSize: 11, color: Colors.orange, fontWeight: FontWeight.w600)),
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
    );
  }
}
