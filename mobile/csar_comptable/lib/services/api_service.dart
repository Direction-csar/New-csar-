import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../models/movement.dart';
import '../models/warehouse.dart';
import '../models/equipment.dart';

/// Service API « CSAR Comptable des matières ».
/// Mêmes conventions que l'app Magasinier : Sanctum Bearer,
/// jeton en stockage sécurisé, déconnexion automatique sur 401.
class ApiService {
  static const String baseUrl = 'https://www.csar.sn/api/comptable/v1';
  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: false),
    iOptions: IOSOptions(accessibility: KeychainAccessibility.first_unlock),
  );
  static const Duration _timeout = Duration(seconds: 20);

  /// Appelé quand le serveur répond 401 (session expirée ou révoquée).
  static void Function()? onUnauthorized;

  static Future<http.Response> _get(Uri uri, {Map<String, String>? headers}) async {
    final response = await http.get(uri, headers: headers).timeout(_timeout);
    await _checkUnauthorized(response, headers);
    return response;
  }

  static Future<http.Response> _post(Uri uri, {Map<String, String>? headers, Object? body}) async {
    final response = await http.post(uri, headers: headers, body: body).timeout(_timeout);
    await _checkUnauthorized(response, headers);
    return response;
  }

  static Future<void> _checkUnauthorized(http.Response response, Map<String, String>? headers) async {
    if (response.statusCode == 401 && (headers?.containsKey('Authorization') ?? false)) {
      await clearToken();
      onUnauthorized?.call();
    }
  }

  static Future<String?> getToken() async {
    try {
      return await _storage.read(key: 'auth_token').timeout(const Duration(seconds: 5));
    } catch (_) {
      try {
        await _storage.deleteAll();
      } catch (_) {}
      return null;
    }
  }

  static Future<void> setToken(String token) async {
    await _storage.write(key: 'auth_token', value: token);
  }

  static Future<void> clearToken() async {
    try {
      await _storage.delete(key: 'auth_token');
    } catch (_) {}
  }

  static Future<bool> hasConnection() async {
    try {
      final addresses = await InternetAddress.lookup('csar.sn')
          .timeout(const Duration(seconds: 5));
      return addresses.isNotEmpty && addresses.first.rawAddress.isNotEmpty;
    } catch (_) {
      return false;
    }
  }

  static Map<String, String> _headers(String? token) {
    final h = {'Content-Type': 'application/json', 'Accept': 'application/json'};
    if (token != null) h['Authorization'] = 'Bearer $token';
    return h;
  }

  // AUTH
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await _post(
      Uri.parse('$baseUrl/login'),
      headers: _headers(null),
      body: jsonEncode({'email': email, 'password': password}),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      await setToken(data['token']);
      return {'success': true, 'user': data['user']};
    }
    return {'success': false, 'message': jsonDecode(response.body)['message'] ?? 'Erreur de connexion'};
  }

  // TABLEAU DE BORD
  static Future<DashboardData> getDashboard() async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/dashboard'),
      headers: _headers(token),
    );
    if (response.statusCode == 200) {
      return DashboardData.fromJson(jsonDecode(response.body));
    }
    throw Exception('Erreur chargement du tableau de bord (${response.statusCode})');
  }

  // MOUVEMENTS
  static Future<List<Movement>> getMovements({
    String? status,
    int? warehouseId,
    String? type,
    String? query,
    String? from,
    String? to,
  }) async {
    final token = await getToken();
    final params = <String, String>{};
    if (status != null) params['status'] = status;
    if (warehouseId != null) params['warehouse_id'] = '$warehouseId';
    if (type != null) params['type'] = type;
    if (query != null && query.isNotEmpty) params['q'] = query;
    if (from != null) params['from'] = from;
    if (to != null) params['to'] = to;
    final uri = Uri.parse('$baseUrl/movements')
        .replace(queryParameters: params);
    final response = await _get(uri, headers: _headers(token));

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final list = (data['data'] as List? ?? []);
      return list.map((e) => Movement.fromJson(e)).toList();
    }
    throw Exception('Erreur chargement des mouvements (${response.statusCode})');
  }

  static Future<MovementDetail> getMovement(int id) async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/movements/$id'),
      headers: _headers(token),
    );
    if (response.statusCode == 200) {
      return MovementDetail.fromJson(jsonDecode(response.body));
    }
    throw Exception('Erreur chargement du mouvement (${response.statusCode})');
  }

  static Future<Map<String, dynamic>> validateMovement(int id) async {
    final token = await getToken();
    final response = await _post(
      Uri.parse('$baseUrl/movements/$id/validate'),
      headers: _headers(token),
    );
    final data = jsonDecode(response.body);
    return {
      'success': response.statusCode == 200,
      'message': data['message'] ?? 'Erreur',
      'movement': data['movement'],
    };
  }

  static Future<Map<String, dynamic>> rejectMovement(int id, String reason) async {
    final token = await getToken();
    final response = await _post(
      Uri.parse('$baseUrl/movements/$id/reject'),
      headers: _headers(token),
      body: jsonEncode({'rejection_reason': reason}),
    );
    final data = jsonDecode(response.body);
    if (response.statusCode == 422 && data['errors'] != null) {
      final errors = data['errors'] as Map<String, dynamic>;
      return {'success': false, 'message': (errors.values.first as List).first};
    }
    return {
      'success': response.statusCode == 200,
      'message': data['message'] ?? 'Erreur',
      'movement': data['movement'],
    };
  }

  // CARBURANT — soldes par produit (unité 'L')
  static Future<List<FuelBalance>> getFuelSummary() async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/fuel/summary'),
      headers: _headers(token),
    );
    if (response.statusCode == 200) {
      return (jsonDecode(response.body) as List)
          .map((e) => FuelBalance.fromJson(e))
          .toList();
    }
    throw Exception('Erreur soldes carburant (${response.statusCode})');
  }

  // MATÉRIEL — affectations aux agents
  static Future<List<EquipmentAssignment>> getAssignments({
    String? status,
    String? query,
  }) async {
    final token = await getToken();
    final params = <String, String>{};
    if (status != null) params['status'] = status;
    if (query != null && query.isNotEmpty) params['q'] = query;
    final uri = Uri.parse('$baseUrl/equipment-assignments')
        .replace(queryParameters: params);
    final response = await _get(uri, headers: _headers(token));

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return ((data['data'] as List?) ?? [])
          .map((e) => EquipmentAssignment.fromJson(e))
          .toList();
    }
    throw Exception('Erreur affectations (${response.statusCode})');
  }

  static Future<Map<String, dynamic>> storeAssignment({
    required String produit,
    required String agentName,
    int? warehouseId,
    String? unite,
    double? quantity,
    String? numeroBon,
  }) async {
    final token = await getToken();
    final body = <String, dynamic>{
      'produit': produit,
      'agent_name': agentName,
    };
    if (warehouseId != null) body['warehouse_id'] = warehouseId;
    if (unite != null) body['unite'] = unite;
    if (quantity != null) body['quantity'] = quantity;
    if (numeroBon != null && numeroBon.isNotEmpty) body['numero_bon'] = numeroBon;
    final response = await _post(
      Uri.parse('$baseUrl/equipment-assignments'),
      headers: _headers(token),
      body: jsonEncode(body),
    );
    final data = jsonDecode(response.body);
    if (response.statusCode == 422 && data['errors'] != null) {
      final errors = data['errors'] as Map<String, dynamic>;
      return {'success': false, 'message': (errors.values.first as List).first};
    }
    return {
      'success': response.statusCode == 201,
      'message': data['message'] ?? 'Erreur',
    };
  }

  static Future<Map<String, dynamic>> closeAssignment(
      int id, String outcome, String? note) async {
    final token = await getToken();
    final response = await _post(
      Uri.parse('$baseUrl/equipment-assignments/$id/close'),
      headers: _headers(token),
      body: jsonEncode({
        'outcome': outcome,
        if (note != null && note.isNotEmpty) 'return_note': note,
      }),
    );
    final data = jsonDecode(response.body);
    if (response.statusCode == 422 && data['errors'] != null) {
      final errors = data['errors'] as Map<String, dynamic>;
      return {'success': false, 'message': (errors.values.first as List).first};
    }
    return {
      'success': response.statusCode == 200,
      'message': data['message'] ?? 'Erreur',
    };
  }

  // MAGASINS (filtres)
  static Future<List<Warehouse>> getWarehouses() async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/warehouses'),
      headers: _headers(token),
    );
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data as List).map((e) => Warehouse.fromJson(e)).toList();
    }
    throw Exception('Erreur chargement des magasins (${response.statusCode})');
  }
}
