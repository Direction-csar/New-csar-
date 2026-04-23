import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl = 'https://www.csar.sn/mobile/v1';

  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getProfile(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/profile'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getMarkets(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/markets'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getProducts(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/products'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getProductCategories(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/product-categories'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> getCollections(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/collections'),
      headers: _headers(token),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> submitCollection(String token, Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse('$baseUrl/collections'),
      headers: _headers(token),
      body: jsonEncode(data),
    );
    return jsonDecode(response.body);
  }

  static Future<void> logout(String token) async {
    await http.post(
      Uri.parse('$baseUrl/logout'),
      headers: _headers(token),
    );
  }

  static Map<String, String> _headers(String token) => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': 'Bearer $token',
  };
}
