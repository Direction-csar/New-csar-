import 'dart:io';

import 'package:device_info_plus/device_info_plus.dart';

import '../../models/ticket_receipt.dart';
import 'sunmi_thermal_printer.dart';

/// État réel de l'imprimante thermique, mappé depuis le SDK fabricant.
enum PrinterState { unknown, ready, unavailable, paperOut, coverOpen, error }

/// Résultat d'une tentative d'impression.
class PrintResult {
  final bool success;
  final String? message;

  const PrintResult.ok() : success = true, message = null;
  const PrintResult.fail(this.message) : success = false;
}

/// Abstraction d'impression thermique indépendante du fabricant.
///
/// Le terminal cible possède une imprimante 58 mm *intégrée* : aucune
/// sélection d'imprimante externe n'est requise. Chaque fabricant fournit
/// son implémentation (Sunmi en premier). Si aucune implémentation
/// intégrée ne correspond au terminal détecté, `detect()` retourne null
/// et l'écran bascule sur le chemin PDF/impression système.
abstract class ThermalPrinterService {
  /// Nom du fabricant/SDK utilisé (ex. « Sunmi »).
  String get vendor;

  /// Vrai si l'imprimante est intégrée au terminal (pas de sélection).
  bool get isIntegrated => true;

  /// Interroge l'état réel de l'imprimante via le SDK fabricant.
  Future<PrinterState> checkStatus();

  /// Imprime [receipt] — opération purement matérielle, aucun effet
  /// métier (ni validation, ni collecte, ni nouveau ticket).
  Future<PrintResult> print(TicketReceipt receipt);

  /// Détecte un fabricant d'imprimante intégrée pris en charge.
  ///
  /// Retourne l'implémentation correspondante ou null si le terminal
  /// n'est pas reconnu — auquel cas l'appelant utilise le fallback PDF.
  static Future<ThermalPrinterService?> detect() async {
    if (!Platform.isAndroid) return null;
    try {
      final info = await DeviceInfoPlugin().androidInfo;
      final ids = '${info.brand} ${info.manufacturer} ${info.model}'
          .toUpperCase();
      if (ids.contains('SUNMI')) {
        return SunmiThermalPrinter();
      }
    } catch (_) {
      // Détection impossible → fallback géré par l'appelant.
    }
    return null;
  }

  /// Libellé humain d'un [PrinterState].
  static String stateLabel(PrinterState s) {
    switch (s) {
      case PrinterState.ready:
        return 'prête';
      case PrinterState.unavailable:
        return 'indisponible';
      case PrinterState.paperOut:
        return 'papier épuisé';
      case PrinterState.coverOpen:
        return 'capot ouvert';
      case PrinterState.error:
        return 'erreur matérielle';
      case PrinterState.unknown:
        return 'état inconnu';
    }
  }
}
