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
              quantity: '${_fmtNum(((a['current_stock'] as num?)?.toDouble() ?? 0) * 50)} kg',
              severity: a['severity'] == 'critical' ? 'critical' : 'warning',
              icon: Icons.warehouse_outlined,
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

  Widget _seeAll(VoidCallback onTap) {
    return TextButton(
      onPressed: onTap,
      style: TextButton.styleFrom(foregroundColor: _green),
      child: const Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text('Voir tout', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
          SizedBox(width: 2),
          Icon(Icons.chevron_right, size: 20),
        ],
      ),
    );
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
                          const SizedBox(height: 14),
                          _buildStatsRow(),
                          const SizedBox(height: 16),
                          _buildAlertsSection(),
                          const SizedBox(height: 16),
                          _buildRecentMovements(),
                          const SizedBox(height: 16),
                          _buildQuickActions(),
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Barre verte : logo + titre + notifications + profil
          Container(
            color: _green,
            child: SafeArea(
              bottom: false,
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 10, 8, 12),
                child: Row(
                  children: [
                    const Icon(Icons.warehouse, color: Colors.white, size: 42),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'CSAR MAGASINIER',
                            style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w800, letterSpacing: 0.3),
                          ),
                          Text(
                            'Gestion des stocks',
                            style: TextStyle(color: Colors.white, fontSize: 14),
                          ),
                        ],
                      ),
                    ),
                    Stack(
                      clipBehavior: Clip.none,
                      children: [
                        IconButton(
                          onPressed: _showAlerts,
                          icon: const Icon(Icons.notifications_none, color: Colors.white, size: 28),
                        ),
                        if (_alerts.isNotEmpty)
                          Positioned(
                            right: 6,
                            top: 4,
                            child: Container(
                              padding: const EdgeInsets.all(3),
                              decoration: const BoxDecoration(color: Color(0xFFE53935), shape: BoxShape.circle),
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
                    const SizedBox(width: 4),
                    PopupMenuButton<String>(
                      offset: const Offset(0, 52),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      onSelected: _onMenu,
                      itemBuilder: (_) => [
                        PopupMenuItem(
                          enabled: false,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(user?['name']?.toString() ?? 'Magasinier',
                                  style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1B2E1F))),
                              Text(user?['email']?.toString() ?? '',
                                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                            ],
                          ),
                        ),
                        const PopupMenuDivider(),
                        const PopupMenuItem(value: 'sync', child: ListTile(leading: Icon(Icons.sync), title: Text('Synchroniser'), contentPadding: EdgeInsets.zero)),
                        const PopupMenuItem(value: 'receipts', child: ListTile(leading: Icon(Icons.receipt_long), title: Text('Mes reçus'), contentPadding: EdgeInsets.zero)),
                        const PopupMenuItem(value: 'logout', child: ListTile(leading: Icon(Icons.logout, color: Colors.red), title: Text('Se déconnecter', style: TextStyle(color: Colors.red)), contentPadding: EdgeInsets.zero)),
                      ],
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(2),
                            decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                            child: CircleAvatar(
                              radius: 20,
                              backgroundColor: const Color(0xFFE8F5E9),
                              child: Text(
                                _initials(user?['name']?.toString()),
                                style: const TextStyle(color: _green, fontWeight: FontWeight.bold, fontSize: 14),
                              ),
                            ),
                          ),
                          const Icon(Icons.keyboard_arrow_down, color: Colors.white),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          // Salutation + sélecteur de magasin (fond clair)
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 0),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Padding(
                  padding: EdgeInsets.only(top: 2),
                  child: Icon(Icons.wb_sunny_outlined, color: Color(0xFF1B2E1F), size: 30),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text.rich(
                        TextSpan(
                          style: const TextStyle(color: Color(0xFF1B2E1F), fontSize: 22),
                          children: [
                            const TextSpan(text: 'Bonjour, '),
                            TextSpan(
                              text: _firstName(user?['name']?.toString()),
                              style: const TextStyle(fontWeight: FontWeight.w800, color: _green),
                            ),
                          ],
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Vue d\'ensemble du magasin',
                        style: TextStyle(color: Colors.grey.shade600, fontSize: 14),
                      ),
                    ],
                  ),
                ),
                if (_warehouses.isNotEmpty)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE8F1E9),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<Warehouse>(
                        value: _selectedWarehouse,
                        icon: const Icon(Icons.keyboard_arrow_down, color: _green, size: 20),
                        style: const TextStyle(color: Color(0xFF1B2E1F), fontSize: 13, fontWeight: FontWeight.w600),
                        isDense: true,
                        selectedItemBuilder: (_) => _warehouses
                            .map((w) => Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.apartment, color: _green, size: 18),
                                    const SizedBox(width: 6),
                                    ConstrainedBox(
                                      constraints: const BoxConstraints(maxWidth: 110),
                                      child: Text(w.name, overflow: TextOverflow.ellipsis),
                                    ),
                                  ],
                                ))
                            .toList(),
                        items: _warehouses
                            .map((w) => DropdownMenuItem(value: w, child: Text(w.name, overflow: TextOverflow.ellipsis)))
                            .toList(),
                        onChanged: (w) {
                          if (w != null) _selectWarehouse(w);
                        },
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ],
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
              TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Déconnexion', style: TextStyle(color: Colors.red))),
            ],
          ),
        );
        if (ok == true && mounted) {
          await context.read<AuthProvider>().logout();
        }
        break;
    }
  }

  /// Nom du produit extrait du libellé (« Riz [25kg] … » ou « Produit: Riz | … »).
  String? _productFromLabel(String label) {
    final a = RegExp(r'^(.+?)\s*\[\d+\s*kg\]', caseSensitive: false).firstMatch(label);
    if (a != null) return a.group(1)!.trim();
    final b = RegExp(r'Produit:\s*(.+?)\s*\|').firstMatch(label);
    if (b != null) return b.group(1)!.trim();
    return null;
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
    const orange = Color(0xFFF59E0B);
    const blue = Color(0xFF3B82F6);
    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _statCard('Stock disponible', _formatTonnage(stockSacs), null, Icons.inventory_2_outlined,
              _greenLight, const Color(0xFFF4FAF4), const Color(0xFF1B2E1F),
              () => _open(() => StockStatusScreen(warehouse: _selectedWarehouse))),
          const SizedBox(width: 8),
          _statCard('Entrées aujourd\'hui', '$_entriesToday', 'mouvements', Icons.arrow_downward,
              _greenLight, const Color(0xFFF4FAF4), const Color(0xFF1B2E1F),
              () => _open(() => MovementFormScreen(warehouse: _selectedWarehouse))),
          const SizedBox(width: 8),
          _statCard('Sorties aujourd\'hui', '$_exitsToday', 'mouvements', Icons.arrow_upward,
              orange, const Color(0xFFFFF8EC), orange,
              () => _open(() => MovementFormScreen(warehouse: _selectedWarehouse))),
          const SizedBox(width: 8),
          _statCard('À valider', '$_pendingSync', 'à synchroniser', Icons.assignment_outlined,
              blue, const Color(0xFFF0F6FF), blue,
              () => _open(() => const SyncScreen())),
        ],
      ),
    );
  }

  Widget _statCard(String label, String value, String? sub, IconData icon, Color color, Color bg,
      Color valueColor, VoidCallback onTap) {
    return Expanded(
      child: Material(
        color: bg,
        borderRadius: BorderRadius.circular(12),
        child: InkWell(
          borderRadius: BorderRadius.circular(12),
          onTap: onTap,
          child: Container(
            padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 4),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: color.withValues(alpha: 0.18)),
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                CircleAvatar(
                  radius: 17,
                  backgroundColor: color,
                  child: Icon(icon, color: Colors.white, size: 19),
                ),
                const SizedBox(height: 8),
                Text(
                  label,
                  style: const TextStyle(fontSize: 10.5, color: Color(0xFF1B2E1F), fontWeight: FontWeight.w500),
                  textAlign: TextAlign.center,
                  maxLines: 2,
                ),
                const SizedBox(height: 4),
                FittedBox(
                  fit: BoxFit.scaleDown,
                  child: Text(value, style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: valueColor)),
                ),
                if (sub != null)
                  Text(sub, style: TextStyle(fontSize: 9.5, color: Colors.grey.shade600), textAlign: TextAlign.center),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ---------- ALERTES STOCK ----------
  Widget _buildAlertsSection() {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFFFFFAFA),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFF8DADA)),
      ),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 10, 4, 4),
            child: Row(
              children: [
                const Icon(Icons.warning_rounded, color: Color(0xFFDC2626), size: 30),
                const SizedBox(width: 10),
                const Text(
                  'ALERTES STOCK',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: Color(0xFFDC2626)),
                ),
                const Spacer(),
                _seeAll(_showAlerts),
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
    Color textColor;
    Color chipBg;
    String chipLabel;
    Color tileColor;
    switch (a.severity) {
      case 'critical':
        textColor = const Color(0xFFDC2626);
        chipBg = const Color(0xFFFBCFCF);
        chipLabel = 'Critique';
        tileColor = const Color(0xFFFDECEC);
        break;
      case 'anomaly':
        textColor = const Color(0xFF7C3AED);
        chipBg = const Color(0xFFE4D7FB);
        chipLabel = 'Anomalie';
        tileColor = const Color(0xFFF5F0FE);
        break;
      case 'info':
        textColor = const Color(0xFF2563EB);
        chipBg = const Color(0xFFD3E4FD);
        chipLabel = 'À surveiller';
        tileColor = const Color(0xFFEEF4FE);
        break;
      default:
        textColor = const Color(0xFFEA7A0C);
        chipBg = const Color(0xFFFDDFA8);
        chipLabel = 'Attention';
        tileColor = const Color(0xFFFEF4E6);
    }

    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 4, 12, 4),
      child: Material(
        color: tileColor,
        borderRadius: BorderRadius.circular(10),
        child: InkWell(
          borderRadius: BorderRadius.circular(10),
          onTap: _showAlerts,
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
            child: Row(
              children: [
                Container(
                  width: 52,
                  height: 52,
                  clipBehavior: Clip.antiAlias,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: a.imageUrl != null && a.imageUrl!.isNotEmpty
                      ? Image.network(
                          a.imageUrl!,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Icon(a.icon, color: textColor, size: 28),
                        )
                      : Icon(a.icon, color: textColor, size: 28),
                ),
                const SizedBox(width: 12),
                Expanded(
                  flex: 5,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(a.title,
                          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15, color: Color(0xFF1B2E1F)),
                          overflow: TextOverflow.ellipsis),
                      const SizedBox(height: 2),
                      Text(a.subtitle,
                          style: TextStyle(fontSize: 13, color: textColor, fontWeight: FontWeight.w500),
                          overflow: TextOverflow.ellipsis),
                    ],
                  ),
                ),
                Container(width: 1, height: 40, color: Colors.black12, margin: const EdgeInsets.symmetric(horizontal: 10)),
                Expanded(
                  flex: 3,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(a.quantity,
                          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF1B2E1F)),
                          overflow: TextOverflow.ellipsis),
                      Text('disponibles', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(color: chipBg, borderRadius: BorderRadius.circular(14)),
                  child: Text(chipLabel, style: TextStyle(color: textColor, fontSize: 11, fontWeight: FontWeight.w700)),
                ),
                Icon(Icons.chevron_right, color: Colors.grey.shade600, size: 20),
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
        borderRadius: BorderRadius.circular(14),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 10, 4, 4),
            child: Row(
              children: [
                const Icon(Icons.schedule, color: _green, size: 30),
                const SizedBox(width: 10),
                const Text('Mouvements récents',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Color(0xFF1B2E1F))),
                const Spacer(),
                _seeAll(() => Navigator.push(context, MaterialPageRoute(builder: (_) => const MovementHistoryScreen()))),
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

    final sacs = isEntry ? m.entrySacs : (isAdjustment ? (m.inventoryDiff ?? 0).abs() : m.exitSacs);
    final kgRaw = isEntry ? m.entryKg : (isAdjustment ? 0.0 : m.exitKg);
    final kg = kgRaw > 0 ? kgRaw : sacs * 50;
    final sign = isEntry ? '+' : (isAdjustment ? (m.inventoryDiff != null && m.inventoryDiff! >= 0 ? '+' : '−') : '−');
    final qtyColor = isEntry ? _green : color;

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
        margin: const EdgeInsets.symmetric(horizontal: 14),
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          border: Border(top: BorderSide(color: Colors.grey.shade200)),
        ),
        child: Row(
          children: [
            CircleAvatar(radius: 22, backgroundColor: color, child: Icon(icon, color: Colors.white, size: 24)),
            const SizedBox(width: 14),
            Expanded(
              flex: 5,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(m.label,
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14.5, color: Color(0xFF1B2E1F)),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(color: color.withValues(alpha: 0.85), borderRadius: BorderRadius.circular(6)),
                    child: Text(typeLabel, style: const TextStyle(fontSize: 11, color: Colors.white, fontWeight: FontWeight.w600)),
                  ),
                ],
              ),
            ),
            Container(width: 1, height: 40, color: Colors.grey.shade300, margin: const EdgeInsets.symmetric(horizontal: 10)),
            Expanded(
              flex: 4,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('$sign${_fmtNum(kg)} kg',
                      style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: qtyColor),
                      overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 2),
                  Text(_productFromLabel(m.label) ?? '${_fmtNum(sacs)} sacs',
                      style: TextStyle(fontSize: 11.5, color: Colors.grey.shade600),
                      overflow: TextOverflow.ellipsis),
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.access_time, size: 15, color: Colors.grey.shade600),
                    const SizedBox(width: 4),
                    Text(time.isEmpty ? '--:--' : time,
                        style: const TextStyle(fontSize: 12.5, color: Color(0xFF1B2E1F))),
                  ],
                ),
                const SizedBox(height: 4),
                Text(_fmtDate(date), style: TextStyle(fontSize: 10.5, color: Colors.grey.shade600)),
              ],
            ),
          ],
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
