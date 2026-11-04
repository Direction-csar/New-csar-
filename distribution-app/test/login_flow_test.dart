import 'dart:convert';

import 'package:csar_distribution/main.dart';
import 'package:csar_distribution/screens/home_screen.dart';
import 'package:csar_distribution/screens/login_screen.dart';
import 'package:csar_distribution/services/auth_service.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Réponses calquées sur le backend réel : les colonnes décimales sortent en
/// chaînes ("3000.00") et les compteurs en entiers.
Map<String, dynamic> _user() => {
      'id': 8,
      'name': 'Agent Distributeur Demo',
      'email': 'distributeur@csar.sn',
      'role': 'distributeur',
      'is_active': 1,
    };

Map<String, dynamic> _planning({Object? planned = '3000.00', Object? executed = '150.00'}) => {
      'id': 1,
      'event_id': 1,
      'name': 'Pikine - Site A',
      'planned_quota_kg': planned,
      'executed_kg': executed,
      'expected_beneficiaries': 60,
      'status': 'active',
      'location': 'Pikine, Marché Syndicat',
      'beneficiaries_count': 14,
      'tickets_count': 8,
      'validated_count': 11,
      'collected_count': 3,
      'event': {'id': 1, 'name': 'Distribution Tabaski 2026 (DEMO)', 'initial_stock_kg': '10000.00'},
    };

http.Response _json(Object body, [int status = 200]) => http.Response.bytes(
      utf8.encode(jsonEncode(body)),
      status,
      headers: {'content-type': 'application/json; charset=utf-8'},
    );

void _mockPlatformChannels() {
  final messenger = TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger;
  messenger.setMockMethodCallHandler(
    const MethodChannel('dev.fluttercommunity.plus/connectivity'),
    (call) async => ['wifi'],
  );
  messenger.setMockStreamHandler(
    const EventChannel('dev.fluttercommunity.plus/connectivity_status'),
    MockStreamHandler.inline(onListen: (_, __) {}),
  );
}

typedef Handler = http.Response Function(http.Request request);

/// Monte l'application complète, tape les identifiants et appuie sur « SE CONNECTER ».
Future<void> _loginThrough(WidgetTester tester, Handler handler) async {
  SharedPreferences.setMockInitialValues({});
  _mockPlatformChannels();
  tester.view.physicalSize = const Size(1080, 2400);
  tester.view.devicePixelRatio = 3;
  addTearDown(tester.view.reset);

  await http.runWithClient(() async {
    await tester.pumpWidget(
      ChangeNotifierProvider(create: (_) => AuthService(), child: const CsarDistributionApp()),
    );
    await tester.pumpAndSettle();
    expect(find.byType(LoginScreen), findsOneWidget);

    await tester.enterText(find.byType(TextFormField).at(0), 'distributeur@csar.sn');
    await tester.enterText(find.byType(TextFormField).at(1), 'Distrib2026!');
    await tester.tap(find.text('SE CONNECTER'));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 300));
    await tester.pump(const Duration(milliseconds: 300));
  }, () => MockClient((req) async => handler(req)));
}

Handler _backend({
  Object? planned = '3000.00',
  Object? executed = '150.00',
  List<Map<String, dynamic>>? plannings,
  http.Response? plannigsResponse,
}) {
  return (req) {
    if (req.url.path.endsWith('/api/login')) {
      return _json({'success': true, 'user': _user(), 'token': '1|fake-token'});
    }
    if (req.url.path.endsWith('/my-plannings')) {
      return plannigsResponse ??
          _json({
            'success': true,
            'data': plannings ?? [_planning(planned: planned, executed: executed)],
          });
    }
    return _json({'success': true, 'data': [], 'server_time': '2026-11-03T10:00:00Z'});
  };
}

void main() {
  testWidgets('connexion réussie → tableau de bord affiché (quotas décimaux en chaînes)', (tester) async {
    await _loginThrough(tester, _backend());

    final ex = tester.takeException(); if (ex != null) { debugPrint("EXC: $ex"); } expect(ex, isNull);
    expect(find.byType(HomeScreen), findsOneWidget);
    expect(find.text('Tableau de bord'), findsOneWidget);
    expect(find.text('Bonjour, Agent Distributeur Demo'), findsOneWidget);
    expect(find.text('Pikine - Site A'), findsWidgets);
    expect(find.text('3000'), findsOneWidget, reason: 'quota planifié 3000.00 kg');
    expect(find.text('150'), findsOneWidget, reason: 'quota exécuté 150.00 kg');
  });

  testWidgets('quotas numériques, nuls ou absents ne cassent pas l\'écran', (tester) async {
    for (final planned in <Object?>[3000, 3000.5, null, 'abc', '']) {
      await _loginThrough(tester, _backend(planned: planned, executed: planned));
      expect(tester.takeException(), isNull, reason: 'quota=$planned');
      expect(find.text('Tableau de bord'), findsOneWidget, reason: 'quota=$planned');
      await tester.pumpWidget(const SizedBox());
    }
  });

  testWidgets('compteurs reçus en chaînes ou décimaux ne cassent pas l\'écran', (tester) async {
    final p = _planning()
      ..['beneficiaries_count'] = '14'
      ..['validated_count'] = 11.0
      ..['tickets_count'] = null
      ..['collected_count'] = '3';
    await _loginThrough(tester, _backend(plannings: [p]));
    expect(tester.takeException(), isNull);
    expect(find.text('Tableau de bord'), findsOneWidget);
  });

  testWidgets('planning sans événement ni lieu : écran affiché', (tester) async {
    final p = _planning()
      ..['event'] = null
      ..['location'] = null;
    await _loginThrough(tester, _backend(plannings: [p]));
    expect(tester.takeException(), isNull);
    expect(find.text('Sans evenement'), findsWidgets);
  });

  testWidgets('aucun planning assigné : tableau de bord vide, pas d\'écran blanc', (tester) async {
    await _loginThrough(tester, _backend(plannings: []));
    expect(tester.takeException(), isNull);
    expect(find.text('Aucun planning assigne'), findsOneWidget);
  });

  testWidgets('identifiants incorrects : message explicite, reste sur la connexion', (tester) async {
    await _loginThrough(tester, (req) => _json({'success': false, 'message': 'Identifiants invalides'}, 401));
    expect(tester.takeException(), isNull);
    expect(find.byType(LoginScreen), findsOneWidget);
    expect(find.text('Identifiants invalides'), findsOneWidget);
    expect(find.byType(HomeScreen), findsNothing);
  });

  testWidgets('API indisponible : message réseau explicite', (tester) async {
    await _loginThrough(tester, (req) => throw http.ClientException('Connection refused'));
    expect(tester.takeException(), isNull);
    expect(find.byType(LoginScreen), findsOneWidget);
    expect(find.textContaining('Erreur réseau'), findsOneWidget);
  });

  testWidgets('réponse de connexion invalide (HTML) : message explicite', (tester) async {
    await _loginThrough(tester, (req) => http.Response('<html>502 Bad Gateway</html>', 502));
    expect(tester.takeException(), isNull);
    expect(find.byType(LoginScreen), findsOneWidget);
    expect(find.textContaining('HTTP 502'), findsOneWidget);
  });

  testWidgets('connexion « réussie » sans jeton : message explicite, pas d\'écran blanc', (tester) async {
    await _loginThrough(tester, (req) => _json({'success': true, 'user': _user()}));
    expect(tester.takeException(), isNull);
    expect(find.byType(LoginScreen), findsOneWidget);
    expect(find.textContaining('jeton manquant'), findsOneWidget);
  });

  testWidgets('connexion « réussie » sans utilisateur : tableau de bord affiché', (tester) async {
    await _loginThrough(tester, (req) {
      if (req.url.path.endsWith('/api/login')) return _json({'success': true, 'token': '1|x'});
      return _backend()(req);
    });
    expect(tester.takeException(), isNull);
    expect(find.text('Bonjour, Agent'), findsOneWidget);
  });

  testWidgets('jeton expiré (401) après connexion : retour à la connexion avec message', (tester) async {
    await _loginThrough(
      tester,
      _backend(plannigsResponse: _json({'message': 'Unauthenticated.'}, 401)),
    );
    await tester.pump(const Duration(milliseconds: 500));
    expect(tester.takeException(), isNull);
    expect(find.byType(LoginScreen), findsOneWidget);
    expect(find.textContaining('Session expirée'), findsOneWidget);
  });

  testWidgets('serveur en erreur 500 sur les plannings : message + bouton Réessayer', (tester) async {
    await _loginThrough(tester, _backend(plannigsResponse: http.Response('<html>boom</html>', 500)));
    expect(tester.takeException(), isNull);
    expect(find.byType(HomeScreen), findsOneWidget);
    expect(find.text('Réessayer'), findsOneWidget);
  });

  testWidgets('format de plannings inattendu : message, pas d\'écran blanc', (tester) async {
    await _loginThrough(tester, _backend(plannigsResponse: _json({'success': true, 'data': 'oops'})));
    expect(tester.takeException(), isNull);
    expect(find.textContaining('Format de réponse inattendu'), findsOneWidget);
  });
}
