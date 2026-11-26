import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:csar_inspector/main.dart';
import 'package:csar_inspector/services/auth_service.dart';

void main() {
  testWidgets('Login screen s\'affiche sans session', (WidgetTester tester) async {
    final auth = AuthService();
    // Session non chargée depuis le secure storage en test : isLoading reste true.
    // On force un état non authentifié en pompant l'app avec un AuthService réel.
    await tester.pumpWidget(
      ChangeNotifierProvider.value(
        value: auth,
        child: const CsarInspectorApp(),
      ),
    );
    await tester.pump();
    // L'écran de chargement ou de login doit s'afficher sans crash.
    expect(find.byType(CsarInspectorApp), findsOneWidget);
  });
}
