import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'screens/login_screen.dart';
import 'screens/main_shell_screen.dart';
import 'theme/csar_theme.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const CsarWarehouseApp());
}

class CsarWarehouseApp extends StatelessWidget {
  const CsarWarehouseApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
      ],
      child: MaterialApp(
        title: 'CSAR Magasinier',
        debugShowCheckedModeBanner: false,
        theme: CsarTheme.light(),
        home: const AuthWrapper(),
      ),
    );
  }
}

class AuthWrapper extends StatefulWidget {
  const AuthWrapper({super.key});

  @override
  State<AuthWrapper> createState() => _AuthWrapperState();
}

class _AuthWrapperState extends State<AuthWrapper> {
  static const String appVersion = '2.0.0+3';
  bool _slow = false;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _timer = Timer(const Duration(seconds: 12), () {
      if (mounted) setState(() => _slow = true);
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, auth, child) {
        if (auth.isLoading) {
          return Scaffold(
            body: Container(
              decoration: const BoxDecoration(
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
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
                      ),
                      child: const Text(
                        'CSAR',
                        style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18, letterSpacing: 2),
                      ),
                    ),
                    const SizedBox(height: 32),
                    const CircularProgressIndicator(color: CsarColors.orange, strokeWidth: 3),
                    if (_slow) ...[
                      const SizedBox(height: 24),
                      const Text('Le chargement est long...',
                          style: TextStyle(color: Colors.white70)),
                      const SizedBox(height: 8),
                      ElevatedButton(
                        onPressed: () => context.read<AuthProvider>().forceStopLoading(),
                        child: const Text('CONTINUER'),
                      ),
                    ],
                    const SizedBox(height: 32),
                    const Text('v$appVersion', style: TextStyle(color: Colors.white38, fontSize: 12)),
                  ],
                ),
              ),
            ),
          );
        }
        return auth.isAuthenticated ? const MainShellScreen() : const LoginScreen();
      },
    );
  }
}
