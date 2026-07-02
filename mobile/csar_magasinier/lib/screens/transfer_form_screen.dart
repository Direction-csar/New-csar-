import 'package:flutter/material.dart';
import '../models/warehouse.dart';
import '../models/product.dart';
import '../services/api_service.dart';
import 'dart:math';

class TransferFormScreen extends StatefulWidget {
  final Warehouse? warehouse;
  const TransferFormScreen({super.key, this.warehouse});

  @override
  State<TransferFormScreen> createState() => _TransferFormScreenState();
}

class _TransferFormScreenState extends State<TransferFormScreen> {
  final _sacsCtrl = TextEditingController();
  final _kgCtrl = TextEditingController();
  final _obsCtrl = TextEditingController();
  DateTime _date = DateTime.now();
  bool _isLoading = false;
  List<Warehouse> _warehouses = [];
  Warehouse? _sourceWarehouse;
  Warehouse? _destWarehouse;
  List<Product> _products = [];
  Product? _selectedProduct;
  int? _selectedFormatKg;

  @override
  void initState() {
    super.initState();
    _sourceWarehouse = widget.warehouse;
    _loadWarehouses();
    _loadProducts();
  }

  Future<void> _loadWarehouses() async {
    try {
      final wh = await ApiService.getWarehouses();
      setState(() => _warehouses = wh);
    } catch (e) {}
  }

  Future<void> _loadProducts() async {
    try {
      final prod = await ApiService.getProducts();
      setState(() => _products = prod);
    } catch (e) {}
  }

  Future<void> _save() async {
    if (_sourceWarehouse == null) {
      _showError('Sélectionnez le magasin source');
      return;
    }
    if (_destWarehouse == null) {
      _showError('Sélectionnez le magasin destination');
      return;
    }
    if (_selectedFormatKg == null) {
      _showError('Sélectionnez un format');
      return;
    }
    if (_sacsCtrl.text.isEmpty || double.tryParse(_sacsCtrl.text) == null) {
      _showError('Quantité sacs invalide');
      return;
    }

    setState(() => _isLoading = true);
    final sacs = double.parse(_sacsCtrl.text);
    final kg = sacs * _selectedFormatKg!;
    final label = '${_selectedProduct?.name ?? "Produit"} [${_selectedFormatKg}kg]';

    try {
      final result = await ApiService.createTransfer({
        'warehouse_id': _sourceWarehouse!.id,
        'destination_warehouse_id': _destWarehouse!.id,
        'movement_date': _date.toIso8601String().split('T')[0],
        'label': label,
        'sacs': sacs,
        'kg': kg,
        'observation': _obsCtrl.text.trim(),
      });

      if (result['success'] == true) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Transfert ${result['data']['numero_transfert']} effectué !'), backgroundColor: Colors.green),
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

  void _updateKg() {
    final s = double.tryParse(_sacsCtrl.text);
    if (s != null && _selectedFormatKg != null) {
      _kgCtrl.text = (s * _selectedFormatKg!).toStringAsFixed(0);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Transfert Inter-Magasin')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Magasin source
            if (_warehouses.isNotEmpty)
              DropdownButtonFormField<Warehouse>(
                value: _sourceWarehouse,
                decoration: const InputDecoration(labelText: 'Magasin Source'),
                items: _warehouses.map((w) => DropdownMenuItem(value: w, child: Text(w.name))).toList(),
                onChanged: (v) => setState(() => _sourceWarehouse = v),
              ),
            const SizedBox(height: 16),

            // Magasin destination
            if (_warehouses.isNotEmpty)
              DropdownButtonFormField<Warehouse>(
                value: _destWarehouse,
                decoration: const InputDecoration(labelText: 'Magasin Destination'),
                items: _warehouses
                    .where((w) => w.id != _sourceWarehouse?.id)
                    .map((w) => DropdownMenuItem(value: w, child: Text(w.name)))
                    .toList(),
                onChanged: (v) => setState(() => _destWarehouse = v),
              ),
            const SizedBox(height: 16),

            // Date
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Date'),
              subtitle: Text('${_date.day}/${_date.month}/${_date.year}'),
              trailing: const Icon(Icons.calendar_today),
              onTap: () async {
                final picked = await showDatePicker(context: context, initialDate: _date, firstDate: DateTime(2020), lastDate: DateTime.now());
                if (picked != null) setState(() => _date = picked);
              },
            ),
            const SizedBox(height: 16),

            // Produit
            if (_products.isNotEmpty)
              DropdownButtonFormField<Product>(
                value: _selectedProduct,
                decoration: const InputDecoration(labelText: 'Produit'),
                items: _products.map((p) => DropdownMenuItem(value: p, child: Text('${p.name} (${p.category})'))).toList(),
                onChanged: (v) => setState(() { _selectedProduct = v; _selectedFormatKg = null; }),
              ),
            const SizedBox(height: 16),

            // Format
            DropdownButtonFormField<int>(
              value: _selectedFormatKg,
              decoration: const InputDecoration(labelText: 'Format de sac (kg)'),
              items: [5, 10, 25, 50, 100].map((f) => DropdownMenuItem(value: f, child: Text('$f kg'))).toList(),
              onChanged: (v) => setState(() { _selectedFormatKg = v; _updateKg(); }),
            ),
            const SizedBox(height: 16),

            // Sacs et KG
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _sacsCtrl,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(labelText: 'Sacs'),
                    onChanged: (_) => _updateKg(),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextField(
                    controller: _kgCtrl,
                    readOnly: true,
                    decoration: InputDecoration(labelText: 'KG${_selectedFormatKg != null ? ' (×$_selectedFormatKg)' : ''}'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Observation
            TextField(
              controller: _obsCtrl,
              maxLines: 2,
              decoration: const InputDecoration(labelText: 'Observations'),
            ),
            const SizedBox(height: 24),

            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _save,
                child: _isLoading
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('EFFECTUER LE TRANSFERT', style: TextStyle(fontSize: 16)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
