import 'dart:io';
import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:open_filex/open_filex.dart';
import 'package:path_provider/path_provider.dart';
import '../../models/fuel.dart';
import '../../services/api_service.dart';

/// État d'erreur partagé avec bouton de rechargement.
class FuelErrorView extends StatelessWidget {
  final Object error;
  final Future<void> Function() onRetry;
  const FuelErrorView({super.key, required this.error, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return ListView(
      children: [
        const SizedBox(height: 80),
        const Icon(Icons.cloud_off, size: 48, color: Colors.black38),
        const SizedBox(height: 16),
        Center(child: Text('$error', textAlign: TextAlign.center)),
        const SizedBox(height: 16),
        Center(
          child: FilledButton(
            onPressed: onRetry,
            child: const Text('Réessayer'),
          ),
        ),
      ],
    );
  }
}

/// Badge de statut partagé par les 3 flux carburant.
class FuelStatusChip extends StatelessWidget {
  final String status;
  const FuelStatusChip(this.status, {super.key});

  @override
  Widget build(BuildContext context) {
    final color = switch (status) {
      'draft' => Colors.grey,
      'validated' => Colors.green.shade700,
      'authorized' => Colors.blue.shade700,
      'rejected' => Colors.red.shade700,
      'submitted' => Colors.orange.shade800,
      'in_review' => Colors.deepPurple.shade400,
      'approved' => Colors.teal.shade700,
      'paid' => Colors.green.shade900,
      'closed' => Colors.blueGrey,
      _ => Colors.grey,
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.4)),
      ),
      child: Text(
        fuelStatusLabel(status),
        style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.w600),
      ),
    );
  }
}

/// Ligne label/valeur utilisée dans les fiches détail.
Widget fuelInfoRow(String label, String? value, {bool bold = false}) {
  return Padding(
    padding: const EdgeInsets.symmetric(vertical: 3),
    child: Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 150,
          child: Text(label, style: const TextStyle(color: Colors.black54)),
        ),
        Expanded(
          child: Text(
            (value == null || value.isEmpty) ? '—' : value,
            style: TextStyle(
              fontWeight: bold ? FontWeight.w700 : FontWeight.normal,
            ),
          ),
        ),
      ],
    ),
  );
}

/// Formate un montant FCFA / une quantité en litres.
String fuelAmount(num? v) =>
    v == null ? '—' : '${_group(v)} FCFA';
String fuelLiters(num? v) =>
    v == null ? '—' : '${_group(v)} L';

String _group(num v) {
  final s = v is double ? v.toStringAsFixed(v == v.roundToDouble() ? 0 : 2) : '$v';
  final parts = s.split('.');
  final intPart = parts[0].replaceAllMapped(
    RegExp(r'(\d)(?=(\d{3})+(?!\d))'),
    (m) => '${m[1]} ',
  );
  return parts.length > 1 ? '$intPart.${parts[1]}' : intPart;
}

/// Télécharge le PDF du dossier puis l'ouvre avec le lecteur du téléphone.
Future<void> openFuelPdf(
    BuildContext context, String type, int id, String reference) async {
  final messenger = ScaffoldMessenger.of(context);
  messenger.showSnackBar(const SnackBar(
    content: Text('Génération du PDF…'),
    duration: Duration(seconds: 1),
  ));
  try {
    final bytes = await ApiService.downloadFuelPdf(type, id);
    if (bytes == null) {
      messenger.showSnackBar(
          const SnackBar(content: Text('PDF indisponible.')));
      return;
    }
    final dir = await getTemporaryDirectory();
    final file = File('${dir.path}/$reference.pdf');
    await file.writeAsBytes(bytes, flush: true);
    await OpenFilex.open(file.path);
  } catch (e) {
    messenger.showSnackBar(SnackBar(content: Text('Erreur PDF : $e')));
  }
}

/// Section « Justificatifs » : liste + ajout via file_picker + suppression.
class FuelAttachmentsSection extends StatefulWidget {
  /// entry | exit | reimb
  final String type;
  final int subjectId;
  final List<FuelAttachment> attachments;
  final VoidCallback onChanged;

  const FuelAttachmentsSection({
    super.key,
    required this.type,
    required this.subjectId,
    required this.attachments,
    required this.onChanged,
  });

  @override
  State<FuelAttachmentsSection> createState() => _FuelAttachmentsSectionState();
}

class _FuelAttachmentsSectionState extends State<FuelAttachmentsSection> {
  bool _busy = false;

  Future<void> _pickAndUpload() async {
    final picked = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
    );
    if (picked == null || picked.files.single.path == null) return;

    setState(() => _busy = true);
    final res = await ApiService.uploadFuelAttachment(
      type: widget.type,
      subjectId: widget.subjectId,
      filePath: picked.files.single.path!,
    );
    if (!mounted) return;
    setState(() => _busy = false);
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(res['message'] ?? '')));
    if (res['success'] == true) widget.onChanged();
  }

  Future<void> _open(FuelAttachment a) async {
    final messenger = ScaffoldMessenger.of(context);
    final bytes = await ApiService.downloadFuelAttachment(a.id);
    if (bytes == null) {
      messenger.showSnackBar(
          const SnackBar(content: Text('Fichier indisponible.')));
      return;
    }
    final dir = await getTemporaryDirectory();
    final file = File('${dir.path}/${a.originalName}');
    await file.writeAsBytes(bytes, flush: true);
    await OpenFilex.open(file.path);
  }

  Future<void> _delete(FuelAttachment a) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Supprimer la pièce ?'),
        content: Text(a.originalName),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Annuler')),
          FilledButton(
              onPressed: () => Navigator.pop(ctx, true),
              child: const Text('Supprimer')),
        ],
      ),
    );
    if (ok != true) return;
    final res = await ApiService.deleteFuelAttachment(a.id);
    if (!mounted) return;
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(res['message'] ?? '')));
    if (res['success'] == true) widget.onChanged();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Text('Justificatifs',
                style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
            const Spacer(),
            _busy
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2))
                : TextButton.icon(
                    onPressed: _pickAndUpload,
                    icon: const Icon(Icons.upload_file, size: 18),
                    label: const Text('Ajouter'),
                  ),
          ],
        ),
        if (widget.attachments.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 8),
            child: Text('Aucune pièce jointe.',
                style: TextStyle(color: Colors.black45)),
          )
        else
          ...widget.attachments.map(
            (a) => ListTile(
              dense: true,
              contentPadding: EdgeInsets.zero,
              leading: Icon(
                a.mimeType == 'application/pdf'
                    ? Icons.picture_as_pdf
                    : Icons.image,
                color: Colors.blueGrey,
              ),
              title: Text(a.originalName,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 14)),
              subtitle: Text(a.kind),
              onTap: () => _open(a),
              trailing: IconButton(
                icon: const Icon(Icons.delete_outline, color: Colors.red),
                onPressed: () => _delete(a),
              ),
            ),
          ),
      ],
    );
  }
}
