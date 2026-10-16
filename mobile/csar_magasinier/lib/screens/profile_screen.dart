import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:intl/intl.dart';
import '../providers/auth_provider.dart';
import '../services/database_service.dart';
import 'sync_screen.dart';
import 'receipt_history_screen.dart';

/// Écran Profil du magasinier connecté.
/// Affiche les informations de l'utilisateur, l'état de synchronisation
/// et permet la déconnexion.
class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  int _pendingSync = 0;
  String? _lastSync;

  @override
  void initState() {
    super.initState();
    _loadInfo();
  }

  Future<void> _loadInfo() async {
    try {
      final pending = await DatabaseService.getPendingSync();
      final prefs = await SharedPreferences.getInstance();
      final raw = prefs.getString('last_sync_at');
      String? label;
      if (raw != null) {
        final dt = DateTime.tryParse(raw);
        if (dt != null) {
          label = DateFormat('dd/MM/yyyy à HH:mm').format(dt);
        }
      }
      setState(() {
        _pendingSync = pending.length;
        _lastSync = label;
      });
    } catch (_) {}
  }

  String _initials(String name) {
    final parts = name.trim().split(RegExp(r'\s+')).where((p) => p.isNotEmpty).toList();
    if (parts.isEmpty) return '?';
    if (parts.length == 1) return parts.first.substring(0, 1).toUpperCase();
    return (parts.first.substring(0, 1) + parts.last.substring(0, 1)).toUpperCase();
  }

  String _roleLabel(String? role) {
    switch (role) {
      case 'super_admin':
        return 'Super administrateur';
      case 'admin':
        return 'Administrateur';
      case 'magasinier':
        return 'Magasinier';
      default:
        return role ?? 'Utilisateur';
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    final name = (user?['name'] ?? 'Magasinier').toString();
    final email = (user?['email'] ?? '').toString();
    final role = user?['role']?.toString();

    return Scaffold(
      backgroundColor: const Color(0xFFF6F8F5),
      appBar: AppBar(title: const Text('Profil')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Carte utilisateur
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 3)),
              ],
            ),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 32,
                  backgroundColor: const Color(0xFF1B5E20),
                  child: Text(
                    _initials(name),
                    style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(name, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                      if (email.isNotEmpty)
                        Text(email, style: TextStyle(fontSize: 13, color: Colors.grey.shade600)),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xFF1B5E20).withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          _roleLabel(role),
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF1B5E20)),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          const Text('ACTIVITÉ', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.grey, letterSpacing: 0.5)),
          const SizedBox(height: 10),

          // Synchronisation
          _menuCard(
            icon: Icons.sync,
            color: Colors.cyan.shade700,
            title: 'Synchronisation',
            subtitle: _lastSync != null
                ? 'Dernière synchro : $_lastSync · $_pendingSync en attente'
                : '$_pendingSync mouvement(s) en attente',
            badge: _pendingSync > 0 ? '$_pendingSync' : null,
            onTap: () async {
              await Navigator.push(context, MaterialPageRoute(builder: (_) => const SyncScreen()));
              _loadInfo();
            },
          ),
          const SizedBox(height: 10),

          // Reçus
          _menuCard(
            icon: Icons.receipt_long,
            color: Colors.indigo,
            title: 'Mes reçus',
            subtitle: 'Historique des reçus de mouvements',
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ReceiptHistoryScreen())),
          ),
          const SizedBox(height: 24),

          // Déconnexion
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () async {
                final confirm = await showDialog<bool>(
                  context: context,
                  builder: (ctx) => AlertDialog(
                    title: const Text('Déconnexion'),
                    content: const Text('Voulez-vous vraiment vous déconnecter ?'),
                    actions: [
                      TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Annuler')),
                      ElevatedButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Déconnexion')),
                    ],
                  ),
                );
                if (confirm == true && context.mounted) {
                  context.read<AuthProvider>().logout();
                }
              },
              icon: const Icon(Icons.logout, color: Colors.red),
              label: const Text('SE DÉCONNECTER', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Colors.red),
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Center(
            child: Text(
              'CSAR Magasinier v1.0.0',
              style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
            ),
          ),
        ],
      ),
    );
  }

  Widget _menuCard({
    required IconData icon,
    required Color color,
    required String title,
    required String subtitle,
    String? badge,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Row(
            children: [
              CircleAvatar(
                backgroundColor: color.withValues(alpha: 0.12),
                child: Icon(icon, color: color, size: 20),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                    Text(subtitle, style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                  ],
                ),
              ),
              if (badge != null)
                Container(
                  margin: const EdgeInsets.only(right: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(color: Colors.red, borderRadius: BorderRadius.circular(12)),
                  child: Text(badge, style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                ),
              Icon(Icons.chevron_right, color: Colors.grey.shade400),
            ],
          ),
        ),
      ),
    );
  }
}
