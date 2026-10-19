import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../models/warehouse.dart';
import '../models/product.dart';

class ApiService {
  static const String baseUrl = 'https://www.csar.sn/api/warehouse/v1';
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

  // MAGASINS
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
    throw Exception('Erreur chargement magasins');
  }

  // FICHE DE STOCK
  static Future<Map<String, dynamic>> getStockSheet(int warehouseId, {String? dateFrom, String? dateTo}) async {
    final token = await getToken();
    final uri = Uri.parse('$baseUrl/warehouses/$warehouseId/sheet').replace(
      queryParameters: {
        if (dateFrom != null) 'date_from': dateFrom,
        if (dateTo != null) 'date_to': dateTo,
      },
    );
    final response = await _get(uri, headers: _headers(token));

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Erreur chargement fiche de stock');
  }

  // MOUVEMENT
  static Future<Map<String, dynamic>> createMovement(Map<String, dynamic> data) async {
    final token = await getToken();
    final response = await _post(
      Uri.parse('$baseUrl/movements'),
      headers: _headers(token),
      body: jsonEncode(data),
    );

    if (response.statusCode == 201) {
      return {'success': true, 'data': jsonDecode(response.body)};
    }
    return {'success': false, 'message': jsonDecode(response.body)['message'] ?? 'Erreur'};
  }

  // SYNCHRONISATION BATCH
  static Future<Map<String, dynamic>> syncMovements(List<Map<String, dynamic>> movements) async {
    final token = await getToken();
    final response = await _post(
      Uri.parse('$baseUrl/sync'),
      headers: _headers(token),
      body: jsonEncode({'movements': movements}),
    );

    if (response.statusCode == 200) {
      return {'success': true, 'data': jsonDecode(response.body)};
    }
    return {'success': false, 'message': jsonDecode(response.body)['message'] ?? 'Erreur sync'};
  }

  // PRODUITS
  static Future<List<Product>> getProducts() async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/products'),
      headers: _headers(token),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['products'] as List).map((e) => Product.fromJson(e)).toList();
    }
    throw Exception('Erreur chargement produits');
  }

  // TRANSFERT INTER-MAGASIN
  static Future<Map<String, dynamic>> createTransfer(Map<String, dynamic> data) async {
    final token = await getToken();
    final response = await _post(
      Uri.parse('$baseUrl/transfers'),
      headers: _headers(token),
      body: jsonEncode(data),
    );

    if (response.statusCode == 201) {
      return {'success': true, 'data': jsonDecode(response.body)};
    }
    return {'success': false, 'message': jsonDecode(response.body)['message'] ?? 'Erreur transfert'};
  }

  // INVENTAIRE PHYSIQUE
  static Future<Map<String, dynamic>> createInventory(Map<String, dynamic> data) async {
    final token = await getToken();
    final response = await _post(
      Uri.parse('$baseUrl/inventory'),
      headers: _headers(token),
      body: jsonEncode(data),
    );

    if (response.statusCode == 201) {
      return {'success': true, 'data': jsonDecode(response.body)};
    }
    return {'success': false, 'message': jsonDecode(response.body)['message'] ?? 'Erreur inventaire'};
  }

  // ÉTAT DU STOCK
  static Future<Map<String, dynamic>> getStockStatus(int warehouseId) async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/stock-status?warehouse_id=$warehouseId'),
      headers: _headers(token),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Erreur état du stock');
  }

  // ALERTES SEUIL
  static Future<Map<String, dynamic>> getAlerts({int threshold = 100}) async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/alerts?threshold=$threshold'),
      headers: _headers(token),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Erreur alertes');
  }

  // REÇU
  static Future<Map<String, dynamic>> getReceipt(String reference) async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/receipt/$reference'),
      headers: _headers(token),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Reçu non trouvé');
  }

  // STATS
  static Future<Map<String, dynamic>> getStats() async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/stats'),
      headers: _headers(token),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Erreur stats');
  }
}
