import 'dart:ui' show PlatformDispatcher;
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'services/auth_service.dart';
import 'services/diagnostics.dart';
import 'screens/login_screen.dart';
import 'screens/home_screen.dart';

/// Filet de sécurité : sans lui, une exception pendant la construction d'un
/// écran s'affiche en blanc (ou en gris) en version release, sans aucune
/// explication. L'agent voit ici un message clair, et le détail reste dans les logs.
Widget _errorScreen(FlutterErrorDetails details) {
  Diagnostics.error('widget', details.exception, details.stack ?? StackTrace.current);
  return Directionality(
    textDirection: TextDirection.ltr,
    child: Container(
      color: const Color(0xFFFBE9E7),
      padding: const EdgeInsets.all(24),
      alignment: Alignment.center,
      child: const Text(
        'Une erreur d\'affichage est survenue.\nRetournez en arrière, ou déconnectez-vous puis reconnectez-vous.',
        textAlign: TextAlign.center,
        style: TextStyle(color: Color(0xFFD84315), fontSize: 15, fontWeight: FontWeight.w600, decoration: TextDecoration.none),
      ),
    ),
  );
}

void main() {
  ErrorWidget.builder = _errorScreen;
  FlutterError.onError = (details) {
    Diagnostics.error('flutter', details.exception, details.stack ?? StackTrace.current);
  };
  PlatformDispatcher.instance.onError = (error, stack) {
    Diagnostics.error('async', error, stack);
    return true;
  };
  runApp(
    ChangeNotifierProvider(
      create: (_) => AuthService(),
      child: const CsarDistributionApp(),
    ),
  );
}

class CsarDistributionApp extends StatelessWidget {
  const CsarDistributionApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'CSAR Distribution',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFFD84315),
          primary: const Color(0xFFD84315),
        ),
        useMaterial3: true,
        appBarTheme: const AppBarTheme(
          backgroundColor: Color(0xFFD84315),
          foregroundColor: Colors.white,
          elevation: 0,
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFFD84315),
            foregroundColor: Colors.white,
            minimumSize: const Size(double.infinity, 50),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        ),
      ),
      home: const AuthWrapper(),
    );
  }
}

class AuthWrapper extends StatefulWidget {
  const AuthWrapper({super.key});

  @override
  State<AuthWrapper> createState() => _AuthWrapperState();
}

class _AuthWrapperState extends State<AuthWrapper> {
  @override
  void initState() {
    super.initState();
    context.read<AuthService>().loadToken();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthService>();
    if (auth.isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator(color: Color(0xFFD84315))),
      );
    }
    return auth.isAuthenticated ? const HomeScreen() : const LoginScreen();
  }
}
