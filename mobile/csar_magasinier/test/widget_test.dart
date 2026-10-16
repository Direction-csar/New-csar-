// Tests de base de l'application CSAR Magasinier.
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:csar_warehouse_keeper/main.dart';

void main() {
  testWidgets('App démarre sans erreur (loader ou écran de connexion)',
      (WidgetTester tester) async {
    await tester.pumpWidget(const CsarWarehouseApp());
    await tester.pump(const Duration(seconds: 1));

    // Au premier lancement : soit le loader (vérif token en cours),
    // soit l'écran de connexion. Dans les deux cas l'app ne plante pas.
    final hasLoader =
        find.byType(CircularProgressIndicator).evaluate().isNotEmpty;
    final hasLogin =
        find.byType(TextFormField).evaluate().isNotEmpty;
    expect(hasLoader || hasLogin, isTrue);
  });
}
