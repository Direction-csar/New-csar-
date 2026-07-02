import 'package:flutter/material.dart';
import '../models/warehouse.dart';
import '../services/api_service.dart';

class InventoryFormScreen extends StatefulWidget {
  final Warehouse? warehouse;
  const InventoryFormScreen({super.key, this.warehouse});

  @override
  State<InventoryFormScreen> createState() => _InventoryFormScreenState();
}

class _InventoryFormScreenState extends State<InventoryFormScreen> {
  final _theoCtrl = TextEditingController();
  final _actualCtrl = TextEditingController();
  final _obsCtrl = TextEditingController();
  DateTime _date = DateTime.now();
  bool _isLoading = false;
  List<Warehouse> _warehouses = [];
  Warehouse? _selectedWarehouse;

  @override
  void initState() {
    super.initState();
    _selectedWarehouse = widget.warehouse;
    _loadWarehouses();
  }

  Future<void> _loadWarehouses() async {
    try {
      final wh = await ApiService.getWarehouses();
      setState(() => _warehouses = wh);
    } catch (e) {}
  }

  Future<void> _save() async {
    if (_selectedWarehouse == null) {
      _showError('Sélectionnez un magasin');
      return;
    }
    if (_theoCtrl.text.isEmpty || double.tryParse(_theoCtrl.text) == null) {
      _showError('Stock théorique invalide');
      return;
    }
    if (_actualCtrl.text.isEmpty || double.tryParse(_actualCtrl.text) == null) {
      _showError('Stock réel invalide');
      return;
    }

    setState(() => _isLoading = true);

    try {
      final result = await ApiService.createInventory({
        'warehouse_id': _selectedWarehouse!.id,
        'movement_date': _date.toIso8601String().split('T')[0],
        'label': 'Inventaire physique ${_date.toIso8601String().split('T')[0]}',
        'theoretical_sacs': double.parse(_theoCtrl.text),
        'actual_sacs': double.parse(_actualCtrl.text),
        'observation': _obsCtrl.text.trim(),
      });

      if (result['success'] == true) {
        final diff = result['data']['difference_sacs'] ?? 0;
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Inventaire enregistré. Écart: ${diff > 0 ? '+' : ''}$diff sacs'),
              backgroundColor: diff == 0 ? Colors.green : Colors.orange,
            ),
          );
          Navigator.pop(context, true);
        }
      } else {
        _showError(result['message'] ?? 'Erreur');
      }
    } catch (e) {
      _showError('Erreur réseau : $e');
    }

    setState(() => _isLoading = false);
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: Colors.red),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Inventaire Physique')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (_warehouses.isNotEmpty)
              DropdownButtonFormField<Warehouse>(
                value: _selectedWarehouse,
                decoration: const InputDecoration(labelText: 'Magasin'),
                items: _warehouses.map((w) => DropdownMenuItem(value: w, child: Text(w.name))).toList(),
                onChanged: (v) => setState(() => _selectedWarehouse = v),
              ),
            const SizedBox(height: 16),

            ListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Date d\'inventaire'),
              subtitle: Text('${_date.day}/${_date.month}/${_date.year}'),
              trailing: const Icon(Icons.calendar_today),
              onTap: () async {
                final picked = await showDatePicker(context: context, initialDate: _date, firstDate: DateTime(2020), lastDate: DateTime.now());
                if (picked != null) setState(() => _date = picked);
              },
            ),
            const SizedBox(height: 16),

            TextField(
              controller: _theoCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Stock Théorique (sacs)'),
            ),
            const SizedBox(height: 16),

            TextField(
              controller: _actualCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Stock Réel compté (sacs)'),
            ),
            const SizedBox(height: 16),

            TextField(
              controller: _obsCtrl,
              maxLines: 3,
              decoration: const InputDecoration(labelText: 'Observations / Justification écart'),
            ),
            const SizedBox(height: 24),

            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _save,
                child: _isLoading
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('ENREGISTRER L\'INVENTAIRE', style: TextStyle(fontSize: 16)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
