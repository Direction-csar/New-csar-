import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';
import 'diagnostics.dart';

class AuthService extends ChangeNotifier {
  String? _token;
  Map<String, dynamic>? _user;
  bool _isLoading = true;
  static const _storageTimeout = Duration(seconds: 5);
  bool _disposed = false;

  @override
  void dispose() {
    _disposed = true;
    super.dispose();
  }

  void _notify() {
    if (!_disposed) notifyListeners();
  }

  bool get isAuthenticated => _token != null;
  bool get isLoading => _isLoading;
  String? get token => _token;
  Map<String, dynamic>? get user => _user;

  Future<void> loadToken() async {
    try {
      final prefs = await SharedPreferences.getInstance().timeout(_storageTimeout);
      final savedToken = prefs.getString('auth_token');
      final userJson = prefs.getString('auth_user');
      final decoded = userJson == null ? null : jsonDecode(userJson);
      _user = decoded is Map<String, dynamic> ? decoded : null;
      _token = savedToken != null && savedToken.trim().isNotEmpty ? savedToken : null;
    } catch (error, stack) {
      _token = null;
      _user = null;
      Diagnostics.error('restore-session', error, stack);
    } finally {
      _isLoading = false;
      _notify();
    }
  }

  /// Retourne null en cas de succès, sinon un message à afficher à l'agent.
  Future<String?> login(String email, String password) async {
    final Map<String, dynamic> res;
    try {
      res = await ApiService.login(email, password);
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
      final prefs = await SharedPreferences.getInstance().timeout(_storageTimeout);
      await prefs.setString('auth_user', jsonEncode(user)).timeout(_storageTimeout);
      await prefs.setString('auth_token', token).timeout(_storageTimeout);
    } catch (error, stack) {
      // Stockage local indisponible : la session reste valable jusqu'à la fermeture de l'application.
      Diagnostics.error('save-session', error, stack);
    }
    _token = token;
    _user = user;
    _notify();
    return null;
  }

  Future<void> logout() async {
    final oldToken = _token;
    await clearSession();
    if (oldToken != null) {
      // Le réseau ne retarde jamais le retour à la connexion.
      unawaited(ApiService.logout(oldToken));
    }
  }

  Future<void> clearSession() async {
    _token = null;
    _user = null;
    try {
      final prefs = await SharedPreferences.getInstance().timeout(_storageTimeout);
      await prefs.remove('auth_token').timeout(_storageTimeout);
      await prefs.remove('auth_user').timeout(_storageTimeout);
    } catch (error, stack) {
      Diagnostics.error('clear-session', error, stack);
    } finally {
      _notify();
    }
  }
}
