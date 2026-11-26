import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';
import '../services/local_db_service.dart';

/// Saisie rapide d'une liste de beneficiaires pour un planning.
/// L'utilisateur colle une liste : une ligne par personne au format
/// `Nom;Telephone;CNI` (tel et CNI optionnels), la quantite est commune.
/// En ligne : envoi en un seul appel batch. Hors-ligne : chaque ligne est
/// enregistree localement puis synchronisee comme une saisie unitaire.
class BulkBeneficiariesScreen extends StatefulWidget {
  final dynamic planning;

  const BulkBeneficiariesScreen({super.key, required this.planning});

  @override
  State<BulkBeneficiariesScreen> createState() => _BulkBeneficiariesScreenState();
}

class _BulkBeneficiariesScreenState extends State<BulkBeneficiariesScreen> {
  final _listCtrl = TextEditingController();
  final _quantiteCtrl = TextEditingController(text: '50');
  final _categoryCtrl = TextEditingController();
  bool _sending = false;
  int? _parsedCount;
  List<String> _parseErrors = [];

  @override
  void dispose() {
    _listCtrl.dispose();
    _quantiteCtrl.dispose();
    _categoryCtrl.dispose();
    super.dispose();
  }

  /// Parse les lignes collees. Separateurs acceptes : ; tabulation ou virgule.
  List<Map<String, dynamic>> _parse(double defaultQty) {
    final items = <Map<String, dynamic>>[];
    _parseErrors = [];
    final lines = _listCtrl.text.split(RegExp(r'\r\n|\r|\n'));

    for (var i = 0; i < lines.length; i++) {
      final raw = lines[i].trim();
      if (raw.isEmpty) continue;

      var parts = raw.split(RegExp(r'[;\t]'));
      if (parts.length == 1) parts = raw.split(',');

      final name = parts.isNotEmpty ? parts[0].trim() : '';
      if (name.isEmpty) {
        _parseErrors.add('Ligne ${i + 1} : nom vide');
        continue;
      }

      final phone = parts.length > 1 && parts[1].trim().isNotEmpty ? parts[1].trim() : null;
      final cni = parts.length > 2 && parts[2].trim().isNotEmpty ? parts[2].trim() : null;
      double qty = defaultQty;
      if (parts.length > 3 && parts[3].trim().isNotEmpty) {
        qty = double.tryParse(parts[3].trim().replaceAll(',', '.')) ?? defaultQty;
      }

      items.add({
        'planning_id': widget.planning['id'],
        'full_name': name,
        'phone': phone,
        'cni': cni,
        'category': _categoryCtrl.text.trim().isEmpty ? 'general' : _categoryCtrl.text.trim(),
        'quantity_kg': qty,
      });
    }
    return items;
  }

  void _preview() {
    final qty = double.tryParse(_quantiteCtrl.text.replaceAll(',', '.')) ?? 0;
    final items = _parse(qty > 0 ? qty : 0);
    setState(() => _parsedCount = items.length);
  }

  Future<void> _submit() async {
    final defaultQty = double.tryParse(_quantiteCtrl.text.replaceAll(',', '.')) ?? 0;
    if (defaultQty <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Quantite par defaut invalide'), backgroundColor: Colors.red),
      );
      return;
    }

    final items = _parse(defaultQty);
    setState(() => _parsedCount = items.length);

    if (items.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Aucune ligne valide a envoyer'), backgroundColor: Colors.orange),
      );
      return;
    }

    setState(() => _sending = true);
    final token = context.read<AuthService>().token;
    final connectivity = await Connectivity().checkConnectivity();

    if (token != null && connectivity != ConnectivityResult.none) {
      try {
        final res = await ApiService.batchBeneficiaries(token, items);
        if (!mounted) return;
        setState(() => _sending = false);
        if (res['success'] == true) {
          final created = res['created_count'] ?? 0;
          final skipped = res['skipped_count'] ?? 0;
          _showResult(
            '$created beneficiaire(s) enregistre(s)${skipped > 0 ? ', $skipped ignore(s) (doublons)' : ''}',
            skipped > 0 ? Colors.orange : Colors.green,
            res['skipped'] as List?,
          );
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(res['message'] ?? 'Erreur lors de l\'envoi'), backgroundColor: Colors.red),
          );
        }
        return;
      } catch (_) {
        // tombe en mode hors-ligne ci-dessous
      }
    }

    // Hors-ligne : chaque ligne est mise en file locale
    for (final item in items) {
      await LocalDbService.savePendingBeneficiaire(item);
    }
    if (!mounted) return;
    setState(() => _sending = false);
    _showResult('${items.length} enregistre(s) hors-ligne. Synchronisation automatique.', Colors.blueGrey, null);
  }

  void _showResult(String message, Color color, List? skipped) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Resultat', textAlign: TextAlign.center),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(color == Colors.green ? Icons.check_circle : Icons.info, color: color, size: 44),
            const SizedBox(height: 10),
            Text(message, textAlign: TextAlign.center),
            if (skipped != null && skipped.isNotEmpty) ...[
              const SizedBox(height: 10),
              const Text('Ignores :', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
              ...skipped.take(8).map((s) => Text(
                    '${s['name'] ?? 'Ligne ${s['index'] + 1}'} : ${s['reason']}',
                    style: const TextStyle(fontSize: 11, color: Colors.grey),
                    textAlign: TextAlign.center,
                  )),
              if (skipped.length > 8) Text('… +${skipped.length - 8}', style: const TextStyle(fontSize: 11)),
            ],
          ],
        ),
        actions: [
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFD84315)),
            onPressed: () {
              Navigator.pop(context);
              Navigator.pop(context);
            },
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final planningName = widget.planning['name'] ?? 'Planning';

    return Scaffold(
      backgroundColor: const Color(0xFFFBE9E7),
      appBar: AppBar(
        backgroundColor: const Color(0xFFD84315),
        title: const Text('Saisie rapide en liste'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.orange.shade200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Planning : $planningName',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  const SizedBox(height: 6),
                  const Text(
                    'Collez votre liste : une ligne par beneficiaire au format\nNom;Telephone;CNI\n(Telephone et CNI optionnels)',
                    style: TextStyle(fontSize: 12, color: Colors.black54),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _quantiteCtrl,
                    keyboardType: const TextInputType.numberWithOptions(decimal: true),
                    decoration: InputDecoration(
                      labelText: 'Quantite par defaut (kg) *',
                      isDense: true,
                      filled: true,
                      fillColor: Colors.white,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: TextField(
                    controller: _categoryCtrl,
                    decoration: InputDecoration(
                      labelText: 'Categorie (defaut : general)',
                      isDense: true,
                      filled: true,
                      fillColor: Colors.white,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _listCtrl,
              maxLines: 12,
              onChanged: (_) => _preview(),
              decoration: InputDecoration(
                hintText: 'Moussa Diop;771234567;1234567890123\nFatou Ndiaye;77…\nAwa Fall',
                hintStyle: const TextStyle(fontSize: 12, color: Colors.grey),
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              ),
              style: const TextStyle(fontSize: 13),
            ),
            const SizedBox(height: 8),
            if (_parsedCount != null)
              Text(
                '$_parsedCount ligne(s) valide(s) detectee(s)'
                '${_parseErrors.isNotEmpty ? ' — ${_parseErrors.length} erreur(s)' : ''}',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: _parseErrors.isEmpty ? Colors.green.shade700 : Colors.orange.shade800,
                ),
              ),
            if (_parseErrors.isNotEmpty)
              ..._parseErrors.take(5).map((e) => Text(e, style: const TextStyle(fontSize: 11, color: Colors.red))),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFD84315),
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 52),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                icon: _sending
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Icon(Icons.playlist_add_check),
                label: Text(_sending ? 'ENVOI…' : 'ENREGISTRER LA LISTE',
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                onPressed: _sending ? null : _submit,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
