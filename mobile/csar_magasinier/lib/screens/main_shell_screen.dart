import 'package:flutter/material.dart';
import 'dashboard_screen.dart';
import 'stock_status_screen.dart';
import 'operations_screen.dart';
import 'missions_screen.dart';
import 'movement_history_screen.dart';
import 'profile_screen.dart';

/// Coquille principale — navigation inférieure CSAR.
class MainShellScreen extends StatefulWidget {
  const MainShellScreen({super.key});

  @override
  State<MainShellScreen> createState() => _MainShellScreenState();
}

class _MainShellScreenState extends State<MainShellScreen> {
  int _currentIndex = 0;

  static const _tabs = <_TabDef>[
    _TabDef(DashboardScreen(), Icons.space_dashboard_rounded, Icons.space_dashboard_outlined, 'Accueil'),
    _TabDef(StockStatusScreen(), Icons.inventory_2_rounded, Icons.inventory_2_outlined, 'Stocks'),
    _TabDef(OperationsScreen(), Icons.assignment_turned_in_rounded, Icons.assignment_turned_in_outlined, 'Opérations'),
    _TabDef(MissionsScreen(), Icons.local_shipping_outlined, Icons.local_shipping_outlined, 'Missions'),
    _TabDef(MovementHistoryScreen(), Icons.history_rounded, Icons.history_outlined, 'Historique'),
    _TabDef(ProfileScreen(), Icons.person_rounded, Icons.person_outline_rounded, 'Profil'),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: [for (final t in _tabs) t.screen],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (i) => setState(() => _currentIndex = i),
        height: 66,
        destinations: [
          for (final t in _tabs)
            NavigationDestination(
              icon: Icon(t.icon),
              selectedIcon: Icon(t.activeIcon),
              label: t.label,
            ),
        ],
      ),
    );
  }
}

class _TabDef {
  final Widget screen;
  final IconData icon;
  final IconData activeIcon;
  final String label;
  const _TabDef(this.screen, this.activeIcon, this.icon, this.label);
}
