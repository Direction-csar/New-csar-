import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';

class AuthProvider with ChangeNotifier {
  bool _isAuthenticated = false;
  bool _isLoading = true;
  Map<String, dynamic>? _user;

  bool get isAuthenticated => _isAuthenticated;
  bool get isLoading => _isLoading;
  Map<String, dynamic>? get user => _user;

  AuthProvider() {
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    _isLoading = true;
    notifyListeners();

    final token = await ApiService.getToken();
    if (token != null && token.isNotEmpty) {
      _isAuthenticated = true;
      await _loadStoredUser();
    }

    _isLoading = false;
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    notifyListeners();

    try {
      final result = await ApiService.login(email, password);
      if (result['success'] == true) {
        _isAuthenticated = true;
        _user = result['user'];
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_user', jsonEncode(_user));
        _isLoading = false;
        notifyListeners();
        return true;
      }
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> _loadStoredUser() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final raw = prefs.getString('auth_user');
      if (raw != null) {
        _user = jsonDecode(raw) as Map<String, dynamic>;
      }
    } catch (_) {}
  }

  Future<void> logout() async {
    await ApiService.clearToken();
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_user');
    _isAuthenticated = false;
    _user = null;
    notifyListeners();
  }
}
