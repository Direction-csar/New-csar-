import 'dart:convert';
import 'package:http/http.dart' as http;
import 'diagnostics.dart';

class ApiService {
  /// URL du serveur, configurable au build :
  /// flutter build apk --dart-define=CSAR_API_URL=http://10.0.2.2
  /// La valeur par défaut reste la production.
  static const String serverUrl = String.fromEnvironment(
    'CSAR_API_URL',
    defaultValue: 'https://www.csar.sn',
  );
  static const String apiBaseUrl = '$serverUrl/api';

  /// Délai maximal d'une requête : au-delà, l'agent voit un message plutôt qu'un chargement sans fin.
  static const Duration requestTimeout = Duration(seconds: 25);
  static const String distBaseUrl = '$serverUrl/mobile/v1/distribution';

  static Future<Map<String, dynamic>> _request(
    String operation,
    Future<http.Response> Function(http.Client) send, {
    Duration timeout = requestTimeout,
  }) async {
    final client = http.Client();
    final watch = Stopwatch()..start();
    try {
      final response = await send(client).timeout(timeout);
      Diagnostics.event(operation,
          'http=${response.statusCode} elapsed_ms=${watch.elapsedMilliseconds}');
      return _decode(response);
    } catch (error, stack) {
      Diagnostics.error(operation, error, stack);
      rethrow;
    } finally {
      // Ferme aussi la connexion lorsque le délai expire.
      client.close();
    }
  }

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
        'http_status': response.statusCode,
        'message': _httpError(response.statusCode),
      };
    }
    if (body is! Map<String, dynamic>) {
      return {
        'success': false,
        'http_status': response.statusCode,
        'message': 'Format de réponse inattendu du serveur (HTTP ${response.statusCode})',
      };
    }
    final map = body;
    map['http_status'] = response.statusCode;
    if (response.statusCode >= 400) {
      map['success'] = false;
      if ([403, 404, 419].contains(response.statusCode) ||
          response.statusCode >= 500 ||
          map['message'] is! String) {
        map['message'] = _httpError(response.statusCode);
      }
    }
    return map;
  }

  static String _httpError(int status) => switch (status) {
    401 => 'Session expirée ou identifiants invalides (HTTP 401). Reconnectez-vous.',
    403 => 'Accès refusé (HTTP 403). Vérifiez les permissions Distribution auprès de l’administrateur.',
    404 => 'Service Distribution introuvable (HTTP 404). Contactez l’administrateur.',
    419 => 'Session expirée (HTTP 419). Reconnectez-vous.',
    >= 500 => 'Serveur indisponible (HTTP $status). Réessayez dans un instant.',
    _ => 'Réponse serveur invalide (HTTP $status)',
  };

  static Future<Map<String, dynamic>> login(String email, String password) =>
    _request('login', (client) => client.post(
      Uri.parse('$apiBaseUrl/login'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    ));

  /// Revocation du jeton cote serveur (best effort : le nettoyage local a lieu quand meme).
  static Future<void> logout(String token) async {
    try {
      await _request('logout', (client) => client.post(
        Uri.parse('$distBaseUrl/logout'),
        headers: _headers(token),
      ), timeout: const Duration(seconds: 5));
    } catch (_) {}
  }

  /// True si la reponse indique un jeton refuse/expire (deconnexion requise).
  static bool isAuthError(Map<String, dynamic> res) =>
      res['http_status'] == 401 || res['http_status'] == 419;

  static Future<Map<String, dynamic>> sync(String token) =>
    _request('sync', (client) => client.get(
      Uri.parse('$distBaseUrl/sync'),
      headers: _headers(token),
    ));

  static Future<Map<String, dynamic>> getEvents(String token) =>
    _request('events', (client) => client.get(
      Uri.parse('$distBaseUrl/events'),
      headers: _headers(token),
    ));

  static Future<Map<String, dynamic>> getMyPlannings(String token) =>
    _request('my-plannings', (client) => client.get(
      Uri.parse('$distBaseUrl/my-plannings'),
      headers: _headers(token),
    ));

  static Future<Map<String, dynamic>> getPlanningBeneficiaries(String token, int planningId) =>
    _request('planning-beneficiaries', (client) => client.get(
      Uri.parse('$distBaseUrl/plannings/$planningId/beneficiaries'),
      headers: _headers(token),
    ));

  static Future<Map<String, dynamic>> checkDuplicate(String token, int planningId, String? phone, String? cni, String fullName) =>
    _request('check-duplicate', (client) => client.post(
      Uri.parse('$distBaseUrl/check-duplicate'),
      headers: _headers(token),
      body: jsonEncode({
        'planning_id': planningId,
        'phone': phone,
        'cni': cni,
        'full_name': fullName,
      }),
    ));

  static Future<Map<String, dynamic>> validateBeneficiary(String token, int beneficiaryId) =>
    _request('validate-beneficiary', (client) => client.post(
      Uri.parse('$distBaseUrl/beneficiaries/$beneficiaryId/validate'),
      headers: _headers(token),
    ));

  static Future<Map<String, dynamic>> generateTicket(String token, int beneficiaryId) =>
    _request('generate-ticket', (client) => client.post(
      Uri.parse('$distBaseUrl/beneficiaries/$beneficiaryId/generate-ticket'),
      headers: _headers(token),
    ));

  static Future<Map<String, dynamic>> collectKit(String token, String ticketCode, {String? scanLocation}) =>
    _request('collect-kit', (client) => client.post(
      Uri.parse('$distBaseUrl/tickets/$ticketCode/collect'),
      headers: _headers(token),
      // 'location' est la clé lue par le backend (scan_location accepté aussi).
      body: jsonEncode({'location': scanLocation}),
    ));

  static Future<Map<String, dynamic>> storeBeneficiaire(String token, Map<String, dynamic> data) =>
    _request('store-beneficiaire', (client) => client.post(
      Uri.parse('$distBaseUrl/beneficiaries'),
      headers: _headers(token),
      body: jsonEncode(data),
    ));

  static Future<Map<String, dynamic>> batch(String token, List<Map<String, dynamic>> beneficiaires) =>
    _request('beneficiaires-batch', (client) => client.post(
      Uri.parse('$distBaseUrl/beneficiaires/batch'),
      headers: _headers(token),
      body: jsonEncode({'beneficiaires': beneficiaires}),
    ));

  /// Import en masse V2 : cree des DistributionBeneficiary (statut pending).
  /// Chaque item : planning_id, full_name, phone?, cni?, address?, category?,
  /// quantity_kg, is_vulnerable?, is_pregnant?, is_elderly?, is_disabled?
  static Future<Map<String, dynamic>> batchBeneficiaries(String token, List<Map<String, dynamic>> beneficiaries) =>
    _request('beneficiaires-batch-v2', (client) => client.post(
      Uri.parse('$distBaseUrl/beneficiaires/batch'),
      headers: _headers(token),
      body: jsonEncode({'beneficiaries': beneficiaries}),
    ));

  static Future<Map<String, dynamic>> scan(String token, String code) =>
    _request('scan', (client) => client.post(
      Uri.parse('$distBaseUrl/scan'),
      headers: _headers(token),
      body: jsonEncode({'code': code}),
    ));

  static Future<Map<String, dynamic>> getTicketByCode(String token, String code) =>
    _request('ticket-by-code', (client) => client.get(
      Uri.parse('$distBaseUrl/tickets/$code'),
      headers: _headers(token),
    ));

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
    return _request('tickets-history', (client) => client.get(uri, headers: _headers(token)));
  }

  static Future<Map<String, dynamic>> getTicket(String token, String code) =>
    _request('ticket', (client) => client.get(
      Uri.parse('$distBaseUrl/tickets/$code'),
      headers: _headers(token),
    ));

  static Map<String, String> _headers(String token) => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': 'Bearer $token',
  };
}
