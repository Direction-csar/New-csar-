import 'dart:async';
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

  /// Retourne null en cas de succès, sinon un message à afficher à l'agent.
  Future<String?> login(String email, String password) async {
    final Map<String, dynamic> res;
    try {
      res = await ApiService.login(email, password).timeout(ApiService.requestTimeout);
    } on TimeoutException {
      return 'Le serveur ne répond pas. Réessayez dans un instant.';
    } catch (_) {
      return 'Erreur réseau : vérifiez votre connexion';
    }

    if (res['success'] != true) {
      final message = res['message'];
      return message is String && message.isNotEmpty ? message : 'Erreur de connexion';
    }

    final token = res['token'];
    if (token is! String || token.isEmpty) {
      return 'Réponse du serveur invalide (jeton manquant). Contactez l\'administrateur.';
    }
    final rawUser = res['user'];
    final user = rawUser is Map ? Map<String, dynamic>.from(rawUser) : <String, dynamic>{};

    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', token);
      await prefs.setString('auth_user', jsonEncode(user));
    } catch (_) {
      // Stockage local indisponible : la session reste valable jusqu'à la fermeture de l'application.
    }
    _token = token;
    _user = user;
    notifyListeners();
    return null;
  }

  Future<void> logout() async {
    final oldToken = _token;
    if (oldToken != null) {
      // Revocation serveur du jeton : sans reponse, on purge quand meme.
      await ApiService.logout(oldToken).timeout(const Duration(seconds: 5), onTimeout: () {});
    }
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('auth_user');
    _token = null;
    _user = null;
    notifyListeners();
  }
}
