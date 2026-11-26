/// Configuration des environnements serveur de l'app Comptable.
///
/// Par défaut : production. Pour pointer vers un autre serveur,
/// compiler avec :
///   flutter run --dart-define=API_HOST=http://192.168.X.X:8000
///   flutter build apk --dart-define=API_HOST=https://preprod.csar.sn
///
/// Ne jamais utiliser `localhost` depuis un téléphone physique :
/// passer l'IP LAN du PC de dev (téléphone sur le même réseau).
/// Un émulateur Android peut utiliser http://10.0.2.2:8000.
class AppConfig {
  AppConfig._();

  static const String apiHost = String.fromEnvironment(
    'API_HOST',
    defaultValue: 'https://www.csar.sn',
  );

  /// API mobile Comptable des matières.
  static const String comptableBaseUrl = '$apiHost/api/comptable/v1';
}
