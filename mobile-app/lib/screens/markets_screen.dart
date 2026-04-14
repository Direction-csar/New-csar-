import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';
import 'collection_screen.dart';

class MarketsScreen extends StatefulWidget {
  const MarketsScreen({super.key});

  @override
  State<MarketsScreen> createState() => _MarketsScreenState();
}

class _MarketsScreenState extends State<MarketsScreen> {
  List<dynamic> _markets = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final token = context.read<AuthService>().token!;
    try {
      final res = await ApiService.getMarkets(token);
      setState(() {
        _markets = res['data'] ?? [];
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Erreur de chargement';
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Marchés')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: Colors.red)))
              : _markets.isEmpty
                  ? const Center(child: Text('Aucun marché disponible'))
                  : RefreshIndicator(
                      onRefresh: _load,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _markets.length,
                        itemBuilder: (_, i) {
                          final m = _markets[i];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            child: ListTile(
                              leading: Container(
                                width: 46,
                                height: 46,
                                decoration: BoxDecoration(
                                  color: const Color(0xFF1B5E20).withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Icon(Icons.store, color: Color(0xFF1B5E20)),
                              ),
                              title: Text(m['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)),
                              subtitle: Text('${m['commune'] ?? ''} · ${m['market_type'] ?? ''}'),
                              trailing: ElevatedButton(
                                style: ElevatedButton.styleFrom(
                                  minimumSize: const Size(80, 34),
                                  padding: EdgeInsets.zero,
                                ),
                                onPressed: () => Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => CollectionScreen(preselectedMarket: m),
                                  ),
                                ),
                                child: const Text('Collecter', style: TextStyle(fontSize: 12)),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
    );
  }
}
