import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/warehouse.dart';
import '../models/stock_movement.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';
import 'movement_form_screen.dart';

class StockSheetScreen extends StatefulWidget {
  final Warehouse warehouse;
  const StockSheetScreen({super.key, required this.warehouse});

  @override
  State<StockSheetScreen> createState() => _StockSheetScreenState();
}

class _StockSheetScreenState extends State<StockSheetScreen> {
  List<StockMovement> _movements = [];
  bool _loading = true;
  double _balanceSacs = 0;
  double _balanceKg = 0;

  @override
  void initState() {
    super.initState();
    _loadSheet();
  }

  Future<void> _loadSheet() async {
    setState(() => _loading = true);
    try {
      final hasNet = await ApiService.hasConnection();
      if (hasNet) {
        final data = await ApiService.getStockSheet(widget.warehouse.id);
        setState(() {
          _balanceSacs = (data['balance_sacs'] ?? 0).toDouble();
          _balanceKg = (data['balance_kg'] ?? 0).toDouble();
          _movements = (data['movements']?['data'] as List? ?? [])
              .map((e) => StockMovement.fromJson(e))
              .toList();
        });
      } else {
        final local = await DatabaseService.getMovementsByWarehouse(widget.warehouse.id);
        setState(() => _movements = local);
      }
    } catch (e) {
      final local = await DatabaseService.getMovementsByWarehouse(widget.warehouse.id);
      setState(() => _movements = local);
    }
    setState(() => _loading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.warehouse.name),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => MovementFormScreen(warehouse: widget.warehouse)),
            ).then((_) => _loadSheet()),
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Header avec solde
                Container(
                  padding: const EdgeInsets.all(16),
                  color: const Color(0xFF1B5E20),
                  child: Column(
                    children: [
                      const Text('SOLDE ACTUEL', style: TextStyle(color: Colors.white70, fontSize: 12)),
                      const SizedBox(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          _balanceBox('Sacs', _balanceSacs.toStringAsFixed(0)),
                          const SizedBox(width: 24),
                          _balanceBox('KG', _balanceKg.toStringAsFixed(0)),
                        ],
                      ),
                    ],
                  ),
                ),

                // Table header
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
                  color: Colors.grey.shade200,
                  child: const Row(
                    children: [
                      Expanded(flex: 2, child: Text('Date', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11))),
                      Expanded(flex: 3, child: Text('Libellé', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11))),
                      Expanded(flex: 2, child: Text('Entrée', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11), textAlign: TextAlign.center)),
                      Expanded(flex: 2, child: Text('Sortie', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11), textAlign: TextAlign.center)),
                      Expanded(flex: 2, child: Text('Solde', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11), textAlign: TextAlign.center)),
                    ],
                  ),
                ),

                // Liste
                Expanded(
                  child: _movements.isEmpty
                      ? const Center(child: Text('Aucun mouvement'))
                      : ListView.builder(
                          itemCount: _movements.length,
                          itemBuilder: (context, i) {
                            final m = _movements[i];
                            return Container(
                              decoration: BoxDecoration(
                                border: Border(bottom: BorderSide(color: Colors.grey.shade300)),
                                color: i % 2 == 0 ? Colors.white : Colors.grey.shade50,
                              ),
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 10),
                              child: Row(
                                children: [
                                  Expanded(flex: 2, child: Text(m.movementDate, style: const TextStyle(fontSize: 11))),
                                  Expanded(flex: 3, child: Text(m.label, style: const TextStyle(fontSize: 11), maxLines: 2, overflow: TextOverflow.ellipsis)),
                                  Expanded(
                                    flex: 2,
                                    child: Text(
                                      m.entrySacs > 0 ? '+${m.entrySacs.toStringAsFixed(0)}' : '',
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(fontSize: 11, color: Colors.green, fontWeight: FontWeight.bold),
                                    ),
                                  ),
                                  Expanded(
                                    flex: 2,
                                    child: Text(
                                      m.exitSacs > 0 ? '-${m.exitSacs.toStringAsFixed(0)}' : '',
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(fontSize: 11, color: Colors.red, fontWeight: FontWeight.bold),
                                    ),
                                  ),
                                  Expanded(
                                    flex: 2,
                                    child: Text(
                                      m.balanceSacs.toStringAsFixed(0),
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                ),
              ],
            ),
    );
  }

  Widget _balanceBox(String label, String value) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.2),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        children: [
          Text(value, style: const TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.bold)),
          Text(label, style: const TextStyle(color: Colors.white70, fontSize: 12)),
        ],
      ),
    );
  }
}
