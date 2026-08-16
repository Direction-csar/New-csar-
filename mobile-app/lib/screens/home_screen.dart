import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:geolocator/geolocator.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';
import '../services/local_db_service.dart';
import '../services/sync_service.dart';
import 'markets_screen.dart';
import 'collection_screen.dart';
import 'history_screen.dart';
import 'stats_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _pendingCount = 0;
  bool _syncing = false;
  StreamSubscription<ConnectivityResult>? _connectivitySub;
  Timer? _locationTimer;

  @override
  void initState() {
    super.initState();
    _refreshPendingCount();
    _connectivitySub = Connectivity().onConnectivityChanged.listen((result) {
      if (result != ConnectivityResult.none) {
        _sync(silent: true);
      }
    });
    _startLocationTracking();
  }

  @override
  void dispose() {
    _connectivitySub?.cancel();
    _locationTimer?.cancel();
    super.dispose();
  }

  Future<void> _refreshPendingCount() async {
    final count = await LocalDbService.countPending();
    if (mounted) setState(() => _pendingCount = count);
  }

  Future<void> _sync({bool silent = false}) async {
    if (_syncing) return;
    final token = context.read<AuthService>().token;
    if (token == null) return;

    final connectivity = await Connectivity().checkConnectivity();
    if (connectivity == ConnectivityResult.none) {
      if (!silent && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Aucune connexion réseau'), backgroundColor: Colors.orange),
        );
      }
      return;
    }

    setState(() => _syncing = true);
    final result = await SyncService.syncPendingCollections(token);
    setState(() {
      _syncing = false;
      _pendingCount = result.remaining;
    });

    if (!mounted) return;
    if (result.synced > 0 || !silent) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            result.synced > 0
                ? '${result.synced} collecte(s) synchronisée(s)${result.remaining > 0 ? ', ${result.remaining} restante(s)' : ''}'
                : (result.remaining > 0 ? '${result.remaining} collecte(s) en attente' : 'Aucune collecte à synchroniser'),
          ),
          backgroundColor: result.synced > 0 ? Colors.green : Colors.blueGrey,
        ),
      );
    }
  }

  void _startLocationTracking() {
    _sendLocationOnce();
    _locationTimer = Timer.periodic(const Duration(minutes: 5), (_) => _sendLocationOnce());
  }

  Future<void> _sendLocationOnce() async {
    final token = context.read<AuthService>().token;
    if (token == null) return;
    try {
      LocationPermission perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }
      if (perm == LocationPermission.deniedForever || perm == LocationPermission.denied) {
        return;
      }
      final pos = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
        timeLimit: const Duration(seconds: 15),
      );
      await ApiService.updateLocation(token, pos.latitude, pos.longitude);
    } catch (_) {
      // Échec silencieux : suivi temps réel best-effort
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthService>();
    final collector = auth.collector;

    return Scaffold(
      appBar: AppBar(
        title: const Text('CSAR Collecte'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await auth.logout();
              if (context.mounted) {
                Navigator.pushReplacementNamed(context, '/');
              }
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF1B5E20), Color(0xFF388E3C)],
                ),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.person_outline, color: Colors.white70, size: 36),
                  const SizedBox(height: 8),
                  Text(
                    'Bonjour, ${collector?['name'] ?? 'Collecteur'}',
                    style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
                  ),
                  Text(
                    collector?['email'] ?? '',
                    style: const TextStyle(color: Colors.white70, fontSize: 13),
                  ),
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white24,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      'Collectes : ${collector?['total_collections'] ?? 0}',
                      style: const TextStyle(color: Colors.white, fontSize: 12),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 28),
            const Text(
              'Actions',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1B5E20)),
            ),
            const SizedBox(height: 16),
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisSpacing: 16,
              mainAxisSpacing: 16,
              children: [
                _ActionCard(
                  icon: Icons.add_circle_outline,
                  label: 'Nouvelle Collecte',
                  color: const Color(0xFF1B5E20),
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const CollectionScreen()),
                  ).then((_) => _refreshPendingCount()),
                ),
                _ActionCard(
                  icon: Icons.store_outlined,
                  label: 'Marchés',
                  color: const Color(0xFF0288D1),
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MarketsScreen())),
                ),
                _ActionCard(
                  icon: Icons.history,
                  label: 'Historique',
                  color: const Color(0xFFF57C00),
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const HistoryScreen())),
                ),
                _ActionCard(
                  icon: Icons.bar_chart,
                  label: 'Statistiques',
                  color: const Color(0xFF0097A7),
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const StatsScreen())),
                ),
                _ActionCard(
                  icon: Icons.sync,
                  label: _syncing
                      ? 'Synchronisation...'
                      : (_pendingCount > 0 ? 'Synchroniser ($_pendingCount)' : 'Synchroniser'),
                  color: _pendingCount > 0 ? Colors.red.shade700 : const Color(0xFF7B1FA2),
                  onTap: _syncing ? () {} : () => _sync(),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _ActionCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _ActionCard({required this.icon, required this.label, required this.color, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: color.withOpacity(0.3)),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 44, color: color),
            const SizedBox(height: 10),
            Text(label, style: TextStyle(fontWeight: FontWeight.w600, color: color, fontSize: 13), textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }
}
