import 'package:flutter/services.dart' show rootBundle;
import 'package:sunmi_printer_plus/sunmi_printer_plus.dart';

import '../../models/ticket_receipt.dart';
import 'thermal_printer_service.dart';

/// Impression sur l'imprimante thermique 58 mm **intégrée** des terminaux
/// Sunmi (V2s/V3 etc.) via le SDK fabricant encapsulé par
/// `sunmi_printer_plus`. Aucune connexion Bluetooth/USB n'est requise :
/// le plugin lie le service d'impression interne du terminal.
///
/// 58 mm ≈ 32 caractères par ligne à la taille par défaut.
class SunmiThermalPrinter extends ThermalPrinterService {
  static const int _cols = 32;

  @override
  String get vendor => 'Sunmi';

  @override
  Future<PrinterState> checkStatus() async {
    try {
      // En 4.1.1, bindingPrinter/initPrinter sont des no-ops dépréciés :
      // la disponibilité se juge par l'appel réel getStatus, qui lève
      // une exception lorsque le service Sunmi est absent du terminal.
      final raw = (await SunmiConfig.getStatus() ?? '').toUpperCase();
      if (raw.isEmpty) return PrinterState.ready;
      if (raw.contains('PAPER') && raw.contains('OUT')) {
        return PrinterState.paperOut;
      }
      if (raw.contains('COVER')) return PrinterState.coverOpen;
      if (raw.contains('ERR') || raw.contains('FAIL')) {
        return PrinterState.error;
      }
      return PrinterState.ready;
    } catch (_) {
      return PrinterState.unavailable;
    }
  }

  @override
  Future<PrintResult> print(TicketReceipt r) async {
    final state = await checkStatus();
    if (state == PrinterState.paperOut) {
      return const PrintResult.fail('Papier thermique épuisé.');
    }
    if (state == PrinterState.coverOpen) {
      return const PrintResult.fail('Capot de l\'imprimante ouvert.');
    }
    if (state == PrinterState.unavailable) {
      return const PrintResult.fail('Imprimante intégrée indisponible.');
    }

    try {
      // ---------- LOGO + EN-TÊTE ----------
      try {
        final logo = await rootBundle.load('assets/logo_distrubition.jpg');
        await SunmiPrinter.printImage(
          logo.buffer.asUint8List(),
          align: SunmiPrintAlign.CENTER,
        );
      } catch (_) {
        // Logo optionnel : ne bloque jamais l'impression du ticket.
      }
      await _text(TicketReceipt.orgLine1, bold: true, center: true);
      await _text(TicketReceipt.orgLine2, bold: true, center: true);
      await _text(TicketReceipt.title, bold: true, center: true);
      await _sep();
      await _text(TicketReceipt.copyLabel, center: true);
      await _sep();

      // ---------- BÉNÉFICIAIRE ----------
      await _row('N° CARTE / REF', r.cardRef);
      await _row('BENEFICIAIRE', r.beneficiaryName);
      await _row('NB BENEFICIAIRES', r.householdSize);
      await _row('SITE', r.site);
      await _row('CAMPAGNE', r.campaign);
      await _row('SERVI PAR', r.servedBy);
      await _row('N° RECU', r.ticketCode);
      await _row('ID COUPON', r.couponId);
      await _row('TYPE', r.distributionType);
      await _row('DATE TRANSACTION', r.transactionDate);
      await _sep();

      // ---------- QR CENTRAL ----------
      await SunmiPrinter.lineWrap(1);
      await SunmiPrinter.printQRCode(
        r.qrToken,
        style: SunmiQrcodeStyle(
          qrcodeSize: 8,
          errorLevel: SunmiQrcodeLevel.LEVEL_M,
          align: SunmiPrintAlign.CENTER,
        ),
      );
      await _text(TicketReceipt.scanHint, bold: true, center: true);
      await SunmiPrinter.lineWrap(1);
      await _sep();

      // ---------- ARTICLES ----------
      await _text('ARTICLES DISTRIBUÉS', bold: true, center: true);
      await SunmiPrinter.printRow(
        cols: [
          SunmiColumn(
            text: 'ARTICLE',
            width: 20,
            style: SunmiTextStyle(bold: true),
          ),
          SunmiColumn(
            text: 'QUANTITÉ',
            width: 12,
            style: SunmiTextStyle(bold: true, align: SunmiPrintAlign.RIGHT),
          ),
        ],
      );
      await _sep();
      if (r.items.isEmpty) {
        await _text('Aucun article renseigné');
      } else {
        for (final it in r.items) {
          for (final line in _wrap(it.name, 20)) {
            await SunmiPrinter.printRow(
              cols: [
                SunmiColumn(text: line, width: 20),
                SunmiColumn(
                  text: line == _wrap(it.name, 20).first ? it.quantity : '',
                  width: 12,
                  style: SunmiTextStyle(align: SunmiPrintAlign.RIGHT),
                ),
              ],
            );
          }
        }
      }
      await _sep();

      // ---------- PIED ----------
      await _row('DATE', r.datePart);
      await _row('HEURE', r.timePart);
      await _row('TERMINAL', r.terminalId);
      await _row('TICKET', r.ticketCode);
      await SunmiPrinter.lineWrap(1);
      for (final line in _wrap(TicketReceipt.keepHint, _cols)) {
        await _text(line, center: true);
      }
      await _text(TicketReceipt.footer, bold: true, center: true);

      await SunmiPrinter.lineWrap(3);
      try {
        await SunmiPrinter.cutPaper();
      } catch (_) {
        // Certains modèles n'ont pas de coupeur : on laisse juste du blanc.
      }
      return const PrintResult.ok();
    } catch (e) {
      return PrintResult.fail('Erreur d\'impression : $e');
    }
  }

  // ---------- Helpers ----------

  Future<void> _text(
    String text, {
    bool bold = false,
    bool center = false,
  }) async {
    for (final line in _wrap(text, _cols)) {
      await SunmiPrinter.printText(
        line,
        style: SunmiTextStyle(
          bold: bold,
          align: center ? SunmiPrintAlign.CENTER : SunmiPrintAlign.LEFT,
        ),
      );
    }
  }

  Future<void> _sep() => _text('-' * _cols, center: true);

  Future<void> _row(String label, String? value) async {
    if (value == null || value.isEmpty) return;
    final lines = _wrap(value, _cols - 14);
    for (var i = 0; i < lines.length; i++) {
      await SunmiPrinter.printRow(
        cols: [
          SunmiColumn(text: i == 0 ? label : '', width: 14),
          SunmiColumn(
            text: lines[i],
            width: _cols - 14,
            style: SunmiTextStyle(align: SunmiPrintAlign.RIGHT, bold: true),
          ),
        ],
      );
    }
  }

  /// Découpe proprement [text] en lignes ≤ [width] caractères sans
  /// couper les mots (le mécanisme 58 mm ne fait pas de retour propre).
  static List<String> _wrap(String text, int width) {
    if (text.isEmpty) return const [''];
    final words = text.split(RegExp(r'\s+'));
    final lines = <String>[];
    var current = '';
    for (final w in words) {
      if (w.length > width) {
        // Mot plus long que la ligne : découpe brutale en morceaux.
        if (current.isNotEmpty) {
          lines.add(current);
          current = '';
        }
        for (var i = 0; i < w.length; i += width) {
          lines.add(
            w.substring(i, i + width > w.length ? w.length : i + width),
          );
        }
        continue;
      }
      final candidate = current.isEmpty ? w : '$current $w';
      if (candidate.length <= width) {
        current = candidate;
      } else {
        lines.add(current);
        current = w;
      }
    }
    if (current.isNotEmpty) lines.add(current);
    return lines.isEmpty ? const [''] : lines;
  }
}
