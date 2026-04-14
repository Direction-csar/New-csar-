import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';

class AuthService extends ChangeNotifier {
  String? _token;
  Map<String, dynamic>? _collector;
  bool _isLoading = true;

  bool get isAuthenticated => _token != null;
  bool get isLoading => _isLoading;
  String? get token => _token;
  Map<String, dynamic>? get collector => _collector;

  Future<void> loadToken() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    if (_token != null) {
      try {
        final res = await ApiService.getProfile(_token!);
        if (res['success'] == true) {
          _collector = res['data'];
        } else {
          _token = null;
          await prefs.remove('auth_token');
        }
      } catch (_) {
        _token = null;
        await prefs.remove('auth_token');
      }
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<String?> login(String email, String password) async {
    try {
      final res = await ApiService.login(email, password);
      if (res['success'] == true) {
        _token = res['data']['token'];
        _collector = res['data']['collector'];
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', _token!);
        notifyListeners();
        return null;
      }
      return res['message'] ?? 'Erreur de connexion';
    } catch (e) {
      return 'Erreur réseau : vérifiez votre connexion';
    }
  }

  Future<void> logout() async {
    if (_token != null) {
      try {
        await ApiService.logout(_token!);
      } catch (_) {}
    }
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    _token = null;
    _collector = null;
    notifyListeners();
  }
}
