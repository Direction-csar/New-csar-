import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'screens/home_screen.dart';
import 'screens/login_screen.dart';
import 'services/auth_service.dart';
import 'theme/csar_theme.dart';

void main() {
  runApp(
    ChangeNotifierProvider(
      create: (_) => AuthService()..loadSession(),
      child: const CsarInspectorApp(),
    ),
  );
}

class CsarInspectorApp extends StatelessWidget {
  const CsarInspectorApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'CSAR Inspecteur',
      debugShowCheckedModeBanner: false,
      theme: CsarTheme.light(),
      home: const _Gate(),
    );
  }
}

class _Gate extends StatelessWidget {
  const _Gate();

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthService>();
    if (auth.isLoading) {
      return Scaffold(
        body: Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              colors: [CsarColors.navy, CsarColors.navyLight],
            ),
          ),
          child: Center(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text('CSAR',
                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 22, letterSpacing: 3)),
                SizedBox(height: 24),
                CircularProgressIndicator(color: CsarColors.orange, strokeWidth: 3),
              ],
            ),
          ),
        ),
      );
    }
    return auth.isAuthenticated ? const HomeScreen() : const LoginScreen();
  }
}
