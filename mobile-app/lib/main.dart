import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'services/auth_service.dart';
import 'services/distribution_auth_service.dart';
import 'screens/home_screen.dart';
import 'screens/mode_select_screen.dart';
import 'screens/distribution/distribution_home_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthService()),
        ChangeNotifierProvider(create: (_) => DistributionAuthService()),
      ],
      child: const CsarApp(),
    ),
  );
}

class CsarApp extends StatelessWidget {
  const CsarApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'CSAR Mobile',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF1B5E20),
          primary: const Color(0xFF1B5E20),
        ),
        useMaterial3: true,
        appBarTheme: const AppBarTheme(
          backgroundColor: Color(0xFF1B5E20),
          foregroundColor: Colors.white,
          elevation: 0,
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF1B5E20),
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
    context.read<DistributionAuthService>().loadToken();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthService>();
    final distAuth = context.watch<DistributionAuthService>();

    if (auth.isLoading || distAuth.isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    if (auth.isAuthenticated) return const HomeScreen();
    if (distAuth.isAuthenticated) return const DistributionHomeScreen();
    return const ModeSelectScreen();
  }
}
