import 'package:flutter/material.dart';
import 'dashboard_screen.dart';
import 'stock_status_screen.dart';
import 'movement_history_screen.dart';
import 'missions_screen.dart';
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
    MissionsScreen(),
    MovementHistoryScreen(),
    ProfileScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(index: _currentIndex, children: _tabs),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.06), blurRadius: 10, offset: const Offset(0, -2))],
        ),
        child: SafeArea(
          top: false,
          child: SizedBox(
            height: 68,
            child: Row(
              children: [
                _navItem(0, Icons.home, Icons.home_outlined, 'Accueil'),
                _navItem(1, Icons.inventory_2, Icons.inventory_2_outlined, 'Stocks'),
                _navItem(2, Icons.assignment, Icons.assignment_outlined, 'Missions'),
                _navItem(3, Icons.swap_horiz, Icons.swap_horiz, 'Mouvements'),
                _navItem(4, Icons.person, Icons.person_outline, 'Profil'),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _navItem(int index, IconData activeIcon, IconData icon, String label) {
    const green = Color(0xFF1B5E20);
    final selected = _currentIndex == index;
    final color = selected ? green : Colors.grey.shade600;
    return Expanded(
      child: InkWell(
        onTap: () => setState(() => _currentIndex = index),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(selected ? activeIcon : icon, color: color, size: 28),
            const SizedBox(height: 4),
            Text(label,
                style: TextStyle(fontSize: 12.5, color: color, fontWeight: selected ? FontWeight.w700 : FontWeight.w500)),
            const SizedBox(height: 5),
            AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              height: 3,
              width: selected ? 56 : 0,
              decoration: BoxDecoration(color: green, borderRadius: BorderRadius.circular(2)),
            ),
          ],
        ),
      ),
    );
  }
}
