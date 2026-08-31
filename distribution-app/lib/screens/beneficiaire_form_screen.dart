import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';
import '../services/local_db_service.dart';

class BeneficiaireFormScreen extends StatefulWidget {
  final dynamic planning;

  const BeneficiaireFormScreen({super.key, required this.planning});

  @override
  State<BeneficiaireFormScreen> createState() => _BeneficiaireFormScreenState();
}

class _BeneficiaireFormScreenState extends State<BeneficiaireFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _cniCtrl = TextEditingController();
  final _addressCtrl = TextEditingController();
  final _quantiteCtrl = TextEditingController();

  static const _categories = ['Vulnerable', 'Religieux', 'Instruction', 'OAL', 'Spontane'];
  String _category = _categories.first;
  bool _isVulnerable = false;
  bool _isPregnant = false;
  bool _isElderly = false;
  bool _isDisabled = false;
  bool _saving = false;
  bool _checkingDup = false;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _cniCtrl.dispose();
    _addressCtrl.dispose();
    _quantiteCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final fullName = _nameCtrl.text.trim();
    final phone = _phoneCtrl.text.trim().isEmpty ? null : _phoneCtrl.text.trim();
    final cni = _cniCtrl.text.trim().isEmpty ? null : _cniCtrl.text.trim();

    final data = {
      'planning_id': widget.planning['id'],
      'full_name': fullName,
      'phone': phone,
      'cni': cni,
      'address': _addressCtrl.text.trim().isEmpty ? null : _addressCtrl.text.trim(),
      'category': _category,
      'is_vulnerable': _isVulnerable,
      'is_pregnant': _isPregnant,
      'is_elderly': _isElderly,
      'is_disabled': _isDisabled,
      'quantity_kg': double.tryParse(_quantiteCtrl.text.replaceAll(',', '.')) ?? 0,
    };

    final token = context.read<AuthService>().token;
    final connectivity = await Connectivity().checkConnectivity();

    if (token != null && connectivity != ConnectivityResult.none) {
      try {
        setState(() => _checkingDup = true);
        final dupRes = await ApiService.checkDuplicate(token, widget.planning['id'], phone, cni, fullName);
        setState(() => _checkingDup = false);
        if (dupRes['success'] == true && dupRes['data'] != null) {
          final dup = dupRes['data'];
          setState(() => _saving = false);
          if (!mounted) return;
          _showDuplicateDialog(dup);
          return;
        }
      } catch (_) {
        setState(() => _checkingDup = false);
      }

      try {
        final res = await ApiService.storeBeneficiaire(token, data);
        setState(() => _saving = false);
        if (!mounted) return;
        if (res['success'] == true) {
          _showSuccessAndClose('Beneficiaire enregistre et synchronise.');
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(res['message'] ?? 'Erreur lors de l\'enregistrement'), backgroundColor: Colors.red),
          );
        }
      } catch (_) {
        await LocalDbService.savePendingBeneficiaire(data);
        setState(() => _saving = false);
        if (!mounted) return;
        _showSuccessAndClose('Enregistre hors-ligne. Sera synchronise automatiquement.');
      }
    } else {
      await LocalDbService.savePendingBeneficiaire(data);
      setState(() => _saving = false);
      if (!mounted) return;
      _showSuccessAndClose('Enregistre hors-ligne. Sera synchronise automatiquement.');
    }
  }

  void _showDuplicateDialog(Map<String, dynamic> dup) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        icon: const Icon(Icons.warning_amber_rounded, color: Colors.orange, size: 48),
        title: const Text('Doublon detecte', textAlign: TextAlign.center),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Cette personne existe deja dans ce planning:', textAlign: TextAlign.center, style: const TextStyle(fontSize: 13)),
            const SizedBox(height: 12),
            Text(dup['full_name'] ?? 'N/A', style: const TextStyle(fontWeight: FontWeight.bold)),
            if (dup['phone'] != null) Text('Tel: ${dup['phone']}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
            if (dup['cni'] != null) Text('CNI: ${dup['cni']}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
            const SizedBox(height: 8),
            Text('Statut: ${dup['status'] ?? 'N/A'}', style: const TextStyle(fontSize: 12)),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.orange),
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

  void _showSuccessAndClose(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.green),
    );
    Navigator.pop(context);
  }

  InputDecoration _inputDecoration(String label, IconData icon) => InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        filled: true,
        fillColor: Colors.white,
      );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFBE9E7),
      appBar: AppBar(
        backgroundColor: const Color(0xFFD84315),
        title: const Text('Nouveau beneficiaire'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextFormField(
                controller: _nameCtrl,
                decoration: _inputDecoration('Nom complet *', Icons.person_outline),
                validator: (v) => v == null || v.trim().isEmpty ? 'Nom requis' : null,
              ),
              const SizedBox(height: 14),
              TextFormField(
                controller: _phoneCtrl,
                keyboardType: TextInputType.phone,
                decoration: _inputDecoration('Telephone', Icons.phone_outlined),
              ),
              const SizedBox(height: 14),
              TextFormField(
                controller: _cniCtrl,
                decoration: _inputDecoration('N CNI', Icons.badge_outlined),
              ),
              const SizedBox(height: 14),
              TextFormField(
                controller: _addressCtrl,
                maxLines: 2,
                decoration: _inputDecoration('Adresse', Icons.location_on_outlined),
              ),
              const SizedBox(height: 14),
              DropdownButtonFormField<String>(
                initialValue: _category,
                decoration: _inputDecoration('Categorie *', Icons.category_outlined),
                items: _categories.map((c) => DropdownMenuItem(value: c, child: Text(c))).toList(),
                onChanged: (v) => setState(() => _category = v ?? _category),
              ),
              const SizedBox(height: 14),
              TextFormField(
                controller: _quantiteCtrl,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: _inputDecoration('Quantite (kg) *', Icons.scale_outlined),
                validator: (v) {
                  final n = double.tryParse((v ?? '').replaceAll(',', '.'));
                  if (n == null || n <= 0) return 'Quantite invalide';
                  return null;
                },
              ),
              const SizedBox(height: 18),
              CheckboxListTile(
                value: _isVulnerable,
                onChanged: (v) => setState(() => _isVulnerable = v ?? false),
                title: const Text('Vulnerable'),
                controlAffinity: ListTileControlAffinity.leading,
                contentPadding: EdgeInsets.zero,
              ),
              CheckboxListTile(
                value: _isPregnant,
                onChanged: (v) => setState(() => _isPregnant = v ?? false),
                title: const Text('Enceinte'),
                controlAffinity: ListTileControlAffinity.leading,
                contentPadding: EdgeInsets.zero,
              ),
              CheckboxListTile(
                value: _isElderly,
                onChanged: (v) => setState(() => _isElderly = v ?? false),
                title: const Text('Personne agee'),
                controlAffinity: ListTileControlAffinity.leading,
                contentPadding: EdgeInsets.zero,
              ),
              CheckboxListTile(
                value: _isDisabled,
                onChanged: (v) => setState(() => _isDisabled = v ?? false),
                title: const Text('Handicap'),
                controlAffinity: ListTileControlAffinity.leading,
                contentPadding: EdgeInsets.zero,
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFD84315),
                    foregroundColor: Colors.white,
                    minimumSize: const Size(double.infinity, 52),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: _saving ? null : _submit,
                  child: _saving
                      ? const SizedBox(height: 22, width: 22, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : _checkingDup
                          ? const Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                              SizedBox(height: 18, width: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)),
                              SizedBox(width: 10),
                              Text('Verification...', style: TextStyle(fontSize: 14)),
                            ])
                          : const Text('ENREGISTRER', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
