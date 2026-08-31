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

  static Future<Map<String, dynamic>> collectKit(String token, String ticketCode, {String? scanLocation}) async {
    final response = await http.post(
      Uri.parse('$distBaseUrl/tickets/$ticketCode/collect'),
      headers: _headers(token),
      body: jsonEncode({'scan_location': scanLocation}),
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

  static Map<String, String> _headers(String token) => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': 'Bearer $token',
  };
}
