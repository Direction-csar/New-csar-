import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';

class AuthService extends ChangeNotifier {
  String? _token;
  Map<String, dynamic>? _user;
  bool _isLoading = true;

  bool get isAuthenticated => _token != null;
  bool get isLoading => _isLoading;
  String? get token => _token;
  Map<String, dynamic>? get user => _user;

  Future<void> loadToken() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    final userJson = prefs.getString('auth_user');
    if (_token != null && userJson != null) {
      try {
        _user = Map<String, dynamic>.from(jsonDecode(userJson));
      } catch (_) {}
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<String?> login(String email, String password) async {
    try {
      final res = await ApiService.login(email, password);
      if (res['success'] == true) {
        _token = res['token'];
        _user = Map<String, dynamic>.from(res['user'] ?? {});
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', _token!);
        await prefs.setString('auth_user', jsonEncode(_user));
        notifyListeners();
        return null;
      }
      return res['message'] ?? 'Erreur de connexion';
    } catch (e) {
      return 'Erreur réseau : vérifiez votre connexion';
    }
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('auth_user');
    _token = null;
    _user = null;
    notifyListeners();
  }
}
