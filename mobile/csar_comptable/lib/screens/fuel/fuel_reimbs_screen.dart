import 'package:flutter/material.dart';
import '../../models/fuel.dart';
import '../../services/api_service.dart';
import 'fuel_widgets.dart';

/// Onglet « Remboursements » : demandes d'achat de carburant par les agents.
class FuelReimbsScreen extends StatefulWidget {
  const FuelReimbsScreen({super.key});

  @override
  State<FuelReimbsScreen> createState() => _FuelReimbsScreenState();
}

class _FuelReimbsScreenState extends State<FuelReimbsScreen> {
  late Future<List<FuelReimbursement>> _future;
  String? _status;
  String _query = '';
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<List<FuelReimbursement>> _load() =>
      ApiService.getFuelReimbursements(status: _status, query: _query);

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
                      hintText: 'Référence, agent, reçu, immat…',
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
                    DropdownMenuItem(value: 'submitted', child: Text('Soumise')),
                    DropdownMenuItem(
                        value: 'in_review', child: Text('En instruction')),
                    DropdownMenuItem(
                        value: 'approved', child: Text('Approuvée')),
                    DropdownMenuItem(value: 'paid', child: Text('Payée')),
                    DropdownMenuItem(value: 'closed', child: Text('Clôturée')),
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
              child: FutureBuilder<List<FuelReimbursement>>(
                future: _future,
                builder: (context, snap) {
                  if (snap.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  if (snap.hasError) {
                    return FuelErrorView(
                        error: snap.error!, onRetry: _refresh);
                  }
                  final reimbs = snap.data!;
                  if (reimbs.isEmpty) {
                    return ListView(
                      children: const [
                        SizedBox(height: 80),
                        Icon(Icons.inbox_outlined,
                            size: 48, color: Colors.black26),
                        Center(child: Text('Aucune demande.')),
                      ],
                    );
                  }
                  return ListView.builder(
                    padding: const EdgeInsets.only(bottom: 80),
                    itemCount: reimbs.length,
                    itemBuilder: (context, i) {
                      final r = reimbs[i];
                      return Card(
                        margin: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 4),
                        child: ListTile(
                          leading: const Icon(Icons.payments_outlined,
                              color: Color(0xFF0D47A1)),
                          title: Row(
                            children: [
                              Expanded(
                                child: Text(r.reference,
                                    style: const TextStyle(
                                        fontWeight: FontWeight.w600)),
                              ),
                              if (r.hasDuplicate)
                                const Tooltip(
                                  message: 'Doublon potentiel',
                                  child: Icon(Icons.warning_amber,
                                      color: Colors.orange, size: 18),
                                ),
                            ],
                          ),
                          subtitle: Text(
                            '${r.agentName}\n${fuelAmount(r.amountRequested)} demandés'
                            '${r.amountPaid != null ? ' · ${fuelAmount(r.amountPaid)} payés' : ''}',
                          ),
                          isThreeLine: true,
                          trailing: FuelStatusChip(r.status),
                          onTap: () async {
                            await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) =>
                                    FuelReimbDetailScreen(reimbId: r.id),
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
            MaterialPageRoute(builder: (_) => const FuelReimbFormScreen()),
          );
          if (created == true) _refresh();
        },
        icon: const Icon(Icons.add),
        label: const Text('Demande'),
      ),
    );
  }
}

/* ==================== FORMULAIRE ==================== */

class FuelReimbFormScreen extends StatefulWidget {
  const FuelReimbFormScreen({super.key});

  @override
  State<FuelReimbFormScreen> createState() => _FuelReimbFormScreenState();
}

class _FuelReimbFormScreenState extends State<FuelReimbFormScreen> {
  final _formKey = GlobalKey<FormState>();
  bool _saving = false;

  final _agentCtrl = TextEditingController();
  final _serviceCtrl = TextEditingController();
  final _purposeCtrl = TextEditingController();
  final _activityRefCtrl = TextEditingController();
  final _vehicleCtrl = TextEditingController();
  final _plateCtrl = TextEditingController();
  final _claimedCtrl = TextEditingController();
  final _requestedCtrl = TextEditingController();
  final _litersCtrl = TextEditingController();
  final _receiptCtrl = TextEditingController();
  final _advanceCtrl = TextEditingController();

  String? _activityType;
  DateTime _expenseDate = DateTime.now();

  @override
  void dispose() {
    for (final c in [
      _agentCtrl, _serviceCtrl, _purposeCtrl, _activityRefCtrl,
      _vehicleCtrl, _plateCtrl, _claimedCtrl, _requestedCtrl,
      _litersCtrl, _receiptCtrl, _advanceCtrl,
    ]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _pickDate() async {
    final d = await showDatePicker(
      context: context,
      initialDate: _expenseDate,
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
    );
    if (d != null) setState(() => _expenseDate = d);
  }

  String get _dateStr =>
      '${_expenseDate.year}-${_expenseDate.month.toString().padLeft(2, '0')}-${_expenseDate.day.toString().padLeft(2, '0')}';

  double? _parse(String s) =>
      s.trim().isEmpty ? null : double.tryParse(s.replaceAll(',', '.'));

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final res = await ApiService.createFuelReimbursement({
      'agent_name': _agentCtrl.text.trim(),
      if (_serviceCtrl.text.trim().isNotEmpty)
        'service': _serviceCtrl.text.trim(),
      'expense_date': _dateStr,
      'purpose': _purposeCtrl.text.trim(),
      if (_activityType != null) 'activity_type': _activityType,
      if (_activityRefCtrl.text.trim().isNotEmpty)
        'activity_ref': _activityRefCtrl.text.trim(),
      if (_vehicleCtrl.text.trim().isNotEmpty)
        'vehicle_label': _vehicleCtrl.text.trim(),
      if (_plateCtrl.text.trim().isNotEmpty)
        'vehicle_plate': _plateCtrl.text.trim(),
      'amount_claimed': _parse(_claimedCtrl.text),
      'amount_requested': _parse(_requestedCtrl.text),
      if (_parse(_litersCtrl.text) != null)
        'quantity_liters': _parse(_litersCtrl.text),
      if (_receiptCtrl.text.trim().isNotEmpty)
        'receipt_ref': _receiptCtrl.text.trim(),
      if (_parse(_advanceCtrl.text) != null)
        'advance_amount': _parse(_advanceCtrl.text),
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
      appBar: AppBar(title: const Text('Demande de remboursement')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _agentCtrl,
              decoration:
                  const InputDecoration(labelText: 'Agent demandeur *'),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Requis' : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _serviceCtrl,
              decoration:
                  const InputDecoration(labelText: 'Service'),
            ),
            const SizedBox(height: 12),
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Date de la dépense'),
              subtitle: Text(_dateStr),
              trailing: const Icon(Icons.calendar_today),
              onTap: _pickDate,
            ),
            TextFormField(
              controller: _purposeCtrl,
              decoration:
                  const InputDecoration(labelText: 'Motif de la dépense *'),
              validator: (v) =>
                  (v == null || v.trim().isEmpty) ? 'Requis' : null,
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: DropdownButtonFormField<String>(
                    initialValue: _activityType,
                    decoration: const InputDecoration(
                        labelText: "Type d'activité"),
                    items: const [
                      DropdownMenuItem(
                          value: 'mission', child: Text('Mission')),
                      DropdownMenuItem(
                          value: 'atelier', child: Text('Atelier')),
                      DropdownMenuItem(
                          value: 'activite', child: Text('Activité')),
                    ],
                    onChanged: (v) =>
                        setState(() => _activityType = v),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextFormField(
                    controller: _activityRefCtrl,
                    decoration: const InputDecoration(
                        labelText: 'Réf. activité'),
                  ),
                ),
              ],
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
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _claimedCtrl,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                        labelText: 'Montant déclaré *'),
                    validator: (v) =>
                        _parse(v ?? '') == null ? 'Nombre requis' : null,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextFormField(
                    controller: _requestedCtrl,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                        labelText: 'Montant demandé *'),
                    validator: (v) =>
                        _parse(v ?? '') == null ? 'Nombre requis' : null,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _litersCtrl,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                        labelText: 'Litres achetés'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextFormField(
                    controller: _receiptCtrl,
                    decoration: const InputDecoration(
                        labelText: 'N° reçu / facture'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _advanceCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                  labelText: 'Avance consentie (FCFA, optionnel)'),
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

class FuelReimbDetailScreen extends StatefulWidget {
  final int reimbId;
  const FuelReimbDetailScreen({super.key, required this.reimbId});

  @override
  State<FuelReimbDetailScreen> createState() =>
      _FuelReimbDetailScreenState();
}

class _FuelReimbDetailScreenState extends State<FuelReimbDetailScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = ApiService.getFuelDetail('reimbursements', widget.reimbId);
  }

  Future<void> _refresh() async {
    setState(() => _future =
        ApiService.getFuelDetail('reimbursements', widget.reimbId));
    await _future;
  }

  Future<void> _action(FuelReimbursement r, String action,
      {Map<String, dynamic> body = const {}}) async {
    final res = await ApiService.fuelReimbAction(r.id, action, body);
    if (!mounted) return;
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(res['message'] ?? '')));
    _refresh();
  }

  Future<void> _amountAction(FuelReimbursement r, String title,
      String field, String action, double? initial) async {
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
          decoration: const InputDecoration(suffixText: 'FCFA'),
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
    final amount = double.tryParse(value.replaceAll(',', '.'));
    if (amount == null || amount <= 0) return;
    _action(r, action, body: {field: amount});
  }

  Future<void> _reject(FuelReimbursement r) async {
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
    _action(r, 'reject', body: {'reason': reason.trim()});
  }

  Future<void> _pay(FuelReimbursement r) async {
    final refCtrl = TextEditingController();
    DateTime payDate = DateTime.now();
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDlg) => AlertDialog(
          title: const Text('Enregistrer le paiement'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: refCtrl,
                autofocus: true,
                decoration: const InputDecoration(
                    labelText: 'Référence du paiement *',
                    hintText: 'Virement, chèque, espèces…'),
              ),
              const SizedBox(height: 12),
              ListTile(
                contentPadding: EdgeInsets.zero,
                title: const Text('Date du paiement'),
                subtitle: Text(
                    '${payDate.day.toString().padLeft(2, '0')}/${payDate.month.toString().padLeft(2, '0')}/${payDate.year}'),
                trailing: const Icon(Icons.calendar_today),
                onTap: () async {
                  final d = await showDatePicker(
                    context: ctx,
                    initialDate: payDate,
                    firstDate: DateTime(2020),
                    lastDate: DateTime.now(),
                  );
                  if (d != null) setDlg(() => payDate = d);
                },
              ),
            ],
          ),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(ctx, false),
                child: const Text('Annuler')),
            FilledButton(
                onPressed: () => Navigator.pop(ctx, true),
                child: const Text('Payer')),
          ],
        ),
      ),
    );
    if (ok != true || refCtrl.text.trim().isEmpty) return;
    final dateStr =
        '${payDate.year}-${payDate.month.toString().padLeft(2, '0')}-${payDate.day.toString().padLeft(2, '0')}';
    _action(r, 'pay',
        body: {'payment_ref': refCtrl.text.trim(), 'payment_date': dateStr});
  }

  Future<void> _commentAction(
      FuelReimbursement r, String title, String action) async {
    final ctrl = TextEditingController();
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(title),
        content: TextField(
          controller: ctrl,
          maxLines: 3,
          autofocus: true,
          decoration:
              const InputDecoration(hintText: 'Observation (optionnel)'),
        ),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Annuler')),
          FilledButton(
              onPressed: () => Navigator.pop(ctx, true),
              child: const Text('Confirmer')),
        ],
      ),
    );
    if (ok != true) return;
    _action(r, action,
        body: {if (ctrl.text.trim().isNotEmpty) 'comment': ctrl.text.trim()});
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Remboursement carburant')),
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
          final r = FuelReimbursement.fromJson(
              data['reimbursement'] as Map<String, dynamic>);
          final canDecide = data['can_decide'] == true;

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              if (r.hasDuplicate)
                Card(
                  color: Colors.orange.shade50,
                  child: ListTile(
                    leading: const Icon(Icons.warning_amber,
                        color: Colors.orange),
                    title: const Text('Doublon potentiel'),
                    subtitle:
                        Text('Semblable à la demande ${r.duplicateOfRef}'),
                  ),
                ),
              Row(
                children: [
                  Expanded(
                    child: Text(r.reference,
                        style: const TextStyle(
                            fontWeight: FontWeight.w700, fontSize: 18)),
                  ),
                  FuelStatusChip(r.status),
                  IconButton(
                    tooltip: 'PDF',
                    icon: const Icon(Icons.picture_as_pdf,
                        color: Colors.red),
                    onPressed: () => openFuelPdf(
                        context, 'reimb', r.id, r.reference),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      fuelInfoRow('Agent', r.agentName, bold: true),
                      fuelInfoRow('Service', r.service),
                      fuelInfoRow('Date dépense', r.expenseDate),
                      fuelInfoRow('Motif', r.purpose),
                      fuelInfoRow('Reçu', r.receiptRef),
                      const Divider(),
                      fuelInfoRow(
                          'Déclaré', fuelAmount(r.amountClaimed)),
                      fuelInfoRow(
                          'Demandé', fuelAmount(r.amountRequested),
                          bold: true),
                      fuelInfoRow(
                          'Approuvé', fuelAmount(r.amountApproved)),
                      fuelInfoRow('Payé', fuelAmount(r.amountPaid),
                          bold: true),
                      if (r.advanceAmount != null) ...[
                        const Divider(),
                        fuelInfoRow(
                            'Avance', fuelAmount(r.advanceAmount)),
                        fuelInfoRow('Justifié',
                            fuelAmount(r.justifiedAmount)),
                        fuelInfoRow('Complément',
                            fuelAmount(r.complementAmount)),
                      ],
                      if (r.rejectionReason != null)
                        fuelInfoRow(
                            'Motif de rejet', r.rejectionReason),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: FuelAttachmentsSection(
                    type: 'reimb',
                    subjectId: r.id,
                    attachments: r.attachments,
                    onChanged: _refresh,
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // ——— Workflow ———
              if (r.status == 'draft')
                ElevatedButton.icon(
                  onPressed: () => _action(r, 'submit'),
                  icon: const Icon(Icons.send),
                  label: const Text('Soumettre la demande'),
                ),
              if (r.status == 'submitted')
                ElevatedButton.icon(
                  onPressed: () => _commentAction(
                      r, "Mettre en instruction", 'review'),
                  icon: const Icon(Icons.rate_review),
                  label: const Text('Instruire'),
                ),
              if (r.status == 'in_review' && canDecide)
                ElevatedButton.icon(
                  onPressed: () => _amountAction(
                      r, 'Approuver la demande', 'amount_approved',
                      'approve', r.amountRequested),
                  icon: const Icon(Icons.check_circle),
                  label: const Text('Approuver (admin)'),
                ),
              if (r.status == 'approved' && canDecide)
                ElevatedButton.icon(
                  onPressed: () => _pay(r),
                  icon: const Icon(Icons.payments),
                  label: const Text('Enregistrer le paiement (admin)'),
                ),
              if (r.status == 'paid')
                ElevatedButton.icon(
                  onPressed: () => _action(r, 'close'),
                  icon: const Icon(Icons.lock),
                  label: const Text('Clôturer le dossier'),
                ),
              if (['draft', 'submitted', 'in_review']
                  .contains(r.status)) ...[
                const SizedBox(height: 8),
                OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.red),
                  onPressed: () => _reject(r),
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
