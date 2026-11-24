/// Configuration serveur de l'APK Inspecteur CSAR.
///
/// Par défaut : production. Pour pointer vers un autre serveur :
///   flutter run --dart-define=API_HOST=http://192.168.X.X
///   flutter build apk --dart-define=API_HOST=https://preprod.csar.sn
class AppConfig {
  AppConfig._();

  static const String apiHost = String.fromEnvironment(
    'API_HOST',
    defaultValue: 'https://www.csar.sn',
  );

  /// API stock / inspections (login inspecteur, opérations, vérifications).
  static const String baseUrl = '$apiHost/api/warehouse/v1';
}
