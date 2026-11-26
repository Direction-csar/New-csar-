import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../models/stock_operation.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/location_service.dart';
import 'package:provider/provider.dart';

/// Détail d'une opération + actions inspecteur :
/// transmettre au magasinier, vérifier, signaler une anomalie (photo + GPS), rejeter.
class OperationDetailScreen extends StatefulWidget {
  final int operationId;
  const OperationDetailScreen({super.key, required this.operationId});

  @override
  State<OperationDetailScreen> createState() => _OperationDetailScreenState();
}

class _OperationDetailScreenState extends State<OperationDetailScreen> {
  StockOperation? _op;
  bool _loading = true;
  String? _error;
  bool _busy = false;

  String? get _token => context.read<AuthService>().token;

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
    final res = await ApiService.getOperation(_token!, widget.operationId);
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (res['success'] == true && res['operation'] != null) {
        _op = StockOperation.fromJson(
            Map<String, dynamic>.from(res['operation'] as Map));
      } else {
        _error = res['message'] as String? ?? 'Opération introuvable.';
      }
    });
  }

  Future<void> _run(Future<Map<String, dynamic>> Function() call, String okMsg) async {
    setState(() => _busy = true);
    final res = await call();
    if (!mounted) return;
    setState(() => _busy = false);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(res['success'] == true
          ? (res['message'] as String? ?? okMsg)
          : (res['message'] as String? ?? 'Action refusée.')),
      backgroundColor: res['success'] == true ? Colors.green : Colors.red,
    ));
    if (res['success'] == true) _load();
  }

  // ------------------------------------------------------------------
  // Transmettre les consignes au magasinier
  // ------------------------------------------------------------------
  Future<void> _transmit() async {
    final op = _op!;
    final warehouseId = (op.warehouse?['id'] as num?)?.toInt() ??
        (op.warehouse?['warehouse_id'] as num?)?.toInt();
    if (warehouseId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Magasin non identifié sur l\'opération.')));
      return;
    }

    List<Map<String, dynamic>> candidats = [];
    final res = await ApiService.getMagasinierCandidates(_token!, warehouseId);
    if (res['success'] == true) {
      candidats = (res['magasiniers'] as List? ?? [])
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
    }
    if (!mounted) return;
    if (candidats.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Aucun magasinier actif rattaché à ce magasin.')));
      return;
    }

    int? selected = candidats.first['id'] as int?;
    final commentCtrl = TextEditingController();
    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheet) => Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text('Transmettre au magasinier',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
              const SizedBox(height: 16),
              DropdownButtonFormField<int>(
                initialValue: selected,
                decoration: const InputDecoration(
                  labelText: 'Magasinier destinataire',
                  border: OutlineInputBorder(),
                ),
                items: candidats
                    .map((m) => DropdownMenuItem<int>(
                          value: m['id'] as int,
                          child: Text('${m['name']} — ${m['email']}'),
                        ))
                    .toList(),
                onChanged: (v) => setSheet(() => selected = v),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: commentCtrl,
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText: 'Consignes opérationnelles (optionnel)',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 8),
              const Text('La position GPS sera jointe automatiquement.',
                  style: TextStyle(fontSize: 12, color: Colors.grey)),
              const SizedBox(height: 16),
              FilledButton(
                onPressed: () => Navigator.of(ctx).pop(true),
                child: const Text('Transmettre'),
              ),
            ],
          ),
        ),
      ),
    );
    if (confirmed != true || selected == null) return;

    final pos = await LocationService.currentPosition();
    await _run(
      () => ApiService.transmit(_token!, op.id, {
        'assigned_magasinier_id': selected,
        'inspector_comment': ?(commentCtrl.text.trim().isNotEmpty ? commentCtrl.text.trim() : null),
        'latitude': ?pos?.lat,
        'longitude': ?pos?.lng,
      }),
      'Consignes transmises.',
    );
  }

  // ------------------------------------------------------------------
  // Vérifier l'opération exécutée
  // ------------------------------------------------------------------
  Future<void> _verify() async {
    final commentCtrl = TextEditingController();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Vérifier l\'opération'),
        content: TextField(
          controller: commentCtrl,
          maxLines: 3,
          decoration: const InputDecoration(
            labelText: 'Observation de vérification (optionnel)',
            border: OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Vérifier')),
        ],
      ),
    );
    if (confirmed != true) return;
    final pos = await LocationService.currentPosition();
    await _run(
      () => ApiService.verify(_token!, _op!.id, {
        'inspector_comment': ?(commentCtrl.text.trim().isNotEmpty ? commentCtrl.text.trim() : null),
        'latitude': ?pos?.lat,
        'longitude': ?pos?.lng,
      }),
      'Opération vérifiée — intégrée au stock officiel.',
    );
  }

  // ------------------------------------------------------------------
  // Signaler une anomalie (rapport + photo + GPS)
  // ------------------------------------------------------------------
  Future<void> _anomaly() async {
    final reportCtrl = TextEditingController();
    File? photo;
    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheet) => Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text('Signaler une anomalie',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              TextField(
                controller: reportCtrl,
                maxLines: 4,
                decoration: const InputDecoration(
                  labelText: 'Description de l\'anomalie *',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              OutlinedButton.icon(
                icon: const Icon(Icons.photo_camera_outlined),
                label: Text(photo == null ? 'Joindre une photo' : 'Photo jointe ✓'),
                onPressed: () async {
                  final picked = await ImagePicker()
                      .pickImage(source: ImageSource.camera, imageQuality: 70, maxWidth: 1600);
                  if (picked != null) {
                    setSheet(() => photo = File(picked.path));
                  }
                },
              ),
              if (photo != null)
                Padding(
                  padding: const EdgeInsets.only(top: 8),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.file(photo!, height: 140, fit: BoxFit.cover),
                  ),
                ),
              const SizedBox(height: 8),
              const Text('La position GPS sera jointe automatiquement.',
                  style: TextStyle(fontSize: 12, color: Colors.grey)),
              const SizedBox(height: 16),
              FilledButton(
                style: FilledButton.styleFrom(backgroundColor: Colors.red),
                onPressed: reportCtrl.text.trim().isEmpty
                    ? null
                    : () => Navigator.of(ctx).pop(true),
                child: const Text('Envoyer l\'anomalie'),
              ),
            ],
          ),
        ),
      ),
    );
    if (confirmed != true || reportCtrl.text.trim().isEmpty) return;

    String? photoBase64;
    if (photo != null) {
      final bytes = await photo!.readAsBytes();
      photoBase64 = base64Encode(bytes);
    }
    final pos = await LocationService.currentPosition();
    await _run(
      () => ApiService.reportAnomaly(_token!, _op!.id, {
        'anomaly_report': reportCtrl.text.trim(),
        'photo_base64': ?photoBase64,
        'latitude': ?pos?.lat,
        'longitude': ?pos?.lng,
      }),
      'Anomalie signalée.',
    );
  }

  // ------------------------------------------------------------------
  // Rejeter l'opération
  // ------------------------------------------------------------------
  Future<void> _reject() async {
    final reasonCtrl = TextEditingController();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Rejeter l\'opération'),
        content: TextField(
          controller: reasonCtrl,
          maxLines: 3,
          decoration: const InputDecoration(
            labelText: 'Motif du rejet *',
            border: OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: Colors.red),
            onPressed: reasonCtrl.text.trim().isEmpty ? null : () => Navigator.pop(ctx, true),
            child: const Text('Rejeter'),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    await _run(
      () => ApiService.reject(_token!, _op!.id, reasonCtrl.text.trim()),
      'Opération rejetée.',
    );
  }

  // ------------------------------------------------------------------
  @override
  Widget build(BuildContext context) {
    if (_loading) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    final op = _op;
    if (op == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Opération')),
        body: Center(child: Text(_error ?? 'Introuvable')),
      );
    }

    return Scaffold(
      appBar: AppBar(title: Text(op.reference)),
      body: Stack(
        children: [
          ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _header(op),
              const SizedBox(height: 12),
              _section('Détails', [
                _row('Type', op.typeLabel),
                _row('Magasin', op.warehouseName),
                if (op.destinationWarehouse != null)
                  _row('Destination',
                      (op.destinationWarehouse!['name'] ?? '') as String),
                _row('Produit', [
                  op.productName,
                  if (op.format != null) '(${op.format})',
                ].whereType<String>().join(' ')),
                _row('Quantité demandée',
                    '${op.quantityRequested ?? '—'} ${op.productUnit ?? ''}'),
                _row('Quantité exécutée',
                    '${op.quantityExecuted ?? '—'}${op.quantityExecutedKg != null ? ' (${op.quantityExecutedKg} kg)' : ''}'),
                _row('Magasinier', (op.magasinier?['name'] ?? '—') as String),
                _row('Exécuté par', (op.executor?['name'] ?? '—') as String),
                _row('Vérifié par', (op.verifier?['name'] ?? '—') as String),
                _row('Créée le', op.createdAt ?? '—'),
                _row('Exécutée le', op.executedAt ?? '—'),
                _row('Vérifiée le', op.verifiedAt ?? '—'),
              ]),
              if ((op.dgInstruction ?? '').isNotEmpty) ...[
                const SizedBox(height: 12),
                _section('Instruction DG', [_text(op.dgInstruction!)]),
              ],
              if ((op.dtlInstruction ?? '').isNotEmpty) ...[
                const SizedBox(height: 12),
                _section('Instruction DTL', [_text(op.dtlInstruction!)]),
              ],
              if ((op.inspectorComment ?? '').isNotEmpty) ...[
                const SizedBox(height: 12),
                _section('Vos consignes', [_text(op.inspectorComment!)]),
              ],
              if ((op.observation ?? '').isNotEmpty) ...[
                const SizedBox(height: 12),
                _section('Observation magasinier', [_text(op.observation!)]),
              ],
              if ((op.anomalyReport ?? '').isNotEmpty) ...[
                const SizedBox(height: 12),
                _section('Anomalie', [_text(op.anomalyReport!)]),
              ],
              if ((op.rejectionReason ?? '').isNotEmpty) ...[
                const SizedBox(height: 12),
                _section('Motif de rejet', [_text(op.rejectionReason!)]),
              ],
            ],
          ),
          if (_busy)
            Container(
              color: Colors.black26,
              child: const Center(child: CircularProgressIndicator()),
            ),
        ],
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Wrap(
            spacing: 8,
            runSpacing: 8,
            alignment: WrapAlignment.center,
            children: [
              if (op.canTransmit)
                FilledButton.icon(
                  onPressed: _busy ? null : _transmit,
                  icon: const Icon(Icons.send),
                  label: const Text('Transmettre'),
                ),
              if (op.canVerify)
                FilledButton.icon(
                  style: FilledButton.styleFrom(backgroundColor: Colors.green.shade700),
                  onPressed: _busy ? null : _verify,
                  icon: const Icon(Icons.check_circle_outline),
                  label: const Text('Vérifier'),
                ),
              if (op.canReportAnomaly)
                OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(foregroundColor: Colors.red),
                  onPressed: _busy ? null : _anomaly,
                  icon: const Icon(Icons.report_problem_outlined),
                  label: const Text('Anomalie'),
                ),
              if (op.canReject)
                TextButton.icon(
                  style: TextButton.styleFrom(foregroundColor: Colors.red),
                  onPressed: _busy ? null : _reject,
                  icon: const Icon(Icons.cancel_outlined),
                  label: const Text('Rejeter'),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _header(StockOperation op) {
    return Card(
      color: Theme.of(context).colorScheme.primaryContainer,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(op.reference,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                ),
                Chip(label: Text(op.statusLabel)),
              ],
            ),
            const SizedBox(height: 8),
            Text(op.warehouseName,
                style: const TextStyle(fontWeight: FontWeight.w600)),
          ],
        ),
      ),
    );
  }

  Widget _section(String title, List<Widget> children) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
            const Divider(height: 16),
            ...children,
          ],
        ),
      ),
    );
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
              width: 140,
              child: Text(label, style: const TextStyle(color: Colors.black54, fontSize: 13))),
          Expanded(child: Text(value.isEmpty ? '—' : value)),
        ],
      ),
    );
  }

  Widget _text(String value) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 4),
        child: Text(value),
      );
}
