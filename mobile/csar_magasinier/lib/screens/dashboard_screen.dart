import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';
import '../models/warehouse.dart';
import '../models/stock_movement.dart';
import 'movement_form_screen.dart';
import 'sync_screen.dart';
import 'transfer_form_screen.dart';
import 'inventory_form_screen.dart';
import 'stock_status_screen.dart';
import 'receipt_screen.dart';
import 'movement_history_screen.dart';
import 'receipt_history_screen.dart';

/// Seuil de stock bas par défaut (en sacs), aligné sur le serveur.
const double kLowStockThresholdSacs = 100;

/// Alerte affichée dans la section « ALERTES STOCK ».
class _AlertItem {
  final String title;
  final String subtitle;
  final String quantity;
  final String severity; // critical | warning | info | anomaly
  final IconData icon;

  const _AlertItem({
    required this.title,
    required this.subtitle,
    required this.quantity,
    required this.severity,
    required this.icon,
  });
}

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  static const _green = Color(0xFF1B5E20);
  static const _greenLight = Color(0xFF2E7D32);
  static const _bg = Color(0xFFF6F8F5);

  List<Warehouse> _warehouses = [];
  Warehouse? _selectedWarehouse;
  List<StockMovement> _recentMovements = [];
  List<_AlertItem> _alerts = [];
  int _pendingSync = 0;
  int _entriesToday = 0;
  int _exitsToday = 0;
  bool _loading = true;
  bool _offline = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final pending = await DatabaseService.getPendingSync();
      _pendingSync = pending.length;

      final hasNet = await ApiService.hasConnection();
      _offline = !hasNet;

      List<Warehouse> warehouses = [];
      if (hasNet) {
        warehouses = await ApiService.getWarehouses();
        await DatabaseService.saveWarehouses(warehouses.map((w) => w.toJson()).toList());
      } else {
        final local = await DatabaseService.getWarehouses();
        warehouses = local.map((m) => Warehouse.fromJson(m)).toList();
      }

      final prefs = await SharedPreferences.getInstance();
      final savedId = prefs.getInt('selected_warehouse_id');
      Warehouse? selected = warehouses.isNotEmpty
          ? warehouses.firstWhere((w) => w.id == savedId, orElse: () => warehouses.first)
          : null;

      List<StockMovement> recent = [];
      List<_AlertItem> alerts = [];

      if (hasNet) {
        // Alertes seuil serveur (par magasin)
        try {
          final res = await ApiService.getAlerts();
          for (final a in (res['alerts'] ?? [])) {
            alerts.add(_AlertItem(
              title: a['warehouse_name'] ?? 'Magasin',
              subtitle: 'Stock bas (${a['percentage']}%)',
              quantity: '${(a['current_stock'] as num?)?.toStringAsFixed(0) ?? '0'} sacs',
              severity: a['severity'] == 'critical' ? 'critical' : 'warning',
              icon: Icons.warehouse,
            ));
          }
        } catch (_) {}

        if (selected != null) {
          // Mouvements récents du magasin sélectionné
          try {
            final sheet = await ApiService.getStockSheet(selected.id);
            recent = (sheet['movements']?['data'] as List? ?? [])
                .map((e) => StockMovement.fromJson(e))
                .toList();
          } catch (_) {}

          // Alertes par produit (stock-status)
          try {
            final status = await ApiService.getStockStatus(selected.id);
            for (final p in (status['products'] ?? [])) {
              final sacs = (p['total_sacs'] as num?)?.toDouble() ?? 0;
              if (sacs <= 0) {
                alerts.add(_AlertItem(
                  title: p['product_name'] ?? 'Produit',
                  subtitle: 'Stock épuisé · ${selected.name}',
                  quantity: '0 sac',
                  severity: 'critical',
                  icon: Icons.inventory_2,
                ));
              } else if (sacs <= kLowStockThresholdSacs) {
                alerts.add(_AlertItem(
                  title: p['product_name'] ?? 'Produit',
                  subtitle: 'Seuil bientôt atteint · ${selected.name}',
                  quantity: '${sacs.toStringAsFixed(0)} sacs',
                  severity: 'warning',
                  icon: Icons.inventory_2,
                ));
              }
            }
          } catch (_) {}
        }

        // Écarts d'inventaire → anomalie
        for (final m in recent) {
          if (m.movementType == 'adjustment' &&
              m.inventoryDiff != null &&
              m.inventoryDiff != 0) {
            alerts.add(_AlertItem(
              title: 'Écart d\'inventaire',
              subtitle: m.label,
              quantity: '${m.inventoryDiff! > 0 ? '+' : ''}${m.inventoryDiff!.toStringAsFixed(0)} sacs',
              severity: 'anomaly',
              icon: Icons.report_problem,
            ));
          }
        }
      } else {
        // Hors ligne : mouvements locaux du magasin
        if (selected != null) {
          recent = await DatabaseService.getMovementsByWarehouse(selected.id);
        }
      }

      // Compteurs du jour (données réelles)
      final today = DateTime.now().toIso8601String().substring(0, 10);
      _entriesToday = recent.where((m) {
        final d = m.movementDate.isNotEmpty ? m.movementDate : (m.createdAt ?? '');
        return d.startsWith(today) && (m.movementType == 'entry' || m.movementType == 'report');
      }).length;
      _exitsToday = recent.where((m) {
        final d = m.movementDate.isNotEmpty ? m.movementDate : (m.createdAt ?? '');
        return d.startsWith(today) && (m.movementType == 'exit' || m.movementType == 'transfer');
      }).length;

      setState(() {
        _warehouses = warehouses;
        _selectedWarehouse = selected;
        _recentMovements = recent.take(5).toList();
        _alerts = alerts;
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _loading = false;
        _error = 'Impossible de charger les données';
      });
    }
  }

  Future<void> _selectWarehouse(Warehouse w) async {
    setState(() => _selectedWarehouse = w);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt('selected_warehouse_id', w.id);
    await _loadData();
  }

  String _firstName(String? fullName) {
    if (fullName == null || fullName.trim().isEmpty) return 'Magasinier';
    return fullName.trim().split(RegExp(r'\s+')).first;
  }

  String _initials(String? fullName) {
    if (fullName == null || fullName.trim().isEmpty) return 'M';
    final parts = fullName.trim().split(RegExp(r'\s+')).where((p) => p.isNotEmpty).toList();
    if (parts.length == 1) return parts.first.substring(0, 1).toUpperCase();
    return (parts.first.substring(0, 1) + parts.last.substring(0, 1)).toUpperCase();
  }

  /// 1 sac = 50 kg (convention serveur) → conversion fiable kg/tonnes.
  String _formatTonnage(double sacs) {
    final kg = sacs * 50;
    if (kg >= 1000) {
      return '${(kg / 1000).toStringAsFixed(1).replaceAll('.', ',')} t';
    }
    return '${kg.toStringAsFixed(0)} kg';
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    return Scaffold(
      backgroundColor: _bg,
      body: _loading && _warehouses.isEmpty
          ? const Center(child: CircularProgressIndicator(color: _green))
          : RefreshIndicator(
              onRefresh: _loadData,
              color: _green,
              child: CustomScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                slivers: [
                  _buildHeader(user),
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (_offline) _offlineBanner(),
                          if (_error != null) _errorBanner(),
                          const SizedBox(height: 8),
                          _buildStatsRow(),
                          const SizedBox(height: 20),
                          _buildAlertsSection(),
                          const SizedBox(height: 20),
                          _buildQuickActions(),
                          const SizedBox(height: 20),
                          _buildRecentMovements(),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }

  // ---------- EN-TÊTE ----------
  Widget _buildHeader(Map<String, dynamic>? user) {
    return SliverToBoxAdapter(
      child: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [_green, _greenLight],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.only(
            bottomLeft: Radius.circular(24),
            bottomRight: Radius.circular(24),
          ),
        ),
        child: SafeArea(
          bottom: false,
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
            child: Column(
              children: [
                // Ligne 1 : logo + titre + notifications + profil
                Row(
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.warehouse, color: _green, size: 26),
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'CSAR MAGASINIER',
                            style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold, letterSpacing: 0.5),
                          ),
                          Text(
                            'Gestion des stocks',
                            style: TextStyle(color: Colors.white70, fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                    Stack(
                      children: [
                        IconButton(
                          onPressed: _showAlerts,
                          icon: const Icon(Icons.notifications_outlined, color: Colors.white, size: 26),
                        ),
                        if (_alerts.isNotEmpty)
                          Positioned(
                            right: 6,
                            top: 6,
                            child: Container(
                              padding: const EdgeInsets.all(4),
                              decoration: const BoxDecoration(color: Colors.red, shape: BoxShape.circle),
                              constraints: const BoxConstraints(minWidth: 18, minHeight: 18),
                              child: Text(
                                '${_alerts.length}',
                                style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                                textAlign: TextAlign.center,
                              ),
                            ),
                          ),
                      ],
                    ),
                    CircleAvatar(
                      radius: 20,
                      backgroundColor: Colors.white24,
                      child: Text(
                        _initials(user?['name']?.toString()),
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
                // Ligne 2 : salutation + sélecteur de magasin
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.wb_sunny_outlined, color: Colors.amber, size: 20),
                              const SizedBox(width: 8),
                              Flexible(
                                child: Text(
                                  'Bonjour, ${_firstName(user?['name']?.toString())}',
                                  style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          const Text(
                            'Vue d\'ensemble du magasin',
                            style: TextStyle(color: Colors.white70, fontSize: 13),
                          ),
                        ],
                      ),
                    ),
                    if (_warehouses.isNotEmpty)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: Colors.white30),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<Warehouse>(
                            value: _selectedWarehouse,
                            dropdownColor: _green,
                            icon: const Icon(Icons.keyboard_arrow_down, color: Colors.white, size: 20),
                            style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
                            isDense: true,
                            items: _warehouses
                                .map((w) => DropdownMenuItem(
                                      value: w,
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          const Icon(Icons.warehouse, color: Colors.white70, size: 16),
                                          const SizedBox(width: 6),
                                          Flexible(child: Text(w.name, overflow: TextOverflow.ellipsis)),
                                        ],
                                      ),
                                    ))
                                .toList(),
                            onChanged: (w) {
                              if (w != null) _selectWarehouse(w);
                            },
                          ),
                        ),
                      ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ---------- BANDEAUX ----------
  Widget _offlineBanner() {
    return Container(
      margin: const EdgeInsets.only(top: 12),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.orange.shade50,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.orange.shade300),
      ),
      child: Row(
        children: [
          Icon(Icons.wifi_off, color: Colors.orange.shade800, size: 18),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              'Hors connexion — données locales affichées',
              style: TextStyle(fontSize: 12, color: Colors.orange.shade800, fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }

  Widget _errorBanner() {
    return Container(
      margin: const EdgeInsets.only(top: 12),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.red.shade50,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.red.shade300),
      ),
      child: Row(
        children: [
          Icon(Icons.error_outline, color: Colors.red.shade800, size: 18),
          const SizedBox(width: 8),
          Expanded(child: Text(_error!, style: TextStyle(fontSize: 12, color: Colors.red.shade800))),
          TextButton(onPressed: _loadData, child: const Text('Réessayer')),
        ],
      ),
    );
  }

  // ---------- 4 CARTES STATISTIQUES ----------
  Widget _buildStatsRow() {
    final stockSacs = _selectedWarehouse?.currentStock ?? 0;
    return Row(
      children: [
        _statCard('Stock dispo.', _formatTonnage(stockSacs), '${stockSacs.toStringAsFixed(0)} sacs', Icons.inventory_2, _greenLight),
        const SizedBox(width: 10),
        _statCard('Entrées auj.', '$_entriesToday', 'mouvements', Icons.arrow_downward, _greenLight),
        const SizedBox(width: 10),
        _statCard('Sorties auj.', '$_exitsToday', 'mouvements', Icons.arrow_upward, Colors.orange.shade700),
        const SizedBox(width: 10),
        _statCard('En attente', '$_pendingSync', 'à synchroniser', Icons.pending_actions, Colors.blue.shade700),
      ],
    );
  }

  Widget _statCard(String label, String value, String sub, IconData icon, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 4),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8, offset: const Offset(0, 2))],
        ),
        child: Column(
          children: [
            CircleAvatar(
              radius: 16,
              backgroundColor: color.withValues(alpha: 0.12),
              child: Icon(icon, color: color, size: 18),
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: TextStyle(fontSize: 10, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 4),
            FittedBox(
              fit: BoxFit.scaleDown,
              child: Text(value, style: TextStyle(fontSize: 19, fontWeight: FontWeight.bold, color: color)),
            ),
            Text(sub, style: TextStyle(fontSize: 9, color: Colors.grey.shade500)),
          ],
        ),
      ),
    );
  }

  // ---------- ALERTES STOCK ----------
  Widget _buildAlertsSection() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 8, 6),
            child: Row(
              children: [
                Icon(Icons.warning_amber_rounded, color: Colors.red.shade700, size: 22),
                const SizedBox(width: 8),
                Text(
                  'ALERTES STOCK',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.red.shade700, letterSpacing: 0.5),
                ),
                const Spacer(),
                TextButton(
                  onPressed: _showAlerts,
                  child: Text('Voir tout ›', style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.w600)),
                ),
              ],
            ),
          ),
          if (_alerts.isEmpty)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 18),
              child: Row(
                children: [
                  Icon(Icons.check_circle_outline, color: Colors.green.shade600, size: 20),
                  const SizedBox(width: 8),
                  Text('Aucun problème de stock détecté', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                ],
              ),
            )
          else
            ..._alerts.take(3).map(_alertTile),
          const SizedBox(height: 8),
        ],
      ),
    );
  }

  Widget _alertTile(_AlertItem a) {
    Color chipColor;
    String chipLabel;
    Color tileColor;
    switch (a.severity) {
      case 'critical':
        chipColor = Colors.red;
        chipLabel = 'Critique';
        tileColor = Colors.red.shade50;
        break;
      case 'anomaly':
        chipColor = Colors.deepPurple;
        chipLabel = 'Anomalie';
        tileColor = const Color(0xFFF3E5F5);
        break;
      case 'info':
        chipColor = Colors.blue;
        chipLabel = 'À surveiller';
        tileColor = Colors.blue.shade50;
        break;
      default:
        chipColor = Colors.orange;
        chipLabel = 'Attention';
        tileColor = Colors.orange.shade50;
    }

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      child: Material(
        color: tileColor,
        borderRadius: BorderRadius.circular(12),
        child: InkWell(
          borderRadius: BorderRadius.circular(12),
          onTap: _showAlerts,
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 18,
                  backgroundColor: Colors.white,
                  child: Icon(a.icon, color: chipColor, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(a.title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14), overflow: TextOverflow.ellipsis),
                      Text(a.subtitle, style: TextStyle(fontSize: 12, color: Colors.grey.shade700), overflow: TextOverflow.ellipsis),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(a.quantity, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(color: chipColor, borderRadius: BorderRadius.circular(10)),
                      child: Text(chipLabel, style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showAlerts() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.55,
        maxChildSize: 0.9,
        minChildSize: 0.35,
        expand: false,
        builder: (ctx, scroll) => Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2))),
              ),
              const SizedBox(height: 16),
              Text(
                'ALERTES STOCK (${_alerts.length})',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.red.shade700),
              ),
              const SizedBox(height: 12),
              Expanded(
                child: _alerts.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.check_circle_outline, size: 48, color: Colors.green.shade400),
                            const SizedBox(height: 8),
                            const Text('Aucun problème de stock détecté'),
                          ],
                        ),
                      )
                    : ListView(
                        controller: scroll,
                        children: _alerts.map(_alertTile).toList(),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ---------- ACTIONS RAPIDES ----------
  Widget _buildQuickActions() {
    final actions = <({String label, IconData icon, Color color, VoidCallback onTap})>[
      (
        label: 'Entrée',
        icon: Icons.arrow_downward,
        color: _greenLight,
        onTap: () => _open(() => MovementFormScreen(warehouse: _selectedWarehouse)),
      ),
      (
        label: 'Sortie',
        icon: Icons.arrow_upward,
        color: Colors.orange.shade700,
        onTap: () => _open(() => MovementFormScreen(warehouse: _selectedWarehouse)),
      ),
      (
        label: 'Transfert',
        icon: Icons.swap_horiz,
        color: Colors.blue.shade700,
        onTap: () => _open(() => TransferFormScreen(warehouse: _selectedWarehouse)),
      ),
      (
        label: 'Inventaire',
        icon: Icons.fact_check,
        color: Colors.deepPurple,
        onTap: () => _open(() => InventoryFormScreen(warehouse: _selectedWarehouse)),
      ),
      (
        label: 'Stocks',
        icon: Icons.bar_chart,
        color: Colors.teal,
        onTap: () => _open(() => StockStatusScreen(warehouse: _selectedWarehouse)),
      ),
      (
        label: 'Sync',
        icon: Icons.sync,
        color: Colors.cyan.shade700,
        onTap: () => _open(() => const SyncScreen()),
      ),
      (
        label: 'Reçus',
        icon: Icons.receipt_long,
        color: Colors.indigo,
        onTap: () => _open(() => const ReceiptHistoryScreen()),
      ),
      (
        label: 'Reçu n°',
        icon: Icons.search,
        color: Colors.grey.shade700,
        onTap: _showReceiptDialog,
      ),
    ];

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('ACTIONS RAPIDES', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.grey, letterSpacing: 0.5)),
          const SizedBox(height: 12),
          GridView.count(
            crossAxisCount: 4,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            mainAxisSpacing: 10,
            crossAxisSpacing: 10,
            childAspectRatio: 0.85,
            children: actions.map(_quickAction).toList(),
          ),
        ],
      ),
    );
  }

  Widget _quickAction(({String label, IconData icon, Color color, VoidCallback onTap}) a) {
    return InkWell(
      onTap: a.onTap,
      borderRadius: BorderRadius.circular(12),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          CircleAvatar(
            radius: 22,
            backgroundColor: a.color.withValues(alpha: 0.12),
            child: Icon(a.icon, color: a.color, size: 22),
          ),
          const SizedBox(height: 6),
          Text(a.label, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600), overflow: TextOverflow.ellipsis),
        ],
      ),
    );
  }

  Future<void> _open(Widget Function() builder) async {
    await Navigator.push(context, MaterialPageRoute(builder: (_) => builder()));
    _loadData();
  }

  // ---------- MOUVEMENTS RÉCENTS ----------
  Widget _buildRecentMovements() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 8, 6),
            child: Row(
              children: [
                Icon(Icons.history, color: Colors.grey.shade700, size: 22),
                const SizedBox(width: 8),
                const Text('Mouvements récents', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                const Spacer(),
                TextButton(
                  onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MovementHistoryScreen())),
                  child: Text('Voir tout ›', style: TextStyle(color: Colors.grey.shade700, fontWeight: FontWeight.w600)),
                ),
              ],
            ),
          ),
          if (_recentMovements.isEmpty)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 18),
              child: Row(
                children: [
                  Icon(Icons.inbox_outlined, color: Colors.grey.shade400, size: 20),
                  const SizedBox(width: 8),
                  Text('Aucun mouvement récent', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                ],
              ),
            )
          else
            ..._recentMovements.map(_movementTile),
          const SizedBox(height: 8),
        ],
      ),
    );
  }

  Widget _movementTile(StockMovement m) {
    final isEntry = m.movementType == 'entry' || m.movementType == 'report';
    final isTransfer = m.movementType == 'transfer';
    final isAdjustment = m.movementType == 'adjustment';

    Color color;
    IconData icon;
    String typeLabel;
    if (isTransfer) {
      color = Colors.blue.shade700;
      icon = Icons.swap_horiz;
      typeLabel = 'Transfert';
    } else if (isAdjustment) {
      color = Colors.deepPurple;
      icon = Icons.fact_check;
      typeLabel = 'Ajustement';
    } else if (isEntry) {
      color = _greenLight;
      icon = Icons.arrow_downward;
      typeLabel = 'Entrée';
    } else {
      color = Colors.orange.shade700;
      icon = Icons.arrow_upward;
      typeLabel = 'Sortie';
    }

    final qty = isEntry ? m.entrySacs : (isAdjustment ? (m.inventoryDiff ?? 0).abs() : m.exitSacs);
    final sign = isEntry ? '+' : (isAdjustment ? (m.inventoryDiff != null && m.inventoryDiff! >= 0 ? '+' : '-') : '-');

    String time = '';
    if (m.createdAt != null) {
      final dt = DateTime.tryParse(m.createdAt!);
      if (dt != null) {
        time = '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
      }
    }

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => ReceiptScreen(reference: m.reference))),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(
            border: Border(bottom: BorderSide(color: Colors.grey.shade100)),
          ),
          child: Row(
            children: [
              CircleAvatar(radius: 18, backgroundColor: color.withValues(alpha: 0.12), child: Icon(icon, color: color, size: 18)),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(m.label, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13), overflow: TextOverflow.ellipsis),
                    const SizedBox(height: 2),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                      decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(6)),
                      child: Text(typeLabel, style: TextStyle(fontSize: 10, color: color, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text('$sign${qty.toStringAsFixed(0)} sacs', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: color)),
                  Text(
                    [if (time.isNotEmpty) time, m.movementDate].join(' · '),
                    style: TextStyle(fontSize: 10, color: Colors.grey.shade500),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ---------- RECHERCHE REÇU ----------
  void _showReceiptDialog() {
    final ctrl = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Rechercher un reçu'),
        content: TextField(
          controller: ctrl,
          decoration: const InputDecoration(labelText: 'Référence (ex: STK-XXXX)'),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Annuler')),
          ElevatedButton(
            onPressed: () {
              if (ctrl.text.isNotEmpty) {
                Navigator.pop(ctx);
                Navigator.push(context, MaterialPageRoute(builder: (_) => ReceiptScreen(reference: ctrl.text.trim())));
              }
            },
            child: const Text('Voir'),
          ),
        ],
      ),
    );
  }
}
