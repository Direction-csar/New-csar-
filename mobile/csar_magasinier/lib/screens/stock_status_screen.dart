import 'package:flutter/material.dart';
import '../models/warehouse.dart';
import '../services/api_service.dart';

class StockStatusScreen extends StatefulWidget {
  final Warehouse? warehouse;
  const StockStatusScreen({super.key, this.warehouse});

  @override
  State<StockStatusScreen> createState() => _StockStatusScreenState();
}

class _StockStatusScreenState extends State<StockStatusScreen> {
  bool _loading = true;
  List<Warehouse> _warehouses = [];
  Warehouse? _selectedWarehouse;
  Map<String, dynamic> _stockData = {};
  List<dynamic> _products = [];

  @override
  void initState() {
    super.initState();
    _selectedWarehouse = widget.warehouse;
    _loadWarehouses();
    if (_selectedWarehouse != null) _loadStockStatus();
  }

  Future<void> _loadWarehouses() async {
    try {
      final wh = await ApiService.getWarehouses();
      setState(() => _warehouses = wh);
    } catch (e) {}
  }

  Future<void> _loadStockStatus() async {
    if (_selectedWarehouse == null) return;
    setState(() => _loading = true);
    try {
      final data = await ApiService.getStockStatus(_selectedWarehouse!.id);
      setState(() {
        _stockData = data;
        _products = data['products'] ?? [];
        _loading = false;
      });
    } catch (e) {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('État du Stock')),
      body: Column(
        children: [
          if (_warehouses.isNotEmpty)
            Padding(
              padding: const EdgeInsets.all(16),
              child: DropdownButtonFormField<Warehouse>(
                value: _selectedWarehouse,
                decoration: const InputDecoration(labelText: 'Magasin'),
                items: _warehouses.map((w) => DropdownMenuItem(value: w, child: Text(w.name))).toList(),
                onChanged: (v) {
                  setState(() => _selectedWarehouse = v);
                  _loadStockStatus();
                },
              ),
            ),

          if (_stockData.isNotEmpty)
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: const Color(0xFF1B5E20).withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFF1B5E20).withOpacity(0.3)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _infoColumn('Région', _stockData['warehouse_region'] ?? '-'),
                  _infoColumn('Total Sacs', '${_stockData['total_current_sacs'] ?? 0}'),
                  _infoColumn('Produits', '${_products.length}'),
                ],
              ),
            ),

          const SizedBox(height: 8),
          const Divider(),

          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _products.isEmpty
                    ? const Center(child: Text('Aucun produit enregistré'))
                    : ListView.builder(
                        itemCount: _products.length,
                        itemBuilder: (context, index) {
                          final p = _products[index];
                          return Card(
                            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                            child: ListTile(
                              leading: CircleAvatar(
                                backgroundColor: const Color(0xFF1B5E20),
                                child: Text('${p['format_kg']}kg', style: const TextStyle(fontSize: 10, color: Colors.white)),
                              ),
                              title: Text(p['product_name'] ?? 'Produit'),
                              subtitle: Text('Format: ${p['format_kg']}kg'),
                              trailing: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text('${p['total_sacs']} sacs', style: const TextStyle(fontWeight: FontWeight.bold)),
                                  Text('${p['total_kg']} kg', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _infoColumn(String label, String value) {
    return Column(
      children: [
        Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
        const SizedBox(height: 4),
        Text(value, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
      ],
    );
  }
}
