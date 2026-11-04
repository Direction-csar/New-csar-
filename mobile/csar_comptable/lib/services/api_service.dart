import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config/app_config.dart';
import '../models/movement.dart';
import '../models/fuel.dart';
import '../models/warehouse.dart';
import '../models/equipment.dart';

/// Service API « CSAR Comptable des matières ».
/// Mêmes conventions que l'app Magasinier : Sanctum Bearer,
/// jeton en stockage sécurisé, déconnexion automatique sur 401.
class ApiService {
  static const String baseUrl = AppConfig.comptableBaseUrl;
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
      final host = Uri.parse(baseUrl).host;
      final addresses = await InternetAddress.lookup(host)
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

  /// Révoque le jeton côté serveur puis le supprime localement.
  /// Best-effort : la session est fermée même si le serveur est injoignable.
  static Future<void> logout() async {
    try {
      final token = await getToken();
      if (token != null) {
        await _post(
          Uri.parse('$baseUrl/logout'),
          headers: _headers(token),
        );
      }
    } catch (_) {
      // Réseau indisponible : le jeton local est quand même effacé.
    } finally {
      await clearToken();
    }
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

  /* ==================== CARBURANT ==================== */

  /// Tableau de bord carburant : soldes, valorisation, dossiers en attente.
  static Future<Map<String, dynamic>> getFuelDashboard() async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/fuel/dashboard'),
      headers: _headers(token),
    );
    if (response.statusCode == 200) {
      return jsonDecode(response.body) as Map<String, dynamic>;
    }
    throw Exception('Erreur tableau de bord carburant (${response.statusCode})');
  }

  /// Liste paginée d'un flux carburant (entries|exits|reimbursements).
  static Future<List<Map<String, dynamic>>> _fuelList(
    String path, {
    String? status,
    int? warehouseId,
    String? query,
  }) async {
    final token = await getToken();
    final params = <String, String>{};
    if (status != null && status.isNotEmpty) params['status'] = status;
    if (warehouseId != null) params['warehouse_id'] = '$warehouseId';
    if (query != null && query.isNotEmpty) params['q'] = query;
    final uri = Uri.parse('$baseUrl/fuel/$path')
        .replace(queryParameters: params.isEmpty ? null : params);
    final response = await _get(uri, headers: _headers(token));

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return ((data['data'] as List?) ?? [])
          .cast<Map<String, dynamic>>();
    }
    throw Exception('Erreur chargement $path (${response.statusCode})');
  }

  static Future<List<FuelEntry>> getFuelEntries(
          {String? status, int? warehouseId, String? query}) async =>
      (await _fuelList('entries',
              status: status, warehouseId: warehouseId, query: query))
          .map(FuelEntry.fromJson)
          .toList();

  static Future<List<FuelExit>> getFuelExits(
          {String? status, int? warehouseId, String? query}) async =>
      (await _fuelList('exits',
              status: status, warehouseId: warehouseId, query: query))
          .map(FuelExit.fromJson)
          .toList();

  static Future<List<FuelReimbursement>> getFuelReimbursements(
          {String? status, String? query}) async =>
      (await _fuelList('reimbursements', status: status, query: query))
          .map(FuelReimbursement.fromJson)
          .toList();

  /// Détail d'un dossier (renvoie la clé métier + données annexes).
  static Future<Map<String, dynamic>> getFuelDetail(String path, int id) async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/fuel/$path/$id'),
      headers: _headers(token),
    );
    if (response.statusCode == 200) {
      return jsonDecode(response.body) as Map<String, dynamic>;
    }
    throw Exception('Erreur chargement du dossier (${response.statusCode})');
  }

  /// POST générique vers un flux (create) ou une transition (action).
  /// Décodage standard : 200/201 = succès, 422 = erreur métier ou validation.
  static Future<Map<String, dynamic>> _fuelPost(
    String path,
    int? id,
    String action,
    Map<String, dynamic> body,
  ) async {
    final token = await getToken();
    final uri = id == null
        ? Uri.parse('$baseUrl/fuel/$path')
        : Uri.parse('$baseUrl/fuel/$path/$id/$action');
    final response = await _post(
      uri,
      headers: _headers(token),
      body: jsonEncode(body),
    );
    final data = jsonDecode(response.body);
    if (response.statusCode == 422 && data['errors'] != null) {
      final errors = data['errors'] as Map<String, dynamic>;
      return {'success': false, 'message': (errors.values.first as List).first};
    }
    return {
      'success': response.statusCode == 200 || response.statusCode == 201,
      'message': data['message'] ?? 'Erreur (${response.statusCode})',
      if (data['entry'] != null) 'entry': data['entry'],
      if (data['exit'] != null) 'exit': data['exit'],
      if (data['reimbursement'] != null) 'reimbursement': data['reimbursement'],
    };
  }

  static Future<Map<String, dynamic>> createFuelEntry(
          Map<String, dynamic> data) =>
      _fuelPost('entries', null, '', data);

  static Future<Map<String, dynamic>> fuelEntryAction(
          int id, String action, Map<String, dynamic> body) =>
      _fuelPost('entries', id, action, body);

  static Future<Map<String, dynamic>> createFuelExit(
          Map<String, dynamic> data) =>
      _fuelPost('exits', null, '', data);

  static Future<Map<String, dynamic>> fuelExitAction(
          int id, String action, Map<String, dynamic> body) =>
      _fuelPost('exits', id, action, body);

  static Future<Map<String, dynamic>> createFuelReimbursement(
          Map<String, dynamic> data) =>
      _fuelPost('reimbursements', null, '', data);

  static Future<Map<String, dynamic>> fuelReimbAction(
          int id, String action, Map<String, dynamic> body) =>
      _fuelPost('reimbursements', id, action, body);

  /// Upload d'un justificatif (multipart). type : entry|exit|reimb.
  static Future<Map<String, dynamic>> uploadFuelAttachment({
    required String type,
    required int subjectId,
    required String filePath,
    String? kind,
  }) async {
    final token = await getToken();
    final uri = Uri.parse('$baseUrl/fuel/$type/$subjectId/attachments');
    final request = http.MultipartRequest('POST', uri)
      ..headers['Authorization'] = 'Bearer $token'
      ..headers['Accept'] = 'application/json'
      ..files.add(await http.MultipartFile.fromPath('file', filePath));
    if (kind != null) request.fields['kind'] = kind;

    final streamed = await request.send().timeout(_timeout);
    final response = await http.Response.fromStream(streamed);
    final data = jsonDecode(response.body);
    return {
      'success': response.statusCode == 201,
      'message': data['message'] ?? 'Erreur (${response.statusCode})',
    };
  }

  /// Suppression d'un justificatif.
  static Future<Map<String, dynamic>> deleteFuelAttachment(int id) async {
    final token = await getToken();
    final response = await http
        .delete(
          Uri.parse('$baseUrl/fuel/attachments/$id'),
          headers: _headers(token),
        )
        .timeout(_timeout);
    final data = jsonDecode(response.body);
    return {
      'success': response.statusCode == 200,
      'message': data['message'] ?? 'Erreur (${response.statusCode})',
    };
  }

  /// Téléchargement d'un justificatif — octets (écriture locale par l'appelant).
  static Future<List<int>?> downloadFuelAttachment(int id) async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/fuel/attachments/$id/download'),
      headers: _headers(token),
    );
    return response.statusCode == 200 ? response.bodyBytes : null;
  }

  /// Téléchargement du PDF d'un dossier (type : entry|exit|reimb).
  static Future<List<int>?> downloadFuelPdf(String type, int id) async {
    final token = await getToken();
    final response = await _get(
      Uri.parse('$baseUrl/fuel/$type/$id/pdf'),
      headers: _headers(token),
    );
    return response.statusCode == 200 ? response.bodyBytes : null;
  }
}
