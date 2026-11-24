import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/app_config.dart';

/// Client HTTP de l'API stock/inspections CSAR.
/// Toutes les requêtes authentifiées envoient le jeton Sanctum.
class ApiService {
  static const Duration _timeout = Duration(seconds: 25);

  static Map<String, String> _headers(String? token) => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

  static Future<Map<String, dynamic>> _request(
    String method,
    String path, {
    String? token,
    Map<String, dynamic>? body,
    Map<String, String>? query,
  }) async {
    final uri = Uri.parse('${AppConfig.baseUrl}$path').replace(queryParameters: query);
    final headers = _headers(token);
    late http.Response res;
    switch (method) {
      case 'GET':
        res = await http.get(uri, headers: headers).timeout(_timeout);
        break;
      case 'POST':
        res = await http
            .post(uri, headers: headers, body: jsonEncode(body ?? {}))
            .timeout(_timeout);
        break;
      default:
        throw ArgumentError('Méthode non supportée : $method');
    }

    Map<String, dynamic> json;
    try {
      json = jsonDecode(utf8.decode(res.bodyBytes)) as Map<String, dynamic>;
    } catch (_) {
      json = {'message': 'Réponse serveur invalide (${res.statusCode}).'};
    }

    if (res.statusCode >= 400) {
      return {'success': false, 'status': res.statusCode, ...json};
    }
    return {'success': true, 'status': res.statusCode, ...json};
  }

  // ------------------------------------------------------------------
  // Authentification (compte inspecteur personnel)
  // ------------------------------------------------------------------
  static Future<Map<String, dynamic>> loginInspector(String email, String password) =>
      _request('POST', '/login/inspecteur', body: {'email': email, 'password': password});

  // ------------------------------------------------------------------
  // Opérations (instructions DG → DTL → inspecteur → magasinier)
  // ------------------------------------------------------------------
  static Future<Map<String, dynamic>> getOperations(String token, {String? status}) =>
      _request('GET', '/operations', token: token, query: {'status': ?status});

  static Future<Map<String, dynamic>> getOperation(String token, int id) =>
      _request('GET', '/operations/$id', token: token);

  static Future<Map<String, dynamic>> getMagasinierCandidates(String token, int warehouseId) =>
      _request('GET', '/operations/magasinier-candidates', token: token, query: {'warehouse_id': '$warehouseId'});

  static Future<Map<String, dynamic>> transmit(String token, int id, Map<String, dynamic> body) =>
      _request('POST', '/operations/$id/transmit', token: token, body: body);

  static Future<Map<String, dynamic>> verify(String token, int id, Map<String, dynamic> body) =>
      _request('POST', '/operations/$id/verify', token: token, body: body);

  static Future<Map<String, dynamic>> reportAnomaly(String token, int id, Map<String, dynamic> body) =>
      _request('POST', '/operations/$id/anomaly', token: token, body: body);

  static Future<Map<String, dynamic>> reject(String token, int id, String reason) =>
      _request('POST', '/operations/$id/reject', token: token, body: {'rejection_reason': reason});

  // ------------------------------------------------------------------
  // Mouvements directs en attente (entrées/sorties/transferts/inventaires
  // saisis via Web, APK magasinier ou synchronisation hors ligne)
  // ------------------------------------------------------------------
  static Future<Map<String, dynamic>> getPendingMovements(String token) =>
      _request('GET', '/pending-movements', token: token);

  static Future<Map<String, dynamic>> verifyMovement(String token, int id) =>
      _request('POST', '/pending-movements/$id/verify', token: token);

  static Future<Map<String, dynamic>> rejectMovement(String token, int id, String reason) =>
      _request('POST', '/pending-movements/$id/reject', token: token, body: {'rejection_reason': reason});

  static Future<Map<String, dynamic>> confirmReceipt(String token, int id) =>
      _request('POST', '/pending-movements/$id/confirm-receipt', token: token);

  // ------------------------------------------------------------------
  // Consultation stock (lecture seule)
  // ------------------------------------------------------------------
  static Future<Map<String, dynamic>> getWarehouses(String token) =>
      _request('GET', '/stock-ops/warehouses', token: token);

  static Future<Map<String, dynamic>> getStockStatus(String token, int warehouseId) =>
      _request('GET', '/stock-ops/status', token: token, query: {'warehouse_id': '$warehouseId'});
}
