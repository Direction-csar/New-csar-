import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String apiBaseUrl = 'https://www.csar.sn/api';
  static const String distBaseUrl = 'https://www.csar.sn/mobile/v1/distribution';

  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$apiBaseUrl/login'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> sync(String token) async {
    final response = await http.get(
      Uri.parse('$distBaseUrl/sync'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getEvents(String token) async {
    final response = await http.get(
      Uri.parse('$distBaseUrl/events'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getMyPlannings(String token) async {
    final response = await http.get(
      Uri.parse('$distBaseUrl/my-plannings'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getPlanningBeneficiaries(String token, int planningId) async {
    final response = await http.get(
      Uri.parse('$distBaseUrl/plannings/$planningId/beneficiaries'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
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
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> validateBeneficiary(String token, int beneficiaryId) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/beneficiaries/$beneficiaryId/validate'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> generateTicket(String token, int beneficiaryId) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/beneficiaries/$beneficiaryId/generate-ticket'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> collectKit(String token, String ticketCode, {String? scanLocation}) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/tickets/$ticketCode/collect'),
      headers: _headers(token),
      body: jsonEncode({'scan_location': scanLocation}),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> storeBeneficiaire(String token, Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/beneficiaries'),
      headers: _headers(token),
      body: jsonEncode(data),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> batch(String token, List<Map<String, dynamic>> beneficiaires) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/beneficiaires/batch'),
      headers: _headers(token),
      body: jsonEncode({'beneficiaires': beneficiaires}),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> scan(String token, String code) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/scan'),
      headers: _headers(token),
      body: jsonEncode({'code': code}),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getTicketByCode(String token, String code) async {
    final response = await http.get(
      Uri.parse('$distBaseUrl/tickets/$code'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getTicket(String token, String code) async {
    final response = await http.get(
      Uri.parse('$distBaseUrl/tickets/$code'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
  }

  static Map<String, String> _headers(String token) => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': 'Bearer $token',
  };
}
