import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../models/warehouse.dart';
import '../models/stock_movement.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';
import 'dart:math';

class MovementFormScreen extends StatefulWidget {
  final Warehouse? warehouse;
  const MovementFormScreen({super.key, this.warehouse});

  @override
  State<MovementFormScreen> createState() => _MovementFormScreenState();
}

class _MovementFormScreenState extends State<MovementFormScreen> {
  final _labelCtrl = TextEditingController();
  final _positionCtrl = TextEditingController();
  final _sacsCtrl = TextEditingController();
  final _kgCtrl = TextEditingController();
  final _obsCtrl = TextEditingController();
  String _movementType = 'entry';
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
    } catch (e) {
      // fallback: pas de magasins chargés
    }
  }

  Future<void> _save() async {
    if (_selectedWarehouse == null) {
      _showError('Sélectionnez un magasin');
      return;
    }
    if (_labelCtrl.text.trim().isEmpty) {
      _showError('Le libellé est obligatoire');
      return;
    }
    if (_sacsCtrl.text.isEmpty || double.tryParse(_sacsCtrl.text) == null) {
      _showError('Quantité sacs invalide');
      return;
    }

    setState(() => _isLoading = true);

    final sacs = double.parse(_sacsCtrl.text);
    final kg = _kgCtrl.text.isNotEmpty ? double.parse(_kgCtrl.text) : sacs * 50;
    final ref = 'STK-${DateTime.now().millisecondsSinceEpoch}-${Random().nextInt(999)}';

    final movement = StockMovement(
      warehouseId: _selectedWarehouse!.id,
      movementDate: _date.toIso8601String().split('T')[0],
      label: _labelCtrl.text.trim(),
      position: _positionCtrl.text.trim(),
      entrySacs: _movementType == 'entry' ? sacs : 0,
      entryKg: _movementType == 'entry' ? kg : 0,
      exitSacs: _movementType == 'exit' ? sacs : 0,
      exitKg: _movementType == 'exit' ? kg : 0,
      observation: _obsCtrl.text.trim(),
      reference: ref,
      movementType: _movementType,
      status: 'draft',
      isSynced: false,
    );

    try {
      final hasNet = await ApiService.hasConnection();
      if (hasNet) {
        final result = await ApiService.createMovement({
          'warehouse_id': movement.warehouseId,
          'movement_date': movement.movementDate,
          'label': movement.label,
          'position': movement.position,
          'movement_type': movement.movementType,
          'sacs': sacs,
          'kg': kg,
          'observation': movement.observation,
        });

        if (result['success'] == true) {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Mouvement synchronisé !'), backgroundColor: Colors.green),
            );
            Navigator.pop(context, true);
          }
        } else {
          await DatabaseService.insertMovement(movement);
          if (mounted) {
            _showError('${result['message']} - Sauvegardé localement');
            Navigator.pop(context, true);
          }
        }
      } else {
        await DatabaseService.insertMovement(movement);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Sauvegardé hors-ligne'), backgroundColor: Colors.orange),
          );
          Navigator.pop(context, true);
        }
      }
    } catch (e) {
      await DatabaseService.insertMovement(movement);
      if (mounted) {
        _showError('Erreur réseau - Sauvegardé localement');
        Navigator.pop(context, true);
      }
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
      appBar: AppBar(title: const Text('Nouveau Mouvement')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Type
            const Text('TYPE DE MOUVEMENT', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey)),
            const SizedBox(height: 8),
            SegmentedButton<String>(
              segments: const [
                ButtonSegment(value: 'entry', label: Text('Entrée'), icon: Icon(Icons.arrow_downward)),
                ButtonSegment(value: 'exit', label: Text('Sortie'), icon: Icon(Icons.arrow_upward)),
                ButtonSegment(value: 'adjustment', label: Text('Ajustement'), icon: Icon(Icons.tune)),
              ],
              selected: {_movementType},
              onSelectionChanged: (s) => setState(() => _movementType = s.first),
            ),
            const SizedBox(height: 20),

            // Magasin
            if (_warehouses.isNotEmpty)
              DropdownButtonFormField<Warehouse>(
                value: _selectedWarehouse,
                decoration: const InputDecoration(labelText: 'Magasin'),
                items: _warehouses.map((w) => DropdownMenuItem(value: w, child: Text('${w.name} (${w.code})'))).toList(),
                onChanged: (v) => setState(() => _selectedWarehouse = v),
              ),
            const SizedBox(height: 16),

            // Date
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Date'),
              subtitle: Text('${_date.day}/${_date.month}/${_date.year}'),
              trailing: const Icon(Icons.calendar_today),
              onTap: () async {
                final picked = await showDatePicker(
                  context: context,
                  initialDate: _date,
                  firstDate: DateTime(2020),
                  lastDate: DateTime.now(),
                );
                if (picked != null) setState(() => _date = picked);
              },
            ),
            const SizedBox(height: 8),

            // Libellé
            TextField(
              controller: _labelCtrl,
              decoration: const InputDecoration(labelText: 'Libellé (ex: Réception lot 2167)'),
            ),
            const SizedBox(height: 16),

            // Position
            TextField(
              controller: _positionCtrl,
              decoration: const InputDecoration(labelText: 'Position Magasin (ex: A1, B2)'),
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
                    onChanged: (v) {
                      final s = double.tryParse(v);
                      if (s != null) _kgCtrl.text = (s * 50).toStringAsFixed(0);
                    },
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextField(
                    controller: _kgCtrl,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(labelText: 'KG'),
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

            // Bouton
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _save,
                child: _isLoading
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('ENREGISTRER', style: TextStyle(fontSize: 16)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _labelCtrl.dispose();
    _positionCtrl.dispose();
    _sacsCtrl.dispose();
    _kgCtrl.dispose();
    _obsCtrl.dispose();
    super.dispose();
  }
}
