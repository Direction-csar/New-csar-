import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';
import '../services/diagnostics.dart';
import 'qr_ticket_screen.dart';

class TicketsHistoryScreen extends StatefulWidget {
  final int? planningId;
  final String? planningName;

  const TicketsHistoryScreen({super.key, this.planningId, this.planningName});

  @override
  State<TicketsHistoryScreen> createState() => _TicketsHistoryScreenState();
}

class _TicketsHistoryScreenState extends State<TicketsHistoryScreen> with SingleTickerProviderStateMixin {
  late TabController _tabs;
  List<dynamic> _all = [];
  Map<String, dynamic> _stats = {};
  bool _loading = true;
  String? _error;
  int _requestId = 0;
  String _search = '';
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 2, vsync: this);
    _load();
  }

  @override
  void dispose() {
    _tabs.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    if (!mounted) return;
    final requestId = ++_requestId;
    setState(() {
      _loading = true;
      _error = null;
    });
    final token = context.read<AuthService>().token;
    try {
      if (token == null) {
        _error = 'Session expirée. Reconnectez-vous.';
        return;
      }
      final res = await ApiService.getTicketsHistory(token, planningId: widget.planningId, search: _search);
      if (!mounted || requestId != _requestId ||
          context.read<AuthService>().token != token) {
        return;
      }
      if (res['success'] == true) {
        final data = res['data'];
        if (data is! List || data.any((t) =>
            t is! Map<String, dynamic> ||
            ['beneficiary', 'planning', 'scanner'].any((key) =>
              t[key] != null && t[key] is! Map<String, dynamic>)) ||
            (res['stats'] != null && res['stats'] is! Map<String, dynamic>)) {
          throw const FormatException('history-shape');
        }
        setState(() {
          _all = data;
          _stats = Map<String, dynamic>.from(res['stats'] ?? {});
        });
      } else if (ApiService.isAuthError(res)) {
        await _sessionExpired();
      } else {
        _error = res['message'] is String ? res['message'] : 'Impossible de charger l’historique.';
      }
    } catch (error, stack) {
      Diagnostics.error('load-history', error, stack);
      if (mounted && requestId == _requestId) {
        _error = error is TimeoutException
            ? 'Le serveur ne répond pas. Réessayez dans un instant.'
            : error is FormatException
                ? 'Format de réponse inattendu du serveur'
                : 'Impossible de joindre le serveur. Vérifiez la connexion.';
      }
    } finally {
      if (mounted && requestId == _requestId) {
        setState(() => _loading = false);
      }
    }
  }

  Future<void> _sessionExpired() async {
    if (!mounted) return;
    final navigator = Navigator.of(context);
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Session expirée ou compte désactivé : reconnectez-vous.'),
        backgroundColor: Colors.red,
      ),
    );
    await context.read<AuthService>().clearSession();
    if (navigator.mounted) navigator.popUntil((route) => route.isFirst);
  }

  List<dynamic> get _issued => _all.where((t) => t['status'] != 'collected').toList();
  List<dynamic> get _collected => _all.where((t) => t['status'] == 'collected').toList();

  String _fmt(dynamic iso) {
    if (iso == null) return '';
    final d = DateTime.tryParse(iso.toString())?.toLocal();
    if (d == null) return '';
    return '${d.day.toString().padLeft(2, '0')}/${d.month.toString().padLeft(2, '0')}/${d.year} ${d.hour.toString().padLeft(2, '0')}:${d.minute.toString().padLeft(2, '0')}';
  }

  Widget _list(List<dynamic> items, bool collected) {
    if (_loading) return const Center(child: CircularProgressIndicator(color: Color(0xFFD84315)));
    if (_error != null) {
      return ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const SizedBox(height: 60),
          Text(_error!, textAlign: TextAlign.center),
          const SizedBox(height: 16),
          OutlinedButton.icon(
            onPressed: _load,
            icon: const Icon(Icons.refresh),
            label: const Text('Réessayer'),
          ),
        ],
      );
    }
    if (items.isEmpty) {
      return ListView(children: [
        const SizedBox(height: 100),
        Center(child: Text(collected ? 'Aucun don recupere' : 'Aucun ticket en attente', style: const TextStyle(color: Colors.grey))),
      ]);
    }
    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: items.length,
      itemBuilder: (_, i) {
        final t = items[i];
        final b = t['beneficiary'] ?? {};
        final p = t['planning'] ?? {};
        final color = collected ? Colors.green : Colors.orange;
        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: color.withOpacity(0.35)),
          ),
          child: Row(
            children: [
              Icon(collected ? Icons.check_circle : Icons.confirmation_number_outlined, color: color, size: 28),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text((b['full_name'] ?? 'N/A').toString(), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                    Text('${t['ticket_code']}  |  ${b['quantity_kg'] ?? 0} kg  |  Tel: ${b['phone'] ?? '-'}',
                        style: const TextStyle(fontSize: 11, color: Colors.grey)),
                    if (widget.planningId == null)
                      Text((p['name'] ?? '').toString(), style: const TextStyle(fontSize: 11, color: Colors.grey)),
                    Text('Ticket emis le ${_fmt(t['issued_at'])}', style: const TextStyle(fontSize: 11, color: Colors.orange)),
                    if (collected)
                      Text('Don recupere le ${_fmt(t['collected_at'])}${t['scanner'] != null ? ' (scanne par ${t['scanner']['name']})' : ''}',
                          style: const TextStyle(fontSize: 11, color: Colors.green, fontWeight: FontWeight.w600))
                    else
                      const Text('Don NON recupere', style: TextStyle(fontSize: 11, color: Colors.red, fontWeight: FontWeight.w600)),
                  ],
                ),
              ),
              if (!collected)
                IconButton(
                  icon: const Icon(Icons.qr_code_2, color: Colors.teal),
                  tooltip: 'Voir QR',
                  onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => QrTicketScreen(ticket: t))),
                ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFBE9E7),
      appBar: AppBar(
        backgroundColor: const Color(0xFFD84315),
        title: Text(widget.planningName != null ? 'Tickets - ${widget.planningName}' : 'Historique des tickets', style: const TextStyle(fontSize: 16)),
        bottom: TabBar(
          controller: _tabs,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: [
            Tab(text: 'Tickets emis (${_search.isEmpty && widget.planningId == null ? (_stats['issued'] ?? _issued.length) : _issued.length})'),
            Tab(text: 'Dons recuperes (${_search.isEmpty && widget.planningId == null ? (_stats['collected'] ?? _collected.length) : _collected.length})'),
          ],
        ),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 10, 12, 4),
            child: TextField(
              controller: _searchCtrl,
              onSubmitted: (v) {
                _search = v;
                _load();
              },
              decoration: InputDecoration(
                hintText: 'Nom, telephone, CNI ou code ticket (Entree)',
                hintStyle: const TextStyle(fontSize: 12),
                prefixIcon: const Icon(Icons.search, size: 20),
                suffixIcon: _search.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18),
                        onPressed: () {
                          _searchCtrl.clear();
                          _search = '';
                          _load();
                        })
                    : null,
                isDense: true,
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
              ),
            ),
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: _load,
              color: const Color(0xFFD84315),
              child: TabBarView(
                controller: _tabs,
                children: [_list(_issued, false), _list(_collected, true)],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
