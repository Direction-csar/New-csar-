import 'package:flutter/material.dart';
import '../../models/fuel.dart';
import '../../models/warehouse.dart';
import '../../services/api_service.dart';
import 'fuel_widgets.dart';

/// Onglet « Entrées » : liste filtrable + saisie + fiche détail.
class FuelEntriesScreen extends StatefulWidget {
  const FuelEntriesScreen({super.key});

  @override
  State<FuelEntriesScreen> createState() => _FuelEntriesScreenState();
}

class _FuelEntriesScreenState extends State<FuelEntriesScreen> {
  late Future<List<FuelEntry>> _future;
  String? _status;
  String _query = '';
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<List<FuelEntry>> _load() =>
      ApiService.getFuelEntries(status: _status, query: _query);

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
                      hintText: 'Référence, fournisseur, facture…',
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
                        value: 'validated', child: Text('Validée')),
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
              child: FutureBuilder<List<FuelEntry>>(
                future: _future,
                builder: (context, snap) {
                  if (snap.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  if (snap.hasError) {
                    return FuelErrorView(
                        error: snap.error!, onRetry: _refresh);
                  }
                  final entries = snap.data!;
                  if (entries.isEmpty) {
                    return ListView(
                      children: const [
                        SizedBox(height: 80),
                        Icon(Icons.inbox_outlined,
                            size: 48, color: Colors.black26),
                        Center(child: Text('Aucune entrée.')),
                      ],
                    );
                  }
                  return ListView.builder(
                    padding: const EdgeInsets.only(bottom: 80),
                    itemCount: entries.length,
                    itemBuilder: (context, i) {
                      final e = entries[i];
                      return Card(
                        margin: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 4),
                        child: ListTile(
                          leading: const Icon(Icons.south,
                              color: Color(0xFF0D47A1)),
                          title: Text(e.reference,
                              style: const TextStyle(
                                  fontWeight: FontWeight.w600)),
                          subtitle: Text(
                            '${e.supplier} — ${e.fuelLabel}\n${fuelLiters(e.quantity)} · ${fuelAmount(e.totalAmount)}',
                          ),
                          isThreeLine: true,
                          trailing: FuelStatusChip(e.status),
                          onTap: () async {
                            await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) =>
                                    FuelEntryDetailScreen(entryId: e.id),
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
            MaterialPageRoute(builder: (_) => const FuelEntryFormScreen()),
          );
          if (created == true) _refresh();
        },
        icon: const Icon(Icons.add),
        label: const Text('Entrée'),
      ),
    );
  }
}

/* ==================== FORMULAIRE ==================== */

class FuelEntryFormScreen extends StatefulWidget {
  const FuelEntryFormScreen({super.key});

  @override
  State<FuelEntryFormScreen> createState() => _FuelEntryFormScreenState();
}

class _FuelEntryFormScreenState extends State<FuelEntryFormScreen> {
  final _formKey = GlobalKey<FormState>();
  bool _saving = false;
  List<Warehouse> _warehouses = [];

  final _supplierCtrl = TextEditingController();
  final _quantityCtrl = TextEditingController();
  final _priceCtrl = TextEditingController();
  final _invoiceCtrl = TextEditingController();
  final _noteRefCtrl = TextEditingController();
  final _fuelLabelCtrl = TextEditingController();
  final _fundingCtrl = TextEditingController();
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
      _supplierCtrl, _quantityCtrl, _priceCtrl, _invoiceCtrl,
      _noteRefCtrl, _fuelLabelCtrl, _fundingCtrl, _notesCtrl,
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

    final res = await ApiService.createFuelEntry({
      'operation_date': _dateStr,
      'warehouse_id': _warehouseId,
      'supplier': _supplierCtrl.text.trim(),
      'fuel_type': _fuelType,
      if (_fuelType == 'autre')
        'fuel_type_label': _fuelLabelCtrl.text.trim(),
      'quantity': double.parse(_quantityCtrl.text.replaceAll(',', '.')),
      'unit_price': double.parse(_priceCtrl.text.replaceAll(',', '.')),
      if (_invoiceCtrl.text.trim().isNotEmpty)
        'invoice_number': _invoiceCtrl.text.trim(),
      if (_noteRefCtrl.text.trim().isNotEmpty)
        'delivery_note_ref': _noteRefCtrl.text.trim(),
      if (_fundingCtrl.text.trim().isNotEmpty)
        'funding_source': _fundingCtrl.text.trim(),
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
      appBar: AppBar(title: const Text('Nouvelle entrée')),
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
            TextFormField(
              controller: _supplierCtrl,
              decoration:
                  const InputDecoration(labelText: 'Fournisseur *'),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Requis' : null,
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
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _quantityCtrl,
                    keyboardType: TextInputType.number,
                    decoration:
                        const InputDecoration(labelText: 'Quantité (L) *'),
                    validator: (v) =>
                        (double.tryParse(v?.replaceAll(',', '.') ?? '') ==
                                null)
                            ? 'Nombre requis'
                            : null,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextFormField(
                    controller: _priceCtrl,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                        labelText: 'Prix unitaire FCFA/L *'),
                    validator: (v) =>
                        (double.tryParse(v?.replaceAll(',', '.') ?? '') ==
                                null)
                            ? 'Nombre requis'
                            : null,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _invoiceCtrl,
              decoration:
                  const InputDecoration(labelText: 'N° facture'),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _noteRefCtrl,
              decoration: const InputDecoration(
                  labelText: 'Réf. bon de livraison'),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _fundingCtrl,
              decoration: const InputDecoration(
                  labelText: 'Source de financement'),
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

class FuelEntryDetailScreen extends StatefulWidget {
  final int entryId;
  const FuelEntryDetailScreen({super.key, required this.entryId});

  @override
  State<FuelEntryDetailScreen> createState() => _FuelEntryDetailScreenState();
}

class _FuelEntryDetailScreenState extends State<FuelEntryDetailScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = ApiService.getFuelDetail('entries', widget.entryId);
  }

  Future<void> _refresh() async {
    setState(() =>
        _future = ApiService.getFuelDetail('entries', widget.entryId));
    await _future;
  }

  Future<void> _validate(FuelEntry e) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Valider cette entrée ?'),
        content: Text(
            'Le stock sera crédité de ${fuelLiters(e.quantity)}.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Annuler')),
          FilledButton(
              onPressed: () => Navigator.pop(ctx, true),
              child: const Text('Valider')),
        ],
      ),
    );
    if (ok != true) return;
    final res =
        await ApiService.fuelEntryAction(e.id, 'validate', {});
    if (!mounted) return;
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(res['message'] ?? '')));
    _refresh();
  }

  Future<void> _reject(FuelEntry e) async {
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
    final res = await ApiService.fuelEntryAction(
        e.id, 'reject', {'reason': reason.trim()});
    if (!mounted) return;
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(res['message'] ?? '')));
    _refresh();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Entrée carburant')),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return FuelErrorView(error: snap.error!, onRetry: _refresh);
          }
          final e = FuelEntry.fromJson(
              snap.data!['entry'] as Map<String, dynamic>);

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(e.reference,
                        style: const TextStyle(
                            fontWeight: FontWeight.w700, fontSize: 18)),
                  ),
                  FuelStatusChip(e.status),
                  IconButton(
                    tooltip: 'PDF',
                    icon: const Icon(Icons.picture_as_pdf,
                        color: Colors.red),
                    onPressed: () => openFuelPdf(
                        context, 'entry', e.id, e.reference),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      fuelInfoRow('Date', e.operationDate),
                      fuelInfoRow('Magasin', e.warehouseName),
                      fuelInfoRow('Fournisseur', e.supplier),
                      fuelInfoRow('Carburant', e.fuelLabel),
                      fuelInfoRow('Quantité', fuelLiters(e.quantity),
                          bold: true),
                      fuelInfoRow(
                          'Prix unitaire', fuelAmount(e.unitPrice)),
                      fuelInfoRow(
                          'Total', fuelAmount(e.totalAmount),
                          bold: true),
                      fuelInfoRow('Facture', e.invoiceNumber),
                      if (e.rejectionReason != null)
                        fuelInfoRow(
                            'Motif de rejet', e.rejectionReason),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: FuelAttachmentsSection(
                    type: 'entry',
                    subjectId: e.id,
                    attachments: e.attachments,
                    onChanged: _refresh,
                  ),
                ),
              ),
              if (e.isDraft) ...[
                const SizedBox(height: 20),
                ElevatedButton.icon(
                  onPressed: () => _validate(e),
                  icon: const Icon(Icons.check_circle),
                  label: const Text('Valider (créditer le stock)'),
                ),
                const SizedBox(height: 8),
                OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.red),
                  onPressed: () => _reject(e),
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
