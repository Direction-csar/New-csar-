import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:geolocator/geolocator.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';

class CollectionScreen extends StatefulWidget {
  final Map<String, dynamic>? preselectedMarket;
  const CollectionScreen({super.key, this.preselectedMarket});

  @override
  State<CollectionScreen> createState() => _CollectionScreenState();
}

class _CollectionScreenState extends State<CollectionScreen> {
  final _formKey = GlobalKey<FormState>();
  List<dynamic> _markets = [];
  Map<String, List<dynamic>> _productsByCategory = {};
  bool _loading = true;
  bool _submitting = false;

  Map<String, dynamic>? _selectedMarket;
  Map<String, dynamic>? _selectedProduct;
  String? _selectedCategory;

  final _provenanceCtrl = TextEditingController();
  final _quantityCtrl = TextEditingController();
  final _prixProdCtrl = TextEditingController();
  final _prixDetailCtrl = TextEditingController();
  final _prixGrosCtrl = TextEditingController();
  DateTime _collectedAt = DateTime.now();
  double? _latitude;
  double? _longitude;
  String _gpsStatus = 'Non capturé';

  @override
  void initState() {
    super.initState();
    _selectedMarket = widget.preselectedMarket;
    _loadData();
    _captureGPS();
  }

  Future<void> _captureGPS() async {
    try {
      LocationPermission perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }
      if (perm == LocationPermission.deniedForever) {
        setState(() => _gpsStatus = 'Permission refusée');
        return;
      }
      final pos = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
        timeLimit: const Duration(seconds: 15),
      );
      setState(() {
        _latitude = pos.latitude;
        _longitude = pos.longitude;
        _gpsStatus = '${pos.latitude.toStringAsFixed(5)}, ${pos.longitude.toStringAsFixed(5)}';
      });
    } catch (_) {
      setState(() => _gpsStatus = 'Erreur GPS');
    }
  }

  @override
  void dispose() {
    _provenanceCtrl.dispose();
    _quantityCtrl.dispose();
    _prixProdCtrl.dispose();
    _prixDetailCtrl.dispose();
    _prixGrosCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    final token = context.read<AuthService>().token!;
    try {
      final mRes = await ApiService.getMarkets(token);
      final pRes = await ApiService.getProducts(token);
      final products = (pRes['data'] ?? []) as List<dynamic>;
      final Map<String, List<dynamic>> grouped = {};
      for (final p in products) {
        final cat = p['category'] ?? 'Autres';
        grouped.putIfAbsent(cat, () => []).add(p);
      }
      setState(() {
        _markets = mRes['data'] ?? [];
        _productsByCategory = grouped;
        if (grouped.isNotEmpty) _selectedCategory = grouped.keys.first;
        _loading = false;
      });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  List<dynamic> get _currentProducts =>
      _selectedCategory != null ? (_productsByCategory[_selectedCategory] ?? []) : [];

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedMarket == null || _selectedProduct == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Sélectionnez un marché et un produit'), backgroundColor: Colors.orange),
      );
      return;
    }
    if (_prixProdCtrl.text.isEmpty && _prixDetailCtrl.text.isEmpty && _prixGrosCtrl.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Saisissez au moins un prix'), backgroundColor: Colors.orange),
      );
      return;
    }
    setState(() => _submitting = true);
    final token = context.read<AuthService>().token!;
    try {
      final res = await ApiService.submitCollection(token, {
        'market_id':          _selectedMarket!['id'],
        'product_id':         _selectedProduct!['id'],
        'provenance':         _provenanceCtrl.text.isNotEmpty ? _provenanceCtrl.text : null,
        'quantity_collected': _quantityCtrl.text.isNotEmpty ? double.tryParse(_quantityCtrl.text) : null,
        'price':              _prixProdCtrl.text.isNotEmpty ? double.parse(_prixProdCtrl.text) : 0,
        'retail_price':       _prixDetailCtrl.text.isNotEmpty ? double.parse(_prixDetailCtrl.text) : null,
        'wholesale_price':    _prixGrosCtrl.text.isNotEmpty ? double.parse(_prixGrosCtrl.text) : null,
        'collection_date':    DateFormat('yyyy-MM-dd').format(_collectedAt),
        'latitude':           _latitude,
        'longitude':          _longitude,
      });
      setState(() => _submitting = false);
      if (!mounted) return;
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Collecte enregistrée !'), backgroundColor: Colors.green),
        );
        Navigator.pop(context);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Erreur'), backgroundColor: Colors.red),
        );
      }
    } catch (_) {
      setState(() => _submitting = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Erreur réseau'), backgroundColor: Colors.red),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        title: const Text('Fiche de Collecte'),
        centerTitle: true,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _card([
                      _sectionHeader('MARCHÉ & DATE', Icons.store, Colors.teal),
                      _gpsIndicator(),
                      const SizedBox(height: 12),
                      _dropdown(
                        label: 'Sélectionner un marché *',
                        icon: Icons.store_outlined,
                        value: _selectedMarket,
                        items: _markets,
                        display: (m) => '${m['name']} — ${m['commune'] ?? ''}',
                        onChanged: (v) => setState(() => _selectedMarket = v),
                        validator: (v) => v == null ? 'Requis' : null,
                      ),
                      const SizedBox(height: 12),
                      _dateField(),
                    ]),
                    const SizedBox(height: 12),
                    _card([
                      _sectionHeader('PRODUIT', Icons.inventory_2, const Color(0xFF1B5E20)),
                      _categorySelector(),
                      const SizedBox(height: 12),
                      _dropdown(
                        label: 'Désignation du produit *',
                        icon: Icons.category_outlined,
                        value: _selectedProduct,
                        items: _currentProducts,
                        display: (p) => '${p['name']} (${p['unit'] ?? 'kg'})',
                        onChanged: (v) => setState(() => _selectedProduct = v),
                        validator: (v) => v == null ? 'Requis' : null,
                      ),
                    ]),
                    const SizedBox(height: 12),
                    _card([
                      _sectionHeader('PROVENANCE & QUANTITÉ', Icons.place, Colors.orange.shade700),
                      _textField(_provenanceCtrl, 'Provenance', Icons.location_on_outlined),
                      const SizedBox(height: 12),
                      _textField(_quantityCtrl, 'Quantité relevée', Icons.scale_outlined, numeric: true),
                    ]),
                    const SizedBox(height: 12),
                    _card([
                      _sectionHeader('PRIX (FCFA)', Icons.monetization_on, Colors.blue.shade700),
                      _priceRow('Prix Producteur', _prixProdCtrl),
                      const SizedBox(height: 10),
                      _priceRow('Prix Détail', _prixDetailCtrl),
                      const SizedBox(height: 10),
                      _priceRow('Prix ½ Gros', _prixGrosCtrl),
                    ]),
                    const SizedBox(height: 20),
                    _submitting
                        ? const Center(child: CircularProgressIndicator())
                        : ElevatedButton.icon(
                            onPressed: _submit,
                            icon: const Icon(Icons.check_circle_outline, size: 22),
                            label: const Text(
                              'ENREGISTRER LA COLLECTE',
                              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                            ),
                            style: ElevatedButton.styleFrom(
                              minimumSize: const Size(double.infinity, 54),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                    const SizedBox(height: 20),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _gpsIndicator() {
    final bool captured = _latitude != null;
    return GestureDetector(
      onTap: _captureGPS,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: captured ? Colors.green.shade50 : Colors.orange.shade50,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: captured ? Colors.green.shade300 : Colors.orange.shade300),
        ),
        child: Row(
          children: [
            Icon(Icons.location_on, size: 18, color: captured ? Colors.green.shade700 : Colors.orange.shade700),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                'GPS : $_gpsStatus',
                style: TextStyle(fontSize: 12, color: captured ? Colors.green.shade700 : Colors.orange.shade700),
              ),
            ),
            Icon(Icons.refresh, size: 16, color: captured ? Colors.green.shade400 : Colors.orange.shade400),
          ],
        ),
      ),
    );
  }

  Widget _card(List<Widget> children) => Card(
    elevation: 2,
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    child: Padding(
      padding: const EdgeInsets.all(16),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: children),
    ),
  );

  Widget _sectionHeader(String title, IconData icon, Color color) => Padding(
    padding: const EdgeInsets.only(bottom: 12),
    child: Row(
      children: [
        Icon(icon, color: color, size: 18),
        const SizedBox(width: 8),
        Text(title, style: TextStyle(fontWeight: FontWeight.bold, color: color, fontSize: 13, letterSpacing: 0.5)),
      ],
    ),
  );

  Widget _categorySelector() {
    final cats = _productsByCategory.keys.toList();
    return SizedBox(
      height: 36,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: cats.length,
        itemBuilder: (_, i) {
          final cat = cats[i];
          final selected = _selectedCategory == cat;
          return GestureDetector(
            onTap: () => setState(() {
              _selectedCategory = cat;
              _selectedProduct = null;
            }),
            child: Container(
              margin: const EdgeInsets.only(right: 8),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: selected ? const Color(0xFF1B5E20) : Colors.grey.shade200,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                cat,
                style: TextStyle(
                  fontSize: 12,
                  color: selected ? Colors.white : Colors.black87,
                  fontWeight: selected ? FontWeight.bold : FontWeight.normal,
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _dateField() => InkWell(
    onTap: () async {
      final d = await showDatePicker(
        context: context,
        initialDate: _collectedAt,
        firstDate: DateTime(2024),
        lastDate: DateTime.now(),
      );
      if (d != null) setState(() => _collectedAt = d);
    },
    child: Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: Colors.grey.shade400),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        children: [
          const Icon(Icons.calendar_today, color: Color(0xFF1B5E20), size: 20),
          const SizedBox(width: 10),
          const Text('Date de collecte : ', style: TextStyle(color: Colors.grey)),
          Text(DateFormat('dd/MM/yyyy').format(_collectedAt), style: const TextStyle(fontWeight: FontWeight.bold)),
        ],
      ),
    ),
  );

  Widget _dropdown({
    required String label,
    required IconData icon,
    required dynamic value,
    required List<dynamic> items,
    required String Function(dynamic) display,
    required void Function(dynamic) onChanged,
    String? Function(dynamic)? validator,
  }) => DropdownButtonFormField<dynamic>(
    value: value,
    isExpanded: true,
    decoration: InputDecoration(
      labelText: label,
      prefixIcon: Icon(icon, size: 20),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
      filled: true,
      fillColor: Colors.white,
      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
    ),
    items: items.map((item) => DropdownMenuItem(
      value: item,
      child: Text(display(item), overflow: TextOverflow.ellipsis),
    )).toList(),
    onChanged: onChanged,
    validator: validator,
  );

  Widget _textField(TextEditingController ctrl, String label, IconData icon, {bool numeric = false}) =>
      TextFormField(
        controller: ctrl,
        keyboardType: numeric ? TextInputType.number : TextInputType.text,
        decoration: InputDecoration(
          labelText: label,
          prefixIcon: Icon(icon, size: 20),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
          filled: true,
          fillColor: Colors.white,
          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        ),
      );

  Widget _priceRow(String label, TextEditingController ctrl) => Row(
    children: [
      SizedBox(
        width: 110,
        child: Text(label, style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 13)),
      ),
      Expanded(
        child: TextFormField(
          controller: ctrl,
          keyboardType: TextInputType.number,
          decoration: InputDecoration(
            hintText: '0',
            suffixText: 'FCFA',
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
            filled: true,
            fillColor: Colors.white,
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          ),
        ),
      ),
    ],
  );
}
