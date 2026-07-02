import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import '../models/warehouse.dart';
import '../models/stock_movement.dart';

class ApiService {
  static const String baseUrl = 'https://www.csar.sn/api/warehouse/v1';
  static const _storage = FlutterSecureStorage();

  static Future<String?> getToken() async {
    return await _storage.read(key: 'auth_token');
  }

  static Future<void> setToken(String token) async {
    await _storage.write(key: 'auth_token', value: token);
  }

  static Future<void> clearToken() async {
    await _storage.delete(key: 'auth_token');
  }

  static Future<bool> hasConnection() async {
    final result = await Connectivity().checkConnectivity();
    return result != ConnectivityResult.none;
  }

  static Map<String, String> _headers(String? token) {
    final h = {'Content-Type': 'application/json', 'Accept': 'application/json'};
    if (token != null) h['Authorization'] = 'Bearer $token';
    return h;
  }

  // AUTH
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
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
    final response = await http.get(
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
    final response = await http.get(uri, headers: _headers(token));

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Erreur chargement fiche de stock');
  }

  // MOUVEMENT
  static Future<Map<String, dynamic>> createMovement(Map<String, dynamic> data) async {
    final token = await getToken();
    final response = await http.post(
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
    final response = await http.post(
      Uri.parse('$baseUrl/sync'),
      headers: _headers(token),
      body: jsonEncode({'movements': movements}),
    );

    if (response.statusCode == 200) {
      return {'success': true, 'data': jsonDecode(response.body)};
    }
    return {'success': false, 'message': jsonDecode(response.body)['message'] ?? 'Erreur sync'};
  }

  // STATS
  static Future<Map<String, dynamic>> getStats() async {
    final token = await getToken();
    final response = await http.get(
      Uri.parse('$baseUrl/stats'),
      headers: _headers(token),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Erreur stats');
  }
}
