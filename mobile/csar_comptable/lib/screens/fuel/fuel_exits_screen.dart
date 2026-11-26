import 'package:flutter/material.dart';
import '../../models/fuel.dart';
import '../../models/warehouse.dart';
import '../../services/api_service.dart';
import 'fuel_widgets.dart';

/// Onglet « Sorties » : bons de dotation carburant.
class FuelExitsScreen extends StatefulWidget {
  const FuelExitsScreen({super.key});

  @override
  State<FuelExitsScreen> createState() => _FuelExitsScreenState();
}

class _FuelExitsScreenState extends State<FuelExitsScreen> {
  late Future<List<FuelExit>> _future;
  String? _status;
  String _query = '';
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<List<FuelExit>> _load() =>
      ApiService.getFuelExits(status: _status, query: _query);

  Future<void> _refresh() async {
    setState(() => _future = _load());
    await _future;
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchCtrl,
                    decoration: InputDecoration(
                      hintText: 'N° bon, bénéficiaire, immat., mission…',
                      prefixIcon: const Icon(Icons.search),
                      suffixIcon: _query.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear),
                              onPressed: () {
                                _searchCtrl.clear();
                                setState(() => _query = '');
                                _refresh();
                              },
                            )
                          : null,
                    ),
                    onSubmitted: (v) {
                      setState(() => _query = v.trim());
                      _refresh();
                    },
                  ),
                ),
                const SizedBox(width: 8),
                DropdownButton<String?>(
                  value: _status,
                  hint: const Text('Statut'),
                  items: const [
                    DropdownMenuItem(value: null, child: Text('Tous')),
                    DropdownMenuItem(value: 'draft', child: Text('Brouillon')),
                    DropdownMenuItem(
                        value: 'authorized', child: Text('Autorisée')),
                    DropdownMenuItem(
                        value: 'validated', child: Text('Livrée')),
                    DropdownMenuItem(
                        value: 'rejected', child: Text('Rejetée')),
                  ],
                  onChanged: (v) {
                    setState(() => _status = v);
                    _refresh();
                  },
                ),
              ],
            ),
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: _refresh,
              child: FutureBuilder<List<FuelExit>>(
                future: _future,
                builder: (context, snap) {
                  if (snap.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  if (snap.hasError) {
                    return FuelErrorView(
                        error: snap.error!, onRetry: _refresh);
                  }
                  final exits = snap.data!;
                  if (exits.isEmpty) {
                    return ListView(
                      children: const [
                        SizedBox(height: 80),
                        Icon(Icons.inbox_outlined,
                            size: 48, color: Colors.black26),
                        Center(child: Text('Aucune sortie.')),
                      ],
                    );
                  }
                  return ListView.builder(
                    padding: const EdgeInsets.only(bottom: 80),
                    itemCount: exits.length,
                    itemBuilder: (context, i) {
                      final x = exits[i];
                      return Card(
                        margin: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 4),
                        child: ListTile(
                          leading: const Icon(Icons.north,
                              color: Color(0xFF0D47A1)),
                          title: Text(x.exitNumber,
                              style: const TextStyle(
                                  fontWeight: FontWeight.w600)),
                          subtitle: Text(
                            '${x.beneficiaryName}${x.vehiclePlate != null ? ' — ${x.vehiclePlate}' : ''}\n${fuelLiters(x.quantityRequested)} demandés'
                            '${x.quantityDelivered != null ? ' · ${fuelLiters(x.quantityDelivered)} délivrés' : ''}',
                          ),
                          isThreeLine: true,
                          trailing: FuelStatusChip(x.status),
                          onTap: () async {
                            await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) =>
                                    FuelExitDetailScreen(exitId: x.id),
                              ),
                            );
                            _refresh();
                          },
                        ),
                      );
                    },
                  );
                },
              ),
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          final created = await Navigator.push<bool>(
            context,
            MaterialPageRoute(builder: (_) => const FuelExitFormScreen()),
          );
          if (created == true) _refresh();
        },
        icon: const Icon(Icons.add),
        label: const Text('Sortie'),
      ),
    );
  }
}

/* ==================== FORMULAIRE ==================== */

class FuelExitFormScreen extends StatefulWidget {
  const FuelExitFormScreen({super.key});

  @override
  State<FuelExitFormScreen> createState() => _FuelExitFormScreenState();
}

class _FuelExitFormScreenState extends State<FuelExitFormScreen> {
  final _formKey = GlobalKey<FormState>();
  bool _saving = false;
  List<Warehouse> _warehouses = [];

  final _beneficiaryCtrl = TextEditingController();
  final _vehicleCtrl = TextEditingController();
  final _plateCtrl = TextEditingController();
  final _destinationCtrl = TextEditingController();
  final _purposeCtrl = TextEditingController();
  final _missionCtrl = TextEditingController();
  final _authRefCtrl = TextEditingController();
  final _quantityCtrl = TextEditingController();
  final _fuelLabelCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();

  int? _warehouseId;
  String _fuelType = 'gasoil';
  DateTime _operationDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    ApiService.getWarehouses().then((w) {
      if (mounted) setState(() => _warehouses = w);
    }).catchError((_) {});
  }

  @override
  void dispose() {
    for (final c in [
      _beneficiaryCtrl, _vehicleCtrl, _plateCtrl, _destinationCtrl,
      _purposeCtrl, _missionCtrl, _authRefCtrl, _quantityCtrl,
      _fuelLabelCtrl, _notesCtrl,
    ]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _pickDate() async {
    final d = await showDatePicker(
      context: context,
      initialDate: _operationDate,
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 30)),
    );
    if (d != null) setState(() => _operationDate = d);
  }

  String get _dateStr =>
      '${_operationDate.year}-${_operationDate.month.toString().padLeft(2, '0')}-${_operationDate.day.toString().padLeft(2, '0')}';

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final res = await ApiService.createFuelExit({
      'operation_date': _dateStr,
      'warehouse_id': _warehouseId,
      'fuel_type': _fuelType,
      if (_fuelType == 'autre')
        'fuel_type_label': _fuelLabelCtrl.text.trim(),
      'beneficiary_name': _beneficiaryCtrl.text.trim(),
      if (_vehicleCtrl.text.trim().isNotEmpty)
        'vehicle_label': _vehicleCtrl.text.trim(),
      if (_plateCtrl.text.trim().isNotEmpty)
        'vehicle_plate': _plateCtrl.text.trim(),
      if (_destinationCtrl.text.trim().isNotEmpty)
        'destination': _destinationCtrl.text.trim(),
      'purpose': _purposeCtrl.text.trim(),
      if (_missionCtrl.text.trim().isNotEmpty)
        'mission_number': _missionCtrl.text.trim(),
      if (_authRefCtrl.text.trim().isNotEmpty)
        'authorization_ref': _authRefCtrl.text.trim(),
      'quantity_requested':
          double.parse(_quantityCtrl.text.replaceAll(',', '.')),
      if (_notesCtrl.text.trim().isNotEmpty)
        'notes': _notesCtrl.text.trim(),
    });

    if (!mounted) return;
    setState(() => _saving = false);
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(res['message'] ?? '')));
    if (res['success'] == true) Navigator.pop(context, true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Nouvelle sortie')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text("Date de l'opération"),
              subtitle: Text(_dateStr),
              trailing: const Icon(Icons.calendar_today),
              onTap: _pickDate,
            ),
            DropdownButtonFormField<int>(
              initialValue: _warehouseId,
              decoration: const InputDecoration(labelText: 'Magasin / cuve *'),
              items: _warehouses
                  .map((w) => DropdownMenuItem(
                      value: w.id, child: Text(w.name)))
                  .toList(),
              validator: (v) => v == null ? 'Magasin requis' : null,
              onChanged: (v) => setState(() => _warehouseId = v),
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: _fuelType,
              decoration:
                  const InputDecoration(labelText: 'Carburant *'),
              items: const [
                DropdownMenuItem(value: 'essence', child: Text('Essence')),
                DropdownMenuItem(value: 'gasoil', child: Text('Gasoil')),
                DropdownMenuItem(value: 'petrole', child: Text('Pétrole')),
                DropdownMenuItem(value: 'autre', child: Text('Autre')),
              ],
              onChanged: (v) => setState(() => _fuelType = v!),
            ),
            if (_fuelType == 'autre') ...[
              const SizedBox(height: 12),
              TextFormField(
                controller: _fuelLabelCtrl,
                decoration: const InputDecoration(
                    labelText: 'Précisez le carburant *'),
                validator: (v) =>
                    (v == null || v.trim().isEmpty) ? 'Requis' : null,
              ),
            ],
            const SizedBox(height: 12),
            TextFormField(
              controller: _beneficiaryCtrl,
              decoration: const InputDecoration(
                  labelText: 'Bénéficiaire / chauffeur *'),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Requis' : null,
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _vehicleCtrl,
                    decoration: const InputDecoration(
                        labelText: 'Véhicule'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextFormField(
                    controller: _plateCtrl,
                    decoration: const InputDecoration(
                        labelText: 'Immatriculation'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _destinationCtrl,
              decoration:
                  const InputDecoration(labelText: 'Destination'),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _purposeCtrl,
              decoration:
                  const InputDecoration(labelText: 'Motif *'),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Requis' : null,
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _missionCtrl,
                    decoration: const InputDecoration(
                        labelText: 'N° mission'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextFormField(
                    controller: _authRefCtrl,
                    decoration: const InputDecoration(
                        labelText: 'Réf. autorisation'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _quantityCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                  labelText: 'Quantité demandée (L) *'),
              validator: (v) =>
                  (double.tryParse(v?.replaceAll(',', '.') ?? '') == null)
                      ? 'Nombre requis'
                      : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _notesCtrl,
              maxLines: 3,
              decoration: const InputDecoration(labelText: 'Notes'),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _saving ? null : _submit,
              icon: _saving
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.white))
                  : const Icon(Icons.save),
              label: Text(_saving ? 'Enregistrement…' : 'Enregistrer'),
            ),
          ],
        ),
      ),
    );
  }
}

/* ==================== DÉTAIL ==================== */

class FuelExitDetailScreen extends StatefulWidget {
  final int exitId;
  const FuelExitDetailScreen({super.key, required this.exitId});

  @override
  State<FuelExitDetailScreen> createState() => _FuelExitDetailScreenState();
}

class _FuelExitDetailScreenState extends State<FuelExitDetailScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = ApiService.getFuelDetail('exits', widget.exitId);
  }

  Future<void> _refresh() async {
    setState(
        () => _future = ApiService.getFuelDetail('exits', widget.exitId));
    await _future;
  }

  Future<void> _quantityAction({
    required String title,
    required String hint,
    required String action,
    required String field,
    required FuelExit e,
    double? initial,
  }) async {
    final ctrl = TextEditingController(
        text: initial != null ? initial.toStringAsFixed(0) : '');
    final value = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(title),
        content: TextField(
          controller: ctrl,
          keyboardType: TextInputType.number,
          autofocus: true,
          decoration: InputDecoration(hintText: hint, suffixText: 'L'),
        ),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Annuler')),
          FilledButton(
              onPressed: () => Navigator.pop(ctx, ctrl.text),
              child: const Text('Confirmer')),
        ],
      ),
    );
    if (value == null) return;
    final qty = double.tryParse(value.replaceAll(',', '.'));
    if (qty == null || qty <= 0) return;
    final res = await ApiService.fuelExitAction(e.id, action, {field: qty});
    if (!mounted) return;
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(res['message'] ?? '')));
    _refresh();
  }

  Future<void> _reject(FuelExit e) async {
    final reasonCtrl = TextEditingController();
    final reason = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Motif du rejet'),
        content: TextField(
          controller: reasonCtrl,
          maxLines: 3,
          autofocus: true,
          decoration:
              const InputDecoration(hintText: 'Motif obligatoire'),
        ),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Annuler')),
          FilledButton(
              style:
                  FilledButton.styleFrom(backgroundColor: Colors.red),
              onPressed: () => Navigator.pop(ctx, reasonCtrl.text),
              child: const Text('Rejeter')),
        ],
      ),
    );
    if (reason == null || reason.trim().isEmpty) return;
    final res = await ApiService.fuelExitAction(
        e.id, 'reject', {'reason': reason.trim()});
    if (!mounted) return;
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(res['message'] ?? '')));
    _refresh();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Bon de sortie')),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return FuelErrorView(error: snap.error!, onRetry: _refresh);
          }
          final data = snap.data!;
          final x =
              FuelExit.fromJson(data['exit'] as Map<String, dynamic>);
          final available = data['available_liters'];

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(x.exitNumber,
                        style: const TextStyle(
                            fontWeight: FontWeight.w700, fontSize: 18)),
                  ),
                  FuelStatusChip(x.status),
                  IconButton(
                    tooltip: 'PDF',
                    icon: const Icon(Icons.picture_as_pdf,
                        color: Colors.red),
                    onPressed: () => openFuelPdf(
                        context, 'exit', x.id, x.exitNumber),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      fuelInfoRow('Date', x.operationDate),
                      fuelInfoRow('Magasin', x.warehouseName),
                      fuelInfoRow('Carburant', x.fuelLabel),
                      fuelInfoRow(
                          'Stock disponible', fuelLiters(available),
                          bold: true),
                      const Divider(),
                      fuelInfoRow(
                          'Bénéficiaire', x.beneficiaryName),
                      fuelInfoRow('Véhicule', x.vehiclePlate),
                      fuelInfoRow('Destination', x.destination),
                      fuelInfoRow('Motif', x.purpose),
                      fuelInfoRow('Mission', x.missionNumber),
                      const Divider(),
                      fuelInfoRow('Demandé',
                          fuelLiters(x.quantityRequested)),
                      fuelInfoRow('Autorisé',
                          fuelLiters(x.quantityAuthorized)),
                      fuelInfoRow('Délivré',
                          fuelLiters(x.quantityDelivered),
                          bold: true),
                      if (x.rejectionReason != null)
                        fuelInfoRow(
                            'Motif de rejet', x.rejectionReason),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: FuelAttachmentsSection(
                    type: 'exit',
                    subjectId: x.id,
                    attachments: x.attachments,
                    onChanged: _refresh,
                  ),
                ),
              ),
              const SizedBox(height: 20),
              if (x.status == 'draft') ...[
                ElevatedButton.icon(
                  onPressed: () => _quantityAction(
                    title: 'Autoriser la sortie',
                    hint: 'Quantité autorisée',
                    action: 'authorize',
                    field: 'quantity_authorized',
                    e: x,
                    initial: x.quantityRequested,
                  ),
                  icon: const Icon(Icons.verified),
                  label: const Text('Autoriser'),
                ),
                const SizedBox(height: 8),
                OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.red),
                  onPressed: () => _reject(x),
                  icon: const Icon(Icons.cancel),
                  label: const Text('Rejeter'),
                ),
              ],
              if (x.status == 'authorized') ...[
                ElevatedButton.icon(
                  onPressed: () => _quantityAction(
                    title: 'Valider la livraison',
                    hint: 'Quantité réellement délivrée',
                    action: 'validate',
                    field: 'quantity_delivered',
                    e: x,
                    initial: x.quantityAuthorized,
                  ),
                  icon: const Icon(Icons.local_gas_station),
                  label: const Text('Valider la livraison'),
                ),
                const SizedBox(height: 8),
                OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.red),
                  onPressed: () => _reject(x),
                  icon: const Icon(Icons.cancel),
                  label: const Text('Rejeter'),
                ),
              ],
              const SizedBox(height: 24),
            ],
          );
        },
      ),
    );
  }
}
