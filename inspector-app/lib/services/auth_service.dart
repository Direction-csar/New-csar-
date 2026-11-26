import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';

/// Session de l'inspecteur : jeton Sanctum + profil, persistés localement.
class AuthService extends ChangeNotifier {
  static const _storage = FlutterSecureStorage();
  static const _tokenKey = 'inspector_token';
  static const _userKey = 'inspector_user';

  String? _token;
  Map<String, dynamic>? _user;
  bool _isLoading = true;

  bool get isAuthenticated => _token != null;
  bool get isLoading => _isLoading;
  String? get token => _token;
  Map<String, dynamic>? get user => _user;
  String get userName => (_user?['name'] as String?) ?? '';

  Future<void> loadSession() async {
    try {
      _token = await _storage.read(key: _tokenKey);
      final raw = await _storage.read(key: _userKey);
      if (raw != null && raw.isNotEmpty) {
        _user = Map<String, dynamic>.from(jsonDecode(raw) as Map);
      }
      if (_token == null) {
        // Repli : certains appareils Android refusent le secure storage.
        final prefs = await SharedPreferences.getInstance();
        _token = prefs.getString(_tokenKey);
      }
    } catch (_) {
      _token = null;
      _user = null;
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<String?> login(String email, String password) async {
    try {
      final res = await ApiService.loginInspector(email, password);
      if (res['success'] == true && res['token'] != null) {
        _token = res['token'] as String;
        _user = Map<String, dynamic>.from(res['user'] as Map);
        try {
          await _storage.write(key: _tokenKey, value: _token);
          await _storage.write(key: _userKey, value: jsonEncode(_user));
        } catch (_) {
          // Secure storage indisponible : repli SharedPreferences.
        }
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_tokenKey, _token!);
        notifyListeners();
        return null;
      }
      return (res['message'] as String?) ?? 'Identifiants invalides.';
    } catch (_) {
      return 'Erreur réseau : vérifiez votre connexion et l\'adresse du serveur.';
    }
  }

  Future<void> logout() async {
    _token = null;
    _user = null;
    try {
      await _storage.delete(key: _tokenKey);
      await _storage.delete(key: _userKey);
    } catch (_) {}
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    notifyListeners();
  }
}
