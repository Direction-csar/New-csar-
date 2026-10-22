import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/equipment.dart';
import '../models/warehouse.dart';
import '../services/api_service.dart';

/// Carburant & Matériel : soldes carburant par produit/magasin et
/// affectations de matériel aux agents (création, retour, perte).
class MaterialsScreen extends StatefulWidget {
  const MaterialsScreen({super.key});

  @override
  State<MaterialsScreen> createState() => _MaterialsScreenState();
}

class _MaterialsScreenState extends State<MaterialsScreen> {
  List<FuelBalance> _fuel = [];
  List<EquipmentAssignment> _assignments = [];
  List<Warehouse> _warehouses = [];
  bool _loading = true;
  String? _error;
  String _statusFilter = 'affectee';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final fuel = await ApiService.getFuelSummary();
      final assignments =
          await ApiService.getAssignments(status: _statusFilter);
      final warehouses = await ApiService.getWarehouses();
      if (!mounted) return;
      setState(() {
        _fuel = fuel;
        _assignments = assignments;
        _warehouses = warehouses;
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
      appBar: AppBar(title: const Text('Carburant & Matériel')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _warehouses.isEmpty ? null : _showAssignDialog,
        icon: const Icon(Icons.add),
        label: const Text('Affecter'),
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading && _fuel.isEmpty && _assignments.isEmpty
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? _errorView()
                : ListView(
                    padding: const EdgeInsets.all(14),
                    children: [
                      _sectionTitle('Soldes carburant (litres)'),
                      if (_fuel.isEmpty)
                        _emptyHint('Aucun mouvement en litres enregistré.')
                      else
                        ..._fuel.map(_fuelCard),
                      const SizedBox(height: 18),
                      Row(
                        children: [
                          Expanded(child: _sectionTitle('Affectations matériel')),
                          DropdownButton<String>(
                            value: _statusFilter,
                            underline: const SizedBox(),
                            items: const [
                              DropdownMenuItem(
                                  value: 'affectee', child: Text('Actives')),
                              DropdownMenuItem(
                                  value: 'retournee', child: Text('Retournées')),
                              DropdownMenuItem(
                                  value: 'perdue', child: Text('Perdues')),
                            ],
                            onChanged: (v) {
                              setState(() => _statusFilter = v!);
                              _load();
                            },
                          ),
                        ],
                      ),
                      if (_assignments.isEmpty)
                        _emptyHint('Aucune affectation pour ce filtre.')
                      else
                        ..._assignments.map(_assignmentCard),
                      const SizedBox(height: 80),
                    ],
                  ),
      ),
    );
  }

  Widget _sectionTitle(String t) => Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: Text(t, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
      );

  Widget _emptyHint(String t) => Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: Text(t, style: TextStyle(color: Colors.grey.shade600)),
      );

  Widget _fuelCard(FuelBalance f) {
    final low = f.balance <= 0;
    return Card(
      child: ListTile(
        leading: Icon(Icons.local_gas_station,
            color: low ? Colors.red : Colors.orange.shade800),
        title: Text(f.produit, style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text(
            'Entrées ${f.totalIn.toStringAsFixed(0)} L • Sorties ${f.totalOut.toStringAsFixed(0)} L${f.warehouseName != null ? ' • ${f.warehouseName}' : ''}'),
        trailing: Text(
          '${f.balance.toStringAsFixed(0)} L',
          style: TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 16,
            color: low ? Colors.red : Colors.green.shade800,
          ),
        ),
      ),
    );
  }

  Widget _assignmentCard(EquipmentAssignment a) {
    final date = a.assignedAt != null
        ? DateFormat('dd/MM/yyyy').format(a.assignedAt!)
        : '';
    return Card(
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: a.isActive
              ? Colors.blue.withValues(alpha: 0.12)
              : Colors.grey.withValues(alpha: 0.15),
          child: Icon(
            a.isActive ? Icons.assignment_ind_outlined : Icons.assignment_turned_in,
            color: a.isActive ? Colors.blue : Colors.grey,
          ),
        ),
        title: Text('${a.produit} ×${a.quantity.toStringAsFixed(0)}',
            style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: Text(
          '${a.agentName} • $date${a.numeroBon != null ? ' • Bon ${a.numeroBon}' : ''}${a.returnNote != null ? '\n${a.returnNote}' : ''}',
          maxLines: 2,
        ),
        isThreeLine: true,
        trailing: a.isActive
            ? PopupMenuButton<String>(
                onSelected: (outcome) => _showCloseDialog(a, outcome),
                itemBuilder: (_) => const [
                  PopupMenuItem(value: 'retournee', child: Text('Retour')),
                  PopupMenuItem(value: 'perdue', child: Text('Déclarer perte')),
                ],
              )
            : Chip(
                label: Text(a.statusLabel, style: const TextStyle(fontSize: 11)),
                backgroundColor: a.status == 'perdue'
                    ? Colors.red.withValues(alpha: 0.15)
                    : Colors.green.withValues(alpha: 0.15),
                side: BorderSide.none,
              ),
      ),
    );
  }

  void _showAssignDialog() {
    final produit = TextEditingController();
    final agent = TextEditingController();
    final bon = TextEditingController();
    double qty = 1;
    int? warehouseId = _warehouses.first.id;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlg) => AlertDialog(
          title: const Text('Affecter du matériel'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: produit,
                  decoration: const InputDecoration(labelText: 'Matériel *'),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: agent,
                  decoration: const InputDecoration(labelText: 'Agent bénéficiaire *'),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: bon,
                  decoration:
                      const InputDecoration(labelText: 'N° bon (optionnel)'),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    const Text('Quantité : '),
                    IconButton(
                      icon: const Icon(Icons.remove_circle_outline),
                      onPressed: qty > 1 ? () => setDlg(() => qty--) : null,
                    ),
                    Text(qty.toStringAsFixed(0)),
                    IconButton(
                      icon: const Icon(Icons.add_circle_outline),
                      onPressed: () => setDlg(() => qty++),
                    ),
                  ],
                ),
                DropdownButtonFormField<int>(
                  initialValue: warehouseId,
                  decoration: const InputDecoration(labelText: 'Magasin'),
                  items: _warehouses
                      .map((w) =>
                          DropdownMenuItem(value: w.id, child: Text(w.name)))
                      .toList(),
                  onChanged: (v) => setDlg(() => warehouseId = v),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Annuler')),
            FilledButton(
              onPressed: () async {
                if (produit.text.trim().isEmpty || agent.text.trim().isEmpty) {
                  return;
                }
                Navigator.pop(ctx);
                final res = await ApiService.storeAssignment(
                  produit: produit.text.trim(),
                  agentName: agent.text.trim(),
                  warehouseId: warehouseId,
                  quantity: qty,
                  numeroBon: bon.text.trim(),
                );
                if (!mounted) return;
                _toast(res['message'], ok: res['success'] == true);
                _load();
              },
              child: const Text('Affecter'),
            ),
          ],
        ),
      ),
    );
  }

  void _showCloseDialog(EquipmentAssignment a, String outcome) {
    final note = TextEditingController();
    final isLoss = outcome == 'perdue';
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(isLoss ? 'Déclarer une perte' : 'Enregistrer un retour'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('${a.produit} — ${a.agentName}'),
            const SizedBox(height: 10),
            TextField(
              controller: note,
              maxLines: 3,
              decoration: InputDecoration(
                labelText: isLoss ? 'Motif (obligatoire) *' : 'Note (état du matériel)',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx), child: const Text('Annuler')),
          FilledButton(
            style: isLoss
                ? FilledButton.styleFrom(backgroundColor: Colors.red)
                : null,
            onPressed: () async {
              if (isLoss && note.text.trim().isEmpty) return;
              Navigator.pop(ctx);
              final res = await ApiService.closeAssignment(
                  a.id, outcome, note.text.trim());
              if (!mounted) return;
              _toast(res['message'], ok: res['success'] == true);
              _load();
            },
            child: Text(isLoss ? 'Déclarer' : 'Retourner'),
          ),
        ],
      ),
    );
  }

  Widget _errorView() => Center(
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

  void _toast(String message, {required bool ok}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: ok ? Colors.green.shade700 : Colors.red.shade700,
      ),
    );
  }
}
