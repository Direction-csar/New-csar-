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
  /// sur les codes HTTP >= 400, au lieu d'échouer sur du HTML ou un corps vide.
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

  static Future<Map<String, dynamic>> collectKit(String token, String ticketCode, {String? scanLocation}) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/tickets/$ticketCode/collect'),
      headers: _headers(token),
      body: jsonEncode({'scan_location': scanLocation}),
    );
    return _decode(response);
  }

  static Future<Map<String, dynamic>> getScanHistory(String token, {String? search, String? date}) async {
    final params = <String, String>{};
    if (search != null && search.isNotEmpty) params['search'] = search;
    if (date != null && date.isNotEmpty) params['date'] = date;
    final uri = Uri.parse('$distBaseUrl/scans/history').replace(queryParameters: params.isEmpty ? null : params);
    final response = await http.get(uri, headers: _headers(token));
    return _decode(response);
  }

  static Future<Map<String, dynamic>> getTicketByCode(String token, String code) async {
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
