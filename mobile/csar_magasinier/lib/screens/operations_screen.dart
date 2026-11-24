import 'dart:convert';
import 'dart:io';
import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../models/stock_operation.dart';
import '../services/api_service.dart';
import 'signature_screen.dart';

/// Écran « Opérations » : instructions transmises par l'inspecteur
/// (circuit DG → DTL → Inspecteur → Magasinier).
/// Le magasinier enregistre ici l'opération réellement effectuée ;
/// le mouvement reste « en attente » jusqu'à vérification de l'inspecteur.
class OperationsScreen extends StatefulWidget {
  const OperationsScreen({super.key});

  @override
  State<OperationsScreen> createState() => _OperationsScreenState();
}

class _OperationsScreenState extends State<OperationsScreen> {
  static const green = Color(0xFF1B5E20);

  List<StockOperation> _operations = [];
  bool _loading = true;
  String? _error;
  String _filter = 'open'; // open | history

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
      final ops = await ApiService.getOperations(
        status: _filter == 'open' ? 'transmise_magasinier' : null,
      );
      if (!mounted) return;
      setState(() {
        _operations = ops;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _error = 'Impossible de charger les opérations. Vérifiez la connexion.';
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F6F4),
      appBar: AppBar(
        title: const Text('Opérations inspecteur'),
        backgroundColor: green,
        foregroundColor: Colors.white,
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _load),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 12, 12, 4),
            child: SegmentedButton<String>(
              segments: const [
                ButtonSegment(
                    value: 'open',
                    label: Text('À exécuter'),
                    icon: Icon(Icons.pending_actions, size: 18)),
                ButtonSegment(
                    value: 'history',
                    label: Text('Historique'),
                    icon: Icon(Icons.history, size: 18)),
              ],
              selected: {_filter},
              onSelectionChanged: (s) {
                _filter = s.first;
                _load();
              },
            ),
          ),
          Expanded(child: _body()),
        ],
      ),
    );
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(_error!, textAlign: TextAlign.center),
            const SizedBox(height: 12),
            FilledButton(onPressed: _load, child: const Text('Réessayer')),
          ],
        ),
      );
    }
    if (_operations.isEmpty) {
      return Center(
        child: Text(
          _filter == 'open'
              ? 'Aucune opération en attente.\nLes consignes de l\'inspecteur apparaîtront ici.'
              : 'Aucune opération.',
          textAlign: TextAlign.center,
          style: const TextStyle(color: Colors.black54),
        ),
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.all(12),
        itemCount: _operations.length,
        separatorBuilder: (context, index) => const SizedBox(height: 10),
        itemBuilder: (_, i) => _card(_operations[i]),
      ),
    );
  }

  Widget _card(StockOperation op) {
    final color = switch (op.status) {
      'transmise_magasinier' => Colors.blue.shade700,
      'executee' => Colors.purple.shade700,
      'verifiee' => Colors.green.shade700,
      'anomalie' => Colors.red.shade700,
      'rejetee' || 'annulee' => Colors.grey.shade600,
      _ => Colors.orange.shade800,
    };
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () async {
          final changed = await Navigator.push<bool>(
            context,
            MaterialPageRoute(
                builder: (_) => OperationDetailScreen(operation: op)),
          );
          if (changed == true) _load();
        },
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(op.reference,
                        style: const TextStyle(fontWeight: FontWeight.bold)),
                  ),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: color.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(op.statusLabel,
                        style: TextStyle(
                            fontSize: 11.5,
                            color: color,
                            fontWeight: FontWeight.w600)),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text('${op.typeLabel} — ${op.warehouseName}',
                  style: const TextStyle(fontWeight: FontWeight.w600)),
              const SizedBox(height: 4),
              Text(
                [
                  if (op.produit != null)
                    '${op.produit}${op.format != null ? ' (${op.format})' : ''}',
                  if (op.quantityRequested != null)
                    'demandé : ${op.quantityRequested} ${op.unite ?? ''}',
                  if (op.quantityExecuted != null)
                    'exécuté : ${op.quantityExecuted}',
                ].join(' · '),
                style: const TextStyle(fontSize: 12.5, color: Colors.black54),
              ),
              if ((op.inspectorComment ?? '').isNotEmpty) ...[
                const SizedBox(height: 4),
                Text('Consigne : ${op.inspectorComment}',
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 12, color: Colors.black45)),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

/// Détail d'une opération + formulaire d'exécution réelle.
class OperationDetailScreen extends StatefulWidget {
  final StockOperation operation;
  const OperationDetailScreen({super.key, required this.operation});

  @override
  State<OperationDetailScreen> createState() => _OperationDetailScreenState();
}

class _OperationDetailScreenState extends State<OperationDetailScreen> {
  static const green = Color(0xFF1B5E20);

  final _qtyCtrl = TextEditingController();
  final _kgCtrl = TextEditingController();
  final _obsCtrl = TextEditingController();
  final _positionCtrl = TextEditingController();
  String? _photoPath;
  Uint8List? _signatureBytes;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    // Pré-remplit avec la quantité demandée (modifiable = écart assumé)
    final op = widget.operation;
    if (op.quantityRequested != null) {
      _qtyCtrl.text = op.quantityRequested!.toStringAsFixed(0);
    }
    if (op.quantityRequestedKg != null) {
      _kgCtrl.text = op.quantityRequestedKg!.toStringAsFixed(0);
    }
  }

  @override
  void dispose() {
    _qtyCtrl.dispose();
    _kgCtrl.dispose();
    _obsCtrl.dispose();
    _positionCtrl.dispose();
    super.dispose();
  }

  Future<String?> _fileToBase64(String path) async {
    final bytes = await File(path).readAsBytes();
    return base64Encode(bytes);
  }

  Future<void> _pickPhoto() async {
    final picked = await ImagePicker()
        .pickImage(source: ImageSource.camera, imageQuality: 70, maxWidth: 1600);
    if (picked != null) setState(() => _photoPath = picked.path);
  }

  Future<void> _pickSignature() async {
    final bytes = await Navigator.push<Uint8List>(
      context,
      MaterialPageRoute(builder: (_) => const SignatureScreen()),
    );
    if (bytes != null) setState(() => _signatureBytes = bytes);
  }

  Future<void> _submit() async {
    final qty = double.tryParse(_qtyCtrl.text.replaceAll(',', '.'));
    if (qty == null || qty <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Quantité exécutée invalide.')));
      return;
    }
    setState(() => _saving = true);
    final op = widget.operation;
    // Référence idempotente : régénérée à chaque ouverture, stable pendant la saisie
    final clientRef = 'OP-${op.id}-${DateTime.now().millisecondsSinceEpoch}';
    final result = await ApiService.executeOperation(op.id, {
      'quantity_executed': qty,
      'quantity_executed_kg':
          double.tryParse(_kgCtrl.text.replaceAll(',', '.')),
      'position': _positionCtrl.text.trim().isEmpty ? null : _positionCtrl.text.trim(),
      'observation': _obsCtrl.text.trim().isEmpty ? null : _obsCtrl.text.trim(),
      'movement_date': DateTime.now().toIso8601String().split('T')[0],
      'client_ref': clientRef,
      'photo_base64': _photoPath != null ? await _fileToBase64(_photoPath!) : null,
      'signature_base64':
          _signatureBytes != null ? base64Encode(_signatureBytes!) : null,
    });
    if (!mounted) return;
    setState(() => _saving = false);

    if (result['success'] == true) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          title: const Text('Opération enregistrée'),
          content: const Text(
              'L\'opération est enregistrée et transmise à l\'inspecteur pour vérification. Elle comptera dans le stock officiel après confirmation.'),
          actions: [
            FilledButton(
              onPressed: () {
                Navigator.pop(ctx);
                Navigator.pop(context, true);
              },
              child: const Text('OK'),
            ),
          ],
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(result['message'] ?? 'Erreur lors de l\'exécution.'),
        backgroundColor: Colors.red,
      ));
    }
  }

  @override
  Widget build(BuildContext context) {
    final op = widget.operation;
    final canExecute = op.canExecute;

    return Scaffold(
      backgroundColor: const Color(0xFFF4F6F4),
      appBar: AppBar(
        title: Text(op.reference),
        backgroundColor: green,
        foregroundColor: Colors.white,
      ),
      body: ListView(
        padding: const EdgeInsets.all(14),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('${op.typeLabel} — ${op.statusLabel}',
                      style: const TextStyle(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  _row('Magasin', op.warehouseName),
                  if (op.destinationName != null)
                    _row('Destination', op.destinationName!),
                  _row('Produit',
                      '${op.produit ?? '—'}${op.format != null ? ' (${op.format})' : ''}'),
                  _row('Quantité demandée',
                      '${op.quantityRequested ?? '—'} ${op.unite ?? ''}'),
                  if (op.quantityRequestedKg != null)
                    _row('Soit', '${op.quantityRequestedKg} kg'),
                  _row('Inspecteur', op.inspectorName ?? '—'),
                  if ((op.instructions ?? '').isNotEmpty) ...[
                    const Divider(height: 20),
                    const Text('Instruction',
                        style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    Text(op.instructions!),
                  ],
                  if ((op.dtlComment ?? '').isNotEmpty) ...[
                    const SizedBox(height: 8),
                    const Text('Commentaire DTL',
                        style: TextStyle(fontWeight: FontWeight.bold)),
                    Text(op.dtlComment!),
                  ],
                  if ((op.inspectorComment ?? '').isNotEmpty) ...[
                    const SizedBox(height: 8),
                    const Text('Consignes de l\'inspecteur',
                        style: TextStyle(fontWeight: FontWeight.bold)),
                    Text(op.inspectorComment!),
                  ],
                  if ((op.anomalyReport ?? '').isNotEmpty) ...[
                    const SizedBox(height: 8),
                    const Text('Anomalie signalée',
                        style: TextStyle(
                            fontWeight: FontWeight.bold, color: Colors.red)),
                    Text(op.anomalyReport!),
                  ],
                  if ((op.rejectionReason ?? '').isNotEmpty) ...[
                    const SizedBox(height: 8),
                    const Text('Motif de rejet',
                        style: TextStyle(
                            fontWeight: FontWeight.bold, color: Colors.red)),
                    Text(op.rejectionReason!),
                  ],
                ],
              ),
            ),
          ),

          if (canExecute) ...[
            const SizedBox(height: 14),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Exécution réelle',
                        style:
                            TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _qtyCtrl,
                      keyboardType:
                          const TextInputType.numberWithOptions(decimal: true),
                      decoration: InputDecoration(
                        labelText: 'Quantité réellement ${op.unite ?? 'traitée'} *',
                        border: const OutlineInputBorder(),
                        helperText: 'Demandé : ${op.quantityRequested ?? '—'}',
                      ),
                    ),
                    const SizedBox(height: 10),
                    TextField(
                      controller: _kgCtrl,
                      keyboardType:
                          const TextInputType.numberWithOptions(decimal: true),
                      decoration: const InputDecoration(
                        labelText: 'Équivalent en kg (optionnel)',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 10),
                    TextField(
                      controller: _positionCtrl,
                      decoration: const InputDecoration(
                        labelText: 'Position / emplacement',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 10),
                    TextField(
                      controller: _obsCtrl,
                      maxLines: 2,
                      decoration: const InputDecoration(
                        labelText: 'Observation',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            icon: Icon(_photoPath != null
                                ? Icons.photo_library
                                : Icons.photo_camera_outlined),
                            label:
                                Text(_photoPath != null ? 'Photo jointe' : 'Photo'),
                            onPressed: _pickPhoto,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: OutlinedButton.icon(
                            icon: Icon(_signatureBytes != null
                                ? Icons.verified
                                : Icons.draw_outlined),
                            label:
                                Text(_signatureBytes != null ? 'Signé' : 'Signature'),
                            onPressed: _pickSignature,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        style: FilledButton.styleFrom(
                            backgroundColor: green,
                            padding: const EdgeInsets.symmetric(vertical: 14)),
                        onPressed: _saving ? null : _submit,
                        icon: _saving
                            ? const SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(
                                    strokeWidth: 2, color: Colors.white))
                            : const Icon(Icons.check_circle_outline),
                        label: Text(_saving ? 'Envoi…' : 'Enregistrer l\'opération'),
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Après enregistrement, l\'opération reste « en attente » : elle n\'intègre le stock officiel qu\'après vérification de l\'inspecteur.',
                      style: TextStyle(fontSize: 11.5, color: Colors.black45),
                    ),
                  ],
                ),
              ),
            ),
          ] else if (op.quantityExecuted != null) ...[
            const SizedBox(height: 14),
            Card(
              color: Colors.green.shade50,
              child: ListTile(
                leading: const Icon(Icons.check_circle, color: Colors.green),
                title: const Text('Exécutée'),
                subtitle: Text(
                    'Quantité : ${op.quantityExecuted} ${op.unite ?? ''}'),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
              width: 150,
              child: Text(label,
                  style: const TextStyle(color: Colors.black54, fontSize: 13))),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}
