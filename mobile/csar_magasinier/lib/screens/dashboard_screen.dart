import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';
import '../models/warehouse.dart';
import '../models/stock_movement.dart';
import '../theme/csar_theme.dart';
import '../widgets/csar_ui.dart';
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
  final String? imageUrl;

  const _AlertItem({
    required this.title,
    required this.subtitle,
    required this.quantity,
    required this.severity,
    required this.icon,
    this.imageUrl,
  });
}

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
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
      await _loadDataInner().timeout(const Duration(seconds: 45));
    } catch (_) {
      if (mounted) {
        setState(() {
          _loading = false;
          _error = 'Impossible de charger les données';
        });
      }
    }
  }

  Future<void> _loadDataInner() async {
    try {
      final pending = await DatabaseService.getPendingSync()
          .timeout(const Duration(seconds: 15), onTimeout: () => <StockMovement>[]);
      _pendingSync = pending.length;

      final hasNet = await ApiService.hasConnection();
      _offline = !hasNet;

      List<Warehouse> warehouses = [];
      if (hasNet) {
        warehouses = await ApiService.getWarehouses();
        try {
          await DatabaseService.saveWarehouses(warehouses.map((w) => w.toJson()).toList())
              .timeout(const Duration(seconds: 10));
        } catch (_) {}
      } else {
        final local = await DatabaseService.getWarehouses()
            .timeout(const Duration(seconds: 10), onTimeout: () => <Map<String, dynamic>>[]);
        warehouses = local.map((m) => Warehouse.fromJson(m)).toList();
      }

      int? savedId;
      try {
        final prefs = await SharedPreferences.getInstance()
            .timeout(const Duration(seconds: 5));
        savedId = prefs.getInt('selected_warehouse_id');
      } catch (_) {}
      Warehouse? selected = warehouses.isNotEmpty
          ? warehouses.firstWhere((w) => w.id == savedId, orElse: () => warehouses.first)
          : null;

      List<StockMovement> recent = [];
      List<_AlertItem> alerts = [];

      if (hasNet) {
        try {
          final res = await ApiService.getAlerts();
          for (final a in (res['alerts'] ?? [])) {
            alerts.add(_AlertItem(
              title: a['warehouse_name'] ?? 'Magasin',
              subtitle: 'Stock bas (${a['percentage']}%)',
              quantity: '${_fmtNum(((a['current_stock'] as num?)?.toDouble() ?? 0) * 50)} kg',
              severity: a['severity'] == 'critical' ? 'critical' : 'warning',
              icon: Icons.warehouse_outlined,
            ));
          }
        } catch (_) {}

        if (selected != null) {
          try {
            final sheet = await ApiService.getStockSheet(selected.id);
            recent = (sheet['movements']?['data'] as List? ?? [])
                .map((e) => StockMovement.fromJson(e))
                .toList();
          } catch (_) {}

          try {
            final status = await ApiService.getStockStatus(selected.id);
            for (final p in (status['products'] ?? [])) {
              final sacs = (p['total_sacs'] as num?)?.toDouble() ?? 0;
              if (sacs <= 0) {
                alerts.add(_AlertItem(
                  title: p['product_name'] ?? 'Produit',
                  subtitle: 'Stock critique',
                  quantity: '0 kg',
                  severity: 'critical',
                  icon: Icons.inventory_2_outlined,
                  imageUrl: p['image_url'],
                ));
              } else if (sacs <= kLowStockThresholdSacs) {
                alerts.add(_AlertItem(
                  title: p['product_name'] ?? 'Produit',
                  subtitle: sacs <= kLowStockThresholdSacs / 2 ? 'Stock critique' : 'Seuil bientôt atteint',
                  quantity: '${_fmtNum(((p['total_kg'] as num?)?.toDouble() ?? sacs * 50))} kg',
                  severity: sacs <= kLowStockThresholdSacs / 2 ? 'critical' : 'warning',
                  icon: Icons.inventory_2_outlined,
                  imageUrl: p['image_url'],
                ));
              }
            }
            for (final e in (status['expiring'] ?? [])) {
              final days = (e['days_left'] as num?)?.toInt() ?? 0;
              alerts.add(_AlertItem(
                title: e['product_name'] ?? 'Produit',
                subtitle: days < 0
                    ? 'Périmé depuis ${-days} jour${-days > 1 ? 's' : ''}'
                    : (days == 0 ? 'Périme aujourd\'hui' : 'Péremption dans $days jour${days > 1 ? 's' : ''}'),
                quantity: '${_fmtNum((e['quantity'] as num?)?.toDouble() ?? 0)} kg',
                severity: days <= 0 ? 'critical' : 'info',
                icon: Icons.event_busy,
                imageUrl: e['image_url'],
              ));
            }
          } catch (_) {}
        }

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
        if (selected != null) {
          recent = await DatabaseService.getMovementsByWarehouse(selected.id)
              .timeout(const Duration(seconds: 10), onTimeout: () => <StockMovement>[]);
        }
      }

      final today = DateTime.now().toIso8601String().substring(0, 10);
      _entriesToday = recent.where((m) {
        final d = m.movementDate.isNotEmpty ? m.movementDate : (m.createdAt ?? '');
        return d.startsWith(today) && (m.movementType == 'entry' || m.movementType == 'report');
      }).length;
      _exitsToday = recent.where((m) {
        final d = m.movementDate.isNotEmpty ? m.movementDate : (m.createdAt ?? '');
        return d.startsWith(today) && (m.movementType == 'exit' || m.movementType == 'transfer');
      }).length;

      const order = {'critical': 0, 'warning': 1, 'info': 2, 'anomaly': 3};
      alerts.sort((a, b) => (order[a.severity] ?? 9).compareTo(order[b.severity] ?? 9));

      setState(() {
        _warehouses = warehouses;
        _selectedWarehouse = selected;
        _recentMovements = recent.take(5).toList();
        _alerts = alerts;
        _loading = false;
      });
    } catch (e) {
      if (mounted) {
        setState(() {
          _loading = false;
          _error = 'Impossible de charger les données';
        });
      }
    }
  }

  Future<void> _selectWarehouse(Warehouse w) async {
    setState(() => _selectedWarehouse = w);
    try {
      final prefs = await SharedPreferences.getInstance()
          .timeout(const Duration(seconds: 5));
      await prefs.setInt('selected_warehouse_id', w.id);
    } catch (_) {}
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

  String _formatTonnage(double sacs) {
    final kg = sacs * 50;
    if (kg >= 1000) {
      return '${(kg / 1000).toStringAsFixed(1).replaceAll('.', ',')} t';
    }
    return '${kg.toStringAsFixed(0)} kg';
  }

  String _fmtNum(double v) {
    final s = v.round().abs().toString();
    final b = StringBuffer();
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) b.write(' ');
      b.write(s[i]);
    }
    return b.toString();
  }

  String _fmtDate(String iso) {
    final dt = DateTime.tryParse(iso);
    if (dt == null) return iso;
    const months = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    return '${dt.day} ${months[dt.month - 1]} ${dt.year}';
  }

  Future<void> _open(Widget Function() builder) async {
    await Navigator.push(context, MaterialPageRoute(builder: (_) => builder()));
    _loadData();
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;

    return Scaffold(
      backgroundColor: CsarColors.bg,
      body: _loading && _warehouses.isEmpty
          ? const Center(child: CircularProgressIndicator(color: CsarColors.blue))
          : RefreshIndicator(
              onRefresh: _loadData,
              color: CsarColors.blue,
              child: CustomScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                slivers: [
                  SliverToBoxAdapter(child: _buildHeader(user)),
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          OfflineBanner(offline: _offline, message: _error, onRetry: _error != null ? _loadData : null),
                          const SizedBox(height: 14),
                          _buildStatsGrid(),
                          const SizedBox(height: 16),
                          _buildAlertsSection(),
                          const SizedBox(height: 16),
                          _buildQuickActions(),
                          const SizedBox(height: 16),
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
    return CsarHeader(
      title: 'Bonjour, ${_firstName(user?['name']?.toString())}',
      subtitle: _selectedWarehouse != null
          ? '${_selectedWarehouse!.name} — ${_selectedWarehouse!.location ?? 'Magasin CSAR'}'
          : 'Gestion des stocks alimentaires',
      trailing: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Stack(
            clipBehavior: Clip.none,
            children: [
              IconButton(
                onPressed: _showAlerts,
                icon: const Icon(Icons.notifications_none_rounded, color: Colors.white, size: 26),
              ),
              if (_alerts.isNotEmpty)
                Positioned(
                  right: 4,
                  top: 4,
                  child: Container(
                    padding: const EdgeInsets.all(3),
                    decoration: const BoxDecoration(color: CsarColors.orange, shape: BoxShape.circle),
                    constraints: const BoxConstraints(minWidth: 17, minHeight: 17),
                    child: Text(
                      '${_alerts.length}',
                      style: const TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.bold),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
            ],
          ),
          PopupMenuButton<String>(
            offset: const Offset(0, 46),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            onSelected: _onMenu,
            itemBuilder: (_) => [
              PopupMenuItem(
                enabled: false,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(user?['name']?.toString() ?? 'Magasinier',
                        style: const TextStyle(fontWeight: FontWeight.bold, color: CsarColors.text)),
                    Text(user?['email']?.toString() ?? '',
                        style: const TextStyle(fontSize: 12, color: CsarColors.textMuted)),
                  ],
                ),
              ),
              const PopupMenuDivider(),
              const PopupMenuItem(value: 'sync', child: ListTile(leading: Icon(Icons.sync_rounded), title: Text('Synchroniser'), contentPadding: EdgeInsets.zero, dense: true)),
              const PopupMenuItem(value: 'receipts', child: ListTile(leading: Icon(Icons.receipt_long_rounded), title: Text('Mes reçus'), contentPadding: EdgeInsets.zero, dense: true)),
              const PopupMenuItem(value: 'logout', child: ListTile(leading: Icon(Icons.logout_rounded, color: CsarColors.red), title: Text('Se déconnecter', style: TextStyle(color: CsarColors.red)), contentPadding: EdgeInsets.zero, dense: true)),
            ],
            child: Container(
              padding: const EdgeInsets.all(2),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.15),
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white.withValues(alpha: 0.4)),
              ),
              child: CircleAvatar(
                radius: 18,
                backgroundColor: Colors.white,
                child: Text(
                  _initials(user?['name']?.toString()),
                  style: const TextStyle(color: CsarColors.navy, fontWeight: FontWeight.w800, fontSize: 13),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // Sélecteur de magasin sous l'en-tête
  Widget _buildWarehouseSelector() {
    if (_warehouses.isEmpty) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: CsarColors.border),
        ),
        child: DropdownButtonHideUnderline(
          child: DropdownButton<Warehouse>(
            value: _selectedWarehouse,
            isExpanded: true,
            icon: const Icon(Icons.keyboard_arrow_down_rounded, color: CsarColors.blue),
            style: const TextStyle(color: CsarColors.text, fontSize: 14, fontWeight: FontWeight.w600),
            items: _warehouses
                .map((w) => DropdownMenuItem(
                      value: w,
                      child: Row(
                        children: [
                          const Icon(Icons.storefront_rounded, color: CsarColors.blue, size: 18),
                          const SizedBox(width: 8),
                          Expanded(child: Text(w.name, overflow: TextOverflow.ellipsis)),
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
    );
  }

  Future<void> _onMenu(String value) async {
    switch (value) {
      case 'sync':
        _open(() => const SyncScreen());
        break;
      case 'receipts':
        _open(() => const ReceiptHistoryScreen());
        break;
      case 'logout':
        final ok = await showDialog<bool>(
          context: context,
          builder: (ctx) => AlertDialog(
            title: const Text('Se déconnecter'),
            content: const Text('Voulez-vous vraiment vous déconnecter ?'),
            actions: [
              TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
              TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Déconnexion', style: TextStyle(color: CsarColors.red))),
            ],
          ),
        );
        if (ok == true && mounted) {
          await context.read<AuthProvider>().logout();
        }
        break;
    }
  }

  String? _productFromLabel(String label) {
    final a = RegExp(r'^(.+?)\s*\[\d+\s*kg\]', caseSensitive: false).firstMatch(label);
    if (a != null) return a.group(1)!.trim();
    final b = RegExp(r'Produit:\s*(.+?)\s*\|').firstMatch(label);
    if (b != null) return b.group(1)!.trim();
    return null;
  }

  // ---------- GRILLE KPI ----------
  Widget _buildStatsGrid() {
    final stockSacs = _selectedWarehouse?.currentStock ?? 0;
    return Column(
      children: [
        _buildWarehouseSelector(),
        const SizedBox(height: 14),
        GridView.count(
          crossAxisCount: 2,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          mainAxisSpacing: 10,
          crossAxisSpacing: 10,
          childAspectRatio: 1.55,
          children: [
            KpiCard(
              icon: Icons.inventory_2_rounded,
              value: _formatTonnage(stockSacs),
              label: 'Stock officiel',
              subtitle: _selectedWarehouse?.name ?? 'Tous magasins',
              color: CsarColors.green,
              onTap: () => _open(() => StockStatusScreen(warehouse: _selectedWarehouse)),
            ),
            KpiCard(
              icon: Icons.south_west_rounded,
              value: '$_entriesToday',
              label: 'Entrées aujourd\'hui',
              subtitle: 'mouvements',
              color: CsarColors.green,
              onTap: () => _open(() => MovementFormScreen(warehouse: _selectedWarehouse)),
            ),
            KpiCard(
              icon: Icons.north_east_rounded,
              value: '$_exitsToday',
              label: 'Sorties aujourd\'hui',
              subtitle: 'mouvements',
              color: CsarColors.orange,
              onTap: () => _open(() => MovementFormScreen(warehouse: _selectedWarehouse)),
            ),
            KpiCard(
              icon: Icons.cloud_upload_outlined,
              value: '$_pendingSync',
              label: 'À synchroniser',
              subtitle: 'mouvements hors ligne',
              color: CsarColors.blue,
              onTap: () => _open(() => const SyncScreen()),
            ),
          ],
        ),
      ],
    );
  }

  // ---------- ALERTES ----------
  Widget _buildAlertsSection() {
    return Card(
      child: Column(
        children: [
          SectionHeader(
            title: 'Alertes stock',
            icon: Icons.warning_amber_rounded,
            trailing: TextButton(
              onPressed: _showAlerts,
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text('Tout voir', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: CsarColors.blue)),
                  Icon(Icons.chevron_right, size: 16, color: CsarColors.blue),
                ],
              ),
            ),
          ),
          if (_alerts.isEmpty)
            const Padding(
              padding: EdgeInsets.fromLTRB(16, 0, 16, 16),
              child: Row(
                children: [
                  Icon(Icons.check_circle_outline_rounded, color: CsarColors.green, size: 18),
                  SizedBox(width: 8),
                  Text('Aucun problème de stock détecté', style: TextStyle(color: CsarColors.textMuted, fontSize: 13)),
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
    Color color;
    String chipLabel;
    switch (a.severity) {
      case 'critical':
        color = CsarColors.red;
        chipLabel = 'Critique';
        break;
      case 'anomaly':
        color = const Color(0xFF7C3AED);
        chipLabel = 'Anomalie';
        break;
      case 'info':
        color = CsarColors.blue;
        chipLabel = 'À surveiller';
        break;
      default:
        color = CsarColors.amber;
        chipLabel = 'Attention';
    }

    return InkWell(
      onTap: _showAlerts,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              clipBehavior: Clip.antiAlias,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: a.imageUrl != null && a.imageUrl!.isNotEmpty
                  ? Image.network(a.imageUrl!, fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Icon(a.icon, color: color, size: 22))
                  : Icon(a.icon, color: color, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(a.title,
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13.5, color: CsarColors.text),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 2),
                  Text(a.subtitle,
                      style: TextStyle(fontSize: 12, color: color, fontWeight: FontWeight.w600),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(chipLabel, style: TextStyle(color: color, fontSize: 10.5, fontWeight: FontWeight.w700)),
            ),
          ],
        ),
      ),
    );
  }

  void _showAlerts() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
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
                child: Container(width: 40, height: 4, decoration: BoxDecoration(color: CsarColors.border, borderRadius: BorderRadius.circular(2))),
              ),
              const SizedBox(height: 14),
              Text('Alertes stock (${_alerts.length})',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: CsarColors.text)),
              const SizedBox(height: 12),
              Expanded(
                child: _alerts.isEmpty
                    ? const Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.check_circle_outline_rounded, size: 48, color: CsarColors.green),
                            SizedBox(height: 8),
                            Text('Aucun problème de stock détecté', style: TextStyle(color: CsarColors.textMuted)),
                          ],
                        ),
                      )
                    : ListView(controller: scroll, children: _alerts.map(_alertTile).toList()),
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
      (label: 'Entrée', icon: Icons.south_west_rounded, color: CsarColors.green, onTap: () => _open(() => MovementFormScreen(warehouse: _selectedWarehouse))),
      (label: 'Sortie', icon: Icons.north_east_rounded, color: CsarColors.orange, onTap: () => _open(() => MovementFormScreen(warehouse: _selectedWarehouse))),
      (label: 'Transfert', icon: Icons.swap_horiz_rounded, color: CsarColors.blue, onTap: () => _open(() => TransferFormScreen(warehouse: _selectedWarehouse))),
      (label: 'Inventaire', icon: Icons.fact_check_outlined, color: const Color(0xFF7C3AED), onTap: () => _open(() => InventoryFormScreen(warehouse: _selectedWarehouse))),
      (label: 'Stocks', icon: Icons.bar_chart_rounded, color: const Color(0xFF0D9488), onTap: () => _open(() => StockStatusScreen(warehouse: _selectedWarehouse))),
      (label: 'Sync', icon: Icons.sync_rounded, color: CsarColors.amber, onTap: () => _open(() => const SyncScreen())),
      (label: 'Reçus', icon: Icons.receipt_long_rounded, color: const Color(0xFF6366F1), onTap: () => _open(() => const ReceiptHistoryScreen())),
      (label: 'Reçu n°', icon: Icons.search_rounded, color: CsarColors.textMuted, onTap: _showReceiptDialog),
    ];

    return Card(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SectionHeader(title: 'Actions rapides', icon: Icons.bolt_rounded),
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
            child: GridView.count(
              crossAxisCount: 4,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 10,
              crossAxisSpacing: 10,
              childAspectRatio: 0.82,
              children: actions.map(_quickAction).toList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _quickAction(({String label, IconData icon, Color color, VoidCallback onTap}) a) {
    return Material(
      color: a.color.withValues(alpha: 0.08),
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        onTap: a.onTap,
        borderRadius: BorderRadius.circular(12),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(a.icon, color: a.color, size: 24),
            const SizedBox(height: 6),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 4),
              child: Text(a.label,
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: CsarColors.text),
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis),
            ),
          ],
        ),
      ),
    );
  }

  // ---------- MOUVEMENTS RÉCENTS ----------
  Widget _buildRecentMovements() {
    return Card(
      child: Column(
        children: [
          SectionHeader(
            title: 'Mouvements récents',
            icon: Icons.history_rounded,
            trailing: TextButton(
              onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MovementHistoryScreen())),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text('Voir tout', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: CsarColors.blue)),
                  Icon(Icons.chevron_right, size: 16, color: CsarColors.blue),
                ],
              ),
            ),
          ),
          if (_recentMovements.isEmpty)
            const Padding(
              padding: EdgeInsets.fromLTRB(16, 0, 16, 16),
              child: Row(
                children: [
                  Icon(Icons.inbox_outlined, color: CsarColors.textMuted, size: 18),
                  SizedBox(width: 8),
                  Text('Aucun mouvement récent', style: TextStyle(color: CsarColors.textMuted, fontSize: 13)),
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
      color = CsarColors.blue;
      icon = Icons.swap_horiz_rounded;
      typeLabel = 'Transfert';
    } else if (isAdjustment) {
      color = const Color(0xFF7C3AED);
      icon = Icons.fact_check_outlined;
      typeLabel = 'Ajustement';
    } else if (isEntry) {
      color = CsarColors.green;
      icon = Icons.south_west_rounded;
      typeLabel = 'Entrée';
    } else {
      color = CsarColors.orange;
      icon = Icons.north_east_rounded;
      typeLabel = 'Sortie';
    }

    final sacs = isEntry ? m.entrySacs : (isAdjustment ? (m.inventoryDiff ?? 0).abs() : m.exitSacs);
    final kgRaw = isEntry ? m.entryKg : (isAdjustment ? 0.0 : m.exitKg);
    final kg = kgRaw > 0 ? kgRaw : sacs * 50;
    final sign = isEntry ? '+' : (isAdjustment ? (m.inventoryDiff != null && m.inventoryDiff! >= 0 ? '+' : '−') : '−');
    final qtyColor = isEntry ? CsarColors.green : color;

    String time = '';
    String date = m.movementDate;
    if (m.createdAt != null) {
      final dt = DateTime.tryParse(m.createdAt!)?.toLocal();
      if (dt != null) {
        time = '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
      }
    }
    if (date.isEmpty && m.createdAt != null) date = m.createdAt!;

    return InkWell(
      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => ReceiptScreen(reference: m.reference))),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 12),
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          border: Border(top: BorderSide(color: CsarColors.border)),
        ),
        child: Row(
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(m.label,
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13.5, color: CsarColors.text),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(6)),
                    child: Text(typeLabel, style: TextStyle(fontSize: 10.5, color: color, fontWeight: FontWeight.w700)),
                  ),
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text('$sign${_fmtNum(kg)} kg',
                    style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: qtyColor)),
                const SizedBox(height: 2),
                Text(_productFromLabel(m.label) ?? '${_fmtNum(sacs)} sacs',
                    style: const TextStyle(fontSize: 11, color: CsarColors.textMuted),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis),
                const SizedBox(height: 2),
                Text('${time.isEmpty ? '--:--' : time} · ${_fmtDate(date)}',
                    style: const TextStyle(fontSize: 10.5, color: CsarColors.textMuted)),
              ],
            ),
          ],
        ),
      ),
    );
  }

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
