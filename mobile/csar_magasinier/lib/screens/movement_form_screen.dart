import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../models/warehouse.dart';
import '../models/product.dart';
import '../models/stock_movement.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';
import 'receipt_screen.dart';
import 'qr_scan_screen.dart';
import 'signature_screen.dart';
import 'dart:math';
import 'dart:io';
import 'dart:convert';
import 'dart:typed_data';
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';

class MovementFormScreen extends StatefulWidget {
  final Warehouse? warehouse;
  const MovementFormScreen({super.key, this.warehouse});

  @override
  State<MovementFormScreen> createState() => _MovementFormScreenState();
}

class _MovementFormScreenState extends State<MovementFormScreen> {
  final _positionCtrl = TextEditingController();
  final _sacsCtrl = TextEditingController();
  final _kgCtrl = TextEditingController();
  final _obsCtrl = TextEditingController();
  final _productCtrl = TextEditingController();
  final _categoryCtrl = TextEditingController();
  String _movementType = 'entry';
  DateTime _date = DateTime.now();
  bool _isLoading = false;
  List<Warehouse> _warehouses = [];
  Warehouse? _selectedWarehouse;
  List<Product> _products = [];
  Product? _selectedProduct;
  int? _selectedFormatKg;
  bool _isCustomProduct = false;
  String? _photoPath;
  Uint8List? _signatureBytes;
  final _picker = ImagePicker();

  @override
  void initState() {
    super.initState();
    _selectedWarehouse = widget.warehouse;
    _loadWarehouses();
    _loadProducts();
  }

  Future<void> _loadWarehouses() async {
    try {
      final wh = await ApiService.getWarehouses();
      setState(() => _warehouses = wh);
    } catch (e) {
      // fallback
    }
  }

  Future<void> _loadProducts() async {
    try {
      final prod = await ApiService.getProducts();
      setState(() => _products = prod);
    } catch (e) {
      // fallback: pas de produits chargés
    }
  }

  Future<void> _save() async {
    if (_selectedWarehouse == null) {
      _showError('Sélectionnez un magasin');
      return;
    }
    if (_productCtrl.text.trim().isEmpty && _selectedProduct == null) {
      _showError('Saisissez un produit');
      return;
    }
    if (_selectedFormatKg == null) {
      _showError('Sélectionnez un format de sac');
      return;
    }
    if (_sacsCtrl.text.isEmpty || double.tryParse(_sacsCtrl.text) == null) {
      _showError('Quantité sacs invalide');
      return;
    }

    setState(() => _isLoading = true);

    final sacs = double.parse(_sacsCtrl.text);
    final kg = sacs * _selectedFormatKg!;
    final ref = 'STK-${DateTime.now().millisecondsSinceEpoch}-${Random().nextInt(999)}';
    final productName = _isCustomProduct ? _productCtrl.text.trim() : _selectedProduct!.name;
    final category = _isCustomProduct ? (_categoryCtrl.text.trim().isNotEmpty ? _categoryCtrl.text.trim() : 'Divers') : _selectedProduct!.category;
    final label = '$productName [${_selectedFormatKg}kg]';

    final movement = StockMovement(
      warehouseId: _selectedWarehouse!.id,
      movementDate: _date.toIso8601String().split('T')[0],
      label: label,
      position: _positionCtrl.text.trim(),
      entrySacs: _movementType == 'entry' ? sacs : 0,
      entryKg: _movementType == 'entry' ? kg : 0,
      exitSacs: _movementType == 'exit' ? sacs : 0,
      exitKg: _movementType == 'exit' ? kg : 0,
      observation: '${_obsCtrl.text.trim()} | Produit: $productName | Catégorie: $category | Format: ${_selectedFormatKg}kg',
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
          'photo_base64': _photoPath != null ? await _fileToBase64(_photoPath!) : null,
          'signature_base64': _signatureBytes != null ? base64Encode(_signatureBytes!) : null,
        });

        if (result['success'] == true) {
          final ref = result['data']['reference'] ?? movement.reference;
          await _saveToReceiptHistory(ref, movement);
          if (mounted) {
            showDialog(
              context: context,
              barrierDismissible: false,
              builder: (ctx) => AlertDialog(
                title: const Text('Mouvement enregistré'),
                content: Text('Référence: $ref\n\nVoulez-vous voir le reçu ?'),
                actions: [
                  TextButton(
                    onPressed: () { Navigator.pop(ctx); Navigator.pop(context, true); },
                    child: const Text('Fermer'),
                  ),
                  ElevatedButton(
                    onPressed: () {
                      Navigator.pop(ctx);
                      Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => ReceiptScreen(reference: ref)));
                    },
                    child: const Text('Voir le reçu'),
                  ),
                ],
              ),
            );
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
        await _saveToReceiptHistory(movement.reference, movement);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Sauvegardé hors-ligne'), backgroundColor: Colors.orange),
          );
          Navigator.pop(context, true);
        }
      }
    } catch (e) {
      await DatabaseService.insertMovement(movement);
      await _saveToReceiptHistory(movement.reference, movement);
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

            // Produit (sélection ou saisie libre)
            Row(
              children: [
                Expanded(
                  child: _isCustomProduct
                    ? TextField(
                        controller: _productCtrl,
                        decoration: const InputDecoration(labelText: 'Nom du produit'),
                      )
                    : DropdownButtonFormField<Product>(
                        value: _selectedProduct,
                        decoration: const InputDecoration(labelText: 'Produit'),
                        items: [
                          ..._products.map((p) => DropdownMenuItem(value: p, child: Text('${p.name} (${p.category})'))),
                          const DropdownMenuItem(value: null, child: Text('➕ Autre produit...')),
                        ],
                        onChanged: (v) {
                          if (v == null) {
                            setState(() {
                              _isCustomProduct = true;
                              _selectedProduct = null;
                              _selectedFormatKg = null;
                            });
                          } else {
                            setState(() {
                              _isCustomProduct = false;
                              _selectedProduct = v;
                              _selectedFormatKg = null;
                            });
                          }
                        },
                      ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Catégorie (si produit custom)
            if (_isCustomProduct)
              TextField(
                controller: _categoryCtrl,
                decoration: const InputDecoration(labelText: 'Catégorie (ex: Céréales, Huiles...)'),
              ),
            if (_isCustomProduct) const SizedBox(height: 16),

            // Format de sac
            DropdownButtonFormField<int>(
              value: _selectedFormatKg,
              decoration: const InputDecoration(labelText: 'Format de sac (kg)'),
              items: [5, 10, 25, 50, 100].map((f) => DropdownMenuItem(value: f, child: Text('$f kg'))).toList(),
              onChanged: (v) => setState(() {
                _selectedFormatKg = v;
                _updateKgFromSacs();
              }),
            ),
            const SizedBox(height: 16),

            // Position
            TextField(
              controller: _positionCtrl,
              decoration: const InputDecoration(labelText: 'Position Magasin (ex: A1, B2)'),
            ),
            const SizedBox(height: 16),

            // Sacs et KG (auto-calcul)
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _sacsCtrl,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(labelText: 'Sacs'),
                    onChanged: (_) => _updateKgFromSacs(),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextField(
                    controller: _kgCtrl,
                    readOnly: true,
                    keyboardType: TextInputType.number,
                    decoration: InputDecoration(
                      labelText: 'KG${_selectedFormatKg != null ? ' (×$_selectedFormatKg)' : ''}',
                    ),
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
            const SizedBox(height: 16),

            // Photo / QR / Signature actions
            Row(
              children: [
                Expanded(
                  child: _iconActionButton(
                    _photoPath != null ? Icons.photo_library : Icons.camera_alt,
                    _photoPath != null ? 'Photo OK' : 'Photo',
                    Colors.blue,
                    _pickPhoto,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _iconActionButton(
                    Icons.qr_code_scanner,
                    'Scan QR',
                    Colors.teal,
                    _scanQr,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _iconActionButton(
                    _signatureBytes != null ? Icons.verified : Icons.edit,
                    _signatureBytes != null ? 'Signé' : 'Signature',
                    Colors.orange,
                    _sign,
                  ),
                ),
              ],
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

  Future<String?> _fileToBase64(String path) async {
    try {
      final bytes = await File(path).readAsBytes();
      return base64Encode(bytes);
    } catch (e) {
      return null;
    }
  }

  Future<void> _pickPhoto() async {
    final picked = await _picker.pickImage(source: ImageSource.camera, maxWidth: 800);
    if (picked != null) {
      setState(() => _photoPath = picked.path);
    }
  }

  Future<void> _scanQr() async {
    final result = await Navigator.push(context, MaterialPageRoute(builder: (_) => const QrScanScreen()));
    if (result != null && result is String) {
      // Si QR contient un produit connu, le sélectionner
      final match = _products.where((p) => result.toLowerCase().contains(p.name.toLowerCase())).firstOrNull;
      if (match != null) {
        setState(() {
          _isCustomProduct = false;
          _selectedProduct = match;
          _selectedFormatKg = null;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Produit scanné: ${match.name}'), backgroundColor: Colors.green),
        );
      } else {
        setState(() {
          _isCustomProduct = true;
          _selectedProduct = null;
          _productCtrl.text = result;
        });
      }
    }
  }

  Future<void> _sign() async {
    final result = await Navigator.push(context, MaterialPageRoute(builder: (_) => const SignatureScreen()));
    if (result != null && result is Uint8List) {
      setState(() => _signatureBytes = result);
    }
  }

  Widget _iconActionButton(IconData icon, String label, Color color, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: color.withOpacity(0.3)),
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 24),
            const SizedBox(height: 4),
            Text(label, style: TextStyle(fontSize: 11, color: color, fontWeight: FontWeight.bold)),
          ],
        ),
      ),
    );
  }

  Future<void> _saveToReceiptHistory(String ref, StockMovement movement) async {
    final prefs = await SharedPreferences.getInstance();
    final stored = prefs.getStringList('receipt_history') ?? [];
    final whName = _selectedWarehouse?.name ?? 'Magasin #${movement.warehouseId}';
    final entry = '$ref|${movement.movementType}|$whName|${movement.movementDate}|${movement.label}';
    stored.insert(0, entry);
    // Garder les 100 derniers
    if (stored.length > 100) stored.removeLast();
    await prefs.setStringList('receipt_history', stored);
  }

  void _updateKgFromSacs() {
    final sacs = double.tryParse(_sacsCtrl.text);
    if (sacs != null && _selectedFormatKg != null) {
      _kgCtrl.text = (sacs * _selectedFormatKg!).toStringAsFixed(0);
    }
  }

  @override
  void dispose() {
    _positionCtrl.dispose();
    _sacsCtrl.dispose();
    _kgCtrl.dispose();
    _obsCtrl.dispose();
    _productCtrl.dispose();
    _categoryCtrl.dispose();
    super.dispose();
  }
}
