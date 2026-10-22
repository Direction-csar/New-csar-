import 'package:flutter/material.dart';
import '../models/mission.dart';
import '../services/api_service.dart';

/// Écran « Missions » : instructions de sortie de stock reçues du DTL.
/// Le magasinier exécute la sortie via le formulaire de mouvement, puis
/// clôture la mission ici en joignant la référence du mouvement.
class MissionsScreen extends StatefulWidget {
  const MissionsScreen({super.key});

  @override
  State<MissionsScreen> createState() => _MissionsScreenState();
}

class _MissionsScreenState extends State<MissionsScreen> {
  static const green = Color(0xFF1B5E20);

  List<Mission> _missions = [];
  bool _loading = true;
  String? _error;
  String _filter = 'open'; // open | terminee | all

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
      final missions = await ApiService.getMissions(
        status: _filter == 'open' ? null : _filter,
      );
      if (!mounted) return;
      setState(() {
        _missions = missions;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _error = 'Impossible de charger les missions. Vérifiez la connexion.';
        _loading = false;
      });
    }
  }

  Future<void> _openMission(Mission mission) async {
    final closed = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => MissionDetailScreen(mission: mission)),
    );
    if (closed == true) _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F6F4),
      appBar: AppBar(
        title: const Text('Missions DTL'),
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
                ButtonSegment(value: 'open', label: Text('À exécuter'), icon: Icon(Icons.pending_actions, size: 18)),
                ButtonSegment(value: 'terminee', label: Text('Terminées'), icon: Icon(Icons.check_circle_outline, size: 18)),
              ],
              selected: {_filter},
              onSelectionChanged: (v) {
                setState(() => _filter = v.first);
                _load();
              },
            ),
          ),
          Expanded(child: _buildBody()),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator(color: green));
    }
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.cloud_off, size: 48, color: Colors.grey.shade500),
            const SizedBox(height: 12),
            Text(_error!, textAlign: TextAlign.center),
            const SizedBox(height: 12),
            ElevatedButton.icon(
              onPressed: _load,
              icon: const Icon(Icons.refresh),
              label: const Text('Réessayer'),
            ),
          ],
        ),
      );
    }
    if (_missions.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.assignment_outlined, size: 48, color: Colors.grey.shade400),
            const SizedBox(height: 12),
            Text(
              _filter == 'open'
                  ? 'Aucune mission de sortie à exécuter'
                  : 'Aucune mission terminée',
              style: TextStyle(color: Colors.grey.shade600),
            ),
          ],
        ),
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      color: green,
      child: ListView.builder(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(12),
        itemCount: _missions.length,
        itemBuilder: (_, i) => _MissionCard(mission: _missions[i], onTap: () => _openMission(_missions[i])),
      ),
    );
  }
}

class _MissionCard extends StatelessWidget {
  final Mission mission;
  final VoidCallback onTap;

  const _MissionCard({required this.mission, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        leading: CircleAvatar(
          backgroundColor: mission.isDone ? Colors.green.shade50 : Colors.orange.shade50,
          child: Icon(
            mission.isDone ? Icons.check_circle : Icons.local_shipping_outlined,
            color: mission.isDone ? Colors.green : Colors.orange.shade800,
          ),
        ),
        title: Text(
          'Mission #${mission.id} — ${mission.trackingCode ?? 'Sans dossier'}',
          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14.5),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (mission.demandeur != null)
              Text('${mission.demandeur}${mission.region != null ? ' — ${mission.region}' : ''}',
                  maxLines: 1, overflow: TextOverflow.ellipsis),
            if (mission.instructions != null)
              Text(mission.instructions!, maxLines: 2, overflow: TextOverflow.ellipsis,
                  style: TextStyle(color: Colors.grey.shade600, fontSize: 12.5)),
          ],
        ),
        trailing: Chip(
          label: Text(mission.statusLabel, style: const TextStyle(fontSize: 11)),
          backgroundColor: mission.isDone ? Colors.green.shade50 : Colors.orange.shade50,
          side: BorderSide.none,
        ),
        onTap: onTap,
      ),
    );
  }
}

/// Détail d'une mission : instructions du DTL + clôture avec rapport
/// et référence du mouvement de sortie enregistré.
class MissionDetailScreen extends StatefulWidget {
  final Mission mission;

  const MissionDetailScreen({super.key, required this.mission});

  @override
  State<MissionDetailScreen> createState() => _MissionDetailScreenState();
}

class _MissionDetailScreenState extends State<MissionDetailScreen> {
  static const green = Color(0xFF1B5E20);
  final _reportCtrl = TextEditingController();
  final _refCtrl = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _submitting = false;

  @override
  void dispose() {
    _reportCtrl.dispose();
    _refCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Clôturer la mission ?'),
        content: const Text('Le rapport sera transmis au DTL et la mission marquée comme terminée.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Confirmer')),
        ],
      ),
    );
    if (confirm != true) return;

    setState(() => _submitting = true);
    final result = await ApiService.reportMission(
      widget.mission.id,
      movementReference: _refCtrl.text.trim(),
      report: _reportCtrl.text.trim(),
    );
    if (!mounted) return;
    setState(() => _submitting = false);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(result['message'] ?? ''),
        backgroundColor: result['success'] == true ? green : Colors.red.shade700,
      ),
    );
    if (result['success'] == true) {
      Navigator.pop(context, true);
    }
  }

  @override
  Widget build(BuildContext context) {
    final m = widget.mission;
    return Scaffold(
      appBar: AppBar(
        title: Text('Mission #${m.id}'),
        backgroundColor: green,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Card(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _row(Icons.tag, 'Dossier', m.trackingCode ?? '-'),
                    _row(Icons.person_outline, 'Demandeur',
                        '${m.demandeur ?? '-'}${m.region != null ? ' — ${m.region}' : ''}'),
                    _row(Icons.calendar_today_outlined, 'Affectée le',
                        m.assignedAt?.substring(0, 10) ?? '-'),
                    const Divider(height: 24),
                    const Text('Instructions du DTL',
                        style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                    const SizedBox(height: 6),
                    Text(m.instructions ?? 'Aucune instruction',
                        style: TextStyle(color: Colors.grey.shade800, height: 1.4)),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            if (m.isOpen) ...[
              Card(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        const Text('Clôturer la mission',
                            style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                        const SizedBox(height: 8),
                        Text(
                          'Enregistrez d\'abord la sortie de stock dans « Mouvements », '
                          'puis indiquez sa référence (ex. STK-XXXX).',
                          style: TextStyle(color: Colors.grey.shade600, fontSize: 12.5),
                        ),
                        const SizedBox(height: 14),
                        TextFormField(
                          controller: _refCtrl,
                          decoration: const InputDecoration(
                            labelText: 'Référence du mouvement de sortie (optionnel)',
                            prefixIcon: Icon(Icons.qr_code),
                            border: OutlineInputBorder(),
                          ),
                        ),
                        const SizedBox(height: 12),
                        TextFormField(
                          controller: _reportCtrl,
                          maxLines: 4,
                          decoration: const InputDecoration(
                            labelText: 'Rapport d\'exécution *',
                            hintText: 'Quantités livrées, bénéficiaires, observations…',
                            border: OutlineInputBorder(),
                          ),
                          validator: (v) {
                            final t = (v ?? '').trim();
                            if (t.length < 10) {
                              return 'Rapport trop court (10 caractères minimum)';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 16),
                        FilledButton.icon(
                          onPressed: _submitting ? null : _submit,
                          style: FilledButton.styleFrom(
                            backgroundColor: green,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                          ),
                          icon: _submitting
                              ? const SizedBox(
                                  width: 18, height: 18,
                                  child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                              : const Icon(Icons.send),
                          label: Text(_submitting ? 'Envoi…' : 'Transmettre le rapport au DTL'),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ] else
              Card(
                color: Colors.green.shade50,
                child: const ListTile(
                  leading: Icon(Icons.check_circle, color: green),
                  title: Text('Mission clôturée'),
                  subtitle: Text('Le rapport a été transmis au DTL.'),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _row(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 17, color: Colors.grey.shade600),
          const SizedBox(width: 8),
          Text('$label : ', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
          Expanded(child: Text(value, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13))),
        ],
      ),
    );
  }
}
