import 'package:flutter/foundation.dart';

/// Ne journalise jamais les corps HTTP, URL, en-têtes ou messages d'exception :
/// ils peuvent contenir un jeton, un mot de passe ou des données personnelles.
class Diagnostics {
  static void event(String operation, String outcome) {
    debugPrint('[Distribution] $operation $outcome');
  }

  static void error(String operation, Object error, StackTrace stack) {
    event(operation, 'error=${error.runtimeType}');
    for (final frame in stack.toString().split('\n').where(
      (line) => line.contains('package:csar_distribution/'),
    ).take(8)) {
      debugPrint(frame);
    }
  }
}
