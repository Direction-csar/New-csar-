import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';
import '../services/api_service.dart';

class ReceiptScreen extends StatefulWidget {
  final String reference;
  const ReceiptScreen({super.key, required this.reference});

  @override
  State<ReceiptScreen> createState() => _ReceiptScreenState();
}

class _ReceiptScreenState extends State<ReceiptScreen> {
  bool _loading = true;
  Map<String, dynamic> _receipt = {};

  @override
  void initState() {
    super.initState();
    _loadReceipt();
  }

  Future<void> _loadReceipt() async {
    try {
      final data = await ApiService.getReceipt(widget.reference);
      setState(() {
        _receipt = data['receipt'] ?? {};
        _loading = false;
      });
    } catch (e) {
      setState(() => _loading = false);
    }
  }

  void _shareReceipt() {
    final text = '''
=== REÇU CSAR ===
Référence: ${_receipt['reference']}
Type: ${_receipt['type_label']}
Magasin: ${_receipt['warehouse_name']} (${_receipt['warehouse_region']})
Date: ${_receipt['movement_date']}
Opérateur: ${_receipt['created_by']}
Quantité: ${_receipt['quantity_sacs']} sacs
Solde avant: ${_receipt['quantity_before']} sacs
Solde après: ${_receipt['quantity_after']} sacs
Motif: ${_receipt['reason']}
=================
''';    Share.share(text, subject: 'Reçu ${_receipt['reference']}');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Reçu / Traçabilité'),
        actions: [
          IconButton(icon: const Icon(Icons.share), onPressed: _shareReceipt),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _receipt.isEmpty
              ? const Center(child: Text('Reçu non trouvé'))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Card(
                    elevation: 4,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Center(
                            child: Column(
                              children: [
                                Image.asset('assets/images/logo_csar.png', height: 60),
                                const SizedBox(height: 8),
                                const Text('REÇU MOUVEMENT', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1B5E20))),
                                const SizedBox(height: 4),
                                Text('Réf: ${_receipt['reference']}', style: const TextStyle(color: Colors.grey)),
                              ],
                            ),
                          ),
                          const Divider(height: 32),
                          _row('Type', _receipt['type_label'] ?? '-'),
                          _row('Magasin', _receipt['warehouse_name'] ?? '-'),
                          _row('Région', _receipt['warehouse_region'] ?? '-'),
                          _row('Date opération', _receipt['movement_date'] ?? '-'),
                          _row('Date création', _receipt['created_at'] ?? '-'),
                          _row('Opérateur', _receipt['created_by'] ?? '-'),
                          const Divider(height: 32),
                          _row('Quantité', '${_receipt['quantity_sacs']} sacs'),
                          _row('Solde avant', '${_receipt['quantity_before']} sacs'),
                          _row('Solde après', '${_receipt['quantity_after']} sacs', isBold: true),
                          const Divider(height: 32),
                          const Text('Motif / Produit:', style: TextStyle(fontWeight: FontWeight.bold)),
                          const SizedBox(height: 4),
                          Text(_receipt['reason'] ?? '-', style: const TextStyle(fontSize: 14)),
                          const SizedBox(height: 24),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              onPressed: _shareReceipt,
                              icon: const Icon(Icons.share),
                              label: const Text('PARTAGER LE REÇU'),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
    );
  }

  Widget _row(String label, String value, {bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: Colors.grey)),
          Text(value, style: TextStyle(fontWeight: isBold ? FontWeight.bold : FontWeight.normal)),
        ],
      ),
    );
  }
}
