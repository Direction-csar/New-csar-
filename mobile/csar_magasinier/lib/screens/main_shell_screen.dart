import 'package:flutter/material.dart';
import 'dashboard_screen.dart';
import 'stock_status_screen.dart';
import 'movement_history_screen.dart';
import 'profile_screen.dart';

/// Coquille principale de l'application avec navigation basse :
/// Accueil, Stocks, Mouvements, Profil.
class MainShellScreen extends StatefulWidget {
  const MainShellScreen({super.key});

  @override
  State<MainShellScreen> createState() => _MainShellScreenState();
}

class _MainShellScreenState extends State<MainShellScreen> {
  int _currentIndex = 0;

  final List<Widget> _tabs = const [
    DashboardScreen(),
    StockStatusScreen(),
    MovementHistoryScreen(),
    ProfileScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(index: _currentIndex, children: _tabs),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (i) => setState(() => _currentIndex = i),
        indicatorColor: const Color(0xFF1B5E20).withValues(alpha: 0.12),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home, color: Color(0xFF1B5E20)),
            label: 'Accueil',
          ),
          NavigationDestination(
            icon: Icon(Icons.inventory_2_outlined),
            selectedIcon: Icon(Icons.inventory_2, color: Color(0xFF1B5E20)),
            label: 'Stocks',
          ),
          NavigationDestination(
            icon: Icon(Icons.swap_horiz),
            selectedIcon: Icon(Icons.swap_horiz, color: Color(0xFF1B5E20)),
            label: 'Mouvements',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person, color: Color(0xFF1B5E20)),
            label: 'Profil',
          ),
        ],
      ),
    );
  }
}
