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
  bool _vulnerable = false;
  bool _religious = false;
  bool _spontaneous = false;
  bool _saving = false;

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

    final data = {
      'planning_id': widget.planning['id'],
      'name': _nameCtrl.text.trim(),
      'phone': _phoneCtrl.text.trim().isEmpty ? null : _phoneCtrl.text.trim(),
      'cni': _cniCtrl.text.trim().isEmpty ? null : _cniCtrl.text.trim(),
      'address': _addressCtrl.text.trim().isEmpty ? null : _addressCtrl.text.trim(),
      'category': _category,
      'vulnerable': _vulnerable,
      'religious': _religious,
      'spontaneous': _spontaneous,
      'quantite_kg': double.tryParse(_quantiteCtrl.text.replaceAll(',', '.')) ?? 0,
    };

    final token = context.read<AuthService>().token;
    final connectivity = await Connectivity().checkConnectivity();

    if (token != null && connectivity != ConnectivityResult.none) {
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
                value: _vulnerable,
                onChanged: (v) => setState(() => _vulnerable = v ?? false),
                title: const Text('Vulnerable'),
                controlAffinity: ListTileControlAffinity.leading,
                contentPadding: EdgeInsets.zero,
              ),
              CheckboxListTile(
                value: _religious,
                onChanged: (v) => setState(() => _religious = v ?? false),
                title: const Text('Religieux'),
                controlAffinity: ListTileControlAffinity.leading,
                contentPadding: EdgeInsets.zero,
              ),
              CheckboxListTile(
                value: _spontaneous,
                onChanged: (v) => setState(() => _spontaneous = v ?? false),
                title: const Text('Spontane'),
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
