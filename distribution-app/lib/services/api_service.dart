import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  /// URL du serveur, configurable au build :
  /// flutter build apk --dart-define=CSAR_API_URL=http://10.0.2.2
  /// La valeur par défaut reste la production.
  static const String serverUrl = String.fromEnvironment(
    'CSAR_API_URL',
    defaultValue: 'https://www.csar.sn',
  );
  static const String apiBaseUrl = '$serverUrl/api';
  static const String distBaseUrl = '$serverUrl/mobile/v1/distribution';

  /// Décode la réponse JSON en remontant le message d'erreur du serveur
  /// sur les codes HTTP >= 400 (401/403/404/422/500), au lieu de laisser
  /// jsonDecode échouer silencieusement sur du HTML ou un corps vide.
  static Map<String, dynamic> _decode(http.Response response) {
    dynamic body;
    try {
      body = jsonDecode(response.body);
    } catch (_) {
      return {
        'success': false,
        'message': 'Erreur serveur (HTTP ${response.statusCode})',
      };
    }
    final map = body is Map<String, dynamic> ? body : <String, dynamic>{};
    if (response.statusCode >= 400) {
      map['success'] = false;
      map['message'] ??= 'Erreur serveur (HTTP ${response.statusCode})';
      map['http_status'] = response.statusCode;
    }
    return map;
  }

  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$apiBaseUrl/login'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> sync(String token) async {
    final response = await http.get(
      Uri.parse('$distBaseUrl/sync'),
      headers: _headers(token),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> getEvents(String token) async {
    final response = await http.get(
      Uri.parse('$distBaseUrl/events'),
      headers: _headers(token),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> getMyPlannings(String token) async {
    final response = await http.get(
      Uri.parse('$distBaseUrl/my-plannings'),
      headers: _headers(token),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> getPlanningBeneficiaries(String token, int planningId) async {
    final response = await http.get(
      Uri.parse('$distBaseUrl/plannings/$planningId/beneficiaries'),
      headers: _headers(token),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> checkDuplicate(String token, int planningId, String? phone, String? cni, String fullName) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/check-duplicate'),
      headers: _headers(token),
      body: jsonEncode({
        'planning_id': planningId,
        'phone': phone,
        'cni': cni,
        'full_name': fullName,
      }),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> validateBeneficiary(String token, int beneficiaryId) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/beneficiaries/$beneficiaryId/validate'),
      headers: _headers(token),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> generateTicket(String token, int beneficiaryId) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/beneficiaries/$beneficiaryId/generate-ticket'),
      headers: _headers(token),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> collectKit(String token, String ticketCode, {String? scanLocation}) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/tickets/$ticketCode/collect'),
      headers: _headers(token),
      // 'location' est la clé lue par le backend (scan_location accepté aussi).
      body: jsonEncode({'location': scanLocation}),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> storeBeneficiaire(String token, Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/beneficiaries'),
      headers: _headers(token),
      body: jsonEncode(data),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> batch(String token, List<Map<String, dynamic>> beneficiaires) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/beneficiaires/batch'),
      headers: _headers(token),
      body: jsonEncode({'beneficiaires': beneficiaires}),
    );
    return _decode(response);
  }

  /// Import en masse V2 : cree des DistributionBeneficiary (statut pending).
  /// Chaque item : planning_id, full_name, phone?, cni?, address?, category?,
  /// quantity_kg, is_vulnerable?, is_pregnant?, is_elderly?, is_disabled?
  static Future<Map<String, dynamic>> batchBeneficiaries(String token, List<Map<String, dynamic>> beneficiaries) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/beneficiaries/batch'),
      headers: _headers(token),
      body: jsonEncode({'beneficiaries': beneficiaries}),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> scan(String token, String code) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/scan'),
      headers: _headers(token),
      body: jsonEncode({'code': code}),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> getTicketByCode(String token, String code) async {
    final response = await http.get(
      Uri.parse('$distBaseUrl/tickets/$code'),
      headers: _headers(token),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> getTicketsHistory(
    String token, {
    int? planningId,
    String? status,
    String? search,
    String? collectedSince,
  }) async {
    final params = <String, String>{};
    if (planningId != null) params['planning_id'] = '$planningId';
    if (status != null && status.isNotEmpty) params['status'] = status;
    if (search != null && search.isNotEmpty) params['search'] = search;
    if (collectedSince != null && collectedSince.isNotEmpty) params['collected_since'] = collectedSince;
    final uri = Uri.parse('$distBaseUrl/tickets/history').replace(queryParameters: params.isEmpty ? null : params);
    final response = await http.get(uri, headers: _headers(token));
    return _decode(response);
  }

  static Future<Map<String, dynamic>> getTicket(String token, String code) async {
    final response = await http.get(
      Uri.parse('$distBaseUrl/tickets/$code'),
      headers: _headers(token),
    );
    return _decode(response);
  }

  static Map<String, String> _headers(String token) => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': 'Bearer $token',
  };
}
