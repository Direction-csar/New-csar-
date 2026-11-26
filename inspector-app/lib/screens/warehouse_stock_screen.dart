import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import 'package:provider/provider.dart';

/// Consultation en lecture seule du stock officiel d'un magasin.
class WarehouseStockScreen extends StatefulWidget {
  final Map<String, dynamic> warehouse;
  const WarehouseStockScreen({super.key, required this.warehouse});

  @override
  State<WarehouseStockScreen> createState() => _WarehouseStockScreenState();
}

class _WarehouseStockScreenState extends State<WarehouseStockScreen> {
  bool _loading = true;
  Map<String, dynamic>? _data;
  String? _error;

  String? get _token => context.read<AuthService>().token;

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
    final res = await ApiService.getStockStatus(
        _token!, (widget.warehouse['id'] as num).toInt());
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (res['success'] == true) {
        _data = res;
      } else {
        _error = res['message'] as String? ?? 'Stock indisponible.';
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text((widget.warehouse['name'] ?? 'Magasin') as String)),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!),
                      const SizedBox(height: 12),
                      FilledButton(onPressed: _load, child: const Text('Réessayer')),
                    ],
                  ),
                )
              : _content(),
    );
  }

  Widget _content() {
    final products = (_data!['products'] as List? ?? [])
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();
    final expiring = (_data!['expiring'] as List? ?? [])
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: ListTile(
              leading: const Icon(Icons.inventory_2_outlined),
              title: const Text('Stock total déclaré'),
              trailing: Text('${_data!['total_current_sacs'] ?? 0} sacs',
                  style: const TextStyle(fontWeight: FontWeight.bold)),
            ),
          ),
          const SizedBox(height: 12),
          const Text('Produits', style: TextStyle(fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          if (products.isEmpty)
            const Card(
                child: Padding(
                    padding: EdgeInsets.all(16),
                    child: Center(child: Text('Aucun stock enregistré.'))))
          else
            ...products.map((p) => Card(
                  child: ListTile(
                    leading: const Icon(Icons.kitchen_outlined),
                    title: Text((p['product_name'] ?? 'Produit') as String),
                    subtitle: Text(
                        'Format : ${p['format_kg']} ${p['unit'] ?? 'kg'}'),
                    trailing: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text('${p['total_sacs'] ?? 0}',
                            style: const TextStyle(fontWeight: FontWeight.bold)),
                        Text('${p['total_kg'] ?? 0} ${p['unit'] ?? 'kg'}',
                            style: const TextStyle(fontSize: 12, color: Colors.black54)),
                      ],
                    ),
                  ),
                )),
          if (expiring.isNotEmpty) ...[
            const SizedBox(height: 16),
            const Text('Péremptions à surveiller (30 j)',
                style: TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            ...expiring.map((e) => Card(
                  color: Colors.orange.shade50,
                  child: ListTile(
                    dense: true,
                    leading: const Icon(Icons.warning_amber_outlined),
                    title: Text((e['product_name'] ?? '') as String),
                    subtitle: Text('Expire le ${e['expiry_date']}'),
                    trailing: Text('${e['quantity']}'),
                  ),
                )),
          ],
        ],
      ),
    );
  }
}
