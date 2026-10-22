import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/movement.dart';
import '../services/api_service.dart';

/// Fiche d'un mouvement : quantités avant/après, mission liée,
/// et décision comptable (valider / rejeter avec motif).
/// Un mouvement validé est gelé — aucune modification silencieuse.
class MovementDetailScreen extends StatefulWidget {
  final int movementId;
  const MovementDetailScreen({super.key, required this.movementId});

  @override
  State<MovementDetailScreen> createState() => _MovementDetailScreenState();
}

class _MovementDetailScreenState extends State<MovementDetailScreen> {
  MovementDetail? _detail;
  bool _loading = true;
  bool _busy = false;
  String? _error;

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
      final detail = await ApiService.getMovement(widget.movementId);
      if (!mounted) return;
      setState(() {
        _detail = detail;
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

  Future<void> _validate() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Valider le mouvement'),
        content: const Text(
            'La validation gèle l\'écriture : elle ne pourra plus être modifiée. Confirmer ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Valider')),
        ],
      ),
    );
    if (confirm != true) return;

    setState(() => _busy = true);
    try {
      final result = await ApiService.validateMovement(widget.movementId);
      if (!mounted) return;
      _toast(result['message'], ok: result['success'] == true);
      await _load();
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _reject() async {
    final controller = TextEditingController();
    final reason = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Rejeter le mouvement'),
        content: TextField(
          controller: controller,
          maxLines: 4,
          decoration: const InputDecoration(
            labelText: 'Motif du rejet (min. 10 caractères) *',
            alignLabelWithHint: true,
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Annuler')),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () => Navigator.pop(ctx, controller.text.trim()),
            child: const Text('Rejeter'),
          ),
        ],
      ),
    );
    if (reason == null || reason.isEmpty) return;
    if (reason.length < 10) {
      _toast('Le motif doit contenir au moins 10 caractères.', ok: false);
      return;
    }

    setState(() => _busy = true);
    try {
      final result = await ApiService.rejectMovement(widget.movementId, reason);
      if (!mounted) return;
      _toast(result['message'], ok: result['success'] == true);
      await _load();
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  void _toast(String message, {required bool ok}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: ok ? Colors.green.shade700 : Colors.red.shade700,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Mouvement #${widget.movementId}')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!))
              : _body(),
    );
  }

  Widget _body() {
    final m = _detail!.movement;
    final mission = _detail!.mission;
    final date = m.createdAt != null
        ? DateFormat('dd/MM/yyyy HH:mm').format(m.createdAt!)
        : '-';

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(m.reference ?? 'Sans référence',
                          style: const TextStyle(
                              fontSize: 18, fontWeight: FontWeight.bold)),
                    ),
                    _statusChip(m.status),
                  ],
                ),
                const Divider(height: 24),
                _row('Type', m.typeLabel),
                _row('Magasin', m.warehouseName ?? '-'),
                _row('Quantité', '${m.quantity}'),
                if (m.quantityBefore != null)
                  _row('Stock avant', '${m.quantityBefore}'),
                if (m.quantityAfter != null)
                  _row('Stock après', '${m.quantityAfter}'),
                _row('Motif', m.reason ?? '-'),
                _row('Saisi par', m.creatorName ?? '-'),
                _row('Date', date),
                if (m.validatedAt != null)
                  _row('Décidé le',
                      DateFormat('dd/MM/yyyy HH:mm').format(m.validatedAt!)),
                if (m.rejectionReason != null)
                  _row('Motif de rejet', m.rejectionReason!,
                      valueColor: Colors.red.shade700),
              ],
            ),
          ),
        ),
        if (mission != null) ...[
          const SizedBox(height: 12),
          Card(
            color: Colors.blue.shade50,
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Mission DTL liée',
                      style: TextStyle(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  _row('Mission', '#${mission['id']} (${mission['status']})'),
                  if (mission['request']?['tracking_code'] != null)
                    _row('Dossier', mission['request']['tracking_code']),
                  if (mission['report'] != null)
                    _row('Rapport', mission['report']),
                ],
              ),
            ),
          ),
        ],
        const SizedBox(height: 24),
        if (m.status == 'draft') ...[
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: _busy ? null : _validate,
              icon: const Icon(Icons.check_circle_outline),
              label: const Text('VALIDER LE MOUVEMENT'),
            ),
          ),
          const SizedBox(height: 10),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.red,
                side: const BorderSide(color: Colors.red),
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
              onPressed: _busy ? null : _reject,
              icon: const Icon(Icons.cancel_outlined),
              label: const Text('REJETER AVEC MOTIF'),
            ),
          ),
        ] else
          Card(
            color: Colors.grey.shade100,
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Text(
                m.status == 'validated'
                    ? 'Mouvement validé : écriture gelée, aucune modification possible.'
                    : 'Mouvement rejeté. Motif : ${m.rejectionReason}',
                textAlign: TextAlign.center,
              ),
            ),
          ),
      ],
    );
  }

  Widget _row(String label, String value, {Color? valueColor}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 110,
            child: Text(label, style: TextStyle(color: Colors.grey.shade600)),
          ),
          Expanded(
            child: Text(value,
                style: TextStyle(
                    fontWeight: FontWeight.w500, color: valueColor)),
          ),
        ],
      ),
    );
  }

  Widget _statusChip(String status) {
    final (label, color) = switch (status) {
      'validated' => ('Validé', Colors.green),
      'rejected' => ('Rejeté', Colors.red),
      _ => ('En attente', Colors.orange),
    };
    return Chip(
      label: Text(label),
      backgroundColor: color.withValues(alpha: 0.15),
      side: BorderSide.none,
    );
  }
}
